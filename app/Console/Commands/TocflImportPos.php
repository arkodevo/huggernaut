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
use Illuminate\Support\Facades\Language;

/**
 * php artisan tocfl:import-pos
 *
 * Reads /Users/chuluoyi/Documents/華語/tocfl/tocfl_pos_map.jsonl
 * (produced by extract_tocfl_pos.py) and for every TOCFL (word, POS, level) tuple:
 *
 *   • If a matching sense already exists in DB (same word_object + definition POS)
 *     → mark source='tocfl', set tocfl_level_id (only if not already set)
 *
 *   • If no matching sense exists for that POS
 *     → create a skeleton sense (source='tocfl', status='draft', level set)
 *       with a skeleton EN definition placeholder
 *
 * A word appearing in multiple TOCFL levels (e.g. 幫 as M in L5, V in L3) gets
 * the lower-level designation on its sense (first occurrence in JSONL wins for
 * source marking; skeletons created at the level they appear).
 *
 * Run with --dry-run to preview without writing.
 */
class TocflImportPos extends Command
{
    protected $signature = 'tocfl:import-pos
                            {--jsonl= : Path to tocfl_pos_map.jsonl (default: ~/Documents/華語/tocfl/tocfl_pos_map.jsonl)}
                            {--dry-run : Preview changes without writing}';

    protected $description = 'Mark existing senses as TOCFL-sourced and create skeleton senses for missing POS';

    // Level slug → designation id
    private array $levelIds = [];

    // POS slug → pos_label id
    private array $posIds = [];

    // EN language id
    private int $enLangId;

    // Stats
    private int $marked   = 0;
    private int $skipped  = 0;
    private int $created  = 0;
    private int $noWord   = 0;

    public function handle(): int
    {
        $jsonl    = $this->option('jsonl')
            ?? ($_SERVER['HOME'] . '/Documents/華語/tocfl/tocfl_pos_map.jsonl');
        $dryRun   = $this->option('dry-run');

        if (! file_exists($jsonl)) {
            $this->error("JSONL not found: $jsonl");
            return 1;
        }

        $this->loadLookups();

        $lines = file($jsonl, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $bar   = $this->output->createProgressBar(count($lines));
        $bar->start();

        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            $this->processWord($entry, $dryRun);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Action', 'Count'],
            [
                ['Senses marked source=tocfl',    $this->marked],
                ['Senses already had source set (skipped)', $this->skipped],
                ['Skeleton senses created',        $this->created],
                ['Words not in DB (skipped)',       $this->noWord],
            ]
        );

        if ($dryRun) {
            $this->warn('DRY RUN — no changes written.');
        }

        return 0;
    }

    // ── Core logic ────────────────────────────────────────────────────────────

    private function processWord(array $entry, bool $dryRun): void
    {
        $trad  = $entry['traditional'];
        $senseList = $entry['senses']; // [{pos, level}, ...]

        $word = WordObject::where('traditional', $trad)->first();
        if (! $word) {
            $this->noWord++;
            return;
        }

        // Load all existing senses with their definitions' POS ids
        $existingSenses = WordSense::where('word_object_id', $word->id)
            ->with('definitions')
            ->get();

        // Build a map: pos_id → [senses that have a definition with this pos]
        $posSenseMap = [];
        foreach ($existingSenses as $sense) {
            foreach ($sense->definitions as $def) {
                if ($def->pos_id) {
                    $posSenseMap[$def->pos_id][] = $sense;
                }
            }
        }

        foreach ($senseList as $tocflEntry) {
            $posSlug   = $tocflEntry['pos'];
            $levelSlug = $tocflEntry['level'];

            $posId   = $this->posIds[$posSlug]   ?? null;
            $levelId = $this->levelIds[$levelSlug] ?? null;

            if (! $posId) {
                $this->warn("Unknown POS slug: $posSlug");
                continue;
            }

            if (isset($posSenseMap[$posId])) {
                // One or more senses already cover this POS — mark them tocfl
                foreach ($posSenseMap[$posId] as $sense) {
                    if ($sense->source !== null) {
                        $this->skipped++;
                        continue; // already tagged (editorial or tocfl) — leave alone
                    }
                    if (! $dryRun) {
                        $sense->source = 'tocfl';
                        if ($levelId && ! $sense->tocfl_level_id) {
                            $sense->tocfl_level_id = $levelId;
                        }
                        $sense->save();
                    }
                    $this->marked++;
                }
            } else {
                // No matching sense — create skeleton
                if (! $dryRun) {
                    $this->createSkeletonSense($word, $posId, $levelId);
                }
                $this->created++;
            }
        }
    }

    private function createSkeletonSense(WordObject $word, int $posId, ?int $levelId): void
    {
        // Use the first available pronunciation for this word
        $pronId = WordPronunciation::where('word_object_id', $word->id)
            ->orderBy('is_primary', 'desc')
            ->value('id');

        if (! $pronId) {
            // No pronunciation yet — still create the sense, pronunciation_id nullable is not ideal
            // but we can at least store it. Skip if required.
            return;
        }

        $sense = WordSense::create([
            'word_object_id' => $word->id,
            'pronunciation_id' => $pronId,
            'tocfl_level_id' => $levelId,
            'status'   => 'draft',
            'source'   => 'tocfl',
        ]);

        // Skeleton definition — placeholder text signals it needs filling
        WordSenseDefinition::create([
            'word_sense_id'   => $sense->id,
            'language_id'     => $this->enLangId,
            'pos_id'          => $posId,
            'definition_text' => '(TOCFL skeleton — definition needed)',
            'sort_order'      => 0,
        ]);

        // Also update the word_sense_pos index
        DB::table('word_sense_pos')->insertOrIgnore([
            'word_sense_id' => $sense->id,
            'pos_id'        => $posId,
            'is_primary'    => true,
        ]);
    }

    // ── Lookups ───────────────────────────────────────────────────────────────

    private function loadLookups(): void
    {
        // Level slugs we care about
        $levelSlugs = [
            'tocfl-prep', 'tocfl-entry', 'tocfl-basic',
            'tocfl-advanced', 'tocfl-high', 'tocfl-fluency',
        ];
        $levels = Designation::whereIn('slug', $levelSlugs)->pluck('id', 'slug');
        foreach ($levelSlugs as $slug) {
            if (isset($levels[$slug])) {
                $this->levelIds[$slug] = $levels[$slug];
            } else {
                $this->warn("Level designation not found: $slug");
            }
        }

        // POS labels
        $pos = PosLabel::pluck('id', 'slug');
        $this->posIds = $pos->toArray();

        // EN language
        $this->enLangId = DB::table('languages')->where('code', 'en')->value('id') ?? 1;
    }
}
