<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grammar_pattern_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grammar_pattern_id')
                  ->constrained('grammar_patterns')
                  ->cascadeOnDelete();
            $table->foreignId('language_id')
                  ->constrained('languages');
            $table->string('formula')->nullable();
            $table->text('usage_note')->nullable();
            $table->text('learner_traps')->nullable();
            $table->timestamps();

            $table->unique(
                ['grammar_pattern_id', 'language_id'],
                'gp_notes_lang_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grammar_pattern_notes');
    }
};
