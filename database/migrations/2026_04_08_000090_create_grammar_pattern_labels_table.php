<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grammar_pattern_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grammar_pattern_id')
                  ->constrained('grammar_patterns')
                  ->cascadeOnDelete();
            $table->foreignId('language_id')
                  ->constrained('languages')
                  ->cascadeOnDelete();
            $table->string('name', 128);
            $table->string('short_description', 255)->nullable();
            $table->timestamps();

            $table->unique(
                ['grammar_pattern_id', 'language_id'],
                'gp_label_lang_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grammar_pattern_labels');
    }
};
