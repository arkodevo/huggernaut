<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per v1.5 spec (§3C): POS and definition are inseparable — each definition
        // row carries its own pos_id. A sense with 3 POS roles has 3 definition rows
        // per language. word_sense_pos is the filter index derived from these rows.
        Schema::create('word_sense_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_sense_id')
                ->constrained('word_senses')
                ->cascadeOnDelete();
            $table->foreignId('language_id')
                ->constrained('languages');
            $table->foreignId('pos_id')
                ->constrained('pos_labels');
            $table->text('definition_text');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['word_sense_id', 'language_id']);
            $table->index(['word_sense_id', 'pos_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_sense_definitions');
    }
};
