<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // radical_id and strokes_trad are editorial fields filled in after initial draft entry.
        // Seeds may create word_objects without this data; editors populate later.
        Schema::table('word_objects', function (Blueprint $table) {
            $table->unsignedSmallInteger('radical_id')->nullable()->change();
            $table->unsignedTinyInteger('strokes_trad')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('word_objects', function (Blueprint $table) {
            $table->unsignedSmallInteger('radical_id')->nullable(false)->change();
            $table->unsignedTinyInteger('strokes_trad')->nullable(false)->change();
        });
    }
};
