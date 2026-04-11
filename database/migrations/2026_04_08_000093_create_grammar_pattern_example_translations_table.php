<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grammar_pattern_example_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grammar_pattern_example_id')
                  ->constrained('grammar_pattern_examples')
                  ->cascadeOnDelete();
            $table->foreignId('language_id')
                  ->constrained('languages')
                  ->cascadeOnDelete();
            $table->text('translation_text');
            $table->timestamps();

            $table->unique(
                ['grammar_pattern_example_id', 'language_id'],
                'gp_ex_trans_lang_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grammar_pattern_example_translations');
    }
};
