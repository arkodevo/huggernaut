<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\NoteType;
use App\Models\NoteTypeLabel;
use Illuminate\Database\Seeder;

class NoteTypeSeeder extends Seeder
{
    public function run(): void
    {
        $en   = Language::where('code', 'en')->value('id');
        $zhTW = Language::where('code', 'zh-TW')->value('id');

        // ── Note types ────────────────────────────────────────────────────────
        // formula        — structural usage pattern with slot labels
        // usage-note     — contextual guidance on when/where to use the word
        // learner-traps  — common mistakes and confusions to avoid
        //
        // To add a new note type: add a row here and re-run the seeder.
        // No migration or code changes needed — controllers and frontend
        // iterate note_types dynamically.

        $types = [
            ['slug' => 'formula',       'sort_order' => 1, 'en' => 'Formula',       'zh' => '公式'],
            ['slug' => 'usage-note',    'sort_order' => 2, 'en' => 'Usage Note',    'zh' => '用法說明'],
            ['slug' => 'learner-traps', 'sort_order' => 3, 'en' => 'Learner Traps', 'zh' => '學習陷阱'],
        ];

        foreach ($types as $data) {
            $type = NoteType::updateOrCreate(
                ['slug' => $data['slug']],
                ['sort_order' => $data['sort_order']]
            );

            NoteTypeLabel::updateOrCreate(
                ['note_type_id' => $type->id, 'language_id' => $en],
                ['label' => $data['en']]
            );
            NoteTypeLabel::updateOrCreate(
                ['note_type_id' => $type->id, 'language_id' => $zhTW],
                ['label' => $data['zh']]
            );
        }
    }
}
