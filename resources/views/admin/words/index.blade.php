@extends('admin.layout')
@section('title', 'Words')

@section('content')

@php
    // Level icons — used in table columns
    $tocflIcons = [
        'tocfl-novice1'  => '🌑',
        'tocfl-novice2'  => '🌑',
        'tocfl-entry'    => '🌒',
        'tocfl-basic'    => '🌓',
        'tocfl-advanced' => '🌔',
        'tocfl-high'     => '🌕',
        'tocfl-fluency'  => '🌝',
    ];
    $hskIcons = [
        'hsk-1' => '🌰',
        'hsk-2' => '🌱',
        'hsk-3' => '🌿',
        'hsk-4' => '🍃',
        'hsk-5' => '🌲',
        'hsk-6' => '🎋',
    ];

    $hasActiveFilter = collect(['q','status','alignment','source','tocfl_level','hsk_level','pos','register','dimension','domain','secondary_domain'])
        ->some(fn ($k) => request()->filled($k));
@endphp

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-bold text-gray-900">Words</h1>
    <div class="flex items-center gap-3">
        @if ($hasActiveFilter)
            {{-- Export dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                    ↓ Export
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" @click.outside="open = false" x-transition
                     class="absolute right-0 mt-1 w-52 bg-white border border-gray-200 rounded-lg shadow-lg z-10 py-1">
                    <a href="{{ route('admin.words.export', array_merge(request()->only(['q','status','alignment','source','tocfl_level','hsk_level','pos','register','dimension','domain','secondary_domain']), ['mode' => 'foundational'])) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <span class="font-medium">Foundational</span>
                        <span class="block text-xs text-gray-400">One row per word</span>
                    </a>
                    <a href="{{ route('admin.words.export', array_merge(request()->only(['q','status','alignment','source','tocfl_level','hsk_level','pos','register','dimension','domain','secondary_domain']), ['mode' => 'by_sense'])) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <span class="font-medium">By Sense</span>
                        <span class="block text-xs text-gray-400">One row per sense + definition</span>
                    </a>
                </div>
            </div>
        @endif
        <a href="{{ route('admin.words.csv-import') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-indigo-300 bg-indigo-50 text-sm font-medium text-indigo-700 hover:bg-indigo-100 transition-colors">
            ↑ Import CSV
        </a>
        <a href="{{ route('admin.words.create') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors">
            + Add word
        </a>
    </div>
</div>

{{-- ── Filters ──────────────────────────────────────────────────────────── --}}
<form method="GET" class="mb-5 space-y-2">

    {{-- Row 1: text search + status --}}
    <div class="flex flex-wrap gap-2">
        <div class="relative" style="width:16rem">
            <input name="q" id="adminSearchInput" value="{{ request('q') }}" placeholder="Search character or definition…"
                   autocomplete="off"
                   class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 w-full"
                   onfocus="showSearchHistory()" oninput="showSearchHistory()">
            <div id="adminSearchHistory" class="absolute top-full left-0 w-full bg-white border border-gray-200 rounded-lg shadow-lg z-50 hidden mt-0.5 max-h-48 overflow-y-auto"></div>
        </div>

        <select name="status"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <option value="">All statuses</option>
            @foreach (['draft', 'review', 'published'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>

        <select name="alignment"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <option value="">Alignment</option>
            <option value="full"     {{ request('alignment') === 'full'     ? 'selected' : '' }}>🤓 Full</option>
            <option value="partial"  {{ request('alignment') === 'partial'  ? 'selected' : '' }}>🤨 Partial</option>
            <option value="disputed" {{ request('alignment') === 'disputed' ? 'selected' : '' }}>😵‍💫 Disputed</option>
            <option value="none"     {{ request('alignment') === 'none'     ? 'selected' : '' }}>— Unset</option>
        </select>

        <button type="submit"
                class="px-4 py-1.5 rounded-lg bg-gray-800 text-sm text-white hover:bg-gray-700 transition-colors">
            Filter
        </button>

        @if ($hasActiveFilter)
            <a href="{{ route('admin.words.index') }}"
               class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700">Clear</a>
        @endif
    </div>

    {{-- Row 2: source + level + POS + designation filters --}}
    <div class="flex flex-wrap gap-2">

        {{-- Source --}}
        <select name="source"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <option value="">Source</option>
            <option value="tocfl"     {{ request('source') === 'tocfl'     ? 'selected' : '' }}>TOCFL</option>
            <option value="editorial" {{ request('source') === 'editorial' ? 'selected' : '' }}>Editorial</option>
        </select>

        {{-- Enriched By --}}
        <select name="enriched_by"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <option value="">Enrichment</option>
            <option value="huiming" {{ request('enriched_by') === 'huiming' ? 'selected' : '' }}>惠明 Huiming</option>
            <option value="shifu"   {{ request('enriched_by') === 'shifu'   ? 'selected' : '' }}>師父 Shifu</option>
            <option value="guangliu" {{ request('enriched_by') === 'guangliu' ? 'selected' : '' }}>光流 Guangliu</option>
            <option value="luoyi"   {{ request('enriched_by') === 'luoyi'   ? 'selected' : '' }}>絡一 Luoyi</option>
            <option value="none"    {{ request('enriched_by') === 'none'    ? 'selected' : '' }}>— Not enriched</option>
        </select>

        {{-- TOCFL Level --}}
        <select name="tocfl_level"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <option value="">TOCFL Level</option>
            @foreach ($tocflLevels as $level)
                @php $icon = $tocflIcons[$level->slug] ?? ''; @endphp
                <option value="{{ $level->id }}" {{ request('tocfl_level') == $level->id ? 'selected' : '' }}>
                    {{ $icon }} {{ $level->labels->first()?->label ?? $level->slug }}
                </option>
            @endforeach
        </select>

        {{-- HSK Level --}}
        <select name="hsk_level"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <option value="">HSK Level</option>
            @foreach ($hskLevels as $level)
                @php $icon = $hskIcons[$level->slug] ?? ''; @endphp
                <option value="{{ $level->id }}" {{ request('hsk_level') == $level->id ? 'selected' : '' }}>
                    {{ $icon }} {{ $level->labels->first()?->label ?? $level->slug }}
                </option>
            @endforeach
        </select>

        {{-- POS — parents as optgroups with "All" + children --}}
        <select name="pos"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <option value="">POS</option>
            @foreach ($posParents as $parent)
                @if ($parent->children->isNotEmpty())
                    <optgroup label="{{ $parent->slug }}">
                        <option value="{{ $parent->id }}" {{ request('pos') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->slug }}
                        </option>
                        @foreach ($parent->children as $child)
                            <option value="{{ $child->id }}" {{ request('pos') == $child->id ? 'selected' : '' }}>
                                &nbsp;&nbsp;{{ $child->slug }}
                            </option>
                        @endforeach
                    </optgroup>
                @else
                    <option value="{{ $parent->id }}" {{ request('pos') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->slug }}
                    </option>
                @endif
            @endforeach
        </select>

        {{-- Register --}}
        <select name="register"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <option value="">Register</option>
            @foreach ($registerDesignations as $d)
                <option value="{{ $d->id }}" {{ request('register') == $d->id ? 'selected' : '' }}>
                    {{ $d->labels->first()?->label ?? $d->slug }}
                </option>
            @endforeach
        </select>

        {{-- Dimension --}}
        <select name="dimension"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <option value="">Dimension</option>
            @foreach ($dimensionDesignations as $d)
                <option value="{{ $d->id }}" {{ request('dimension') == $d->id ? 'selected' : '' }}>
                    {{ $d->labels->first()?->label ?? $d->slug }}
                </option>
            @endforeach
        </select>

        {{-- Domain (primary) + Secondary Domain (cascades via Alpine) --}}
        <div x-data="{ domainId: '{{ request('domain') }}' }" class="flex gap-2">

            {{-- Primary Domain — grouped optgroups --}}
            <select name="domain"
                    x-model="domainId"
                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="">Domain</option>
                @foreach ($domainGroups as $group)
                    @php $groupLabel = $group->labels->first()?->label ?? $group->slug; @endphp
                    <optgroup label="{{ $groupLabel }}">
                        @foreach ($group->designations as $d)
                            <option value="{{ $d->id }}" {{ request('domain') == $d->id ? 'selected' : '' }}>
                                {{ $d->labels->first()?->label ?? $d->slug }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>

            {{-- Secondary Domain — appears only when primary is selected --}}
            <select name="secondary_domain"
                    x-show="domainId"
                    x-transition
                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="">2nd Domain</option>
                @foreach ($domainGroups as $group)
                    @php $groupLabel = $group->labels->first()?->label ?? $group->slug; @endphp
                    <optgroup label="{{ $groupLabel }}">
                        @foreach ($group->designations as $d)
                            <option value="{{ $d->id }}" {{ request('secondary_domain') == $d->id ? 'selected' : '' }}>
                                {{ $d->labels->first()?->label ?? $d->slug }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>

        </div>

    </div>
</form>

{{-- ── Empty / prompt state ────────────────────────────────────────────── --}}
@if ($words === null)
    <div class="bg-white rounded-xl border border-gray-200 px-6 py-16 text-center">
        <p class="text-3xl mb-3">字</p>
        <p class="text-gray-500 text-sm">Search for a character, or use the filters above to find words.</p>
    </div>

{{-- ── Results ──────────────────────────────────────────────────────────── --}}
@else

{{-- Check for CJK terms not in DB --}}
@if (request()->filled('q'))
    @php
        $rawQ2 = request('q');
        $missingTerms = [];
        preg_match_all('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}]+/u', $rawQ2, $m2);
        if (! empty($m2[0])) {
            $foundTrads = \App\Models\WordObject::whereIn('traditional', $m2[0])
                ->orWhereIn('simplified', $m2[0])
                ->pluck('traditional')->toArray();
            $missingTerms = array_values(array_filter($m2[0], fn ($t) => ! in_array($t, $foundTrads)));
        }
    @endphp
    @if (! empty($missingTerms))
        <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 mb-4 flex items-center justify-between">
            <p class="text-sm text-amber-800">
                Not in database: <span class="cn font-medium">{{ implode('、', $missingTerms) }}</span>
            </p>
            <button data-terms="{{ implode(',', $missingTerms) }}" onclick="quickShifuImport(this.dataset.terms.split(','))"
                    class="px-3 py-1.5 rounded-lg bg-indigo-600 text-xs font-semibold text-white hover:bg-indigo-500 transition-colors shrink-0 ml-3">
                Import with 師父
            </button>
        </div>
    @endif
@endif

@php
    $sortUrl = fn (string $col) => route('admin.words.index', array_merge(
        request()->only(['q', 'status', 'alignment', 'source', 'tocfl_level', 'hsk_level', 'pos', 'register', 'dimension', 'domain', 'secondary_domain']),
        [
            'sort'      => $col,
            'direction' => ($sort === $col && $direction === 'asc') ? 'desc' : 'asc',
        ]
    ));
    $sortIcon = fn (string $col) => match (true) {
        $sort === $col && $direction === 'asc'  => '↑',
        $sort === $col && $direction === 'desc' => '↓',
        default                                  => '↕',
    };
    $thActive = fn (string $col) => $sort === $col ? 'text-indigo-600' : 'text-gray-400';
@endphp

<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    @if ($words->isEmpty())
        <div class="px-5 py-8 text-center">
            <p class="text-sm text-gray-400 mb-3">No words match those filters.</p>
            @if (request()->filled('q'))
                @php
                    // Extract CJK terms from the search query
                    $rawQ = request('q');
                    $cjkTerms = [];
                    preg_match_all('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}]+/u', $rawQ, $matches);
                    if (! empty($matches[0])) {
                        $existing = \App\Models\WordObject::whereIn('traditional', $matches[0])
                            ->orWhereIn('simplified', $matches[0])
                            ->pluck('traditional')->toArray();
                        $cjkTerms = array_values(array_filter($matches[0], fn ($t) => ! in_array($t, $existing)));
                    }
                @endphp
                @if (! empty($cjkTerms))
                    <p class="text-sm text-gray-600 mb-2">
                        {{ count($cjkTerms) === 1 ? '1 word' : count($cjkTerms) . ' words' }} not in database:
                        <span class="cn font-medium text-gray-900">{{ implode('、', $cjkTerms) }}</span>
                    </p>
                    <button data-terms="{{ implode(',', $cjkTerms) }}" onclick="quickShifuImport(this.dataset.terms.split(','))"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors">
                        Import with 師父
                    </button>
                @endif
            @endif
        </div>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Char</th>

                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                        <a href="{{ $sortUrl('pinyin') }}"
                           class="inline-flex items-center gap-1 hover:text-gray-700 transition-colors">
                            Pinyin <span class="{{ $thActive('pinyin') }} text-xs">{{ $sortIcon('pinyin') }}</span>
                        </a>
                    </th>

                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">POS · Definition</th>

                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">TOCFL</th>

                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">HSK</th>

                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                        <a href="{{ $sortUrl('status') }}"
                           class="inline-flex items-center gap-1 hover:text-gray-700 transition-colors">
                            Status <span class="{{ $thActive('status') }} text-xs">{{ $sortIcon('status') }}</span>
                        </a>
                    </th>

                    <th class="px-5 py-3 w-12"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($words as $word)
                    @php
                        // When a level filter is active, prefer showing the sense that
                        // actually matches — otherwise fall back to the first sense.
                        $firstSense = $word->senses->first();
                        if (request('tocfl_level')) {
                            $matched = $word->senses->firstWhere('tocfl_level_id', request('tocfl_level'));
                            if ($matched) $firstSense = $matched;
                        } elseif (request('hsk_level')) {
                            $matched = $word->senses->firstWhere('hsk_level_id', request('hsk_level'));
                            if ($matched) $firstSense = $matched;
                        }

                        $pinyin     = $firstSense?->pronunciation?->pronunciation_text;
                        $firstDef   = $firstSense?->definitions->first();
                        $pos        = $firstDef?->posLabel?->slug;
                        $defText    = $firstDef?->definition_text;
                        $extra      = $word->senses_count - 1;

                        $tocflSlug  = $firstSense?->tocflLevel?->slug;
                        $tocflLabel = $firstSense?->tocflLevel?->labels->first()?->label;
                        $hskSlug    = $firstSense?->hskLevel?->slug;
                        $hskLabel   = $firstSense?->hskLevel?->labels->first()?->label;
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">

                        {{-- Char + simplified --}}
                        <td class="px-5 py-3">
                            <span class="cn text-2xl leading-none">{{ $word->traditional }}</span>
                            @if ($word->simplified && $word->simplified !== $word->traditional)
                                <div class="cn text-base text-gray-400 mt-0.5">{{ $word->simplified }}</div>
                            @endif
                        </td>

                        {{-- Pinyin --}}
                        <td class="px-5 py-3 text-gray-600 font-mono text-xs">
                            {{ $pinyin ?? '—' }}
                        </td>

                        {{-- POS · Definition --}}
                        <td class="px-5 py-3">
                            @foreach ($word->senses as $sense)
                                @foreach ($sense->definitions->where('language_id', 1) as $def)
                                    <p class="text-gray-800{{ !$loop->parent->first || !$loop->first ? ' mt-1 pt-1 border-t border-gray-100' : '' }}">
                                        @if ($def->posLabel?->slug)
                                            <span class="font-mono text-xs text-gray-400 mr-1">{{ $def->posLabel->slug }}</span>·
                                        @endif
                                        {{ Str::limit($def->definition_text, 65) }}
                                    </p>
                                @endforeach
                            @endforeach
                        </td>

                        {{-- TOCFL --}}
                        <td class="px-5 py-3 text-xs text-gray-600">
                            @if ($tocflLabel)
                                <span title="{{ $tocflSlug }}">
                                    {{ $tocflIcons[$tocflSlug] ?? '' }} {{ $tocflLabel }}
                                </span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- HSK --}}
                        <td class="px-5 py-3 text-xs text-gray-600">
                            @if ($hskLabel)
                                <span title="{{ $hskSlug }}">
                                    {{ $hskIcons[$hskSlug] ?? '' }} {{ $hskLabel }}
                                </span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-3">
                            @include('admin.partials.status-badge', ['status' => $word->status])
                        </td>

                        {{-- View --}}
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.words.show', $word) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                                View →
                            </a>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="px-5 py-3 border-t border-gray-100">
            {{ $words->links() }}
        </div>
    @endif
</div>

@endif

@push('scripts')
<script>
function quickShifuImport(terms) {
    var csv = terms.join('\n');
    var blob = new Blob([csv], { type: 'text/csv' });
    var file = new File([blob], 'quick-import.csv', { type: 'text/csv' });
    var formData = new FormData();
    formData.append('csv_file', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    fetch('{{ route("admin.words.csv-import.process") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: formData,
    }).then(function(r) {
        if (r.redirected) {
            window.location.href = r.url;
        } else {
            return r.text();
        }
    }).then(function(html) {
        if (html) {
            document.open();
            document.write(html);
            document.close();
        }
    });
}
</script>
<script>
(function() {
    const HISTORY_KEY = 'admin_search_history';
    const MAX_HISTORY = 20;
    const input = document.getElementById('adminSearchInput');
    const dropdown = document.getElementById('adminSearchHistory');
    if (!input || !dropdown) return;

    function getHistory() {
        try { return JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]'); } catch { return []; }
    }

    function saveHistory(term) {
        if (!term.trim()) return;
        let history = getHistory().filter(h => h !== term.trim());
        history.unshift(term.trim());
        if (history.length > MAX_HISTORY) history = history.slice(0, MAX_HISTORY);
        localStorage.setItem(HISTORY_KEY, JSON.stringify(history));
    }

    function showSearchHistory() {
        const history = getHistory();
        const q = input.value.trim().toLowerCase();
        const filtered = q ? history.filter(h => h.toLowerCase().includes(q)) : history;
        if (filtered.length === 0) { dropdown.classList.add('hidden'); return; }

        dropdown.innerHTML = filtered.map(h =>
            `<div class="px-3 py-1.5 text-sm cursor-pointer hover:bg-indigo-50 flex justify-between items-center group" onclick="selectHistory(this, '${h.replace(/'/g, "\\'")}')">
                <span>${h}</span>
                <button onclick="event.stopPropagation(); removeHistory('${h.replace(/'/g, "\\'")}', this)" class="text-gray-300 hover:text-red-400 text-xs opacity-0 group-hover:opacity-100">&times;</button>
            </div>`
        ).join('') + `<div class="px-3 py-1 text-xs text-gray-400 border-t cursor-pointer hover:bg-gray-50" onclick="clearHistory()">Clear history</div>`;
        dropdown.classList.remove('hidden');
    }

    window.showSearchHistory = showSearchHistory;

    window.selectHistory = function(el, term) {
        input.value = term;
        dropdown.classList.add('hidden');
        input.form.submit();
    };

    window.removeHistory = function(term, btn) {
        let history = getHistory().filter(h => h !== term);
        localStorage.setItem(HISTORY_KEY, JSON.stringify(history));
        showSearchHistory();
    };

    window.clearHistory = function() {
        localStorage.removeItem(HISTORY_KEY);
        dropdown.classList.add('hidden');
    };

    // Save current search to history on page load
    const currentQ = input.value.trim();
    if (currentQ) saveHistory(currentQ);

    // Hide dropdown on click outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#adminSearchInput') && !e.target.closest('#adminSearchHistory')) {
            dropdown.classList.add('hidden');
        }
    });

    // Submit on Enter saves to history
    input.form.addEventListener('submit', function() {
        saveHistory(input.value);
    });
})();
</script>
@endpush

@endsection
