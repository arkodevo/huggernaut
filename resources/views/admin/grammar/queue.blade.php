@extends('admin.layout')
@section('title', 'Grammar Enrichment Queue')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">🙏 Grammar Enrichment Queue</h1>
        <p class="text-sm text-gray-500 mt-1">
            Draft patterns needing 師父 enrichment (missing notes or examples).
            Step through one at a time to keep quality high.
        </p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.grammar.index') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            ← Back to Patterns
        </a>
    </div>
</div>

@if ($drafts->isEmpty())
    <div class="bg-white border border-gray-200 rounded-lg p-8 text-center">
        <p class="text-lg font-medium text-gray-900">🎉 Queue is empty!</p>
        <p class="text-sm text-gray-500 mt-2">All draft patterns have notes and examples.</p>
    </div>
@else

{{-- ── Control Bar ─────────────────────────────────────────────────────── --}}
<div class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg p-4 mb-5 flex items-center justify-between flex-wrap gap-3">
    <div class="text-sm text-indigo-900">
        <span class="font-semibold">{{ $drafts->count() }}</span> drafts in queue.
        <span id="gq-progress" class="ml-2 text-indigo-600"></span>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" id="gq-start-btn"
                class="px-4 py-2 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors disabled:opacity-50">
            ▶ Start Enrichment
        </button>
        <button type="button" id="gq-pause-btn"
                class="hidden px-4 py-2 rounded-lg bg-amber-500 text-sm font-semibold text-white hover:bg-amber-400 transition-colors">
            ⏸ Pause
        </button>
    </div>
</div>

{{-- ── Draft cards ─────────────────────────────────────────────────────── --}}
<div class="space-y-4" id="gq-cards">
    @foreach ($drafts as $draft)
        @php
            $noteEn = $draft->notes->firstWhere('language_id', 1);
            $noteZh = $draft->notes->firstWhere('language_id', 2);
            $labelEn = $draft->labels->firstWhere('language_id', 1);
            $needsNotes = ! $noteEn || (! $noteEn->formula && ! $noteEn->usage_note && ! $noteEn->learner_traps);
            $needsExamples = $draft->examples_count === 0;
        @endphp

        <div class="gq-card bg-white rounded-lg border border-gray-200 overflow-hidden"
             data-pattern-id="{{ $draft->id }}"
             data-enrich-url="{{ route('admin.grammar.enrich', $draft) }}"
             data-apply-url="{{ route('admin.grammar.apply-enrichment', $draft) }}"
             data-edit-url="{{ route('admin.grammar.edit', $draft) }}">

            {{-- Card header --}}
            <div class="flex items-center justify-between gap-4 p-4 border-b border-gray-100">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="gq-status-icon text-2xl" title="pending">⚫</span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-gray-900 truncate">{{ $draft->chinese_label }}</h3>
                            @if ($labelEn?->name)
                                <span class="text-sm text-gray-500">— {{ $labelEn->name }}</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">
                            <code>{{ $draft->slug }}</code>
                            @if ($draft->pattern_template)
                                · <span class="font-mono">{{ Str::limit($draft->pattern_template, 50) }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            @if ($needsNotes)
                                <span class="px-1.5 py-0.5 rounded text-xs bg-amber-100 text-amber-700">needs notes</span>
                            @endif
                            @if ($needsExamples)
                                <span class="px-1.5 py-0.5 rounded text-xs bg-amber-100 text-amber-700">needs examples</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('admin.grammar.edit', $draft) }}" target="_blank"
                       class="px-3 py-1.5 rounded-lg text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50">
                        ✎ Edit
                    </a>
                </div>
            </div>

            {{-- Preview area (filled by JS after enrichment) --}}
            <div class="gq-preview hidden p-4 bg-gradient-to-br from-indigo-50/40 to-purple-50/40">
                <div class="gq-preview-content text-sm"></div>
                <div class="gq-preview-actions hidden mt-4 flex-wrap gap-2">
                    <button type="button" class="gq-apply-btn px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-500">
                        → Review & Edit
                    </button>
                    <button type="button" class="gq-skip-btn px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50">
                        → Skip
                    </button>
                    <button type="button" class="gq-retry-btn hidden px-4 py-2 rounded-lg border border-indigo-300 bg-white text-sm font-medium text-indigo-600 hover:bg-indigo-50">
                        ↻ Retry
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- ── Queue runner JS ─────────────────────────────────────────────────── --}}
<script>
(function() {
    const csrfToken = @json(csrf_token());
    const cards = Array.from(document.querySelectorAll('.gq-card'));
    const startBtn = document.getElementById('gq-start-btn');
    const pauseBtn = document.getElementById('gq-pause-btn');
    const progressEl = document.getElementById('gq-progress');

    let currentIndex = -1;
    let paused = false;

    const MAX_PER_BATCH = 9; // safety cap

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function setStatus(card, icon, title) {
        const el = card.querySelector('.gq-status-icon');
        if (el) { el.textContent = icon; el.title = title; }
    }

    function updateProgress() {
        const done = cards.filter(c => c.dataset.done === '1').length;
        progressEl.textContent = `${done} / ${cards.length} done`;
    }

    function renderPreview(card, enrichment) {
        const preview = card.querySelector('.gq-preview');
        const content = card.querySelector('.gq-preview-content');
        const actions = card.querySelector('.gq-preview-actions');
        preview.classList.remove('hidden');
        actions.classList.remove('hidden');
        actions.classList.add('flex');

        // Stash enrichment payload for apply step
        card._enrichment = enrichment;

        const template = enrichment.pattern_template || '';
        const labels = enrichment.labels || {};
        const notes = enrichment.notes || {};
        const examples = Array.isArray(enrichment.examples) ? enrichment.examples : [];

        let html = '';

        // Pattern template
        if (template) {
            html += `<div class="mb-3"><div class="text-xs uppercase tracking-wide text-indigo-600 font-semibold mb-1">Pattern Template</div><div class="text-sm font-mono text-gray-800">${escapeHtml(template)}</div></div>`;
        }

        // Labels — iterate every key 師父 returned
        const labelEntries = Object.entries(labels).filter(([_, lab]) => lab?.name);
        if (labelEntries.length) {
            html += '<div class="mb-3"><div class="text-xs uppercase tracking-wide text-indigo-600 font-semibold mb-1">Labels</div>';
            labelEntries.forEach(([code, lab]) => {
                html += `<div class="text-sm"><span class="text-gray-400">${escapeHtml(code.toUpperCase())}:</span> <strong>${escapeHtml(lab.name)}</strong>${lab.short_description ? ` — <em class="text-gray-600">${escapeHtml(lab.short_description)}</em>` : ''}</div>`;
            });
            html += '</div>';
        }

        // Notes — iterate every key 師父 returned
        Object.entries(notes).forEach(([code, n]) => {
            if (!n) return;
            if (!n.formula && !n.usage_note && !n.learner_traps) return;
            const title = `${code.toUpperCase()} Notes`;
            html += `<div class="mb-3"><div class="text-xs uppercase tracking-wide text-indigo-600 font-semibold mb-1">${escapeHtml(title)}</div>`;
            if (n.formula)       html += `<div class="text-sm"><span class="text-gray-400">Formula:</span> <span class="font-mono">${escapeHtml(n.formula)}</span></div>`;
            if (n.usage_note)    html += `<div class="text-sm"><span class="text-gray-400">Usage:</span> ${escapeHtml(n.usage_note)}</div>`;
            if (n.learner_traps) html += `<div class="text-sm"><span class="text-gray-400">Traps:</span> ${escapeHtml(n.learner_traps)}</div>`;
            html += '</div>';
        });

        // Examples
        if (examples.length) {
            html += '<div class="mb-1"><div class="text-xs uppercase tracking-wide text-indigo-600 font-semibold mb-1">Examples</div>';
            examples.forEach(ex => {
                html += `<div class="text-sm mb-1">`;
                html += `<div class="font-serif">${escapeHtml(ex.chinese_traditional || '')}</div>`;
                if (ex.pinyin)  html += `<div class="text-xs text-gray-500 italic">${escapeHtml(ex.pinyin)}</div>`;
                // Show English translation under either the legacy "english" key
                // or any non-Chinese coverage lang code on the example row.
                const translation = ex.english ?? Object.entries(ex).find(([k, v]) =>
                    !['chinese_traditional','chinese_simplified','pinyin','note'].includes(k) &&
                    !k.toLowerCase().startsWith('zh') &&
                    typeof v === 'string'
                )?.[1];
                if (translation) html += `<div class="text-xs text-gray-600">${escapeHtml(translation)}</div>`;
                html += `</div>`;
            });
            html += '</div>';
        }

        if (!html) html = '<p class="text-sm text-amber-700">師父 returned no usable content. Skip or retry.</p>';

        content.innerHTML = html;
    }

    function renderError(card, message) {
        const preview = card.querySelector('.gq-preview');
        const content = card.querySelector('.gq-preview-content');
        const actions = card.querySelector('.gq-preview-actions');
        preview.classList.remove('hidden');
        actions.classList.remove('hidden');
        actions.classList.add('flex');
        content.innerHTML = `<p class="text-sm text-red-600">Error: ${escapeHtml(message)}</p>`;
        card.querySelector('.gq-apply-btn').classList.add('hidden');
        card.querySelector('.gq-retry-btn').classList.remove('hidden');
    }

    async function enrichOne(card) {
        setStatus(card, '🔄', 'enriching');
        card.querySelector('.gq-apply-btn').classList.remove('hidden');
        card.querySelector('.gq-retry-btn').classList.add('hidden');
        const content = card.querySelector('.gq-preview-content');
        card.querySelector('.gq-preview').classList.remove('hidden');
        content.innerHTML = '<p class="text-sm text-indigo-700 italic">師父 is reflecting…</p>';

        try {
            const resp = await fetch(card.dataset.enrichUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await resp.json();
            if (!resp.ok || data.error) {
                setStatus(card, '⚠️', 'error');
                renderError(card, data.error || resp.statusText);
                return;
            }
            renderPreview(card, data.enrichment || {});
            setStatus(card, '👁', 'awaiting review');
        } catch (e) {
            setStatus(card, '⚠️', 'error');
            renderError(card, e.message);
        }
    }

    // Hand off to the edit page for review/editing. We stash 師父's enrichment
    // in sessionStorage keyed by pattern id, then redirect with ?from_queue=1.
    // The edit page reads it, pre-stages all content into the form, and the
    // reviewer edits + clicks Save Changes to persist. Nothing is saved here.
    function applyOne(card) {
        if (!card._enrichment) return;
        const patternId = card.dataset.patternId;
        const editUrl = card.dataset.editUrl;
        try {
            sessionStorage.setItem(
                `grammar_staged_enrichment_${patternId}`,
                JSON.stringify(card._enrichment)
            );
        } catch (e) {
            alert('Could not stash enrichment: ' + e.message);
            return;
        }
        const sep = editUrl.includes('?') ? '&' : '?';
        window.location.href = `${editUrl}${sep}from_queue=1`;
    }

    function skipCurrent(card) {
        setStatus(card, '⏭', 'skipped');
        card.dataset.done = '1';
        card.querySelector('.gq-preview-actions').classList.add('hidden');
        updateProgress();
        advance();
    }

    async function advance() {
        if (paused) return;
        currentIndex++;
        if (currentIndex >= cards.length || currentIndex >= MAX_PER_BATCH) {
            progressEl.textContent += ' — batch complete';
            startBtn.classList.remove('hidden');
            startBtn.textContent = '▶ Resume';
            startBtn.disabled = false;
            pauseBtn.classList.add('hidden');
            return;
        }
        const card = cards[currentIndex];
        if (card.dataset.done === '1') {
            advance();
            return;
        }
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        await enrichOne(card);
    }

    // ── Event wiring ────────────────────────────────────────────────────────
    startBtn.addEventListener('click', () => {
        paused = false;
        startBtn.classList.add('hidden');
        pauseBtn.classList.remove('hidden');
        advance();
    });

    pauseBtn.addEventListener('click', () => {
        paused = true;
        pauseBtn.classList.add('hidden');
        startBtn.classList.remove('hidden');
        startBtn.textContent = '▶ Resume';
    });

    // Delegated click handlers on cards
    cards.forEach(card => {
        card.querySelector('.gq-apply-btn').addEventListener('click', () => {
            // Hands off to the edit page via redirect — no advance, no save here.
            applyOne(card);
        });
        card.querySelector('.gq-skip-btn').addEventListener('click', () => {
            skipCurrent(card);
        });
        card.querySelector('.gq-retry-btn').addEventListener('click', async () => {
            await enrichOne(card);
        });
    });

    updateProgress();
})();
</script>

@endif

@endsection
