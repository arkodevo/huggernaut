<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grammar_pattern_word_senses', function (Blueprint $table) {
            $table->foreignId('grammar_pattern_id')
                  ->constrained('grammar_patterns')
                  ->cascadeOnDelete();
            $table->foreignId('word_sense_id')
                  ->constrained('word_senses')
                  ->cascadeOnDelete();
            $table->string('role', 16); // marker | key_vocab
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->text('editorial_note')->nullable();
            $table->timestamps();

            $table->primary(['grammar_pattern_id', 'word_sense_id']);
            $table->index('word_sense_id'); // reverse lookup: patterns using this sense
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grammar_pattern_word_senses');
    }
};
