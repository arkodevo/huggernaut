<?php

namespace App\Helpers;

/**
 * Converts between numeric-tone pinyin (DB storage format) and
 * tone-marked pinyin (diacritic display format).
 *
 * DB stores numeric-tone, no separator: "biao3bai2", "an1ning2"
 * Display renders tone-marked, space-separated syllables: "biǎo bái", "ān níng"
 *
 * Tone-mark placement rules (official Hànyǔ Pīnyīn standard):
 *   1. If the syllable contains a or e, that vowel takes the mark.
 *   2. If the syllable contains ou, the o takes the mark.
 *   3. Otherwise, the last vowel takes the mark.
 *
 * Tone 5 (neutral / 輕聲) = no digit → no diacritic, written as-is.
 */
class PinyinHelper
{
    /** Tone-marked vowel lookup [vowel][tone 1-5]. Tone 5 = no mark. */
    private const MARKS = [
        'a' => ['ā', 'á', 'ǎ', 'à', 'a'],
        'e' => ['ē', 'é', 'ě', 'è', 'e'],
        'i' => ['ī', 'í', 'ǐ', 'ì', 'i'],
        'o' => ['ō', 'ó', 'ǒ', 'ò', 'o'],
        'u' => ['ū', 'ú', 'ǔ', 'ù', 'u'],
        'ü' => ['ǖ', 'ǘ', 'ǚ', 'ǜ', 'ü'],
        'v' => ['ǖ', 'ǘ', 'ǚ', 'ǜ', 'ü'],  // v = ü in some romanisations
    ];

    private const VOWELS = ['a', 'e', 'i', 'o', 'u', 'ü', 'v'];

    /**
     * Convert a full numeric-tone pinyin string to tone-marked display form.
     *
     * @param  string  $numeric  e.g. "biao3bai2" or "an1ning2"
     * @param  string  $sep      syllable separator for display (default: space)
     * @return string            e.g. "biǎo bái" or "ān níng"
     */
    public static function toMarked(string $numeric, string $sep = ' '): string
    {
        if (trim($numeric) === '') {
            return '';
        }

        // Split at every position immediately after a tone digit (1–5).
        // This correctly handles neutral-tone syllables (no digit) as trailing chunks.
        $syllables = preg_split('/(?<=[1-5])/', strtolower($numeric), -1, PREG_SPLIT_NO_EMPTY);

        return implode($sep, array_map([self::class, 'markSyllable'], $syllables));
    }

    /**
     * Convert a single syllable like "biao3" → "biǎo" or "ba" (neutral) → "ba".
     */
    private static function markSyllable(string $syl): string
    {
        // Syllable with a tone digit at the end
        if (preg_match('/^([a-züv]+)([1-5])$/u', $syl, $m)) {
            $letters = $m[1];
            $tone    = (int) $m[2];
        } else {
            return $syl; // neutral tone — no mark
        }

        if ($tone === 5) {
            return $letters;
        }

        // Rule 1: a or e always takes the mark
        foreach (['a', 'e'] as $v) {
            if (str_contains($letters, $v)) {
                return str_replace($v, self::MARKS[$v][$tone - 1], $letters);
            }
        }

        // Rule 2: 'ou' → o takes the mark
        if (str_contains($letters, 'ou')) {
            return str_replace('o', self::MARKS['o'][$tone - 1], $letters);
        }

        // Rule 3: last vowel takes the mark
        for ($i = mb_strlen($letters) - 1; $i >= 0; $i--) {
            $char = mb_substr($letters, $i, 1);
            if (in_array($char, self::VOWELS, true)) {
                $mark = self::MARKS[$char][$tone - 1];
                return mb_substr($letters, 0, $i) . $mark . mb_substr($letters, $i + 1);
            }
        }

        return $letters; // fallback — no vowel found (shouldn't happen)
    }
}
