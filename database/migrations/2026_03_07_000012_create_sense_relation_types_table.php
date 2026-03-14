<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sense_relation_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            // Semantic: synonym_close · synonym_related · antonym · contrast · register_variant
            // Lexical family: derivative · family_member · compound
            // family_member: POS of related_sense carries noun/adj/adv detail
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sense_relation_types');
    }
};
