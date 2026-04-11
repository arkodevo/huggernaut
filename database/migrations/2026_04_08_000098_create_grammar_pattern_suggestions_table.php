<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grammar_pattern_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('pattern_text', 128);
            $table->text('chinese_example')->nullable();
            $table->text('shifu_notes')->nullable();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignId('grammar_pattern_id')
                  ->nullable()
                  ->constrained('grammar_patterns')
                  ->nullOnDelete();
            $table->string('status', 16)->default('pending'); // pending | accepted | rejected | duplicate
            $table->timestamp('status_updated_at')->nullable();
            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('pattern_text');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grammar_pattern_suggestions');
    }
};
