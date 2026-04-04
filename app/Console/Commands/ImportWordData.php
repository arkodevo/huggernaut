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
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportWordData extends Command
{
    protected $signature = 'words:import
        {file : Path to JSONL file}
        {--dry-run : Validate only, do not import}
        {--upsert : Update existing entries with richer data (default: skip existing)}
        {--status=published : Status for new entries (draft|review|published)}';

    protected $description = 'Import word data from Huiming template JSONL format';

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

                $channel = $s['channel'] ?? '';
                if ($channel && ! isset($this->designations[$channel])) {
                    $issues[] = "{$trad}: unknown channel '{$channel}'";
                }
                $connotation = $s['connotation'] ?? '';
                if ($connotation && ! isset($this->designations[$connotation])) {
                    $issues[] = "{$trad}: unknown connotation '{$connotation}'";
                }
                foreach ($s['domains'] ?? [] as $d) {
                    if (! isset($this->designations[$d])) {
                        $issues[] = "{$trad}: unknown domain '{$d}'";
                    }
                }
                foreach ($s['register'] ?? [] as $r) {
                    if (! isset($this->designations[$r])) {
                        $issues[] = "{$trad}: unknown register '{$r}'";
                    }
                }
                foreach ($s['dimension'] ?? [] as $dim) {
                    if (! isset($this->designations[$dim])) {
                        $issues[] = "{$trad}: unknown dimension '{$dim}' — may need to create it";
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
        $semanticModeId = isset($s['semantic_mode']) ? ($this->designations[$s['semantic_mode']] ?? null) : null;
        $sensitivityId  = isset($s['sensitivity']) ? ($this->designations[$s['sensitivity']] ?? null) : null;
        $tocflId        = isset($s['tocfl']) ? ($this->designations[$s['tocfl']] ?? null) : null;
        $hskId          = isset($s['hsk']) ? ($this->designations[$s['hsk']] ?? null) : null;

        // Create sense
        $sense = WordSense::create([
            'word_object_id'   => $word->id,
            'pronunciation_id' => $pronunciation->id,
            'channel_id'       => $channelId,
            'connotation_id'   => $connotationId,
            'semantic_mode_id' => $semanticModeId,
            'sensitivity_id'   => $sensitivityId,
            'intensity'        => $s['intensity'] ?? null,
            'valency'          => $s['valency'] ?? null,
            'formula'          => $s['formula'] ?? null,
            'usage_note'       => $s['usage_note'] ?? null,
            'learner_traps'    => $s['learner_traps'] ?? null,
            'tocfl_level_id'   => $tocflId,
            'hsk_level_id'     => $hskId,
            'status'           => $status,
        ]);

        // Domains (first = primary, rest = secondary)
        $domains = $s['domains'] ?? [];
        $domainSync = [];
        foreach ($domains as $idx => $slug) {
            $id = $this->designations[$slug] ?? null;
            if ($id) {
                $domainSync[$id] = ['is_primary' => $idx === 0, 'sort_order' => $idx];
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

        // Examples
        foreach ($s['examples'] ?? [] as $ex) {
            WordSenseExample::create([
                'word_sense_id' => $sense->id,
                'definition_id' => $defEn->id,
                'chinese_text'  => $ex['chinese'],
                'english_text'  => $ex['english'] ?? null,
                'source'        => 'default',
                'is_public'     => true,
                'is_suppressed' => false,
            ]);
        }
    }
}
