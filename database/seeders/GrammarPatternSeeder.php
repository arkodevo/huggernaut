<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\GrammarPattern;
use App\Models\GrammarPatternGroup;
use App\Models\GrammarPatternGroupLabel;
use App\Models\GrammarPatternLabel;
use App\Models\GrammarPatternNote;
use App\Models\Language;
use Illuminate\Database\Seeder;

class GrammarPatternSeeder extends Seeder
{
    public function run(): void
    {
        $enId = Language::where('code', 'en')->value('id');
        $zhId = Language::where('code', 'zh-TW')->value('id');

        // ── TOCFL level designation IDs ──────────────────────────────────────
        $tocflEntry    = Designation::where('slug', 'tocfl-entry')->value('id');
        $tocflBasic    = Designation::where('slug', 'tocfl-basic')->value('id');
        $tocflAdvanced = Designation::where('slug', 'tocfl-advanced')->value('id');

        // HSK equivalents
        $hsk2 = Designation::where('slug', 'hsk-2')->value('id');
        $hsk3 = Designation::where('slug', 'hsk-3')->value('id');
        $hsk4 = Designation::where('slug', 'hsk-4')->value('id');

        // ══════════════════════════════════════════════════════════════════════
        // ── GROUPS ───────────────────────────────────────────────────────────
        // ══════════════════════════════════════════════════════════════════════

        $groups = [
            [
                'slug' => 'complement-patterns',
                'sort_order' => 1,
                'en_name' => 'Complement Patterns',
                'zh_name' => '補語句型',
                'en_desc' => 'Patterns using verbal or adjectival complements (degree, result, direction, potential)',
                'zh_desc' => '使用動詞或形容詞補語的句型（程度、結果、方向、可能）',
            ],
            [
                'slug' => 'sentence-patterns',
                'sort_order' => 2,
                'en_name' => 'Sentence Patterns',
                'zh_name' => '特殊句型',
                'en_desc' => 'Core sentence-level constructions that restructure word order or argument roles',
                'zh_desc' => '改變語序或論元角色的核心句型結構',
            ],
            [
                'slug' => 'question-patterns',
                'sort_order' => 3,
                'en_name' => 'Question Patterns',
                'zh_name' => '疑問句型',
                'en_desc' => 'Patterns for forming questions (A-not-A, particle, choice, rhetorical)',
                'zh_desc' => '形成疑問句的句型（正反問、語氣助詞、選擇、反問）',
            ],
            [
                'slug' => 'correlative-patterns',
                'sort_order' => 4,
                'en_name' => 'Correlative Patterns',
                'zh_name' => '關聯句型',
                'en_desc' => 'Paired connective structures expressing scope, exception, progression, or emphasis',
                'zh_desc' => '表達範圍、例外、漸進或強調的成對關聯結構',
            ],
            [
                'slug' => 'adverb-patterns',
                'sort_order' => 5,
                'en_name' => 'Adverb Patterns',
                'zh_name' => '副詞句型',
                'en_desc' => 'Patterns built around adverbs that control timing, sequence, or pragmatic force',
                'zh_desc' => '以副詞為核心，控制時序或語用力量的句型',
            ],
        ];

        $groupMap = [];

        foreach ($groups as $g) {
            $group = GrammarPatternGroup::create([
                'slug'       => $g['slug'],
                'sort_order' => $g['sort_order'],
            ]);

            GrammarPatternGroupLabel::create([
                'grammar_pattern_group_id' => $group->id,
                'language_id'              => $enId,
                'name'                     => $g['en_name'],
                'description'              => $g['en_desc'],
            ]);

            GrammarPatternGroupLabel::create([
                'grammar_pattern_group_id' => $group->id,
                'language_id'              => $zhId,
                'name'                     => $g['zh_name'],
                'description'              => $g['zh_desc'],
            ]);

            $groupMap[$g['slug']] = $group->id;
        }

        // ══════════════════════════════════════════════════════════════════════
        // ── PATTERNS ─────────────────────────────────────────────────────────
        // ══════════════════════════════════════════════════════════════════════

        $patterns = [
            // ── 1. V得Vs-i (degree complement) ──────────────────────────────
            [
                'slug'             => 'v-de-degree-complement',
                'chinese_label'    => 'V得程度補語',
                'pattern_template' => '[V] 得 [Vs-i]',
                'group'            => 'complement-patterns',
                'tocfl'            => $tocflEntry,
                'hsk'              => $hsk2,
                'sort_order'       => 1,
                'en_name'          => 'Degree Complement with 得',
                'zh_name'          => 'V得程度補語',
                'en_short'         => 'Describes the degree or manner of an action using 得 + stative verb',
                'zh_short'         => '用「得」加狀態動詞描述動作的程度或方式',
                'en_formula'       => '[Verb] 得 [Stative Verb]',
                'zh_formula'       => '[動詞] 得 [狀態動詞]',
                'en_usage'         => 'The complement after 得 describes HOW the action is performed — its quality, speed, or degree. The main verb cannot take an object in this position; if an object is needed, state it first or use topic-comment structure: 中文，他說得很好。',
                'zh_usage'         => '「得」後面的補語描述動作的方式、品質或程度。主要動詞在此結構中不能帶賓語；如需賓語，需先提出或使用主題句：中文，他說得很好。',
                'en_traps'         => 'Common error: *他說中文得很好 — cannot place object between verb and 得. Must be: 他中文說得很好 or 他說中文說得很好 (verb copying). Also: 得 here is pronounced "de" (neutral tone), not "dé" (to get).',
                'zh_traps'         => '常見錯誤：*他說中文得很好——動詞和「得」之間不能放賓語。必須說：他中文說得很好，或他說中文說得很好（動詞重複）。注意：此處「得」讀輕聲 de，不是「得到」的 dé。',
            ],

            // ── 2. 是...的 (emphasis/focus) ─────────────────────────────────
            [
                'slug'             => 'shi-de-cleft',
                'chinese_label'    => '是...的句',
                'pattern_template' => '是 [Focus] 的',
                'group'            => 'sentence-patterns',
                'tocfl'            => $tocflEntry,
                'hsk'              => $hsk2,
                'sort_order'       => 2,
                'en_name'          => 'The 是...的 Focus Construction',
                'zh_name'          => '是...的句',
                'en_short'         => 'Highlights when, where, how, or why a known event happened',
                'zh_short'         => '強調已知事件的時間、地點、方式或原因',
                'en_formula'       => '[Subject] 是 [Time/Place/Manner] [Verb] 的',
                'zh_formula'       => '[主語] 是 [時間/地點/方式] [動詞] 的',
                'en_usage'         => 'Used for events that have already happened, where both speaker and listener know the event occurred. The 是...的 frame focuses on the CIRCUMSTANCE (when, where, how, who with), not the event itself. 是 can be omitted in casual speech, but 的 cannot.',
                'zh_usage'         => '用於已經發生的事件，說話者和聽話者都知道事件已發生。「是...的」框架聚焦於事件的情境（何時、何地、如何、與誰），而非事件本身。口語中「是」可省略，但「的」不能省。',
                'en_traps'         => 'Don\'t confuse with 的 as possessive or nominalizer. This 的 closes the focus frame. Common error: using 是...的 for events that haven\'t happened yet (*我是明天去的). Object placement: 的 can come before or after the object — 我是在台灣買的書 or 我是在台灣買書的, but the former is more natural.',
                'zh_traps'         => '不要與「的」的所有格或名詞化用法混淆。此處「的」用於關閉強調框架。常見錯誤：對未發生事件使用是...的（*我是明天去的）。賓語位置：「的」可在賓語前後——我是在台灣買的書或我是在台灣買書的，但前者更自然。',
            ],

            // ── 3. A不A (affirmative-negative question) ─────────────────────
            [
                'slug'             => 'a-not-a-question',
                'chinese_label'    => '正反問句',
                'pattern_template' => '[V/Vs] 不 [V/Vs]？',
                'group'            => 'question-patterns',
                'tocfl'            => $tocflEntry,
                'hsk'              => $hsk2,
                'sort_order'       => 3,
                'en_name'          => 'A-not-A Question',
                'zh_name'          => '正反問句',
                'en_short'         => 'Forms yes/no questions by juxtaposing affirmative and negative forms',
                'zh_short'         => '將肯定和否定形式並列來形成是非問句',
                'en_formula'       => '[Subject] [V/Adj] 不 [V/Adj] [Object]？',
                'zh_formula'       => '[主語] [動/形] 不 [動/形] [賓語]？',
                'en_usage'         => 'A neutral way to ask yes/no questions without any presupposition. More neutral than 嗎 questions, which can carry slight expectation. For 有, use 有沒有 (not *有不有). For monosyllabic verbs: 去不去, 好不好. For disyllabic: 喜歡不喜歡 or shortened 喜不喜歡.',
                'zh_usage'         => '不帶預設立場的中性是非問句。比「嗎」問句更中性，後者可能帶有輕微期待。「有」用「有沒有」（不能說*有不有）。單音節動詞：去不去、好不好。雙音節：喜歡不喜歡，或縮短為喜不喜歡。',
                'en_traps'         => 'Never use 不 with 有 in this pattern — *有不有 is ungrammatical. Must be 有沒有. For past events with 了, use V了沒有 (去了沒有？) not *去不去了. Cannot combine with 嗎 — *你去不去嗎？ is redundant.',
                'zh_traps'         => '此句型中不能用「不」搭配「有」——*有不有不合語法，必須用「有沒有」。過去式帶「了」時用 V了沒有（去了沒有？），不能說*去不去了。不能與「嗎」同時使用——*你去不去嗎？是多餘的。',
            ],

            // ── 4. 把 construction ──────────────────────────────────────────
            [
                'slug'             => 'ba-construction',
                'chinese_label'    => '把字句',
                'pattern_template' => '[Subj] 把 [O] [V-Complement]',
                'group'            => 'sentence-patterns',
                'tocfl'            => $tocflBasic,
                'hsk'              => $hsk3,
                'sort_order'       => 4,
                'en_name'          => 'The 把 Disposal Construction',
                'zh_name'          => '把字句',
                'en_short'         => 'Foregrounds the object and what happens to it — the speaker disposes of, handles, or affects it',
                'zh_short'         => '將賓語前置，強調對賓語的處置或影響',
                'en_formula'       => '[Subject] 把 [Object] [Verb + Complement/了/在/給/成]',
                'zh_formula'       => '[主語] 把 [賓語] [動詞＋補語/了/在/給/成]',
                'en_usage'         => 'The 把 construction signals that the subject deliberately acts on a specific, known object, causing a change in its state or position. The object must be definite (not *把一本書). The verb CANNOT stand alone — it must carry a complement, 了, directional, or resultative element. Think of it as: "take X and do something to it."',
                'zh_usage'         => '「把」字句表示主語刻意對特定已知賓語施加動作，造成其狀態或位置的改變。賓語必須是確定的（不能說*把一本書）。動詞不能單獨出現——必須帶補語、了、趨向或結果成分。可理解為：「拿X來做某事」。',
                'en_traps'         => 'The verb cannot stand alone: *我把門關 is incomplete → 我把門關了/關上/關好. The object must be specific: *把一個東西放好 is awkward → 把那個東西放好. Negation goes BEFORE 把: 我沒把書帶來 (not *我把書沒帶來). 不 also before 把: 我不把這件事告訴他.',
                'zh_traps'         => '動詞不能單獨使用：*我把門關不完整→我把門關了/關上/關好。賓語必須特定：*把一個東西放好不自然→把那個東西放好。否定詞放在「把」前：我沒把書帶來（不是*我把書沒帶來）。「不」也在「把」前：我不把這件事告訴他。',
            ],

            // ── 5. 被 passive ───────────────────────────────────────────────
            [
                'slug'             => 'bei-passive',
                'chinese_label'    => '被字句',
                'pattern_template' => '[Subj] 被 ([Agent]) [V-Complement]',
                'group'            => 'sentence-patterns',
                'tocfl'            => $tocflBasic,
                'hsk'              => $hsk3,
                'sort_order'       => 5,
                'en_name'          => 'The 被 Passive Construction',
                'zh_name'          => '被字句',
                'en_short'         => 'Expresses that the subject is affected by an action — often negatively or unexpectedly',
                'zh_short'         => '表示主語受到動作影響——通常帶有不利或意外的語義',
                'en_formula'       => '[Subject] 被 ([Agent]) [Verb + Complement/了]',
                'zh_formula'       => '[主語] 被 ([施事者]) [動詞＋補語/了]',
                'en_usage'         => 'Unlike English passive, 被 traditionally carries a negative or adverse connotation — something happened TO the subject that was undesirable. Modern usage is expanding to neutral contexts (被選為 "was selected as"), but the adversative sense remains dominant in spoken Chinese. The agent can be omitted: 書被借走了.',
                'zh_usage'         => '與英文被動不同，「被」傳統上帶有不利或負面含義——主語遭受了不好的事。現代用法已擴展到中性語境（被選為），但在口語中不利含義仍占主導。施事者可省略：書被借走了。',
                'en_traps'         => 'Like 把, the verb cannot stand alone: *他被罵 is incomplete → 他被罵了/被罵哭了. Don\'t overuse 被 for neutral passives where Chinese would just use active voice or topic-comment: "The book was published" → 書出版了 (not ?書被出版了). Register note: 叫 and 讓 are colloquial alternatives to 被.',
                'zh_traps'         => '和「把」一樣，動詞不能單獨使用：*他被罵不完整→他被罵了/被罵哭了。不要過度使用「被」來表達中性被動，中文常用主動語態或主題句：「書被出版了」不自然→書出版了。語域提示：「叫」和「讓」是「被」的口語替代。',
            ],

            // ── 6. 越來越 (progressive change) ──────────────────────────────
            [
                'slug'             => 'yue-lai-yue',
                'chinese_label'    => '越來越',
                'pattern_template' => '越來越 [Vs/V]',
                'group'            => 'correlative-patterns',
                'tocfl'            => $tocflBasic,
                'hsk'              => $hsk3,
                'sort_order'       => 6,
                'en_name'          => 'Progressive Change with 越來越',
                'zh_name'          => '越來越',
                'en_short'         => 'Expresses a trend of increasing change — "more and more"',
                'zh_short'         => '表達持續增加的變化趨勢——「越來越」',
                'en_formula'       => '[Subject] 越來越 [Vs/V]',
                'zh_formula'       => '[主語] 越來越 [狀態動詞/動詞]',
                'en_usage'         => 'Indicates a progressive change over time. Works naturally with stative verbs (越來越好, 越來越貴) and some action verbs expressing degree (越來越喜歡, 越來越了解). Related pattern: 越A越B (the more A, the more B) — a more complex correlative form.',
                'zh_usage'         => '表示隨時間推移的漸進變化。與狀態動詞搭配自然（越來越好、越來越貴），也可與部分表程度的動作動詞搭配（越來越喜歡、越來越了解）。相關句型：越A越B（越...越...）——更複雜的關聯形式。',
                'en_traps'         => 'Cannot negate directly: *越來越不好 is possible (越來越 + negative Vs), but *不越來越好 is wrong. The complement must express a quality that can intensify — *越來越是學生 makes no sense. Don\'t confuse with 越...越...: 越來越 = trend over time; 越A越B = proportional correlation.',
                'zh_traps'         => '不能直接否定：越來越不好可以（越來越+否定狀態詞），但*不越來越好不行。補語必須能表達可加強的性質——*越來越是學生不通。不要與「越...越...」混淆：越來越=隨時間的趨勢；越A越B=比例相關。',
            ],

            // ── 7. 除了...以外 (exception/addition) ─────────────────────────
            [
                'slug'             => 'chule-yiwai',
                'chinese_label'    => '除了...以外',
                'pattern_template' => '除了 [X] 以外，[Subj] 也/都 [VP]',
                'group'            => 'correlative-patterns',
                'tocfl'            => $tocflAdvanced,
                'hsk'              => $hsk4,
                'sort_order'       => 7,
                'en_name'          => 'Exception/Addition with 除了...以外',
                'zh_name'          => '除了...以外',
                'en_short'         => 'Expresses either "in addition to" (with 也/還) or "except for" (with 都)',
                'zh_short'         => '表達「除了...還/也」（追加）或「除了...都」（排除）',
                'en_formula'       => '除了 [X] (以外)，[Subject] 也/還/都 [VP]',
                'zh_formula'       => '除了 [X] (以外)，[主語] 也/還/都 [VP]',
                'en_usage'         => 'This pattern has two opposite meanings depending on the adverb: with 也/還 = "in addition to X, also..." (additive); with 都 = "except for X, everything/everyone..." (exclusive). 以外 is optional but adds formality. Context and the adverb choice completely determine the meaning.',
                'zh_usage'         => '此句型有兩個相反含義，取決於副詞：搭配也/還=「除了X，還...」（追加）；搭配都=「除了X，都...」（排除）。「以外」可省略但較正式。語境和副詞選擇完全決定含義。',
                'en_traps'         => 'The additive/exclusive distinction is the #1 trap: 除了他以外，大家都去了 = "except him, everyone went" (exclusive). 除了他以外，我也去了 = "in addition to him, I also went" (additive). Mixing up 也 and 都 reverses the entire meaning. 以外 can also be 之外 in written register.',
                'zh_traps'         => '追加/排除的區別是最大陷阱：除了他以外，大家都去了=「除了他，大家都去了」（排除）。除了他以外，我也去了=「除了他，我也去了」（追加）。混淆「也」和「都」會完全顛倒含義。書面語中「以外」也可用「之外」。',
            ],

            // ── 8. 就 vs 才 (sequence/emphasis) ─────────────────────────────
            [
                'slug'             => 'jiu-cai-contrast',
                'chinese_label'    => '就/才',
                'pattern_template' => '[Time/Condition] 就/才 [VP]',
                'group'            => 'adverb-patterns',
                'tocfl'            => $tocflAdvanced,
                'hsk'              => $hsk4,
                'sort_order'       => 8,
                'en_name'          => 'Sequence and Expectation: 就 vs 才',
                'zh_name'          => '就/才',
                'en_short'         => '就 = sooner/easier than expected; 才 = later/harder than expected',
                'zh_short'         => '就=比預期早/容易；才=比預期晚/困難',
                'en_formula'       => '[Subject] [Time/Condition] 就/才 [VP]',
                'zh_formula'       => '[主語] [時間/條件] 就/才 [VP]',
                'en_usage'         => '就 signals that something happened sooner, more easily, or more naturally than expected: 他八點就到了 ("he arrived as early as 8"). 才 signals the opposite — later, harder, or with more difficulty: 他十點才到 ("he didn\'t arrive until 10"). They encode the SPEAKER\'S subjective evaluation of timing, not objective fact.',
                'zh_usage'         => '「就」表示事情發生得比預期早、容易或自然：他八點就到了（「他八點就到了」）。「才」表示相反——比預期晚、困難：他十點才到（「他十點才到」）。它們表達說話者對時間的主觀評價，而非客觀事實。',
                'en_traps'         => 'Key asymmetry: 就 takes 了 (他就到了), but 才 typically does NOT take 了 for completed events (他才到, NOT *他才到了 — though regional variation exists). Also: 才 in conditional = "only if/then": 你來我才去 = "only if you come will I go." Don\'t confuse adverb 才 with 才 meaning "talent."',
                'zh_traps'         => '關鍵不對稱：「就」帶「了」（他就到了），但「才」表完成時通常不帶「了」（他才到，不說*他才到了——但有地區差異）。另外：條件句中「才」=「只有...才」：你來我才去=「只有你來我才去」。不要混淆副詞「才」與「才能/人才」的「才」。',
            ],

            // ── 9. 連...都/也 (even...still) ────────────────────────────────
            [
                'slug'             => 'lian-dou-ye',
                'chinese_label'    => '連...都/也',
                'pattern_template' => '連 [X] 都/也 [VP]',
                'group'            => 'correlative-patterns',
                'tocfl'            => $tocflAdvanced,
                'hsk'              => $hsk4,
                'sort_order'       => 9,
                'en_name'          => 'Emphatic Inclusion with 連...都/也',
                'zh_name'          => '連...都/也',
                'en_short'         => '"Even X..." — emphasizes an extreme or unexpected case to make a point',
                'zh_short'         => '「連X都/也...」——強調極端或意外情況以表達觀點',
                'en_formula'       => '連 [Extreme/Unexpected Case] 都/也 [VP]',
                'zh_formula'       => '連 [極端/意外情況] 都/也 [VP]',
                'en_usage'         => 'Highlights an extreme or unexpected case to imply that everything else follows naturally. 連老師都不知道 = "even the teacher doesn\'t know" (implying nobody does). The X after 連 should be the most extreme example on a scale. 都 and 也 are interchangeable here, though 都 emphasizes totality and 也 emphasizes addition.',
                'zh_usage'         => '強調一個極端或意外的例子，暗示其他情況自然也是如此。連老師都不知道=「連老師都不知道」（暗示沒人知道）。「連」後的X應是某個尺度上最極端的例子。「都」和「也」在此可互換，但「都」強調全面性，「也」強調追加。',
                'en_traps'         => '連 must precede the emphasized element, not the verb: *他連都不知道 is wrong → 連他都不知道 or 他連這個都不知道. The X after 連 should genuinely be extreme or surprising — using it with unremarkable elements sounds odd. In negative sentences, 連...都不/沒 is the standard form.',
                'zh_traps'         => '「連」必須放在被強調成分前，不是動詞前：*他連都不知道錯誤→連他都不知道或他連這個都不知道。「連」後的X應確實是極端或令人驚訝的——用在平凡的事物上會不自然。否定句中，「連...都不/沒」是標準形式。',
            ],
        ];

        foreach ($patterns as $p) {
            $pattern = GrammarPattern::create([
                'slug'                     => $p['slug'],
                'chinese_label'            => $p['chinese_label'],
                'pattern_template'         => $p['pattern_template'],
                'grammar_pattern_group_id' => $groupMap[$p['group']],
                'tocfl_level_id'           => $p['tocfl'],
                'hsk_level_id'             => $p['hsk'],
                'status'                   => 'draft',
                'sort_order'               => $p['sort_order'],
            ]);

            // Labels
            GrammarPatternLabel::create([
                'grammar_pattern_id' => $pattern->id,
                'language_id'        => $enId,
                'name'               => $p['en_name'],
                'short_description'  => $p['en_short'],
            ]);

            GrammarPatternLabel::create([
                'grammar_pattern_id' => $pattern->id,
                'language_id'        => $zhId,
                'name'               => $p['zh_name'],
                'short_description'  => $p['zh_short'],
            ]);

            // Notes
            GrammarPatternNote::create([
                'grammar_pattern_id' => $pattern->id,
                'language_id'        => $enId,
                'formula'            => $p['en_formula'],
                'usage_note'         => $p['en_usage'],
                'learner_traps'      => $p['en_traps'],
            ]);

            GrammarPatternNote::create([
                'grammar_pattern_id' => $pattern->id,
                'language_id'        => $zhId,
                'formula'            => $p['zh_formula'],
                'usage_note'         => $p['zh_usage'],
                'learner_traps'      => $p['zh_traps'],
            ]);
        }

        $this->command->info('Seeded 5 grammar pattern groups + 9 grammar patterns with bilingual labels + notes.');
    }
}
