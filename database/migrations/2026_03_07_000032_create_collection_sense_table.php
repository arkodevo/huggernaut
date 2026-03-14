<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_sense', function (Blueprint $table) {
            $table->foreignId('collection_id')
                ->constrained('collections')
                ->cascadeOnDelete();
            $table->foreignId('word_sense_id')
                ->constrained('word_senses')
                ->cascadeOnDelete();
            $table->primary(['collection_id', 'word_sense_id']);
            $table->integer('sort_order')->default(0);       // lists are reorderable
            $table->unsignedTinyInteger('mastery_level')->default(0); // 0–5 mastery indicator
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_sense');
    }
};
