<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sense_relation_type_labels', function (Blueprint $table) {
            $table->foreignId('relation_type_id')
                ->constrained('sense_relation_types')
                ->cascadeOnDelete();
            $table->foreignId('language_id')
                ->constrained('languages')
                ->cascadeOnDelete();
            $table->string('label');                    // Lexical Cluster · 詞族 · Wortfamilie
            $table->primary(['relation_type_id', 'language_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sense_relation_type_labels');
    }
};
