@extends('admin.layout')
@section('title', $pattern->chinese_label . ' — Grammar Pattern')

@section('content')

@php
    $tocflIcons = [
        'tocfl-novice1'  => '🌑', 'tocfl-novice2' => '🌑',
        'tocfl-entry'    => '🌒', 'tocfl-basic'    => '🌓',
        'tocfl-advanced' => '🌔', 'tocfl-high'     => '🌕',
        'tocfl-fluency'  => '🌝',
    ];
    $statusIcon = match($pattern->status) {
        'published' => '🟢',
        'review'    => '🟡',
        default     => '⚫',
    };
@endphp

<div class="max-w-4xl">
    <a href="{{ route('admin.grammar.index', ['tab' => 'patterns']) }}" class="text-sm text-indigo-600 hover:underline mb-4 inline-block">← Back to Grammar Patterns</a>

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                {{ $statusIcon }} {{ $pattern->chinese_label }}
            </h1>
            <p class="text-sm text-gray-400 font-mono mt-1">{{ $pattern->slug }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.grammar.edit', $pattern) }}"
               class="px-4 py-2 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors">
                Edit
            </a>
            <form method="POST" action="{{ route('admin.grammar.destroy', $pattern) }}"
                  onsubmit="return confirm('Delete this grammar pattern?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-lg border border-red-300 text-sm text-red-600 hover:bg-red-50 transition-colors">
                    Delete
                </button>
            </form>
        </div>
    </div>

    {{-- ── Meta ────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-lg border border-gray-200 p-5 mb-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-400 block text-xs uppercase tracking-wide">Status</span>
                <span class="font-medium text-gray-800">{{ ucfirst($pattern->status) }}</span>
            </div>
            <div>
                <span class="text-gray-400 block text-xs uppercase tracking-wide">Group</span>
                <span class="font-medium text-gray-800">{{ $pattern->group?->labels->first()?->name ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-400 block text-xs uppercase tracking-wide">TOCFL Band</span>
                @if ($pattern->tocflLevel)
                    <span class="font-medium text-gray-800">
                        {{ $tocflIcons[$pattern->tocflLevel->slug] ?? '' }}
                        {{ $pattern->tocflLevel->labels->first()?->label ?? $pattern->tocflLevel->slug }}
                    </span>
                @else
                    <span class="text-gray-400">—</span>
                @endif
            </div>
            <div>
                <span class="text-gray-400 block text-xs uppercase tracking-wide">HSK Level</span>
                @if ($pattern->hskLevel)
                    <span class="font-medium text-gray-800">
                        {{ $pattern->hskLevel->labels->first()?->label ?? $pattern->hskLevel->slug }}
                    </span>
                @else
                    <span class="text-gray-400">—</span>
                @endif
            </div>
        </div>

        @if ($pattern->pattern_template)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <span class="text-gray-400 block text-xs uppercase tracking-wide mb-1">Pattern Template</span>
                <code class="text-sm font-mono text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded block">
                    {{ $pattern->pattern_template }}
                </code>
            </div>
        @endif
    </div>

    {{-- ── Labels ──────────────────────────────────────────────────────── --}}
    @if ($pattern->labels->isNotEmpty())
        <div class="bg-white rounded-lg border border-gray-200 p-5 mb-5">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Labels</h2>
            @foreach ($pattern->labels as $label)
                <div class="mb-3 last:mb-0">
                    <span class="text-xs text-gray-400 uppercase">{{ $label->language?->name ?? '' }}</span>
                    <p class="font-medium text-gray-800">{{ $label->name }}</p>
                    @if ($label->short_description)
                        <p class="text-sm text-gray-500">{{ $label->short_description }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Notes ───────────────────────────────────────────────────────── --}}
    @if ($pattern->notes->isNotEmpty())
        <div class="bg-white rounded-lg border border-gray-200 p-5 mb-5">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Notes</h2>
            @foreach ($pattern->notes as $note)
                <div class="border-l-4 border-indigo-200 pl-4 mb-4 last:mb-0">
                    <span class="text-xs text-gray-400 uppercase">{{ $note->language?->name ?? '' }}</span>
                    @if ($note->formula)
                        <div class="mt-1">
                            <span class="text-xs text-gray-400">Formula:</span>
                            <code class="text-sm font-mono text-indigo-600 block">{{ $note->formula }}</code>
                        </div>
                    @endif
                    @if ($note->usage_note)
                        <div class="mt-2">
                            <span class="text-xs text-gray-400">Usage Note:</span>
                            <p class="text-sm text-gray-700">{{ $note->usage_note }}</p>
                        </div>
                    @endif
                    @if ($note->learner_traps)
                        <div class="mt-2">
                            <span class="text-xs text-gray-400">Learner Traps:</span>
                            <p class="text-sm text-gray-700">{{ $note->learner_traps }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Linked Word Senses ──────────────────────────────────────────── --}}
    <div class="bg-white rounded-lg border border-gray-200 p-5 mb-5">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">
            Linked Word Senses ({{ $pattern->wordSenses->count() }})
        </h2>
        @if ($pattern->wordSenses->isEmpty())
            <p class="text-gray-400 text-sm">No word senses linked yet. Link them from the edit page.</p>
        @else
            <div class="space-y-2">
                @foreach ($pattern->wordSenses as $ws)
                    <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $ws->pivot->role === 'marker' ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $ws->pivot->role }}
                        </span>
                        <a href="{{ route('admin.words.show', $ws->wordObject) }}" class="text-indigo-600 hover:underline font-medium">
                            {{ $ws->wordObject->traditional }}
                        </a>
                        <span class="text-sm text-gray-500">
                            {{ $ws->pronunciation?->pronunciation_text ?? '' }}
                        </span>
                        <span class="text-sm text-gray-400">
                            {{ $ws->definitions->first()?->posLabel?->slug ?? '' }}
                            — {{ Str::limit($ws->definitions->first()?->definition_text ?? '', 50) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Examples ────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-lg border border-gray-200 p-5 mb-5">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">
            Examples ({{ $pattern->examples->count() }})
        </h2>
        @if ($pattern->examples->isEmpty())
            <p class="text-gray-400 text-sm">No examples yet. Add them from the edit page.</p>
        @else
            @foreach ($pattern->examples as $ex)
                <div class="py-3 border-b border-gray-100 last:border-0">
                    <p class="text-lg font-serif text-gray-900">{{ $ex->chinese_text }}</p>
                    @if ($ex->pinyin_text)
                        <p class="text-sm text-gray-400 italic">{{ $ex->pinyin_text }}</p>
                    @endif
                    @foreach ($ex->translations as $tr)
                        <p class="text-sm text-gray-600">
                            <span class="text-xs text-gray-400 uppercase">{{ $tr->language?->code ?? '' }}:</span>
                            {{ $tr->translation_text }}
                        </p>
                    @endforeach
                    <div class="flex items-center gap-2 mt-1 text-xs text-gray-400">
                        <span>{{ $ex->source }}</span>
                        @if ($ex->is_suppressed)
                            <span class="text-red-400">suppressed</span>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- ── Related Patterns ────────────────────────────────────────────── --}}
    @if ($pattern->relatedPatterns->isNotEmpty())
        <div class="bg-white rounded-lg border border-gray-200 p-5 mb-5">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Related Patterns</h2>
            @foreach ($pattern->relatedPatterns as $rp)
                <div class="flex items-center gap-3 py-1.5">
                    <span class="px-2 py-0.5 rounded text-xs font-mono bg-gray-100 text-gray-600">
                        {{ $rp->pivot->relation_type }}
                    </span>
                    <a href="{{ route('admin.grammar.show', $rp) }}" class="text-indigo-600 hover:underline">
                        {{ $rp->chinese_label }}
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Recent Suggestions ──────────────────────────────────────────── --}}
    @if ($pattern->suggestions->isNotEmpty())
        <div class="bg-white rounded-lg border border-gray-200 p-5 mb-5">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Recent 師父 Suggestions</h2>
            @foreach ($pattern->suggestions as $sug)
                <div class="py-2 border-b border-gray-50 last:border-0 text-sm">
                    <span class="text-gray-500">"{{ $sug->pattern_text }}"</span>
                    @if ($sug->chinese_example)
                        <span class="text-gray-400"> — {{ Str::limit($sug->chinese_example, 40) }}</span>
                    @endif
                    <span class="text-xs text-gray-300 ml-2">{{ $sug->created_at->diffForHumans() }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
