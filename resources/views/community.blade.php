<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Community — 流動 Living Lexicon</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@300;400;600;700&family=DM+Mono:ital,wght@0,300;0,400;1,300&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
@include('partials.lexicon._foundations')
@include('partials.lexicon._example-sentence-css')
@include('partials.lexicon._workshop-css')
<style>
.com-main { max-width: 720px; margin: 0 auto; padding: 1.5rem 1rem 3rem; }
.com-header { margin-bottom: 1.25rem; }
.com-title {
  font-family: 'DM Mono', monospace; font-size: 1.1rem;
  color: var(--ink); letter-spacing: 0.04em;
  margin: 0 0 0.35rem;
}
.com-sub {
  font-family: 'Cormorant Garamond', serif; font-size: 0.95rem;
  color: var(--dim); margin: 0;
}

/* ── TABS (match my-activity) ── */
.com-tabs {
  display: flex; gap: 0.25rem;
  border-bottom: 1px solid var(--border);
  margin-bottom: 1.25rem;
}
.com-tab {
  font-family: 'DM Mono', monospace; font-size: 0.78rem;
  letter-spacing: 0.04em; color: var(--dim);
  padding: 0.6rem 0.9rem;
  border-bottom: 2px solid transparent;
  text-decoration: none;
  transition: color 0.15s, border-color 0.15s;
  cursor: pointer;
}
.com-tab:hover { color: var(--ink); }
.com-tab.active {
  color: var(--accent);
  border-bottom-color: var(--accent);
}

/* ── PANEL ── */
.com-panel { min-height: 200px; }

.com-empty {
  font-family: 'Cormorant Garamond', serif; font-size: 1rem;
  color: var(--dim); text-align: center;
  padding: 3rem 1rem;
  border: 1px dashed var(--border); border-radius: 4px;
}
.com-empty a {
  color: var(--accent); text-decoration: none;
  border-bottom: 1px solid rgba(98,64,200,0.3);
}
.com-empty a:hover { border-bottom-color: var(--accent); }

.com-coming-soon {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim); letter-spacing: 0.05em;
  text-align: center;
  padding: 3rem 1rem;
  border: 1px dashed var(--border); border-radius: 4px;
  opacity: 0.7;
}
.com-coming-soon .cs-label {
  font-size: 0.62rem; letter-spacing: 0.08em;
  color: var(--accent); margin-bottom: 0.4rem;
}
.com-coming-soon .cs-body {
  font-family: 'Cormorant Garamond', serif; font-size: 0.95rem;
  max-width: 380px; margin: 0 auto; line-height: 1.6;
}

/* ── WRITINGS LIST (reuses mwr-list card pattern) ── */
.com-count {
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  color: var(--dim); margin-bottom: 0.75rem;
}
.com-wr-list {
  display: flex; flex-direction: column; gap: 0.75rem;
}
.com-wr-list .ex-sent.saved-writing {
  background: var(--surface2);
  border: 1px solid var(--border); border-radius: 3px;
  padding: 0.6rem 0.75rem; overflow: hidden;
  transition: border-color 0.15s;
}
.com-wr-list .ex-sent.saved-writing:hover {
  border-color: rgba(98,64,200,0.25);
}
.com-card-top {
  display: flex; flex-direction: row; gap: 0.6rem;
  align-items: flex-start;
}
.com-card-body {
  background: var(--bg);
  border-radius: 3px;
  padding: 0.6rem 0.75rem;
  margin-top: 0.5rem;
}
.com-word-link {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', serif;
  font-size: 2.2rem; color: var(--ink);
  text-decoration: none; font-weight: 400;
  transition: opacity 0.15s;
  line-height: 1.2;
}
.com-word-link:hover { opacity: 0.7; }
.com-pinyin {
  font-family: 'DM Mono', monospace; font-size: 0.78rem;
  color: var(--dim);
}
.com-card-meta-col {
  display: flex; flex-direction: column; gap: 0.15rem;
  justify-content: center; min-height: 100%;
}
.com-writing-chips {
  display: flex; align-items: stretch; gap: 0.4rem;
  flex-wrap: wrap; margin-bottom: 0.15rem;
}
.com-writing-chips .ex-sent-pos,
.com-writing-chips .shifu-chip {
  display: inline-flex; align-items: center;
  height: 1.5rem; box-sizing: border-box;
}
.shifu-chip {
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  letter-spacing: 0.04em;
  color: var(--accent); background: rgba(98,64,200,0.07);
  border: 1px solid rgba(98,64,200,0.18);
  border-radius: 2px; padding: 0.1rem 0.45rem;
  white-space: nowrap;
}
.com-author-row {
  display: flex; align-items: center; justify-content: space-between;
  margin-top: 0.55rem; gap: 0.5rem;
  padding-top: 0.5rem;
  border-top: 1px solid rgba(0,0,0,0.06);
}
.com-author {
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  color: var(--dim); letter-spacing: 0.03em;
}
.com-author-name { color: var(--ink); }
.com-date {
  font-family: 'DM Mono', monospace; font-size: 0.62rem;
  color: var(--dim); opacity: 0.7;
}
.com-fb-summary {
  font-family: 'DM Mono', monospace; font-size: 0.85rem;
  color: var(--accent); cursor: pointer; user-select: none;
  list-style: none;
}
.com-fb-summary::before { content: '▸ '; }
details[open] .com-fb-summary::before { content: '▾ '; }
.com-fb-text {
  font-family: 'Cormorant Garamond', serif; font-size: 1rem;
  color: var(--dim); line-height: 1.6;
  padding: 0.3rem 0 0 0.4rem;
  border-left: 2px solid rgba(98,64,200,0.15);
  margin-top: 0.2rem;
}
.ex-sent-cn .highlight { color: var(--accent); font-weight: 600; }

/* ── MOST-AFFIRMED LEADERBOARD ── */
.com-lead-intro {
  font-family: 'Cormorant Garamond', serif; font-size: 0.95rem;
  color: var(--dim); line-height: 1.5;
  margin-bottom: 1rem; padding: 0 0.25rem;
  font-style: italic;
}
.com-lead-list { display: flex; flex-direction: column; gap: 0.45rem; }
.com-lead-row {
  display: flex; align-items: center; gap: 0.85rem;
  padding: 0.7rem 0.9rem;
  border: 1px solid var(--border); border-radius: 4px;
  text-decoration: none; color: inherit;
  transition: background 0.12s, border-color 0.12s;
}
.com-lead-row:hover { background: rgba(0,0,0,0.015); border-color: var(--accent); }
.com-lead-rank {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim); letter-spacing: 0.03em;
  min-width: 1.8rem; text-align: right;
  opacity: 0.7;
}
.com-lead-row.is-top .com-lead-rank { color: var(--accent); opacity: 1; font-weight: 600; }
.com-lead-char {
  font-family: 'Noto Serif TC', serif; font-size: 1.4rem;
  color: var(--ink); min-width: 2.2rem;
  line-height: 1;
}
.com-lead-body { flex: 1; min-width: 0; }
.com-lead-top {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim); letter-spacing: 0.03em;
  margin-bottom: 0.15rem;
}
.com-lead-pos { color: var(--accent); }
.com-lead-def {
  font-family: 'Cormorant Garamond', serif; font-size: 0.95rem;
  color: var(--ink); line-height: 1.35;
  overflow: hidden; text-overflow: ellipsis;
  white-space: nowrap;
}
.com-lead-count {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  min-width: 3rem;
  padding: 0.25rem 0.5rem;
  background: rgba(98,64,200,0.06);
  border: 1px solid rgba(98,64,200,0.18);
  border-radius: 3px;
}
.com-lead-count-num {
  font-family: 'DM Mono', monospace; font-size: 0.95rem;
  color: var(--accent); font-weight: 600;
  line-height: 1.1;
}
.com-lead-count-lbl {
  font-family: 'DM Mono', monospace; font-size: 0.55rem;
  color: var(--dim); letter-spacing: 0.06em;
  text-transform: uppercase;
  margin-top: 0.1rem;
}

/* ── PAGINATION (match my-writings) ── */
.com-pagination {
  display: flex; justify-content: center; gap: 0.5rem;
  margin-top: 1.5rem; padding-top: 1rem;
  border-top: 1px solid var(--border);
}
.com-pagination a,
.com-pagination span {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  padding: 0.35rem 0.7rem; border-radius: 2px;
  text-decoration: none; transition: all 0.15s;
}
.com-pagination a {
  color: var(--accent);
  border: 1px solid rgba(98,64,200,0.25);
}
.com-pagination a:hover {
  background: rgba(98,64,200,0.08);
  border-color: var(--accent);
}
.com-pagination span.current {
  color: #fff; background: var(--accent);
  border: 1px solid var(--accent);
}
.com-pagination span.disabled {
  color: var(--dim); opacity: 0.4;
  border: 1px solid var(--border);
}
</style>
</head>
<body>
<script>
  window.__AUTH = @json($authUser);
  var textDir = localStorage.getItem('textDir') || 'horizontal';
</script>

@include('partials.lexicon._site-header', ['backUrl' => route('lexicon.index'), 'backLabel' => 'Lexicon'])

<div class="com-main">
  <div class="com-header">
    <h1 class="com-title">Community</h1>
    <p class="com-sub">Writings, disputations, and affirmations from every learner in the Lexicon.</p>
  </div>

  {{-- ── TABS ── --}}
  <div class="com-tabs">
    <a class="com-tab {{ $tab === 'writings' ? 'active' : '' }}"
       href="{{ route('community', ['tab' => 'writings']) }}">
      📖 Writings
    </a>
    <a class="com-tab {{ $tab === 'disputations' ? 'active' : '' }}"
       href="{{ route('community', ['tab' => 'disputations']) }}">
      💬 Disputations
    </a>
    <a class="com-tab {{ $tab === 'affirmations' ? 'active' : '' }}"
       href="{{ route('community', ['tab' => 'affirmations']) }}">
      👍 Affirmations
    </a>
  </div>

  {{-- ── PANELS ── --}}
  <div class="com-panel">

    @if ($tab === 'writings')
      @if (! $writings || $writings->total() === 0)
        <div class="com-empty">
          No public writings yet. When a learner flips a writing to 🌐 Public on their <a href="{{ route('my-writings') }}">My Writings</a> page, it will appear here.
        </div>
      @else
        <div class="com-count">{{ $writings->total() }} {{ $writings->total() === 1 ? 'writing' : 'writings' }}</div>
        <div class="ex-sentences com-wr-list">
          @foreach ($writings as $w)
            <div class="ex-sent saved-writing" data-word="{{ $w['traditional'] }}">
              <div class="com-card-top">
                <a href="/lexicon/{{ $w['smartId'] }}" class="com-word-link">{{ $w['traditional'] }}</a>
                <div class="com-card-meta-col">
                  <div class="com-writing-chips">
                    @if ($w['posAbbr'])
                      <span class="ex-sent-pos">{{ $w['posAbbr'] }}</span>
                    @endif
                    @if ($w['source_type'] === 'generated')
                      <span class="shifu-chip">🙏 師父 generated</span>
                    @elseif ($w['ai_verified'])
                      <span class="shifu-chip">👏 師父 verified</span>
                    @endif
                  </div>
                  @if (! empty($w['assessed_level']) || ! empty($w['assessed_mastery']))
                    <div class="com-writing-chips ws-assess-row">
                      @if (! empty($w['assessed_level']))
                        @php
                          $lvlMap = ['beginner'=>['🌱','Beginner','初學'],'learner'=>['🌿','Learner','學習'],'developing'=>['🍃','Developing','發展'],'advanced'=>['🌳','Advanced','進階'],'fluent'=>['🀄','Fluent','流利']];
                          $lv = $lvlMap[$w['assessed_level']] ?? null;
                        @endphp
                        @if ($lv)<span class="ws-level-chip">{{ $lv[0] }} {{ $lv[1] }}</span>@endif
                      @endif
                      @if (! empty($w['assessed_mastery']))
                        @php
                          $mstMap = ['seed'=>['🌱','Seed','播'],'sprout'=>['🌿','Sprout','萌'],'bud'=>['🌸','Bud','蕾'],'flower'=>['🌼','Flower','綻'],'fruit'=>['🍎','Fruit','熟']];
                          $ms = $mstMap[$w['assessed_mastery']] ?? null;
                        @endphp
                        @if ($ms)<span class="ws-mastery-chip">{{ $ms[0] }} {{ $ms[1] }}</span>@endif
                      @endif
                    </div>
                  @endif
                  @if ($w['pinyin'])
                    <div class="com-pinyin">{{ $w['pinyin'] }}</div>
                  @endif
                </div>
              </div>
              <div class="com-card-body">
                <div class="ex-sent-cn" data-word="{{ $w['traditional'] }}">{{ $w['chinese_text'] }}</div>
                <div class="ex-sent-en">{{ $w['english_text'] }}</div>
                @if ($w['ai_feedback'])
                  <details class="saved-item-feedback">
                    <summary class="com-fb-summary">師父 feedback</summary>
                    <div class="com-fb-text">{{ $w['ai_feedback'] }}@if (! empty($w['mastery_guidance']))<div style="margin-top:0.4rem;color:var(--accent)"><strong>Growth tip:</strong> {{ $w['mastery_guidance'] }}</div>@endif</div>
                  </details>
                @endif
                <div class="com-author-row">
                  <span class="com-author">by <span class="com-author-name">{{ $w['author'] }}</span></span>
                  <span class="com-date">{{ $w['created_at'] }}</span>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        @if ($writings->hasPages())
          <div class="com-pagination">
            @if ($writings->onFirstPage())
              <span class="disabled">&larr; Previous</span>
            @else
              <a href="{{ $writings->previousPageUrl() }}">&larr; Previous</a>
            @endif

            @foreach ($writings->getUrlRange(1, $writings->lastPage()) as $page => $url)
              @if ($page == $writings->currentPage())
                <span class="current">{{ $page }}</span>
              @else
                <a href="{{ $url }}">{{ $page }}</a>
              @endif
            @endforeach

            @if ($writings->hasMorePages())
              <a href="{{ $writings->nextPageUrl() }}">Next &rarr;</a>
            @else
              <span class="disabled">Next &rarr;</span>
            @endif
          </div>
        @endif
      @endif

    @elseif ($tab === 'disputations')
      <div class="com-coming-soon">
        <div class="cs-label">COMING SOON</div>
        <div class="cs-body">
          When learners dispute a word sense, their rationale — and 三人行's verdict once adjudicated — will appear here. Disputations are the community's editorial voice; every resolved dispute shapes the Word Object.
        </div>
      </div>

    @else {{-- affirmations: most-trusted senses leaderboard --}}
      @if (empty($affirmations))
        <div class="com-empty">
          No affirmations yet. Tap 👍 on any sense in the <a href="{{ route('lexicon.index') }}">lexicon</a> to be the first. The senses the community trusts most will surface here.
        </div>
      @else
        <div class="com-lead-intro">
          The senses learners trust most — every 👍 is a quiet vote that the definition, examples, and nuance ring true.
        </div>
        <div class="com-lead-list">
          @foreach ($affirmations as $row)
            <a class="com-lead-row {{ $row['rank'] <= 3 ? 'is-top' : '' }}"
               href="{{ route('lexicon.show', $row['smartId']) }}">
              <div class="com-lead-rank">#{{ $row['rank'] }}</div>
              <div class="com-lead-char">{{ $row['traditional'] }}</div>
              <div class="com-lead-body">
                <div class="com-lead-top">
                  @if ($row['pinyin']) <span>{{ $row['pinyin'] }}</span> @endif
                  @if ($row['pos']) <span class="com-lead-pos">· {{ $row['pos'] }}</span> @endif
                </div>
                <div class="com-lead-def">{{ $row['definition'] }}</div>
              </div>
              <div class="com-lead-count" title="{{ $row['count'] }} affirmations">
                <span class="com-lead-count-num">{{ $row['count'] }}</span>
                <span class="com-lead-count-lbl">👍</span>
              </div>
            </a>
          @endforeach
        </div>
      @endif
    @endif

  </div>
</div>

<script>
// Highlight the headword in each Chinese writing with accent colour
document.querySelectorAll('.ex-sent-cn[data-word]').forEach(function(el) {
  var word = el.dataset.word;
  if (word && el.textContent.includes(word)) {
    el.innerHTML = el.textContent.split(word).join('<span class="highlight">' + word + '</span>');
  }
});

// Respect vertical-text preference on writings cards
if (textDir === 'vertical') {
  document.querySelectorAll('.ex-sent').forEach(function(el) { el.classList.add('vertical'); });
  document.querySelectorAll('.com-word-link').forEach(function(el) {
    el.style.writingMode = 'vertical-rl';
    el.style.textOrientation = 'mixed';
    el.style.letterSpacing = '0.08em';
    el.style.lineHeight = '1.3';
  });
}
</script>

@include('partials.lexicon._site-footer')
</body>
</html>
