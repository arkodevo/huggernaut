<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('word_objects', function (Blueprint $table) {
            // SUBTLEX-CH frequency data (Cai & Brysbaert, 2010)
            // subtlex_rank: position in SUBTLEX frequency list (1 = most frequent)
            // subtlex_ppm:  occurrences per million words (W/million)
            // subtlex_cd:   contextual diversity — % of films/shows the word appears in (W-CD%)
            $table->unsignedInteger('subtlex_rank')->nullable()->after('alignment');
            $table->decimal('subtlex_ppm', 10, 4)->nullable()->after('subtlex_rank');
            $table->decimal('subtlex_cd', 5, 2)->nullable()->after('subtlex_ppm');
        });
    }

    public function down(): void
    {
        Schema::table('word_objects', function (Blueprint $table) {
            $table->dropColumn(['subtlex_rank', 'subtlex_ppm', 'subtlex_cd']);
        });
    }
};
