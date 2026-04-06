<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('word_senses', function (Blueprint $table) {
            $table->string('enriched_by', 20)->nullable()->after('alignment');
            $table->timestamp('enriched_at')->nullable()->after('enriched_by');
        });
    }

    public function down(): void
    {
        Schema::table('word_senses', function (Blueprint $table) {
            $table->dropColumn(['enriched_by', 'enriched_at']);
        });
    }
};
