<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Collocations are defined at sense level but resolve to a word_object —
        // the character-level pairing, not a specific sense. This is the accepted
        // limitation: collocations in Chinese are typically word-level pairings.
        Schema::create('word_sense_collocations', function (Blueprint $table) {
            $table->foreignId('word_sense_id')
                ->constrained('word_senses')
                ->cascadeOnDelete();
            $table->foreignId('collocation_word_object_id')
                ->constrained('word_objects')
                ->cascadeOnDelete();
            $table->primary(['word_sense_id', 'collocation_word_object_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_sense_collocations');
    }
};
