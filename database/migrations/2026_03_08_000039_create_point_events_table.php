<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Slug identifying what triggered this event.
            // Earn examples: daily_login, word_saved, flashcard_session, streak_7day
            // Spend examples: ai_workshop_generation, points_to_ai_credit
            $table->string('event_type', 50);

            // Positive = earned, negative = spent.
            $table->integer('points');

            // Snapshot of points_balance after this event — useful for audit trail.
            $table->integer('balance_after');

            // Polymorphic link to whatever triggered the event (optional).
            // e.g. App\Models\WordSense when saving a word.
            $table->nullableMorphs('subject');

            // Extra context: streak count, session card count, etc.
            $table->jsonb('meta')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_events');
    }
};
