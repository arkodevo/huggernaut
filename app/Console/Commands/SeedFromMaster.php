<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// Seed word_senses from master_tocfl_vocabulary.
//
// This is the master-driven scaffold step, separate from the enrichment
// re-import (words:import --upsert). Here we stamp the seed fields
// (tocfl_level_id, pos, pinyin, source='tocfl', alignment='full') onto
// new sense rows with placeholder definitions. 澄言 later fills the
// editorial content and comes back through words:import, which matches
// by (pinyin, pos) and replaces the placeholders with real content.
//
// Typical uses:
//   # What would seeding L1-L4 create?
//   php artisan seed:from-master --level=1 --level=2 --level=3 --level=4 --dry-run
//
//   # Single word smoke-test
//   php artisan seed:from-master --smart-ids=u5bb6 --dry-run
//
//   # Live seed across whole bands
//   php artisan seed:from-master --band=tocfl-novice1
//
// Boundaries:
//   - Does NOT create word_objects. If master references a word not in
//     the DB, that row is reported and skipped — a separate workflow
//     handles new word_object creation.
//   - Does NOT seed a second sense with the same (pinyin, pos) as an
//     existing one. If that key is already in DB, it's skipped.
//   - Does NOT set enriched_by / enriched_at. This is pre-enrichment.
//   - Does NOT seed rows where master's official_pos is NULL (idiomatic
//     phrases — 對不起, 請問). Those need editorial POS assignment.

class SeedFromMaster extends Command
{
    protected $signature = 'seed:from-master
        {--level=* : Master level number(s): 1..7. Repeat flag for multiple.}
        {--band=* : Band slug(s): tocfl-novice1, tocfl-entry, etc. Repeat for multiple.}
        {--smart-ids= : Comma-separated smart_ids to target specific words}
        {--status=draft : Sense status on create (draft|review|published)}
        {--dry-run : Report what would be created without writing}
        {--limit= : Cap total creations (safety for first-test runs)}
    ';

    protected $description = 'Seed skeleton word_senses from master_tocfl_vocabulary (stone → DB)';

    private const PINYIN_SYSTEM_ID = 1;
    private const PLACEHOLDER_DEF = '[skeleton — awaiting enrichment]';

    /** Master POS token → DB pos_labels slug. Hyphenated master variants
     * collapse to the unhyphenated DB slug. */
    private const POS_NORM = [
        'V-sep'    => 'Vsep',
        'Vs-pred'  => 'Vspred',
        'Vp-sep'   => 'Vpsep',
        'Vs-sep'   => 'Vssep',
        'Vs-attr'  => 'Vsattr',
    ];

    /**
     * Master POS → DB POS slugs that are considered equivalent for match purposes.
     *
     * Master uses a coarse N for pronouns (我, 你, 他, 這裡, …) and numerals
     * (一, 二, 百, …). The DB was deliberately refined to Prn/Num for those
     * — not a bug, a taxonomy upgrade. Don't let the seeder re-introduce a
     * duplicate N sense where a Prn/Num sense already covers it.
     *
     * For POS values not listed here, match only against themselves.
     */
    private const POS_MATCH_SET = [
        'N' => ['N', 'Prn', 'Num'],
    ];

    private const LEVEL_TO_BAND = [
        1 => 'tocfl-novice1',
        2 => 'tocfl-novice2',
        3 => 'tocfl-entry',
        4 => 'tocfl-basic',
        5 => 'tocfl-advanced',
        6 => 'tocfl-high',
        7 => 'tocfl-fluency',
    ];

    private int $langEn;
    /** @var array<string,int> pos slug → pos_labels.id */
    private array $posIdBySlug;
    /** @var array<string,int> band slug → designations.id */
    private array $bandIdBySlug;

    public function handle(): int
    {
        $this->langEn = (int) DB::table('languages')->where('code', 'en')->value('id');
        $this->posIdBySlug = DB::table('pos_labels')->pluck('id', 'slug')->all();
        $this->bandIdBySlug = DB::table('designations as d')
            ->join('attributes as a', 'a.id', '=', 'd.attribute_id')
            ->where('a.slug', 'tocfl-level')
            ->pluck('d.id', 'd.slug')
            ->all();

        $rows = $this->selectMasterRows();
        if ($rows === null) return self::FAILURE;

        $this->info('Master rows in scope: ' . count($rows));

        $created = 0;
        $skippedAlreadyExists = 0;
        $skippedNoWordObject = 0;
        $skippedNullPos = 0;
        $skippedUnknownPos = 0;
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $dryRun = (bool) $this->option('dry-run');
        $status = $this->option('status');

        $missingWords = [];
        $nullPosRows = [];
        $unknownPosRows = [];
        $createdRows = [];
        $toneNeighborRows = [];  // created despite tone-neighbor existing — for audit

        if (! $dryRun) DB::beginTransaction();

        try {
            foreach ($rows as $m) {
                if ($limit !== null && $created >= $limit) {
                    $this->warn("--limit={$limit} reached; stopping.");
                    break;
                }

                // Null POS → needs editorial, not master-driven seeding
                if (! $m->official_pos) {
                    $skippedNullPos++;
                    $nullPosRows[] = $m;
                    continue;
                }

                $posSlug = self::POS_NORM[$m->official_pos] ?? $m->official_pos;
                $posId = $this->posIdBySlug[$posSlug] ?? null;
                if (! $posId) {
                    $skippedUnknownPos++;
                    $unknownPosRows[] = ['word' => $m->traditional, 'pos' => $m->official_pos];
                    continue;
                }

                $bandSlug = self::LEVEL_TO_BAND[$m->level_number] ?? null;
                $bandId   = $bandSlug ? ($this->bandIdBySlug[$bandSlug] ?? null) : null;

                // Master sometimes stores alternates as "pinyin1/pinyin2"
                // (e.g. 姊姊 "jie3jie/jie3"). If we create a new sense, we
                // pick the first form — the seedSense path gets a clean
                // string. Matching against existing DB senses still tries
                // every alternate via senseExists.
                $createPinyin = explode('/', (string) $m->pinyin)[0] ?? '';

                $word = DB::table('word_objects')->where('traditional', $m->traditional)->first();
                if (! $word) {
                    $skippedNoWordObject++;
                    $missingWords[] = $m->traditional;
                    continue;
                }

                // Strict pinyin match with POS equivalence. Tone-neighbor
                // differences go through to create, but flagged for review.
                $probe = $this->senseProbe($word->id, $m->pinyin, $posSlug);
                if ($probe['match']) {
                    $skippedAlreadyExists++;
                    continue;
                }

                // New sense needed. Record for reporting.
                $createdRows[] = [
                    'word'    => $m->traditional,
                    'smart_id'=> $word->smart_id,
                    'pinyin'  => $createPinyin,
                    'master_pinyin' => $m->pinyin,
                    'pos'     => $posSlug,
                    'band'    => $bandSlug,
                    'level'   => $m->level_number,
                ];

                // If a same-POS tone-neighbor already exists on this word,
                // this is either a convention drift (先生 xian1sheng1 vs DB
                // xian1sheng5) or a genuine tone-distinguishing sense (空
                // kong1 vs kong4). Flag for review — 澄言 sees it via
                // _sibling_senses during enrichment as a second safety net.
                if ($probe['tone_neighbor']) {
                    $toneNeighborRows[] = [
                        'word'   => $m->traditional,
                        'seed'   => "{$createPinyin}|{$posSlug}",
                        'neighbors' => $probe['neighbor_pinyins'],
                        'band'   => $bandSlug,
                    ];
                }

                if (! $dryRun) {
                    $this->seedSense($word->id, $createPinyin, $posId, $bandId, $status);
                }
                $created++;
            }

            if (! $dryRun) DB::commit();
        } catch (\Throwable $e) {
            if (! $dryRun) DB::rollBack();
            $this->error('Seeder failed: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }

        // Report
        $this->line('');
        $this->info('=== Seeding report' . ($dryRun ? ' (DRY RUN)' : '') . ' ===');
        $this->line(sprintf('  created:                 %d', $created));
        $this->line(sprintf('  skipped (already exists): %d', $skippedAlreadyExists));
        $this->line(sprintf('  skipped (no word_object): %d', $skippedNoWordObject));
        $this->line(sprintf('  skipped (null POS):       %d', $skippedNullPos));
        $this->line(sprintf('  skipped (unknown POS):    %d', $skippedUnknownPos));

        if (! empty($createdRows)) {
            $this->line('');
            $this->line($dryRun ? 'Would create:' : 'Created:');
            foreach (array_slice($createdRows, 0, 100) as $r) {
                $this->line(sprintf('  %s (%s) %s|%s → %s', $r['word'], $r['smart_id'], $r['pinyin'], $r['pos'], $r['band']));
            }
            if (count($createdRows) > 100) {
                $this->line(sprintf('  … and %d more', count($createdRows) - 100));
            }
        }

        if (! empty($missingWords)) {
            $this->line('');
            $this->warn('word_objects missing for ' . count($missingWords) . ' master rows (create separately):');
            foreach (array_slice(array_unique($missingWords), 0, 40) as $w) {
                $this->line('  ' . $w);
            }
        }

        if (! empty($nullPosRows)) {
            $this->line('');
            $this->warn('Master rows with null POS (need editorial POS): ' . count($nullPosRows));
            foreach (array_slice($nullPosRows, 0, 20) as $r) {
                $this->line(sprintf('  L%d %s (%s)', $r->level_number, $r->traditional, $r->pinyin ?: ''));
            }
        }

        if (! empty($unknownPosRows)) {
            $this->line('');
            $this->warn('Master rows with POS not in pos_labels: ' . count($unknownPosRows));
            foreach (array_slice($unknownPosRows, 0, 20) as $r) {
                $this->line(sprintf('  %s → pos=%s', $r['word'], $r['pos']));
            }
        }

        if (! empty($toneNeighborRows)) {
            $this->line('');
            $this->warn('Tone-neighbor collisions (created skeleton, but a same-POS '
                . 'tone-variant sense already exists on this word — could be '
                . 'convention drift OR a genuine tone-distinguishing sense):');
            foreach ($toneNeighborRows as $r) {
                $this->line(sprintf(
                    '  %s: seeded %s at %s — neighbor(s): %s',
                    $r['word'], $r['seed'], $r['band'] ?? '-', implode(', ', $r['neighbors'])
                ));
            }
            $this->line('  → review during enrichment via _sibling_senses; merge duplicates editorially.');
        }

        return self::SUCCESS;
    }

    /**
     * Pull master rows matching the filter flags.
     *
     * @return array<int,object>|null
     */
    private function selectMasterRows(): ?array
    {
        $levels = array_map('intval', $this->option('level'));
        $bands  = $this->option('band');
        $smartIds = $this->option('smart-ids');

        // smart-ids: resolve to traditional chars via word_objects
        $tradFromSmartIds = [];
        if ($smartIds) {
            $ids = array_map('trim', explode(',', $smartIds));
            $tradFromSmartIds = DB::table('word_objects')
                ->whereIn('smart_id', $ids)
                ->pluck('traditional')
                ->all();
            if (empty($tradFromSmartIds)) {
                $this->error('No word_objects matched the given --smart-ids.');
                return null;
            }
        }

        // Translate band slugs to level numbers
        foreach ($bands as $band) {
            $level = array_search($band, self::LEVEL_TO_BAND, true);
            if ($level === false) {
                $this->error("Unknown --band slug: {$band}");
                return null;
            }
            $levels[] = $level;
        }
        $levels = array_values(array_unique($levels));

        if (empty($levels) && empty($tradFromSmartIds)) {
            $this->error('Specify at least one --level, --band, or --smart-ids');
            return null;
        }

        $q = DB::table('master_tocfl_vocabulary');
        if (! empty($levels)) $q->whereIn('level_number', $levels);
        if (! empty($tradFromSmartIds)) $q->whereIn('traditional', $tradFromSmartIds);

        return $q->orderBy('level_number')->orderBy('row_seq')->get()->all();
    }

    /**
     * Sense-match probe.
     *
     * STRICT: exact pinyin (full numeric tones) + POS-equivalence aware
     * (master N matches DB N/Prn/Num for pronouns/numerals). Handles
     * master's "pinyin1/pinyin2" alternates.
     *
     * Returns ['match' => bool, 'tone_neighbor' => bool, 'neighbor_pinyins' => string[]].
     *   - match=true  → exact hit; skip seeding.
     *   - match=false, tone_neighbor=true → no exact hit but a same-POS
     *     sense exists with a tone-stripped-identical pinyin. Likely a
     *     convention drift (master citation tones vs DB neutralized 5)
     *     OR a genuine tone-distinguishing sense (kong1 vs kong4). The
     *     caller WARNs and creates the skeleton anyway; the duplicate is
     *     then visible to 澄言 via _sibling_senses during enrichment, and
     *     to the auditor via this seeder's warning log.
     */
    private function senseProbe(int $wordObjectId, ?string $masterPinyin, string $masterPosSlug): array
    {
        $equivalentPos = self::POS_MATCH_SET[$masterPosSlug] ?? [$masterPosSlug];
        $posIds = array_values(array_filter(array_map(
            fn ($slug) => $this->posIdBySlug[$slug] ?? null,
            $equivalentPos
        )));
        if (empty($posIds)) {
            return ['match' => false, 'tone_neighbor' => false, 'neighbor_pinyins' => []];
        }

        $existingPinyins = DB::table('word_senses as ws')
            ->join('word_pronunciations as wp', 'wp.id', '=', 'ws.pronunciation_id')
            ->join('word_sense_definitions as wsd', function ($j) use ($posIds) {
                $j->on('wsd.word_sense_id', '=', 'ws.id')
                  ->where('wsd.language_id', '=', $this->langEn)
                  ->whereIn('wsd.pos_id', $posIds);
            })
            ->where('ws.word_object_id', $wordObjectId)
            ->pluck('wp.pronunciation_text')
            ->all();

        if (empty($existingPinyins)) {
            return ['match' => false, 'tone_neighbor' => false, 'neighbor_pinyins' => []];
        }

        $masterAlternates = array_filter(array_map('trim', explode('/', (string) $masterPinyin)));

        // Exact match first.
        foreach ($masterAlternates as $mp) {
            if (in_array($mp, $existingPinyins, true)) {
                return ['match' => true, 'tone_neighbor' => false, 'neighbor_pinyins' => []];
            }
        }

        // No exact hit — check for a tone-stripped neighbor.
        $masterNorm = array_map(fn ($p) => $this->normalizePinyin($p), $masterAlternates);
        $neighbors = [];
        foreach ($existingPinyins as $ep) {
            if (in_array($this->normalizePinyin($ep), $masterNorm, true)) {
                $neighbors[] = $ep;
            }
        }

        return [
            'match'            => false,
            'tone_neighbor'    => ! empty($neighbors),
            'neighbor_pinyins' => array_values(array_unique($neighbors)),
        ];
    }

    /**
     * Tone-stripped pinyin key. Used only as a second-pass probe for the
     * "convention drift vs genuine tone distinction" neighbor warning —
     * never as a match-equivalence decision.
     */
    private function normalizePinyin(?string $p): string
    {
        if ($p === null || $p === '') return '';
        return strtolower(preg_replace('/[0-9]/', '', $p));
    }

    /**
     * Create the sense row, its pronunciation (or reuse), and a placeholder
     * EN definition carrying the POS. All from master — source='tocfl',
     * alignment='full'.
     */
    private function seedSense(int $wordObjectId, string $pinyin, int $posId, ?int $bandId, string $status): void
    {
        $now = now();

        $pronunciationId = DB::table('word_pronunciations')
            ->where('word_object_id', $wordObjectId)
            ->where('pronunciation_system_id', self::PINYIN_SYSTEM_ID)
            ->where('pronunciation_text', $pinyin)
            ->value('id');

        if (! $pronunciationId) {
            $pronunciationId = DB::table('word_pronunciations')->insertGetId([
                'word_object_id'          => $wordObjectId,
                'pronunciation_system_id' => self::PINYIN_SYSTEM_ID,
                'pronunciation_text'      => $pinyin,
                'is_primary'              => false,
                'has_audio'               => '{}',
                'created_at'              => $now,
                'updated_at'              => $now,
            ]);
        }

        $senseId = DB::table('word_senses')->insertGetId([
            'word_object_id'  => $wordObjectId,
            'pronunciation_id'=> $pronunciationId,
            'tocfl_level_id'  => $bandId,
            'hsk_level_id'    => null,
            'source'          => 'tocfl',
            'alignment'       => 'full',
            'status'          => $status,
            'enriched_by'     => null,
            'enriched_at'     => null,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        DB::table('word_sense_definitions')->insert([
            'word_sense_id'   => $senseId,
            'language_id'     => $this->langEn,
            'pos_id'          => $posId,
            'definition_text' => self::PLACEHOLDER_DEF,
            'sort_order'      => 0,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);
    }
}
