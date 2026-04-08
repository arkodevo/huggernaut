<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_sense_example_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_sense_example_id')
                  ->constrained('word_sense_examples')
                  ->cascadeOnDelete();
            $table->foreignId('language_id')
                  ->constrained('languages')
                  ->cascadeOnDelete();
            $table->text('translation_text');
            $table->timestamps();

            $table->unique(['word_sense_example_id', 'language_id'], 'example_lang_unique');
        });

        // Migrate existing english_text into the new table
        $enId = DB::table('languages')->where('code', 'en')->value('id');

        if ($enId) {
            DB::statement("
                INSERT INTO word_sense_example_translations
                    (word_sense_example_id, language_id, translation_text, created_at, updated_at)
                SELECT id, {$enId}, english_text, COALESCE(created_at, NOW()), COALESCE(updated_at, NOW())
                FROM word_sense_examples
                WHERE english_text IS NOT NULL AND english_text != ''
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('word_sense_example_translations');
    }
};
