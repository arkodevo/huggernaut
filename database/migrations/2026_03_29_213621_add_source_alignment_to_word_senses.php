<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('word_senses', function (Blueprint $table) {
            // tocfl = TOCFL oracle · editorial = team-added · null = unverified/legacy
            $table->string('source', 16)->nullable()->after('status');
            // full · partial · disputed — applies to editorial senses only
            $table->string('alignment', 16)->nullable()->after('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('word_senses', function (Blueprint $table) {
            $table->dropColumn(['source', 'alignment']);
        });
    }
};
