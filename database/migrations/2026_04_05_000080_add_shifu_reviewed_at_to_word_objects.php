<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('word_objects', function (Blueprint $table) {
            $table->timestamp('shifu_reviewed_at')->nullable()->after('subtlex_cd');
        });
    }

    public function down(): void
    {
        Schema::table('word_objects', function (Blueprint $table) {
            $table->dropColumn('shifu_reviewed_at');
        });
    }
};
