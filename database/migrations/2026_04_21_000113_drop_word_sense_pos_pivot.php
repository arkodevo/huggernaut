<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Retire word_sense_pos pivot (2026-04-21).
//
// Rationale: the pivot was introduced as "filter-index derived from
// word_sense_definitions.pos_id" for query-performance reasons. In
// practice:
//   1. Nothing on the hot read path uses it — the lexicon (LWP, admin
//      show, learner views) reads POS from word_sense_definitions.pos_id.
//   2. The pivot drifted from the source on 882 senses (pivot=Vpt while
//      definitions=V). Nothing enforced the invariant that the pivot was
//      derived from the source, so over time edits to one side leaked
//      past the other.
//   3. EnrichSkeleton.php was reading from the pivot instead of the
//      source — propagating wrong POS into every batch skeleton that
//      used this tool. That was discovered in the batch 06 audit.
//
// Same class of bug as the word_sense_examples.english_text column we
// retired yesterday: dual-store where the non-source drifts and nothing
// notices. The fix is the same: kill the dual-store.
//
// After this migration:
//   - POS lives only on word_sense_definitions.pos_id (v1.5 spec:
//     "POS + definition are inseparable")
//   - The ~10 code sites that previously wrote to the pivot have been
//     updated: they write only to word_sense_definitions.pos_id.
//   - The two code sites that previously READ from the pivot (skeleton
//     generator, sense-matching) now read from definitions.
//   - The posLabels() relation on WordSense is removed. The wordSenses()
//     relation on PosLabel is removed. The WordSensePos pivot model is
//     deleted. To enumerate senses by POS, query word_sense_definitions.

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('word_sense_pos')) {
            Schema::dropIfExists('word_sense_pos');
        }
    }

    public function down(): void
    {
        // Restore the table shape (empty). Data is NOT restored — the
        // authoritative source is word_sense_definitions.pos_id, and the
        // pivot was never the source.
        if (! Schema::hasTable('word_sense_pos')) {
            Schema::create('word_sense_pos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('word_sense_id')
                    ->constrained('word_senses')
                    ->cascadeOnDelete();
                $table->foreignId('pos_id')
                    ->constrained('pos_labels')
                    ->cascadeOnDelete();
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
                $table->unique(['word_sense_id', 'pos_id']);
            });

            // Seed from definitions (EN, sort_order=0 preferred) for correctness.
            $enLangId = DB::table('languages')->where('code', 'en')->value('id');
            if ($enLangId) {
                DB::statement("
                    INSERT INTO word_sense_pos (word_sense_id, pos_id, is_primary, created_at, updated_at)
                    SELECT DISTINCT ON (word_sense_id) word_sense_id, pos_id, true, NOW(), NOW()
                    FROM word_sense_definitions
                    WHERE language_id = ?
                    ORDER BY word_sense_id, sort_order
                ", [$enLangId]);
            }
        }
    }
};
