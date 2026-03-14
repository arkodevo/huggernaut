<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Supports optional secondary semantic domain per word sense.
// Both domain_id (primary) and secondary_domain_id → designations.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('word_senses', function (Blueprint $table) {
            $table->foreignId('secondary_domain_id')
                  ->nullable()
                  ->after('domain_id')
                  ->constrained('designations')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('word_senses', function (Blueprint $table) {
            $table->dropForeign(['secondary_domain_id']);
            $table->dropColumn('secondary_domain_id');
        });
    }
};
