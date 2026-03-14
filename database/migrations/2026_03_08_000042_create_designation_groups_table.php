<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Designation groups organise domain designations into named sections
// (e.g. "Human Inner Life", "Body & Health").  Each group belongs to
// one attribute (currently only the 'domain' attribute uses groups).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('designation_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 80)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('designation_groups');
    }
};
