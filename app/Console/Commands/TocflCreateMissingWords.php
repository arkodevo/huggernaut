<?php

namespace App\Console\Commands;

use App\Models\Designation;
use App\Models\PosLabel;
use App\Models\WordObject;
use App\Models\WordPronunciation;
use App\Models\WordSense;
use App\Models\WordSenseDefinition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan tocfl:create-missing-words [--dry-run]
 *
 * Reads /Users/chuluoyi/Documents/華語/tocfl/tocfl_missing_words.jsonl
 * (produced by build_missing_words.py) and for each entry:
 *
 *   1. Creates word_object (traditional, simplified, smart_id, status=draft)
 *   2. Creates word_pronunciation (Pinyin, numeric-tone, is_primary=true)
 *   3. For each (pos, level) sense:
 *        - Creates word_sense (source=tocfl, status=draft, tocfl_level_id, pronunciation_id)
 *        - Creates word_sense_definition (EN placeholder, pos_id)
 *        - Inserts word_sense_pos index row
 *
 * Skips any traditional that already exists in word_objects (safety guard).
 */
class TocflCreateMissingWords extends Command
{
    protected $signature = 'tocfl:create-missing-words
                            {--jsonl= : Path to tocfl_missing_words.jsonl}
                            {--dry-run : Preview without writing}';

    protected $description = 'Create word_objects + skeleton senses for TOCFL words missing from the DB';

    private const PINYIN_SYSTEM_ID = 1;
    private const PLACEHOLDER_DEF  = '(TOCFL skeleton — definition needed)';

    private array $posIds   = [];
    private array $levelIds = [];
    private int   $enLangId;
    private array $existingWords = [];  // traditional → true

    public function handle(): int
    {
        $jsonlPath = $this->option('jsonl')
            ?? ($_SERVER['HOME'] . '/Documents/華語/tocfl/tocfl_missing_words.jsonl');
        $dryRun = $this->option('dry-run');

        if (! file_exists($jsonlPath)) {
            $this->error("JSONL not found: $jsonlPath");
            return 1;
        }

        $this->loadLookups();

        $lines = file($jsonlPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $total = count($lines);
        $this->info("Entries to process: {$total}");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $created  = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            $trad  = $entry['traditional'];

            // Safety guard — skip if already in DB
            if (isset($this->existingWords[$trad])) {
                $skipped++;
                $bar->advance();
                continue;
            }

            if ($dryRun) {
                $created++;
                $bar->advance();
                continue;
            }

            try {
                DB::transaction(function () use ($entry, &$created) {
                    // 1. word_object
                    $word = WordObject::create([
                        'traditional' => $entry['traditional'],
                        'simplified'  => $entry['simplified'] ?: null,
                        'smart_id'    => $entry['smart_id'],
                        'status'      => 'draft',
                    ]);

                    // 2. word_pronunciation
                    $pron = WordPronunciation::create([
                        'word_object_id'          => $word->id,
                        'pronunciation_system_id' => self::PINYIN_SYSTEM_ID,
                        'pronunciation_text'      => $entry['pinyin'],
                        'is_primary'              => true,
                    ]);

                    // 3. word_senses — one per (pos, level) pair
                    foreach ($entry['senses'] as $s) {
                        $posId   = $this->posIds[$s['pos']]   ?? null;
                        $levelId = $this->levelIds[$s['level']] ?? null;

                        if (! $posId) {
                            continue;
                        }

                        $sense = WordSense::create([
                            'word_object_id'   => $word->id,
                            'pronunciation_id' => $pron->id,
                            'tocfl_level_id'   => $levelId,
                            'status'           => 'draft',
                            'source'           => 'tocfl',
                        ]);

                        // Definition placeholder
                        WordSenseDefinition::create([
                            'word_sense_id'   => $sense->id,
                            'language_id'     => $this->enLangId,
                            'pos_id'          => $posId,
                            'definition_text' => self::PLACEHOLDER_DEF,
                            'sort_order'      => 0,
                        ]);

                        // POS index
                        DB::table('word_sense_pos')->insertOrIgnore([
                            'word_sense_id' => $sense->id,
                            'pos_id'        => $posId,
                            'is_primary'    => true,
                        ]);
                    }

                    $created++;
                });
            } catch (\Throwable $e) {
                $errors[] = "{$entry['traditional']}: " . $e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Action', 'Count'],
            [
                ['Word objects created', $created],
                ['Skipped (already in DB)', $skipped],
                ['Errors', count($errors)],
            ]
        );

        if (! empty($errors)) {
            $this->newLine();
            $this->warn('Errors (first 10):');
            foreach (array_slice($errors, 0, 10) as $e) {
                $this->line("  $e");
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn("DRY RUN — no changes written. Would create {$created} word objects.");
        } else {
            // Bust lexicon caches
            cache()->forget('lexicon_words');
            cache()->forget('lexicon_words_slim');
            $this->info('Lexicon caches cleared.');
        }

        return 0;
    }

    private function loadLookups(): void
    {
        // POS slug → id
        $this->posIds = PosLabel::pluck('id', 'slug')->toArray();

        // Level slug → designation id
        $levelSlugs = ['tocfl-prep', 'tocfl-entry', 'tocfl-basic', 'tocfl-advanced', 'tocfl-high', 'tocfl-fluency'];
        $this->levelIds = Designation::whereIn('slug', $levelSlugs)
            ->pluck('id', 'slug')
            ->toArray();

        // EN language id
        $this->enLangId = DB::table('languages')->where('code', 'en')->value('id') ?? 1;

        // Existing words
        DB::table('word_objects')->select('traditional')->orderBy('id')->each(function ($row) {
            $this->existingWords[$row->traditional] = true;
        });
    }
}
