<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Unified typed relation system. Absorbs:
        //   semantic neighbors  → synonym_close / synonym_related / antonym / contrast / register_variant
        //   word family tree   → family_member (POS of related_sense = noun/adj/adv detail)
        //   compounds          → compound
        //   morphological      → derivative
        Schema::create('word_sense_relations', function (Blueprint $table) {
            $table->foreignId('word_sense_id')
                ->constrained('word_senses')
                ->cascadeOnDelete();
            $table->foreignId('related_sense_id')
                ->constrained('word_senses')
                ->cascadeOnDelete();
            $table->foreignId('relation_type_id')
                ->constrained('sense_relation_types');
            $table->primary(['word_sense_id', 'related_sense_id', 'relation_type_id']);
            $table->text('editorial_note')->nullable();
            // Only when derivable data isn't enough — e.g. cultural nuance, historical derivation
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_sense_relations');
    }
};
