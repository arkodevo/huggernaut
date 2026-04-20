<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Normalized translations for user-authored writings.
//
// Mirrors the structure of word_sense_example_translations: learner writes
// in Chinese (stored on user_saved_examples.chinese_text), and the AI-verified
// / learner-supplied English rendering now lives here instead of the
// user_saved_examples.english_text scalar column.
//
// This is multilingual-native: future coverage languages (ja, ko, vi…) get a
// row keyed by language_id without schema change.
//
// This migration creates the table + backfills from the legacy column. A
// follow-up migration drops the column once all read/write sites are
// updated.

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_saved_example_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_saved_example_id')
                ->constrained('user_saved_examples')
                ->cascadeOnDelete();
            $table->foreignId('language_id')
                ->constrained('languages')
                ->cascadeOnDelete();
            $table->text('translation_text');
            $table->timestamps();

            $table->unique(['user_saved_example_id', 'language_id'], 'user_saved_example_lang_unique');
        });

        // Backfill — one row per user_saved_example that has english_text.
        $enLangId = DB::table('languages')->where('code', 'en')->value('id');
        if ($enLangId) {
            DB::statement("
                INSERT INTO user_saved_example_translations (user_saved_example_id, language_id, translation_text, created_at, updated_at)
                SELECT id, {$enLangId}, english_text, COALESCE(created_at, NOW()), COALESCE(updated_at, NOW())
                FROM user_saved_examples
                WHERE english_text IS NOT NULL AND english_text != ''
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_saved_example_translations');
    }
};
