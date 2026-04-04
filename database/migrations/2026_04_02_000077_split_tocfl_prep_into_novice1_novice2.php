<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Split tocfl-prep (預備級) into tocfl-novice1 (準備級一級) and tocfl-novice2 (準備級二級).
 *
 * The TOCFL 華語八千詞 spreadsheet distinguishes Novice 1 and Novice 2 as separate levels.
 * Our DB previously collapsed both into a single tocfl-prep designation.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename existing tocfl-prep → tocfl-novice1
        DB::table('designations')
            ->where('slug', 'tocfl-prep')
            ->update(['slug' => 'tocfl-novice1']);

        // Update labels for novice1
        $novice1Id = DB::table('designations')->where('slug', 'tocfl-novice1')->value('id');

        DB::table('designation_labels')
            ->where('designation_id', $novice1Id)
            ->where('language_id', 1)
            ->update(['label' => 'Novice 1']);

        DB::table('designation_labels')
            ->where('designation_id', $novice1Id)
            ->where('language_id', 2)
            ->update(['label' => '準備級一級']);

        // 2. Create tocfl-novice2
        $attributeId = DB::table('designations')->where('slug', 'tocfl-novice1')->value('attribute_id');

        $novice2Id = DB::table('designations')->insertGetId([
            'attribute_id' => $attributeId,
            'slug'         => 'tocfl-novice2',
            'sort_order'   => 2,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Create labels for novice2
        DB::table('designation_labels')->insert([
            ['designation_id' => $novice2Id, 'language_id' => 1, 'label' => 'Novice 2', 'created_at' => now(), 'updated_at' => now()],
            ['designation_id' => $novice2Id, 'language_id' => 2, 'label' => '準備級二級', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Bump sort_order for all levels after novice1
        // novice1=1, novice2=2, entry=3, basic=4, advanced=5, high=6, fluency=7
        DB::table('designations')->where('slug', 'tocfl-entry')->update(['sort_order' => 3]);
        DB::table('designations')->where('slug', 'tocfl-basic')->update(['sort_order' => 4]);
        DB::table('designations')->where('slug', 'tocfl-advanced')->update(['sort_order' => 5]);
        DB::table('designations')->where('slug', 'tocfl-high')->update(['sort_order' => 6]);
        DB::table('designations')->where('slug', 'tocfl-fluency')->update(['sort_order' => 7]);
    }

    public function down(): void
    {
        // Merge novice2 senses back into novice1
        $novice1Id = DB::table('designations')->where('slug', 'tocfl-novice1')->value('id');
        $novice2Id = DB::table('designations')->where('slug', 'tocfl-novice2')->value('id');

        if ($novice2Id && $novice1Id) {
            DB::table('word_senses')
                ->where('tocfl_level_id', $novice2Id)
                ->update(['tocfl_level_id' => $novice1Id]);

            DB::table('designation_labels')->where('designation_id', $novice2Id)->delete();
            DB::table('designations')->where('id', $novice2Id)->delete();
        }

        // Rename back
        if ($novice1Id) {
            DB::table('designations')->where('id', $novice1Id)->update(['slug' => 'tocfl-prep']);
            DB::table('designation_labels')
                ->where('designation_id', $novice1Id)
                ->where('language_id', 1)
                ->update(['label' => 'Prep']);
            DB::table('designation_labels')
                ->where('designation_id', $novice1Id)
                ->where('language_id', 2)
                ->update(['label' => '預備級']);
        }

        // Restore sort orders
        DB::table('designations')->where('slug', 'tocfl-entry')->update(['sort_order' => 2]);
        DB::table('designations')->where('slug', 'tocfl-basic')->update(['sort_order' => 3]);
        DB::table('designations')->where('slug', 'tocfl-advanced')->update(['sort_order' => 4]);
        DB::table('designations')->where('slug', 'tocfl-high')->update(['sort_order' => 5]);
        DB::table('designations')->where('slug', 'tocfl-fluency')->update(['sort_order' => 6]);
    }
};
