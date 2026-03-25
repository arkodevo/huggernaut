<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifu_engagements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('word_sense_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('word_object_id')->nullable()->constrained()->nullOnDelete();
            $table->string('context', 32);            // writing_conservatory, test, generation
            $table->string('word_label', 32);          // hanzi for quick display
            $table->string('outcome', 16)->nullable(); // saved, abandoned, correct, incorrect
            $table->unsignedTinyInteger('interaction_count')->default(0);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->index(['user_id', 'started_at']);
            $table->index('word_sense_id');
            $table->index('context');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifu_engagements');
    }
};
