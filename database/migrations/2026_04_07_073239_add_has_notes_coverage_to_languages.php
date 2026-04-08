<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->boolean('has_notes_coverage')->default(false)->after('code');
        });

        // EN and ZH-TW get notes coverage by default
        DB::table('languages')->where('code', 'en')->update(['has_notes_coverage' => true]);
        DB::table('languages')->where('code', 'zh-TW')->update(['has_notes_coverage' => true]);
    }

    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('has_notes_coverage');
        });
    }
};
