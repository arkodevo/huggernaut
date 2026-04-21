<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;
use App\Models\PronunciationSystem;
use App\Models\Designation;
use App\Models\WordObject;
use App\Models\WordPronunciation;
use App\Models\WordSense;
use App\Models\WordSenseDefinition;

class TocflBand1Seeder extends Seeder
{
    public function run(): void
    {
        $enId      = Language::where('code', 'en')->value('id');
        $pinyinId  = PronunciationSystem::where('slug', 'pinyin')->value('id');
        $tocflPrep = Designation::where('slug', 'tocfl-prep')->value('id');

        // [trad, simp|null, radical_id, strokes_trad, pinyin, pos_id, def_en]
        // Radical IDs = Kangxi radical numbers (our seeded IDs 1–214).
        // For multi-character words, radical/strokes = first character.
        // POS IDs: N=1 V=2 Adv=4 Conj=6 Det=8 Prn=9 Num=10 Vs=15
        $words = [
            // ── Pronouns ────────────────────────────────────────────────────
            ['我',     null,     62,  7, 'wǒ',        9, 'I; me'],
            ['你',     null,      9,  7, 'nǐ',        9, 'you (singular)'],
            ['他',     null,      9,  5, 'tā',        9, 'he; him'],
            ['她',     null,     38,  6, 'tā',        9, 'she; her'],
            ['我們',   '我们',   62,  7, 'wǒmen',     9, 'we; us'],
            ['你們',   '你们',    9,  7, 'nǐmen',     9, 'you (plural)'],
            ['他們',   '他们',    9,  5, 'tāmen',     9, 'they; them'],
            ['什麼',   '什么',    9,  4, 'shénme',    9, 'what'],
            ['誰',     '谁',    149, 15, 'shuí',      9, 'who'],
            ['自己',   null,    132,  6, 'zìjǐ',      9, 'oneself; self'],

            // ── Determiners ─────────────────────────────────────────────────
            ['這',     '这',    162, 16, 'zhè',       8, 'this'],
            ['那',     null,    163,  7, 'nà',        8, 'that'],
            ['哪',     null,     30,  9, 'nǎ',        8, 'which; what'],
            ['每',     null,     80,  7, 'měi',       8, 'every; each'],

            // ── Adverbs ──────────────────────────────────────────────────────
            ['也',     null,      5,  3, 'yě',        4, 'also; too; as well'],
            ['都',     null,    163, 10, 'dōu',       4, 'all; both; entirely'],
            ['很',     null,     60,  9, 'hěn',       4, 'very; quite'],
            ['不',     null,      1,  4, 'bù',        4, 'not; no'],
            ['沒',     '没',     85,  7, 'méi',       4, 'not have; did not (negative particle)'],
            ['怎麼',   '怎么',   61,  9, 'zěnme',     4, 'how; in what way'],
            ['為什麼', '为什么', 86, 12, 'wèishénme', 4, 'why; for what reason'],
            ['已經',   '已经',   49,  3, 'yǐjīng',   4, 'already'],
            ['非常',   null,    175,  8, 'fēicháng',  4, 'very; extremely'],
            ['再',     null,      1,  6, 'zài',       4, 'again; once more'],
            ['還',     '还',    162, 17, 'hái',       4, 'still; also; in addition'],

            // ── Numerals ─────────────────────────────────────────────────────
            ['一',     null,      1,  1, 'yī',       10, 'one; 1'],
            ['二',     null,      7,  2, 'èr',       10, 'two; 2'],
            ['三',     null,      1,  3, 'sān',      10, 'three; 3'],
            ['四',     null,     31,  5, 'sì',       10, 'four; 4'],
            ['五',     null,      7,  4, 'wǔ',       10, 'five; 5'],
            ['六',     null,      8,  4, 'liù',      10, 'six; 6'],
            ['七',     null,      1,  2, 'qī',       10, 'seven; 7'],
            ['八',     null,     12,  2, 'bā',       10, 'eight; 8'],
            ['九',     null,      5,  2, 'jiǔ',      10, 'nine; 9'],
            ['十',     null,     24,  2, 'shí',      10, 'ten; 10'],
            ['百',     null,    106,  6, 'bǎi',      10, 'hundred; 100'],
            ['千',     null,     24,  3, 'qiān',     10, 'thousand; 1,000'],
            ['多少',   null,     36,  6, 'duōshǎo',  10, 'how many; how much'],

            // ── Nouns ────────────────────────────────────────────────────────
            ['人',     null,      9,  2, 'rén',       1, 'person; people'],
            ['家',     null,     40, 10, 'jiā',       1, 'home; family; household'],
            ['國',     '国',     31, 11, 'guó',       1, 'country; nation; state'],
            ['年',     null,     51,  6, 'nián',      1, 'year'],
            ['月',     null,     74,  4, 'yuè',       1, 'month; moon'],
            ['天',     null,     37,  4, 'tiān',      1, 'day; sky; heaven'],
            ['朋友',   null,     74,  8, 'péngyǒu',   1, 'friend'],
            ['學生',   '学生',   39, 16, 'xuéshēng',  1, 'student; pupil'],
            ['老師',   '老师',  125,  6, 'lǎoshī',    1, 'teacher'],
            ['書',     '书',     73, 10, 'shū',       1, 'book'],
            ['水',     null,     85,  4, 'shuǐ',      1, 'water'],
            ['飯',     '饭',    184, 12, 'fàn',       1, 'rice; cooked rice; meal'],
            ['車',     '车',    159,  7, 'chē',       1, 'vehicle; car'],
            ['錢',     '钱',    167, 16, 'qián',      1, 'money; coin'],
            ['手機',   '手机',   64,  4, 'shǒujī',    1, 'mobile phone; cell phone'],
            ['學校',   '学校',   39, 16, 'xuéxiào',   1, 'school'],
            ['時間',   '时间',   72, 10, 'shíjiān',   1, 'time'],
            ['地方',   null,     32,  6, 'dìfāng',    1, 'place; location'],
            ['名字',   null,     30,  6, 'míngzì',    1, 'name'],
            ['今天',   null,      9,  4, 'jīntiān',   1, 'today'],
            ['明天',   null,     72,  8, 'míngtiān',  1, 'tomorrow'],
            ['昨天',   null,     72,  9, 'zuótiān',   1, 'yesterday'],

            // ── Verbs ────────────────────────────────────────────────────────
            ['是',     null,     72,  9, 'shì',       2, 'to be; is; are; am'],
            ['有',     null,     74,  6, 'yǒu',       2, 'to have; there is/are'],
            ['說',     '说',    149, 14, 'shuō',      2, 'to say; to speak; to talk'],
            ['看',     null,    109,  9, 'kàn',       2, 'to look; to watch; to see; to read'],
            ['吃',     null,     30,  6, 'chī',       2, 'to eat'],
            ['喝',     null,     30, 12, 'hē',        2, 'to drink'],
            ['來',     '来',     75,  8, 'lái',       2, 'to come'],
            ['去',     null,     28,  5, 'qù',        2, 'to go'],
            ['做',     null,      9, 11, 'zuò',       2, 'to do; to make'],
            ['知道',   null,    111,  8, 'zhīdào',    2, 'to know'],
            ['想',     null,     61, 13, 'xiǎng',     2, 'to think; to want; to miss'],
            ['喜歡',   '喜欢',   30, 12, 'xǐhuān',    2, 'to like; to be fond of'],
            ['愛',     '爱',     61, 13, 'ài',        2, 'to love'],
            ['住',     null,      9,  7, 'zhù',       2, 'to live; to reside; to stay'],
            ['叫',     null,     30,  5, 'jiào',      2, 'to be called; to call; to shout'],
            ['買',     '买',    154, 12, 'mǎi',       2, 'to buy; to purchase'],
            ['聽',     '听',    128, 22, 'tīng',      2, 'to listen; to hear'],
            ['寫',     '写',     14, 15, 'xiě',       2, 'to write'],
            ['走',     null,    156,  7, 'zǒu',       2, 'to walk; to go; to leave'],
            ['回',     null,     31,  6, 'huí',       2, 'to return; to go back'],
            ['問',     '问',    169, 11, 'wèn',       2, 'to ask; to inquire'],
            ['學',     '学',     39, 16, 'xué',       2, 'to study; to learn'],
            ['工作',   null,     48,  3, 'gōngzuò',   2, 'to work; work; job'],
            ['謝謝',   '谢谢',  149, 17, 'xièxiè',    2, 'to thank; thank you'],
            ['對不起', '对不起', 41, 14, 'duìbuqǐ',   2, "I'm sorry; to be sorry"],

            // ── Stative verbs (Vs) ───────────────────────────────────────────
            ['好',     null,     38,  6, 'hǎo',      15, 'good; well; fine'],
            ['大',     null,     37,  3, 'dà',       15, 'big; large; great'],
            ['小',     null,     42,  3, 'xiǎo',     15, 'small; little; young'],
            ['多',     null,     36,  6, 'duō',      15, 'many; much; more'],
            ['少',     null,     42,  4, 'shǎo',     15, 'few; little; less'],
            ['新',     null,     69, 13, 'xīn',      15, 'new'],
            ['快',     null,     61,  7, 'kuài',     15, 'fast; quick; soon'],
            ['高',     null,    189, 10, 'gāo',      15, 'tall; high; above average'],
            ['熱',     '热',     86, 15, 'rè',       15, 'hot; warm'],
            ['冷',     null,     15,  7, 'lěng',     15, 'cold; cool'],
            ['貴',     '贵',    154, 12, 'guì',      15, 'expensive; costly; precious'],
            ['便宜',   null,      9,  9, 'piányí',   15, 'cheap; inexpensive'],

            // ── Conjunctions ─────────────────────────────────────────────────
            ['和',     null,     30,  8, 'hé',        6, 'and; together with'],
            ['因為',   '因为',   31,  6, 'yīnwèi',    6, 'because; since'],
            ['所以',   null,     63,  8, 'suǒyǐ',     6, 'therefore; so; as a result'],
        ];

        foreach ($words as [$trad, $simp, $radicalId, $strokes, $pinyinText, $posId, $defEn]) {
            // Build smart_id by concatenating Unicode codepoints of all chars
            $smartId = implode('', array_map(
                fn ($c) => 'u' . strtolower(dechex(mb_ord($c, 'UTF-8'))),
                preg_split('//u', $trad, -1, PREG_SPLIT_NO_EMPTY)
            ));

            $word = WordObject::firstOrCreate(
                ['traditional' => $trad],
                [
                    'smart_id'    => $smartId,
                    'simplified'  => $simp,
                    'radical_id'  => $radicalId,
                    'strokes_trad'=> $strokes,
                    'status'      => 'published',
                ]
            );

            $pron = WordPronunciation::firstOrCreate(
                [
                    'word_object_id'          => $word->id,
                    'pronunciation_system_id' => $pinyinId,
                    'pronunciation_text'      => $pinyinText,
                ],
                ['is_primary' => true]
            );

            $sense = WordSense::firstOrCreate(
                ['word_object_id' => $word->id, 'pronunciation_id' => $pron->id],
                [
                    'status'         => 'published',
                    'tocfl_level_id' => $tocflPrep,
                ]
            );

            WordSenseDefinition::firstOrCreate(
                ['word_sense_id' => $sense->id, 'language_id' => $enId, 'pos_id' => $posId],
                ['definition_text' => $defEn, 'sort_order' => 1]
            );

            // word_sense_pos pivot retired 2026-04-21 — POS on definitions only.
        }

        $this->command->info('TOCFL Band 1: 100 words seeded.');
    }
}
