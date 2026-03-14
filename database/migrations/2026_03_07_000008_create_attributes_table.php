<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();
            $table->string('slug', 64)->unique();
            // register · channel · connotation · sensitivity · intensity · dimension · semantic-mode · domain
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_spectrum')->default(false);     // single-select ordered range
            $table->boolean('is_multi_select')->default(false); // register, dimension
            $table->boolean('default_visible')->default(true);  // sensitivity hidden by default
            $table->unsignedTinyInteger('tier_required')->nullable(); // subscription tier gate
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
