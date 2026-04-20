<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Repair dimension labels (2026-04-20).
//
// The dimension attribute's designation_labels had two problems:
//
// 1. SCRAMBLED ZH LABELS — the first 5 slugs (internal, external, abstract,
//    concrete, dim-fluid) had ZH labels that belonged to other linguistic
//    categories entirely (時間性, 體態性, 結果性, 語用性, 修辭性). This
//    appears to be a seeder error from an earlier iteration of the dimension
//    taxonomy.
//
// 2. MISSING LABELS — the 5 slugs added later (aspectual, grammatical,
//    spatial, pragmatic, temporal) had no EN or ZH labels at all.
//
// Target labels match the v2.3 Enrichment Quality Guide §3.5 definitions.

return new class extends Migration {
    public function up(): void
    {
        $enLangId = DB::table('languages')->where('code', 'en')->value('id');
        $zhLangId = DB::table('languages')->where('code', 'zh-TW')->value('id');

        // slug → [EN, ZH]
        $labels = [
            'internal'    => ['Internal',    '內在'],
            'external'    => ['External',    '外在'],
            'abstract'    => ['Abstract',    '抽象'],
            'concrete'    => ['Concrete',    '具體'],
            'dim-fluid'   => ['Fluid',       '流動'],
            'aspectual'   => ['Aspectual',   '體態'],
            'grammatical' => ['Grammatical', '語法'],
            'spatial'     => ['Spatial',     '空間'],
            'pragmatic'   => ['Pragmatic',   '語用'],
            'temporal'    => ['Temporal',    '時間'],
        ];

        $attrId = DB::table('attributes')->where('slug', 'dimension')->value('id');
        $dimDesignations = DB::table('designations')
            ->where('attribute_id', $attrId)
            ->pluck('id', 'slug');

        foreach ($labels as $slug => [$en, $zh]) {
            $designationId = $dimDesignations[$slug] ?? null;
            if (! $designationId) continue;

            if ($enLangId) {
                DB::table('designation_labels')->updateOrInsert(
                    ['designation_id' => $designationId, 'language_id' => $enLangId],
                    ['label' => $en, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            if ($zhLangId) {
                DB::table('designation_labels')->updateOrInsert(
                    ['designation_id' => $designationId, 'language_id' => $zhLangId],
                    ['label' => $zh, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        // Restore the pre-repair state (scrambled + missing) is not useful;
        // this is a pure correction. Leaving down() as no-op on the labels
        // themselves — there is no prior-state worth rolling back to.
    }
};
