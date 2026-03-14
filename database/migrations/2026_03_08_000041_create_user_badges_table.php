<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();

            // When the badge was first earned.
            $table->timestamp('earned_at')->useCurrent();

            // Null until the "new badge" notification is dismissed.
            $table->timestamp('notified_at')->nullable();

            $table->unique(['user_id', 'badge_id']);
            $table->index(['user_id', 'earned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};
