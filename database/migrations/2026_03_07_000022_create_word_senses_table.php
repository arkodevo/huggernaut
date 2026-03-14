<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The primary lexical unit. 行 = 5 word_senses across 2 pronunciations.
        //
        // Designation FKs here are for single-select spectrum attributes
        // (channel, connotation, semantic_mode, sensitivity, domain).
        //
        // Multi-select attributes (register, dimension) live in word_sense_designations pivot.
        //
        // intensity is a scalar (1–5), not a designation FK, because it is a number
        // not a category — it drives the flower icon directly.
        Schema::create('word_senses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_object_id')
                ->constrained('word_objects')
                ->cascadeOnDelete();
            $table->foreignId('pronunciation_id')
                ->constrained('word_pronunciations');

            // Single-select spectrum designations (direct FK — one value per sense)
            $table->foreignId('channel_id')
                ->nullable()->constrained('designations')->nullOnDelete();
            // spoken-only · spoken-dominant · fluid · written-dominant · written-only
            $table->foreignId('connotation_id')
                ->nullable()->constrained('designations')->nullOnDelete();
            // positive · positive-dominant · context-dependent · negative-dominant · negative
            $table->foreignId('semantic_mode_id')
                ->nullable()->constrained('designations')->nullOnDelete();
            // literal-only · literal-dominant · fluid · metaphorical-dominant · metaphorical-only
            $table->foreignId('sensitivity_id')
                ->nullable()->constrained('designations')->nullOnDelete();
            // general · mature · profanity · sexual · taboo
            $table->foreignId('domain_id')
                ->nullable()->constrained('designations')->nullOnDelete();
            // TOCFL task domain: 個人資料 · 教育 · 旅行 · etc.

            // Scalar attributes
            $table->unsignedTinyInteger('intensity')->nullable();    // 1–5 (flower icons 🌸→🌺)
            $table->unsignedTinyInteger('valency')->nullable();      // 0 intransitive · 1 transitive · 2 ditransitive

            // Editorial content
            $table->string('formula')->nullable();                   // [Subject] + 擴大 + [Object/Scope]
            $table->text('usage_note')->nullable();
            $table->text('learner_traps')->nullable();

            // Level frameworks — both always stored; UI shows one per users.level_framework.
            // Full designation FKs so Level gets the same taxonomy treatment as every other
            // attribute: i18n labels, icons, and filter chips work identically to Register etc.
            $table->foreignId('tocfl_level_id')
                ->nullable()->constrained('designations')->nullOnDelete();
            // prep · entry · basic · advanced · high · fluency (6 designations, moon-phase icons)
            $table->foreignId('hsk_level_id')
                ->nullable()->constrained('designations')->nullOnDelete();
            // hsk-1 · hsk-2 · hsk-3 · hsk-4 · hsk-5 · hsk-6 (6 designations, growth icons)

            $table->string('status', 16)->default('draft');          // draft · review · published
            $table->timestamps();

            $table->index(['word_object_id', 'status']);
            $table->index('tocfl_level_id');
            $table->index('hsk_level_id');
            $table->index('intensity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_senses');
    }
};
