<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // General pivot for all multi-select designation attributes.
        // Covers: register (🦋→🕷️, multi-select) and dimension (🐙🐢🐟🦂🦀, multi-select).
        // The attribute a designation belongs to is derivable via designations.attribute_id —
        // no separate pivot table per attribute is needed.
        //
        // Single-select spectrum attributes (channel, connotation, semantic_mode, sensitivity,
        // domain) remain as direct FKs on word_senses for query simplicity.
        Schema::create('word_sense_designations', function (Blueprint $table) {
            $table->foreignId('word_sense_id')
                ->constrained('word_senses')
                ->cascadeOnDelete();
            $table->foreignId('designation_id')
                ->constrained('designations')
                ->cascadeOnDelete();
            $table->primary(['word_sense_id', 'designation_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_sense_designations');
    }
};
