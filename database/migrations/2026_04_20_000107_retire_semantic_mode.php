<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Retire semantic_mode (2026-04-20).
//
// Rationale: of 4,634 tagged senses, 4,574 (98.6%) defaulted to 'literal-only'.
// No enrichment layer (Huiming, Chengyan, 師父) meaningfully engaged with the
// field — every entry silently defaulted. The literal/figurative axis is
// already captured by (a) sense splitting when literal and metaphorical uses
// diverge, and (b) the dimension attribute (concrete vs abstract/internal)
// which marks whether a single sense's reference is physical or non-physical.
//
// This migration:
//   1. Drops word_senses.semantic_mode_id FK + column
//   2. Removes word_sense_designations rows pointing to semantic-mode designations
//   3. Removes the semantic-mode designations themselves (+ labels)
//   4. Removes the semantic-mode attribute row (+ labels)
//
// Rollback restores schema but NOT data (data was dead anyway).

return new class extends Migration {
    public function up(): void
    {
        // 1. Drop FK + column on word_senses
        if (Schema::hasColumn('word_senses', 'semantic_mode_id')) {
            Schema::table('word_senses', function (Blueprint $table) {
                $table->dropForeign(['semantic_mode_id']);
                $table->dropColumn('semantic_mode_id');
            });
        }

        // 2. Collect semantic-mode designation IDs
        $attrId = DB::table('attributes')->where('slug', 'semantic-mode')->value('id');
        if (! $attrId) return; // already gone

        $desigIds = DB::table('designations')
            ->where('attribute_id', $attrId)
            ->pluck('id')
            ->all();

        if (! empty($desigIds)) {
            // Clean any pivot rows (shouldn't exist since single-select used
            // semantic_mode_id, but safe)
            DB::table('word_sense_designations')->whereIn('designation_id', $desigIds)->delete();
            DB::table('designation_labels')->whereIn('designation_id', $desigIds)->delete();
            DB::table('designations')->whereIn('id', $desigIds)->delete();
        }

        // 3. Remove attribute labels + row
        DB::table('attribute_labels')->where('attribute_id', $attrId)->delete();
        DB::table('attributes')->where('id', $attrId)->delete();
    }

    public function down(): void
    {
        // Restore schema only — data intentionally not restored.
        if (! Schema::hasColumn('word_senses', 'semantic_mode_id')) {
            Schema::table('word_senses', function (Blueprint $table) {
                $table->foreignId('semantic_mode_id')
                    ->nullable()
                    ->constrained('designations')
                    ->nullOnDelete();
            });
        }
    }
};
