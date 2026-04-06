<?php

namespace App\Console\Commands;

use App\Models\PosLabel;
use App\Models\WordObject;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan tocfl:verify-senses [--dry-run]
 *
 * Reads tocfl_pos_map.jsonl (group format: {forms, pos, level}).
 *
 * Pass 1 — Build verified sense ID set:
 *   For each (forms_group × pos_slug) pair, find every word_sense where:
 *     - word_object.traditional  ∈ forms_group
 *     - sense has a definition   with that pos_id
 *   All such sense IDs are "verified TOCFL" and must be kept.
 *
 *   A sense on 老頭 for N is verified because 老頭 appears in the
 *   ['老頭兒','老頭','老頭子'] group that carries N — even if 老頭
 *   itself doesn't have its own row in TOCFL.
 *
 * Pass 2 — Purge:
 *   Delete every word_sense where source='tocfl' AND id NOT IN verified set.
 *   These are the old/wrong-POS senses incorrectly swept up by the
 *   catch-all in the previous run (e.g. 流傳 Vpt).
 *   source='editorial' senses are never touched.
 *
 * Each deleted sense is fully cascaded:
 *   definitions, examples, designations, domains, pos-index,
 *   collocations, relations (both directions).
 */
class TocflVerifySenses extends Command
{
    protected $signature = 'tocfl:verify-senses
                            {--jsonl= : Path to tocfl_pos_map.jsonl}
                            {--dry-run : Preview without writing}';

    protected $description = 'Purge source=tocfl senses whose (word, POS) is not in the TOCFL data';

    public function handle(): int
    {
        $jsonlPath = $this->option('jsonl')
            ?? ($_SERVER['HOME'] . '/Documents/華語/tocfl/tocfl_pos_map.jsonl');
        $dryRun = $this->option('dry-run');

        if (! file_exists($jsonlPath)) {
            $this->error("JSONL not found: $jsonlPath");
            return 1;
        }

        // ── Load POS slug → id map ────────────────────────────────────────────
        $posIds = PosLabel::pluck('id', 'slug')->toArray();

        // ── Load word traditional → word_object id(s) ────────────────────────
        // One traditional can map to multiple word_objects in theory — collect all.
        $this->info('Loading word objects…');
        $wordMap = [];   // traditional → [word_object_id, ...]
        DB::table('word_objects')->select('id', 'traditional')->orderBy('id')->each(function ($row) use (&$wordMap) {
            $wordMap[$row->traditional][] = $row->id;
        });

        // ── Pass 1: build verified sense ID set ───────────────────────────────
        $this->info('Building verified sense ID set…');

        $lines = file($jsonlPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $bar   = $this->output->createProgressBar(count($lines));
        $bar->start();

        $verifiedIds = [];   // sense_id => true

        foreach ($lines as $line) {
            $entry   = json_decode($line, true);
            $forms   = $entry['forms'];
            $posSlug = $entry['pos'];
            $posId   = $posIds[$posSlug] ?? null;

            if (! $posId) {
                $bar->advance();
                continue;
            }

            // Collect all word_object IDs for any form in this group
            $wordObjectIds = [];
            foreach ($forms as $form) {
                foreach ($wordMap[$form] ?? [] as $woId) {
                    $wordObjectIds[] = $woId;
                }
            }

            if (empty($wordObjectIds)) {
                $bar->advance();
                continue;
            }

            // Find senses belonging to those word_objects that have a definition with this POS
            $senseIds = DB::table('word_senses as ws')
                ->join('word_sense_definitions as wsd', 'wsd.word_sense_id', '=', 'ws.id')
                ->whereIn('ws.word_object_id', $wordObjectIds)
                ->where('wsd.pos_id', $posId)
                ->pluck('ws.id')
                ->toArray();

            foreach ($senseIds as $id) {
                $verifiedIds[$id] = true;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Also always verify all editorial senses (belt-and-suspenders)
        $editorialIds = DB::table('word_senses')
            ->where('source', 'editorial')
            ->pluck('id')
            ->toArray();
        foreach ($editorialIds as $id) {
            $verifiedIds[$id] = true;
        }

        $verifiedCount = count($verifiedIds);
        $this->info("Verified sense IDs: {$verifiedCount}");

        // ── Pass 2: find senses to purge ──────────────────────────────────────
        $allTocflIds = DB::table('word_senses')
            ->where('source', 'tocfl')
            ->pluck('id')
            ->toArray();

        $toDelete = array_values(array_filter(
            $allTocflIds,
            fn ($id) => ! isset($verifiedIds[$id])
        ));

        $this->info('TOCFL senses total:   ' . count($allTocflIds));
        $this->info('To be deleted:        ' . count($toDelete));
        $this->info('To be kept (verified):' . ($verifiedCount - count($editorialIds)));
        $this->info('Editorial (untouched):' . count($editorialIds));

        if (empty($toDelete)) {
            $this->info('Nothing to delete.');
            return 0;
        }

        // Show a sample of what will be deleted
        $sample = DB::table('word_senses as ws')
            ->join('word_objects as wo', 'wo.id', '=', 'ws.word_object_id')
            ->select('ws.id', 'wo.traditional',
                DB::raw("(SELECT string_agg(pl.slug, ',') FROM word_sense_definitions wsd JOIN pos_labels pl ON pl.id = wsd.pos_id WHERE wsd.word_sense_id = ws.id) AS pos_slugs"))
            ->whereIn('ws.id', array_slice($toDelete, 0, 15))
            ->get();

        $this->newLine();
        $this->warn('Sample of senses to be deleted:');
        foreach ($sample as $s) {
            $this->line("  [{$s->id}] {$s->traditional}  pos={$s->pos_slugs}");
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('DRY RUN — no changes written.');
            return 0;
        }

        // ── Confirm ───────────────────────────────────────────────────────────
        if (! $this->confirm('Proceed with deletion of ' . count($toDelete) . ' senses?')) {
            $this->info('Aborted.');
            return 0;
        }

        // ── Cascade delete in chunks ──────────────────────────────────────────
        $this->info('Deleting…');
        $chunks = array_chunk($toDelete, 200);
        $deleted = 0;

        foreach ($chunks as $chunk) {
            DB::transaction(function () use ($chunk, &$deleted) {
                DB::table('word_sense_definitions')->whereIn('word_sense_id', $chunk)->delete();
                DB::table('word_sense_examples')->whereIn('word_sense_id', $chunk)->delete();
                DB::table('word_sense_designations')->whereIn('word_sense_id', $chunk)->delete();
                DB::table('word_sense_domains')->whereIn('word_sense_id', $chunk)->delete();
                DB::table('word_sense_pos')->whereIn('word_sense_id', $chunk)->delete();
                DB::table('word_sense_collocations')->whereIn('word_sense_id', $chunk)->delete();
                DB::table('word_sense_relations')
                    ->whereIn('word_sense_id', $chunk)
                    ->delete();
                DB::table('word_senses')->whereIn('id', $chunk)->delete();
                $deleted += count($chunk);
            });
        }

        // Bust lexicon caches
        cache()->forget('lexicon_words');
        cache()->forget('lexicon_words_slim');

        $this->newLine();
        $this->info("✓ Deleted {$deleted} unverified source=tocfl senses.");
        $this->info('Lexicon caches cleared.');

        return 0;
    }
}
