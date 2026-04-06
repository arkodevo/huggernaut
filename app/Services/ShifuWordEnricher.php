<?php

namespace App\Services;

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

            // Generate sense_id
            $pos = $sense['pos'] ?? 'UNK';
            $sense['sense_id'] = $data['word']['smart_id'] . '_' . $pos . '_' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
        }

        return $data;
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are 師父 (Shifu), the expert Chinese language enrichment system for Living Lexicon 流動.

Your role: Generate complete word sense enrichments for Mandarin Chinese. You MUST identify ALL distinct senses — different POS, different readings (pinyin), different meanings. A word like 行 has 5+ senses. A word like 好 has 3+.

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
      "formula": "寫字 / 認字",
      "usage_note": "Basic noun for written character or word.",
      "learner_traps": "和「詞」不同；「字」指單一字，「詞」指詞語。",
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
- Maximum 4 domains per sense. Choose carefully.
- Order by relevance: position 1 = most relevant domain, position 4 = least.
- Think: "What is this word MOST about?" That is position 1.
- Not every word needs 4 domains. 1-2 well-chosen domains are better than 4 vague ones.
- Example: 流 → ["movement", "nature", "philosophy"] (3 is enough)

FORMULAS:
- Target word MUST appear in its own formula
- Use Chinese with [Slot Labels]: 把 [Noun] 當作 [Noun]
- Vsep: show both joined and split forms

VALID SLUGS — use ONLY these values:

POS: Adv, Aux, CE, Conj, Det, IE, Intj, M, N, Num, Ph, Prep, Prn, Ptc, V, Vaux, Vcomp, Vi, Vp, Vpsep, Vpt, Vs, Vsattr, Vsep, Vspred, Vssep, Vst

channel: spoken-only, spoken-dominant, channel-balanced, written-dominant, written-only
connotation: positive, positive-dominant, neutral, negative-dominant, negative, context-dependent
register: literary, formal, standard, informal, colloquial, slang
dimension: internal, external, abstract, concrete, dim-fluid, aspectual, grammatical, spatial, pragmatic, temporal
semantic_mode: literal-only, literal-dominant, balanced, metaphorical-dominant, metaphorical-only
sensitivity: general, mature, profanity, sexual, taboo
tocfl: DO NOT USE — always set to null
hsk: DO NOT USE — always set to null

domains (MAX 4 per sense, ordered by relevance — most relevant first): light, daily-living, personal, emotion, cognition, education, perception, travel, personality, food, values, shopping, body, health, family, entertainment, medicine, work, identity, environment, social-relations, language, communication, clothing, aesthetics, housing, objects, safety, business, law, life, politics, society, nature, appearance, animals, plants, science, history, weather, materials, music, place, space, martial-arts, time, money, movement, transportation, art, sound, leisure, hobby, sports, technology, religion, media, philosophy, properties, energy, number-quantity, culture

structure: single (1 char), left-right (e.g. 好), top-bottom (e.g. 花), enclosing (e.g. 國)
For 3+ char words: use the dominant structure or "left-right" as default.
PROMPT;
    }
}
