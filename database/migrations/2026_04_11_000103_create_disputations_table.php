<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('word_sense_id')
                  ->constrained('word_senses')
                  ->cascadeOnDelete();

            // Array of field keys the learner flagged. Format examples:
            //   "definition" · "formula" · "usage_note" · "learner_traps"
            //   "example:{id}" (specific example sentence id)
            //   "attribute:{slug}" (e.g. "attribute:register")
            // Keys are strings, not FKs — the composer UI maps them back
            // to sense payload positions for display; schema-level lookup
            // is not required.
            $table->jsonb('fields_disputed');

            $table->text('rationale');

            // Per-row snapshot of the learner's choice at submission time.
            // Defaults from users.default_disputes_anonymous on the composer,
            // but the learner can override per dispute. Once stored here the
            // value is frozen — flipping the profile setting later does NOT
            // retroactively hide or reveal past disputes (unlike affirmations,
            // which read the live setting).
            $table->boolean('is_anonymous')->default(false);

            // pending | under_review | resolved
            $table->string('status', 16)->default('pending');

            // fully_agree | partially_agree | disagree — null until resolved
            $table->string('verdict', 16)->nullable();

            // 三人行 adjudicator (editor/admin role). Null until picked up
            // from the queue in Phase B #3.5.
            $table->foreignId('adjudicator_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->text('adjudicator_notes')->nullable();

            $table->timestamps();
            $table->timestamp('resolved_at')->nullable();

            // Browse by sense (community feed, LWP disputeCount aggregation)
            $table->index('word_sense_id');
            // My Activity ledger: user's own disputes, newest first
            $table->index(['user_id', 'created_at']);
            // Community feed: filter by status, order by time
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputations');
    }
};
