<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Language;
use App\Models\PosLabel;
use App\Models\WordObject;
use App\Models\WordSense;
use App\Models\WordSenseDefinition;
use App\Models\WordSenseExample;
use App\Models\NoteType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WordSenseController extends Controller
{
    public function create(WordObject $word): View
    {
        $word->load('pronunciations.pronunciationSystem');
        [$attributes, $posLabels, $languages] = $this->formDependencies();

        return view('admin.senses.create', compact('word', 'attributes', 'posLabels', 'languages'));
    }

    public function store(Request $request, WordObject $word): RedirectResponse
    {
        $data = $this->validateSense($request);

        $sense = WordSense::create(array_merge($data['sense'], [
            'word_object_id' => $word->id,
        ]));

        // Write per-language notes and derive canonical
        $this->syncNotes($sense, $data['notes']);

        // Multi-select designations (register, dimension)
        if (! empty($data['designations'])) {
            $sense->designations()->sync($data['designations']);
        }

        // Domains (many-to-many, ordered, max 4)
        if (! empty($data['domains'])) {
            $domainSync = [];
            foreach (array_slice($data['domains'], 0, 4) as $i => $domainId) {
                $domainSync[$domainId] = ['sort_order' => $i];
            }
            $sense->domains()->sync($domainSync);
        }

        // Inline definitions
        foreach ($data['definitions'] as $def) {
            WordSenseDefinition::create(array_merge($def, ['word_sense_id' => $sense->id]));
        }

        return redirect()->route('admin.words.show', $word)
            ->with('success', 'Sense created.');
    }

    public function edit(WordObject $word, WordSense $sense): View
    {
        abort_unless($sense->word_object_id === $word->id, 404);

        $word->load('pronunciations.pronunciationSystem');
        $sense->load('designations', 'definitions.posLabel');
        [$attributes, $posLabels, $languages] = $this->formDependencies();

        return view('admin.senses.edit', compact('word', 'sense', 'attributes', 'posLabels', 'languages'));
    }

    public function update(Request $request, WordObject $word, WordSense $sense): RedirectResponse
    {
        abort_unless($sense->word_object_id === $word->id, 404);

        $data = $this->validateSense($request);

        $sense->update($data['sense']);

        // Write per-language notes and derive canonical
        $this->syncNotes($sense, $data['notes']);

        $sense->designations()->sync($data['designations'] ?? []);

        // Domains (many-to-many, ordered, max 4)
        $domainSync = [];
        foreach (array_slice($data['domains'] ?? [], 0, 4) as $i => $domainId) {
            $domainSync[$domainId] = ['sort_order' => $i];
        }
        $sense->domains()->sync($domainSync);

        // Replace definitions: delete removed, upsert existing + new
        $keptIds = [];
        foreach ($data['definitions'] as $def) {
            if (! empty($def['id'])) {
                WordSenseDefinition::where('id', $def['id'])
                    ->where('word_sense_id', $sense->id)
                    ->update([
                        'language_id'     => $def['language_id'],
                        'pos_id'          => $def['pos_id'],
                        'definition_text' => $def['definition_text'],
                        'sort_order'      => $def['sort_order'] ?? 0,
                    ]);
                $keptIds[] = $def['id'];
            } else {
                $newDef = WordSenseDefinition::create(array_merge($def, ['word_sense_id' => $sense->id]));
                $keptIds[] = $newDef->id;
            }
        }

        // Delete definitions not in the submitted set
        $sense->definitions()->whereNotIn('id', $keptIds)->delete();

        return redirect()->route('admin.words.show', $word)
            ->with('success', 'Sense updated.');
    }

    public function destroy(WordObject $word, WordSense $sense): RedirectResponse
    {
        abort_unless($sense->word_object_id === $word->id, 404);

        DB::transaction(function () use ($sense) {
            // Delete child rows before the sense itself
            $sense->definitions()->delete();
            $sense->examples()->delete();
            DB::table('word_sense_designations')->where('word_sense_id', $sense->id)->delete();
            DB::table('word_sense_domains')->where('word_sense_id', $sense->id)->delete();
            DB::table('word_sense_pos')->where('word_sense_id', $sense->id)->delete();
            DB::table('word_sense_collocations')->where('word_sense_id', $sense->id)->delete();
            DB::table('word_sense_relations')
                ->where('word_sense_id', $sense->id)
                ->delete();
            DB::table('word_sense_notes')
                ->where('word_sense_id', $sense->id)
                ->delete();
            $sense->delete();
        });

        // Bust lexicon caches so the deletion is reflected immediately
        cache()->forget('lexicon_words');
        cache()->forget('lexicon_words_slim');

        return redirect()->route('admin.words.show', $word)
            ->with('success', 'Sense deleted.');
    }

    public function updateStatus(Request $request, WordSense $sense): RedirectResponse
    {
        $request->validate(['status' => ['required', 'in:draft,review,published']]);
        $sense->update(['status' => $request->status]);

        return back()->with('success', "Sense status updated to '{$request->status}'.");
    }

    // ── Shared helpers ────────────────────────────────────────────────────────

    private function validateSense(Request $request): array
    {
        $validated = $request->validate([
            'pronunciation_id'  => ['required', 'exists:word_pronunciations,id'],
            'channel_id'        => ['nullable', 'exists:designations,id'],
            'connotation_id'    => ['nullable', 'exists:designations,id'],
            'semantic_mode_id'  => ['nullable', 'exists:designations,id'],
            'sensitivity_id'    => ['nullable', 'exists:designations,id'],
            'domains'           => ['nullable', 'array'],
            'domains.*'         => ['nullable', 'exists:designations,id'],
            'tocfl_level_id'    => ['nullable', 'exists:designations,id'],
            'hsk_level_id'      => ['nullable', 'exists:designations,id'],
            'intensity'         => ['nullable', 'integer', 'min:1', 'max:5'],
            'valency'           => ['nullable', 'integer', 'min:0', 'max:2'],
            'status'            => ['required', 'in:draft,review,published'],
            'alignment'         => ['nullable', 'in:full,partial,disputed'],
            'source'            => ['nullable', 'in:tocfl,editorial'],
            'designations'      => ['nullable', 'array'],
            'designations.*'    => ['exists:designations,id'],
            'definitions'       => ['nullable', 'array'],
            'definitions.*.id'              => ['nullable', 'integer'],
            'definitions.*.language_id'     => ['required', 'exists:languages,id'],
            'definitions.*.pos_id'          => ['required', 'exists:pos_labels,id'],
            'definitions.*.definition_text' => ['required', 'string'],
            'definitions.*.sort_order'      => ['nullable', 'integer'],
            // Per-language notes (formula, usage_note, learner_traps)
            'notes'                         => ['nullable', 'array'],
            'notes.*'                       => ['array'],
            'notes.*.formula'               => ['nullable', 'string', 'max:255'],
            'notes.*.usage_note'            => ['nullable', 'string'],
            'notes.*.learner_traps'         => ['nullable', 'string'],
        ]);

        $sense = array_intersect_key($validated, array_flip([
            'pronunciation_id', 'channel_id', 'connotation_id', 'semantic_mode_id',
            'sensitivity_id', 'tocfl_level_id', 'hsk_level_id',
            'intensity', 'valency', 'status',
            'alignment', 'source',
        ]));

        return [
            'sense'        => $sense,
            'designations' => $validated['designations'] ?? [],
            'domains'      => array_values(array_filter($validated['domains'] ?? [])),
            'definitions'  => $validated['definitions']  ?? [],
            'notes'        => $validated['notes'] ?? [],
        ];
    }

    /**
     * Write per-language notes to word_sense_notes and derive canonical on word_senses.
     * Canonical prefers ZH-TW; falls back to EN.
     *
     * @param  array<int, array{formula?: string, usage_note?: string, learner_traps?: string}>  $notes  Keyed by language_id
     */
    private function syncNotes(WordSense $sense, array $notes): void
    {
        if (empty($notes)) {
            return;
        }

        $now = now();

        // Map form field names → note_type slugs
        $fieldToSlug = [
            'formula'       => 'formula',
            'usage_note'    => 'usage-note',
            'learner_traps' => 'learner-traps',
        ];

        $noteTypes = NoteType::all()->pluck('id', 'slug'); // slug → id

        foreach ($notes as $langId => $fields) {
            foreach ($fieldToSlug as $field => $slug) {
                $noteTypeId = $noteTypes[$slug] ?? null;
                if (! $noteTypeId) continue;

                $content = trim($fields[$field] ?? '') ?: null;

                if ($content) {
                    DB::table('word_sense_notes')->updateOrInsert(
                        [
                            'word_sense_id' => $sense->id,
                            'language_id'   => $langId,
                            'note_type_id'  => $noteTypeId,
                        ],
                        [
                            'content'    => $content,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );
                } else {
                    // Content blank — remove the row if it exists
                    DB::table('word_sense_notes')
                        ->where('word_sense_id', $sense->id)
                        ->where('language_id', $langId)
                        ->where('note_type_id', $noteTypeId)
                        ->delete();
                }
            }
        }

        // Derive canonical on word_senses: prefer ZH-TW, fall back to EN
        $zhId = Language::where('code', 'zh-TW')->value('id');
        $enId = Language::where('code', 'en')->value('id');

        $allNotes = DB::table('word_sense_notes')
            ->where('word_sense_id', $sense->id)
            ->whereIn('language_id', array_filter([$zhId, $enId]))
            ->get();

        // Build lookup: noteTypeId → langId → content
        $lookup = [];
        foreach ($allNotes as $row) {
            $lookup[$row->note_type_id][$row->language_id] = $row->content;
        }

        $canonical = [];
        foreach ($fieldToSlug as $field => $slug) {
            $typeId = $noteTypes[$slug] ?? null;
            if (! $typeId) continue;
            $canonical[$field] = $lookup[$typeId][$zhId] ?? $lookup[$typeId][$enId] ?? null;
        }

        $sense->updateQuietly($canonical);
    }

    private function formDependencies(): array
    {
        $en = Language::where('code', 'en')->value('id');

        $attributes = Attribute::with([
            'designations' => fn ($q) => $q->orderBy('sort_order'),
            'designations.labels' => fn ($q) => $q->where('language_id', $en),
        ])
            ->orderBy('sort_order')
            ->get()
            ->keyBy('slug');

        $posLabels = PosLabel::with([
            'translations' => fn ($q) => $q->where('language_id', $en),
        ])
            ->whereNull('parent_id')
            ->with([
                'children.translations' => fn ($q) => $q->where('language_id', $en),
            ])
            ->orderBy('sort_order')
            ->get();

        $languages = Language::where('is_active', true)->orderBy('code')->get();

        return [$attributes, $posLabels, $languages];
    }
}
