<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Replaces the single domain_id + secondary_domain_id FKs on word_senses
// with a many-to-many pivot so a sense can span multiple domain contexts.
//
// Each row: one sense ↔ one domain designation, with is_primary flag + sort_order.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_sense_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_sense_id')->constrained('word_senses')->cascadeOnDelete();
            $table->foreignId('designation_id')->constrained('designations')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['word_sense_id', 'designation_id']);
            $table->index(['word_sense_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_sense_domains');
    }
};
