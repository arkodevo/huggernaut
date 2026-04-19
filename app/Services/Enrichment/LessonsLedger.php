<?php

namespace App\Services\Enrichment;

/**
 * LessonsLedger — structured memory of failure patterns caught in
 * past batch audits. Each entry names a recurring pattern, gives
 * a concrete example from a real batch, and tells 師父 what NOT
 * to do.
 *
 * Entry criterion: a pattern qualifies for the ledger when it has
 * been caught in TWO OR MORE audit cycles (recurrence). A one-off
 * mistake is just a mistake; a pattern bitten twice is a class
 * that future enrichment should be primed against.
 *
 * The ledger is injected into 師父's enrichment prompt. Each call
 * sees every active lesson as a named anti-pattern.
 *
 * To add a new lesson, append to self::$lessons with:
 *   - id: short kebab-case slug
 *   - category: one of the tags below
 *   - title: what the pattern is (≤60 chars)
 *   - seen_in: which batches / revs
 *   - description: what the failure looks like
 *   - example: a real concrete instance
 *   - correct: what to do instead
 *
 * Categories: slug_invention, relation_typing, definition_quality,
 * example_quality, sense_architecture, language_isolation, reviewer_process.
 *
 * Future: back this with a qa_lessons table and admin UI for
 * adding/editing lessons as they surface.
 */
class LessonsLedger
{
    /** @var array<int, array<string, mixed>> */
    private static array $lessons = [
        [
            'id'          => 'domain-pluralization',
            'category'    => 'slug_invention',
            'title'       => 'Domain slug pluralization / variant invention',
            'seen_in'     => ['L4-batch-01 rev2', 'L4-batches-02..09 (propagated)'],
            'description' => 'Writing a domain slug that almost matches the frozen set but differs in plurality or variant form.',
            'example'     => '`material` (singular) written where DB has `materials`; `sport` where DB has `sports`; `culture-arts` where DB has `culture`.',
            'correct'     => 'Copy the slug exactly from the frozen domain list. No pluralization changes, no hyphenation variants. If the desired concept is not in the list, flag it — do not invent.',
        ],
        [
            'id'          => 'synonym-family-remapping',
            'category'    => 'slug_invention',
            'title'       => 'Slug remapping to synonyms not in frozen set',
            'seen_in'     => ['L4-batch-01 rev1', 'L4-batches-02..09 (propagated)'],
            'description' => 'Using a plausible-sounding English synonym instead of the DB-authoritative slug.',
            'example'     => '`relationships` (not in set) used for `social-relations`; `labor` used for `work`; `aesthetic` used for `appearance`; `perception` taxonomically distinct from `senses` here — check the frozen list.',
            'correct'     => 'Always verify against FrozenSets::domains(). The slug is the slug — not a translation of the concept.',
        ],
        [
            'id'          => 'channel-suffix-invention',
            'category'    => 'slug_invention',
            'title'       => 'Channel slug suffix invention (written-preferred)',
            'seen_in'     => ['L3-batch-12', 'L4-batch-01 rev1'],
            'description' => 'Inventing `-preferred` or similar suffix when the frozen set uses `-dominant`.',
            'example'     => '`written-preferred` / `spoken-preferred` — neither exists; the DB has `written-dominant` / `spoken-dominant`.',
            'correct'     => 'Channel is a 5-slug set: channel-balanced, written-dominant, spoken-dominant, written-only, spoken-only. Nothing else.',
        ],
        [
            'id'          => 'type-of-as-contrast',
            'category'    => 'relation_typing',
            'title'       => 'Type-of relationship typed as contrast',
            'seen_in'     => ['L4-batch-01 rev1/rev2 (罷工↔抗議)', 'L4-batch-02 rev1 (癌症↔腫瘤)'],
            'description' => 'Using the contrast slot for a type-of / is-a / category-member relationship, which is a semantic-map distinction, not a polarity.',
            'example'     => '罷工 contrast 抗議 — a strike is a TYPE OF protest. Not polar on any shared dimension. Belongs in learner_traps, not relations.',
            'correct'     => 'Apply the written-dimension test: shared dimension in ≤5 words. If you cannot articulate it, the pair is a trap, not a contrast.',
        ],
        [
            'id'          => 'middle-ground-demotion',
            'category'    => 'relation_typing',
            'title'       => 'Middle-ground demotion (contrast → related) that blurs',
            'seen_in'     => ['L4-batch-01 rev2 (哎呀↔唉)'],
            'description' => 'When a reviewer flags a weak contrast, demoting to synonym_related instead of removing can make the network blurrier.',
            'example'     => '哎呀/哎喲 ↔ 唉 — demoted to synonym_related made the family feel substitutable when it is not. Remove or restore cleanly; do not demote.',
            'correct'     => 'If contrast fails the dimension test, either articulate a cleaner dimension and keep as contrast, or remove the pair entirely. Don\'t settle for a weak middle category.',
        ],
        [
            'id'          => 'gloss-stacking-definition',
            'category'    => 'definition_quality',
            'title'       => 'ZH definition as semicolon-separated synonym list',
            'seen_in'     => ['L4-batch-01 rev1 (愛好 N, 案子, 安慰)', 'L4-batch-02 rev1 (same pattern)'],
            'description' => 'A ZH definition that is just three near-synonyms separated by semicolons, adding no context, mechanism, or boundary.',
            'example'     => '愛好 N: "愛好；喜歡做的事；興趣" — starts with the headword, lists synonyms, no explanation.',
            'correct'     => 'L4+ definitions must explain. Add duration, mechanism, target, register, or boundary. A learner should get something the English gloss alone cannot give.',
        ],
        [
            'id'          => 'engineered-standalone-examples',
            'category'    => 'example_quality',
            'title'       => 'Stilted standalone examples for bound morphemes',
            'seen_in'     => ['L4-batch-01 rev1 (案)', 'L4-batch-02 rev2 (棒 N)'],
            'description' => 'For characters that live in compounds (癌, 案, 棒, 保), inventing awkward standalone sentences to satisfy "target word appears in example."',
            'example'     => '棒 N: "一根棒" and "那支棒" read as engineered. 棒 standalone is rare; real usage is 球棒 / 警棒 / 棒球棒.',
            'correct'     => 'Use compound-position examples (本案, 肺癌, 棒球棒) and add a _flags note: "bound morpheme — examples use compound-position form to reflect natural usage."',
        ],
        [
            'id'          => 'meta-commentary-examples',
            'category'    => 'example_quality',
            'title'       => 'Meta-commentary in examples',
            'seen_in'     => ['L3-batch-12', 'L4-batch-01 rev1 (癌, 案)', 'L4-batch-02 rev1 (保, 寶, 報)'],
            'description' => 'Examples that describe the word instead of using it in natural context.',
            'example'     => '"很多詞都和X有關" — this is a meta-statement about vocabulary, not a use of the word.',
            'correct'     => 'Examples demonstrate usage. If you find yourself writing "這個詞..." or "作動詞時..." — stop, that is meta, rewrite.',
        ],
        [
            'id'          => 'en-text-in-zh-notes',
            'category'    => 'language_isolation',
            'title'       => 'English text bleeding into ZH note fields',
            'seen_in'     => ['L4-batch-02 rev1 (拌, 棒 M)', 'Huiming upstream pipeline artifact'],
            'description' => 'Pure English phrasing (e.g. "Verb meaning comfort; also noun-like as consolation") appearing in usage_note_zh or learner_traps_zh.',
            'example'     => '安慰 usage_note_zh = "Verb meaning comfort..." — ZH field contains EN text.',
            'correct'     => 'Each language\'s field is written natively in that language. Write EN and ZH independently — do not translate word-for-word. Scan ZH for 4+ consecutive ASCII letter runs pre-submit.',
        ],
        [
            'id'          => 'compressed-sense-mixing',
            'category'    => 'sense_architecture',
            'title'       => 'Two senses compressed into one',
            'seen_in'     => ['L4-batch-01 rev1 (拜拜, 熬)', 'L4-batch-02 rev1 (愛好, 包裝, 保障, 報導)'],
            'description' => 'Different POS, domains, or register compressed into a single sense, with usage_note saying "also used as..."',
            'example'     => '拜拜 as single Vi — but Taiwan usage has (a) farewell Vi + (b) worship at temple Vsep. 熬 as single Vpt — but cooking (simmer, domain food) and enduring (persist through hardship, domain emotion) are distinct.',
            'correct'     => 'Six split triggers in the SENSE-SPLIT TRIGGERS section. If usage_note starts with "also used as" — split.',
        ],
        [
            'id'          => 'reviewer-map-not-verified',
            'category'    => 'reviewer_process',
            'title'       => 'Reviewer-prescribed slug maps applied without DB verification',
            'seen_in'     => ['L4-batch-01 rev1 (my own error, propagated across 9 batches)'],
            'description' => 'When a reviewer\'s feedback prescribes a slug remapping, applying it without cross-checking against the DB-authoritative set can propagate an error across many senses.',
            'example'     => '光流\'s rev1 feedback on batch 01 prescribed `social-relations → relationships`, `work → labor` etc. — the prescribed targets were not in the DB. 澄言 applied faithfully, causing 148 slug failures across batches 02-09 before FrozenSets caught them.',
            'correct'     => 'Every slug in your output gets verified against FrozenSets::domains() (or the equivalent method) before submission. A reviewer\'s authority does not override the DB\'s authority.',
        ],
        [
            'id'          => 'category-as-synonymy',
            'category'    => 'relation_typing',
            'title'       => 'Category / sequence / hypernym forced into synonym_related',
            'seen_in'     => ['L4-batch-04 rev1→rev3 (丙, 菠菜)'],
            'description' => 'When no true lexical relation exists, downgrading to a category relation (hypernym/hyponym), a sequence member, or a taxonomic sibling is NOT synonym_related. These are category relations, distinct from lexical neighborhoods — they fail the substitution test.',
            'example'     => '丙 → synonym_related [甲, 乙, 丁] — these are ordering members of 天干, not synonyms. 丙 doesn\'t mean the same thing as 甲. · 菠菜 → synonym_related [青菜] — 青菜 is a hypernym (菠菜 is-a 青菜), category inclusion not meaning-equivalence.',
            'correct'     => 'Apply the substitution test: can X replace Y in 3 natural sentences without meaning loss? If no, it\'s not synonym_related. When only category/sequence/hypernym candidates exist, keep the §9 flag — don\'t force them into the synonym slot. This error pattern was introduced by a Claude-side reviewer push-back (光流) accepted by a Claude-side enricher (澄言). Cross-provider (OpenAI / 惠明) caught what same-provider reasoning missed.',
        ],
        [
            'id'          => 'intensity-default-1',
            'category'    => 'definition_quality',
            'title'       => 'Intensity silently defaulted to 1 — skipped the two-stage decision',
            'seen_in'     => ['Pre-2026-04-19 DB state (5,599 senses at intensity=1 out of ~7,600 total)'],
            'description' => 'Intensity is a TWO-STAGE editorial decision (Stage 1: does intensity apply? / Stage 2: if yes, grade 1-5). The original single-stage framing ("1-5 or null") made null feel like an empty field, so enrichers reflexively wrote 1 — treating 1 as the unconscious default. 5,599 senses sit at intensity=1 because Stage 1 was skipped entirely. Intensity 1 should mean "genuinely mild" (喜歡, 有點, 說) — not "the enricher didn\'t consider whether intensity applies."',
            'example'     => 'Prior state: 愛 (to love) at intensity=1 alongside 熱愛 (passionate love) at intensity=1 alongside 痴迷 (obsession) at intensity=1 — the scale is pedagogically broken because no distinction was made. Concrete nouns like 桌子 also sat at intensity=1 when Stage 1 should have answered "Not Applicable." Cross-POS: 很 (Adv "very") and 有點 (Adv "a bit") shouldn\'t sit at the same level, but often do in pre-spec data.',
            'correct'     => 'Apply the two-stage decision to every sense. STAGE 1: Does this sense scale on strength/force/degree? If NO → Not Applicable (null) with _flags note naming the reason (concrete noun / function word / measure word / etc.). STOP at Stage 1. If YES → continue. STAGE 2 (only when Stage 1 = YES): Grade on the 1-5 scale. Cross-POS calibration: 愛/喊/很/好/熱情 all sit at 3 — same force across POS. If choosing 1, note the reasoning in _flags ("mild-range action verb" / "subtle-expression Vs"). Not Applicable is a first-class editorial choice — treat it with the same dignity as picking 3. Full spec: memory/project_intensity_specification.md. Validator rule R22 enforces range 1-5 or null; the two-stage framing in the 師父 prompt + template enforces the explicit decision.',
        ],
    ];

    /**
     * All active ledger entries.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::$lessons;
    }

    /**
     * Filter by category tag.
     */
    public static function byCategory(string $category): array
    {
        return array_values(array_filter(
            self::$lessons,
            fn ($l) => $l['category'] === $category
        ));
    }

    /**
     * Render the ledger as a prompt section — named anti-patterns
     * with concrete examples from past audits.
     */
    public static function renderForPrompt(): string
    {
        $out = "LESSONS LEDGER — named anti-patterns from past audits. Every pattern here has been caught in 2+ batches. Check your output against each before submitting:\n";
        foreach (self::$lessons as $i => $l) {
            $n = $i + 1;
            $out .= "\n{$n}. **{$l['title']}** (category: {$l['category']})\n";
            $out .= "   Seen in: " . implode('; ', $l['seen_in']) . "\n";
            $out .= "   Pattern: {$l['description']}\n";
            $out .= "   Example: {$l['example']}\n";
            $out .= "   Do instead: {$l['correct']}\n";
        }
        return $out;
    }
}
