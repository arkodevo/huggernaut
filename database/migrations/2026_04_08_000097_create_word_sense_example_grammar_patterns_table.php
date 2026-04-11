<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tags vocabulary examples with the grammar patterns they demonstrate
        Schema::create('word_sense_example_grammar_patterns', function (Blueprint $table) {
            $table->foreignId('word_sense_example_id')
                  ->constrained('word_sense_examples')
                  ->cascadeOnDelete();
            $table->foreignId('grammar_pattern_id')
                  ->constrained('grammar_patterns')
                  ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(
                ['word_sense_example_id', 'grammar_pattern_id'],
                'ws_ex_gp_primary'
            );
            $table->index('grammar_pattern_id'); // reverse: examples demonstrating this pattern
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_sense_example_grammar_patterns');
    }
};
