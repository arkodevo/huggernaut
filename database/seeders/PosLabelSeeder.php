<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\PosLabel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosLabelSeeder extends Seeder
{
    public function run(): void
    {
        $en   = Language::where('code', 'en')->value('id');
        $zhTW = Language::where('code', 'zh-TW')->value('id');

        // ── Top-level POS labels ────────────────────────────────────────────────
        //
        // Slugs follow TOCFL notation. V is a parent grouping label only;
        // verb subtypes (children of V) carry all sense data.
        // Ptc matches TOCFL source exactly (not Ptcl).

        $topLevel = [
            ['slug' => 'N',    'sort_order' => 1,  'en' => 'Noun',                 'zh' => '名詞'],
            ['slug' => 'V',    'sort_order' => 2,  'en' => 'Verb',                 'zh' => '動詞'],
            ['slug' => 'M',    'sort_order' => 3,  'en' => 'Measure Word',         'zh' => '量詞'],
            ['slug' => 'Adv',  'sort_order' => 4,  'en' => 'Adverb',               'zh' => '副詞'],
            ['slug' => 'Prep', 'sort_order' => 5,  'en' => 'Preposition',          'zh' => '介詞'],
            ['slug' => 'Conj', 'sort_order' => 6,  'en' => 'Conjunction',          'zh' => '連詞'],
            ['slug' => 'Ptc',  'sort_order' => 7,  'en' => 'Particle',             'zh' => '助詞'],
            ['slug' => 'Det',  'sort_order' => 8,  'en' => 'Determiner',           'zh' => '限定詞'],
            ['slug' => 'Prn',  'sort_order' => 9,  'en' => 'Pronoun',              'zh' => '代詞'],
            ['slug' => 'Num',  'sort_order' => 10, 'en' => 'Number',               'zh' => '數詞'],
            ['slug' => 'IE',   'sort_order' => 11, 'en' => 'Idiomatic Expression', 'zh' => '慣用語'],
            ['slug' => 'Ph',   'sort_order' => 12, 'en' => 'Phrase',               'zh' => '片語'],
            ['slug' => 'CE',   'sort_order' => 13, 'en' => 'Chengyu',              'zh' => '成語'],
        ];

        foreach ($topLevel as $data) {
            $pos = PosLabel::firstOrCreate(
                ['slug' => $data['slug']],
                ['sort_order' => $data['sort_order'], 'parent_id' => null]
            );

            $this->label($pos->id, $en,   $data['en']);
            $this->label($pos->id, $zhTW, $data['zh']);
        }

        // ── Verb subtypes (children of V) ──────────────────────────────────────
        // Canonical TOCFL verb taxonomy + Vt (traditional transitive, kept alongside Vp).
        // Hyphens removed from slugs; preserved in EN labels for legibility.

        $vId = PosLabel::where('slug', 'V')->value('id');

        $verbSubtypes = [
            ['slug' => 'Vi',      'sort_order' => 1,  'en' => 'Intransitive Verb',                          'zh' => '不及物動詞'],
            ['slug' => 'Vp',      'sort_order' => 2,  'en' => 'Process Verb (Intransitive)',                 'zh' => '過程不及物動詞'],
            ['slug' => 'Vpsep',   'sort_order' => 3,  'en' => 'Vp-sep / Separable Process Verb',            'zh' => '離合動態動詞'],
            ['slug' => 'Vpt',     'sort_order' => 4,  'en' => 'Process Verb (Transitive)',                  'zh' => '過程及物動詞'],
            ['slug' => 'Vs',      'sort_order' => 5,  'en' => 'Stative Verb',                               'zh' => '狀態動詞'],
            ['slug' => 'Vsattr',  'sort_order' => 6,  'en' => 'Vs-attr / Stative Verb (Attributive)',       'zh' => '限定狀態動詞'],
            ['slug' => 'Vspred',  'sort_order' => 7,  'en' => 'Vs-pred / Stative Verb (Predicative)',       'zh' => '謂語狀態動詞'],
            ['slug' => 'Vssep',   'sort_order' => 8,  'en' => 'Vs-sep / Separable Stative Verb',            'zh' => '離合狀態動詞'],
            ['slug' => 'Vst',     'sort_order' => 9,  'en' => 'State-Transitive Verb',                      'zh' => '狀態及物動詞'],
            ['slug' => 'Vaux',    'sort_order' => 10, 'en' => 'Auxiliary Verb',                             'zh' => '助動詞'],
            ['slug' => 'Vsep',    'sort_order' => 11, 'en' => 'V-sep / Separable Verb',                     'zh' => '離合詞'],
        ];

        foreach ($verbSubtypes as $data) {
            $pos = PosLabel::firstOrCreate(
                ['slug' => $data['slug']],
                ['sort_order' => $data['sort_order'], 'parent_id' => $vId]
            );

            $this->label($pos->id, $en,   $data['en']);
            $this->label($pos->id, $zhTW, $data['zh']);
        }
    }

    private function label(int $posId, int $langId, string $text): void
    {
        // pos_label_translations has a composite PK (pos_id, language_id) — no id column.
        // Use DB::table updateOrInsert which works correctly with composite keys.
        DB::table('pos_label_translations')->updateOrInsert(
            ['pos_id' => $posId, 'language_id' => $langId],
            ['label'  => $text, 'updated_at' => now()]
        );
    }
}
