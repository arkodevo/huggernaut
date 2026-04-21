<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// Generate a batch skeleton JSON for 澄言 to enrich.
//
// Two formats:
//
// Format A — words already in the DB. Query the current state and write
// it out as a skeleton for further enrichment. Use for TOCFL/HSK band
// batches pre-seeded by the importer.
//
//   php artisan enrich:skeleton --format=a --batch-name=L5-batch-06 \
//        --filter=tocfl-fluency --unenriched-only
//
// Format B — words not yet in the DB. Read a CSV and produce minimal
// entries for 澄言 to fully enrich (and for the importer to create from
// scratch). Use for thematic batches or editorial additions.
//
//   php artisan enrich:skeleton --format=b --batch-name=theme-emotion-01 \
//        --csv=~/Documents/華語/source/emotion-words.csv
//
// Output: ~/Downloads/cowork/<batch-name>-rev0.json
// Convention: always named rev0 (澄言 produces rev1, rev2, …).
//
// Philosophy (sanrenxing):
// - Format A preserves DB state. Never re-derive what's already set.
// - Format B POS/pinyin hints are ADVISORY. 澄言 is free to split senses
//   and reassign readings — our role is to seed the conversation, not
//   dictate the outcome. POS disagreements are how all of us sharpen.

class EnrichSkeleton extends Command
{
    protected $signature = 'enrich:skeleton
        {--format= : a (from DB) or b (from CSV)}
        {--batch-name= : Identifier for the batch (e.g. L5-batch-06). Used for filename and logs.}
        {--filter= : Format A — TOCFL/HSK level slug (e.g. tocfl-fluency, hsk-3)}
        {--unenriched-only : Format A — only include senses where enriched_by IS NULL}
        {--smart-ids= : Format A — comma-separated smart_ids to override filter}
        {--csv= : Format B — path to input CSV (columns: traditional,simplified?,pinyin?,pos?,tocfl?,hsk?,note?)}
        {--batch-size=27 : Words per output file (default 27)}
        {--cowork-dir= : Override default ~/Downloads/cowork/}
        {--enriched-by=chengyan : Enricher attribution stamped on each skeleton sense. Default chengyan — our cowork enricher (2026-04-15 onward). Override only if someone else is filling this batch.}
        {--dry-run : Show what would be produced without writing files}
    ';

    protected $description = 'Generate a batch skeleton JSON for 澄言 enrichment (Format A: DB, Format B: CSV)';

    private int $langEn;
    private int $langZh;
    /** @var array<int,string> designation_id → slug */
    private array $designationSlugById = [];
    /** @var array<int,string> designation_id → attribute_slug */
    private array $designationAttrById = [];
    /** @var array<int,string> pos_id → slug */
    private array $posSlugById = [];
    /** @var array<int,string> note_type_id → slug */
    private array $noteTypeSlugById = [];
    /** @var array<int,string> relation_type_id → slug */
    private array $relationSlugById = [];

    public function handle(): int
    {
        $format = $this->option('format');
        $batchName = $this->option('batch-name');

        if (! $format || ! in_array($format, ['a', 'b'], true)) {
            $this->error('--format is required and must be "a" or "b"');
            return self::FAILURE;
        }

        if (! $batchName) {
            $this->error('--batch-name is required (e.g. L5-batch-06)');
            return self::FAILURE;
        }

        $this->loadLookupTables();

        return match ($format) {
            'a' => $this->handleFormatA($batchName),
            'b' => $this->handleFormatB($batchName),
        };
    }

    // ── Lookup tables shared by both formats ─────────────────────────

    private function loadLookupTables(): void
    {
        $this->langEn = (int) DB::table('languages')->where('code', 'en')->value('id');
        $this->langZh = (int) DB::table('languages')->where('code', 'zh-TW')->value('id');

        $this->designationSlugById = DB::table('designations')->pluck('slug', 'id')->all();

        $this->designationAttrById = DB::table('designations as d')
            ->join('attributes as a', 'a.id', '=', 'd.attribute_id')
            ->pluck('a.slug', 'd.id')
            ->all();

        $this->posSlugById = DB::table('pos_labels')->pluck('slug', 'id')->all();
        $this->noteTypeSlugById = DB::table('note_types')->pluck('slug', 'id')->all();
        $this->relationSlugById = DB::table('sense_relation_types')->pluck('slug', 'id')->all();
    }

    // ── Format A: words already in DB ────────────────────────────────

    private function handleFormatA(string $batchName): int
    {
        $filter = $this->option('filter');
        $unenrichedOnly = (bool) $this->option('unenriched-only');
        $smartIdsOpt = $this->option('smart-ids');

        // Assemble sense query
        $query = DB::table('word_senses as ws')
            ->join('word_objects as wo', 'wo.id', '=', 'ws.word_object_id')
            ->leftJoin('word_pronunciations as wp', 'wp.id', '=', 'ws.pronunciation_id')
            ->select(
                'ws.id as sense_id', 'ws.word_object_id', 'ws.source', 'ws.alignment',
                'ws.channel_id', 'ws.connotation_id', 'ws.sensitivity_id',
                'ws.intensity', 'ws.valency',
                'ws.formula as canonical_formula',
                'ws.usage_note as canonical_usage_note',
                'ws.learner_traps as canonical_learner_traps',
                'ws.tocfl_level_id', 'ws.hsk_level_id',
                'ws.enriched_by',
                'wp.pronunciation_text as pinyin',
                'wo.smart_id', 'wo.traditional', 'wo.simplified', 'wo.structure'
            );

        if ($smartIdsOpt) {
            $smartIds = array_map('trim', explode(',', $smartIdsOpt));
            $query->whereIn('wo.smart_id', $smartIds);
        } elseif ($filter) {
            $levelId = DB::table('designations')->where('slug', $filter)->value('id');
            if (! $levelId) {
                $this->error("Unknown --filter slug: {$filter}");
                return self::FAILURE;
            }
            // Match either tocfl_level_id or hsk_level_id — the slug tells us which.
            $col = str_starts_with($filter, 'hsk-') ? 'ws.hsk_level_id' : 'ws.tocfl_level_id';
            $query->where($col, $levelId);
        } else {
            $this->error('Format A requires --filter or --smart-ids');
            return self::FAILURE;
        }

        if ($unenrichedOnly) {
            $query->whereNull('ws.enriched_by');
        }

        $query->orderBy('wp.pronunciation_text')
              ->orderBy('wo.traditional')
              ->orderBy('ws.id');

        $senseRows = $query->get();
        $this->info('Fetched ' . $senseRows->count() . ' senses.');

        if ($senseRows->isEmpty()) {
            $this->warn('No senses matched the filter. Nothing to do.');
            return self::SUCCESS;
        }

        $shaped = $this->shapeFormatA($senseRows);
        return $this->writeBatches($shaped, $batchName);
    }

    /**
     * Preload side-tables and shape rows into the v2.3 skeleton format.
     *
     * @param  \Illuminate\Support\Collection<int,object>  $senseRows
     * @return array<int,array<string,mixed>>  word entries grouped by word_object_id
     */
    private function shapeFormatA($senseRows): array
    {
        $senseIds = $senseRows->pluck('sense_id')->map(fn ($v) => (int) $v)->all();

        $posBySense = [];
        foreach (DB::table('word_sense_pos')
                    ->whereIn('word_sense_id', $senseIds)
                    ->orderBy('word_sense_id')
                    ->orderByDesc('is_primary')
                    ->get() as $row) {
            if (! isset($posBySense[$row->word_sense_id])) {
                $posBySense[$row->word_sense_id] = $this->posSlugById[$row->pos_id] ?? null;
            }
        }

        $defsBySense = [];
        foreach (DB::table('word_sense_definitions')
                    ->whereIn('word_sense_id', $senseIds)
                    ->orderBy('sort_order')
                    ->get() as $row) {
            $key = $row->language_id === $this->langEn ? 'en'
                 : ($row->language_id === $this->langZh ? 'zh-TW' : null);
            if ($key && ! isset($defsBySense[$row->word_sense_id][$key])) {
                $defsBySense[$row->word_sense_id][$key] = $row->definition_text;
            }
        }

        $domainsBySense = [];
        foreach (DB::table('word_sense_domains')
                    ->whereIn('word_sense_id', $senseIds)
                    ->orderBy('sort_order')
                    ->get() as $row) {
            $slug = $this->designationSlugById[$row->designation_id] ?? null;
            if ($slug) $domainsBySense[$row->word_sense_id][] = $slug;
        }

        $registerBySense = [];
        $dimensionBySense = [];
        foreach (DB::table('word_sense_designations')
                    ->whereIn('word_sense_id', $senseIds)
                    ->get() as $row) {
            $attr = $this->designationAttrById[$row->designation_id] ?? null;
            $slug = $this->designationSlugById[$row->designation_id] ?? null;
            if (! $slug) continue;
            if ($attr === 'register')  $registerBySense[$row->word_sense_id][] = $slug;
            if ($attr === 'dimension') $dimensionBySense[$row->word_sense_id][] = $slug;
        }

        $notesBySense = [];
        foreach (DB::table('word_sense_notes')
                    ->whereIn('word_sense_id', $senseIds)
                    ->get() as $row) {
            $typeSlug = $this->noteTypeSlugById[$row->note_type_id] ?? null;
            if (! $typeSlug) continue;
            $langKey = $row->language_id === $this->langEn ? 'en'
                    : ($row->language_id === $this->langZh ? 'zh' : null);
            if ($langKey) {
                $notesBySense[$row->word_sense_id][$langKey][$typeSlug] = $row->content;
            }
        }

        $relationsBySense = [];
        foreach (DB::table('word_sense_relations')
                    ->whereIn('word_sense_id', $senseIds)
                    ->get() as $row) {
            $typeSlug = $this->relationSlugById[$row->relation_type_id] ?? null;
            if (! $typeSlug) continue;
            $relationsBySense[$row->word_sense_id][$typeSlug][] = $row->related_word_text;
        }

        $collocationsBySense = [];
        foreach (DB::table('word_sense_collocations')
                    ->whereIn('word_sense_id', $senseIds)
                    ->orderBy('collocation_text')
                    ->get() as $row) {
            $collocationsBySense[$row->word_sense_id][] = $row->collocation_text;
        }

        $examplesBySense = [];
        foreach (DB::table('word_sense_examples as e')
                    ->leftJoin('word_sense_example_translations as t', function ($j) {
                        $j->on('t.word_sense_example_id', '=', 'e.id')
                          ->where('t.language_id', '=', $this->langEn);
                    })
                    ->whereIn('e.word_sense_id', $senseIds)
                    ->where('e.is_suppressed', false)
                    ->whereNull('e.user_id')
                    ->orderBy('e.id')
                    ->select('e.word_sense_id', 'e.chinese_text', 't.translation_text as english')
                    ->get() as $row) {
            $examplesBySense[$row->word_sense_id][] = [
                'chinese' => $row->chinese_text,
                'english' => $row->english,
            ];
        }

        // Group rows into word entries
        $byWord = [];
        foreach ($senseRows as $row) {
            $woId = (int) $row->word_object_id;
            if (! isset($byWord[$woId])) {
                $byWord[$woId] = [
                    'word' => [
                        'smart_id'    => $row->smart_id,
                        'traditional' => $row->traditional,
                        'simplified'  => $row->simplified,
                        'structure'   => $row->structure ?: 'single',
                    ],
                    'senses' => [],
                ];
            }

            $sid = (int) $row->sense_id;
            $noteEn = $notesBySense[$sid]['en'] ?? [];
            $noteZh = $notesBySense[$sid]['zh'] ?? [];

            $tocflSlug = $row->tocfl_level_id ? ($this->designationSlugById[$row->tocfl_level_id] ?? null) : null;
            $hskSlug   = $row->hsk_level_id   ? ($this->designationSlugById[$row->hsk_level_id]   ?? null) : null;

            $byWord[$woId]['senses'][] = [
                'pinyin'        => $row->pinyin,
                'pos'           => $posBySense[$sid] ?? null,
                'source'        => $row->source ?: 'tocfl',
                'alignment'     => $row->alignment ?: 'partial',
                'enriched_by'   => $this->option('enriched-by') ?: 'chengyan',
                'definitions'   => [
                    'en'    => $defsBySense[$sid]['en']   ?? null,
                    'zh-TW' => $defsBySense[$sid]['zh-TW'] ?? null,
                ],
                'domains'       => $domainsBySense[$sid] ?? [],
                'register'      => $registerBySense[$sid] ?? [],
                'connotation'   => $row->connotation_id ? ($this->designationSlugById[$row->connotation_id] ?? null) : null,
                'channel'       => $row->channel_id     ? ($this->designationSlugById[$row->channel_id]     ?? null) : null,
                'dimension'     => $dimensionBySense[$sid] ?? [],
                'intensity'     => $row->intensity !== null ? (int) $row->intensity : null,
                'sensitivity'   => $row->sensitivity_id ? ($this->designationSlugById[$row->sensitivity_id] ?? null) : null,
                'valency'       => $row->valency !== null ? (int) $row->valency : null,
                'tocfl'         => $tocflSlug,
                'hsk'           => $hskSlug,
                'formula_en'       => $noteEn['formula']       ?? null,
                'formula_zh'       => $noteZh['formula']       ?? null,
                'usage_note_en'    => $noteEn['usage-note']    ?? null,
                'usage_note_zh'    => $noteZh['usage-note']    ?? null,
                'learner_traps_en' => $noteEn['learner-traps'] ?? null,
                'learner_traps_zh' => $noteZh['learner-traps'] ?? null,
                'relations'     => [
                    'synonym_close'   => $relationsBySense[$sid]['synonym_close']   ?? [],
                    'synonym_related' => $relationsBySense[$sid]['synonym_related'] ?? [],
                    'antonym'         => $relationsBySense[$sid]['antonym']         ?? [],
                    'contrast'        => $relationsBySense[$sid]['contrast']        ?? [],
                ],
                'collocations'  => $collocationsBySense[$sid] ?? [],
                'examples'      => $examplesBySense[$sid] ?? [],
                '_db_sense_id'       => $sid,
                '_db_word_object_id' => $woId,
            ];
        }

        return array_values($byWord);
    }

    // ── Format B: words not yet in DB (CSV input) ────────────────────

    private function handleFormatB(string $batchName): int
    {
        $csvPath = $this->option('csv');
        if (! $csvPath) {
            $this->error('Format B requires --csv=path/to/words.csv');
            return self::FAILURE;
        }

        // Expand ~ in path
        $csvPath = str_replace('~', $_SERVER['HOME'] ?? '', $csvPath);

        if (! is_file($csvPath)) {
            $this->error("CSV not found: {$csvPath}");
            return self::FAILURE;
        }

        $rows = $this->readCsv($csvPath);
        if (empty($rows)) {
            $this->warn('CSV is empty.');
            return self::SUCCESS;
        }

        $this->info('Read ' . count($rows) . ' rows from CSV.');

        // Duplicate detection — flag + pause per Luoyi's call
        $duplicates = $this->detectDuplicates($rows);
        if (! empty($duplicates)) {
            $this->warn('The following words already exist in the DB:');
            foreach ($duplicates as $dup) {
                $this->line("  - {$dup['traditional']} (smart_id={$dup['smart_id']}, existing_senses={$dup['sense_count']})");
            }
            $this->line('');
            $this->line('For each duplicate, decide: skip it (remove from CSV), or use --format=a --smart-ids=... to enrich the existing entries instead.');
            $this->line('Aborting so you can resolve. Re-run when the CSV is clean.');
            return self::FAILURE;
        }

        $shaped = $this->shapeFormatB($rows);
        return $this->writeBatches($shaped, $batchName);
    }

    /**
     * Read CSV. Expected columns (all optional except traditional):
     *   traditional, simplified, pinyin, pos, tocfl, hsk, note
     *
     * @return array<int,array<string,string>>
     */
    private function readCsv(string $path): array
    {
        $fh = fopen($path, 'r');
        if (! $fh) return [];

        $header = fgetcsv($fh);
        if (! $header) {
            fclose($fh);
            return [];
        }
        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        $rows = [];
        while (($r = fgetcsv($fh)) !== false) {
            if (count(array_filter($r, fn ($v) => trim((string) $v) !== '')) === 0) continue; // skip blank
            $row = [];
            foreach ($header as $i => $col) {
                $row[$col] = isset($r[$i]) ? trim($r[$i]) : '';
            }
            if (empty($row['traditional'])) continue; // skip if no traditional
            $rows[] = $row;
        }
        fclose($fh);
        return $rows;
    }

    /**
     * Check the DB for any of the CSV's traditional chars.
     *
     * @param  array<int,array<string,string>>  $rows
     * @return array<int,array{traditional:string,smart_id:string,sense_count:int}>
     */
    private function detectDuplicates(array $rows): array
    {
        $traditionals = array_column($rows, 'traditional');
        if (empty($traditionals)) return [];

        $existing = DB::table('word_objects')
            ->whereIn('traditional', $traditionals)
            ->select('id', 'traditional', 'smart_id')
            ->get();

        if ($existing->isEmpty()) return [];

        $senseCountsByWo = DB::table('word_senses')
            ->whereIn('word_object_id', $existing->pluck('id'))
            ->select('word_object_id', DB::raw('count(*) as c'))
            ->groupBy('word_object_id')
            ->pluck('c', 'word_object_id');

        return $existing->map(fn ($e) => [
            'traditional' => $e->traditional,
            'smart_id'    => $e->smart_id,
            'sense_count' => (int) ($senseCountsByWo[$e->id] ?? 0),
        ])->values()->all();
    }

    /**
     * Shape CSV rows into v2.3 skeleton entries.
     *
     * Hints (pinyin, pos, tocfl, hsk) are advisory. If provided, they
     * seed a single skeleton sense. If 澄言 decides the word has more
     * senses, they add them during enrichment.
     *
     * @param  array<int,array<string,string>>  $rows
     * @return array<int,array<string,mixed>>
     */
    private function shapeFormatB(array $rows): array
    {
        $words = [];

        foreach ($rows as $r) {
            $trad = $r['traditional'];
            $simp = $r['simplified'] ?? '';
            if ($simp === '') $simp = $this->deriveSimplified($trad);

            $smartId   = $this->deriveSmartId($trad);
            $structure = $this->deriveStructure($trad);

            $pinyinHint = $r['pinyin'] ?? '';
            $posHint    = $r['pos']    ?? '';
            $tocflHint  = $r['tocfl']  ?? '';
            $hskHint    = $r['hsk']    ?? '';

            // If any sense hint provided, seed one skeleton sense. Otherwise
            // leave senses empty — 澄言 creates all from scratch.
            $senses = [];
            if ($pinyinHint !== '' || $posHint !== '' || $tocflHint !== '' || $hskHint !== '') {
                $senses[] = [
                    'pinyin'        => $pinyinHint ?: null,
                    'pos'           => $posHint ?: null,
                    'source'        => 'editorial',
                    'alignment'     => 'partial',
                    'enriched_by'   => $this->option('enriched-by') ?: 'chengyan',
                    'definitions'   => ['en' => null, 'zh-TW' => null],
                    'domains'       => [],
                    'register'      => [],
                    'connotation'   => null,
                    'channel'       => null,
                    'dimension'     => [],
                    'intensity'     => null,
                    'sensitivity'   => null,
                    'valency'       => null,
                    'tocfl'         => $tocflHint ?: null,
                    'hsk'           => $hskHint ?: null,
                    'formula_en'       => null,
                    'formula_zh'       => null,
                    'usage_note_en'    => null,
                    'usage_note_zh'    => null,
                    'learner_traps_en' => null,
                    'learner_traps_zh' => null,
                    'relations'        => [
                        'synonym_close' => [], 'synonym_related' => [],
                        'antonym' => [], 'contrast' => [],
                    ],
                    'collocations'  => [],
                    'examples'      => [],
                    '_hint_note'    => $r['note'] ?? null,
                ];
            }

            $words[] = [
                'word' => [
                    'smart_id'    => $smartId,
                    'traditional' => $trad,
                    'simplified'  => $simp,
                    'structure'   => $structure,
                ],
                'senses' => $senses,
                '_from_csv'   => true,
                '_hint_note'  => $r['note'] ?? null,
            ];
        }

        return $words;
    }

    /**
     * Derive smart_id from traditional chars: u6d41_u52d5 pattern.
     */
    private function deriveSmartId(string $trad): string
    {
        $parts = [];
        $len = mb_strlen($trad);
        for ($i = 0; $i < $len; $i++) {
            $ch = mb_substr($trad, $i, 1);
            // Convert char to unicode codepoint
            $bytes = unpack('N', mb_convert_encoding($ch, 'UCS-4BE', 'UTF-8'));
            $cp = $bytes[1] ?? 0;
            $parts[] = 'u' . strtolower(dechex($cp));
        }
        return implode('_', $parts);
    }

    /**
     * Derive simplified from traditional. Attempts OpenCC via shell; falls
     * back to the traditional string unchanged with a note flag (reviewer
     * should catch + correct).
     */
    private function deriveSimplified(string $trad): string
    {
        $escaped = escapeshellarg($trad);
        $result = @shell_exec("echo {$escaped} | opencc -c t2s.json 2>/dev/null");
        $result = $result ? trim($result) : '';

        if ($result && mb_strlen($result) === mb_strlen($trad)) {
            return $result;
        }

        // Fallback: return unchanged. 澄言 / 絡一 will catch it on review.
        return $trad;
    }

    /**
     * Heuristic structure detector. For single-char: "single". For
     * multi-char: default to "left-right". 澄言 may correct on review.
     */
    private function deriveStructure(string $trad): string
    {
        return mb_strlen($trad) === 1 ? 'single' : 'left-right';
    }

    // ── Shared: chunk + write ────────────────────────────────────────

    /**
     * Chunk the shaped word entries into --batch-size chunks and write
     * each to cowork as rev0 JSON.
     *
     * @param  array<int,array<string,mixed>>  $words
     */
    private function writeBatches(array $words, string $batchName): int
    {
        $batchSize = (int) $this->option('batch-size');
        if ($batchSize < 1) $batchSize = 27;

        $coworkDir = $this->option('cowork-dir')
            ?: ($_SERVER['HOME'] ?? '') . '/Downloads/cowork';
        $coworkDir = rtrim($coworkDir, '/');

        if (! is_dir($coworkDir) && ! $this->option('dry-run')) {
            mkdir($coworkDir, 0777, true);
        }

        $chunks = array_chunk($words, $batchSize);
        $isDry = (bool) $this->option('dry-run');

        $this->info(sprintf(
            '%d word%s → %d batch file%s of up to %d words each',
            count($words), count($words) === 1 ? '' : 's',
            count($chunks), count($chunks) === 1 ? '' : 's',
            $batchSize
        ));

        foreach ($chunks as $i => $chunk) {
            $suffix = count($chunks) === 1 ? '' : sprintf('_%02d', $i + 1);
            $filename = sprintf('%s/%s%s-rev0.json', $coworkDir, $batchName, $suffix);

            // Tag each word with its batch identity
            foreach ($chunk as $seq => $word) {
                $chunk[$seq]['_batch']          = $batchName;
                $chunk[$seq]['_batch_sequence'] = $seq + 1;
            }

            $payload = json_encode($chunk, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

            if ($isDry) {
                $this->line("  (dry-run) would write {$filename} (" . count($chunk) . " words, " . strlen($payload) . " bytes)");
            } else {
                file_put_contents($filename, $payload);
                $this->info("  wrote {$filename} (" . count($chunk) . " words)");
            }
        }

        if ($isDry) {
            $this->line('');
            $this->comment('Dry run — no files written. Re-run without --dry-run to emit.');
        } else {
            $this->line('');
            $this->comment('Hand off to 澄言. They work in ' . $coworkDir . ' and deliver rev1.');
        }

        return self::SUCCESS;
    }
}
