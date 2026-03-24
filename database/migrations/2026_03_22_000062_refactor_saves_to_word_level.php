<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refactor from sense-level saves to word-level saves.
 *
 * - user_saved_words   replaces  user_saved_senses
 * - collection_word    replaces  collection_sense
 * - user_saved_examples gains word_object_id (writings belong to word, tagged to sense)
 * - collection_test_answers gains word_object_id
 *
 * Old tables are kept temporarily for data migration, then dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. user_saved_words ──────────────────────────────────────────────
        Schema::create('user_saved_words', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('word_object_id')->constrained('word_objects')->cascadeOnDelete();
            $table->text('personal_note')->nullable();
            $table->timestamp('saved_at')->useCurrent();
            $table->timestamps();
            $table->primary(['user_id', 'word_object_id']);
        });

        // ── 2. collection_word ───────────────────────────────────────────────
        Schema::create('collection_word', function (Blueprint $table) {
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('word_object_id')->constrained('word_objects')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->unsignedTinyInteger('mastery_level')->default(0);
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();
            $table->primary(['collection_id', 'word_object_id']);
        });

        // ── 3. Add word_object_id to user_saved_examples ─────────────────────
        Schema::table('user_saved_examples', function (Blueprint $table) {
            $table->foreignId('word_object_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('word_objects')
                  ->nullOnDelete();
        });

        // ── 4. Add word_object_id to collection_test_answers ─────────────────
        Schema::table('collection_test_answers', function (Blueprint $table) {
            $table->foreignId('word_object_id')
                  ->nullable()
                  ->after('collection_test_id')
                  ->constrained('word_objects')
                  ->nullOnDelete();
        });

        // ── 5. Migrate existing data ─────────────────────────────────────────

        // Migrate user_saved_senses → user_saved_words
        DB::statement("
            INSERT INTO user_saved_words (user_id, word_object_id, personal_note, saved_at, created_at, updated_at)
            SELECT DISTINCT uss.user_id, ws.word_object_id, MAX(uss.personal_note), MIN(uss.saved_at), NOW(), NOW()
            FROM user_saved_senses uss
            JOIN word_senses ws ON ws.id = uss.word_sense_id
            GROUP BY uss.user_id, ws.word_object_id
            ON CONFLICT DO NOTHING
        ");

        // Migrate collection_sense → collection_word
        DB::statement("
            INSERT INTO collection_word (collection_id, word_object_id, sort_order, mastery_level, added_at, created_at, updated_at)
            SELECT DISTINCT cs.collection_id, ws.word_object_id, MIN(cs.sort_order), MAX(cs.mastery_level), MIN(cs.added_at), NOW(), NOW()
            FROM collection_sense cs
            JOIN word_senses ws ON ws.id = cs.word_sense_id
            GROUP BY cs.collection_id, ws.word_object_id
            ON CONFLICT DO NOTHING
        ");

        // Backfill word_object_id on user_saved_examples
        DB::statement("
            UPDATE user_saved_examples
            SET word_object_id = (
                SELECT ws.word_object_id
                FROM word_senses ws
                WHERE ws.id = user_saved_examples.word_sense_id
            )
            WHERE word_sense_id IS NOT NULL
        ");

        // Backfill word_object_id on collection_test_answers
        DB::statement("
            UPDATE collection_test_answers
            SET word_object_id = (
                SELECT ws.word_object_id
                FROM word_senses ws
                WHERE ws.id = collection_test_answers.word_sense_id
            )
            WHERE word_sense_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('collection_test_answers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('word_object_id');
        });
        Schema::table('user_saved_examples', function (Blueprint $table) {
            $table->dropConstrainedForeignId('word_object_id');
        });
        Schema::dropIfExists('collection_word');
        Schema::dropIfExists('user_saved_words');
    }
};
