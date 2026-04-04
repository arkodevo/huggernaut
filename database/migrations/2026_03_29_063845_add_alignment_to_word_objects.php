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
        Schema::table('word_objects', function (Blueprint $table) {
            // full · partial · disputed — editorial POS alignment signal
            $table->string('alignment', 16)->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('word_objects', function (Blueprint $table) {
            $table->dropColumn('alignment');
        });
    }
};
