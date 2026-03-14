<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_label_translations', function (Blueprint $table) {
            $table->foreignId('pos_id')
                ->constrained('pos_labels')
                ->cascadeOnDelete();
            $table->foreignId('language_id')
                ->constrained('languages')
                ->cascadeOnDelete();
            $table->string('label');                    // Intransitive Verb · 不及物動詞 · intransitives Verb
            $table->primary(['pos_id', 'language_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_label_translations');
    }
};
