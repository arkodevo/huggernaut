<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Saved at sense level, not word level — a student saves the specific
        // meaning of 行 they are studying, not the character itself.
        // Personal data (notes, mastery) is global — it travels across all lists.
        Schema::create('user_saved_senses', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('word_sense_id')
                ->constrained('word_senses')
                ->cascadeOnDelete();
            $table->primary(['user_id', 'word_sense_id']);
            $table->text('personal_note')->nullable();
            $table->timestamp('saved_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_saved_senses');
    }
};
