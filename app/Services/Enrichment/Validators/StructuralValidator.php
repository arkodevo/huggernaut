<?php

namespace App\Services\Enrichment\Validators;

use App\Services\Enrichment\FrozenSets;

/**
 * StructuralValidator — per-sense enrichment rule sweep.
 *
 * Ports the 7-rule audit I've been running in Python against every
 * batch into a reusable PHP service. Any caller can now enforce the
 * same discipline: the importer (already uses FrozenSets directly),
 * the admin word/sense edit form, 師父's self-check before returning,
 * or a future SenseAuditor orchestrator.
 *
 * Returns a structured result:
 *   [
 *     'blockers' => [ ['rule' => 'R3', 'message' => ...], ... ],  // import will fail
 *     'warnings' => [ ['rule' => 'R8', 'message' => ...], ... ],  // should review
 *   ]
 *
 * Usage:
 *   $result = StructuralValidator::validateSense($sense, $traditional);
 *   if ($result['blockers']) { ... }
 *
 * Rule numbering matches the audit discipline we use in batch reviews.
 */
class StructuralValidator
{
    /** POS slugs that are verbs (require integer valency). */
    private const VERB_POS = [
        'V', 'Vi', 'Vp', 'Vpt', 'Vpsep', 'Vs', 'Vsattr', 'Vspred',
        'Vssep', 'Vst', 'Vaux', 'Vsep', 'Vcomp',
    ];

    /** §3 banned meta-patterns in examples. */
    private const META_PATTERNS = [
        '很多詞都和', '這個詞', '這詞', '作動詞', '做動詞時',
        '這個字', '此詞', '此字', '這個用法',
    ];

    /** Note types required in bilingual form. */
    private const BILINGUAL_NOTE_FIELDS = [
        'formula_en', 'formula_zh',
        'usage_note_en', 'usage_note_zh',
        'learner_traps_en', 'learner_traps_zh',
    ];

    /** Valid word-level structure values (not in DB — schema constant). */
    private const STRUCTURE_VALUES = ['single', 'left-right', 'top-bottom', 'enclosing'];

    /** Valid alignment values (word-level and sense-level). */
    private const ALIGNMENT_VALUES = ['full', 'partial', 'disputed'];

    /** Valid source values. */
    private const SOURCE_VALUES = ['tocfl', 'editorial'];

    /** Valid relation type keys. */
    private const RELATION_KEYS = ['synonym_close', 'synonym_related', 'antonym', 'contrast'];

    /** Intensity range (1-5, inclusive). Null allowed for non-intensity-scaling POS. */
    private const INTENSITY_MIN = 1;
    private const INTENSITY_MAX = 5;

    /**
     * Validate a single sense. Returns blockers + warnings.
     *
     * @param array  $sense        The sense structure (as delivered to import or edited in admin)
     * @param string $traditional  The word's traditional form (for error messages)
     * @return array{blockers: array, warnings: array}
     */
    public static function validateSense(array $sense, string $traditional = 'unknown'): array
    {
        $blockers = [];
        $warnings = [];

        $pos = $sense['pos'] ?? null;
        $label = "{$traditional} " . ($pos ?: '?');

        // R1: Relations must be string arrays (no objects/dicts)
        foreach (($sense['relations'] ?? []) as $type => $items) {
            if (! is_array($items)) {
                $blockers[] = self::issue('R1', $label, "relations.{$type} must be an array, got " . gettype($items));
                continue;
            }
            foreach ($items as $i => $item) {
                if (! is_string($item)) {
                    $blockers[] = self::issue('R1', $label, "relations.{$type}[{$i}] must be a string, got " . gettype($item));
                }
            }
        }

        // R2: Domains must be in the frozen set, min 1, max 4
        $domains = $sense['domains'] ?? [];
        if (count($domains) < 1) {
            $blockers[] = self::issue('R2', $label, "must have at least 1 domain");
        }
        if (count($domains) > 4) {
            $warnings[] = self::issue('R2', $label, "has " . count($domains) . " domains (max 4 per guide)");
        }
        foreach ($domains as $d) {
            if (! FrozenSets::isValidDomain($d)) {
                $blockers[] = self::issue('R2', $label, "unknown domain '{$d}' — not in frozen set");
            }
        }

        // R3: Channel must be in frozen set (if present)
        $channel = $sense['channel'] ?? null;
        if ($channel !== null && $channel !== '' && ! FrozenSets::isValidChannel($channel)) {
            $blockers[] = self::issue('R3', $label, "unknown channel '{$channel}'");
        }

        // R4: Connotation must be in frozen set (if present)
        $connotation = $sense['connotation'] ?? null;
        if ($connotation !== null && $connotation !== '' && ! FrozenSets::isValidConnotation($connotation)) {
            $blockers[] = self::issue('R4', $label, "unknown connotation '{$connotation}'");
        }

        // R4a: Semantic mode must be in frozen set (if present)
        // Gap surfaced by 惠明 on L4-batch-05 rev2 (不平 had 'dim-fluid' — a dimension slug — in semantic_mode field).
        $semMode = $sense['semantic_mode'] ?? null;
        if ($semMode !== null && $semMode !== '' && ! in_array($semMode, FrozenSets::semanticModes(), true)) {
            $blockers[] = self::issue('R4a', $label, "unknown semantic_mode '{$semMode}' — valid: " . implode(', ', FrozenSets::semanticModes()));
        }

        // R4b: Sensitivity must be in frozen set (if present)
        $sens = $sense['sensitivity'] ?? null;
        if ($sens !== null && $sens !== '' && ! in_array($sens, FrozenSets::sensitivities(), true)) {
            $blockers[] = self::issue('R4b', $label, "unknown sensitivity '{$sens}' — valid: " . implode(', ', FrozenSets::sensitivities()));
        }

        // R5: Registers all in frozen set
        foreach (($sense['register'] ?? []) as $r) {
            if (! FrozenSets::isValidRegister($r)) {
                $blockers[] = self::issue('R5', $label, "unknown register '{$r}'");
            }
        }

        // R6: Dimensions all in settled set
        foreach (($sense['dimension'] ?? []) as $d) {
            if (! FrozenSets::isValidDimension($d)) {
                $blockers[] = self::issue('R6', $label, "unknown dimension '{$d}'");
            }
        }

        // R7: Valency must be integer for verb POS, null otherwise
        $valency = $sense['valency'] ?? null;
        if (in_array($pos, self::VERB_POS, true)) {
            if (! is_int($valency)) {
                $warnings[] = self::issue('R7', $label, "verb POS requires integer valency, got " . var_export($valency, true));
            }
        } else {
            if ($valency !== null && $valency !== '') {
                $warnings[] = self::issue('R7', $label, "non-verb POS should have null valency, got " . var_export($valency, true));
            }
        }

        // R8: §4/§9 coverage — 2+ relations OR 1 + _flags
        $relCounts = 0;
        foreach (['synonym_close', 'synonym_related', 'antonym', 'contrast'] as $t) {
            $relCounts += count($sense['relations'][$t] ?? []);
        }
        $hasFlags = ! empty($sense['_flags']);
        if ($relCounts < 2 && ! ($relCounts >= 1 && $hasFlags)) {
            $warnings[] = self::issue('R8', $label, "§9 under-covered: {$relCounts} relation(s), _flags=" . ($hasFlags ? 'yes' : 'no'));
        }

        // R9: Bilingual note fields — all 6 should be present
        foreach (self::BILINGUAL_NOTE_FIELDS as $field) {
            if (empty($sense[$field])) {
                $warnings[] = self::issue('R9', $label, "missing {$field}");
            }
        }

        // R10: Collocations ≥ 2
        $collCount = count($sense['collocations'] ?? []);
        if ($collCount < 2) {
            $warnings[] = self::issue('R10', $label, "only {$collCount} collocation(s) — minimum 2");
        }

        // R11: Examples ≥ 2
        $examples = $sense['examples'] ?? [];
        if (count($examples) < 2) {
            $blockers[] = self::issue('R11', $label, "only " . count($examples) . " example(s) — minimum 2");
        }

        // R12: Target word must appear in each Chinese example
        foreach ($examples as $i => $ex) {
            $cn = $ex['chinese'] ?? '';
            if (! str_contains($cn, $traditional)) {
                // Allow Vsep split forms and Vcomp attached forms — but warn for inspection
                $warnings[] = self::issue('R12', $label, "example " . ($i + 1) . " missing target word '{$traditional}': \"{$cn}\"");
            }
        }

        // R13: §3 banned meta-patterns in examples
        foreach ($examples as $i => $ex) {
            $cn = $ex['chinese'] ?? '';
            foreach (self::META_PATTERNS as $pattern) {
                if (str_contains($cn, $pattern)) {
                    $blockers[] = self::issue('R13', $label, "example " . ($i + 1) . " contains banned meta-pattern '{$pattern}': \"{$cn}\"");
                    break;
                }
            }
        }

        // R14: Definitions EN + ZH-TW both required
        if (empty($sense['definitions']['en'] ?? null)) {
            $blockers[] = self::issue('R14', $label, "missing definitions.en");
        }
        if (empty($sense['definitions']['zh-TW'] ?? null)) {
            $blockers[] = self::issue('R14', $label, "missing definitions.zh-TW");
        }

        // R15: Pinyin required
        if (empty($sense['pinyin'] ?? null)) {
            $blockers[] = self::issue('R15', $label, "missing pinyin");
        }

        // R16: POS required and valid
        if (! $pos) {
            $blockers[] = self::issue('R16', $label, "missing POS");
        } elseif (! FrozenSets::isValidPosLabel($pos)) {
            $blockers[] = self::issue('R16', $label, "unknown POS '{$pos}'");
        }

        // R18b: Sense-level alignment must be valid
        $senseAlignment = $sense['alignment'] ?? null;
        if ($senseAlignment !== null && $senseAlignment !== '' && ! in_array($senseAlignment, self::ALIGNMENT_VALUES, true)) {
            $blockers[] = self::issue('R18b', $label, "unknown alignment '{$senseAlignment}' — valid: " . implode(', ', self::ALIGNMENT_VALUES));
        }

        // R19: Source must be valid
        $source = $sense['source'] ?? null;
        if ($source !== null && $source !== '' && ! in_array($source, self::SOURCE_VALUES, true)) {
            $blockers[] = self::issue('R19', $label, "unknown source '{$source}' — valid: " . implode(', ', self::SOURCE_VALUES));
        }

        // R20: TOCFL level must be in frozen set (if present)
        $tocfl = $sense['tocfl'] ?? null;
        if ($tocfl !== null && $tocfl !== '' && ! in_array($tocfl, FrozenSets::tocflLevels(), true)) {
            $blockers[] = self::issue('R20', $label, "unknown tocfl level '{$tocfl}' — valid: " . implode(', ', FrozenSets::tocflLevels()));
        }

        // R21: HSK level must be in frozen set (if present)
        $hsk = $sense['hsk'] ?? null;
        if ($hsk !== null && $hsk !== '' && ! in_array($hsk, FrozenSets::hskLevels(), true)) {
            $blockers[] = self::issue('R21', $label, "unknown hsk level '{$hsk}' — valid: " . implode(', ', FrozenSets::hskLevels()));
        }

        // R22: Intensity must be integer 1-5 or null (Not Applicable).
        // Intensity is a TWO-STAGE editorial decision:
        //   Stage 1: Does intensity apply? (NO → Not Applicable, stored as null. STOP.)
        //   Stage 2 (only if Stage 1 = YES): Grade 1-5 on the strength scale.
        // Default-1 was the systemic gap surfaced 2026-04-19: 5,599 senses sat at
        // intensity=1 because the old single-stage framing ("1-5 or null") made null
        // feel like an empty field to skip past. The two-stage framing in the 師父 prompt
        // + template + ledger entry 13 makes Not Applicable an explicit first-class choice.
        // This rule is only the mechanical range check; the real discipline is editorial.
        $intensity = $sense['intensity'] ?? null;
        if ($intensity !== null) {
            if (! is_int($intensity)) {
                $blockers[] = self::issue('R22', $label, "intensity must be integer 1-5 or null, got " . var_export($intensity, true));
            } elseif ($intensity < self::INTENSITY_MIN || $intensity > self::INTENSITY_MAX) {
                $blockers[] = self::issue('R22', $label, "intensity {$intensity} out of range (must be " . self::INTENSITY_MIN . "-" . self::INTENSITY_MAX . " or null)");
            }
        }

        // R23: Relation keys must be valid (synonym_close / synonym_related / antonym / contrast)
        foreach (($sense['relations'] ?? []) as $key => $_) {
            if (! in_array($key, self::RELATION_KEYS, true)) {
                $blockers[] = self::issue('R23', $label, "unknown relation type '{$key}' — valid: " . implode(', ', self::RELATION_KEYS));
            }
        }

        return [
            'blockers' => $blockers,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate all senses in a word entry (for batch / import use).
     *
     * @param array $wordEntry  ['word' => [...], 'senses' => [...]]
     * @return array{blockers: array, warnings: array}
     */
    public static function validateWord(array $wordEntry): array
    {
        $traditional = $wordEntry['word']['traditional'] ?? 'unknown';
        $blockers = $warnings = [];

        // R17: Word-level structure must be valid
        $structure = $wordEntry['word']['structure'] ?? null;
        if ($structure !== null && $structure !== '' && ! in_array($structure, self::STRUCTURE_VALUES, true)) {
            $blockers[] = self::issue('R17', $traditional, "unknown structure '{$structure}' — valid: " . implode(', ', self::STRUCTURE_VALUES));
        }

        // R18a: Word-level alignment must be valid
        $wordAlignment = $wordEntry['word']['alignment'] ?? null;
        if ($wordAlignment !== null && $wordAlignment !== '' && ! in_array($wordAlignment, self::ALIGNMENT_VALUES, true)) {
            $blockers[] = self::issue('R18a', $traditional, "unknown word-level alignment '{$wordAlignment}' — valid: " . implode(', ', self::ALIGNMENT_VALUES));
        }

        foreach ($wordEntry['senses'] ?? [] as $sense) {
            $r = self::validateSense($sense, $traditional);
            $blockers = array_merge($blockers, $r['blockers']);
            $warnings = array_merge($warnings, $r['warnings']);
        }

        return ['blockers' => $blockers, 'warnings' => $warnings];
    }

    /**
     * Summary string — human-readable report.
     */
    public static function summarize(array $result): string
    {
        $nb = count($result['blockers']);
        $nw = count($result['warnings']);
        if (! $nb && ! $nw) return "✅ clean";
        $out = [];
        if ($nb) $out[] = "❌ {$nb} blocker" . ($nb === 1 ? '' : 's');
        if ($nw) $out[] = "⚠ {$nw} warning" . ($nw === 1 ? '' : 's');
        return implode(' · ', $out);
    }

    /**
     * Build a standard issue record.
     */
    private static function issue(string $rule, string $where, string $message): array
    {
        return [
            'rule'    => $rule,
            'where'   => $where,
            'message' => $message,
        ];
    }
}
