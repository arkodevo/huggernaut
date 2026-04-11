<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>My Writings — 流動 Living Lexicon</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@300;400;600;700&family=DM+Mono:ital,wght@0,300;0,400;1,300&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
@include('partials.lexicon._foundations')
@include('partials.lexicon._example-sentence-css')
@include('partials.lexicon._workshop-css')
<style>
/* ── MAIN ── */
.mwr-main { max-width: 640px; margin: 0 auto; padding: 1.5rem 1rem 3rem; }

/* ── SEARCH ── */
.mwr-search {
  width: 100%; box-sizing: border-box;
  font-family: 'DM Mono', monospace; font-size: 0.78rem;
  color: var(--ink); background: #fff;
  border: 1px solid var(--border); border-radius: 2px;
  padding: 0.5rem 0.75rem; outline: none;
  margin-bottom: 1rem;
  transition: border-color 0.2s;
}
.mwr-search::placeholder { color: rgba(26,24,40,0.3); }
.mwr-search:focus { border-color: var(--accent); }

/* ── COUNT ── */
.mwr-count {
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  color: var(--dim); margin-bottom: 0.75rem;
}

/* ── WRITING-SPECIFIC OVERRIDES ── */
/* .mwr-card-top styled in list container section */
.mwr-card-meta-col {
  display: flex; flex-direction: column; gap: 0.15rem;
  justify-content: center; min-height: 100%;
}
.mwr-divider {
  border: none; border-top: 1px solid var(--border);
  margin: 0.4rem 0;
  width: 100%;
}
.mwr-word-link {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', serif;
  font-size: 2.2rem; color: var(--ink);
  text-decoration: none; font-weight: 400;
  transition: opacity 0.15s;
  line-height: 1.2;
}
.mwr-word-link:hover { opacity: 0.7; }
.mwr-pinyin {
  font-family: 'DM Mono', monospace; font-size: 0.78rem;
  color: var(--dim);
}
.mwr-word-vertical {
  writing-mode: vertical-rl;
  text-orientation: mixed;
  letter-spacing: 0.08em;
  line-height: 1.3;
}
.ex-sent-cn .highlight { color: var(--accent); font-weight: 600; }
.saved-writing-chips {
  display: flex; align-items: stretch; gap: 0.4rem;
  flex-wrap: wrap; margin-bottom: 0.15rem;
}
.saved-writing-chips .ex-sent-pos,
.saved-writing-chips .shifu-chip,
.saved-writing-chips .visibility-chip {
  display: inline-flex; align-items: center;
  height: 1.5rem; box-sizing: border-box;
}
.visibility-chip {
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  letter-spacing: 0.04em;
  border-radius: 2px; padding: 0.1rem 0.45rem;
  white-space: nowrap; cursor: pointer;
  border: 1px solid transparent;
  transition: background 0.15s, border-color 0.15s;
  user-select: none;
}
.visibility-chip.is-public {
  color: var(--jade, #1a7f5a);
  background: rgba(26,127,90,0.07);
  border-color: rgba(26,127,90,0.22);
}
.visibility-chip.is-private {
  color: var(--dim);
  background: rgba(0,0,0,0.04);
  border-color: rgba(0,0,0,0.12);
}
.visibility-chip:hover { filter: brightness(0.95); }
.shifu-chip {
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  letter-spacing: 0.04em;
  color: var(--accent); background: rgba(98,64,200,0.07);
  border: 1px solid rgba(98,64,200,0.18);
  border-radius: 2px; padding: 0.1rem 0.45rem;
  white-space: nowrap;
}

/* ── FEEDBACK ── */
.saved-item-feedback { margin-top: 0.6rem; }
.saved-item-feedback summary {
  font-family: 'DM Mono', monospace; font-size: 0.85rem;
  color: var(--accent); cursor: pointer; user-select: none;
  list-style: none;
}
.saved-item-feedback summary::before { content: '▸ '; }
.saved-item-feedback[open] summary::before { content: '▾ '; }
.saved-item-feedback-text {
  font-family: 'Cormorant Garamond', serif; font-size: 1rem;
  color: var(--dim); line-height: 1.6;
  padding: 0.3rem 0 0 0.4rem;
  border-left: 2px solid rgba(98,64,200,0.15);
  margin-top: 0.2rem;
}

/* ── META ROW ── */
.mwr-meta {
  display: flex; align-items: center; justify-content: space-between;
  margin-top: 0.4rem; gap: 0.5rem;
}
.mwr-date {
  font-family: 'DM Mono', monospace; font-size: 0.62rem;
  color: var(--dim); opacity: 0.7;
}
.mwr-delete-btn {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--dim); background: none; border: none;
  cursor: pointer; padding: 0.2rem 0;
  transition: color 0.2s;
}
.mwr-delete-btn:hover { color: var(--rose); }

/* ── DELETE CONFIRM — uses shared .confirm-delete-* from _foundations ── */
/* Legacy aliases kept for backward compat */
.delete-confirm { /* now uses shared .confirm-delete-bar */ }
.delete-confirm-yes {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: #fff; background: var(--rose);
  border: none; border-radius: 2px;
  padding: 0.3rem 0.7rem; cursor: pointer;
}
.delete-confirm-yes:hover { opacity: 0.8; }
.delete-confirm-no {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim); background: none;
  border: 1px solid var(--border); border-radius: 2px;
  padding: 0.3rem 0.7rem; cursor: pointer;
}
.delete-confirm-no:hover { border-color: var(--accent); color: var(--text); }

/* ── LIST CONTAINER ── */
.mwr-list {
  display: flex; flex-direction: column; gap: 0.75rem;
}
.mwr-list .ex-sent.saved-writing {
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 3px;
  padding: 0.6rem 0.75rem;
  overflow: hidden;
  transition: border-color 0.15s;
}
.mwr-list .ex-sent.saved-writing:hover {
  border-color: rgba(98,64,200,0.25);
}
/* Hero header (word, POS, pinyin, chips) */
.mwr-card-top {
  display: flex; flex-direction: row; gap: 0.6rem;
  align-items: flex-start;
}
/* Content body (translation, feedback, meta) — white bg inset */
.mwr-card-body {
  background: var(--bg);
  border-radius: 3px;
  padding: 0.6rem 0.75rem;
  margin-top: 0.5rem;
}

/* ── EMPTY STATE ── */
.mwr-empty {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1rem; color: var(--dim);
  font-style: italic; padding: 2rem 0;
  text-align: center;
}
.mwr-empty a { color: var(--accent); }

/* ── PAGINATION ── */
.mwr-pagination {
  display: flex; justify-content: center; gap: 0.5rem;
  margin-top: 1.5rem; padding-top: 1rem;
  border-top: 1px solid var(--border);
}
.mwr-pagination a,
.mwr-pagination span {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  padding: 0.35rem 0.7rem; border-radius: 2px;
  text-decoration: none; transition: all 0.15s;
}
.mwr-pagination a {
  color: var(--accent);
  border: 1px solid rgba(98,64,200,0.25);
}
.mwr-pagination a:hover {
  background: rgba(98,64,200,0.08);
  border-color: var(--accent);
}
.mwr-pagination span.current {
  color: #fff; background: var(--accent);
  border: 1px solid var(--accent);
}
.mwr-pagination span.disabled {
  color: var(--dim); opacity: 0.4;
  border: 1px solid var(--border);
}

/* ── NO RESULTS ── */
.mwr-no-results {
  display: none;
  font-family: 'DM Mono', monospace; font-size: 0.78rem;
  color: var(--dim); text-align: center; padding: 1.5rem 0;
}
</style>
</head>
<body>
<script>
  window.__AUTH = @json($authUser);
  var textDir = localStorage.getItem('textDir') || 'horizontal';
</script>

@include('partials.lexicon._site-header', ['backUrl' => route('lexicon.index'), 'backLabel' => 'Lexicon'])

<div class="mwr-main">
  @if($writings->total() === 0)
    <div class="mwr-empty">
      No writings yet. <a href="{{ route('lexicon.index') }}">Explore the lexicon</a> and use the Writing Conservatory to create your first one.
    </div>
  @else
    <input type="text" class="mwr-search" id="mwrSearch" placeholder="Search writings…" oninput="mwrFilter()">
    <div class="mwr-count" id="mwrCount">{{ $writings->total() }} {{ $writings->total() === 1 ? 'writing' : 'writings' }}</div>

    <div class="ex-sentences mwr-list" id="mwrList">
      @foreach($writings as $w)
        <div class="ex-sent saved-writing" id="mwr-card-{{ $w['id'] }}" data-search="{{ mb_strtolower($w['chinese_text'] . ' ' . $w['english_text'] . ' ' . $w['traditional']) }}" data-id="{{ $w['id'] }}" data-word="{{ $w['traditional'] }}">
          <div class="mwr-card-top">
            <a href="/lexicon/{{ $w['smartId'] }}" class="mwr-word-link">{{ $w['traditional'] }}</a>
            <div class="mwr-card-meta-col">
              <div class="saved-writing-chips">
                @if($w['posAbbr'])
                  <span class="ex-sent-pos">{{ $w['posAbbr'] }}</span>
                @endif
                @if($w['source_type'] === 'generated')
                  <span class="shifu-chip">🙏 師父 generated</span>
                @elseif($w['ai_verified'])
                  <span class="shifu-chip">👏 師父 verified</span>
                @endif
                <span class="visibility-chip {{ $w['is_public'] ? 'is-public' : 'is-private' }}"
                      id="mwr-vis-{{ $w['id'] }}"
                      data-id="{{ $w['id'] }}"
                      data-public="{{ $w['is_public'] ? '1' : '0' }}"
                      onclick="mwrToggleVisibility(this)"
                      title="Click to toggle community visibility">
                  {!! $w['is_public'] ? '🌐 Public' : '🔒 Private' !!}
                </span>
              </div>
              @if(!empty($w['assessed_level']) || !empty($w['assessed_mastery']))
                <div class="saved-writing-chips ws-assess-row">
                  @if(!empty($w['assessed_level']))
                    @php
                      $lvlMap = ['beginner'=>['🌱','Beginner','初學'],'learner'=>['🌿','Learner','學習'],'developing'=>['🍃','Developing','發展'],'advanced'=>['🌳','Advanced','進階'],'fluent'=>['🀄','Fluent','流利']];
                      $lv = $lvlMap[$w['assessed_level']] ?? null;
                    @endphp
                    @if($lv)
                      <span class="ws-level-chip">{{ $lv[0] }} {{ $lv[1] }}</span>
                    @endif
                  @endif
                  @if(!empty($w['assessed_mastery']))
                    @php
                      $mstMap = ['seed'=>['🌱','Seed','播'],'sprout'=>['🌿','Sprout','萌'],'bud'=>['🌸','Bud','蕾'],'flower'=>['🌼','Flower','綻'],'fruit'=>['🍎','Fruit','熟']];
                      $ms = $mstMap[$w['assessed_mastery']] ?? null;
                    @endphp
                    @if($ms)
                      <span class="ws-mastery-chip">{{ $ms[0] }} {{ $ms[1] }}</span>
                    @endif
                  @endif
                </div>
              @endif
              @if($w['pinyin'])
                <div class="mwr-pinyin">{{ $w['pinyin'] }}</div>
              @endif
            </div>
          </div>
          <div class="mwr-card-body">
            <div class="ex-sent-cn" data-word="{{ $w['traditional'] }}">{{ $w['chinese_text'] }}</div>
            <div class="ex-sent-en">{{ $w['english_text'] }}</div>
            @if($w['ai_feedback'])
              <details class="saved-item-feedback">
                <summary>師父 feedback</summary>
                <div class="saved-item-feedback-text">{{ $w['ai_feedback'] }}@if(!empty($w['mastery_guidance']))<div class="ws-mastery-guidance" style="margin-top:0.4rem;color:var(--accent)"><strong>Growth tip:</strong> {{ $w['mastery_guidance'] }}</div>@endif</div>
              </details>
            @endif
            <div class="mwr-meta">
              <span class="mwr-date">{{ $w['created_at'] }}</span>
              <button class="mwr-delete-btn" onclick="mwrConfirmDelete(this, {{ $w['id'] }})">✕ delete</button>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mwr-no-results" id="mwrNoResults">No writings match your search.</div>

    @if($writings->hasPages())
      <div class="mwr-pagination">
        @if($writings->onFirstPage())
          <span class="disabled">&larr; Previous</span>
        @else
          <a href="{{ $writings->previousPageUrl() }}">&larr; Previous</a>
        @endif

        @foreach($writings->getUrlRange(1, $writings->lastPage()) as $page => $url)
          @if($page == $writings->currentPage())
            <span class="current">{{ $page }}</span>
          @else
            <a href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach

        @if($writings->hasMorePages())
          <a href="{{ $writings->nextPageUrl() }}">Next &rarr;</a>
        @else
          <span class="disabled">Next &rarr;</span>
        @endif
      </div>
    @endif
  @endif
</div>

<script>
var csrf = document.querySelector('meta[name="csrf-token"]').content;

// Apply vertical mode if learner preference
if (textDir === 'vertical') {
  document.querySelectorAll('.ex-sent').forEach(function(el) { el.classList.add('vertical'); });
  document.querySelectorAll('.mwr-word-link').forEach(function(el) { el.classList.add('mwr-word-vertical'); });
}

// Highlight headword in Chinese text with purple
document.querySelectorAll('.ex-sent-cn[data-word]').forEach(function(el) {
  var word = el.dataset.word;
  if (word && el.textContent.includes(word)) {
    el.innerHTML = el.textContent.split(word).join('<span class="highlight">' + word + '</span>');
  }
});

function mwrFilter() {
  var q = document.getElementById('mwrSearch').value.toLowerCase().trim();
  var cards = document.querySelectorAll('.ex-sent[data-search]');
  var visible = 0;
  cards.forEach(function(card) {
    var match = !q || card.dataset.search.includes(q);
    card.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  document.getElementById('mwrNoResults').style.display = visible === 0 && q ? 'block' : 'none';
}

function mwrConfirmDelete(btn, id) {
  var card = btn.closest('.ex-sent');
  if (card.querySelector('.delete-confirm')) return;
  var bar = document.createElement('div');
  bar.className = 'delete-confirm';
  bar.innerHTML = '<span class="delete-confirm-msg">Delete this writing?</span>' +
    '<button class="delete-confirm-yes" onclick="mwrDelete(' + id + ', this)">Delete</button>' +
    '<button class="delete-confirm-no" onclick="this.closest(\'.delete-confirm\').remove()">Cancel</button>';
  card.appendChild(bar);
}

function mwrToggleVisibility(chip) {
  var id = chip.dataset.id;
  var current = chip.dataset.public === '1';
  var next = !current;
  chip.style.opacity = '0.5';
  fetch('/my-writings/' + id + '/visibility', {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    body: JSON.stringify({ is_public: next }),
  }).then(function(r) { return r.ok ? r.json() : null; }).then(function(data) {
    chip.style.opacity = '1';
    if (!data || !data.ok) return;
    chip.dataset.public = data.is_public ? '1' : '0';
    chip.classList.toggle('is-public', data.is_public);
    chip.classList.toggle('is-private', !data.is_public);
    chip.innerHTML = data.is_public ? '🌐 Public' : '🔒 Private';
  }).catch(function() { chip.style.opacity = '1'; });
}

function mwrDelete(id, btn) {
  btn.disabled = true; btn.textContent = '…';
  fetch('/api/workshop/saved-example/' + id, {
    method: 'DELETE',
    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
  }).then(function(r) {
    if (r.ok) {
      var card = document.getElementById('mwr-card-' + id);
      card.style.transition = 'opacity 0.2s';
      card.style.opacity = '0';
      setTimeout(function() { card.remove(); }, 250);
    }
  });
}
</script>
@include('partials.lexicon._site-footer')
</body>
</html>
