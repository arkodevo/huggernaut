<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\GrammarPattern;
use App\Models\GrammarPatternGroup;
use App\Models\GrammarPatternNote;
use App\Models\GrammarPatternSuggestion;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GrammarPatternController extends Controller
{
    // ── Filter keys ──────────────────────────────────────────────────────────

    private const FILTER_KEYS = ['q', 'status', 'group', 'tocfl_level', 'hsk_level'];

    // ── Index (Patterns tab) ─────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'patterns');
        $filterData = $this->filterData();

        // ── Patterns tab ─────────────────────────────────────────────────────
        $patterns = null;
        $sort = null;
        $direction = 'asc';

        if ($tab === 'patterns') {
            $hasFilter = collect(self::FILTER_KEYS)->some(fn ($k) => $request->filled($k));

            if ($hasFilter) {
                $sort = in_array($request->sort, ['chinese_label', 'status']) ? $request->sort : null;
                $direction = $request->direction === 'asc' ? 'asc' : 'desc';

                $query = $this->baseQuery();
                $this->applyFilters($query, $request);

                if ($sort) {
                    $query->orderBy($sort, $direction);
                } else {
                    $query->latest();
                }

                $patterns = $query->paginate(30)->withQueryString();
            }
        }

        // ── Suggestions tab ──────────────────────────────────────────────────
        $suggestions = null;
        $suggestionCounts = null;

        if ($tab === 'suggestions') {
            $sugQuery = GrammarPatternSuggestion::with(['user', 'pattern', 'reviewer']);

            $sugStatus = $request->input('sug_status', 'pending');
            if ($sugStatus !== 'all') {
                $sugQuery->where('status', $sugStatus);
            }

            $suggestions = $sugQuery->latest()->paginate(30)->withQueryString();

            $suggestionCounts = [
                'pending'  => GrammarPatternSuggestion::where('status', 'pending')->count(),
                'accepted' => GrammarPatternSuggestion::where('status', 'accepted')->count(),
                'rejected' => GrammarPatternSuggestion::where('status', 'rejected')->count(),
            ];
        }

        // Always fetch pending count for the tab badge
        $pendingSuggestionCount = GrammarPatternSuggestion::where('status', 'pending')->count();

        return view('admin.grammar.index', compact(
            'tab',
            'patterns',
            'sort',
            'direction',
            'suggestions',
            'suggestionCounts',
            'pendingSuggestionCount',
        ) + $filterData);
    }

    // ── Create ───────────────────────────────────────────────────────────────

    public function create(Request $request): View
    {
        $deps = $this->formDependencies();

        // If created from a suggestion, pre-fill
        $suggestion = null;
        if ($request->filled('from_suggestion')) {
            $suggestion = GrammarPatternSuggestion::find($request->from_suggestion);
        }

        return view('admin.grammar.create', compact('suggestion') + $deps);
    }

    // ── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'slug'                     => ['required', 'string', 'max:128', 'regex:/^[a-z0-9\-]+$/', 'unique:grammar_patterns,slug'],
            'chinese_label'            => ['required', 'string', 'max:64'],
            'pattern_template'         => ['nullable', 'string', 'max:255'],
            'grammar_pattern_group_id' => ['nullable', 'exists:grammar_pattern_groups,id'],
            'tocfl_level_id'           => ['nullable', 'exists:designations,id'],
            'hsk_level_id'             => ['nullable', 'exists:designations,id'],
            'status'                   => ['required', 'in:draft,review,published'],
            'labels'                   => ['nullable', 'array'],
            'labels.*.name'            => ['nullable', 'string', 'max:128'],
            'labels.*.short_description' => ['nullable', 'string', 'max:255'],
            'notes'                    => ['nullable', 'array'],
            'notes.*.formula'          => ['nullable', 'string'],
            'notes.*.usage_note'       => ['nullable', 'string'],
            'notes.*.learner_traps'    => ['nullable', 'string'],
            'pending_examples'         => ['nullable', 'array'],
            'pending_examples.*.chinese_text'        => ['nullable', 'string'],
            'pending_examples.*.pinyin_text'         => ['nullable', 'string'],
            'pending_examples.*.translations'        => ['nullable', 'array'],
            'pending_examples.*.translations.*'      => ['nullable', 'string'],
            'from_suggestion'          => ['nullable', 'exists:grammar_pattern_suggestions,id'],
        ]);

        $pattern = GrammarPattern::create([
            'slug'                     => $data['slug'],
            'chinese_label'            => $data['chinese_label'],
            'pattern_template'         => $data['pattern_template'] ?? null,
            'grammar_pattern_group_id' => $data['grammar_pattern_group_id'] ?? null,
            'tocfl_level_id'           => $data['tocfl_level_id'] ?? null,
            'hsk_level_id'             => $data['hsk_level_id'] ?? null,
            'status'                   => $data['status'],
        ]);

        // Sync labels
        if (! empty($data['labels'])) {
            foreach ($data['labels'] as $langId => $labelData) {
                if (! empty($labelData['name'])) {
                    $pattern->labels()->create([
                        'language_id'       => $langId,
                        'name'              => $labelData['name'],
                        'short_description' => $labelData['short_description'] ?? null,
                    ]);
                }
            }
        }

        // Sync notes
        $this->syncNotes($pattern, $data['notes'] ?? []);

        // Sync pending examples (generated by 師父 during create flow)
        $this->syncPendingExamples($pattern, $data['pending_examples'] ?? []);

        // If created from suggestion, mark it accepted
        if (! empty($data['from_suggestion'])) {
            GrammarPatternSuggestion::where('id', $data['from_suggestion'])->update([
                'status'            => 'accepted',
                'grammar_pattern_id' => $pattern->id,
                'status_updated_at' => now(),
                'reviewed_by'       => auth()->id(),
            ]);
        }

        return redirect()->route('admin.grammar.show', $pattern)
            ->with('success', "Grammar pattern '{$pattern->chinese_label}' created.");
    }

    // ── Show ─────────────────────────────────────────────────────────────────

    public function show(GrammarPattern $pattern): View
    {
        $pattern->load([
            'group.labels',
            'labels.language',
            'notes.language',
            'tocflLevel.labels',
            'hskLevel.labels',
            'examples' => fn ($q) => $q->orderBy('sort_order')->with('translations.language'),
            'wordSenses' => fn ($q) => $q->with([
                'wordObject',
                'pronunciation',
                'definitions' => fn ($d) => $d->where('language_id', 1)->with('posLabel'),
            ]),
            'relatedPatterns',
            'suggestions' => fn ($q) => $q->latest()->limit(10),
        ]);

        return view('admin.grammar.show', compact('pattern'));
    }

    // ── Edit ─────────────────────────────────────────────────────────────────

    public function edit(GrammarPattern $pattern): View
    {
        $pattern->load([
            'labels.language',
            'notes.language',
            'examples' => fn ($q) => $q->orderBy('sort_order')->with('translations.language'),
            'wordSenses' => fn ($q) => $q->with([
                'wordObject',
                'definitions' => fn ($d) => $d->where('language_id', 1)->with('posLabel'),
            ]),
        ]);

        $deps = $this->formDependencies();

        return view('admin.grammar.edit', compact('pattern') + $deps);
    }

    // ── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, GrammarPattern $pattern): RedirectResponse
    {
        $data = $request->validate([
            'slug'                     => ['required', 'string', 'max:128', 'regex:/^[a-z0-9\-]+$/', 'unique:grammar_patterns,slug,' . $pattern->id],
            'chinese_label'            => ['required', 'string', 'max:64'],
            'pattern_template'         => ['nullable', 'string', 'max:255'],
            'grammar_pattern_group_id' => ['nullable', 'exists:grammar_pattern_groups,id'],
            'tocfl_level_id'           => ['nullable', 'exists:designations,id'],
            'hsk_level_id'             => ['nullable', 'exists:designations,id'],
            'status'                   => ['required', 'in:draft,review,published'],
            'labels'                   => ['nullable', 'array'],
            'labels.*.name'            => ['nullable', 'string', 'max:128'],
            'labels.*.short_description' => ['nullable', 'string', 'max:255'],
            'notes'                    => ['nullable', 'array'],
            'notes.*.formula'          => ['nullable', 'string'],
            'notes.*.usage_note'       => ['nullable', 'string'],
            'notes.*.learner_traps'    => ['nullable', 'string'],
            'pending_examples'                  => ['nullable', 'array'],
            'pending_examples.*.chinese_text'   => ['nullable', 'string'],
            'pending_examples.*.pinyin_text'    => ['nullable', 'string'],
            'pending_examples.*.translations'   => ['nullable', 'array'],
            'pending_examples.*.translations.*' => ['nullable', 'string'],
        ]);

        $pattern->update([
            'slug'                     => $data['slug'],
            'chinese_label'            => $data['chinese_label'],
            'pattern_template'         => $data['pattern_template'] ?? null,
            'grammar_pattern_group_id' => $data['grammar_pattern_group_id'] ?? null,
            'tocfl_level_id'           => $data['tocfl_level_id'] ?? null,
            'hsk_level_id'             => $data['hsk_level_id'] ?? null,
            'status'                   => $data['status'],
        ]);

        // Sync labels
        if (! empty($data['labels'])) {
            foreach ($data['labels'] as $langId => $labelData) {
                if (! empty($labelData['name'])) {
                    $pattern->labels()->updateOrCreate(
                        ['language_id' => $langId],
                        [
                            'name'              => $labelData['name'],
                            'short_description' => $labelData['short_description'] ?? null,
                        ]
                    );
                }
            }
        }

        // Sync notes
        $this->syncNotes($pattern, $data['notes'] ?? []);

        // Sync any 師父-staged examples from the edit form
        $this->syncPendingExamples($pattern, $data['pending_examples'] ?? []);

        return redirect()->route('admin.grammar.show', $pattern)
            ->with('success', "Grammar pattern '{$pattern->chinese_label}' updated.");
    }

    // ── Status (AJAX from index) ────────────────────────────────────────────

    public function updateStatus(Request $request, GrammarPattern $pattern): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:draft,review,published'],
        ]);

        $pattern->update(['status' => $data['status']]);

        return response()->json([
            'ok'     => true,
            'id'     => $pattern->id,
            'status' => $pattern->status,
        ]);
    }

    // ── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(GrammarPattern $pattern): RedirectResponse
    {
        $label = $pattern->chinese_label;
        $pattern->delete();

        return redirect()->route('admin.grammar.index')
            ->with('success', "Grammar pattern '{$label}' deleted.");
    }

    // ── Suggestion actions ───────────────────────────────────────────────────

    public function acceptSuggestion(GrammarPatternSuggestion $suggestion): RedirectResponse
    {
        return redirect()->route('admin.grammar.create', [
            'from_suggestion' => $suggestion->id,
        ]);
    }

    public function rejectSuggestion(Request $request, GrammarPatternSuggestion $suggestion): RedirectResponse
    {
        $suggestion->update([
            'status'            => 'rejected',
            'status_updated_at' => now(),
            'reviewed_by'       => auth()->id(),
        ]);

        return back()->with('success', "Suggestion '{$suggestion->pattern_text}' rejected.");
    }

    public function linkSuggestion(Request $request, GrammarPatternSuggestion $suggestion): RedirectResponse
    {
        $request->validate([
            'grammar_pattern_id' => ['required', 'exists:grammar_patterns,id'],
        ]);

        $suggestion->update([
            'status'             => 'duplicate',
            'grammar_pattern_id' => $request->grammar_pattern_id,
            'status_updated_at'  => now(),
            'reviewed_by'        => auth()->id(),
        ]);

        return back()->with('success', "Suggestion linked to existing pattern.");
    }

    // ── 師父 enrichment ──────────────────────────────────────────────────────
    //
    // Asks 師父 to generate bilingual notes (formula, usage_note, learner_traps)
    // and 4-6 graded examples for a draft grammar pattern. Returns a JSON preview
    // — nothing is persisted. The admin reviews, edits, and hits Save Changes
    // to commit the notes, or clicks individual "+ Add" buttons to save examples.

    public function enrich(Request $request, GrammarPattern $pattern): JsonResponse
    {
        $pattern->load(['labels.language', 'notes.language']);

        $seed = [
            'chinese_label'    => $pattern->chinese_label,
            'english_label'    => $pattern->labels->firstWhere('language_id', 1)?->name,
            'pattern_template' => $pattern->pattern_template,
            'slug'             => $pattern->slug,
            'hint_context'     => null,
        ];

        // Dynamically seed existing notes keyed by coverage lang code so the
        // prompt builder can include them as "preserve/improve" context.
        foreach (Language::where('has_notes_coverage', true)->get() as $cl) {
            $key = 'existing_note_' . str_replace('-', '_', $cl->code);
            $seed[$key] = $pattern->notes->firstWhere('language_id', $cl->id);
        }

        return $this->runEnrichment($seed, ['pattern_id' => $pattern->id]);
    }

    /**
     * Enrich from raw seed fields — used by the create.blade.php form before
     * a pattern is persisted, and by the suggestion-accept flow.
     *
     * Required input: chinese_label (or pattern_text fallback)
     * Optional: slug, pattern_template, english_label, hint_context
     */
    public function enrichSeed(Request $request): JsonResponse
    {
        $data = $request->validate([
            'chinese_label'    => ['required', 'string', 'max:128'],
            'slug'             => ['nullable', 'string', 'max:128'],
            'pattern_template' => ['nullable', 'string', 'max:255'],
            'english_label'    => ['nullable', 'string', 'max:128'],
            'hint_context'     => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->runEnrichment([
            'chinese_label'    => $data['chinese_label'],
            'english_label'    => $data['english_label'] ?? null,
            'pattern_template' => $data['pattern_template'] ?? null,
            'slug'             => $data['slug'] ?? null,
            'existing_note_en' => null,
            'existing_note_zh' => null,
            'hint_context'     => $data['hint_context'] ?? null,
        ]);
    }

    /**
     * Accept a suggestion with 師父 enrichment in one step:
     *   1. Create a draft GrammarPattern seeded from the suggestion's pattern_text
     *   2. Mark the suggestion accepted and linked
     *   3. Return the new pattern ID so the client can redirect to its edit page
     *      where the enrichment preview is invoked.
     *
     * No Anthropic call here — the edit view fires enrich() on load via the
     * `auto_enrich` query param, keeping server-side logic simple and making
     * the flow interruptible.
     */
    public function enrichSuggestion(Request $request, GrammarPatternSuggestion $suggestion): RedirectResponse
    {
        if ($suggestion->status !== 'pending') {
            return back()->with('error', 'Only pending suggestions can be enriched.');
        }

        // Derive a slug from the pattern_text (strip non-ASCII, fallback to hash)
        $baseSlug = Str::slug(Str::limit($suggestion->pattern_text, 40, ''));
        if ($baseSlug === '') {
            $baseSlug = 'gp-' . substr(md5($suggestion->pattern_text), 0, 8);
        }
        $slug = $baseSlug;
        $i = 2;
        while (GrammarPattern::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i;
            $i++;
        }

        $pattern = GrammarPattern::create([
            'slug'          => $slug,
            'chinese_label' => mb_substr($suggestion->pattern_text, 0, 64),
            'status'        => 'draft',
        ]);

        $suggestion->update([
            'status'             => 'accepted',
            'grammar_pattern_id' => $pattern->id,
            'status_updated_at'  => now(),
            'reviewed_by'        => auth()->id(),
        ]);

        return redirect()->route('admin.grammar.edit', [
            'pattern'     => $pattern,
            'auto_enrich' => 1,
            'hint_context' => $suggestion->chinese_example ?: $suggestion->shifu_notes,
        ])->with('success', "Draft pattern '{$pattern->chinese_label}' created. 師父 is enriching it now…");
    }

    /**
     * Persist a 師父 enrichment preview in one shot — labels + notes + examples.
     * Used by the draft-queue step-through so the admin can approve with a
     * single click instead of saving a form. Idempotent on re-approval.
     */
    public function applyEnrichment(Request $request, GrammarPattern $pattern): JsonResponse
    {
        $data = $request->validate([
            'enrichment'                    => ['required', 'array'],
            'enrichment.pattern_template'   => ['nullable', 'string', 'max:255'],
            'enrichment.labels'             => ['nullable', 'array'],
            'enrichment.notes'              => ['nullable', 'array'],
            'enrichment.examples'           => ['nullable', 'array'],
        ]);

        $enr = $data['enrichment'];

        // Pattern template: only fill if currently empty (don't overwrite human edits)
        $templateAdded = false;
        if (! empty($enr['pattern_template']) && empty($pattern->pattern_template)) {
            $pattern->update(['pattern_template' => $enr['pattern_template']]);
            $templateAdded = true;
        }

        // Build coverage-lang map keyed by short+full code so incoming payloads
        // keyed by either form ('en' or 'zh-TW') resolve to the correct row.
        $coverageLangs = Language::where('has_notes_coverage', true)->get();
        $langIdFor = function (string $code) use ($coverageLangs) {
            $exact = $coverageLangs->firstWhere('code', $code);
            if ($exact) return $exact->id;
            $prefix = $coverageLangs->first(fn ($l) => str_starts_with($l->code, $code));
            return $prefix?->id;
        };

        // Labels: upsert for every key 師父 returned
        $labelsAdded = 0;
        foreach (($enr['labels'] ?? []) as $code => $lab) {
            $langId = $langIdFor($code);
            if (! $langId) continue;
            if (is_array($lab) && (! empty($lab['name']) || ! empty($lab['short_description']))) {
                $pattern->labels()->updateOrCreate(
                    ['language_id' => $langId],
                    [
                        'name'              => $lab['name'] ?? null,
                        'short_description' => $lab['short_description'] ?? null,
                    ]
                );
                $labelsAdded++;
            }
        }

        // Notes: upsert for every key 師父 returned
        $notesAdded = 0;
        foreach (($enr['notes'] ?? []) as $code => $n) {
            $langId = $langIdFor($code);
            if (! $langId) continue;
            if (is_array($n) && (! empty($n['formula']) || ! empty($n['usage_note']) || ! empty($n['learner_traps']))) {
                GrammarPatternNote::updateOrCreate(
                    ['grammar_pattern_id' => $pattern->id, 'language_id' => $langId],
                    [
                        'formula'       => $n['formula'] ?? null,
                        'usage_note'    => $n['usage_note'] ?? null,
                        'learner_traps' => $n['learner_traps'] ?? null,
                    ]
                );
                $notesAdded++;
            }
        }

        // Examples: append new ones with source='shifu', translating into every
        // non-Chinese coverage language whose code appears as a key in the row.
        $translationLangs = $coverageLangs->reject(fn ($l) => str_starts_with($l->code, 'zh'));
        $examples = $enr['examples'] ?? [];
        $createdCount = 0;
        if (is_array($examples)) {
            $sortBase = (int) ($pattern->examples()->max('sort_order') ?? 0);
            foreach ($examples as $idx => $ex) {
                if (! is_array($ex) || empty($ex['chinese_traditional'])) {
                    continue;
                }
                // Skip dupes (same chinese_text already exists)
                $already = $pattern->examples()
                    ->where('chinese_text', $ex['chinese_traditional'])
                    ->exists();
                if ($already) {
                    continue;
                }

                $example = $pattern->examples()->create([
                    'chinese_text' => $ex['chinese_traditional'],
                    'pinyin_text'  => $ex['pinyin'] ?? null,
                    'source'       => 'shifu',
                    'sort_order'   => $sortBase + $idx + 1,
                    'is_suppressed' => false,
                ]);

                // Write a translation row for every non-Chinese coverage lang
                // whose code (or short prefix) appears on this example row.
                // Accepts either the full code ("en") or language name fallback.
                foreach ($translationLangs as $tl) {
                    $value = $ex[$tl->code] ?? $ex[strtolower($tl->code)] ?? null;
                    // Back-compat: old schema used "english" as a hardcoded key.
                    if (! $value && $tl->code === 'en') {
                        $value = $ex['english'] ?? null;
                    }
                    if (! empty($value)) {
                        $example->translations()->create([
                            'language_id'      => $tl->id,
                            'translation_text' => $value,
                        ]);
                    }
                }
                $createdCount++;
            }
        }

        return response()->json([
            'ok'             => true,
            'pattern_id'     => $pattern->id,
            'template_added' => $templateAdded,
            'labels_added'   => $labelsAdded,
            'notes_added'    => $notesAdded,
            'examples_added' => $createdCount,
        ]);
    }

    // ── Draft enrichment queue ──────────────────────────────────────────────
    //
    // Shows all draft patterns that still lack notes or examples, plus a
    // sequential AJAX step-through UI that calls /enrich on each in turn.
    // Mirrors the Words CSV review UX: promise-chained enrichment, one pattern
    // at a time, admin approves or skips each before moving on.

    public function queue(Request $request): View
    {
        $drafts = GrammarPattern::where('status', 'draft')
            ->with(['labels', 'notes', 'group.labels'])
            ->withCount('examples')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(function (GrammarPattern $p) {
                // Needs enrichment if: no EN note with content, OR no examples
                $noteEn = $p->notes->firstWhere('language_id', 1);
                $hasNotes = $noteEn && ($noteEn->formula || $noteEn->usage_note || $noteEn->learner_traps);
                return ! $hasNotes || $p->examples_count === 0;
            })
            ->values();

        return view('admin.grammar.queue', compact('drafts'));
    }

    /**
     * Shared enrichment runner — takes a normalised seed array, calls Anthropic,
     * parses JSON, returns a preview response.
     */
    private function runEnrichment(array $seed, array $extra = []): JsonResponse
    {
        $systemPrompt = $this->buildEnrichmentPrompt($seed);

        $userMessage = "Generate enrichment content for: {$seed['chinese_label']}"
            . (! empty($seed['pattern_template']) ? " — template: {$seed['pattern_template']}" : '')
            . (! empty($seed['hint_context'])     ? " — context: {$seed['hint_context']}"     : '');

        $result = $this->callAnthropic($systemPrompt, $userMessage);

        if (isset($result['error']) || ! isset($result['content'])) {
            return response()->json([
                'error'  => 'AI request failed',
                'detail' => $result['error'] ?? 'unknown',
            ], 502);
        }

        $raw = collect($result['content'] ?? [])
            ->map(fn ($b) => $b['text'] ?? '')
            ->join('');

        $clean = preg_replace('/```json|```/', '', $raw);
        $parsed = json_decode(trim($clean), true);

        if (! is_array($parsed)) {
            return response()->json([
                'error' => 'Could not parse 師父 response',
                'raw'   => mb_substr($raw, 0, 500),
            ], 502);
        }

        return response()->json(array_merge([
            'enrichment' => $parsed,
        ], $extra));
    }

    private function buildEnrichmentPrompt(array $seed): string
    {
        $label    = $seed['chinese_label'];
        $template = $seed['pattern_template'] ?: '(not yet set)';
        $slug     = $seed['slug'] ?: '(not yet assigned)';
        $enLabel  = $seed['english_label'] ?: '(not yet set)';

        // Coverage languages drive the multilingual shape of the response.
        // Adding a new `has_notes_coverage = true` language automatically tells
        // 師父 to emit content for it — no prompt edit needed.
        $coverageLangs = Language::where('has_notes_coverage', true)->orderBy('id')->get();

        // Translation langs for examples = coverage langs minus Chinese
        // (the example source sentence is already Chinese).
        $translationLangs = $coverageLangs->reject(fn ($l) => str_starts_with($l->code, 'zh'));

        // Existing draft content (to preserve/improve across re-enrichment).
        // Keyed by coverage lang code, lookup matches `existing_note_{code}`.
        $existingContext = '';
        foreach ($coverageLangs as $cl) {
            $key = 'existing_note_' . str_replace('-', '_', $cl->code);
            $note = $seed[$key] ?? null;
            if ($note && ($note->formula || $note->usage_note || $note->learner_traps)) {
                if ($existingContext === '') {
                    $existingContext = "\n\nEXISTING DRAFT CONTENT (improve or preserve — do not regress quality):\n";
                }
                $tag = strtoupper($cl->code);
                if ($note->formula)       $existingContext .= "- {$tag} formula: {$note->formula}\n";
                if ($note->usage_note)    $existingContext .= "- {$tag} usage note: {$note->usage_note}\n";
                if ($note->learner_traps) $existingContext .= "- {$tag} learner traps: {$note->learner_traps}\n";
            }
        }

        if (! empty($seed['hint_context'])) {
            $existingContext .= "\nLEARNER CONTEXT THAT SURFACED THIS PATTERN:\n- {$seed['hint_context']}\n";
        }

        // ── Build dynamic pieces of the prompt ────────────────────────────
        $coverageList = $coverageLangs
            ->map(fn ($l) => "  * \"{$l->code}\" — {$l->name}")
            ->implode("\n");

        // Example translation fields: one key per non-Chinese coverage lang.
        // Field name uses lowercase code, e.g. "en", "ja".
        $translationFields = $translationLangs
            ->map(fn ($l) => "\"{$l->code}\": \"natural {$l->name} translation (not literal)\"")
            ->implode(",\n      ");
        $translationFieldsNote = $translationLangs->count() > 0
            ? "Include natural translations in the non-Chinese coverage languages."
            : '(No additional translation languages.)';

        // Labels/notes skeleton per coverage lang
        $labelsSkeleton = $coverageLangs->map(function ($l) {
            $isZh = str_starts_with($l->code, 'zh');
            $namePh = $isZh ? '顯示名稱' : "{$l->name} display name";
            $descPh = $isZh ? '一句話簡介' : "one-line description, max 100 chars";
            return "    \"{$l->code}\": { \"name\": \"{$namePh}\", \"short_description\": \"{$descPh}\" }";
        })->implode(",\n");

        $notesSkeleton = $coverageLangs->map(function ($l) {
            $isZh = str_starts_with($l->code, 'zh');
            $formulaPh = $isZh
                ? '[主語] 把 [賓語] [動詞+補語]'
                : '[Subject] 把 [Object] [Verb + Complement]';
            return <<<JSON
    "{$l->code}": {
      "formula": "{$formulaPh}",
      "usage_note": "...",
      "learner_traps": "..."
    }
JSON;
        })->implode(",\n");

        $exampleSkeleton = trim("\"chinese_traditional\": \"...\",\n      \"chinese_simplified\": \"...\",\n      \"pinyin\": \"...\",\n      " . ($translationFields ?: '') . ($translationFields ? ",\n      " : '') . "\"note\": \"one short line on what this example illustrates (optional)\"");

        return <<<PROMPT
You are 師父 (Shifu), the editorial expert for 流動 Living Lexicon — a precision Chinese vocabulary and grammar platform for intermediate and advanced learners. You are warm, intellectually precise, and allergic to textbook flatness. Even here in the editorial workshop, your voice carries the same care you bring to learners: every note, every example, every nuance is a small act of teaching.

Your role: generate bilingual notes and graded examples for grammar patterns. You are helping the editorial team enrich a grammar pattern entry. Produce content that is pedagogically sharp, culturally grounded, and calibrated for a thoughtful learner — not a drill book.

PATTERN:
- Chinese label: {$label}
- English label: {$enLabel}
- Template: {$template}
- Slug: {$slug}
{$existingContext}

COVERAGE LANGUAGES (you MUST produce labels + notes for every one of these, keyed by the code):
{$coverageList}

YOUR TASK: Generate a language-neutral pattern template, labels + notes for every coverage language above, plus 4 to 6 learner-facing examples.

GUIDELINES:
- PATTERN_TEMPLATE: A single language-neutral structural skeleton with English placeholder labels and Chinese function words preserved — e.g. "[Subject] 把 [Object] [Verb + Complement]". This is the canonical row-level template (not per-language). If the current template field is already set and reasonable, repeat it verbatim.
- LABELS: For each coverage language, produce a native-language display name and a short one-line description. Chinese descriptions should be compact (~50 字); other languages ~100 chars.
- FORMULA (per language): Compact structural skeleton using bracketed placeholders. Same structural skeleton across all languages, but placeholder labels localised — e.g. "[Subject] 把 [Object] [Verb + Complement]" for English, "[主語] 把 [賓語] [動詞+補語]" for Chinese. For any other coverage language, use that language's most natural grammatical terminology.
- USAGE NOTE (per language): 2–3 sentences. Explain what the pattern expresses, when to reach for it, what register it lives in, and one nuance a learner might miss. Write natively in each coverage language — not translations of each other.
- LEARNER TRAPS (per language): 1–2 sentences. Name the single most common mistake intermediate learners from that language background make — be specific, not generic.
- EXAMPLES: 4 to 6 total, graded from simpler to more expressive. Use Traditional Chinese for the source. Each example should:
  * Feel like a real sentence someone might actually say or write
  * Demonstrate the pattern unambiguously
  * Use natural collocations, not drill-style vocabulary
  * Include pinyin with tone marks
  * Include a natural Simplified Chinese rendering as well
  * {$translationFieldsNote}

Respond ONLY in this exact JSON format (no markdown fences, no extra prose). Use the language codes listed above verbatim as keys:

{
  "pattern_template": "[Subject] 把 [Object] [Verb + Complement]",
  "labels": {
{$labelsSkeleton}
  },
  "notes": {
{$notesSkeleton}
  },
  "examples": [
    {
      {$exampleSkeleton}
    }
  ]
}
PROMPT;
    }

    private function callAnthropic(string $systemPrompt, string $userMessage): array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key'         => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
                'model'      => config('services.anthropic.model', 'claude-sonnet-4-20250514'),
                'max_tokens' => 2500,
                'system'     => $systemPrompt,
                'messages'   => [
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ]);

            return $response->json() ?? ['error' => 'empty_response'];
        } catch (\Throwable $e) {
            report($e);
            return ['error' => $e->getMessage()];
        }
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function baseQuery()
    {
        return GrammarPattern::with([
            'group.labels' => fn ($q) => $q->where('language_id', 1),
            'tocflLevel.labels' => fn ($q) => $q->where('language_id', 1),
            'hskLevel.labels' => fn ($q) => $q->where('language_id', 1),
            'labels' => fn ($q) => $q->where('language_id', 1),
        ])->withCount('wordSenses');
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('group')) {
            $query->where('grammar_pattern_group_id', $request->group);
        }

        if ($request->filled('tocfl_level')) {
            $query->where('tocfl_level_id', $request->tocfl_level);
        }

        if ($request->filled('hsk_level')) {
            $query->where('hsk_level_id', $request->hsk_level);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('chinese_label', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('pattern_template', 'like', "%{$q}%")
                    ->orWhereHas('labels', fn ($l) => $l
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('short_description', 'like', "%{$q}%")
                    );
            });
        }
    }

    private function filterData(): array
    {
        $groups = GrammarPatternGroup::with(['labels' => fn ($q) => $q->where('language_id', 1)])
            ->orderBy('sort_order')
            ->get();

        $tocflLevels = Designation::whereHas('attribute', fn ($q) => $q->where('slug', 'tocfl-level'))
            ->with(['labels' => fn ($q) => $q->where('language_id', 1)])
            ->orderBy('sort_order')
            ->get();

        $hskLevels = Designation::whereHas('attribute', fn ($q) => $q->where('slug', 'hsk-level'))
            ->with(['labels' => fn ($q) => $q->where('language_id', 1)])
            ->orderBy('sort_order')
            ->get();

        return compact('groups', 'tocflLevels', 'hskLevels');
    }

    private function formDependencies(): array
    {
        $groups = GrammarPatternGroup::with(['labels' => fn ($q) => $q->where('language_id', 1)])
            ->orderBy('sort_order')
            ->get();

        $tocflLevels = Designation::whereHas('attribute', fn ($q) => $q->where('slug', 'tocfl-level'))
            ->with(['labels' => fn ($q) => $q->where('language_id', 1)])
            ->orderBy('sort_order')
            ->get();

        $hskLevels = Designation::whereHas('attribute', fn ($q) => $q->where('slug', 'hsk-level'))
            ->with(['labels' => fn ($q) => $q->where('language_id', 1)])
            ->orderBy('sort_order')
            ->get();

        $coverageLangs = Language::where('has_notes_coverage', true)->orderBy('id')->get();

        return compact('groups', 'tocflLevels', 'hskLevels', 'coverageLangs');
    }

    private function syncPendingExamples(GrammarPattern $pattern, array $pending): void
    {
        $sort = 0;
        foreach ($pending as $row) {
            $chinese = trim($row['chinese_text'] ?? '');
            if ($chinese === '') {
                continue;
            }

            $example = $pattern->examples()->create([
                'chinese_text' => $chinese,
                'pinyin_text'  => $row['pinyin_text'] ?? null,
                'source'       => 'shifu',
                'sort_order'   => $sort++,
            ]);

            foreach (($row['translations'] ?? []) as $langId => $text) {
                if (! empty($text)) {
                    $example->translations()->create([
                        'language_id'      => (int) $langId,
                        'translation_text' => $text,
                    ]);
                }
            }
        }
    }

    private function syncNotes(GrammarPattern $pattern, array $notesData): void
    {
        foreach ($notesData as $langId => $fields) {
            $hasContent = collect($fields)->filter(fn ($v) => ! empty($v))->isNotEmpty();

            if ($hasContent) {
                GrammarPatternNote::updateOrCreate(
                    ['grammar_pattern_id' => $pattern->id, 'language_id' => $langId],
                    [
                        'formula'       => $fields['formula'] ?? null,
                        'usage_note'    => $fields['usage_note'] ?? null,
                        'learner_traps' => $fields['learner_traps'] ?? null,
                    ]
                );
            }
        }
    }
}
