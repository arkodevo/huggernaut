@extends('admin.layout')
@section('title', 'Edit Sense — ' . $word->traditional)

@section('content')

<div class="mb-6">
    <a href="{{ route('admin.words.show', $word) }}" class="text-sm text-gray-500 hover:text-gray-700">← {{ $word->traditional }}</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-1">Edit Sense</h1>
</div>

@include('admin.senses._form', [
    'action'       => route('admin.words.senses.update', [$word, $sense]),
    'method'       => 'PUT',
    'sense'        => $sense,
    'word'         => $word,
    'attributes'   => $attributes,
    'posLabels'    => $posLabels,
    'languages'    => $languages,
    'existingDefs' => $sense->definitions->map(fn ($d) => [
        'id'              => $d->id,
        'language_id'     => $d->language_id,
        'pos_id'          => $d->pos_id,
        'definition_text' => $d->definition_text,
        'sort_order'      => $d->sort_order,
    ])->values()->all(),
])

{{-- ── Examples section (edit only) ──────────────────────────────────── --}}
@php
    $coverageLangs = \App\Models\Language::where('has_notes_coverage', true)->orderBy('id')->get();
    // For example translations: exclude Chinese — the source sentence is already Chinese
    $translationLangs = $coverageLangs->reject(fn ($l) => str_starts_with($l->code, 'zh'));
    // Eager-load translations keyed by language_id for each example
    $sense->load('examples.translations');
@endphp

<div class="mt-6 bg-white rounded-xl border border-gray-200">
    <div class="px-5 py-3 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-900">
            Examples ({{ $sense->examples->count() }})
            <span class="text-xs text-gray-400 font-normal ml-2">{{ $translationLangs->pluck('code')->implode(' + ') }}</span>
        </h2>
    </div>

    @if ($sense->examples->isEmpty())
        <p class="px-5 py-4 text-sm text-gray-400">No examples yet.</p>
    @else
        @foreach ($sense->examples as $ex)
            @php $exTranslations = $ex->translations->keyBy('language_id'); @endphp
            <div class="example-wrap px-5 py-4 border-b border-gray-100 {{ $ex->is_suppressed ? 'bg-red-50/40' : '' }}">
                <form method="POST" action="{{ route('admin.examples.update', $ex) }}" class="space-y-2">
                    @csrf @method('PUT')

                    {{-- Chinese text (source sentence — always present) --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Chinese</label>
                        <textarea name="chinese_text" rows="2" required
                                  class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm cn focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ $ex->chinese_text }}</textarea>
                    </div>

                    {{-- Per-coverage-language translations --}}
                    @foreach ($translationLangs as $cl)
                        <div>
                            <label class="block text-xs font-semibold text-indigo-500 mb-1">{{ strtoupper($cl->code) }} · {{ $cl->name }}</label>
                            <textarea name="translations[{{ $cl->id }}]" rows="1"
                                      placeholder="{{ $cl->name }} translation…"
                                      class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ $exTranslations->get($cl->id)?->translation_text ?? '' }}</textarea>
                        </div>
                    @endforeach

                    {{-- Meta row: source, suppressed, save, delete --}}
                    <div class="flex items-center gap-3 pt-1">
                        <select name="source"
                                class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            @foreach (['default', 'ai_generated', 'student', 'community'] as $s)
                                <option value="{{ $s }}" {{ $ex->source === $s ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $s)) }}
                                </option>
                            @endforeach
                        </select>

                        <label class="flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer">
                            <input type="hidden" name="is_suppressed" value="0">
                            <input type="checkbox" name="is_suppressed" value="1"
                                   class="h-3.5 w-3.5 rounded border-gray-300 text-red-500"
                                   {{ $ex->is_suppressed ? 'checked' : '' }}>
                            Suppressed
                        </label>

                        <button type="submit"
                                class="px-3 py-1.5 rounded-lg bg-gray-800 text-xs font-medium text-white hover:bg-gray-700 transition-colors">
                            Save
                        </button>

                        <span class="ml-auto">
                            <button type="button"
                                    data-confirm="Click again to delete example"
                                    onclick="this.closest('.example-wrap').querySelector('.delete-form').submit();"
                                    class="text-xs text-red-400 hover:text-red-600 px-1.5 py-0.5 rounded">Delete</button>
                        </span>
                    </div>
                </form>

                {{-- Separate delete form --}}
                <form method="POST" action="{{ route('admin.examples.destroy', $ex) }}" class="delete-form hidden">
                    @csrf @method('DELETE')
                </form>
            </div>
        @endforeach
    @endif

    {{-- Add example --}}
    <div class="px-5 py-4 bg-gray-50 rounded-b-xl">
        <p class="text-xs font-medium text-gray-600 mb-3">Add example</p>
        <form method="POST" action="{{ route('admin.senses.examples.store', $sense) }}" class="space-y-2">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Chinese</label>
                <textarea name="chinese_text" rows="2" placeholder="Chinese example sentence…" required
                          class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm cn focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
            </div>
            {{-- Translation fields — exclude zh-* (Chinese is the source, not a translation target) --}}
            @foreach ($translationLangs as $cl)
                <div>
                    <label class="block text-xs font-semibold text-indigo-500 mb-1">{{ strtoupper($cl->code) }} · {{ $cl->name }}</label>
                    <textarea name="translations[{{ $cl->id }}]" rows="1"
                              placeholder="{{ $cl->name }} translation…"
                              class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                </div>
            @endforeach
            <div class="flex gap-3 items-center pt-1">
                <select name="source"
                        class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @foreach (['default', 'ai_generated', 'student', 'community'] as $s)
                        <option value="{{ $s }}">{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-3 py-1.5 rounded-lg bg-gray-800 text-xs font-medium text-white hover:bg-gray-700 transition-colors">
                    Add
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
