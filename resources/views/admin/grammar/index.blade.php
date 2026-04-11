@extends('admin.layout')
@section('title', 'Grammar Patterns')

@section('content')

@php
    $tocflIcons = [
        'tocfl-novice1'  => '🌑', 'tocfl-novice2' => '🌑',
        'tocfl-entry'    => '🌒', 'tocfl-basic'    => '🌓',
        'tocfl-advanced' => '🌔', 'tocfl-high'     => '🌕',
        'tocfl-fluency'  => '🌝',
    ];

    $hasActiveFilter = collect(['q','status','group','tocfl_level','hsk_level'])
        ->some(fn ($k) => request()->filled($k));
@endphp

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-bold text-gray-900">Grammar Patterns</h1>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.grammar.create') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors">
            + New Pattern
        </a>
    </div>
</div>

{{-- ── Tabs ────────────────────────────────────────────────────────────── --}}
<div class="flex gap-1 mb-5 border-b border-gray-200">
    <a href="{{ route('admin.grammar.index', ['tab' => 'patterns']) }}"
       class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
              {{ $tab === 'patterns'
                  ? 'border-indigo-600 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
        📚 Patterns
    </a>
    <a href="{{ route('admin.grammar.index', ['tab' => 'suggestions']) }}"
       class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors inline-flex items-center gap-1.5
              {{ $tab === 'suggestions'
                  ? 'border-indigo-600 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
        🙏 師父 Suggestions
        @if ($pendingSuggestionCount > 0)
            <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-rose-500 rounded-full">
                {{ $pendingSuggestionCount }}
            </span>
        @endif
    </a>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- ── PATTERNS TAB ──────────────────────────────────────────────────── --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
@if ($tab === 'patterns')

    {{-- Filters --}}
    <form method="GET" class="mb-5 space-y-2">
        <input type="hidden" name="tab" value="patterns">

        <div class="flex flex-wrap gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Search label, slug, or formula..."
                   class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                   style="width:18rem">

            <select name="status"
                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none">
                <option value="">All statuses</option>
                @foreach (['draft', 'review', 'published'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                @endforeach
            </select>

            <select name="group"
                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none">
                <option value="">All groups</option>
                @foreach ($groups as $g)
                    <option value="{{ $g->id }}" {{ request('group') == $g->id ? 'selected' : '' }}>
                        {{ $g->labels->first()?->name ?? $g->slug }}
                    </option>
                @endforeach
            </select>

            <select name="tocfl_level"
                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none">
                <option value="">TOCFL Level</option>
                @foreach ($tocflLevels as $level)
                    <option value="{{ $level->id }}" {{ request('tocfl_level') == $level->id ? 'selected' : '' }}>
                        {{ $tocflIcons[$level->slug] ?? '' }} {{ $level->labels->first()?->label ?? $level->slug }}
                    </option>
                @endforeach
            </select>

            <button type="submit"
                    class="rounded-lg bg-indigo-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors">
                Filter
            </button>

            @if ($hasActiveFilter)
                <a href="{{ route('admin.grammar.index', ['tab' => 'patterns']) }}"
                   class="rounded-lg border border-gray-300 px-4 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors inline-flex items-center">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- Results --}}
    @if ($patterns === null)
        <p class="text-gray-500 text-sm">Apply filters to see grammar patterns, or <a href="{{ route('admin.grammar.create') }}" class="text-indigo-600 hover:underline">create a new pattern</a>.</p>
    @elseif ($patterns->isEmpty())
        <p class="text-gray-500 text-sm">No grammar patterns match your filters.</p>
    @else
        <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Status</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Label</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Name (EN)</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Formula</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Group</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Band</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Links</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($patterns as $p)
                        @php
                            $statusIcon = match($p->status) {
                                'published' => '🟢',
                                'review'    => '🟡',
                                default     => '⚫',
                            };
                            $tocflSlug = $p->tocflLevel?->slug ?? '';
                            $levelIcon = $tocflIcons[$tocflSlug] ?? '';
                            $levelLabel = $p->tocflLevel?->labels->first()?->label ?? '';
                        @endphp
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 text-center">{{ $statusIcon }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ route('admin.grammar.show', $p) }}" class="text-indigo-600 hover:underline font-medium">
                                    {{ $p->chinese_label }}
                                </a>
                            </td>
                            <td class="px-4 py-2 text-gray-600">
                                {{ $p->labels->first()?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-2 text-gray-500 font-mono text-xs">
                                {{ Str::limit($p->pattern_template, 40) ?? '—' }}
                            </td>
                            <td class="px-4 py-2 text-gray-500">
                                {{ $p->group?->labels->first()?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                {{ $levelIcon }} {{ $levelLabel }}
                            </td>
                            <td class="px-4 py-2 text-gray-500">
                                {{ $p->word_senses_count }} ws
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $patterns->links() }}</div>
    @endif

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- ── SUGGESTIONS TAB ───────────────────────────────────────────────── --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
@else

    {{-- Status filter --}}
    <div class="flex gap-2 mb-4">
        @foreach (['pending', 'accepted', 'rejected', 'all'] as $s)
            @php
                $count = $s === 'all'
                    ? array_sum($suggestionCounts ?? [])
                    : ($suggestionCounts[$s] ?? 0);
                $isActive = request('sug_status', 'pending') === $s;
            @endphp
            <a href="{{ route('admin.grammar.index', ['tab' => 'suggestions', 'sug_status' => $s]) }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                      {{ $isActive
                          ? 'bg-indigo-100 text-indigo-700 border border-indigo-300'
                          : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50' }}">
                {{ ucfirst($s) }} ({{ $count }})
            </a>
        @endforeach
    </div>

    @if ($suggestions->isEmpty())
        <p class="text-gray-500 text-sm">No suggestions in this category.</p>
    @else
        <div class="space-y-3">
            @foreach ($suggestions as $sug)
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-gray-900 text-lg">{{ $sug->pattern_text }}</span>
                                @php
                                    $sugStatusColor = match($sug->status) {
                                        'pending'  => 'bg-amber-100 text-amber-700',
                                        'accepted' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        'duplicate' => 'bg-gray-100 text-gray-600',
                                        default    => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $sugStatusColor }}">
                                    {{ $sug->status }}
                                </span>
                            </div>

                            @if ($sug->chinese_example)
                                <p class="text-gray-700 mb-1">
                                    <span class="text-xs text-gray-400 uppercase tracking-wide">Context:</span>
                                    <span class="font-serif">{{ $sug->chinese_example }}</span>
                                </p>
                            @endif

                            @if ($sug->shifu_notes)
                                <p class="text-sm text-gray-500 italic">{{ $sug->shifu_notes }}</p>
                            @endif

                            <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                                <span>{{ $sug->user?->name ?? 'Unknown' }}</span>
                                <span>{{ $sug->created_at->diffForHumans() }}</span>
                                @if ($sug->pattern)
                                    <span>→ linked to
                                        <a href="{{ route('admin.grammar.show', $sug->pattern) }}" class="text-indigo-500 hover:underline">
                                            {{ $sug->pattern->chinese_label }}
                                        </a>
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if ($sug->status === 'pending')
                            <div class="flex items-center gap-2 shrink-0">
                                {{-- Accept → create draft --}}
                                <form method="POST" action="{{ route('admin.grammar.suggestions.accept', $sug) }}">
                                    @csrf
                                    <button type="submit" title="Accept — create draft pattern"
                                            class="px-3 py-1.5 rounded-lg text-sm font-medium bg-green-50 text-green-700 border border-green-300 hover:bg-green-100 transition-colors">
                                        ✓ Accept
                                    </button>
                                </form>

                                {{-- Reject --}}
                                <form method="POST" action="{{ route('admin.grammar.suggestions.reject', $sug) }}">
                                    @csrf
                                    <button type="submit" title="Reject — not a useful pattern"
                                            class="px-3 py-1.5 rounded-lg text-sm font-medium bg-red-50 text-red-700 border border-red-300 hover:bg-red-100 transition-colors">
                                        ✗ Reject
                                    </button>
                                </form>

                                {{-- Link to existing --}}
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" title="Link to existing pattern"
                                            class="px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-50 text-gray-600 border border-gray-300 hover:bg-gray-100 transition-colors">
                                        🔗 Link
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-transition
                                         class="absolute right-0 mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow-lg z-20 p-3">
                                        <form method="POST" action="{{ route('admin.grammar.suggestions.link', $sug) }}">
                                            @csrf
                                            <label class="block text-xs text-gray-500 mb-1">Select existing pattern:</label>
                                            <select name="grammar_pattern_id"
                                                    class="w-full rounded border border-gray-300 px-2 py-1 text-sm mb-2">
                                                <option value="">—</option>
                                                @php
                                                    $allPatterns = \App\Models\GrammarPattern::orderBy('chinese_label')->get();
                                                @endphp
                                                @foreach ($allPatterns as $gp)
                                                    <option value="{{ $gp->id }}">{{ $gp->chinese_label }} ({{ $gp->slug }})</option>
                                                @endforeach
                                            </select>
                                            <button type="submit"
                                                    class="w-full rounded bg-indigo-600 px-3 py-1 text-sm font-medium text-white hover:bg-indigo-500">
                                                Link
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $suggestions->links() }}</div>
    @endif

@endif

@endsection
