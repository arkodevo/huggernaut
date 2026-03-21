<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Migrates existing domain_id + secondary_domain_id data from word_senses
// into the new word_sense_domains pivot table, then drops the old FK columns.
return new class extends Migration
{
    public function up(): void
    {
        // Copy primary domains → pivot (is_primary = true, sort_order = 0)
        DB::statement("
            INSERT INTO word_sense_domains (word_sense_id, designation_id, is_primary, sort_order, created_at, updated_at)
            SELECT id, domain_id, true, 0, NOW(), NOW()
            FROM word_senses
            WHERE domain_id IS NOT NULL
        ");

        // Copy secondary domains → pivot (is_primary = false, sort_order = 1)
        DB::statement("
            INSERT INTO word_sense_domains (word_sense_id, designation_id, is_primary, sort_order, created_at, updated_at)
            SELECT id, secondary_domain_id, false, 1, NOW(), NOW()
            FROM word_senses
            WHERE secondary_domain_id IS NOT NULL
            AND secondary_domain_id != domain_id
        ");

        // Drop old FK columns
        DB::statement('ALTER TABLE word_senses DROP CONSTRAINT IF EXISTS word_senses_domain_id_foreign');
        DB::statement('ALTER TABLE word_senses DROP CONSTRAINT IF EXISTS word_senses_secondary_domain_id_foreign');
        DB::statement('ALTER TABLE word_senses DROP COLUMN IF EXISTS domain_id');
        DB::statement('ALTER TABLE word_senses DROP COLUMN IF EXISTS secondary_domain_id');
    }

    public function down(): void
    {
        // Re-add columns
        DB::statement('ALTER TABLE word_senses ADD COLUMN domain_id BIGINT NULL');
        DB::statement('ALTER TABLE word_senses ADD COLUMN secondary_domain_id BIGINT NULL');
        DB::statement('ALTER TABLE word_senses ADD CONSTRAINT word_senses_domain_id_foreign FOREIGN KEY (domain_id) REFERENCES designations(id) ON DELETE SET NULL');
        DB::statement('ALTER TABLE word_senses ADD CONSTRAINT word_senses_secondary_domain_id_foreign FOREIGN KEY (secondary_domain_id) REFERENCES designations(id) ON DELETE SET NULL');

        // Copy pivot data back
        DB::statement("
            UPDATE word_senses SET domain_id = (
                SELECT designation_id FROM word_sense_domains
                WHERE word_sense_domains.word_sense_id = word_senses.id AND is_primary = true
                LIMIT 1
            )
        ");
        DB::statement("
            UPDATE word_senses SET secondary_domain_id = (
                SELECT designation_id FROM word_sense_domains
                WHERE word_sense_domains.word_sense_id = word_senses.id AND is_primary = false
                ORDER BY sort_order LIMIT 1
            )
        ");
    }
};
