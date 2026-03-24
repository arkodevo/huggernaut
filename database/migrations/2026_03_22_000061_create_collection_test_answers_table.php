<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_test_id')->constrained('collection_tests')->cascadeOnDelete();
            $table->foreignId('word_sense_id')->constrained('word_senses')->cascadeOnDelete();
            $table->smallInteger('question_index');
            $table->text('correct_value');              // JSON of correct answer(s)
            $table->text('chosen_value');                // what learner selected/typed
            $table->boolean('is_correct');
            $table->jsonb('hints_used')->default('[]');  // array of hint slugs
            $table->string('score_tier', 10);            // clean, assisted, learning
            $table->text('ai_feedback')->nullable();     // only for usage mode
            $table->integer('time_spent_ms')->nullable();
            $table->timestamps();

            $table->index(['collection_test_id', 'question_index']);
            $table->index('word_sense_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_test_answers');
    }
};
