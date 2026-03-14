@extends('admin.layout')
@section('title', 'Words')

@section('content')

@php
    // Level icons — used in table columns
    $tocflIcons = [
        'tocfl-prep'     => '🌑',
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

    $hasActiveFilter = collect(['q','status','tocfl_level','hsk_level','pos','register','dimension','domain','secondary_domain'])
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
                    <a href="{{ route('admin.words.export', array_merge(request()->only(['q','status','tocfl_level','hsk_level','pos','register','dimension','domain','secondary_domain']), ['mode' => 'foundational'])) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <span class="font-medium">Foundational</span>
                        <span class="block text-xs text-gray-400">One row per word</span>
                    </a>
                    <a href="{{ route('admin.words.export', array_merge(request()->only(['q','status','tocfl_level','hsk_level','pos','register','dimension','domain','secondary_domain']), ['mode' => 'by_sense'])) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <span class="font-medium">By Sense</span>
                        <span class="block text-xs text-gray-400">One row per sense + definition</span>
                    </a>
                </div>
            </div>
        @endif
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
        <input name="q" value="{{ request('q') }}" placeholder="Search character or definition…"
               class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 w-64">

        <select name="status"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <option value="">All statuses</option>
            @foreach (['draft', 'review', 'published'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
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

    {{-- Row 2: level + POS + designation filters --}}
    <div class="flex flex-wrap gap-2">

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
                            {{ $parent->slug }} — All
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

@php
    $sortUrl = fn (string $col) => route('admin.words.index', array_merge(
        request()->only(['q', 'status', 'tocfl_level', 'hsk_level', 'pos', 'register', 'dimension', 'domain', 'secondary_domain']),
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
        <p class="px-5 py-8 text-sm text-gray-400 text-center">No words match those filters.</p>
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
                        $firstSense = $word->senses->first();
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
                            @if ($firstDef)
                                <p class="text-gray-800">
                                    @if ($pos)
                                        <span class="font-mono text-xs text-gray-400 mr-1">{{ $pos }}</span>·
                                    @endif
                                    {{ Str::limit($defText, 65) }}
                                </p>
                            @endif
                            @if ($extra > 0)
                                <a href="{{ route('admin.words.show', $word) }}"
                                   class="text-xs text-indigo-500 hover:text-indigo-700 mt-0.5 inline-block">
                                    +{{ $extra }} more
                                </a>
                            @endif
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

@endsection
