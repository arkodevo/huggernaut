<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// Import the TOCFL 8k master vocabulary list into the immutable
// master_tocfl_vocabulary table.
//
//   php artisan master:import-tocfl \
//       --xlsx=/Users/chuluoyi/Documents/華語/planning/華語八千詞_expanded_final_numeric.xlsx \
//       --source-version=2024-09-23
//
// Pipeline: parse xlsx → apply known xlsx-origin corrections in memory
// → insert the clean result. The xlsx has a handful of defects that
// this command silently fixes on the way in; see CORRECTIONS below.
// Correction guards only fire when the expected pre-correction value
// is present, so running against a different xlsx version is safe —
// guards fail, the raw row passes through.
//
// Scope: only TOCFL rows (Levels 1–7). Lulu rows (Level 8) are skipped.
// No editorial columns are imported — just the pure master fields:
// band_label, level_number, traditional, pinyin, official_pos, row_seq.
//
// Immutability: UPDATE and DELETE triggers block writes on the stone.
// If the table is already populated, the command refuses to run unless
// --force-empty is passed, which drops and recreates the triggers +
// truncates. Nuclear — take a pg_dump backup first.
//
// The xlsx → JSON step is shelled out to Python3 (openpyxl). PHP
// doesn't have phpspreadsheet installed and we don't want to add it
// just for a one-shot import.

class MasterImportTocfl extends Command
{
    protected $signature = 'master:import-tocfl
        {--xlsx= : Path to the TOCFL master xlsx}
        {--csv= : Path to a pre-expanded CSV (columns: source,band_label,level_number,traditional,pinyin,pos,notes)}
        {--source-version= : Identifier for this revision}
        {--force-empty : Drop and recreate the table before import}
        {--dry-run : Parse and report counts without writing}
    ';

    protected $description = 'Import TOCFL 8k master vocabulary into the immutable reference table';

    public function handle(): int
    {
        $xlsx    = $this->option('xlsx');
        $csv     = $this->option('csv');
        $version = $this->option('source-version');

        if (! $xlsx && ! $csv) {
            $this->error('One of --xlsx or --csv is required');
            return self::FAILURE;
        }
        if ($xlsx && $csv) {
            $this->error('Pass either --xlsx OR --csv, not both');
            return self::FAILURE;
        }
        if ($xlsx && ! is_file($xlsx)) {
            $this->error('--xlsx file not found: ' . $xlsx);
            return self::FAILURE;
        }
        if ($csv && ! is_file($csv)) {
            $this->error('--csv file not found: ' . $csv);
            return self::FAILURE;
        }

        if (! $version) {
            $this->error('--source-version is required');
            return self::FAILURE;
        }

        $existingCount = DB::table('master_tocfl_vocabulary')->count();
        if ($existingCount > 0 && ! $this->option('force-empty')) {
            $this->error("master_tocfl_vocabulary already populated ({$existingCount} rows). Refusing to re-import.");
            $this->line('Pass --force-empty to drop + recreate (use for genuine xlsx revisions).');
            return self::FAILURE;
        }

        $rows = $xlsx ? $this->parseXlsx($xlsx) : $this->parseCsv($csv);
        if ($rows === null) return self::FAILURE;

        $this->info('Parsed ' . count($rows) . ' raw TOCFL rows from xlsx.');

        $rows = $this->applyCorrections($rows);
        $this->info('After corrections: ' . count($rows) . ' rows.');

        $byLevel = [];
        foreach ($rows as $r) $byLevel[$r['level_number']] = ($byLevel[$r['level_number']] ?? 0) + 1;
        foreach (range(1, 7) as $l) {
            $this->line(sprintf('  L%d: %d', $l, $byLevel[$l] ?? 0));
        }

        if ($this->option('dry-run')) {
            $this->comment('Dry run — no rows written.');
            return self::SUCCESS;
        }

        if ($existingCount > 0) {
            $this->warn('--force-empty: dropping + recreating master_tocfl_vocabulary');
            // The trigger blocks DELETE, so we TRUNCATE via drop-and-create.
            // Triggers and the table are both owned by the migration; the
            // safest path is to drop the whole table via raw SQL that also
            // drops the triggers, then re-run the migration's CREATE.
            DB::unprepared(<<<'SQL'
                DROP TRIGGER IF EXISTS master_tocfl_vocabulary_no_delete ON master_tocfl_vocabulary;
                DROP TRIGGER IF EXISTS master_tocfl_vocabulary_no_update ON master_tocfl_vocabulary;
                TRUNCATE TABLE master_tocfl_vocabulary RESTART IDENTITY;
                CREATE TRIGGER master_tocfl_vocabulary_no_update
                    BEFORE UPDATE ON master_tocfl_vocabulary
                    FOR EACH ROW EXECUTE FUNCTION master_tocfl_vocabulary_readonly();
                CREATE TRIGGER master_tocfl_vocabulary_no_delete
                    BEFORE DELETE ON master_tocfl_vocabulary
                    FOR EACH ROW EXECUTE FUNCTION master_tocfl_vocabulary_readonly();
            SQL);
        }

        $now = now()->toIso8601String();
        $chunks = array_chunk($rows, 500);
        $inserted = 0;
        foreach ($chunks as $chunk) {
            $payload = array_map(fn ($r) => [
                'band_label'     => $r['band_label'],
                'level_number'   => $r['level_number'],
                'traditional'    => $r['traditional'],
                'pinyin'         => $r['pinyin'],
                'official_pos'   => $r['official_pos'],
                'row_seq'        => $r['row_seq'],
                'source_version' => $version,
                'imported_at'    => $now,
            ], $chunk);

            DB::table('master_tocfl_vocabulary')->insert($payload);
            $inserted += count($payload);
            $this->line("  inserted {$inserted} / " . count($rows));
        }

        $this->info("Done. {$inserted} rows written to master_tocfl_vocabulary (source_version={$version}).");
        $this->comment('Table is now stone — UPDATE and DELETE blocked by trigger.');
        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------
    // Corrections for known xlsx-origin defects.
    //
    // Each entry is row_seq-keyed and guarded by the expected pre-correction
    // value — guards fail silently (with a warn) on a different xlsx, so the
    // command degrades gracefully into a raw import.
    //
    // UPDATES — pinyin: fix syllable-boundary corruption from the xlsx's
    //   letter-by-letter diacritic→numeric conversion
    //   POS: fix one casing drift
    //   traditional: strip zhuyin-in-parens annotations that leaked in
    //
    // DELETES — prune xlsx redundancies caused by mis-expanded compound cells
    //   like "盒/盒(子) hé/hézi M/N" → should be 4 rows, xlsx gave 6-8.
    //   Also carves out two semantic POS errors: 刷子 V and 架子 V (both
    //   noun-only) that came from mechanical POS propagation across variants.
    //
    // Discovered 2026-04-23 by 絡一 + 光流 auditing the stone.
    // -------------------------------------------------------------------------
    private const PINYIN_FIXES = [
        // Letter-walk bugs — syllable-count-preserving, boundary-crossed
        // (caught by 絡一 + 光流 across multiple audit passes 2026-04-23).
        // A consonant (typically n or g) migrated across a syllable boundary
        // in the xlsx's letter-by-letter diacritic→numeric conversion.
        1692 => ['fa3ne2r',             'fan3er2'],             // 反而
        2039 => ['lia4nai4',            'lian4ai4'],            // 戀愛: n 戀→愛
        2169 => ['pin2gan1',            'ping2an1'],            // 平安: g 平→安
        2205 => ['qing3jia4',           'qing2kuang4'],         // 情況: scrambled
        3231 => ['fan1gan4',            'fang1an4'],            // 方案: g 方→案
        3342 => ['ga3nen1',             'gan3en1'],             // 感恩: n 感→恩
        3722 => ['ji1ne2',              'jin1e2'],              // 金額: n 金→額
        3727 => ['jin4gai4',            'jing4ai4'],            // 敬愛: g 敬→愛
        4223 => ['qi1nai4',             'qin1ai4'],             // 親愛: n 親→愛
        4286 => ['ra2ne2r',             'ran2er2'],             // 然而
        4766 => ['xiang1qin1xian1gai4', 'xiang1qin1xiang1ai4'], // 相親相愛: g 2nd-相→愛
        4923 => ['yi1ba1ne2ryan2',      'yi1ban1er2yan2'],      // 一般而言: n 般→而; 而 mistreated as erhua
        4938 => ['yi1ne2r',             'yin1er2'],             // 因而
        4946 => ['yin1ge2r',            'ying1er2'],            // 嬰兒
        5299 => ['a4nan4',              'an4an4'],              // 暗暗: n 1st-暗→2nd-暗
        5315 => ['ba4nan4',             'ban4an4'],             // 辦案: n 辦→案
        5733 => ['din4ge2',             'ding4e2'],             // 定額: g 定→額
        5789 => ['e1nai4',              'en1ai4'],              // 恩愛: n 恩→愛
        5996 => ['?gonji3',             'gong1ji3'],            // 供給: literal ? + missing syllable
        6370 => ['ji4ne2r',             'jin4er2'],             // 進而
        6709 => ['min2ge2',             'ming2e2'],             // 名額: g 名→額
        7099 => ['she1nao4',            'shen1ao4'],            // 深奧: n 深→奧
        7571 => ['xi1nai4',             'xin1ai4'],             // 心愛: n 心→愛
        // Compound-reading contamination — single-char variant inherited
        // the multi-char pinyin from its sibling row in the xlsx's
        // "A/B pinyin POS" compound expansion. Per 絡一's 2026-04-23
        // clarification: expansion "A/B pinyin-AB" yields A with its OWN
        // reading + B with pinyin-AB. The 9 rows below had B storing the
        // AB reading instead of B's single-char reading.
        824  => ['sheng1yin1', 'sheng1'],  // 聲 (compound 聲音)
        1479 => ['bao3zheng4', 'zheng4'],  // 証 N (compound 保證)
        1480 => ['bao3zheng4', 'zheng4'],  // 証 V (compound 保證)
        1533 => ['bu4zhi4',    'bu4'],     // 布 (compound 佈置, variant trad)
        1696 => ['fang3wen4',  'fang3'],   // 訪 (compound 訪問)
        1783 => ['gu3ji1',     'ji1'],     // 跡 (compound 古蹟, variant trad)
        1963 => ['jiu4jiu5',   'jiu4'],    // 舅 (reduplication 舅舅)
        3639 => ['jian4jian4', 'jian4'],   // 漸 (reduplication 漸漸)
        6740 => ['mo4mo4',     'mo4'],     // 默 (reduplication 默默)
        // Paired swap: rows 3469 and 3470 had their pinyin AND POS
        // swapped with each other in the xlsx. Each row's fix is the
        // other's original value.
        3469 => ['guo4jie2', 'guo2li4'],   // 國立 (was holding 過節's pinyin)
        3470 => ['guo2li4',  'guo4jie2'],  // 過節 (was holding 國立's pinyin)
    ];

    private const POS_FIXES = [
        1528 => ['conj',    'Conj'],       // 不如
        3469 => ['V-sep',   'Vs-attr'],    // 國立 (was holding 過節's POS)
        3470 => ['Vs-attr', 'V-sep'],      // 過節 (was holding 國立's POS)
    ];

    private const TRADITIONAL_FIXES = [
        560 => ['部份(˙ㄈㄣ)', '部份'],
        561 => ['部份(˙ㄈㄣ)', '部份'],
        602 => ['窗戶(˙ㄏㄨ)', '窗戶'],
    ];

    private const DELETE_ROW_SEQS = [
        // Triplicates — primary variant duplicated instead of expanding to -子
        609, 610, 611, 612,            // 盒 L3
        853, 854, 855, 856,            // 瓶 L3
        844, 845, 846, 847,            // 盤 L3
        1066, 1067, 1068, 1069,        // 箱 L4
        // Triplicates with semantic carve-out (-子 variant is N only)
        1124, 1125, 1127, 1128, 1129,  // 刷 L4 (1127 = spurious 刷子 V)
        6268, 6269, 6270, 6271, 6272,  // 架 L7 (6272 = spurious 架子 V)
        // Doubles — copy-pasted rows
        897,   // 一下子兒 L3
        770,   // 指頭 L3
        604,   // 窗戶 L3 (602 kept after zhuyin strip)
        2136,  // 腦 L5
        6545,  // 老頭兒 L7
    ];

    /**
     * Apply the correction pass to parsed rows. Returns the filtered +
     * corrected row list. Emits warnings when a guard fails (caller is
     * importing a different xlsx than the corrections were written for).
     *
     * @param  array<int,array<string,mixed>>  $rows
     * @return array<int,array<string,mixed>>
     */
    private function applyCorrections(array $rows): array
    {
        $deleteSet = array_flip(self::DELETE_ROW_SEQS);
        $updates = 0;
        $deletes = 0;
        $guardMisses = 0;

        $filtered = [];
        foreach ($rows as $row) {
            $seq = $row['row_seq'];

            if (isset($deleteSet[$seq])) {
                $deletes++;
                continue;
            }

            if (isset(self::PINYIN_FIXES[$seq])) {
                [$expected, $correct] = self::PINYIN_FIXES[$seq];
                if ($row['pinyin'] === $expected) {
                    $row['pinyin'] = $correct;
                    $updates++;
                } else {
                    $this->warn("pinyin guard miss @ row_seq {$seq}: expected '{$expected}', got '{$row['pinyin']}' — skipping");
                    $guardMisses++;
                }
            }
            if (isset(self::POS_FIXES[$seq])) {
                [$expected, $correct] = self::POS_FIXES[$seq];
                if ($row['official_pos'] === $expected) {
                    $row['official_pos'] = $correct;
                    $updates++;
                } else {
                    $this->warn("pos guard miss @ row_seq {$seq}: expected '{$expected}', got '{$row['official_pos']}' — skipping");
                    $guardMisses++;
                }
            }
            if (isset(self::TRADITIONAL_FIXES[$seq])) {
                [$expected, $correct] = self::TRADITIONAL_FIXES[$seq];
                if ($row['traditional'] === $expected) {
                    $row['traditional'] = $correct;
                    $updates++;
                } else {
                    $this->warn("traditional guard miss @ row_seq {$seq}: expected '{$expected}', got '{$row['traditional']}' — skipping");
                    $guardMisses++;
                }
            }

            $filtered[] = $row;
        }

        $this->line("  corrections: {$updates} updated, {$deletes} deleted" .
                    ($guardMisses ? ", {$guardMisses} guard misses (raw xlsx differs from expected)" : ""));

        return $filtered;
    }

    /**
     * Parse a pre-expanded CSV with BOM-tolerant UTF-8 reading. Expected
     * columns: source, band_label, level_number, traditional, pinyin, pos, notes.
     * Only TOCFL-source rows with level 1..7 are kept.
     *
     * @return array<int,array<string,mixed>>|null
     */
    private function parseCsv(string $path): ?array
    {
        $fh = fopen($path, 'r');
        if (! $fh) {
            $this->error('Could not open CSV: ' . $path);
            return null;
        }
        $header = fgetcsv($fh);
        if (! $header) {
            fclose($fh);
            $this->error('CSV empty or unreadable');
            return null;
        }
        // Strip BOM from first header cell if present
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }
        $map = array_flip(array_map(fn ($h) => trim(strtolower($h)), $header));
        $need = ['source', 'band_label', 'level_number', 'traditional', 'pinyin', 'pos'];
        foreach ($need as $col) {
            if (! isset($map[$col])) {
                $this->error("CSV missing required column: {$col}");
                fclose($fh);
                return null;
            }
        }

        $rows = [];
        $seq = 0;
        while (($r = fgetcsv($fh)) !== false) {
            $seq++;
            if (count(array_filter($r, fn ($v) => trim((string) $v) !== '')) === 0) continue;
            $source = trim($r[$map['source']] ?? '');
            $lvl    = (int) ($r[$map['level_number']] ?? 0);
            $trad   = trim($r[$map['traditional']] ?? '');
            $pin    = trim($r[$map['pinyin']] ?? '');
            $pos    = trim($r[$map['pos']] ?? '');
            $band   = trim($r[$map['band_label']] ?? '');

            if ($source !== 'TOCFL') continue;
            if ($lvl < 1 || $lvl > 7) continue;
            if ($trad === '') continue;

            $rows[] = [
                'band_label'   => $band,
                'level_number' => $lvl,
                'traditional'  => $trad,
                'pinyin'       => $pin !== '' ? $pin : null,
                'official_pos' => $pos !== '' ? $pos : null,
                'row_seq'      => $seq,
            ];
        }
        fclose($fh);
        return $rows;
    }

    /**
     * Shell out to Python3 + openpyxl to parse the xlsx into clean rows.
     * Returns null on error. Filters out Lulu (Level 8) and preserves
     * original row order via row_seq.
     *
     * @return array<int,array<string,mixed>>|null
     */
    private function parseXlsx(string $xlsx): ?array
    {
        $python = <<<'PY'
import sys, json
try:
    import openpyxl
except ImportError:
    print(json.dumps({"error": "openpyxl not installed. pip3 install openpyxl"}), file=sys.stderr)
    sys.exit(2)

path = sys.argv[1]
wb = openpyxl.load_workbook(path, data_only=True, read_only=True)
ws = wb.active
rows = []
seq = 0
for row in ws.iter_rows(min_row=2, values_only=True):
    # Columns: source, band_label, level_number, traditional, pinyin,
    #          official_pos, [guangliu_pos], [huiming_pos]
    if not row or len(row) < 6:
        continue
    source = (row[0] or "").strip() if isinstance(row[0], str) else ""
    band = row[1]
    lvl = row[2]
    trad = row[3]
    pinyin = row[4]
    pos = row[5]
    seq += 1
    # Filter: TOCFL only, Levels 1..7
    if source != "TOCFL":
        continue
    if not isinstance(lvl, int) or lvl < 1 or lvl > 7:
        continue
    if not trad:
        continue
    rows.append({
        "band_label": str(band).strip() if band else "",
        "level_number": int(lvl),
        "traditional": str(trad).strip(),
        "pinyin": (str(pinyin).strip() if pinyin else None),
        "official_pos": (str(pos).strip() if pos else None),
        "row_seq": seq,
    })
print(json.dumps(rows, ensure_ascii=False))
PY;

        $tmpScript = tempnam(sys_get_temp_dir(), 'xlsx_parse_') . '.py';
        file_put_contents($tmpScript, $python);

        $cmd = 'python3 ' . escapeshellarg($tmpScript) . ' ' . escapeshellarg($xlsx) . ' 2>&1';
        $output = shell_exec($cmd);
        @unlink($tmpScript);

        if ($output === null) {
            $this->error('python3 not available or script failed to run');
            return null;
        }

        $data = json_decode($output, true);
        if (! is_array($data)) {
            $this->error('Failed to parse xlsx. Python output:');
            $this->line($output);
            return null;
        }

        if (isset($data['error'])) {
            $this->error($data['error']);
            return null;
        }

        return $data;
    }
}
