<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pure orthographic identity — character with no pronunciation.
        // The same character (行) across all its readings and senses is one word_object.
        Schema::create('word_objects', function (Blueprint $table) {
            $table->id();
            $table->string('smart_id')->unique();       // Unicode codepoint slug: u884c = 行 · u81a8u8139 = 膨脹
            $table->string('traditional');
            $table->string('simplified')->nullable();
            $table->unsignedSmallInteger('radical_id');
            $table->foreign('radical_id')->references('id')->on('radicals');
            $table->unsignedTinyInteger('strokes_trad');
            $table->unsignedTinyInteger('strokes_simp')->nullable();
            $table->string('structure', 32)->nullable();
            // left-right · top-bottom · enclosing · single
            $table->string('status', 16)->default('draft');
            // draft · review · published
            $table->timestamps();

            $table->index('traditional');
            $table->index('simplified');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_objects');
    }
};
