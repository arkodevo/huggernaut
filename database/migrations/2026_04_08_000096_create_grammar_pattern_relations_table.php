<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grammar_pattern_relations', function (Blueprint $table) {
            $table->foreignId('grammar_pattern_id')
                  ->constrained('grammar_patterns')
                  ->cascadeOnDelete();
            $table->foreignId('related_pattern_id')
                  ->constrained('grammar_patterns')
                  ->cascadeOnDelete();
            $table->string('relation_type', 24); // prerequisite | builds_on | variant | contrast | related
            $table->text('editorial_note')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->primary(
                ['grammar_pattern_id', 'related_pattern_id', 'relation_type'],
                'gp_relation_primary'
            );
            $table->index('related_pattern_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grammar_pattern_relations');
    }
};
