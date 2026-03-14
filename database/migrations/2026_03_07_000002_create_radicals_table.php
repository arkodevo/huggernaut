<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radicals', function (Blueprint $table) {
            $table->smallIncrements('id');              // Kangxi number 1–214 — seeded as natural key
            $table->string('character', 8);
            $table->unsignedTinyInteger('stroke_count');
            $table->string('meaning_en')->nullable();
            $table->string('meaning_zh')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radicals');
    }
};
