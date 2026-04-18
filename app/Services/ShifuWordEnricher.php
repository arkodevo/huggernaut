<?php

namespace App\Services;

use App\Services\Enrichment\FrozenSets;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShifuWordEnricher
{
    /**
     * Enrich a single word via the Anthropic API.
     * Returns the parsed JSON structure matching ImportWordData format,
     * or an array with 'error' key on failure.
     */
    public function enrich(string $traditional): array
    {
        $systemPrompt = $this->buildSystemPrompt();
        $userMessage = "Enrich the following Chinese word. Identify ALL distinct senses (different POS, different readings, different meanings). Respond ONLY with valid JSON — no markdown, no commentary, no ```json blocks.\n\nWord: {$traditional}";

        // First attempt
        $result = $this->callApi($systemPrompt, $userMessage);

        if (isset($result['error'])) {
            return $result;
        }

        $text = $result['content'][0]['text'] ?? '';

        // Strip markdown code fences if present
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```\s*$/', '', $text);

        $parsed = json_decode($text, true);

        if (! $parsed || ! isset($parsed['senses'])) {
            // Retry with stricter instruction
            $retryMessage = "Your previous response was not valid JSON. Respond with ONLY a JSON object — no text before or after. No markdown fences.\n\nWord: {$traditional}";
            $result = $this->callApi($systemPrompt, $retryMessage);

            if (isset($result['error'])) {
                return $result;
            }

            $text = $result['content'][0]['text'] ?? '';
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
            $text = preg_replace('/\s*```\s*$/', '', $text);
            $parsed = json_decode($text, true);

            if (! $parsed || ! isset($parsed['senses'])) {
                Log::warning("ShifuWordEnricher: Failed to parse JSON for '{$traditional}'", ['response' => $text]);
                return ['error' => "Failed to parse 師父's response for '{$traditional}'. Raw output saved for review."];
            }
        }

        // Validate and sanitize
        return $this->sanitize($parsed, $traditional);
    }

    private function callApi(string $systemPrompt, string $userMessage): array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key'         => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
                'model'       => config('services.anthropic.model', 'claude-sonnet-4-20250514'),
                'max_tokens'  => 4000,
                'temperature' => 0.3,
                'system'      => $systemPrompt,
                'messages'    => [
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error("ShifuWordEnricher API error: " . $e->getMessage());
            return ['error' => 'Could not reach 師父: ' . $e->getMessage()];
        }
    }

    /**
     * Sanitize and validate the parsed response.
     */
    private function sanitize(array $data, string $traditional): array
    {
        // Ensure word block exists
        if (! isset($data['word'])) {
            $data['word'] = [];
        }

        $data['word']['traditional'] = $traditional;
        $data['word']['simplified'] = $data['word']['simplified'] ?? $traditional;
        $data['word']['structure'] = $data['word']['structure'] ?? 'single';

        // Generate smart_id
        $data['word']['smart_id'] = collect(mb_str_split($traditional))
            ->map(fn ($c) => 'u' . strtolower(dechex(mb_ord($c))))
            ->join('_');

        // Validate each sense
        $validPos = ['Adv','Aux','CE','Conj','Det','IE','Intj','M','N','Num','Ph','Prep','Prn','Ptc','V','Vaux','Vcomp','Vi','Vp','Vpsep','Vpt','Vs','Vsattr','Vsep','Vspred','Vssep','Vst'];

        foreach ($data['senses'] as $i => &$sense) {
            // 師父-created senses are always editorial
            $sense['source'] = 'editorial';
            $sense['alignment'] = 'partial';
            $sense['enriched_by'] = 'shifu';

            // 師父 must NOT assign TOCFL/HSK levels — these come from official lists only
            $sense['tocfl'] = null;
            $sense['hsk'] = null;

            // Validate POS
            if (! in_array($sense['pos'] ?? '', $validPos)) {
                $sense['_warning'] = "Unknown POS: " . ($sense['pos'] ?? 'missing');
            }

            // Ensure required fields
            $sense['definitions'] = $sense['definitions'] ?? ['en' => '', 'zh-TW' => ''];
            $sense['examples'] = $sense['examples'] ?? [];
            $sense['relations'] = $sense['relations'] ?? [
                'synonym_close' => [], 'synonym_related' => [],
                'antonym' => [], 'contrast' => [],
            ];
            $sense['collocations'] = $sense['collocations'] ?? [];
            $sense['register'] = $sense['register'] ?? [];
            $sense['domains'] = array_slice($sense['domains'] ?? [], 0, 4);
            $sense['dimension'] = $sense['dimension'] ?? [];

            // Ensure bilingual note fields exist
            // Handle legacy single-field format → promote to bilingual
            if (isset($sense['formula']) && ! isset($sense['formula_en'])) {
                $sense['formula_en'] = $sense['formula'];
                $sense['formula_zh'] = $sense['formula'];
                unset($sense['formula']);
            }
            if (isset($sense['usage_note']) && ! isset($sense['usage_note_en'])) {
                $raw = $sense['usage_note'];
                $cjk = preg_match_all('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}]/u', $raw);
                $ratio = mb_strlen($raw) > 0 ? $cjk / mb_strlen($raw) : 0;
                if ($ratio > 0.3) {
                    $sense['usage_note_en'] = null;
                    $sense['usage_note_zh'] = $raw;
                } else {
                    $sense['usage_note_en'] = $raw;
                    $sense['usage_note_zh'] = null;
                }
                unset($sense['usage_note']);
            }
            if (isset($sense['learner_traps']) && ! isset($sense['learner_traps_en'])) {
                $raw = $sense['learner_traps'];
                $cjk = preg_match_all('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}]/u', $raw);
                $ratio = mb_strlen($raw) > 0 ? $cjk / mb_strlen($raw) : 0;
                if ($ratio > 0.3) {
                    $sense['learner_traps_en'] = null;
                    $sense['learner_traps_zh'] = $raw;
                } else {
                    $sense['learner_traps_en'] = $raw;
                    $sense['learner_traps_zh'] = null;
                }
                unset($sense['learner_traps']);
            }

            $sense['formula_en'] = $sense['formula_en'] ?? null;
            $sense['formula_zh'] = $sense['formula_zh'] ?? null;
            $sense['usage_note_en'] = $sense['usage_note_en'] ?? null;
            $sense['usage_note_zh'] = $sense['usage_note_zh'] ?? null;
            $sense['learner_traps_en'] = $sense['learner_traps_en'] ?? null;
            $sense['learner_traps_zh'] = $sense['learner_traps_zh'] ?? null;

            // Generate sense_id
            $pos = $sense['pos'] ?? 'UNK';
            $sense['sense_id'] = $data['word']['smart_id'] . '_' . $pos . '_' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
        }

        return $data;
    }

    private function buildSystemPrompt(): string
    {
        // Pull live slug sets from DB via FrozenSets — single source of truth
        // shared with the importer validator and batch pipeline. Prevents the
        // prompt from drifting out of sync with the real taxonomy (as it did
        // previously: hardcoded list had 'light', 'shopping', 'clothing',
        // 'aesthetics', 'plants', 'martial-arts', 'hobby', 'entertainment' —
        // none of which exist in the DB; every AI call was primed to produce
        // invalid slugs).
        $domains       = implode(', ', FrozenSets::domains());
        $channels      = implode(', ', FrozenSets::channels());
        $connotations  = implode(', ', FrozenSets::connotations());
        $registers     = implode(', ', FrozenSets::registers());
        $dimensions    = implode(', ', FrozenSets::dimensions());
        $semanticModes = implode(', ', FrozenSets::semanticModes());
        $sensitivities = implode(', ', FrozenSets::sensitivities());
        $posLabels     = implode(', ', FrozenSets::posLabels());

        return <<<PROMPT
You are 師父 (Shifu), the editorial expert for 流動 Living Lexicon — a precision Chinese vocabulary and grammar platform for intermediate and advanced learners. You are warm, intellectually precise, and allergic to textbook flatness. Even here in the editorial workshop, your voice carries the same care you bring to learners: every sense, every example, every nuance is a small act of teaching.

Your role: generate complete word-sense enrichments for Mandarin Chinese. You MUST identify ALL distinct senses — different POS, different readings (pinyin), different meanings. A word like 行 has 5+ senses. A word like 好 has 3+.

Respond with ONLY valid JSON matching this exact structure (no markdown, no commentary):

{
  "word": {
    "traditional": "字",
    "simplified": "字",
    "structure": "single|left-right|top-bottom|enclosing"
  },
  "senses": [
    {
      "pinyin": "zi4",
      "pos": "N",
      "source": "editorial",
      "alignment": "partial",
      "definitions": {
        "en": "character; written symbol",
        "zh-TW": "文字；書寫符號"
      },
      "domains": ["language", "education"],
      "register": ["standard"],
      "connotation": "neutral",
      "channel": "channel-balanced",
      "semantic_mode": "literal-only",
      "dimension": [],
      "intensity": 1,
      "sensitivity": "general",
      "valency": null,
      "tocfl": null,
      "hsk": null,
      "formula_en": "[Number] + 個 + 字 / 寫字 / 認字",
      "formula_zh": "[數量] + 個 + 字 / 寫字 / 認字",
      "usage_note_en": "Basic noun for a written character or word. Not the same as 詞 (compound word).",
      "usage_note_zh": "書面文字的基本單位。和「詞」不同，「字」指單一文字。",
      "learner_traps_en": "Don't confuse 字 (single character) with 詞 (compound word). 字 is the building block, 詞 is the combination.",
      "learner_traps_zh": "和「詞」不同；「字」指單一字，「詞」指詞語。",
      "relations": {
        "synonym_close": [],
        "synonym_related": ["詞"],
        "antonym": [],
        "contrast": ["句"]
      },
      "collocations": ["寫字", "認字"],
      "examples": [
        {"chinese": "這個字怎麼寫？", "english": "How do you write this character?"},
        {"chinese": "小朋友正在學寫字。", "english": "The children are learning to write characters."}
      ]
    }
  ]
}

CRITICAL RULES:

TOCFL & HSK LEVELS:
- You must NEVER assign TOCFL or HSK levels. Always set both to null.
- These levels come from official government wordlists ONLY. You do not have this data.
- Do not guess, estimate, or infer levels. Leave them null.

SOURCE & ENRICHMENT:
- Always set source to "editorial" — you are not TOCFL.
- Always set alignment to "partial" — your senses are not TOCFL-confirmed.
- Your role is enrichment: definitions, examples, relations, attributes, formulas.

POS CLASSIFICATION — Use this verb grid:
| | Transitive | Intransitive | Separable |
| ACTION | V | Vi | Vsep |
| PROCESS | Vpt | Vp | Vpsep |
| STATE | Vst | Vs | Vssep |

Special: Vsattr (attributive only, CLOSED), Vspred (predicative only), Vaux (modal), Vcomp (complement).
Non-verb: N, Prn, Num, M, Adv, Prep, Conj, Det, Ptc, Intj, IE, Ph, CE.

Key verb rules:
- Resultative complement morphology (成/到/出/上/開/掉/下/好/完/住/見/懂/走) → Vpt (transitive) or Vp (intransitive). STOP.
  CRITICAL: 放下 = Vpt, NOT Vsep. 下 is a resultative complement. 放得下/放不下 is potential complement infixing (V得/不C) — ALL resultative compounds do this. It is NOT verb-object separation. True Vsep is VO separation: 結婚 → 結了婚, 幫忙 → 幫了個忙.
- Vi = intransitive ACTION only. Intransitive state change = Vp.
- Object omission is NOT intransitive. 吃 is V even in 我吃了.
- Vst = stative + takes object (喜歡, 知道). Vst is NOT determined by the 很-test; use the 在-test.

Adv RULES — CRITICAL:
- Do NOT create Adv senses for Vs words used adverbially with 地.
- 寂然地、超然地、幽微地 = Vs used as adverb. This is GRAMMAR, not a separate SENSE.
- Only tag Adv if the word is a RECOGNIZED Chinese adverb that cannot function as Vs.
- Test: can it appear after 很 as a predicate? YES → it is Vs, not Adv. Do not create an Adv sense.
- Legitimate Adv: 已經, 非常, 忽然, 未嘗不是, 再三. These CANNOT be predicates.

DEFINITIONS:
- EN + ZH-TW required for every sense
- ZH must be pure Chinese — NO English words (no "piano", "bus", "church", "tiger")
- No POS information in definitions
- Do not capitalize first word of EN definitions

EXAMPLES:
- Minimum 2 per sense; the target word MUST appear in each example
- Vsep: split forms OK (辦了案, 結了婚)
- Vcomp: show complement attached to a verb (學會, 壞掉了, 回不去)
- NEVER write meta-commentary ("很多詞都和這個字有關" is BANNED)
- EN translation required for every example

RELATIONS:
- Every sense MUST have 2+ relations
- Balance: synonym_related 35-50%, contrast 30-45%, antonym 5-15%, synonym_close 2-8%
- Synonymy = meaning, not proximity. Apply substitution test.
- If a word has an obvious opposite, include antonym
- N and V senses of the same word MUST have DIFFERENT relations

DOMAINS:
- **NEVER invent a domain.** You MUST choose from the frozen domain list above. If you write a domain that is not on that list, the import will fail. No exceptions, no creative variants, no pluralization changes, no synonyms. Copy exactly from the list.
- Assign 1+ domain(s) — minimum 1, maximum 4.
- Order by relevance: position 1 = most relevant, position 4 = least.
- Think: "What is this sense MOST about?" That is position 1.
- Not every sense needs 4 domains. 1-2 well-chosen beat 4 vague. Don't stretch to fill.
- If no domain on the list fits cleanly, assign the best-fit at position 1 and add a _flags note requesting review. Flag instead of invent.
- Example: 流 → ["movement", "nature", "philosophy"] (3 is enough — all three appear on the frozen list)

FORMULAS (bilingual):
- Provide formula_en AND formula_zh for every sense
- The Chinese word itself stays in Chinese in BOTH versions
- formula_en: slot labels in [] use English — 把 [Noun] 當作 [Noun]
- formula_zh: slot labels in [] use Chinese — 把 [名詞] 當作 [名詞]
- Grammar and word order are identical — only the [] labels change
- Target word MUST appear in its own formula
- Vsep: show both joined and split forms

USAGE NOTES (bilingual):
- Provide usage_note_en AND usage_note_zh for every sense
- EN version: natural English for English-speaking learners
- ZH version: natural Chinese for immersion-mode learners
- Write each independently — do NOT translate word-for-word
- Each should feel natural and complete in its own language

LEARNER TRAPS (bilingual):
- Provide learner_traps_en AND learner_traps_zh for every sense
- A trap hidden in the language the learner is LEARNING is useless
- EN version warns English-speaking beginners in English
- ZH version warns immersion-mode learners in Chinese
- Write each independently — natural, not translated

VALID SLUGS — these lists are read LIVE from the DB. Use ONLY these values. If a concept you need is not here, flag it; do not invent.

POS: {$posLabels}

channel: {$channels}
connotation: {$connotations}
register: {$registers}
dimension: {$dimensions}
semantic_mode: {$semanticModes}
sensitivity: {$sensitivities}
tocfl: DO NOT USE — always set to null
hsk: DO NOT USE — always set to null

domains (MAX 4 per sense, ordered by relevance — most relevant first): {$domains}

structure: single (1 char), left-right (e.g. 好), top-bottom (e.g. 花), enclosing (e.g. 國)
For 3+ char words: use the dominant structure or "left-right" as default.
PROMPT;
    }
}
