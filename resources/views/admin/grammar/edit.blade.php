@extends('admin.layout')
@section('title', 'Edit ' . $pattern->chinese_label)

@section('content')

@php
    // Pre-index existing notes and labels by language_id for form population
    $existingNotes  = $pattern->notes->keyBy('language_id');
    $existingLabels = $pattern->labels->keyBy('language_id');

    // Translation languages for examples (exclude Chinese)
    $translationLangs = $coverageLangs->reject(fn ($l) => str_starts_with($l->code, 'zh'));
@endphp

<div class="max-w-3xl">
    <a href="{{ route('admin.grammar.show', $pattern) }}" class="text-sm text-indigo-600 hover:underline mb-4 inline-block">← Back to {{ $pattern->chinese_label }}</a>

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit: {{ $pattern->chinese_label }}</h1>

    <form method="POST" action="{{ route('admin.grammar.update', $pattern) }}" class="space-y-6">
        @csrf @method('PUT')

        {{-- ── Core fields ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-lg border border-gray-200 p-5 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Core</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chinese Label *</label>
                    <input type="text" name="chinese_label"
                           value="{{ old('chinese_label', $pattern->chinese_label) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           required>
                    @error('chinese_label') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                    <input type="text" name="slug"
                           value="{{ old('slug', $pattern->slug) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           required>
                    @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pattern Template</label>
                <input type="text" name="pattern_template"
                       value="{{ old('pattern_template', $pattern->pattern_template) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                       placeholder="Subject + 把 + Object + Verb + Complement">
                <p class="text-xs text-gray-400 mt-1">Language-neutral structural skeleton</p>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Group</label>
                    <select name="grammar_pattern_group_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                        <option value="">— None —</option>
                        @foreach ($groups as $g)
                            <option value="{{ $g->id }}" {{ old('grammar_pattern_group_id', $pattern->grammar_pattern_group_id) == $g->id ? 'selected' : '' }}>
                                {{ $g->labels->first()?->name ?? $g->slug }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">TOCFL Band</label>
                    <select name="tocfl_level_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                        <option value="">—</option>
                        @foreach ($tocflLevels as $level)
                            <option value="{{ $level->id }}" {{ old('tocfl_level_id', $pattern->tocfl_level_id) == $level->id ? 'selected' : '' }}>
                                {{ $level->labels->first()?->label ?? $level->slug }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">HSK Level</label>
                    <select name="hsk_level_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                        <option value="">—</option>
                        @foreach ($hskLevels as $level)
                            <option value="{{ $level->id }}" {{ old('hsk_level_id', $pattern->hsk_level_id) == $level->id ? 'selected' : '' }}>
                                {{ $level->labels->first()?->label ?? $level->slug }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                <select name="status"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                    @foreach (['draft', 'review', 'published'] as $s)
                        <option value="{{ $s }}" {{ old('status', $pattern->status) === $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ── Labels (i18n names) ────────────────────────────────────────── --}}
        <div class="bg-white rounded-lg border border-gray-200 p-5 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Labels (per language)</h2>

            @foreach ($coverageLangs as $lang)
                @php $existingLabel = $existingLabels->get($lang->id); @endphp
                <div class="border-l-4 border-indigo-200 pl-4 space-y-2">
                    <p class="text-sm font-medium text-gray-600">{{ $lang->name }} ({{ $lang->code }})</p>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Display Name</label>
                        <input type="text" name="labels[{{ $lang->id }}][name]"
                               value="{{ old("labels.{$lang->id}.name", $existingLabel?->name ?? '') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Short Description</label>
                        <input type="text" name="labels[{{ $lang->id }}][short_description]"
                               value="{{ old("labels.{$lang->id}.short_description", $existingLabel?->short_description ?? '') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── Notes (i18n formula / usage / traps) ───────────────────────── --}}
        <div class="bg-white rounded-lg border border-gray-200 p-5 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Notes (per language)</h2>

            @foreach ($coverageLangs as $lang)
                @php $existingNote = $existingNotes->get($lang->id); @endphp
                <details class="border-l-4 border-indigo-200 pl-4" {{ $loop->first ? 'open' : '' }}>
                    <summary class="text-sm font-medium text-gray-600 cursor-pointer py-1">
                        {{ $lang->name }} ({{ $lang->code }})
                        @if ($existingNote?->formula || $existingNote?->usage_note || $existingNote?->learner_traps)
                            <span class="text-green-500 text-xs ml-1">✓ has content</span>
                        @endif
                    </summary>
                    <div class="space-y-3 mt-2">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Formula</label>
                            <input type="text" name="notes[{{ $lang->id }}][formula]"
                                   value="{{ old("notes.{$lang->id}.formula", $existingNote?->formula ?? '') }}"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Usage Note</label>
                            <textarea name="notes[{{ $lang->id }}][usage_note]" rows="3"
                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old("notes.{$lang->id}.usage_note", $existingNote?->usage_note ?? '') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Learner Traps</label>
                            <textarea name="notes[{{ $lang->id }}][learner_traps]" rows="3"
                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old("notes.{$lang->id}.learner_traps", $existingNote?->learner_traps ?? '') }}</textarea>
                        </div>
                    </div>
                </details>
            @endforeach
        </div>

        {{-- ── Submit ──────────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-6 py-2.5 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors">
                Save Changes
            </button>
            <a href="{{ route('admin.grammar.show', $pattern) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
        </div>
    </form>

    {{-- ── Examples (outside main form — each example is its own form) ──── --}}
    <div class="bg-white rounded-lg border border-gray-200 p-5 space-y-4 mt-6">
        <h2 class="text-lg font-semibold text-gray-800">Examples</h2>

        @foreach ($pattern->examples as $ex)
            @php
                $exTranslations = $ex->translations->keyBy(fn ($t) => $t->language_id);
            @endphp
            <div class="border border-gray-100 rounded-lg p-4 space-y-3 bg-gray-50">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400 font-mono">#{{ $ex->id }}</span>
                    <form method="POST" action="{{ route('admin.grammar.examples.destroy', $ex) }}"
                          onsubmit="return confirm('Delete this example?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600">Delete</button>
                    </form>
                </div>

                <form method="POST" action="{{ route('admin.grammar.examples.update', $ex) }}">
                    @csrf @method('PUT')

                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Chinese</label>
                            <textarea name="chinese_text" rows="2"
                                      class="w-full rounded border border-gray-300 px-3 py-2 text-sm font-serif">{{ $ex->chinese_text }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Pinyin</label>
                            <input type="text" name="pinyin_text" value="{{ $ex->pinyin_text }}"
                                   class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                        </div>

                        @foreach ($translationLangs as $tLang)
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ $tLang->name }} Translation</label>
                                <textarea name="translations[{{ $tLang->id }}]" rows="2"
                                          class="w-full rounded border border-gray-300 px-3 py-2 text-sm">{{ $exTranslations->get($tLang->id)?->translation_text ?? '' }}</textarea>
                            </div>
                        @endforeach

                        <div class="flex items-center gap-4">
                            <select name="source" class="rounded border border-gray-300 px-2 py-1 text-xs">
                                @foreach (['default', 'ai_generated', 'shifu', 'community'] as $src)
                                    <option value="{{ $src }}" {{ $ex->source === $src ? 'selected' : '' }}>{{ $src }}</option>
                                @endforeach
                            </select>

                            <label class="flex items-center gap-1.5 text-xs text-gray-500">
                                <input type="checkbox" name="is_suppressed" value="1" {{ $ex->is_suppressed ? 'checked' : '' }}>
                                Suppressed
                            </label>

                            <button type="submit"
                                    class="ml-auto px-3 py-1 rounded bg-indigo-600 text-xs font-medium text-white hover:bg-indigo-500">
                                Save Example
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endforeach

        {{-- Add new example --}}
        <details class="border border-dashed border-gray-300 rounded-lg p-4">
            <summary class="text-sm text-indigo-600 cursor-pointer font-medium">+ Add Example</summary>
            <form method="POST" action="{{ route('admin.grammar.examples.store', $pattern) }}" class="mt-3 space-y-2">
                @csrf

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Chinese *</label>
                    <textarea name="chinese_text" rows="2" required
                              class="w-full rounded border border-gray-300 px-3 py-2 text-sm font-serif"></textarea>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Pinyin</label>
                    <input type="text" name="pinyin_text"
                           class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>

                @foreach ($translationLangs as $tLang)
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ $tLang->name }} Translation</label>
                        <textarea name="translations[{{ $tLang->id }}]" rows="2"
                                  class="w-full rounded border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                @endforeach

                <div class="flex items-center gap-4">
                    <select name="source" class="rounded border border-gray-300 px-2 py-1 text-xs">
                        @foreach (['default', 'ai_generated', 'shifu', 'community'] as $src)
                            <option value="{{ $src }}">{{ $src }}</option>
                        @endforeach
                    </select>

                    <button type="submit"
                            class="ml-auto px-3 py-1 rounded bg-green-600 text-xs font-medium text-white hover:bg-green-500">
                        Add Example
                    </button>
                </div>
            </form>
        </details>
    </div>
</div>

@endsection
