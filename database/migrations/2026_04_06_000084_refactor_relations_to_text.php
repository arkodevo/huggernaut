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
        Schema::table('word_sense_relations', function (Blueprint $table) {
            $table->string('related_word_text', 32)->nullable()->after('word_sense_id');
        });

        // 2. Populate text from existing FK data
        DB::statement('
            UPDATE word_sense_relations
            SET related_word_text = (
                SELECT wo.traditional
                FROM word_senses ws
                JOIN word_objects wo ON wo.id = ws.word_object_id
                WHERE ws.id = word_sense_relations.related_sense_id
            )
        ');

        // 3. Drop old primary key and FK
        Schema::table('word_sense_relations', function (Blueprint $table) {
            $table->dropPrimary(['word_sense_id', 'related_sense_id', 'relation_type_id']);
            $table->dropForeign(['related_sense_id']);
            $table->dropColumn('related_sense_id');
        });

        // 4. Finalize: make text non-nullable, set new primary key
        Schema::table('word_sense_relations', function (Blueprint $table) {
            $table->string('related_word_text', 32)->nullable(false)->change();
            $table->primary(['word_sense_id', 'related_word_text', 'relation_type_id']);
        });
    }

    public function down(): void
    {
        Schema::table('word_sense_relations', function (Blueprint $table) {
            $table->dropPrimary(['word_sense_id', 'related_word_text', 'relation_type_id']);
        });

        Schema::table('word_sense_relations', function (Blueprint $table) {
            $table->foreignId('related_sense_id')
                ->nullable()
                ->constrained('word_senses')
                ->cascadeOnDelete();
            $table->dropColumn('related_word_text');
        });
    }
};
