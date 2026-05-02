<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Master TOCFL vocabulary — immutable reference table (stone).
//
// Purpose: canonical in-DB representation of the official TOCFL 8k
// word list (華語八千詞). Until now the list only existed as an xlsx
// under ~/Documents/華語/planning/. Putting it inside the DB makes it
// reachable from Laravel — drift detection becomes a LEFT JOIN instead
// of a Python script.
//
// Design:
//   - Pure TOCFL source only. No editorial columns (no 光流_pos, no
//     惠明_pos). Those belong in working tables, not reference.
//   - No Lulu rows (150 custom additions at Level 8 in the xlsx) —
//     those are editorial and go elsewhere.
//   - Immutable: BEFORE UPDATE and BEFORE DELETE triggers raise.
//     Rows are INSERTed exactly once by the import artisan command
//     and then the table is read-only forever.
//
// Future: a parallel `master_hsk_vocabulary` table will hold the HSK
// reference list when Luoyi is ready. Same stone discipline.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('master_tocfl_vocabulary', function (Blueprint $table) {
            $table->id();
            $table->string('band_label', 64);        // "準備級一級(Novice 1)"
            $table->smallInteger('level_number');    // 1..7 (TOCFL only)
            $table->string('traditional', 64);
            $table->string('pinyin', 64)->nullable();
            $table->string('official_pos', 16)->nullable();  // nullable: idiomatic phrases
            $table->integer('row_seq');              // original xlsx row order
            $table->string('source_version', 64);    // e.g. "2026-04-03-expanded_final_numeric"
            $table->timestampTz('imported_at');
            // Deliberately NO updated_at. NO modified_by. Immutable.

            $table->index('traditional');
            $table->index(['traditional', 'pinyin']);
            $table->index('level_number');
        });

        // Trigger: block UPDATE and DELETE forever. INSERT remains allowed
        // for the import command. Convention: only the import command
        // inserts. If we need to block INSERT after initial population,
        // that's a separate lock-down migration.
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION master_tocfl_vocabulary_readonly()
            RETURNS TRIGGER AS $$
            BEGIN
                RAISE EXCEPTION 'master_tocfl_vocabulary is immutable — % blocked', TG_OP;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER master_tocfl_vocabulary_no_update
                BEFORE UPDATE ON master_tocfl_vocabulary
                FOR EACH ROW EXECUTE FUNCTION master_tocfl_vocabulary_readonly();

            CREATE TRIGGER master_tocfl_vocabulary_no_delete
                BEFORE DELETE ON master_tocfl_vocabulary
                FOR EACH ROW EXECUTE FUNCTION master_tocfl_vocabulary_readonly();

            COMMENT ON TABLE master_tocfl_vocabulary IS
                'Immutable TOCFL 8k reference. Stone. UPDATE and DELETE blocked by trigger. Populated once by php artisan master:import-tocfl. Do not modify.';
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS master_tocfl_vocabulary_no_delete ON master_tocfl_vocabulary;
            DROP TRIGGER IF EXISTS master_tocfl_vocabulary_no_update ON master_tocfl_vocabulary;
            DROP FUNCTION IF EXISTS master_tocfl_vocabulary_readonly();
        SQL);

        Schema::dropIfExists('master_tocfl_vocabulary');
    }
};
