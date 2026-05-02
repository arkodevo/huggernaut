<?php
/**
 * Generate ~/Downloads/cowork/lens-disputes-rev0.json
 *
 * The 11 master-aligned senses that failed lens recheck on 2026-04-26.
 * After investigation under the three-key philosophical lens
 * (state-as-inhabited, substrate concepts, relational causality),
 * 絡一 + 光流 + 澄言 + 惠明 converged: master was right, our editorial
 * disputes were stacked English-trained reads.
 *
 * Each entry below names the lens key that applies and the framing
 * 澄言 should bring to usage_note_en + learner_traps_en. The previous
 * editorial sibling (alignment='partial', source='editorial') will be
 * archived from the DB once 澄言 hands rev1 back and 絡一 verdicts.
 *
 * Run: php database/scripts/gen_lens_disputes_rev0.php
 * Out: ~/Downloads/cowork/lens-disputes-rev0.json
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$langEn = (int) DB::table('languages')->where('code', 'en')->value('id');
$langZh = (int) DB::table('languages')->where('code', 'zh-TW')->value('id');

$designationSlug = DB::table('designations')->pluck('slug', 'id')->all();
$designationAttr = DB::table('designations as d')
    ->join('attributes as a', 'a.id', '=', 'd.attribute_id')
    ->pluck('a.slug', 'd.id')->all();
$posSlug = DB::table('pos_labels')->pluck('slug', 'id')->all();
$noteTypeSlug = DB::table('note_types')->pluck('slug', 'id')->all();
$relationSlug = DB::table('sense_relation_types')->pluck('slug', 'id')->all();

// ── The 11 lens-recheck disputes ────────────────────────────────────
// sense_id => [ lens_key, dispute_summary, framing_directive ]
$lensDisputes = [
    8362 => [
        'lens_key' => 1, // foundational note: not a state verb but a relational/distributive nominal
        'lens_key_label' => 'Distributive-nominal vs pronoun (Key 0 / framework call)',
        'editorial_sibling_id' => 12801,
        'dispute_summary' => 'We classed 人人 as Prn (pronoun, like English "everybody") because its referent feels pronominal. Master classes it as N. The lens corrective: 人人 is the noun 人 reduplicated for distributive force — "person-by-person" / "each and every person" — a noun-phrase, not a member of the closed pronoun class. It heads NPs, takes modifiers (天下人人), and behaves nominally. The English translation "everyone" pulls toward Prn; the Chinese morphology (N+N reduplication) pulls toward N.',
        'framing_directive' => 'In usage_note_en, name the AABB / 個個-人人-家家-處處 distributive-nominal family. Note that English-trained learners read 人人 as a pronoun by default — flag this as a category error: it is a reduplicated noun expressing distributive scope over the noun referent, not a pronoun substitute. learner_traps_en should call out the 人人 vs 大家 distinction (大家 is a collective Prn-like term; 人人 is N-distributive).',
    ],
    8396 => [
        'lens_key' => 1,
        'lens_key_label' => 'Key 1 — state as held capacity (受得了 = state of being able-to-bear)',
        'editorial_sibling_id' => 12803,
        'dispute_summary' => 'We split the resultative complement structure 受+得+了 out as a CE (complement expression) sibling because the surface form is a 動+得+補 frame. Master keeps it as Vst — a single lexicalised stative verb expressing the held capacity to endure. The lens corrective: this is Key 1 — endurance is a sustained state inhabited, not an event that culminates in a result. The 得 here lexicalises a capacity-state, not an active result-attainment. 受得了 sits with 忍, 愛, 信 as a held mode of being (capacity to bear), not with 看見 / 想到 (event-resultative).',
        'framing_directive' => 'In usage_note_en, frame 受得了 as a held capacity — "the state of being able to bear (X)" — not "the action of bearing (X) successfully." Cross-reference Key 1 (psychological/cognitive dispositions and states of capacity). learner_traps_en should warn against treating 受得了 as resultative-actional in spite of the 得 morphology; the negation 受不了 takes the same stative framing (state of not-being-able-to-bear), which confirms Vst over CE/Vpt.',
    ],
    8453 => [
        'lens_key' => 3,
        'lens_key_label' => 'Key 3 — relational causality (the cause is foregrounded; the experiencer receives)',
        'editorial_sibling_id' => 12799,
        'dispute_summary' => 'We split 嚇 into a Vp sibling reading "to become frightened" (process verb, state-change into fear) because English "get scared" conjures a process-verb intuition. Master keeps it as Vspred (stative-predicate) capturing 你嚇我 / 嚇到我 / 嚇死我 — the agentive-source-of-fear frame. The lens corrective: this is Key 3 — Chinese grammatically foregrounds the cause-of-the-experience (the source of the startle) rather than the experiencer-as-process-undergoer. 嚇 names "X exerts startle-force on Y," with the experiencer in the affected position, not "Y undergoes the process of becoming startled." The Vp split re-centred the experiencer in English-style process framing.',
        'framing_directive' => 'In usage_note_en, lead with the relational frame: "Read 嚇 as X startles Y / X exerts startle-force on Y. The cause is grammatically foregrounded as the subject; the experiencer Y is the affected object." Cross-reference 感動, 吸引, 引起, 讓+state. learner_traps_en should call out the English bias to say "I got scared by X" (centering self-as-experiencer) versus the Chinese 你嚇我 (centering you-as-cause). Key 3 worked-example territory.',
    ],
    2314 => [
        'lens_key' => 1,
        'lens_key_label' => 'Master-perspective + Vpt (transitive process verb is the right read)',
        'editorial_sibling_id' => 12802,
        'dispute_summary' => 'We added a V (general transitive verb) editorial sibling alongside master Vpt because we read 扔 as a simple action verb. Master keeps it as Vpt — a transitive process verb — capturing the unfolding-disposal arc (lift → release → object departs). The lens corrective: this is the master-perspective principle. Vpt is the more precise classification because 扔 names a process with an internal arc, not a punctual action; the V sibling was an English-trained simplification that flattened the process structure.',
        'framing_directive' => 'In usage_note_en, name the process arc: 扔 is "to throw / toss / discard" as a transitive process — release-and-departure, not an instantaneous transfer. Pair with the resultative cluster 扔掉 / 扔下 / 扔出去 to show the natural process-completion partners. learner_traps_en: contrast with 丟 (more colloquial, broader scope of "lose / drop / discard") and 投 (directional projection, often toward a target).',
    ],
    2441 => [
        'lens_key' => 1,
        'lens_key_label' => 'Master-perspective — V (general action verb) is the right level of abstraction',
        'editorial_sibling_id' => 12806,
        'dispute_summary' => 'We added a Vpt (transitive process verb) editorial sibling because the morphology 提+高 is a 動+結 resultative compound, and our default rule said "resultative compounds are Vpt." Master keeps it as plain V. The lens corrective: 提高 has lexicalised — the resultative reading (lift-something-to-be-high) is etymological, but the compound now functions as a single transitive action verb meaning "to raise / to improve." Applying the resultative-complement test mechanically misses the lexicalisation. Master is honoring the lexical reality, not the morphological surface.',
        'framing_directive' => 'In usage_note_en, name the lexicalisation explicitly: "提高 originated as a resultative compound (提 lift + 高 high) but has lexicalised into a single transitive action verb. Treat it as V, not as a productive resultative — you cannot insert aspect particles between 提 and 高 (✗提了高), confirming it is a fused unit." Pair with sibling raise-verbs: 提升, 增加, 改善 — show what V cluster it lives in. learner_traps_en: warn against trying to use 提高 with a non-abstract object (✗提高桌子 — for physical lifting use 抬 / 舉 / 升); 提高 takes abstract complements like 水準, 品質, 效率, 能力.',
    ],
    12789 => [
        'lens_key' => 1,
        'lens_key_label' => 'Key 1 — teaching as held disposition + master-perspective on Vst',
        'editorial_sibling_id' => 12798,
        'dispute_summary' => 'We added a V (action verb) editorial sibling for 教 jiao4 because "to teach" feels like an action in English. Master keeps it as Vst. The lens corrective: this is Key 1 plus master-perspective. 教 jiao4 in Chinese pedagogy names the sustained relational disposition of teaching (the held state of being-in-the-teaching-relation), not punctual transmission events. It pairs naturally with 學 (the held disposition of learning) — both are stative because both name modes of being one inhabits over time, not single acts. The V split read 教 as "perform a teaching action," which is English-bias misreading the Chinese stative orientation.',
        'framing_directive' => 'In usage_note_en, lead with Key 1 framing: "教 (jiao4) is stative in Chinese — it names the sustained disposition of teaching, the held mode of being-in-the-teaching-relation, not a punctual action of transmitting information. Pair it with 學 (xue2) — both are states one inhabits over time." Cross-reference 愛, 信, 知 as cohort. learner_traps_en: warn against the English-bias to read 教 as actional ("I taught the class"); the Chinese frame is "I am in the teaching relation with the class." Note also the 教 jiao1 sibling (Vst for "to make / let / cause," sense 11785) — the 4th-tone reading is the pedagogical state, the 1st-tone reading is the causative state.',
    ],
    12797 => [
        'lens_key' => 2,
        'lens_key_label' => 'Key 2 — substrate concept; Det is broader than English specifier',
        'editorial_sibling_id' => null, // 8338 (Vst anger) is a separate legitimate sense, not a 1:1 sibling
        'dispute_summary' => 'We disputed 氣 Det because we read Det through the English lens (pre-noun specifier like "this / some / which") and 氣 in compounds (氧氣, 怒氣, 香氣) follows rather than precedes the noun root. Master classes it as Det because in TOCFL\'s broader Chinese sense Det covers any noun-phrase modifier — including post-nominal category-determining suffixes that turn a root into a category member of the 氣 family. The lens corrective: this is Key 2 — 氣 is a substrate concept that resists boxing into one POS, and Key 0 master-perspective — Det in Chinese grammar is broader than the English Det class. The disputed sibling tagged Det as wrong because we mapped Chinese Det onto English Det rather than honoring the Chinese definition.',
        'framing_directive' => 'In usage_note_en, do the Key 2 work explicitly: "氣 is one of Chinese\'s foundational substrate concepts — breath-energy, atmosphere, mood, vital force — and resists boxing into a single POS. The same character takes multiple grammatical roles because no single role contains it: as a noun (天氣, 空氣, 氣味), as a category-determining suffix in compounds (氧氣 oxygen-gas, 怒氣 anger-energy, 香氣 fragrant-air, 蒸氣 steam-gas), and as a stative verb (我氣他 \'I\'m angry at him\'). The Det sense names the suffix-role: 氣 turns a root into a category-member of the gas/atmosphere/energetic-quality family." Note explicitly that Chinese Det covers post-nominal category-determining modifiers, not just pre-nominal specifiers. learner_traps_en: English-trained learners expect Det to mean "this/that/some/which" and miss Chinese Det\'s broader scope — flag this as the trap. Also note the classical/Daoist resonance: 氣 underneath all roles names the energetic field that animates things.',
    ],
    8404 => [
        'lens_key' => 1,
        'lens_key_label' => 'Key 1 — resignation as held state + master-perspective on Vs',
        'editorial_sibling_id' => 12804,
        'dispute_summary' => 'We added an IE (idiomatic expression) editorial sibling for 算了 because it reads as a fixed pragmatic phrase ("forget it / never mind"). Master keeps it as Vs. The lens corrective: this is Key 1 — 算了 names the held state of having-resolved-to-let-go, the inhabited disposition of resignation/dismissal. It is stative because the speaker is in the state of having-decided-to-stop-pursuing, not performing a discrete pragmatic act. The IE split treated 算了 as a frozen utterance; master treats it as a stative verb whose meaning is the resignation-state itself. Both readings make sense, but Vs is more ontologically precise.',
        'framing_directive' => 'In usage_note_en, lead with Key 1: "算了 names a held state — the inhabited disposition of having-let-go, having-resigned-the-matter. Approach it the way you would 忍 (the state of bearing): not as something one does but as a mode one shifts into." Note that the 了 here is the perfective of state-entry (one has now entered the let-go state), confirming the stative reading. learner_traps_en: warn against treating 算了 as merely a fixed exclamation; the Vs classification reflects that the phrase names a specific psychological-state move, with proper subject scope (我算了 / 你算了吧 work; the negation 不算了 means "not let-it-go" — a state-negation).',
    ],
    8349 => [
        'lens_key' => 1,
        'lens_key_label' => 'Master-perspective — V (general) over Vsep (split-able)',
        'editorial_sibling_id' => 12800,
        'dispute_summary' => 'We added a Vsep editorial sibling because 請假 has the surface 動+賓 morphology (請 ask-for + 假 leave) and Vsep tests can pass on it (請了三天假, 請過一次假). Master keeps it as plain V. The lens corrective: master-perspective — 請假 has lexicalised enough that the V classification is more lexically faithful than the morphological-surface Vsep classification. The split tests pass but produce stilted output for many uses; the dominant register treats 請假 as a fused verb. Note: 光流 has a known tendency to over-promote Vsep (caught by 惠明 on 問好 prior); this is the same failure mode.',
        'framing_directive' => 'In usage_note_en, acknowledge the morphology but uphold the V reading: "請假 is morphologically a 動+賓 (request + leave) compound and admits limited splitting (請了三天假, 請過一次假), but the dominant lexical register treats it as a fused transitive verb meaning \'to request leave / to take time off.\' Master classes it V — apply the V reading and treat the splittable patterns as a secondary register-marked option, not the primary frame." learner_traps_en: warn against over-applying the Vsep tests — the existence of split forms does not promote a verb to Vsep when the lexical register treats the compound as fused. Cross-reference the 問好 / 結婚 / 跳舞 cluster discussion of where lexicalisation overrides morphological splittability.',
    ],
    12796 => [
        'lens_key' => 2,
        'lens_key_label' => 'Key 2 — substrate concept; 起 takes multiple POS because no single one contains it',
        'editorial_sibling_id' => null, // 11289 (Vcomp) is a separate legitimate editorial sense
        'dispute_summary' => 'We disputed 起 N (case / incident / occurrence) because we read 起 as primarily verbal (to rise / to start). Master classes 起 N alongside its V (qi3 to rise), Ptc (resultative tendency), M (measure word for incidents), and Vcomp (editorial complement-form) senses. The lens corrective: this is Key 2 — 起 is a substrate concept of arising / origination / inception that resists boxing into one POS. The N sense names instances of arising/occurrence (一起事故 = one case of an incident, where 起 nominalises "an arising-event"). The disputed framing read 起 N as suspicious because we expected it to be only verbal; master honors that 起 carries instance-of-arising semantics that take a nominal grammatical role.',
        'framing_directive' => 'In usage_note_en, do the Key 2 work: "起 is a substrate concept of arising / inception / origination, and like 氣 it takes multiple POS because no single one contains it: as V (起床 to rise from bed), as Ptc/Vcomp (拿起 to pick up — resultative-tendency upward), as M (一起事故 one incident-instance), and as N (this sense — case / incident / occurrence). The N sense names the nominalised arising-event itself: \'an incident\' is literally \'an arising.\' Read all five senses as facets of the same arising-substrate concept." learner_traps_en: warn against treating the 起 senses as unrelated homonyms; they are aspects of one substrate concept, and seeing them as a unified family is the path to fluency with 起.',
    ],
    8410 => [
        'lens_key' => 1,
        'lens_key_label' => 'Master-perspective — V (general) over Vi (intransitive)',
        'editorial_sibling_id' => 12805,
        'dispute_summary' => 'We added a Vi (intransitive verb) editorial sibling because 躺 (to lie down) typically appears without a direct object, fitting the intransitive frame. Master keeps it as plain V. The lens corrective: master-perspective — V is the right level of abstraction because 躺 admits transitive-locative complements (躺床上 / 躺在沙發上 / 躺著看書) and aspectual extensions, and the Vi classification is overly narrow. The fact that 躺 frequently appears intransitively does not promote it to Vi when the V classification is more lexically faithful and accommodates the locative-complement uses.',
        'framing_directive' => 'In usage_note_en, name the V scope: "躺 is a general action verb (V), not strictly intransitive. While it commonly appears without a direct object (我躺一下), it readily takes locative complements (躺在床上 lie on the bed, 躺著看書 lie there reading) and aspectual extensions. The V classification covers this scope; restricting to Vi would miss the locative pattern." learner_traps_en: contrast 躺 with 睡 — 躺 names the body posture (horizontal, reclining) without sleep; 睡 names the sleep state. 躺著睡 (lying-and-sleeping) shows the productive distinction.',
    ],
];

// ── Pull the full sense data ────────────────────────────────────────
$senseIds = array_keys($lensDisputes);

$senseRows = DB::table('word_senses as ws')
    ->join('word_objects as wo', 'wo.id', '=', 'ws.word_object_id')
    ->leftJoin('word_pronunciations as wp', 'wp.id', '=', 'ws.pronunciation_id')
    ->whereIn('ws.id', $senseIds)
    ->select(
        'ws.id as sense_id', 'ws.word_object_id', 'ws.source', 'ws.alignment',
        'ws.channel_id', 'ws.connotation_id', 'ws.sensitivity_id',
        'ws.intensity', 'ws.valency',
        'ws.tocfl_level_id', 'ws.hsk_level_id',
        'ws.enriched_by',
        'wp.pronunciation_text as pinyin',
        'wo.smart_id', 'wo.traditional', 'wo.simplified', 'wo.structure'
    )
    ->orderBy('wo.smart_id')
    ->get();

// POS by sense (from word_sense_definitions)
$posBySense = [];
foreach (DB::table('word_sense_definitions')
    ->whereIn('word_sense_id', $senseIds)
    ->where('language_id', $langEn)
    ->orderBy('word_sense_id')->orderBy('sort_order')
    ->get() as $row) {
    if (! isset($posBySense[$row->word_sense_id])) {
        $posBySense[$row->word_sense_id] = $posSlug[$row->pos_id] ?? null;
    }
}

// Existing definitions (preserve as _previous_definition for context)
$defsBySense = [];
foreach (DB::table('word_sense_definitions')
    ->whereIn('word_sense_id', $senseIds)
    ->orderBy('sort_order')->get() as $row) {
    $key = $row->language_id === $langEn ? 'en' : ($row->language_id === $langZh ? 'zh-TW' : null);
    if ($key && ! isset($defsBySense[$row->word_sense_id][$key])) {
        $defsBySense[$row->word_sense_id][$key] = $row->definition_text;
    }
}

// Existing examples (preserve for context — Chengyan can keep, refresh, or replace)
$examplesBySense = [];
foreach (DB::table('word_sense_examples as e')
    ->leftJoin('word_sense_example_translations as t', function ($j) use ($langEn) {
        $j->on('t.word_sense_example_id', '=', 'e.id')
          ->where('t.language_id', '=', $langEn);
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

// Existing collocations
$collocationsBySense = [];
foreach (DB::table('word_sense_collocations')
    ->whereIn('word_sense_id', $senseIds)
    ->orderBy('collocation_text')->get() as $row) {
    $collocationsBySense[$row->word_sense_id][] = $row->collocation_text;
}

// Existing relations
$relationsBySense = [];
foreach (DB::table('word_sense_relations')
    ->whereIn('word_sense_id', $senseIds)->get() as $row) {
    $type = $relationSlug[$row->relation_type_id] ?? null;
    if ($type) $relationsBySense[$row->word_sense_id][$type][] = $row->related_word_text;
}

// Existing notes
$notesBySense = [];
foreach (DB::table('word_sense_notes')
    ->whereIn('word_sense_id', $senseIds)->get() as $row) {
    $type = $noteTypeSlug[$row->note_type_id] ?? null;
    if (! $type) continue;
    $lang = $row->language_id === $langEn ? 'en' : ($row->language_id === $langZh ? 'zh' : null);
    if ($lang) $notesBySense[$row->word_sense_id][$lang][$type] = $row->content;
}

// ── Assemble entries ────────────────────────────────────────────────
$entries = [];
foreach ($senseRows as $row) {
    $sid = $row->sense_id;
    $disp = $lensDisputes[$sid];
    $pos = $posBySense[$sid] ?? null;
    $tocflSlug = $row->tocfl_level_id ? ($designationSlug[$row->tocfl_level_id] ?? null) : null;
    $hskSlug = $row->hsk_level_id ? ($designationSlug[$row->hsk_level_id] ?? null) : null;

    $entry = [
        'word' => [
            'smart_id' => $row->smart_id,
            'traditional' => $row->traditional,
            'simplified' => $row->simplified,
            'structure' => $row->structure,
        ],
        'senses' => [[
            'pinyin' => $row->pinyin,
            'pos' => $pos,
            'source' => 'tocfl', // re-affirming master-aligned classification
            'alignment' => 'full', // will be flipped from 'disputed' to 'full' after rev1 import
            'enriched_by' => 'chengyan',
            'definitions' => [
                'en' => '[lens-recheck — awaiting re-enrichment]',
                'zh-TW' => null,
            ],
            'domains' => [],
            'register' => [],
            'connotation' => null,
            'channel' => null,
            'dimension' => [],
            'intensity' => null,
            'sensitivity' => null,
            'valency' => null,
            'tocfl' => $tocflSlug,
            'hsk' => $hskSlug,
            'formula_en' => null,
            'formula_zh' => null,
            'usage_note_en' => null,
            'usage_note_zh' => null,
            'learner_traps_en' => null,
            'learner_traps_zh' => null,
            'relations' => [
                'synonym_close' => [],
                'synonym_related' => [],
                'antonym' => [],
                'contrast' => [],
            ],
            'collocations' => [],
            'examples' => [],
            '_db_sense_id' => (int) $sid,
            '_db_word_object_id' => (int) $row->word_object_id,
        ]],
        '_lens_recheck' => [
            'lens_key' => $disp['lens_key'],
            'lens_key_label' => $disp['lens_key_label'],
            'dispute_summary' => $disp['dispute_summary'],
            'framing_directive' => $disp['framing_directive'],
            'editorial_sibling_to_archive' => $disp['editorial_sibling_id'],
        ],
        '_previous_state' => [
            'definitions' => $defsBySense[$sid] ?? [],
            'examples' => $examplesBySense[$sid] ?? [],
            'collocations' => $collocationsBySense[$sid] ?? [],
            'relations' => $relationsBySense[$sid] ?? [],
            'notes' => $notesBySense[$sid] ?? [],
        ],
        '_batch' => 'lens-disputes',
        '_batch_sequence' => count($entries) + 1,
    ];
    $entries[] = $entry;
}

$header = [
    '_meta' => [
        'batch' => 'lens-disputes',
        'rev' => 0,
        'date' => date('Y-m-d'),
        'origin' => 'POS philosophical lens recheck (2026-04-26). 11 of 11 active disputes failed lens recheck. 絡一 + 光流 + 澄言 + 惠明 converged: master was right; the disputes were stacked English-trained reads.',
        'lens_keys' => [
            1 => 'State as inhabited — psychological/cognitive dispositions, sustained capacities, doctrinal/affective held conditions. (忍, 愛, 信, 知, 喜歡, 怕, 受得了, 算了, 教 jiao4)',
            2 => 'Substrate concepts that resist boxing — foundational notions (氣, 心, 性, 道, 起) take multiple POS because no single role contains them. The grammar follows the ontology.',
            3 => 'Relational causality — the cause of an experience is grammatically foregrounded; the experiencer receives. (你吸引我, 那感動了我, 你嚇我)',
        ],
        'meta_rule' => 'Investigate the framework before disputing. The 嚴格師父 self-correction (2026-04-26): consensus across all three reviewers does not equal correctness when all three share an English-trained lens bias. The check on stacked-bias is framework investigation, not another reviewer.',
        'companion_docs' => [
            'POS Reference Guide v2.2',
            'Enrichment Quality Guide v2.5',
            'memory: project_state_verb_lens.md (POS philosophical lens — Chinese grammar encodes ontology)',
        ],
        'workflow' => [
            'For each entry below: (a) read _lens_recheck.framing_directive and apply it to usage_note_en + learner_traps_en, (b) re-derive definitions, formula, examples, relations, collocations from scratch with the lens framing in mind, (c) you may keep good content from _previous_state.examples / .collocations if it survives the lens reframe — but treat them as candidates, not commitments.',
            'Drop the editorial-sibling reading entirely. The DB cleanup (archiving sibling sense ids listed in _lens_recheck.editorial_sibling_to_archive) is 絡一s call after rev1.',
            'After enrichment, the alignment field on the master sense will flip from disputed to full at import time. The disputed marker is being retired across the 11 senses simultaneously.',
        ],
    ],
];

$output = json_encode(
    array_merge([$header], $entries),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);

$outPath = getenv('HOME') . '/Downloads/cowork/lens-disputes-rev0.json';
file_put_contents($outPath, $output);
echo "Wrote {$outPath} (" . filesize($outPath) . " bytes, " . count($entries) . " senses)\n";
