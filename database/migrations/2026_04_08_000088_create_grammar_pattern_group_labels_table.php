<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grammar_pattern_group_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grammar_pattern_group_id')
                  ->constrained('grammar_pattern_groups')
                  ->cascadeOnDelete();
            $table->foreignId('language_id')
                  ->constrained('languages')
                  ->cascadeOnDelete();
            $table->string('name', 128);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(
                ['grammar_pattern_group_id', 'language_id'],
                'gp_group_lang_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grammar_pattern_group_labels');
    }
};
