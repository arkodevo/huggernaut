<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Retire user_saved_examples.english_text (2026-04-20).
//
// Rationale: user_saved_example_translations (migration 000109) is the
// normalized, multilingual-native store for learner-writing translations,
// keyed by (user_saved_example_id, language_id). The legacy english_text
// scalar column has been backfilled (5 rows → 5 translations, 1:1) and
// all read/write sites have been rewritten to use the translations table.
//
// Rollback restores the column shape and does a best-effort data restore
// from the translations table — English only. The translations table
// remains the source of truth.

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('user_saved_examples', 'english_text')) {
            Schema::table('user_saved_examples', function (Blueprint $table) {
                $table->dropColumn('english_text');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('user_saved_examples', 'english_text')) {
            Schema::table('user_saved_examples', function (Blueprint $table) {
                $table->text('english_text')->nullable()->after('chinese_text');
            });

            $enLangId = DB::table('languages')->where('code', 'en')->value('id');
            if ($enLangId) {
                DB::statement("
                    UPDATE user_saved_examples e
                    SET english_text = t.translation_text
                    FROM user_saved_example_translations t
                    WHERE t.user_saved_example_id = e.id
                      AND t.language_id = ?
                ", [$enLangId]);
            }
        }
    }
};
