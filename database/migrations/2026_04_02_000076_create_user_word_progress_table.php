<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_word_progress', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('word_object_id')->constrained('word_objects')->cascadeOnDelete();
            $table->boolean('pinyin_passed')->default(false);
            $table->boolean('definition_passed')->default(false);
            $table->boolean('usage_passed')->default(false);
            $table->timestamp('pinyin_passed_at')->nullable();
            $table->timestamp('definition_passed_at')->nullable();
            $table->timestamp('usage_passed_at')->nullable();
            $table->timestamps();
            $table->primary(['user_id', 'word_object_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_word_progress');
    }
};
