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

    <div class="flex items-start justify-between mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-900">Edit: {{ $pattern->chinese_label }}</h1>
        <button type="button" id="gp-enrich-btn"
                data-url="{{ route('admin.grammar.enrich', $pattern) }}"
                class="shrink-0 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500 transition shadow-sm">
            🙏 Enrich with 師父
        </button>
    </div>

    {{-- ── 師父 Enrichment Preview (populated by AJAX) ────────────────────── --}}
    <div id="gp-enrich-panel" class="hidden bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg p-5 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-indigo-900">🙏 師父 suggests the following enrichment</h3>
            <button type="button" onclick="document.getElementById('gp-enrich-panel').classList.add('hidden')"
                    class="text-xs text-indigo-500 hover:text-indigo-700">✕ Close</button>
        </div>
        <p class="text-xs text-indigo-700 mb-4">Review each section. Click <strong>Apply</strong> to populate the form fields below — nothing is saved until you click <strong>Save Changes</strong>. Examples get their own individual <strong>+ Add</strong> buttons.</p>
        <div id="gp-enrich-content" class="space-y-5"></div>
    </div>

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

        {{-- ── Notes (bilingual, grouped by note type) ──────────────────────
             Each note type is one collapsible section; each section shows all
             coverage languages inside. Matches the word-sense form pattern so
             reviewers see EN + ZH side-by-side per concept (not two separate
             language silos). --}}
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Notes</h2>

            @php
                $grammarNoteTypes = [
                    ['field' => 'formula',        'label' => 'Formula',       'input' => 'input',
                     'border' => 'border-gray-200',  'bg' => 'bg-gray-50',  'title' => 'text-gray-900'],
                    ['field' => 'usage_note',     'label' => 'Usage Note',    'input' => 'textarea',
                     'border' => 'border-amber-200', 'bg' => 'bg-amber-50', 'title' => 'text-amber-800'],
                    ['field' => 'learner_traps',  'label' => 'Learner Traps', 'input' => 'textarea',
                     'border' => 'border-red-200',   'bg' => 'bg-red-50',   'title' => 'text-red-800'],
                ];
            @endphp

            @foreach ($grammarNoteTypes as $nt)
                <div class="bg-white rounded-xl border {{ $nt['border'] }} overflow-hidden">
                    <details open>
                        <summary class="px-5 py-3 {{ $nt['bg'] }} border-b {{ $nt['border'] }} cursor-pointer">
                            <span class="text-sm font-semibold {{ $nt['title'] }}">{{ $nt['label'] }}</span>
                            <span class="text-xs text-gray-400 ml-2">{{ $coverageLangs->pluck('code')->implode(' + ') }}</span>
                        </summary>
                        <div class="p-5 space-y-3">
                            @foreach ($coverageLangs as $lang)
                                @php
                                    $existingNote = $existingNotes->get($lang->id);
                                    $oldKey = "notes.{$lang->id}.{$nt['field']}";
                                    $saved = $existingNote?->{$nt['field']} ?? '';
                                @endphp
                                <div>
                                    <label class="block text-xs font-semibold text-indigo-500 mb-1">{{ strtoupper($lang->code) }} · {{ $lang->name }}</label>
                                    @if ($nt['input'] === 'input')
                                        <input type="text" name="notes[{{ $lang->id }}][{{ $nt['field'] }}]"
                                               value="{{ old($oldKey, $saved) }}"
                                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    @else
                                        <textarea name="notes[{{ $lang->id }}][{{ $nt['field'] }}]" rows="3"
                                                  class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old($oldKey, $saved) }}</textarea>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </details>
                </div>
            @endforeach
        </div>

        {{-- ── Staged Examples (師父 — unsaved until Save Changes) ────────────
             Hidden by default; revealed by stageExample() when 師父 stages rows,
             re-hidden when all rows are discarded or after Save Changes. --}}
        <div class="bg-white rounded-lg border border-green-300 p-5 space-y-3 hidden" id="gp-staged-examples-section">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Staged Examples
                    <span class="text-xs text-gray-400 font-normal">(師父 — unsaved)</span>
                </h2>
            </div>
            <p class="text-xs text-gray-500">These will save when you click <strong>Save Changes</strong>. Edit freely first.</p>
            <div id="gp-staged-examples-container" class="space-y-3"></div>
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
                    <span class="text-xs text-gray-400 font-mono">Example {{ $loop->iteration }}</span>
                    <form method="POST" action="{{ route('admin.grammar.examples.destroy', $ex) }}">
                        @csrf @method('DELETE')
                        <button type="submit" data-confirm="Click again to delete"
                                class="text-xs text-red-400 hover:text-red-600 px-1.5 py-0.5 rounded">Delete</button>
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

{{-- ── 師父 enrichment JS ───────────────────────────────────────────────── --}}
<script>
(function() {
    const btn     = document.getElementById('gp-enrich-btn');
    const panel   = document.getElementById('gp-enrich-panel');
    const content = document.getElementById('gp-enrich-content');
    if (!btn || !panel || !content) return;

    // Language IDs: 1=en, 2=zh-TW, 3=zh-CN (matches languages table)
    // Coverage languages from the DB — drives all per-language apply logic.
    const COVERAGE_LANGS = @json($coverageLangs->map(fn ($l) => ['id' => $l->id, 'code' => $l->code])->values());
    function langIdForCode(shortCode) {
        const row = COVERAGE_LANGS.find(l => l.code === shortCode)
                 || COVERAGE_LANGS.find(l => l.code.toLowerCase().startsWith(shortCode.toLowerCase()));
        return row ? row.id : null;
    }
    // Legacy aliases — kept so submitExample() keeps working. When/if additional
    // translation languages come online, teach 師父 to emit them.
    const LANG_EN = langIdForCode('en');
    const LANG_ZH = langIdForCode('zh');

    // Find a form field by name attribute inside the main edit form
    function findField(name) {
        const form = document.querySelector('form[action*="grammar"]');
        if (!form) return null;
        return form.querySelector(`[name="${name.replace(/"/g, '\\"')}"]`);
    }

    function setFieldValue(name, value) {
        const el = findField(name);
        if (el && value != null) {
            el.value = value;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.classList.add('bg-indigo-50');
            setTimeout(() => el.classList.remove('bg-indigo-50'), 1500);
        }
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    // ── Add-example form helpers ────────────────────────────────────────────
    // Submits a generated example directly to the existing grammar.examples.store
    // endpoint via a hidden form POST so we get the normal redirect + flash.
    const examplesStoreUrl = @json(route('admin.grammar.examples.store', $pattern));
    const csrfToken = @json(csrf_token());

    @php $translationLangIds = $translationLangs->pluck('id')->values(); @endphp
    const translationLangIds = @json($translationLangIds);
    const TRANSLATION_LANGS = @json($translationLangs->map(fn ($l) => ['id' => $l->id, 'code' => $l->code, 'name' => $l->name])->values());

    // Running counter so each staged row gets a unique pending_examples[i] index.
    let stagedCounter = 0;

    // Build an editable staged-example row with field names targeting
    // pending_examples[i][...] so it posts alongside the main form and
    // syncPendingExamples() on the controller persists it on Save Changes.
    function buildStagedExampleRow(ex) {
        const i = stagedCounter++;
        const wrapper = document.createElement('div');
        wrapper.className = 'border border-green-300 rounded-lg p-4 space-y-3 bg-green-50';
        wrapper.dataset.stagedIndex = i;

        const translationFields = TRANSLATION_LANGS.map(tl => {
            const value = ex[tl.code] ?? (tl.code === 'en' ? ex.english : null) ?? '';
            return `
                <div>
                    <label class="block text-xs text-gray-500 mb-1">${escapeHtml(tl.name)} Translation</label>
                    <textarea name="pending_examples[${i}][translations][${tl.id}]" rows="2" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">${escapeHtml(value)}</textarea>
                </div>`;
        }).join('');

        wrapper.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="text-xs text-green-700 font-semibold uppercase tracking-wide">🙏 師父 staged · unsaved</span>
                <button type="button" class="gp-staged-discard text-xs text-red-400 hover:text-red-600">Discard</button>
            </div>
            <div class="space-y-2">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Chinese *</label>
                    <textarea name="pending_examples[${i}][chinese_text]" rows="2" class="w-full rounded border border-gray-300 px-3 py-2 text-sm font-serif">${escapeHtml(ex.chinese_traditional || '')}</textarea>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Pinyin</label>
                    <input type="text" name="pending_examples[${i}][pinyin_text]" value="${escapeHtml(ex.pinyin || '')}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>
                ${translationFields}
            </div>`;

        wrapper.querySelector('.gp-staged-discard').addEventListener('click', () => {
            wrapper.remove();
            updateStagedEmptyState();
        });
        return wrapper;
    }

    function updateStagedEmptyState() {
        const container = document.getElementById('gp-staged-examples-container');
        const section = document.getElementById('gp-staged-examples-section');
        if (!container || !section) return;
        section.classList.toggle('hidden', container.children.length === 0);
    }

    // Insert a staged row into the staged-examples container (inside the main
    // form so it submits with Save Changes).
    function stageExample(ex) {
        const container = document.getElementById('gp-staged-examples-container');
        if (!container) return false;
        const row = buildStagedExampleRow(ex);
        container.appendChild(row);
        updateStagedEmptyState();
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return true;
    }

    // ── Render the preview panel ───────────────────────────────────────────
    function renderPreview(data) {
        const labels   = data.labels || {};
        const notes    = data.notes  || {};
        const exs      = Array.isArray(data.examples) ? data.examples : [];
        const template = data.pattern_template || '';

        const coreBlock = template ? `
            <div class="bg-white rounded-lg border border-indigo-100 p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-semibold text-indigo-900 uppercase tracking-wide">Core — Pattern Template</h4>
                    <button type="button" class="gp-apply-template text-xs px-2 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-500">Apply template</button>
                </div>
                <p class="text-sm font-mono text-gray-800">${escapeHtml(template)}</p>
            </div>` : '';

        const labelBlock = `
            <div class="bg-white rounded-lg border border-indigo-100 p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-semibold text-indigo-900 uppercase tracking-wide">Labels</h4>
                    <button type="button" class="gp-apply-labels text-xs px-2 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-500">Apply all labels</button>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">EN name</p>
                        <p class="font-medium text-gray-800">${escapeHtml(labels.en?.name)}</p>
                        <p class="text-xs text-gray-500 mt-1 italic">${escapeHtml(labels.en?.short_description)}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">ZH 名稱</p>
                        <p class="font-medium text-gray-800 font-serif">${escapeHtml(labels.zh?.name)}</p>
                        <p class="text-xs text-gray-500 mt-1 italic font-serif">${escapeHtml(labels.zh?.short_description)}</p>
                    </div>
                </div>
            </div>`;

        const notesBlock = `
            <div class="bg-white rounded-lg border border-indigo-100 p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-semibold text-indigo-900 uppercase tracking-wide">Notes (formula · usage · traps)</h4>
                    <button type="button" class="gp-apply-notes text-xs px-2 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-500">Apply all notes</button>
                </div>
                ${Object.entries(notes).map(([code, rawNote]) => {
                    const n = rawNote || {};
                    // Render whatever keys 師父 returned — works for en, zh-TW,
                    // and any future coverage language. Hardcoded ['en','zh']
                    // was silently dropping 'zh-TW' from the preview.
                    const langRow = COVERAGE_LANGS.find(l => l.code === code)
                                 || COVERAGE_LANGS.find(l => l.code.toLowerCase().startsWith(code.toLowerCase()));
                    const label = code === 'en' ? 'English'
                                : (code === 'zh-TW' || code === 'zh') ? '中文'
                                : (langRow?.code ?? code);
                    return `
                    <div class="border-l-2 border-indigo-200 pl-3 mb-3">
                        <p class="text-xs font-medium text-indigo-700 mb-1">${label}</p>
                        <p class="text-xs text-gray-500">Formula</p>
                        <p class="text-sm font-mono text-gray-800 mb-2">${escapeHtml(n.formula)}</p>
                        <p class="text-xs text-gray-500">Usage note</p>
                        <p class="text-sm text-gray-800 mb-2">${escapeHtml(n.usage_note)}</p>
                        <p class="text-xs text-gray-500">Learner traps</p>
                        <p class="text-sm text-gray-800">${escapeHtml(n.learner_traps)}</p>
                    </div>`;
                }).join('')}
            </div>`;

        const examplesBlock = `
            <div class="bg-white rounded-lg border border-indigo-100 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-semibold text-indigo-900 uppercase tracking-wide">Examples (${exs.length})</h4>
                    <button type="button" class="gp-apply-all-examples text-xs px-2 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-500">Apply all examples</button>
                </div>
                <div class="space-y-3">
                    ${exs.map((ex, i) => `
                        <div class="border-l-2 border-indigo-200 pl-3" data-ex-idx="${i}">
                            <p class="text-base font-serif text-gray-900">${escapeHtml(ex.chinese_traditional)}</p>
                            ${ex.pinyin ? `<p class="text-xs text-gray-500">${escapeHtml(ex.pinyin)}</p>` : ''}
                            <p class="text-sm text-gray-700 italic mt-1">${escapeHtml(ex.english)}</p>
                            ${ex.note ? `<p class="text-xs text-indigo-600 mt-1">— ${escapeHtml(ex.note)}</p>` : ''}
                            <button type="button" class="gp-add-example mt-2 text-xs px-2 py-1 rounded bg-green-600 text-white hover:bg-green-500">+ Add this example</button>
                        </div>
                    `).join('')}
                </div>
            </div>`;

        content.innerHTML = coreBlock + labelBlock + notesBlock + examplesBlock;
        panel.classList.remove('hidden');

        // Helper: turn block background green + replace button with "N added ✓"
        function markApplied(btnEl, summary) {
            if (!btnEl) return;
            const block = btnEl.closest('.bg-white');
            if (block) {
                block.classList.remove('bg-white', 'border-indigo-100');
                block.classList.add('bg-green-50', 'border-green-300');
            }
            const badge = document.createElement('span');
            badge.className = 'text-xs px-2 py-1 rounded bg-green-600 text-white font-semibold';
            badge.textContent = `✓ ${summary}`;
            btnEl.replaceWith(badge);
        }

        content.querySelector('.gp-apply-template')?.addEventListener('click', (e) => {
            setFieldValue('pattern_template', template);
            markApplied(e.currentTarget, 'template added');
        });

        content.querySelector('.gp-apply-labels')?.addEventListener('click', (e) => {
            let n = 0;
            Object.entries(labels).forEach(([code, lab]) => {
                const langId = langIdForCode(code);
                if (!langId || !lab) return;
                setFieldValue(`labels[${langId}][name]`,              lab.name);
                setFieldValue(`labels[${langId}][short_description]`, lab.short_description);
                if (lab.name) n++;
            });
            markApplied(e.currentTarget, `${n} label${n === 1 ? '' : 's'} added`);
        });

        content.querySelector('.gp-apply-notes')?.addEventListener('click', (e) => {
            let n = 0;
            Object.entries(notes).forEach(([code, note]) => {
                const langId = langIdForCode(code);
                if (!langId || !note) return;
                setFieldValue(`notes[${langId}][formula]`,       note.formula);
                setFieldValue(`notes[${langId}][usage_note]`,    note.usage_note);
                setFieldValue(`notes[${langId}][learner_traps]`, note.learner_traps);
                if (note.formula || note.usage_note || note.learner_traps) n++;
            });
            markApplied(e.currentTarget, `${n} note${n === 1 ? '' : 's'} added`);
        });

        // Bulk "Apply all examples" — inserts editable staged forms into the
        // Examples section below. Nothing is persisted until the reviewer clicks
        // each staged form's "Add Example" button.
        content.querySelector('.gp-apply-all-examples')?.addEventListener('click', (e) => {
            const btnEl = e.currentTarget;
            let staged = 0;
            exs.forEach(ex => { if (stageExample(ex)) staged++; });
            markApplied(btnEl, `${staged} example${staged === 1 ? '' : 's'} staged — edit & save below`);
            content.querySelectorAll('.gp-add-example').forEach(b => b.remove());
        });

        content.querySelectorAll('.gp-add-example').forEach((b, i) => {
            b.addEventListener('click', () => {
                if (stageExample(exs[i])) {
                    b.disabled = true;
                    b.textContent = '✓ staged below';
                    b.classList.remove('bg-green-600', 'hover:bg-green-500');
                    b.classList.add('bg-gray-300', 'text-gray-600');
                }
            });
        });
    }

    // ── Main trigger ───────────────────────────────────────────────────────
    btn.addEventListener('click', async () => {
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '師父 is thinking…';
        panel.classList.remove('hidden');
        content.innerHTML = '<p class="text-sm text-indigo-700 italic">師父 is reflecting on this pattern…</p>';

        try {
            const resp = await fetch(btn.dataset.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await resp.json();
            if (!resp.ok || data.error) {
                content.innerHTML = `<p class="text-sm text-red-600">Error: ${escapeHtml(data.error || resp.statusText)}</p>`;
                if (data.raw) {
                    content.innerHTML += `<pre class="text-xs text-gray-500 mt-2 whitespace-pre-wrap">${escapeHtml(data.raw)}</pre>`;
                }
                return;
            }
            renderPreview(data.enrichment || {});
        } catch (e) {
            content.innerHTML = `<p class="text-sm text-red-600">Request failed: ${escapeHtml(e.message)}</p>`;
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    // Auto-trigger when redirected from suggestion "Enrich & Draft" flow
    const params = new URLSearchParams(window.location.search);
    if (params.get('auto_enrich') === '1') {
        // Small delay so the page has fully settled + user sees the click
        setTimeout(() => btn.click(), 350);
    }

    // Handoff from the Enrichment Queue: queue stashes 師父's output in
    // sessionStorage then redirects here with ?from_queue=1. We render the
    // preview panel directly (no second API call) so the reviewer can edit
    // and save via the normal Save Changes flow.
    if (params.get('from_queue') === '1') {
        const patternId = @json($pattern->id);
        const key = `grammar_staged_enrichment_${patternId}`;
        const raw = sessionStorage.getItem(key);
        if (raw) {
            try {
                const enrichment = JSON.parse(raw);
                sessionStorage.removeItem(key);
                btn.disabled = true;
                btn.textContent = '師父 staged';
                renderPreview(enrichment);
            } catch (_) { /* ignore */ }
        }
    }
})();
</script>

@endsection
