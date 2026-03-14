<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pronunciation_systems', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 32)->unique();       // pinyin · zhuyin · jyutping
            $table->string('name');
            $table->string('language');                 // Mandarin · Cantonese
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pronunciation_systems');
    }
};
