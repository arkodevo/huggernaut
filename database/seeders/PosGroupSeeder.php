<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\PosLabel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosGroupSeeder extends Seeder
{
    public function run(): void
    {
        $en   = Language::where('code', 'en')->value('id');
        $zhTW = Language::where('code', 'zh-TW')->value('id');

        // ── Groups ─────────────────────────────────────────────────────────────
        // Simplified learner-facing categories. Maps granular TOCFL POS to
        // traditional grammar terms (e.g. all Vs variants → Adjective).

        $groups = [
            ['slug' => 'noun',       'sort_order' => 1,  'en' => 'Noun',         'zh' => '名詞'],
            ['slug' => 'verb',       'sort_order' => 2,  'en' => 'Verb',         'zh' => '動詞'],
            ['slug' => 'adjective',  'sort_order' => 3,  'en' => 'Adjective',    'zh' => '形容詞'],
            ['slug' => 'adverb',     'sort_order' => 4,  'en' => 'Adverb',       'zh' => '副詞'],
            ['slug' => 'measure',    'sort_order' => 5,  'en' => 'Measure Word', 'zh' => '量詞'],
            ['slug' => 'particle',   'sort_order' => 6,  'en' => 'Particle',     'zh' => '助詞'],
            ['slug' => 'function',   'sort_order' => 7,  'en' => 'Function Word','zh' => '功能詞'],
            ['slug' => 'expression', 'sort_order' => 8,  'en' => 'Expression',   'zh' => '表達語'],
        ];

        foreach ($groups as $data) {
            $group = \App\Models\PosGroup::firstOrCreate(
                ['slug' => $data['slug']],
                ['sort_order' => $data['sort_order']]
            );

            \App\Models\PosGroupLabel::updateOrCreate(
                ['pos_group_id' => $group->id, 'language_id' => $en],
                ['label' => $data['en']]
            );
            \App\Models\PosGroupLabel::updateOrCreate(
                ['pos_group_id' => $group->id, 'language_id' => $zhTW],
                ['label' => $data['zh']]
            );
        }

        // ── Assign group_id to pos_labels ──────────────────────────────────────

        $map = [
            'noun'       => ['N'],
            'verb'       => ['Vi', 'Vp', 'Vpsep', 'Vpt', 'Vst', 'Vaux', 'Vsep', 'Vpsep'],
            'adjective'  => ['Vs', 'Vsattr', 'Vspred', 'Vssep'],
            'adverb'     => ['Adv'],
            'measure'    => ['M'],
            'particle'   => ['Ptc'],
            'function'   => ['Prep', 'Conj', 'Det', 'Prn', 'Num'],
            'expression' => ['IE', 'Ph'],
            // V (parent grouping) intentionally left ungrouped (group_id = null)
        ];

        foreach ($map as $groupSlug => $posSlugs) {
            $groupId = \App\Models\PosGroup::where('slug', $groupSlug)->value('id');
            PosLabel::whereIn('slug', $posSlugs)->update(['group_id' => $groupId]);
        }
    }
}
