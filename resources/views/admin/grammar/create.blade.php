@extends('admin.layout')
@section('title', 'Create Grammar Pattern')

@section('content')

<div class="max-w-3xl">
    <a href="{{ route('admin.grammar.index') }}" class="text-sm text-indigo-600 hover:underline mb-4 inline-block">← Back to Grammar Patterns</a>

    <div class="flex items-start justify-between mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-900">Create Grammar Pattern</h1>
        <button type="button" id="gp-enrich-seed-btn"
                data-url="{{ route('admin.grammar.enrich-seed') }}"
                class="shrink-0 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                title="Fill in Chinese label (and ideally a pattern template) first, then let 師父 draft the rest">
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
        <p class="text-xs text-indigo-700 mb-4">Review each section. Click <strong>Apply</strong> to populate the form fields below — nothing is saved until you click <strong>Create Pattern</strong>. Examples will be added after the pattern is created (use the edit view).</p>
        <div id="gp-enrich-content" class="space-y-5"></div>
    </div>

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

        {{-- ── Notes (bilingual, grouped by note type) ──────────────────────
             Matches the Words admin layout: each note type is one section;
             all coverage languages appear side-by-side inside. --}}
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
                                @php $oldKey = "notes.{$lang->id}.{$nt['field']}"; @endphp
                                <div>
                                    <label class="block text-xs font-semibold text-indigo-500 mb-1">{{ strtoupper($lang->code) }} · {{ $lang->name }}</label>
                                    @if ($nt['input'] === 'input')
                                        <input type="text" name="notes[{{ $lang->id }}][{{ $nt['field'] }}]"
                                               value="{{ old($oldKey) }}"
                                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                               placeholder="{{ $lang->code === 'en' ? '[Subject] + 把 + [Object] + [Verb-Complement]' : '[主語] + 把 + [賓語] + [動補]' }}">
                                    @else
                                        <textarea name="notes[{{ $lang->id }}][{{ $nt['field'] }}]" rows="3"
                                                  class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old($oldKey) }}</textarea>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </details>
                </div>
            @endforeach
        </div>

        {{-- ── Examples (staged via 師父 enrichment) ───────────────────────── --}}
        <div class="bg-white rounded-lg border border-gray-200 p-5 space-y-3" id="gp-examples-section">
            <h2 class="text-lg font-semibold text-gray-800">Examples</h2>
            <p class="text-sm text-gray-500" id="gp-examples-empty">
                No examples yet. Use the 師父 enrichment panel above, then click <em>Apply examples</em> to stage them here — they'll be saved when you click Create Pattern.
            </p>
            <div id="gp-examples-staged" class="space-y-2"></div>
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

{{-- ── 師父 enrichment JS (seed-based) ──────────────────────────────────── --}}
<script>
(function() {
    const btn     = document.getElementById('gp-enrich-seed-btn');
    const panel   = document.getElementById('gp-enrich-panel');
    const content = document.getElementById('gp-enrich-content');
    if (!btn || !panel || !content) return;

    // Coverage languages from the DB — drives all per-language apply logic.
    // Adding a new has_notes_coverage language automatically surfaces here.
    const COVERAGE_LANGS = @json($coverageLangs->map(fn ($l) => ['id' => $l->id, 'code' => $l->code])->values());
    // Short-code map (en → 1, zh-TW → 2) — 師父 keys its output by short code,
    // so we resolve the matching coverage lang id at apply time.
    function langIdForCode(shortCode) {
        // Accept 'en', 'zh', or full codes like 'zh-TW'
        const row = COVERAGE_LANGS.find(l => l.code === shortCode)
                 || COVERAGE_LANGS.find(l => l.code.toLowerCase().startsWith(shortCode.toLowerCase()));
        return row ? row.id : null;
    }
    // Translation langs for examples: coverage langs minus Chinese-family
    // (example source sentence is already Chinese). Matches senses/_form standard.
    const TRANSLATION_LANG_IDS = COVERAGE_LANGS
        .filter(l => !l.code.toLowerCase().startsWith('zh'))
        .map(l => l.id);

    const csrfToken = @json(csrf_token());

    function findField(name) {
        const form = document.querySelector('form[action*="grammar"]');
        if (!form) return null;
        return form.querySelector(`[name="${name.replace(/"/g, '\\"')}"]`);
    }
    function setFieldValue(name, value) {
        const el = findField(name);
        if (el && value != null && value !== '') {
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
                    <button type="button" class="gp-apply-labels text-xs px-2 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-500">Apply labels</button>
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
                    <h4 class="text-xs font-semibold text-indigo-900 uppercase tracking-wide">Notes</h4>
                    <button type="button" class="gp-apply-notes text-xs px-2 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-500">Apply notes</button>
                </div>
                ${Object.entries(notes).map(([code, rawNote]) => {
                    const n = rawNote || {};
                    // Label from the coverage-lang table when we know it; falls
                    // back to the code itself so any new language 師父 returns
                    // still renders instead of silently vanishing.
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

        const examplesBlock = exs.length ? `
            <div class="bg-white rounded-lg border border-indigo-100 p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-semibold text-indigo-900 uppercase tracking-wide">Examples (${exs.length})</h4>
                    <button type="button" class="gp-apply-examples text-xs px-2 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-500">Apply examples</button>
                </div>
                <p class="text-xs text-gray-500 mb-3 italic">Applied examples will be saved when you create this pattern.</p>
                <div class="space-y-3">
                    ${exs.map(ex => `
                        <div class="border-l-2 border-indigo-200 pl-3">
                            <p class="text-base font-serif text-gray-900">${escapeHtml(ex.chinese_traditional)}</p>
                            ${ex.pinyin ? `<p class="text-xs text-gray-500">${escapeHtml(ex.pinyin)}</p>` : ''}
                            <p class="text-sm text-gray-700 italic mt-1">${escapeHtml(ex.english)}</p>
                        </div>
                    `).join('')}
                </div>
                <p class="gp-examples-applied text-xs text-green-700 mt-2 hidden">✓ ${exs.length} example${exs.length === 1 ? '' : 's'} staged — will save with the pattern.</p>
            </div>` : '';

        content.innerHTML = coreBlock + labelBlock + notesBlock + examplesBlock;
        panel.classList.remove('hidden');

        // Helper: green background + swap button for "N added ✓" badge
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

        content.querySelector('.gp-apply-examples')?.addEventListener('click', (e) => {
            const form    = document.querySelector('form[action*="grammar"]');
            const section = document.getElementById('gp-examples-section');
            const staged  = document.getElementById('gp-examples-staged');
            const empty   = document.getElementById('gp-examples-empty');
            if (!form || !section || !staged) return;

            // Wipe any previously-staged inputs + rendered cards so re-applying
            // replaces instead of accumulating.
            form.querySelectorAll('input[name^="pending_examples["]').forEach(el => el.remove());
            staged.innerHTML = '';

            exs.forEach((ex, i) => {
                // Hidden inputs that submit with the form
                const fields = {
                    [`pending_examples[${i}][chinese_text]`]: ex.chinese_traditional || '',
                    [`pending_examples[${i}][pinyin_text]`]:  ex.pinyin || '',
                };
                // Write one translation entry per non-Chinese coverage lang.
                // 師父 keys translations by language code (e.g. "en", "ja").
                // Legacy fallback: the original "english" key.
                TRANSLATION_LANG_IDS.forEach(id => {
                    const row = COVERAGE_LANGS.find(l => l.id === id);
                    if (!row) return;
                    const value = ex[row.code] ?? (row.code === 'en' ? ex.english : null) ?? '';
                    fields[`pending_examples[${i}][translations][${id}]`] = value;
                });
                Object.entries(fields).forEach(([name, value]) => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = name;
                    inp.value = value;
                    form.appendChild(inp);
                });

                // Visible staged card — shows all non-Chinese translations
                const translations = TRANSLATION_LANG_IDS
                    .map(id => COVERAGE_LANGS.find(l => l.id === id))
                    .filter(Boolean)
                    .map(row => ex[row.code] ?? (row.code === 'en' ? ex.english : null))
                    .filter(Boolean);

                const card = document.createElement('div');
                card.className = 'border-l-4 border-green-300 pl-3 py-1';
                card.innerHTML = `
                    <p class="text-base font-serif text-gray-900">${escapeHtml(ex.chinese_traditional)}</p>
                    ${ex.pinyin ? `<p class="text-xs text-gray-500">${escapeHtml(ex.pinyin)}</p>` : ''}
                    ${translations.map(t => `<p class="text-sm text-gray-700 italic">${escapeHtml(t)}</p>`).join('')}
                `;
                staged.appendChild(card);
            });

            if (empty) empty.classList.add('hidden');

            // Green-wash the form's Examples section + replace the panel button
            section.classList.remove('border-gray-200');
            section.classList.add('bg-green-50', 'border-green-300');
            markApplied(e.currentTarget, `${exs.length} example${exs.length === 1 ? '' : 's'} staged`);
        });
    }

    btn.addEventListener('click', async () => {
        const chineseLabel    = findField('chinese_label')?.value.trim() || '';
        const slug            = findField('slug')?.value.trim() || '';
        const patternTemplate = findField('pattern_template')?.value.trim() || '';

        if (!chineseLabel) {
            alert('Please fill in the Chinese Label first — 師父 needs something to work with.');
            return;
        }

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
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    chinese_label: chineseLabel,
                    slug,
                    pattern_template: patternTemplate,
                    @if ($suggestion)
                        hint_context: @json(trim(($suggestion->chinese_example ?? '') . ' ' . ($suggestion->shifu_notes ?? ''))),
                    @endif
                }),
            });
            const data = await resp.json();
            if (!resp.ok || data.error) {
                content.innerHTML = `<p class="text-sm text-red-600">Error: ${escapeHtml(data.error || resp.statusText)}</p>`;
                if (data.raw) content.innerHTML += `<pre class="text-xs text-gray-500 mt-2 whitespace-pre-wrap">${escapeHtml(data.raw)}</pre>`;
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
})();
</script>

@endsection
