<?php

namespace App\Console\Commands;

use App\Models\Designation;
use App\Models\Language;
use App\Models\PosLabel;
use App\Models\WordObject;
use App\Models\WordPronunciation;
use App\Models\WordSense;
use App\Models\WordSenseDefinition;
use App\Models\WordSenseExample;
use App\Services\Enrichment\FrozenSets;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportWordData extends Command
{
    protected $signature = 'words:import
        {file : Path to JSONL file}
        {--dry-run : Validate only, do not import}
        {--upsert : Update existing entries with richer data (default: skip existing)}
        {--status=published : Status for new entries (draft|review|published)}
        {--enriched-by= : Override enriched_by for every sense in this import. If omitted, reads from each senses\'s enriched_by field in the JSON (written by enrich:skeleton).}';

    protected $description = 'Import word data from v2.3 skeleton JSONL format (enricher attribution read from the file, not hardcoded)';

    private const PINYIN_SYSTEM_ID = 1;

    private int $langEn;
    private int $langZh;
    private array $designations;
    private array $posLabels;
    /** @var array<int,string> pos_id → slug (for matching existing senses by POS) */
    private array $posSlugById;

    public function handle(): int
    {
        $file   = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $upsert = $this->option('upsert');
        $status = $this->option('status');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        // Load caches
        $this->langEn       = Language::where('code', 'en')->value('id');
        $this->langZh       = Language::where('code', 'zh-TW')->value('id');
        $this->designations = Designation::all()->keyBy('slug')->map->id->all();
        $this->posLabels    = PosLabel::all()->keyBy('slug')->map->id->all();
        $this->posSlugById  = PosLabel::all()->keyBy('id')->map->slug->all();

        // Parse: support both JSONL (one entry per line) and pretty-printed
        // JSON array (the format enrich:skeleton emits). Detect by sniffing
        // the first non-whitespace character: '[' → JSON array; '{' → JSONL.
        $raw = file_get_contents($file);
        $trimmed = ltrim($raw);
        $entries = [];

        if ($trimmed !== '' && $trimmed[0] === '[') {
            // Pretty-printed JSON array — parse as one document.
            $entries = json_decode($raw, true);
            if (! is_array($entries)) {
                $this->error('JSON array parse error: ' . json_last_error_msg());
                return 1;
            }
        } else {
            // JSONL — one entry per line.
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $parseErrors = 0;
            foreach ($lines as $i => $line) {
                $entry = json_decode($line, true);
                if (! $entry) {
                    $this->error('JSON parse error on line ' . ($i + 1));
                    $parseErrors++;
                    continue;
                }
                $entries[] = $entry;
            }
            if ($parseErrors) {
                $this->error("{$parseErrors} parse errors — aborting.");
                return 1;
            }
        }

        // Filter out non-word top-level entries (e.g. _meta header blocks
        // produced by gen_lens_disputes_rev0.php and similar cowork artifacts).
        // Convention: word entries have a 'word' key; metadata blocks do not.
        $rawCount = count($entries);
        $entries = array_values(array_filter($entries, fn($e) => is_array($e) && isset($e['word'])));
        $skipped = $rawCount - count($entries);

        $count = count($entries);
        $this->info("Loaded {$count} entries from {$file}" . ($skipped ? " ({$skipped} non-word header(s) skipped)" : '') . ($upsert ? ' [UPSERT MODE]' : ''));

        // Validate
        $issues = $this->validate($entries);
        if ($issues) {
            $this->error("Validation failed with " . count($issues) . " issues:");
            foreach ($issues as $issue) {
                $this->line("  - {$issue}");
            }
            return 1;
        }

        $this->info("Validation passed ✓");

        if ($dryRun) {
            $this->info("Dry run — no changes made.");
            return 0;
        }

        // Import
        $created = $updated = $skipped = $senses = 0;

        DB::beginTransaction();

        try {
            foreach ($entries as $entry) {
                $result = $this->importEntry($entry, $status, $upsert);

                if ($result['action'] === 'created') {
                    $created++;
                    $senses += $result['senses'];
                    $this->line("  ✓ {$entry['word']['traditional']} (created, {$result['senses']} senses)");
                } elseif ($result['action'] === 'updated') {
                    $updated++;
                    $senses += $result['senses'];
                    $detail = sprintf(
                        'matched %d, editorial-added %d, preserved %d',
                        $result['matched']   ?? 0,
                        $result['editorial'] ?? 0,
                        $result['preserved'] ?? 0
                    );
                    $this->line("  ↻ {$entry['word']['traditional']} (upsert: {$detail})");
                } else {
                    $skipped++;
                    $this->line("  ○ {$entry['word']['traditional']} (exists)");
                }
            }

            DB::commit();

            $this->info("Import complete: {$created} created, {$updated} updated, {$skipped} skipped, {$senses} senses.");

            // Bust the lexicon cache so the site picks up new words immediately.
            cache()->forget('lexicon_words');
            cache()->forget('lexicon_words_slim');
            cache()->forget('lexicon_domain_groups');
            cache()->forget('word_index_slim');

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    private function validate(array $entries): array
    {
        $issues = [];

        foreach ($entries as $i => $entry) {
            $trad = $entry['word']['traditional'] ?? "entry[$i]";

            if (empty($entry['word']['smart_id'])) {
                $issues[] = "{$trad}: missing smart_id";
            }
            if (empty($entry['word']['structure'])) {
                $issues[] = "{$trad}: missing structure";
            }

            foreach ($entry['senses'] ?? [] as $j => $s) {
                $pos = $s['pos'] ?? '';
                if (! isset($this->posLabels[$pos])) {
                    $issues[] = "{$trad} sense " . ($j + 1) . ": unknown POS '{$pos}'";
                }
                if (empty($s['definitions']['en'])) {
                    $issues[] = "{$trad} sense " . ($j + 1) . ": missing EN definition";
                }
                if (empty($s['pinyin'])) {
                    $issues[] = "{$trad} sense " . ($j + 1) . ": missing pinyin";
                }
                if (count($s['examples'] ?? []) < 2) {
                    $issues[] = "{$trad} sense " . ($j + 1) . ": needs at least 2 examples";
                }

                // Category-strict validation via FrozenSets (single source of truth).
                // Replaces permissive "any designation" checks that let e.g. 'tocfl-high'
                // pass a channel check because it exists as *some* designation.
                $channel = $s['channel'] ?? '';
                if ($channel && ! FrozenSets::isValidChannel($channel)) {
                    $issues[] = "{$trad}: unknown channel '{$channel}' — valid: " . implode(', ', FrozenSets::channels());
                }
                $connotation = $s['connotation'] ?? '';
                if ($connotation && ! FrozenSets::isValidConnotation($connotation)) {
                    $issues[] = "{$trad}: unknown connotation '{$connotation}' — valid: " . implode(', ', FrozenSets::connotations());
                }
                foreach ($s['domains'] ?? [] as $d) {
                    if (! FrozenSets::isValidDomain($d)) {
                        $issues[] = "{$trad}: unknown domain '{$d}'";
                    }
                }
                foreach ($s['register'] ?? [] as $r) {
                    if (! FrozenSets::isValidRegister($r)) {
                        $issues[] = "{$trad}: unknown register '{$r}' — valid: " . implode(', ', FrozenSets::registers());
                    }
                }
                foreach ($s['dimension'] ?? [] as $dim) {
                    if (! FrozenSets::isValidDimension($dim)) {
                        $issues[] = "{$trad}: unknown dimension '{$dim}' — valid: " . implode(', ', FrozenSets::dimensions());
                    }
                }
            }
        }

        return $issues;
    }

    private function importEntry(array $entry, string $status, bool $upsert): array
    {
        $w = $entry['word'];

        $word = WordObject::where('smart_id', $w['smart_id'])->first();

        if ($word) {
            if (! $upsert) {
                return ['action' => 'skipped', 'senses' => 0];
            }

            // Upsert on existing word — per-sense match, NEVER wipe.
            //
            // Policy (2026-04-22, after audit revealed 86 foundational senses
            // lost to the previous wipe-and-recreate upsert):
            //   1. Match incoming senses to existing by (pinyin, pos).
            //   2. Matched → update editorial content in place. Seed fields
            //      (tocfl_level_id, hsk_level_id, source, alignment) are
            //      preserved — band stamps come from the seeder before
            //      enrichment begins, never from the enrichment re-import.
            //   3. Unmatched incoming → create as an EDITORIAL addition:
            //      source='editorial', alignment='partial', NO band stamps.
            //      Enricher discipline (guided by _sibling_senses context in
            //      the skeleton): only invent a new POS sense when it's
            //      absent from both the current batch AND siblings. When
            //      this path triggers, it's 澄言's deliberate judgment and
            //      the provenance is recorded explicitly.
            //   4. Unmatched EXISTING (on the word, not in the batch) →
            //      preserve. Never delete from import.

            // Word-level: update benign fields. Preserve status (never
            // downgrade published→draft from a draft batch). Preserve
            // traditional (identifier — if smart_id matches, trad is right).
            $wordStatus = $word->status === 'published' ? 'published' : $status;
            $word->update([
                'simplified' => $w['simplified'] ?? $word->simplified,
                'structure'  => $w['structure']  ?? $word->structure,
                'status'     => $wordStatus,
            ]);

            // Index existing senses by (pinyin|pos). POS from definitions
            // (EN row — authoritative since pivot retired 2026-04-21).
            $existingSenses = $word->senses()->with(['pronunciation', 'definitions'])->get();
            $existingByKey = [];
            foreach ($existingSenses as $es) {
                $pinyin = $es->pronunciation?->pronunciation_text ?? '';
                $defEn = $es->definitions->where('language_id', $this->langEn)->first();
                $posSlug = $defEn ? ($this->posSlugById[$defEn->pos_id] ?? '') : '';
                $key = $pinyin . '|' . $posSlug;
                $existingByKey[$key] = $es;
            }

            $matched = 0;
            $editorialAdded = 0;
            $matchedIds = [];
            foreach ($entry['senses'] as $i => $senseData) {
                $key = ($senseData['pinyin'] ?? '') . '|' . ($senseData['pos'] ?? '');
                if (isset($existingByKey[$key])) {
                    $this->updateExistingSense($existingByKey[$key], $senseData, $status);
                    $matchedIds[] = $existingByKey[$key]->id;
                    $matched++;
                } else {
                    // Editorial addition. Force provenance fields; drop any
                    // tocfl/hsk that might be in the JSON (editorial additions
                    // are out-of-band by definition).
                    $senseData['source']    = 'editorial';
                    $senseData['alignment'] = 'partial';
                    unset($senseData['tocfl'], $senseData['hsk']);
                    $this->importSense($word, $senseData, $i, $status, stampBands: false);
                    $this->line("    ✚ editorial sense added: {$key}");
                    $editorialAdded++;
                }
            }

            $unmatchedExisting = $existingSenses->reject(fn ($es) => in_array($es->id, $matchedIds));
            $preserved = $unmatchedExisting->count();
            foreach ($unmatchedExisting as $es) {
                $pinyin = $es->pronunciation?->pronunciation_text ?? '';
                $defEn = $es->definitions->where('language_id', $this->langEn)->first();
                $posSlug = $defEn ? ($this->posSlugById[$defEn->pos_id] ?? '') : '';
                $this->line("    ⊙ preserved existing sense: {$pinyin}|{$posSlug} (sense_id={$es->id})");
            }

            return [
                'action'    => 'updated',
                'senses'    => $matched + $editorialAdded,
                'matched'   => $matched,
                'editorial' => $editorialAdded,
                'preserved' => $preserved,
            ];
        }

        // New entry
        $word = WordObject::create([
            'smart_id'     => $w['smart_id'],
            'traditional'  => $w['traditional'],
            'simplified'   => $w['simplified'] ?? $w['traditional'],
            'structure'    => $w['structure'],
            'status'       => $status,
        ]);

        $senseCount = 0;
        foreach ($entry['senses'] as $i => $senseData) {
            $this->importSense($word, $senseData, $i, $status);
            $senseCount++;
        }

        return ['action' => 'created', 'senses' => $senseCount];
    }

    /**
     * Create a new sense.
     *
     * @param  bool  $stampBands  When false, tocfl_level_id and hsk_level_id
     *                            are left null regardless of incoming JSON.
     *                            Upsert paths pass false — band stamps are
     *                            seed data, never written by the enrichment
     *                            pipeline. When true (the non-upsert /
     *                            first-import path), stamps are taken from
     *                            the JSON; that path will eventually move
     *                            to a master-driven seeder.
     */
    private function importSense(WordObject $word, array $s, int $sortOrder, string $status, bool $stampBands = true): void
    {
        // Pronunciation
        $pronunciation = WordPronunciation::firstOrCreate(
            [
                'word_object_id'          => $word->id,
                'pronunciation_system_id' => self::PINYIN_SYSTEM_ID,
                'pronunciation_text'      => $s['pinyin'],
            ],
            ['is_primary' => true]
        );

        // Resolve FKs
        $channelId      = isset($s['channel'])     && $s['channel']     ? ($this->designations[$s['channel']]     ?? null) : null;
        $connotationId  = isset($s['connotation']) && $s['connotation'] ? ($this->designations[$s['connotation']] ?? null) : null;
        $sensitivityId  = isset($s['sensitivity']) ? ($this->designations[$s['sensitivity']] ?? null) : null;
        $tocflId        = $stampBands && isset($s['tocfl']) ? ($this->designations[$s['tocfl']] ?? null) : null;
        $hskId          = $stampBands && isset($s['hsk'])   ? ($this->designations[$s['hsk']]   ?? null) : null;

        // Create sense. source/alignment are recorded explicitly — the
        // upsert path sets source='editorial'/alignment='partial' for
        // editorial additions (new sense POS that 澄言 added beyond TOCFL).
        // First-import path defaults to source='tocfl'/alignment='full'
        // (the master-seeded case).
        $sense = WordSense::create([
            'word_object_id'   => $word->id,
            'pronunciation_id' => $pronunciation->id,
            'channel_id'       => $channelId,
            'connotation_id'   => $connotationId,
            'sensitivity_id'   => $sensitivityId,
            'intensity'        => $s['intensity'] ?? null,
            'valency'          => $s['valency'] ?? null,
            'formula'          => $s['formula'] ?? null,
            'usage_note'       => $s['usage_note'] ?? null,
            'learner_traps'    => $s['learner_traps'] ?? null,
            'tocfl_level_id'   => $tocflId,
            'hsk_level_id'     => $hskId,
            'source'           => $s['source']    ?? 'tocfl',
            'alignment'        => $s['alignment'] ?? 'full',
            'status'           => $status,
            // Enricher attribution: CLI flag wins, then per-sense field from
            // the JSON (written by enrich:skeleton), then null. NEVER
            // hardcoded — that was a silent-attribution bug that mislabeled
            // 388 Chengyan-era senses as Huiming before 2026-04-21.
            'enriched_by'      => $this->option('enriched-by') ?: ($s['enriched_by'] ?? null),
            'enriched_at'      => now(),
        ]);

        $this->writeSenseChildren($sense, $s);
    }

    /**
     * Update an existing sense in place — upsert path.
     *
     * NEVER touches tocfl_level_id, hsk_level_id, source, alignment. Those
     * are seed data, set by the seeder before enrichment; the enrichment
     * re-import only replaces editorial content.
     *
     * Child content (definitions, examples, notes, pivots) is wiped and
     * rewritten from the incoming data. User-contributed examples
     * (source != 'default') are preserved.
     */
    private function updateExistingSense(WordSense $sense, array $s, string $status): void
    {
        $senseId = $sense->id;

        // Pronunciation — firstOrCreate will return the existing row when
        // the match key hit (pinyin matched). No harm in the redundant call.
        $pronunciation = WordPronunciation::firstOrCreate(
            [
                'word_object_id'          => $sense->word_object_id,
                'pronunciation_system_id' => self::PINYIN_SYSTEM_ID,
                'pronunciation_text'      => $s['pinyin'],
            ],
            ['is_primary' => false]
        );

        // Resolve editorial FKs. tocfl_level_id / hsk_level_id deliberately
        // NOT read from $s — they're seed fields, untouchable here.
        $channelId     = isset($s['channel'])     && $s['channel']     ? ($this->designations[$s['channel']]     ?? null) : null;
        $connotationId = isset($s['connotation']) && $s['connotation'] ? ($this->designations[$s['connotation']] ?? null) : null;
        $sensitivityId = isset($s['sensitivity']) ? ($this->designations[$s['sensitivity']] ?? null) : null;

        // Preserve status upward — never downgrade published→draft.
        $senseStatus = $sense->status === 'published' ? 'published' : $status;

        $sense->update([
            'pronunciation_id' => $pronunciation->id,
            'channel_id'       => $channelId,
            'connotation_id'   => $connotationId,
            'sensitivity_id'   => $sensitivityId,
            'intensity'        => $s['intensity'] ?? null,
            'valency'          => $s['valency'] ?? null,
            'status'           => $senseStatus,
            'enriched_by'      => $this->option('enriched-by') ?: ($s['enriched_by'] ?? $sense->enriched_by),
            'enriched_at'      => now(),
            // tocfl_level_id, hsk_level_id, source, alignment — NOT touched.
        ]);

        // Wipe child content for this sense. User-contributed examples
        // (source != 'default') are preserved. Example translations for
        // default examples need explicit cleanup — no CASCADE assumed.
        $defaultExampleIds = WordSenseExample::where('word_sense_id', $senseId)
            ->where('source', 'default')
            ->pluck('id')->all();
        if ($defaultExampleIds) {
            DB::table('word_sense_example_translations')
                ->whereIn('word_sense_example_id', $defaultExampleIds)
                ->delete();
            WordSenseExample::whereIn('id', $defaultExampleIds)->delete();
        }
        WordSenseDefinition::where('word_sense_id', $senseId)->delete();
        DB::table('word_sense_designations')->where('word_sense_id', $senseId)->delete();
        DB::table('word_sense_domains')->where('word_sense_id', $senseId)->delete();
        DB::table('word_sense_notes')->where('word_sense_id', $senseId)->delete();

        // Rewrite child content from incoming data.
        $this->writeSenseChildren($sense, $s);
    }

    /**
     * Write all child content for a sense: domains, designations pivots,
     * definitions (EN + zh-TW with POS), examples + translations, notes.
     *
     * Shared by importSense (new) and updateExistingSense (upsert).
     * Assumes the caller has already wiped any prior child rows when
     * updating an existing sense.
     */
    private function writeSenseChildren(WordSense $sense, array $s): void
    {
        // Domains (ordered by relevance, max 4)
        $domains = array_slice($s['domains'] ?? [], 0, 4);
        $domainSync = [];
        foreach ($domains as $idx => $slug) {
            $id = $this->designations[$slug] ?? null;
            if ($id) {
                $domainSync[$id] = ['sort_order' => $idx];
            }
        }
        if ($domainSync) {
            $sense->domains()->sync($domainSync);
        }

        // Designations pivot: register + dimensions
        $designationIds = [];
        foreach ($s['register'] ?? [] as $reg) {
            $id = $this->designations[$reg] ?? null;
            if ($id) $designationIds[] = $id;
        }
        foreach ($s['dimension'] ?? [] as $dim) {
            $id = $this->designations[$dim] ?? null;
            if ($id) $designationIds[] = $id;
        }
        if ($designationIds) {
            $sense->designations()->attach(array_unique($designationIds));
        }

        // POS
        $posId = $this->posLabels[$s['pos']] ?? null;

        // Definition (EN)
        $defEn = WordSenseDefinition::create([
            'word_sense_id'   => $sense->id,
            'language_id'     => $this->langEn,
            'pos_id'          => $posId,
            'definition_text' => $s['definitions']['en'],
            'formula'         => $s['formula'] ?? null,
            'usage_note'      => $s['usage_note'] ?? null,
            'sort_order'      => 0,
        ]);

        // Definition (zh-TW) if provided
        if (! empty($s['definitions']['zh-TW'])) {
            WordSenseDefinition::create([
                'word_sense_id'   => $sense->id,
                'language_id'     => $this->langZh,
                'pos_id'          => $posId,
                'definition_text' => $s['definitions']['zh-TW'],
                'sort_order'      => 0,
            ]);
        }

        // Examples — translations go in word_sense_example_translations.
        foreach ($s['examples'] ?? [] as $ex) {
            $example = WordSenseExample::create([
                'word_sense_id' => $sense->id,
                'definition_id' => $defEn->id,
                'chinese_text'  => $ex['chinese'],
                'source'        => 'default',
                'is_public'     => true,
                'is_suppressed' => false,
            ]);

            if (! empty($ex['english'])) {
                DB::table('word_sense_example_translations')->insert([
                    'word_sense_example_id' => $example->id,
                    'language_id'           => $this->langEn,
                    'translation_text'      => $ex['english'],
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }
        }

        // Bilingual notes (word_sense_notes)
        $this->syncNotes($sense, $s);
    }

    /**
     * Write per-language notes to word_sense_notes (normalized: one row per note type).
     * Supports bilingual fields (formula_en/_zh, usage_note_en/_zh, learner_traps_en/_zh)
     * with fallback to single fields via CJK detection.
     * Also derives canonical values on word_senses (ZH-preferred, EN fallback).
     */
    private function syncNotes(WordSense $sense, array $s): void
    {
        $now = now();

        // note_type slug → JSONL field mapping
        $noteFieldMap = [
            'formula'       => ['en' => 'formula_en',       'zh' => 'formula_zh',       'single' => 'formula'],
            'usage-note'    => ['en' => 'usage_note_en',    'zh' => 'usage_note_zh',    'single' => 'usage_note'],
            'learner-traps' => ['en' => 'learner_traps_en', 'zh' => 'learner_traps_zh', 'single' => 'learner_traps'],
        ];

        // Resolve note_type IDs (cached on class)
        if (! isset($this->noteTypeIds)) {
            $this->noteTypeIds = \App\Models\NoteType::all()->pluck('id', 'slug')->all();
        }

        $canonicalFormula = null;
        $canonicalUsage   = null;
        $canonicalTraps   = null;

        foreach ($noteFieldMap as $typeSlug => $fields) {
            $typeId = $this->noteTypeIds[$typeSlug] ?? null;
            if (! $typeId) continue;

            // Resolve EN content
            $enContent = trim($s[$fields['en']] ?? '') ?: null;

            // Resolve ZH content
            $zhContent = trim($s[$fields['zh']] ?? '') ?: null;

            // Fallback: single field with CJK detection
            $singleValue = trim($s[$fields['single']] ?? '') ?: null;
            if ($singleValue) {
                if ($this->isCjk($singleValue)) {
                    $zhContent = $zhContent ?? $singleValue;
                } else {
                    $enContent = $enContent ?? $singleValue;
                }
            }

            // Write EN note
            if ($enContent) {
                $this->writeNoteRow($sense->id, $this->langEn, $typeId, $enContent, $now);
            }

            // Write ZH note
            if ($zhContent) {
                $this->writeNoteRow($sense->id, $this->langZh, $typeId, $zhContent, $now);
            }

            // Derive canonical (ZH-preferred, EN fallback)
            $canonical = $zhContent ?? $enContent;
            if ($typeSlug === 'formula')       $canonicalFormula = $canonical;
            if ($typeSlug === 'usage-note')    $canonicalUsage   = $canonical;
            if ($typeSlug === 'learner-traps') $canonicalTraps   = $canonical;
        }

        // Write canonical on word_senses
        $sense->updateQuietly([
            'formula'       => $canonicalFormula,
            'usage_note'    => $canonicalUsage,
            'learner_traps' => $canonicalTraps,
        ]);
    }

    private function writeNoteRow(int $senseId, int $langId, int $typeId, string $content, $now): void
    {
        DB::table('word_sense_notes')->updateOrInsert(
            ['word_sense_id' => $senseId, 'language_id' => $langId, 'note_type_id' => $typeId],
            [
                'content'    => $content,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    /**
     * Detect if a string is predominantly CJK (Chinese/Japanese/Korean).
     */
    private function isCjk(string $text): bool
    {
        $cjkCount = preg_match_all('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}\x{f900}-\x{faff}]/u', $text);
        $totalChars = mb_strlen(preg_replace('/\s+/', '', $text));
        return $totalChars > 0 && ($cjkCount / $totalChars) > 0.3;
    }
}
