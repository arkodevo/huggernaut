<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // ── Semantic relations (what this migration adds) ──────────────────────────
    //   synonym_close    — near-identical meaning, high substitutability
    //   synonym_related  — same semantic neighborhood, distinct usage
    //   antonym          — direct logical opposite (kept, sort_order updated)
    //   contrast         — contrasts in dimension without being a strict antonym
    //   register_variant — same concept, different social/stylistic register
    //
    // ── Lexical-family relations (kept unchanged except sort_order) ────────────
    //   derivative · family_member · compound
    //
    // ── Retired ───────────────────────────────────────────────────────────────
    //   synonym       → split into synonym_close + synonym_related
    //   lexical_cluster → absorbed by synonym_related
    //   see_also      → absorbed by contrast

    public function up(): void
    {
        // 1. Remove retired types (labels cascade via FK)
        DB::table('sense_relation_types')
            ->whereIn('slug', ['synonym', 'lexical_cluster', 'see_also'])
            ->delete();

        // 2. Insert new semantic relation types
        $now = now();
        DB::table('sense_relation_types')->insertOrIgnore([
            ['slug' => 'synonym_close',    'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'synonym_related',  'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'contrast',         'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'register_variant', 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 3. Update sort_orders on kept types
        DB::table('sense_relation_types')->where('slug', 'antonym')->update(['sort_order' => 3]);
        DB::table('sense_relation_types')->where('slug', 'derivative')->update(['sort_order' => 6]);
        DB::table('sense_relation_types')->where('slug', 'family_member')->update(['sort_order' => 7]);
        DB::table('sense_relation_types')->where('slug', 'compound')->update(['sort_order' => 8]);

        // 4. Upsert labels for all current types
        $en   = DB::table('languages')->where('code', 'en')->value('id');
        $zhTW = DB::table('languages')->where('code', 'zh-TW')->value('id');

        $labels = [
            'synonym_close'    => ['en' => 'Close',            'zh' => '近義詞（緊）'],
            'synonym_related'  => ['en' => 'Related',          'zh' => '近義詞（廣）'],
            'antonym'          => ['en' => 'Antonym',          'zh' => '反義詞'],
            'contrast'         => ['en' => 'Contrast',         'zh' => '對比詞'],
            'register_variant' => ['en' => 'Register Variant', 'zh' => '語域變體'],
            'derivative'       => ['en' => 'Derivative',       'zh' => '衍生詞'],
            'family_member'    => ['en' => 'Family Member',    'zh' => '詞族成員'],
            'compound'         => ['en' => 'Compound',         'zh' => '複合詞'],
        ];

        foreach ($labels as $slug => $pair) {
            $typeId = DB::table('sense_relation_types')->where('slug', $slug)->value('id');
            if (! $typeId) continue;

            DB::table('sense_relation_type_labels')->upsert(
                [
                    ['relation_type_id' => $typeId, 'language_id' => $en,   'label' => $pair['en'], 'created_at' => $now, 'updated_at' => $now],
                    ['relation_type_id' => $typeId, 'language_id' => $zhTW, 'label' => $pair['zh'], 'created_at' => $now, 'updated_at' => $now],
                ],
                ['relation_type_id', 'language_id'],
                ['label', 'updated_at']
            );
        }
    }

    public function down(): void
    {
        // Remove the new types (labels cascade)
        DB::table('sense_relation_types')
            ->whereIn('slug', ['synonym_close', 'synonym_related', 'contrast', 'register_variant'])
            ->delete();

        // Restore retired types with their original sort_orders
        $now = now();
        DB::table('sense_relation_types')->insertOrIgnore([
            ['slug' => 'synonym',         'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'lexical_cluster', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'see_also',        'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Restore sort_orders
        DB::table('sense_relation_types')->where('slug', 'antonym')->update(['sort_order' => 2]);
        DB::table('sense_relation_types')->where('slug', 'derivative')->update(['sort_order' => 4]);
        DB::table('sense_relation_types')->where('slug', 'family_member')->update(['sort_order' => 6]);
        DB::table('sense_relation_types')->where('slug', 'compound')->update(['sort_order' => 7]);

        // Restore labels for revived types
        $en   = DB::table('languages')->where('code', 'en')->value('id');
        $zhTW = DB::table('languages')->where('code', 'zh-TW')->value('id');

        $restored = [
            'synonym'         => ['en' => 'Synonym',        'zh' => '同義詞'],
            'lexical_cluster' => ['en' => 'Lexical Cluster', 'zh' => '詞族'],
            'see_also'        => ['en' => 'See Also',        'zh' => '參見'],
        ];

        foreach ($restored as $slug => $pair) {
            $typeId = DB::table('sense_relation_types')->where('slug', $slug)->value('id');
            if (! $typeId) continue;

            DB::table('sense_relation_type_labels')->upsert(
                [
                    ['relation_type_id' => $typeId, 'language_id' => $en,   'label' => $pair['en'], 'created_at' => $now, 'updated_at' => $now],
                    ['relation_type_id' => $typeId, 'language_id' => $zhTW, 'label' => $pair['zh'], 'created_at' => $now, 'updated_at' => $now],
                ],
                ['relation_type_id', 'language_id'],
                ['label', 'updated_at']
            );
        }
    }
};
