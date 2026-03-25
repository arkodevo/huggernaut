<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifu_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engagement_id')->constrained('shifu_engagements')->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence');   // 1, 2, 3... within engagement
            $table->text('learner_input');
            $table->text('shifu_response');
            $table->boolean('is_correct')->nullable(); // null for generation, true/false for critique
            $table->json('hints_used')->nullable();    // hint slugs if in test context
            $table->timestamp('created_at')->useCurrent();

            $table->index(['engagement_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifu_interactions');
    }
};
