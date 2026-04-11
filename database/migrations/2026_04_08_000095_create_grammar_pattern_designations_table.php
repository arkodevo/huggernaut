<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grammar_pattern_designations', function (Blueprint $table) {
            $table->foreignId('grammar_pattern_id')
                  ->constrained('grammar_patterns')
                  ->cascadeOnDelete();
            $table->foreignId('designation_id')
                  ->constrained('designations')
                  ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(
                ['grammar_pattern_id', 'designation_id'],
                'gp_desig_primary'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grammar_pattern_designations');
    }
};
