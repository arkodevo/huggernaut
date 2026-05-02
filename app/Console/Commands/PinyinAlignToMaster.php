<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// Align DB word_pronunciations to master_tocfl_vocabulary's pinyin.
//
// Master is stone. DB is mutable. When they disagree on pinyin for a
// word that exists in both, DB moves. This resolves:
//   - Neutralization drift (master xian1sheng1 vs DB xian1sheng5)
//   - Tone sandhi on 一/不 (master yi1qi3 vs DB yi4qi3)
//   - Rhotacization (master huar4 vs DB hua4r)
//   - Any other tone-level disagreements on the same base syllables
//
// Dry-run is the default. --apply writes the changes. --tsv dumps the
// full audit (all classes, including aligned rows) for inspection.
//
// Scope: only touches pronunciations on word_objects whose traditional
// character exists in master_tocfl_vocabulary. Editorial additions and
// non-TOCFL words are left alone.
//
// Safety: when the proposed target already exists on the same word_object
// (an UPDATE would create a merge situation), the row is classified as
// 'merge-needed' and skipped — handled in a separate follow-up. Rare,
// but real.

class PinyinAlignToMaster extends Command
{
    protected $signature = 'pinyin:align-to-master
        {--apply : Write changes. Default is dry-run.}
        {--strict : Master is authoritative — bypass patchMasterTarget. Use when DB may carry artifacts (e.g. derivative pinyin bugs) you want replaced, not preserved. Default is non-strict (patches master to preserve DB tone digits master dropped).}
        {--tsv= : Optional path to write full audit TSV.}
        {--sample=30 : Number of sample rows per class to print.}
    ';

    protected $description = 'Align word_pronunciations.pronunciation_text to master TOCFL pinyin';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $strict = (bool) $this->option('strict');
        $tsvPath = $this->option('tsv');
        $sampleSize = (int) $this->option('sample');

        // Build master index: traditional → set of pinyin forms.
        // Master has entries at multiple levels sharing the same pinyin in most
        // cases. Homographs (好 hao3/hao4) appear with distinct pinyins — we
        // collect them all. Alternates delimited by / are split.
        $masterByTrad = [];
        foreach (DB::table('master_tocfl_vocabulary')->select('traditional','pinyin')->get() as $row) {
            if (! $row->pinyin) continue;
            $alternates = array_filter(array_map('trim', explode('/', $row->pinyin)));
            foreach ($alternates as $p) {
                $masterByTrad[$row->traditional][$p] = true;
            }
        }
        foreach ($masterByTrad as &$set) {
            $set = array_keys($set);
        }
        unset($set);

        $this->info('Master indexed: ' . count($masterByTrad) . ' distinct traditional characters.');

        // Walk DB pronunciations for word_objects that exist in master.
        $dbRows = DB::table('word_pronunciations as wp')
            ->join('word_objects as wo', 'wo.id', '=', 'wp.word_object_id')
            ->whereIn('wo.traditional', array_keys($masterByTrad))
            ->select('wp.id as pron_id', 'wp.pronunciation_text as db_pinyin',
                     'wo.id as wo_id', 'wo.traditional', 'wo.smart_id')
            ->get();

        $this->info('DB pronunciations in scope: ' . $dbRows->count());

        $classes = [
            'aligned'                 => [],  // DB exactly matches master raw
            'master-error-db-correct' => [],  // master has dropped tones; DB is already correct; NO DB change
            'proposed-change'         => [],  // single unambiguous target; UPDATE DB
            'ambiguous'               => [],  // multiple master pinyins could fit
            'no-match'                => [],  // DB pinyin doesn't fit any master form
            'merge-needed'            => [],  // target already exists on same word_object
        ];

        // First pass: collect all DB pinyins per word_object (to detect merges)
        $pinyinsByWo = [];
        foreach ($dbRows as $r) {
            $pinyinsByWo[$r->wo_id][] = $r->db_pinyin;
        }

        foreach ($dbRows as $r) {
            $masterSet = $masterByTrad[$r->traditional] ?? [];

            if (in_array($r->db_pinyin, $masterSet, true)) {
                $classes['aligned'][] = $r;
                continue;
            }

            $dbNorm = $this->toneStrip($r->db_pinyin);
            $masterNormIndex = [];  // master pinyin → its tone-stripped form
            foreach ($masterSet as $mp) {
                $masterNormIndex[$mp] = $this->toneStrip($mp);
            }
            $candidates = array_keys(array_filter($masterNormIndex, fn ($norm) => $norm === $dbNorm));

            if (count($candidates) === 0) {
                $classes['no-match'][] = $r;
            } elseif (count($candidates) > 1) {
                $classes['ambiguous'][] = (object) ((array) $r + ['candidates' => $candidates]);
            } else {
                $masterTarget = $candidates[0];
                // Patch master errors: if master dropped a non-trailing tone
                // digit that DB correctly has (e.g., 可愛 master "keai4" vs DB
                // "ke3'ai4" — the 3 on 'ke' was dropped), restore the digit
                // from DB. Trailing-slot drops are legitimate neutralization
                // and left alone (小子 "xiao3zi5" → "xiao3zi" stays).
                //
                // --strict bypasses this: master is authoritative as-is, DB
                // moves to master's clean form. Use when DB carries artifacts
                // (e.g. bogus extra tone digits from derivative pinyin bugs)
                // that you want replaced, not preserved.
                if ($strict) {
                    $target = $masterTarget;
                    $patched = false;
                } else {
                    [$target, $patched] = $this->patchMasterTarget(
                        $r->db_pinyin, $masterTarget
                    );
                }
                $r = (object) ((array) $r + [
                    'target'        => $target,
                    'master_target' => $masterTarget,
                    'patched'       => $patched,
                    'transform'     => $this->classifyTransform($r->db_pinyin, $target),
                ]);
                // If patched target equals DB's existing value, DB was
                // already right — master has an error. No DB change.
                if ($target === $r->db_pinyin) {
                    $classes['master-error-db-correct'][] = $r;
                    continue;
                }
                // If target already exists as a different pronunciation row
                // on this word_object, updating would create a duplicate.
                if (in_array($target, $pinyinsByWo[$r->wo_id] ?? [], true)) {
                    $classes['merge-needed'][] = $r;
                } else {
                    $classes['proposed-change'][] = $r;
                }
            }
        }

        // Summary
        $this->line('');
        $this->info('=== Audit Summary ' . ($apply ? '(APPLY)' : '(DRY RUN)') . ' ===');
        foreach ($classes as $cls => $rows) {
            $this->line(sprintf('  %-17s %5d', $cls . ':', count($rows)));
        }

        // Sample each non-empty class (except 'aligned' — the baseline)
        foreach (['proposed-change', 'master-error-db-correct', 'ambiguous', 'no-match', 'merge-needed'] as $cls) {
            if (empty($classes[$cls])) continue;
            $this->line('');
            $this->line("--- {$cls} (showing up to {$sampleSize}) ---");
            foreach (array_slice($classes[$cls], 0, $sampleSize) as $r) {
                if ($cls === 'proposed-change' || $cls === 'merge-needed') {
                    $suffix = $r->patched
                        ? sprintf('  [%s · PATCHED from master "%s"]', $r->transform, $r->master_target)
                        : sprintf('  [%s]', $r->transform);
                    $this->line(sprintf(
                        '  %s (%s)  DB "%s" → "%s"%s',
                        $r->traditional, $r->smart_id, $r->db_pinyin, $r->target, $suffix
                    ));
                } elseif ($cls === 'master-error-db-correct') {
                    $this->line(sprintf(
                        '  %s (%s)  DB "%s" already correct — master has "%s" (dropped tones)',
                        $r->traditional, $r->smart_id, $r->db_pinyin, $r->master_target
                    ));
                } elseif ($cls === 'ambiguous') {
                    $this->line(sprintf(
                        '  %s (%s)  DB "%s" ↔ candidates: %s',
                        $r->traditional, $r->smart_id, $r->db_pinyin, implode(', ', $r->candidates)
                    ));
                } else {
                    $this->line(sprintf(
                        '  %s (%s)  DB "%s" has no master match (master has: %s)',
                        $r->traditional, $r->smart_id, $r->db_pinyin,
                        implode(', ', $masterByTrad[$r->traditional] ?? ['-'])
                    ));
                }
            }
        }

        // Transform breakdown for proposed-change
        if (! empty($classes['proposed-change'])) {
            $this->line('');
            $this->line('--- proposed-change by transform class ---');
            $byTransform = [];
            foreach ($classes['proposed-change'] as $r) {
                $byTransform[$r->transform] = ($byTransform[$r->transform] ?? 0) + 1;
            }
            foreach ($byTransform as $t => $n) {
                $this->line(sprintf('  %-22s %d', $t, $n));
            }
        }

        // TSV dump
        if ($tsvPath) {
            $fh = fopen($tsvPath, 'w');
            fwrite($fh, "class\ttraditional\tsmart_id\tpron_id\tdb_pinyin\ttarget\ttransform\tcandidates\n");
            foreach ($classes as $cls => $rows) {
                foreach ($rows as $r) {
                    $target = $r->target ?? '';
                    $tf = $r->transform ?? '';
                    $cand = isset($r->candidates) ? implode('|', $r->candidates) : '';
                    fwrite($fh, implode("\t", [
                        $cls, $r->traditional, $r->smart_id, $r->pron_id,
                        $r->db_pinyin, $target, $tf, $cand,
                    ]) . "\n");
                }
            }
            fclose($fh);
            $this->info('TSV written: ' . $tsvPath);
        }

        // Apply
        if ($apply) {
            if (empty($classes['proposed-change'])) {
                $this->warn('Nothing to apply.');
                return self::SUCCESS;
            }
            $this->line('');
            $this->warn('Applying ' . count($classes['proposed-change']) . ' updates...');
            DB::beginTransaction();
            try {
                $updated = 0;
                foreach ($classes['proposed-change'] as $r) {
                    DB::table('word_pronunciations')
                        ->where('id', $r->pron_id)
                        ->update([
                            'pronunciation_text' => $r->target,
                            'updated_at'         => now(),
                        ]);
                    $updated++;
                }
                DB::commit();
                $this->info("Applied {$updated} pronunciation updates.");
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error('Apply failed: ' . $e->getMessage());
                return self::FAILURE;
            }
        } else {
            $this->line('');
            $this->comment('Dry run. Re-run with --apply to commit, ideally after a backup.');
        }

        return self::SUCCESS;
    }

    /**
     * Tone-stripped canonical key for matching. Also normalizes two other
     * mechanical convention differences:
     *   - ASCII 'v' used as a surrogate for 'ü' (DB legacy) → 'ü'
     *   - Apostrophe syllable separators (ke3'ai4) → dropped
     * These don't change the word; they just pick a canonical romanization.
     */
    /**
     * When master's target form has fewer tone digits than DB, check each
     * missing-digit position:
     *   - Last syllable slot missing a digit → legitimate neutralization
     *     ('xiao3zi5' → 'xiao3zi'). Keep master's form.
     *   - Non-last slot missing a digit → master error (dropped a non-
     *     neutralizable tone). Patch the target by restoring DB's digit
     *     at that position, preserving master's other conventions (ü,
     *     no apostrophe, etc.).
     *
     * Returns [patchedTarget, wasPatched].
     *
     * Approach: walk both normalized forms slot by slot and build the
     * target character-by-character. When master lacks a digit that DB
     * has and we're not at the final syllable, inject DB's digit.
     */
    private function patchMasterTarget(string $db, string $master): array
    {
        // Normalize both for comparison; master is already master-shape
        $dbN = str_replace("'", '', str_replace('v', 'ü', strtolower($db)));
        // $master is canonical target as-is.
        $pairs = $this->diffTonePairs($dbN, $master);
        if (empty($pairs)) return [$master, false];

        // Figure out which slot(s) of master are missing a non-trailing digit
        // that DB has. Count the total syllable-slot positions (= digit
        // positions on DB) to know what "trailing" means.
        $dbDigitPositions = [];
        for ($k = 0, $n = strlen($dbN); $k < $n; $k++) {
            if (ctype_digit($dbN[$k])) $dbDigitPositions[] = $k;
        }
        $lastDbDigitPos = end($dbDigitPositions) ?: -1;

        $needsPatch = false;
        foreach ($pairs as [$f, $t]) {
            if ($t === null && in_array($f, ['1','2','3','4'], true)) {
                // Master missing a digit DB has. Was it at the last DB digit slot?
                // If not, it's a non-final missing digit → patch.
                // We don't know which exact slot index was the missing one without
                // more bookkeeping, but the simple heuristic below is: if the
                // pairs list has >1 DB-only entries OR the DB ends in a digit
                // and this pair is not that trailing digit, patch.
                $needsPatch = true;
                break;
            }
        }
        if (! $needsPatch) return [$master, false];

        // Build patched target: walk DB and master in parallel, keeping
        // master's letters/digits, but injecting DB's tone digits when
        // master's slot is empty AND that slot is not the final one.
        $out = '';
        $i = 0; $j = 0;
        $dl = strlen($dbN); $ml = strlen($master);
        while ($i < $dl || $j < $ml) {
            $dc = $i < $dl ? $dbN[$i] : '';
            $mc = $j < $ml ? $master[$j] : '';
            $dIsDigit = $dc !== '' && ctype_digit($dc);
            $mIsDigit = $mc !== '' && ctype_digit($mc);

            if (! $dIsDigit && ! $mIsDigit) {
                // Letters should match post-normalization. Take master's.
                $out .= $mc;
                $i++; $j++;
            } elseif ($dIsDigit && $mIsDigit) {
                // Both have a digit — take master's
                $out .= $mc;
                $i++; $j++;
            } elseif ($dIsDigit) {
                // DB has a digit here, master doesn't. Is this the final DB digit?
                if ($i === $lastDbDigitPos) {
                    // Trailing neutralization — don't inject
                } else {
                    // Non-final — inject DB's digit
                    $out .= $dc;
                }
                $i++;
            } else {
                // Master has a digit DB doesn't — take master's
                $out .= $mc;
                $j++;
            }
        }

        return [$out, true];
    }

    private function toneStrip(string $p): string
    {
        $p = strtolower($p);
        $p = str_replace(['v'], ['ü'], $p);
        $p = str_replace("'", '', $p);
        return preg_replace('/[0-9]/', '', $p);
    }

    /**
     * Slot-by-slot tone comparison. Since the two forms have matching
     * tone-stripped keys, the letter sequences are identical — only the
     * tone digits differ (or are present/absent at different positions).
     *
     * For each differing tone slot, classify the pair:
     *   - neutralization-shift: one side is 5/absent, other is 1-4
     *   - tone-distinct: both in 1-4 but different (sandhi or genuine)
     *
     * Whole transform is 'neutralization' iff every differing slot is a
     * neutralization-shift. Otherwise check for sandhi-yi/bu/rhotacization.
     */
    private function classifyTransform(string $from, string $to): string
    {
        $fromN = str_replace("'", '', str_replace('v', 'ü', strtolower($from)));
        $toN   = str_replace("'", '', str_replace('v', 'ü', strtolower($to)));

        $pairs = $this->diffTonePairs($fromN, $toN);

        if (empty($pairs)) {
            // toneStrip matched but no differing digit positions — shouldn't
            // reach here (exact match would have been 'aligned')
            return 'convention';
        }

        $neutralOnly = true;
        foreach ($pairs as [$f, $t]) {
            $fIsNeutral = ($f === null || $f === '5');
            $tIsNeutral = ($t === null || $t === '5');
            $fIsToned   = in_array($f, ['1','2','3','4'], true);
            $tIsToned   = in_array($t, ['1','2','3','4'], true);
            // Convention-only: both sides neutral (5 ↔ bare), same semantic
            if ($fIsNeutral && $tIsNeutral) continue;
            // Neutralization-shift: one neutral, other toned (citation ↔ neutral)
            if (($fIsNeutral && $tIsToned) || ($tIsNeutral && $fIsToned)) continue;
            // Real tone difference at this slot
            $neutralOnly = false;
            break;
        }
        if ($neutralOnly) return 'neutralization';

        // Not pure neutralization — check structured sandhi / rhotacization.
        $firstBase = preg_replace('/[0-9]/', '', $this->firstSyllable($fromN));
        if (in_array($firstBase, ['yi', 'bu'], true)) {
            return 'sandhi-' . $firstBase;
        }
        if ((bool) preg_match('/\dr/', $fromN) !== (bool) preg_match('/\dr/', $toN)) {
            return 'rhotacization';
        }
        return 'tone-other';
    }

    /**
     * Walk both pinyin strings in parallel (letters identical post-
     * normalization). Collect (fromDigit, toDigit) pairs at every
     * position where the two sides disagree on what digit (if any)
     * follows the shared letter sequence.
     *
     * Absent digit is represented as null. We treat null and '5' both as
     * "neutral" in the caller.
     *
     * @return array<int,array{0:?string,1:?string}>
     */
    private function diffTonePairs(string $from, string $to): array
    {
        $pairs = [];
        $i = 0; $j = 0;
        $fl = strlen($from); $tl = strlen($to);
        while ($i < $fl || $j < $tl) {
            $fc = $i < $fl ? $from[$i] : '';
            $tc = $j < $tl ? $to[$j]   : '';
            $fIsDigit = $fc !== '' && ctype_digit($fc);
            $tIsDigit = $tc !== '' && ctype_digit($tc);

            if ($fIsDigit && $tIsDigit) {
                if ($fc !== $tc) $pairs[] = [$fc, $tc];
                $i++; $j++;
            } elseif ($fIsDigit) {
                $pairs[] = [$fc, null];
                $i++;
            } elseif ($tIsDigit) {
                $pairs[] = [null, $tc];
                $j++;
            } else {
                // Both non-digit letters; they should match post-normalization
                $i++; $j++;
            }
        }
        return $pairs;
    }

    private function extractTones(string $p): array
    {
        preg_match_all('/(\d)/', $p, $m);
        return $m[1] ?? [];
    }

    private function firstSyllable(string $p): string
    {
        // First syllable = letters before the first digit (if any), else whole string
        preg_match('/^([a-zA-Z]+\d?)/', $p, $m);
        return $m[1] ?? $p;
    }
}
