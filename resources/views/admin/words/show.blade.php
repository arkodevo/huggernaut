@php use App\Helpers\PinyinHelper; @endphp
@extends('admin.layout')
@section('title', $word->traditional)

@section('content')

{{-- Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <a href="{{ route('admin.words.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Words</a>
        <div class="flex items-center gap-4 mt-1">
            <h1 class="cn text-4xl font-bold text-gray-900">{{ $word->traditional }}</h1>
            @if ($word->simplified && $word->simplified !== $word->traditional)
                <span class="cn text-2xl text-gray-400">({{ $word->simplified }})</span>
            @endif
            @include('admin.partials.status-badge', ['status' => $word->status])
        </div>
        <p class="text-sm text-gray-500 mt-0.5 font-mono">{{ $word->smart_id }}</p>
        @if ($word->alignment)
            @php
                $alignIcon  = ['full' => '💚', 'partial' => '🟡', 'disputed' => '🟥'][$word->alignment] ?? '';
                $alignLabel = ['full' => 'Full alignment', 'partial' => 'Partial alignment', 'disputed' => 'Disputed'][$word->alignment] ?? $word->alignment;
            @endphp
            <span class="mt-1 inline-flex items-center gap-1 text-xs text-gray-500">
                {{ $alignIcon }} {{ $alignLabel }}
            </span>
        @endif
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.words.edit', $word) }}"
           class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
            Edit
        </a>
        <a href="{{ route('admin.words.senses.create', $word) }}"
           class="px-3 py-1.5 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors">
            + Add sense
        </a>
    </div>
</div>

{{-- Meta --}}
<div class="grid grid-cols-2 gap-4 mb-6 max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs text-gray-500 mb-1">Radical</p>
        <p class="text-sm font-medium text-gray-900">
            {{ $word->radical?->character ?? '—' }}
            @if($word->radical) — {{ $word->radical->meaning_en }} @endif
        </p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs text-gray-500 mb-1">Strokes</p>
        <p class="text-sm font-medium text-gray-900">
            {{ $word->strokes_trad ?? '—' }}
            @if ($word->strokes_simp && $word->strokes_simp !== $word->strokes_trad)
                / {{ $word->strokes_simp }} (simp.)
            @endif
        </p>
    </div>
    @if ($word->subtlex_rank)
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs text-gray-500 mb-1">Frequency <span class="text-gray-400">(SUBTLEX-CH)</span></p>
        <p class="text-sm font-medium text-gray-900">
            #{{ number_format($word->subtlex_rank) }}
            <span class="text-xs text-gray-400 ml-1">{{ number_format($word->subtlex_ppm, 1) }}/M</span>
        </p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs text-gray-500 mb-1">Contextual Diversity</p>
        <p class="text-sm font-medium text-gray-900">{{ $word->subtlex_cd }}%
            <span class="text-xs text-gray-400 ml-1">of films/shows</span>
        </p>
    </div>
    @endif
</div>

{{-- ── Pronunciations ──────────────────────────────────────────────────── --}}
<section class="mb-6 bg-white rounded-xl border border-gray-200">
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-900">Pronunciations</h2>
    </div>

    @if ($word->pronunciations->isEmpty())
        <p class="px-5 py-4 text-sm text-gray-400">No pronunciations yet.</p>
    @else
        <ul class="divide-y divide-gray-100">
            @foreach ($word->pronunciations as $pron)
                <li class="flex items-center justify-between px-5 py-3">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-900">{{ PinyinHelper::toMarked($pron->pronunciation_text) }}</span>
                        <span class="text-xs font-mono text-gray-400">{{ $pron->pronunciation_text }}</span>
                        <span class="text-xs text-gray-400">{{ $pron->pronunciationSystem->name }}</span>
                        @if ($pron->is_primary)
                            <span class="text-xs bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded">primary</span>
                        @endif
                    </div>
                    <form method="POST"
                          action="{{ route('admin.words.pronunciations.destroy', [$word, $pron]) }}"
                          onsubmit="return confirm('Remove this pronunciation?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600">Remove</button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif

    {{-- Add pronunciation form --}}
    <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
        <p class="text-xs font-medium text-gray-600 mb-3">Add pronunciation</p>
        <form method="POST" action="{{ route('admin.words.pronunciations.store', $word) }}"
              class="flex flex-wrap gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-1">System</label>
                <select name="pronunciation_system_id" required
                        class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @foreach (\App\Models\PronunciationSystem::all() as $sys)
                        <option value="{{ $sys->id }}">{{ $sys->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Pronunciation</label>
                <input name="pronunciation_text" required placeholder="e.g. xíng"
                       class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 w-36">
            </div>
            <div class="flex items-center gap-1.5 pb-1.5">
                <input type="checkbox" name="is_primary" id="is_primary" class="h-4 w-4 rounded border-gray-300">
                <label for="is_primary" class="text-xs text-gray-600">Primary</label>
            </div>
            <button type="submit"
                    class="px-3 py-1.5 rounded-lg bg-gray-800 text-xs font-medium text-white hover:bg-gray-700 transition-colors">
                Add
            </button>
        </form>
    </div>
</section>

{{-- ── Senses ───────────────────────────────────────────────────────────── --}}
<section class="bg-white rounded-xl border border-gray-200">
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-900">Senses ({{ $word->senses->count() }})</h2>
        <a href="{{ route('admin.words.senses.create', $word) }}"
           class="text-xs font-medium text-indigo-600 hover:text-indigo-800">+ Add sense</a>
    </div>

    @if ($word->senses->isEmpty())
        <p class="px-5 py-6 text-sm text-gray-400">No senses yet. Add one above.</p>
    @else
        <ul class="divide-y divide-gray-100">
            @foreach ($word->senses as $sense)
                <li class="px-5 py-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-700">{{ $sense->pronunciation ? PinyinHelper::toMarked($sense->pronunciation->pronunciation_text) : '—' }}</span>
                            @if($sense->pronunciation)
                                <span class="text-xs font-mono text-gray-400">{{ $sense->pronunciation->pronunciation_text }}</span>
                            @endif
                            @include('admin.partials.status-badge', ['status' => $sense->status])
                            @if ($sense->tocflLevel)
                                <span class="text-xs text-gray-400">TOCFL: {{ $sense->tocflLevel->labels->first()?->label ?? $sense->tocflLevel->slug }}</span>
                            @endif
                            @if ($sense->hskLevel)
                                <span class="text-xs text-gray-400">HSK: {{ $sense->hskLevel->labels->first()?->label ?? $sense->hskLevel->slug }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ route('admin.words.senses.edit', [$word, $sense]) }}"
                               class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Edit →</a>
                            <form method="POST"
                                  action="{{ route('admin.words.senses.destroy', [$word, $sense]) }}"
                                  onsubmit="return confirm('Delete this sense ({{ $sense->pronunciation->pronunciation_text ?? '' }} · {{ $sense->definitions->first()?->posLabel?->slug ?? '?' }})?\n\nThis removes its definitions, examples, and all related data and cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-xs text-red-400 hover:text-red-600 transition-colors">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    @if ($sense->definitions->isNotEmpty())
                        <ul class="mt-2 space-y-0.5">
                            @foreach ($sense->definitions->take(3) as $def)
                                <li class="text-sm text-gray-600">
                                    <span class="text-xs font-mono text-indigo-600">{{ $def->posLabel->slug ?? '' }}</span>
                                    {{ $def->definition_text }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</section>

@endsection
