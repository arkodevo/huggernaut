<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot linking learner writings to the grammar patterns 師父 spotted in
     * them. Created by Api\WorkshopController::saveExample() when the learner
     * saves a critiqued sentence — persists the chips shown on the saved card.
     */
    public function up(): void
    {
        Schema::create('user_saved_example_grammar_patterns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_saved_example_id')
                  ->constrained('user_saved_examples')
                  ->cascadeOnDelete();

            $table->foreignId('grammar_pattern_id')
                  ->constrained('grammar_patterns')
                  ->cascadeOnDelete();

            // 師父 verdict for this pattern usage
            $table->string('status', 16)->default('correct'); // correct | almost | misused

            // 師父's one-line observation about this pattern in this sentence
            $table->text('note')->nullable();

            $table->timestamps();

            $table->unique(
                ['user_saved_example_id', 'grammar_pattern_id'],
                'usaeg_unique'
            );
            $table->index('grammar_pattern_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_saved_example_grammar_patterns');
    }
};
