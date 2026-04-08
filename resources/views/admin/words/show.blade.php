@php use App\Helpers\PinyinHelper; @endphp
@extends('admin.layout')
@section('title', $word->traditional)

@push('styles')
    @include('admin.partials.attr-chips')
@endpush

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
                @php
                    $byAttr = $sense->designations->groupBy(fn ($d) => $d->attribute?->slug ?? '');
                    $registers  = $byAttr->get('register',  collect());
                    $dimensions = $byAttr->get('dimension',  collect());
                    $primaryDomain   = $sense->domains->first();
                    $secondaryDomains = $sense->domains->slice(1);
                    $enDefs = $sense->definitions->where('language_id', 1);
                    $zhDefs = $sense->definitions->where('language_id', 2);
                    $valencyMap = [0 => 'Intransitive', 1 => 'Transitive', 2 => 'Ditransitive'];
                @endphp
                <li class="px-5 py-4" x-data="{ open: false }">
                    {{-- Summary row (always visible) --}}
                    <div class="flex items-start justify-between">
                        <button type="button" @click="open = !open" class="flex items-center gap-3 text-left group">
                            <svg class="w-4 h-4 text-indigo-500 transition-transform shrink-0" :class="open && 'rotate-90'" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
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
                        </button>
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

                    {{-- Compact definition preview (always visible) --}}
                    @if ($enDefs->isNotEmpty())
                        <ul class="mt-2 ml-6 space-y-0.5">
                            @foreach ($enDefs->take(3) as $def)
                                <li class="text-sm text-gray-600">
                                    <span class="text-xs font-mono text-indigo-600">{{ $def->posLabel->slug ?? '' }}</span>
                                    {{ $def->definition_text }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if ($zhDefs->isNotEmpty())
                        <ul class="mt-1 ml-6 space-y-0.5">
                            @foreach ($zhDefs->take(3) as $def)
                                <li class="text-sm text-gray-500">
                                    <span class="text-xs font-mono text-indigo-600">{{ $def->posLabel->slug ?? '' }}</span>
                                    <span class="cn">{{ $def->definition_text }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    {{-- Expanded detail (accordion) --}}
                    <div x-show="open" x-transition.duration.150ms class="mt-4 ml-6 space-y-4">

                        {{-- ── All Definitions ──────────────────────────── --}}
                        @if ($sense->definitions->count() > 3)
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">All Definitions ({{ $sense->definitions->count() }})</h4>
                                <ul class="space-y-0.5">
                                    @foreach ($sense->definitions as $def)
                                        <li class="text-sm text-gray-600">
                                            <span class="text-xs text-gray-400">[{{ $def->language->code ?? '?' }}]</span>
                                            <span class="text-xs font-mono text-indigo-600">{{ $def->posLabel->slug ?? '' }}</span>
                                            {{ $def->definition_text }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- ── Reading / Source ──────────────────────────── --}}
                        @if ($sense->source || $sense->alignment)
                            <div class="flex flex-wrap gap-x-2 gap-y-1 text-sm">
                                @if ($sense->source)
                                    <span class="text-gray-600">Source: {{ ucfirst($sense->source) }}</span>
                                @endif
                                @if ($sense->source && $sense->alignment)
                                    <span class="text-gray-400">&nbsp;|&nbsp;</span>
                                @endif
                                @if ($sense->alignment)
                                    @php $aIcon = ['full' => '💚', 'partial' => '🟡', 'disputed' => '🟥'][$sense->alignment] ?? ''; @endphp
                                    <span class="text-gray-600">Alignment: {{ ucfirst($sense->alignment) }} {{ $aIcon }}</span>
                                @endif
                            </div>
                        @endif

                        {{-- ── Attribute Cards (learner-style grid) ─────── --}}
                        @php
                            $registerIcons    = ['literary'=>'🦋','formal'=>'🐝','standard'=>'🐞','colloquial'=>'🪲','informal'=>'🦗','slang'=>'🕷️'];
                            $connotationIcons = ['positive'=>'☀️','positive-dominant'=>'🌤️','neutral'=>'⛅','negative-dominant'=>'🌥️','negative'=>'⛈️','context-dependent'=>'🌦️'];
                            $connotationClass = ['positive'=>'conno-pos','positive-dominant'=>'conno-pos','neutral'=>'conno-neu','negative-dominant'=>'conno-neg','negative'=>'conno-neg','context-dependent'=>'conno-ctx'];
                            $channelIcons     = ['spoken-only'=>'🦎','spoken-dominant'=>'🐍','channel-balanced'=>'🦜','written-dominant'=>'🦚','written-only'=>'🐉'];
                            $dimensionIcons   = ['abstract'=>'🐙','concrete'=>'🐢','internal'=>'🐟','external'=>'🦂','dim-fluid'=>'🦀','aspectual'=>'🐡','grammatical'=>'🪼','spatial'=>'🐚','pragmatic'=>'🦑','temporal'=>'🐠'];
                            $intensityIcons   = [1=>'🌸',2=>'🌼',3=>'🪷',4=>'🌻',5=>'🌺'];
                            $intensityLabels  = [1=>'Faint',2=>'Mild',3=>'Moderate',4=>'Strong',5=>'Blazing'];
                            $hasCards = $sense->channel || $sense->connotation || $sense->semanticMode || $sense->sensitivity
                                     || $registers->isNotEmpty() || $dimensions->isNotEmpty() || $sense->intensity;
                        @endphp
                        @if ($hasCards)
                            <div class="admin-attrs">
                                @if ($registers->isNotEmpty())
                                    <div class="card-attr attr-register">
                                        <div class="card-attr-header">Register</div>
                                        <div class="card-attr-value multi">
                                            @foreach ($registers as $des)
                                                <span class="attr-val-item">
                                                    <span class="attr-icon">{{ $registerIcons[$des->slug] ?? '' }}</span>
                                                    <span class="attr-label">{{ $des->labels->first()?->label ?? ucfirst($des->slug) }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($sense->connotation)
                                    <div class="card-attr attr-connotation {{ $connotationClass[$sense->connotation->slug] ?? 'conno-neu' }}">
                                        <div class="card-attr-header">Connotation</div>
                                        <div class="card-attr-value">
                                            <span class="attr-icon">{{ $connotationIcons[$sense->connotation->slug] ?? '' }}</span>
                                            <span class="attr-label">{{ $sense->connotation->labels->first()?->label ?? ucfirst($sense->connotation->slug) }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if ($sense->channel)
                                    <div class="card-attr attr-channel">
                                        <div class="card-attr-header">Channel</div>
                                        <div class="card-attr-value">
                                            <span class="attr-icon">{{ $channelIcons[$sense->channel->slug] ?? '' }}</span>
                                            <span class="attr-label">{{ $sense->channel->labels->first()?->label ?? ucfirst($sense->channel->slug) }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if ($dimensions->isNotEmpty())
                                    <div class="card-attr attr-dimension">
                                        <div class="card-attr-header">Dimension</div>
                                        <div class="card-attr-value multi">
                                            @foreach ($dimensions as $des)
                                                <span class="attr-val-item">
                                                    <span class="attr-icon">{{ $dimensionIcons[$des->slug] ?? '' }}</span>
                                                    <span class="attr-label">{{ $des->labels->first()?->label ?? ucfirst($des->slug) }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($sense->intensity)
                                    <div class="card-attr attr-intensity">
                                        <div class="card-attr-header">Intensity</div>
                                        <div class="card-attr-value">
                                            <span class="attr-icon">{{ $intensityIcons[$sense->intensity] ?? '' }}</span>
                                            <span class="attr-label">{{ $intensityLabels[$sense->intensity] ?? $sense->intensity }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if ($sense->semanticMode)
                                    <div class="card-attr attr-semantic-mode">
                                        <div class="card-attr-header">Semantic Mode</div>
                                        <div class="card-attr-value">
                                            <span class="attr-label">{{ $sense->semanticMode->labels->first()?->label ?? ucfirst($sense->semanticMode->slug) }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if ($sense->sensitivity)
                                    <div class="card-attr attr-sensitivity">
                                        <div class="card-attr-header">Sensitivity</div>
                                        <div class="card-attr-value">
                                            <span class="attr-label">{{ $sense->sensitivity->labels->first()?->label ?? ucfirst($sense->sensitivity->slug) }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- ── Formula / Usage / Traps (bilingual from word_sense_notes) ── --}}
                        @php
                            $enNote = \DB::table('word_sense_notes')->where('word_sense_id', $sense->id)->where('language_id', 1)->first();
                            $zhNote = \DB::table('word_sense_notes')->where('word_sense_id', $sense->id)->where('language_id', 2)->first();
                            $hasNotes = $enNote || $zhNote || $sense->formula || $sense->usage_note || $sense->learner_traps;
                        @endphp
                        @if ($hasNotes || (isset($sense->valency) && $sense->valency !== null))
                            <div class="space-y-1.5">
                                @if (isset($sense->valency) && $sense->valency !== null)
                                    <p class="text-sm text-gray-600"><span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Valency:</span> {{ $valencyMap[$sense->valency] ?? $sense->valency }}</p>
                                @endif

                                {{-- Formula --}}
                                @if ($enNote?->formula || $zhNote?->formula || $sense->formula)
                                    <div class="text-sm font-mono bg-gray-50 rounded px-2.5 py-1.5 border border-gray-200 space-y-0.5">
                                        @if ($enNote?->formula)
                                            <p class="text-gray-700"><span class="text-xs font-sans font-semibold text-indigo-500">EN</span> {{ $enNote->formula }}</p>
                                        @endif
                                        @if ($zhNote?->formula && $zhNote->formula !== ($enNote?->formula ?? ''))
                                            <p class="text-gray-500"><span class="text-xs font-sans font-semibold text-indigo-500">ZH</span> {{ $zhNote->formula }}</p>
                                        @endif
                                        @if (!$enNote?->formula && !$zhNote?->formula && $sense->formula)
                                            <p class="text-gray-700">{{ $sense->formula }}</p>
                                        @endif
                                    </div>
                                @endif

                                {{-- Usage Note --}}
                                @if ($enNote?->usage_note || $zhNote?->usage_note || $sense->usage_note)
                                    <div class="text-sm bg-amber-50 border border-amber-200 rounded px-2.5 py-1.5">
                                        <span class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Usage Note</span>
                                        @if ($enNote?->usage_note)
                                            <p class="text-amber-900 mt-0.5"><span class="text-xs font-semibold text-indigo-500">EN</span> {{ $enNote->usage_note }}</p>
                                        @endif
                                        @if ($zhNote?->usage_note)
                                            <p class="text-amber-800 mt-0.5 {{ $enNote?->usage_note ? 'opacity-75 border-t border-dashed border-amber-200 pt-1' : '' }}"><span class="text-xs font-semibold text-indigo-500">ZH</span> {{ $zhNote->usage_note }}</p>
                                        @endif
                                        @if (!$enNote?->usage_note && !$zhNote?->usage_note && $sense->usage_note)
                                            <p class="text-amber-900 mt-0.5">{{ $sense->usage_note }}</p>
                                        @endif
                                    </div>
                                @endif

                                {{-- Learner Traps --}}
                                @if ($enNote?->learner_traps || $zhNote?->learner_traps || $sense->learner_traps)
                                    <div class="text-sm bg-red-50 border border-red-200 rounded px-2.5 py-1.5">
                                        <span class="text-xs font-semibold text-red-600 uppercase tracking-wide">Learner Traps</span>
                                        @if ($enNote?->learner_traps)
                                            <p class="text-red-900 mt-0.5"><span class="text-xs font-semibold text-indigo-500">EN</span> {{ $enNote->learner_traps }}</p>
                                        @endif
                                        @if ($zhNote?->learner_traps)
                                            <p class="text-red-800 mt-0.5 {{ $enNote?->learner_traps ? 'opacity-75 border-t border-dashed border-red-200 pt-1' : '' }}"><span class="text-xs font-semibold text-indigo-500">ZH</span> {{ $zhNote->learner_traps }}</p>
                                        @endif
                                        @if (!$enNote?->learner_traps && !$zhNote?->learner_traps && $sense->learner_traps)
                                            <p class="text-red-900 mt-0.5">{{ $sense->learner_traps }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- ── Domains ───────────────────────────────────── --}}
                        @if ($sense->domains->isNotEmpty())
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Domains</h4>
                                <div class="flex flex-wrap gap-2">
                                    @if ($primaryDomain)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 border border-emerald-200 px-2 py-0.5 text-xs text-emerald-700 font-medium">
                                            {{ $primaryDomain->labels->first()?->label ?? $primaryDomain->slug }}
                                        </span>
                                    @endif
                                    @foreach ($secondaryDomains as $dom)
                                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                                            {{ $dom->labels->first()?->label ?? $dom->slug }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- ── Examples ──────────────────────────────────── --}}
                        @if ($sense->examples->isNotEmpty())
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Examples ({{ $sense->examples->count() }})</h4>
                                <ul class="space-y-2">
                                    @foreach ($sense->examples as $ex)
                                        <li class="text-sm border-l-2 {{ $ex->is_suppressed ? 'border-red-200 opacity-60' : 'border-gray-200' }} pl-3">
                                            <p class="cn text-gray-800">{{ $ex->chinese_text }}</p>
                                            @if ($ex->english_text)
                                                <p class="text-gray-500 text-xs mt-0.5">{{ $ex->english_text }}</p>
                                            @endif
                                            <div class="flex gap-2 mt-0.5">
                                                <span class="text-xs text-gray-400">{{ $ex->source }}</span>
                                                @if ($ex->is_suppressed)
                                                    <span class="text-xs text-red-400">suppressed</span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- ── Relations ─────────────────────────────────── --}}
                        @if ($sense->senseRelations->isNotEmpty())
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Relations ({{ $sense->senseRelations->count() }})</h4>
                                <ul class="space-y-0.5">
                                    @foreach ($sense->senseRelations as $rel)
                                        <li class="text-sm text-gray-600">
                                            <span class="text-xs font-mono text-purple-600">{{ $rel->relationType->labels->first()?->label ?? $rel->relationType->slug ?? '?' }}</span>
                                            →
                                            <span class="cn font-medium">{{ $rel->related_word_text }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</section>

@endsection
