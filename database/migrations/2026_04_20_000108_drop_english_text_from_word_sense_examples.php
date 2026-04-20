<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Retire word_sense_examples.english_text (2026-04-20).
//
// Rationale: word_sense_example_translations (migration 000086) is the
// normalized, multilingual-native store for example translations, keyed by
// (word_sense_example_id, language_id). The legacy english_text scalar
// column was partially migrated — 778 stragglers backfilled 2026-04-20 —
// and all read/write sites have been rewritten to use the translations
// table. This migration drops the redundant column.
//
// Backfill confirmation (from 2026-04-20 pre-migration check):
//   total word_sense_examples        = 12,387
//   total word_sense_example_translations = 12,387 (1:1 after backfill)
//
// Rollback restores the column shape but NOT the data. Use the translations
// table as the source of truth; there is no recovery path for the legacy
// column by design.

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('word_sense_examples', 'english_text')) {
            Schema::table('word_sense_examples', function (Blueprint $table) {
                $table->dropColumn('english_text');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('word_sense_examples', 'english_text')) {
            Schema::table('word_sense_examples', function (Blueprint $table) {
                $table->text('english_text')->nullable()->after('chinese_text');
            });

            // Best-effort restore from translations table — English only.
            $enLangId = DB::table('languages')->where('code', 'en')->value('id');
            if ($enLangId) {
                DB::statement("
                    UPDATE word_sense_examples e
                    SET english_text = t.translation_text
                    FROM word_sense_example_translations t
                    WHERE t.word_sense_example_id = e.id
                      AND t.language_id = ?
                ", [$enLangId]);
            }
        }
    }
};
