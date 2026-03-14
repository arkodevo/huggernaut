<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\SenseRelationType;
use App\Models\SenseRelationTypeLabel;
use Illuminate\Database\Seeder;

class SenseRelationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $en   = Language::where('code', 'en')->value('id');
        $zhTW = Language::where('code', 'zh-TW')->value('id');

        // ── Semantic relations ─────────────────────────────────────────────────
        // synonym_close    — near-identical meaning, high substitutability
        // synonym_related  — same semantic neighborhood, distinct usage
        // antonym          — direct logical opposite
        // contrast         — contrasts in dimension without being a strict antonym
        // register_variant — same concept, different social/stylistic register
        //
        // ── Lexical-family relations ───────────────────────────────────────────
        // derivative       — morphologically derived form
        // family_member    — noun/adj/adv form; POS of related_sense carries detail
        // compound         — multi-character word containing this character
        $types = [
            ['slug' => 'synonym_close',    'sort_order' => 1, 'en' => 'Close',            'zh' => '近義詞（緊）'],
            ['slug' => 'synonym_related',  'sort_order' => 2, 'en' => 'Related',          'zh' => '近義詞（廣）'],
            ['slug' => 'antonym',          'sort_order' => 3, 'en' => 'Antonym',          'zh' => '反義詞'],
            ['slug' => 'contrast',         'sort_order' => 4, 'en' => 'Contrast',         'zh' => '對比詞'],
            ['slug' => 'register_variant', 'sort_order' => 5, 'en' => 'Register Variant', 'zh' => '語域變體'],
            ['slug' => 'derivative',       'sort_order' => 6, 'en' => 'Derivative',       'zh' => '衍生詞'],
            ['slug' => 'family_member',    'sort_order' => 7, 'en' => 'Family Member',    'zh' => '詞族成員'],
            ['slug' => 'compound',         'sort_order' => 8, 'en' => 'Compound',         'zh' => '複合詞'],
        ];

        foreach ($types as $data) {
            $type = SenseRelationType::updateOrCreate(
                ['slug' => $data['slug']],
                ['sort_order' => $data['sort_order']]
            );

            SenseRelationTypeLabel::updateOrCreate(
                ['relation_type_id' => $type->id, 'language_id' => $en],
                ['label' => $data['en']]
            );
            SenseRelationTypeLabel::updateOrCreate(
                ['relation_type_id' => $type->id, 'language_id' => $zhTW],
                ['label' => $data['zh']]
            );
        }
    }
}
