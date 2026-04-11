<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\GrammarPattern;
use App\Models\GrammarPatternGroup;
use App\Models\GrammarPatternNote;
use App\Models\GrammarPatternSuggestion;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return redirect()->route('admin.grammar.show', $pattern)
            ->with('success', "Grammar pattern '{$pattern->chinese_label}' updated.");
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
