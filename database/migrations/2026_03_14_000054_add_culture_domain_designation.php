<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Adds 'culture' as a domain designation — distinct from 'society' (structures,
// populations, systems) because culture covers traditions, customs, heritage,
// artistic movements, and cultural exchange.
return new class extends Migration
{
    public function up(): void
    {
        // Find the domain attribute id
        $domainAttrId = DB::table('attributes')->where('slug', 'domain')->value('id');
        if (! $domainAttrId) {
            return;
        }

        // Find the domain group (if grouped) — use the same group as 'society'
        $societyDesig = DB::table('designations')->where('slug', 'society')->first();
        $groupId = $societyDesig?->designation_group_id;

        // Get max sort_order in this attribute
        $maxSort = DB::table('designations')->where('attribute_id', $domainAttrId)->max('sort_order') ?? 0;

        $designationId = DB::table('designations')->insertGetId([
            'attribute_id'         => $domainAttrId,
            'slug'                 => 'culture',
            'designation_group_id' => $groupId,
            'sort_order'           => $maxSort + 1,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // Add EN + ZH labels
        DB::table('designation_labels')->insert([
            [
                'designation_id' => $designationId,
                'language_id'    => 1, // EN
                'label'          => 'Culture',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'designation_id' => $designationId,
                'language_id'    => 2, // ZH
                'label'          => '文化',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }

    public function down(): void
    {
        $desig = DB::table('designations')->where('slug', 'culture')->first();
        if ($desig) {
            DB::table('designation_labels')->where('designation_id', $desig->id)->delete();
            DB::table('designations')->where('id', $desig->id)->delete();
        }
    }
};
