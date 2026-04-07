<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create the word_sense_notes table
        Schema::create('word_sense_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_sense_id')->constrained('word_senses')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages');
            $table->string('formula')->nullable();
            $table->text('usage_note')->nullable();
            $table->text('learner_traps')->nullable();
            $table->timestamps();

            $table->unique(['word_sense_id', 'language_id']);
        });

        // 2. Seed from existing data:
        //    - word_senses has canonical formula, usage_note, learner_traps
        //    - These were authored in Chinese (Huiming) or English (shifu)
        //    - Create rows for all existing languages where sense data exists

        // For every sense that has at least one of the three fields populated,
        // create a note row for each language the sense has definitions in.
        // The canonical content goes into the matching language row.
        // The other language row is created with NULLs (to be filled later).

        DB::statement("
            INSERT INTO word_sense_notes (word_sense_id, language_id, formula, usage_note, learner_traps, created_at, updated_at)
            SELECT DISTINCT
                ws.id,
                wsd.language_id,
                CASE WHEN wsd.language_id = 1 THEN ws.formula ELSE NULL END,
                CASE WHEN wsd.language_id = 1 THEN ws.usage_note ELSE NULL END,
                CASE WHEN wsd.language_id = 1 THEN ws.learner_traps ELSE NULL END,
                NOW(),
                NOW()
            FROM word_senses ws
            JOIN word_sense_definitions wsd ON wsd.word_sense_id = ws.id
            WHERE ws.formula IS NOT NULL
               OR ws.usage_note IS NOT NULL
               OR ws.learner_traps IS NOT NULL
            ON CONFLICT (word_sense_id, language_id) DO NOTHING
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('word_sense_notes');
    }
};
