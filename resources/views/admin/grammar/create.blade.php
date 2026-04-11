@extends('admin.layout')
@section('title', 'Create Grammar Pattern')

@section('content')

<div class="max-w-3xl">
    <a href="{{ route('admin.grammar.index') }}" class="text-sm text-indigo-600 hover:underline mb-4 inline-block">← Back to Grammar Patterns</a>

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Grammar Pattern</h1>

    @if ($suggestion)
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-amber-800">
                <strong>Creating from 師父 suggestion:</strong> "{{ $suggestion->pattern_text }}"
            </p>
            @if ($suggestion->chinese_example)
                <p class="text-sm text-amber-700 mt-1">Context: <span class="font-serif">{{ $suggestion->chinese_example }}</span></p>
            @endif
            @if ($suggestion->shifu_notes)
                <p class="text-sm text-amber-600 mt-1 italic">{{ $suggestion->shifu_notes }}</p>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('admin.grammar.store') }}" class="space-y-6">
        @csrf

        @if ($suggestion)
            <input type="hidden" name="from_suggestion" value="{{ $suggestion->id }}">
        @endif

        {{-- ── Core fields ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-lg border border-gray-200 p-5 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Core</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chinese Label *</label>
                    <input type="text" name="chinese_label"
                           value="{{ old('chinese_label', $suggestion->pattern_text ?? '') }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="把字句" required>
                    @error('chinese_label') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                    <input type="text" name="slug"
                           value="{{ old('slug') }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="ba-construction" required>
                    @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pattern Template</label>
                <input type="text" name="pattern_template"
                       value="{{ old('pattern_template') }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                       placeholder="Subject + 把 + Object + Verb + Complement">
                <p class="text-xs text-gray-400 mt-1">Language-neutral structural skeleton</p>
                @error('pattern_template') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Group</label>
                    <select name="grammar_pattern_group_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                        <option value="">— None —</option>
                        @foreach ($groups as $g)
                            <option value="{{ $g->id }}" {{ old('grammar_pattern_group_id') == $g->id ? 'selected' : '' }}>
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
                            <option value="{{ $level->id }}" {{ old('tocfl_level_id') == $level->id ? 'selected' : '' }}>
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
                            <option value="{{ $level->id }}" {{ old('hsk_level_id') == $level->id ? 'selected' : '' }}>
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
                        <option value="{{ $s }}" {{ old('status', 'draft') === $s ? 'selected' : '' }}>
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
                <div class="border-l-4 border-indigo-200 pl-4 space-y-2">
                    <p class="text-sm font-medium text-gray-600">{{ $lang->name }} ({{ $lang->code }})</p>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Display Name</label>
                        <input type="text" name="labels[{{ $lang->id }}][name]"
                               value="{{ old("labels.{$lang->id}.name") }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                               placeholder="{{ $lang->code === 'en' ? 'The Ba Construction' : '把字句' }}">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Short Description</label>
                        <input type="text" name="labels[{{ $lang->id }}][short_description]"
                               value="{{ old("labels.{$lang->id}.short_description") }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                               placeholder="One-line summary for search results">
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── Notes (i18n formula / usage / traps) ───────────────────────── --}}
        <div class="bg-white rounded-lg border border-gray-200 p-5 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Notes (per language)</h2>

            @foreach ($coverageLangs as $lang)
                <details class="border-l-4 border-indigo-200 pl-4" {{ $loop->first ? 'open' : '' }}>
                    <summary class="text-sm font-medium text-gray-600 cursor-pointer py-1">
                        {{ $lang->name }} ({{ $lang->code }})
                    </summary>
                    <div class="space-y-3 mt-2">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Formula</label>
                            <input type="text" name="notes[{{ $lang->id }}][formula]"
                                   value="{{ old("notes.{$lang->id}.formula") }}"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                                   placeholder="{{ $lang->code === 'en' ? '[Subject] + 把 + [Object] + [Verb-Complement]' : '[主語] + 把 + [賓語] + [動補]' }}">
                        </div>

                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Usage Note</label>
                            <textarea name="notes[{{ $lang->id }}][usage_note]" rows="3"
                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old("notes.{$lang->id}.usage_note") }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Learner Traps</label>
                            <textarea name="notes[{{ $lang->id }}][learner_traps]" rows="3"
                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old("notes.{$lang->id}.learner_traps") }}</textarea>
                        </div>
                    </div>
                </details>
            @endforeach
        </div>

        {{-- ── Submit ──────────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-6 py-2.5 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors">
                Create Pattern
            </button>
            <a href="{{ route('admin.grammar.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
        </div>
    </form>
</div>

@endsection
