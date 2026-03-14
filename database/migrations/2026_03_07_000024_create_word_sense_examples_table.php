<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_sense_examples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_sense_id')
                ->constrained('word_senses')
                ->cascadeOnDelete();
            // Optional: pin to a specific definition within the sense
            $table->foreignId('definition_id')
                ->nullable()
                ->constrained('word_sense_definitions')
                ->nullOnDelete();
            $table->text('chinese_text');
            $table->text('english_text')->nullable();
            $table->string('source', 32)->default('default');
            // default · student · ai_generated · community
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->boolean('ai_verified')->default(false);
            $table->boolean('is_public')->default(true);
            $table->boolean('is_suppressed')->default(false); // global editorial flag (admin action)
            $table->string('theme')->nullable();
            $table->timestamps();

            $table->index(['word_sense_id', 'source']);
            $table->index(['word_sense_id', 'is_public', 'is_suppressed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_sense_examples');
    }
};
