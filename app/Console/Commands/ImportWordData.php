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

        // Parse JSONL
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $entries = [];
        $parseErrors = 0;

        foreach ($lines as $i => $line) {
            $entry = json_decode($line, true);
            if (! $entry) {
                $this->error("JSON parse error on line " . ($i + 1));
                $parseErrors++;
                continue;
            }
            $entries[] = $entry;
        }

        if ($parseErrors) {
            $this->error("{$parseErrors} parse errors — aborting.");
            return 1;
        }

        $count = count($entries);
        $this->info("Loaded {$count} entries from {$file}" . ($upsert ? ' [UPSERT MODE]' : ''));

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
                    $this->line("  ↻ {$entry['word']['traditional']} (updated, {$result['senses']} senses)");
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

            // Update word-level fields
            $word->update([
                'traditional' => $w['traditional'],
                'simplified'  => $w['simplified'] ?? $w['traditional'],
                'structure'   => $w['structure'],
                'status'      => $status,
            ]);

            // Delete existing senses and all children to replace cleanly
            $existingSenseIds = $word->senses()->pluck('id')->all();
            if ($existingSenseIds) {
                // Delete children first (definitions, examples, pivots)
                WordSenseDefinition::whereIn('word_sense_id', $existingSenseIds)->delete();
                WordSenseExample::whereIn('word_sense_id', $existingSenseIds)
                    ->where('source', 'default') // Only delete default examples, preserve user examples
                    ->delete();
                DB::table('word_sense_designations')->whereIn('word_sense_id', $existingSenseIds)->delete();
                DB::table('word_sense_domains')->whereIn('word_sense_id', $existingSenseIds)->delete();
                DB::table('word_sense_pos')->whereIn('word_sense_id', $existingSenseIds)->delete();
                DB::table('word_sense_notes')->whereIn('word_sense_id', $existingSenseIds)->delete();
                WordSense::whereIn('id', $existingSenseIds)->delete();
            }

            // Delete old pronunciations and recreate
            WordPronunciation::where('word_object_id', $word->id)->delete();

            $senseCount = 0;
            foreach ($entry['senses'] as $i => $senseData) {
                $this->importSense($word, $senseData, $i, $status);
                $senseCount++;
            }

            return ['action' => 'updated', 'senses' => $senseCount];
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

    private function importSense(WordObject $word, array $s, int $sortOrder, string $status): void
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
        $tocflId        = isset($s['tocfl']) ? ($this->designations[$s['tocfl']] ?? null) : null;
        $hskId          = isset($s['hsk']) ? ($this->designations[$s['hsk']] ?? null) : null;

        // Create sense
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
            'status'           => $status,
            // Enricher attribution: CLI flag wins, then per-sense field from
            // the JSON (written by enrich:skeleton), then null. NEVER
            // hardcoded — that was a silent-attribution bug that mislabeled
            // 388 Chengyan-era senses as Huiming before 2026-04-21.
            'enriched_by'      => $this->option('enriched-by') ?: ($s['enriched_by'] ?? null),
            'enriched_at'      => now(),
        ]);

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

        // POS index
        if ($posId) {
            $sense->posLabels()->attach($posId, ['is_primary' => true]);
        }

        // Examples — translations go in word_sense_example_translations.
        $enLangId = \DB::table('languages')->where('code', 'en')->value('id');
        foreach ($s['examples'] ?? [] as $ex) {
            $example = WordSenseExample::create([
                'word_sense_id' => $sense->id,
                'definition_id' => $defEn->id,
                'chinese_text'  => $ex['chinese'],
                'source'        => 'default',
                'is_public'     => true,
                'is_suppressed' => false,
            ]);

            if (! empty($ex['english']) && $enLangId) {
                \DB::table('word_sense_example_translations')->insert([
                    'word_sense_example_id' => $example->id,
                    'language_id'           => $enLangId,
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
