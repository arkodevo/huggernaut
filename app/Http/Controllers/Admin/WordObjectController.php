<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\DesignationGroup;
use App\Models\PosLabel;
use App\Models\Radical;
use App\Models\WordObject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WordObjectController extends Controller
{
    // ── Filter keys used in both index() and export() ────────────────────────

    private const FILTER_KEYS = ['q', 'status', 'tocfl_level', 'hsk_level', 'pos', 'register', 'dimension', 'domain', 'secondary_domain'];

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        [$filterData] = [$this->filterData()];

        $hasFilter = collect(self::FILTER_KEYS)->some(fn ($k) => $request->filled($k));

        if (! $hasFilter) {
            return view('admin.words.index', [
                'words'     => null,
                'sort'      => null,
                'direction' => 'asc',
                ...$filterData,
            ]);
        }

        $sortable  = ['pinyin', 'status'];
        $sort      = in_array($request->sort, $sortable) ? $request->sort : null;
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $query = $this->baseQuery();

        // ── Ordering ──────────────────────────────────────────────────────────
        if ($sort === 'status') {
            $query->orderBy('status', $direction);
        } elseif ($sort === 'pinyin') {
            $query->orderByRaw(
                '(SELECT wp.pronunciation_text
                  FROM word_senses ws
                  JOIN word_pronunciations wp ON wp.id = ws.pronunciation_id
                  WHERE ws.word_object_id = word_objects.id
                  ORDER BY ws.id LIMIT 1) ' . strtoupper($direction)
            );
        } else {
            $query->latest();
        }

        $this->applyFilters($query, $request);

        $words = $query->paginate(30)->withQueryString();

        return view('admin.words.index', compact('words', 'sort', 'direction') + $filterData);
    }

    // ── Export ────────────────────────────────────────────────────────────────

    public function export(Request $request): Response
    {
        $mode = $request->input('mode', 'by_sense'); // foundational | by_sense

        $query = $this->baseQuery();
        $this->applyFilters($query, $request);

        // For export we don't paginate — stream all matching rows.
        $words = $query->get();

        $filename = 'liudong-export-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $output = fopen('php://temp', 'r+');

        // UTF-8 BOM so Excel opens Chinese characters correctly.
        fwrite($output, "\xEF\xBB\xBF");

        if ($mode === 'foundational') {
            fputcsv($output, ['Traditional', 'Simplified', 'Strokes', 'Status', 'Senses']);
            foreach ($words as $word) {
                fputcsv($output, [
                    $word->traditional,
                    $word->simplified ?? '',
                    $word->strokes_trad ?? '',
                    $word->status,
                    $word->senses_count,
                ]);
            }
        } else {
            // by_sense — one row per word_sense
            fputcsv($output, [
                'Traditional', 'Simplified', 'Pinyin',
                'POS', 'Definition', 'TOCFL Level', 'HSK Level', 'Status',
            ]);
            foreach ($words as $word) {
                foreach ($word->senses as $sense) {
                    $def = $sense->definitions->first();
                    fputcsv($output, [
                        $word->traditional,
                        $word->simplified ?? '',
                        $sense->pronunciation?->pronunciation_text ?? '',
                        $def?->posLabel?->slug ?? '',
                        $def?->definition_text ?? '',
                        $sense->tocflLevel?->labels->first()?->label ?? '',
                        $sense->hskLevel?->labels->first()?->label ?? '',
                        $word->status,
                    ]);
                }
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv, 200, $headers);
    }

    // ── Shared helpers ────────────────────────────────────────────────────────

    /** Eager-load query used by both index() and export(). */
    private function baseQuery()
    {
        return WordObject::with([
            'senses' => fn ($q) => $q->orderBy('id')->with([
                'pronunciation',
                'tocflLevel' => fn ($q) => $q->with(['labels' => fn ($q) => $q->where('language_id', 1)]),
                'hskLevel'   => fn ($q) => $q->with(['labels' => fn ($q) => $q->where('language_id', 1)]),
                'definitions' => fn ($q) => $q->where('language_id', 1)->with('posLabel'),
            ]),
        ])->withCount('senses');
    }

    /** Apply all request filters to the given query (mutates in place). */
    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tocfl_level')) {
            $query->whereHas('senses', fn ($s) => $s->where('tocfl_level_id', $request->tocfl_level));
        }

        if ($request->filled('hsk_level')) {
            $query->whereHas('senses', fn ($s) => $s->where('hsk_level_id', $request->hsk_level));
        }

        if ($request->filled('pos')) {
            $posId   = (int) $request->pos;
            $posIds  = collect([$posId]);
            $posLabel = PosLabel::find($posId);

            // If a parent POS is selected, include all its children.
            if ($posLabel && is_null($posLabel->parent_id)) {
                $posIds = $posIds->merge(
                    PosLabel::where('parent_id', $posId)->pluck('id')
                );
            }

            $query->whereHas('senses.definitions', fn ($d) => $d->whereIn('pos_id', $posIds));
        }

        if ($request->filled('register')) {
            $query->whereHas('senses', fn ($s) => $s->whereHas('designations', fn ($d) =>
                $d->where('designations.id', $request->register)
            ));
        }

        if ($request->filled('dimension')) {
            $query->whereHas('senses', fn ($s) => $s->whereHas('designations', fn ($d) =>
                $d->where('designations.id', $request->dimension)
            ));
        }

        if ($request->filled('domain')) {
            $query->whereHas('senses', fn ($s) => $s->whereHas('domains', fn ($d) =>
                $d->where('designations.id', $request->domain)
            ));
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('traditional', 'like', "%{$q}%")
                    ->orWhere('simplified', 'like', "%{$q}%")
                    ->orWhereHas('senses.definitions', fn ($d) => $d
                        ->where('language_id', 1)
                        ->whereRaw('definition_text ~* ?', ['\\y' . preg_quote($q, '/') . '\\y'])
                    );
            });
        }
    }

    /** Data for populating all filter dropdowns — passed to every index view. */
    private function filterData(): array
    {
        $tocflLevels = Designation::whereHas('attribute', fn ($q) => $q->where('slug', 'tocfl-level'))
            ->with(['labels' => fn ($q) => $q->where('language_id', 1)])
            ->orderBy('sort_order')
            ->get();

        $hskLevels = Designation::whereHas('attribute', fn ($q) => $q->where('slug', 'hsk-level'))
            ->with(['labels' => fn ($q) => $q->where('language_id', 1)])
            ->orderBy('sort_order')
            ->get();

        // Parents first, each carrying their children for the optgroup select.
        $posParents = PosLabel::whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $registerDesignations = Designation::whereHas('attribute', fn ($q) => $q->where('slug', 'register'))
            ->with(['labels' => fn ($q) => $q->where('language_id', 1)])
            ->orderBy('sort_order')
            ->get();

        $dimensionDesignations = Designation::whereHas('attribute', fn ($q) => $q->where('slug', 'dimension'))
            ->with(['labels' => fn ($q) => $q->where('language_id', 1)])
            ->orderBy('sort_order')
            ->get();

        // Domain groups — each carries its grouped designations for <optgroup> select.
        $domainGroups = DesignationGroup::whereHas('attribute', fn ($q) => $q->where('slug', 'domain'))
            ->with([
                'labels'       => fn ($q) => $q->where('language_id', 1),
                'designations' => fn ($q) => $q
                    ->orderBy('sort_order')
                    ->with(['labels' => fn ($q) => $q->where('language_id', 1)]),
            ])
            ->orderBy('sort_order')
            ->get();

        return compact(
            'tocflLevels',
            'hskLevels',
            'posParents',
            'registerDesignations',
            'dimensionDesignations',
            'domainGroups'
        );
    }

    // ── Standard CRUD ─────────────────────────────────────────────────────────

    public function create(): View
    {
        $radicals = Radical::orderBy('id')->get();

        return view('admin.words.create', compact('radicals'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'traditional'  => ['required', 'string', 'max:16'],
            'simplified'   => ['nullable', 'string', 'max:16'],
            'radical_id'   => ['required', 'exists:radicals,id'],
            'strokes_trad' => ['required', 'integer', 'min:1', 'max:64'],
            'strokes_simp' => ['nullable', 'integer', 'min:1', 'max:64'],
            'structure'    => ['nullable', 'in:single,left-right,top-bottom,enclosing'],
            'status'       => ['required', 'in:draft,review,published'],
        ]);

        $data['smart_id'] = $this->generateSmartId($data['traditional']);

        $word = WordObject::create($data);

        return redirect()->route('admin.words.show', $word)
            ->with('success', "Word object '{$word->traditional}' created.");
    }

    public function show(WordObject $word): View
    {
        $word->load([
            'radical',
            'pronunciations.pronunciationSystem',
            'senses.pronunciation',
            'senses.tocflLevel.labels',
            'senses.hskLevel.labels',
            'senses.definitions.posLabel',
        ]);

        return view('admin.words.show', compact('word'));
    }

    public function edit(WordObject $word): View
    {
        $radicals = Radical::orderBy('id')->get();

        return view('admin.words.edit', compact('word', 'radicals'));
    }

    public function update(Request $request, WordObject $word): RedirectResponse
    {
        $data = $request->validate([
            'traditional'  => ['required', 'string', 'max:16'],
            'simplified'   => ['nullable', 'string', 'max:16'],
            'radical_id'   => ['required', 'exists:radicals,id'],
            'strokes_trad' => ['required', 'integer', 'min:1', 'max:64'],
            'strokes_simp' => ['nullable', 'integer', 'min:1', 'max:64'],
            'structure'    => ['nullable', 'in:single,left-right,top-bottom,enclosing'],
            'status'       => ['required', 'in:draft,review,published'],
        ]);

        $word->update($data);

        return redirect()->route('admin.words.show', $word)
            ->with('success', "Word object '{$word->traditional}' updated.");
    }

    public function updateStatus(Request $request, WordObject $word): RedirectResponse
    {
        $request->validate(['status' => ['required', 'in:draft,review,published']]);
        $word->update(['status' => $request->status]);

        return back()->with('success', "Status updated to '{$request->status}'.");
    }

    private function generateSmartId(string $chars): string
    {
        return collect(mb_str_split($chars))
            ->map(fn ($c) => 'u' . strtolower(dechex(mb_ord($c))))
            ->join('');
    }
}
