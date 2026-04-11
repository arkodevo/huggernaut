<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('affirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('word_sense_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'word_sense_id']);
            $table->index('word_sense_id');
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affirmations');
    }
};
