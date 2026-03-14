<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // User-authored sentences — independent records, not saves of existing examples.
        // These are the student's own constructions, linked to the sense they were written for.
        // is_public = true contributes to the community examples pool in Phase 4.
        Schema::create('user_saved_examples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('word_sense_id')
                ->constrained('word_senses')
                ->cascadeOnDelete();
            $table->text('chinese_text');
            $table->text('english_text')->nullable();
            $table->boolean('ai_verified')->default(false);
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'word_sense_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_saved_examples');
    }
};
