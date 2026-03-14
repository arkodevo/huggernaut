<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();

            // Stable identifier — never changes once set.
            $table->string('slug', 60)->unique();

            $table->string('name', 100);
            $table->text('description');

            // Emoji or icon key (e.g. '🌿', 'streak-fire').
            $table->string('icon', 50)->default('🏅');

            // How the badge is triggered:
            //   points_total  — lifetime points_total_earned >= threshold
            //   action_count  — count of point_events with event_type = action_type >= threshold
            //   streak        — login/activity streak >= threshold (days)
            //   manual        — admin-granted only, no automatic trigger
            $table->string('trigger_type', 30)->default('points_total');

            // The number to reach (points, action count, or streak days).
            $table->unsignedInteger('threshold')->default(0);

            // For action_count trigger: which event_type slug to count.
            $table->string('action_type', 50)->nullable();

            // AI credits awarded when this badge is first earned.
            $table->unsignedSmallInteger('bonus_credits')->default(0);

            // Soft disable without deleting — hidden badges don't trigger automatically.
            $table->boolean('is_active')->default(true);

            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
