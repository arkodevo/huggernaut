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
                          action="{{ route('admin.words.pronunciations.destroy', [$word, $pron]) }}">
                        @csrf @method('DELETE')
                        <button type="submit" data-confirm="Click again to remove pronunciation"
                                class="text-xs text-red-400 hover:text-red-600 px-1.5 py-0.5 rounded">Remove</button>
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
                                  action="{{ route('admin.words.senses.destroy', [$word, $sense]) }}">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        data-confirm="Click again — deletes definitions, examples, and all related data"
                                        class="text-xs text-red-400 hover:text-red-600 px-1.5 py-0.5 rounded transition-colors">
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
                            $channelIcons     = ['spoken-only'=>'🦎','spoken-dominant'=>'🐍','balanced'=>'🦜','written-dominant'=>'🦚','written-only'=>'🐉'];
                            $dimensionIcons   = ['abstract'=>'🐙','concrete'=>'🐢','internal'=>'🐟','external'=>'🦂','dim-fluid'=>'🦀','aspectual'=>'🐡','grammatical'=>'🪼','spatial'=>'🐚','pragmatic'=>'🦑','temporal'=>'🐠'];
                            $intensityIcons   = [1=>'🌸',2=>'🌼',3=>'🪷',4=>'🌻',5=>'🌺'];
                            $intensityLabels  = [1=>'Faint',2=>'Mild',3=>'Moderate',4=>'Strong',5=>'Blazing'];
                            $hasCards = $sense->channel || $sense->connotation || $sense->sensitivity
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

                        {{-- ── Notes (bilingual, dynamic note types) ── --}}
                        @php
                            $noteRows = \DB::table('word_sense_notes')
                                ->join('note_types', 'word_sense_notes.note_type_id', '=', 'note_types.id')
                                ->leftJoin('note_type_labels', function ($j) {
                                    $j->on('note_type_labels.note_type_id', '=', 'note_types.id')
                                      ->where('note_type_labels.language_id', 1);
                                })
                                ->where('word_sense_notes.word_sense_id', $sense->id)
                                ->select(
                                    'word_sense_notes.language_id',
                                    'word_sense_notes.content',
                                    'note_types.slug as note_slug',
                                    'note_types.sort_order',
                                    'note_type_labels.label as note_label',
                                )
                                ->orderBy('note_types.sort_order')
                                ->get();

                            // Group: slug => [ lang_id => row ]
                            $notesByType = $noteRows->groupBy('note_slug')->map(fn ($rows) => $rows->keyBy('language_id'));
                            $hasNotes = $noteRows->isNotEmpty();

                            // Styling per note type slug
                            $noteStyles = [
                                'formula'       => 'text-sm font-mono bg-gray-50 rounded px-2.5 py-1.5 border border-gray-200 space-y-0.5',
                                'usage_note'    => 'text-sm bg-amber-50 border border-amber-200 rounded px-2.5 py-1.5',
                                'learner_traps' => 'text-sm bg-red-50 border border-red-200 rounded px-2.5 py-1.5',
                            ];
                            $noteHeaderStyles = [
                                'formula'       => '',
                                'usage_note'    => 'text-xs font-semibold text-amber-600 uppercase tracking-wide',
                                'learner_traps' => 'text-xs font-semibold text-red-600 uppercase tracking-wide',
                            ];
                            $noteTextStyles = [
                                'usage_note'    => ['text-amber-900 mt-0.5', 'text-amber-800 mt-0.5'],
                                'learner_traps' => ['text-red-900 mt-0.5', 'text-red-800 mt-0.5'],
                            ];
                            $noteDividerStyles = [
                                'usage_note'    => 'opacity-75 border-t border-dashed border-amber-200 pt-1',
                                'learner_traps' => 'opacity-75 border-t border-dashed border-red-200 pt-1',
                            ];
                        @endphp
                        @if ($hasNotes || (isset($sense->valency) && $sense->valency !== null))
                            <div class="space-y-1.5">
                                @if (isset($sense->valency) && $sense->valency !== null)
                                    <p class="text-sm text-gray-600"><span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Valency:</span> {{ $valencyMap[$sense->valency] ?? $sense->valency }}</p>
                                @endif

                                @foreach ($notesByType as $slug => $langNotes)
                                    @php
                                        $enContent = $langNotes->get(1)?->content;
                                        $zhContent = $langNotes->get(2)?->content;
                                        $label = $langNotes->first()->note_label ?? str_replace('_', ' ', ucfirst($slug));
                                        $wrapClass = $noteStyles[$slug] ?? 'text-sm bg-gray-50 rounded px-2.5 py-1.5 border border-gray-200 space-y-0.5';
                                        $headerClass = $noteHeaderStyles[$slug] ?? '';
                                        $textEn = $noteTextStyles[$slug][0] ?? 'text-gray-700';
                                        $textZh = $noteTextStyles[$slug][1] ?? 'text-gray-500';
                                        $divider = $noteDividerStyles[$slug] ?? '';
                                    @endphp
                                    @if ($enContent || $zhContent)
                                        <div class="{{ $wrapClass }}">
                                            @if ($headerClass)
                                                <span class="{{ $headerClass }}">{{ $label }}</span>
                                            @endif
                                            @if ($enContent)
                                                <p class="{{ $textEn }}"><span class="text-xs font-sans font-semibold text-indigo-500">EN</span> {{ $enContent }}</p>
                                            @endif
                                            @if ($zhContent)
                                                <p class="{{ $textZh }} {{ $enContent && $divider ? $divider : '' }}"><span class="text-xs font-sans font-semibold text-indigo-500">ZH</span> {{ $zhContent }}</p>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
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
                                            @if ($ex->englishTranslation)
                                                <p class="text-gray-500 text-xs mt-0.5">{{ $ex->englishTranslation }}</p>
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
