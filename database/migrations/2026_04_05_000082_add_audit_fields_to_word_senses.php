<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('word_senses', function (Blueprint $table) {
            $table->string('audited_by', 20)->nullable()->after('enriched_at');
            $table->timestamp('audited_at')->nullable()->after('audited_by');
        });
    }

    public function down(): void
    {
        Schema::table('word_senses', function (Blueprint $table) {
            $table->dropColumn(['audited_by', 'audited_at']);
        });
    }
};
