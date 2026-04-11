<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grammar_patterns', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 128)->unique();
            $table->string('chinese_label', 64);
            $table->string('pattern_template', 255)->nullable();
            $table->foreignId('grammar_pattern_group_id')
                  ->nullable()
                  ->constrained('grammar_pattern_groups')
                  ->nullOnDelete();
            $table->foreignId('tocfl_level_id')
                  ->nullable()
                  ->constrained('designations')
                  ->nullOnDelete();
            $table->foreignId('hsk_level_id')
                  ->nullable()
                  ->constrained('designations')
                  ->nullOnDelete();
            $table->string('status', 16)->default('draft');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('tocfl_level_id');
            $table->index('hsk_level_id');
            $table->index('grammar_pattern_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grammar_patterns');
    }
};
