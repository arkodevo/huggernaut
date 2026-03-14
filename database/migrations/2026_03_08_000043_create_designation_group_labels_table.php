<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('designation_group_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('designation_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('label', 120);
            $table->timestamps();

            $table->unique(['designation_group_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('designation_group_labels');
    }
};
