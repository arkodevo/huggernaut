<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add text column
        Schema::table('word_sense_collocations', function (Blueprint $table) {
            $table->string('collocation_text', 64)->nullable()->after('word_sense_id');
        });

        // 2. Populate text from existing FK data
        DB::statement('
            UPDATE word_sense_collocations
            SET collocation_text = (
                SELECT traditional FROM word_objects
                WHERE word_objects.id = word_sense_collocations.collocation_word_object_id
            )
        ');

        // 3. Drop old FK and primary key, set new structure
        Schema::table('word_sense_collocations', function (Blueprint $table) {
            $table->dropPrimary(['word_sense_id', 'collocation_word_object_id']);
            $table->dropForeign(['collocation_word_object_id']);
            $table->dropColumn('collocation_word_object_id');
        });

        Schema::table('word_sense_collocations', function (Blueprint $table) {
            $table->string('collocation_text', 64)->nullable(false)->change();
            $table->primary(['word_sense_id', 'collocation_text']);
        });
    }

    public function down(): void
    {
        Schema::table('word_sense_collocations', function (Blueprint $table) {
            $table->dropPrimary(['word_sense_id', 'collocation_text']);
        });

        Schema::table('word_sense_collocations', function (Blueprint $table) {
            $table->foreignId('collocation_word_object_id')
                ->nullable()
                ->constrained('word_objects')
                ->cascadeOnDelete();
            $table->dropColumn('collocation_text');
        });
    }
};
