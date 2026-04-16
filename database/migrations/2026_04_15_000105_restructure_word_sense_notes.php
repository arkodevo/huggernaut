<?php

use App\Models\NoteType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Seed note_types first (migration depends on them) ───────────
        $this->seedNoteTypes();

        $formulaId      = NoteType::where('slug', 'formula')->value('id');
        $usageNoteId    = NoteType::where('slug', 'usage-note')->value('id');
        $learnerTrapsId = NoteType::where('slug', 'learner-traps')->value('id');

        // ── 2. Add new columns + drop old unique constraint ────────────────
        Schema::table('word_sense_notes', function (Blueprint $table) {
            // Drop old unique constraint BEFORE data migration — we'll be inserting
            // multiple rows per (word_sense_id, language_id) now.
            $table->dropUnique('word_sense_notes_word_sense_id_language_id_unique');

            $table->foreignId('note_type_id')
                ->nullable()
                ->after('language_id')
                ->constrained('note_types')
                ->cascadeOnDelete();
            $table->text('content')->nullable()->after('note_type_id');
        });

        // ── 3. Migrate data: explode each row into up to 3 rows ───────────
        // Process in chunks to avoid memory issues.
        DB::table('word_sense_notes')
            ->whereNull('note_type_id')
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($formulaId, $usageNoteId, $learnerTrapsId) {
                $inserts = [];
                $deleteIds = [];
                $now = now();

                foreach ($rows as $row) {
                    $deleteIds[] = $row->id;

                    // Create a separate row for each non-null field
                    if (! empty($row->formula)) {
                        $inserts[] = [
                            'word_sense_id' => $row->word_sense_id,
                            'language_id'   => $row->language_id,
                            'note_type_id'  => $formulaId,
                            'content'       => $row->formula,
                            'created_at'    => $row->created_at ?? $now,
                            'updated_at'    => $row->updated_at ?? $now,
                        ];
                    }

                    if (! empty($row->usage_note)) {
                        $inserts[] = [
                            'word_sense_id' => $row->word_sense_id,
                            'language_id'   => $row->language_id,
                            'note_type_id'  => $usageNoteId,
                            'content'       => $row->usage_note,
                            'created_at'    => $row->created_at ?? $now,
                            'updated_at'    => $row->updated_at ?? $now,
                        ];
                    }

                    if (! empty($row->learner_traps)) {
                        $inserts[] = [
                            'word_sense_id' => $row->word_sense_id,
                            'language_id'   => $row->language_id,
                            'note_type_id'  => $learnerTrapsId,
                            'content'       => $row->learner_traps,
                            'created_at'    => $row->created_at ?? $now,
                            'updated_at'    => $row->updated_at ?? $now,
                        ];
                    }
                }

                // Delete old wide rows, insert new normalized rows
                DB::table('word_sense_notes')->whereIn('id', $deleteIds)->delete();

                foreach (array_chunk($inserts, 200) as $batch) {
                    DB::table('word_sense_notes')->insert($batch);
                }
            });

        // ── 4. Delete orphaned rows (all fields were null — no content) ──
        DB::table('word_sense_notes')->whereNull('note_type_id')->delete();

        // ── 5. Drop old columns ──────────────────────────────────────────
        Schema::table('word_sense_notes', function (Blueprint $table) {
            $table->dropColumn(['formula', 'usage_note', 'learner_traps']);
        });

        // ── 6. Make note_type_id non-nullable, add new unique constraint ──
        Schema::table('word_sense_notes', function (Blueprint $table) {
            $table->foreignId('note_type_id')->nullable(false)->change();
            $table->unique(['word_sense_id', 'language_id', 'note_type_id'], 'wsn_sense_lang_type_unique');
        });
    }

    public function down(): void
    {
        // Reverse: add back old columns, migrate data back, drop new columns

        Schema::table('word_sense_notes', function (Blueprint $table) {
            $table->dropUnique('wsn_sense_lang_type_unique');
            $table->string('formula')->nullable()->after('language_id');
            $table->text('usage_note')->nullable()->after('formula');
            $table->text('learner_traps')->nullable()->after('usage_note');
        });

        // Pivot normalized rows back into wide rows
        $formulaId      = NoteType::where('slug', 'formula')->value('id');
        $usageNoteId    = NoteType::where('slug', 'usage-note')->value('id');
        $learnerTrapsId = NoteType::where('slug', 'learner-traps')->value('id');

        $groups = DB::table('word_sense_notes')
            ->select('word_sense_id', 'language_id')
            ->distinct()
            ->get();

        foreach ($groups as $group) {
            $notes = DB::table('word_sense_notes')
                ->where('word_sense_id', $group->word_sense_id)
                ->where('language_id', $group->language_id)
                ->get()
                ->keyBy('note_type_id');

            DB::table('word_sense_notes')
                ->where('word_sense_id', $group->word_sense_id)
                ->where('language_id', $group->language_id)
                ->delete();

            DB::table('word_sense_notes')->insert([
                'word_sense_id' => $group->word_sense_id,
                'language_id'   => $group->language_id,
                'formula'       => $notes->get($formulaId)?->content,
                'usage_note'    => $notes->get($usageNoteId)?->content,
                'learner_traps' => $notes->get($learnerTrapsId)?->content,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        Schema::table('word_sense_notes', function (Blueprint $table) {
            $table->dropColumn(['note_type_id', 'content']);
            $table->unique(['word_sense_id', 'language_id']);
        });
    }

    private function seedNoteTypes(): void
    {
        (new \Database\Seeders\NoteTypeSeeder)->run();
    }
};
