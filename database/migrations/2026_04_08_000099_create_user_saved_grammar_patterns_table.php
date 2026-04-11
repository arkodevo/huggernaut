<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_saved_grammar_patterns', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('grammar_pattern_id')
                  ->constrained('grammar_patterns')
                  ->cascadeOnDelete();
            $table->text('personal_note')->nullable();
            $table->timestamp('saved_at')->useCurrent();
            $table->timestamps();

            $table->primary(['user_id', 'grammar_pattern_id'], 'user_gp_primary');
            $table->index('grammar_pattern_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_saved_grammar_patterns');
    }
};
