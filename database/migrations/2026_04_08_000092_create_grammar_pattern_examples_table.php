<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grammar_pattern_examples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grammar_pattern_id')
                  ->constrained('grammar_patterns')
                  ->cascadeOnDelete();
            $table->text('chinese_text');
            $table->text('pinyin_text')->nullable();
            $table->string('source', 32)->default('default');
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->boolean('ai_verified')->default(false);
            $table->boolean('is_suppressed')->default(false);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['grammar_pattern_id', 'sort_order']);
            $table->index(['grammar_pattern_id', 'is_suppressed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grammar_pattern_examples');
    }
};
