<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_labels', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 32)->unique();       // Vi · Vs · Vst · N · M · Prn (TOCFL notation)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('pos_labels')
                ->nullOnDelete();                       // Vi → Action Verb → Verb (3 display tiers)
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_labels');
    }
};
