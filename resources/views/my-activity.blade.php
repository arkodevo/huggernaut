<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>My Activity — 流動 Living Lexicon</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@300;400;600;700&family=DM+Mono:ital,wght@0,300;0,400;1,300&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
@include('partials.lexicon._foundations')
<style>
.mact-main { max-width: 720px; margin: 0 auto; padding: 1.5rem 1rem 3rem; }
.mact-header { margin-bottom: 1.25rem; }
.mact-title {
  font-family: 'DM Mono', monospace; font-size: 1.1rem;
  color: var(--ink); letter-spacing: 0.04em;
  margin: 0 0 0.35rem;
}
.mact-sub {
  font-family: 'Cormorant Garamond', serif; font-size: 0.95rem;
  color: var(--dim); margin: 0;
}

/* ── TABS ── */
.mact-tabs {
  display: flex; gap: 0.25rem;
  border-bottom: 1px solid var(--border);
  margin-bottom: 1.25rem;
}
.mact-tab {
  font-family: 'DM Mono', monospace; font-size: 0.78rem;
  letter-spacing: 0.04em; color: var(--dim);
  padding: 0.6rem 0.9rem;
  border-bottom: 2px solid transparent;
  text-decoration: none;
  transition: color 0.15s, border-color 0.15s;
  cursor: pointer;
}
.mact-tab:hover { color: var(--ink); }
.mact-tab.active {
  color: var(--accent);
  border-bottom-color: var(--accent);
}

/* ── TAB CONTENT ── */
.mact-panel {
  min-height: 200px;
}

.mact-empty {
  font-family: 'Cormorant Garamond', serif; font-size: 1rem;
  color: var(--dim); text-align: center;
  padding: 3rem 1rem;
  border: 1px dashed var(--border);
  border-radius: 4px;
}
.mact-empty a {
  color: var(--accent); text-decoration: none; border-bottom: 1px solid rgba(98,64,200,0.3);
}
.mact-empty a:hover { border-bottom-color: var(--accent); }

.mact-coming-soon {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim); letter-spacing: 0.05em;
  text-align: center;
  padding: 3rem 1rem;
  border: 1px dashed var(--border);
  border-radius: 4px;
  opacity: 0.7;
}
.mact-coming-soon .cs-label {
  font-size: 0.62rem; letter-spacing: 0.08em;
  color: var(--accent); margin-bottom: 0.4rem;
}
.mact-coming-soon .cs-body {
  font-family: 'Cormorant Garamond', serif; font-size: 0.95rem;
  max-width: 360px; margin: 0 auto; line-height: 1.6;
}

/* ── DISPUTATION LIST ── */
.mact-disp-list { display: flex; flex-direction: column; gap: 0.7rem; }
.mact-disp-row {
  border: 1px solid var(--border); border-radius: 4px;
  padding: 0.85rem 1rem;
  background: #fff;
  transition: border-color 0.12s;
}
.mact-disp-row:hover { border-color: rgba(184, 48, 80, 0.3); }
.mact-disp-head {
  display: flex; align-items: baseline; gap: 0.75rem;
  flex-wrap: wrap; margin-bottom: 0.4rem;
}
.mact-disp-char {
  font-family: 'Noto Serif TC', serif; font-size: 1.4rem;
  color: var(--ink); text-decoration: none;
  line-height: 1;
}
.mact-disp-char:hover { color: var(--accent); }
.mact-disp-meta {
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  color: var(--dim); letter-spacing: 0.03em;
}
.mact-disp-meta-pos { color: var(--accent); }
.mact-disp-status {
  margin-left: auto;
  font-family: 'DM Mono', monospace; font-size: 0.58rem;
  letter-spacing: 0.08em; text-transform: uppercase;
  padding: 0.18rem 0.5rem;
  border-radius: 2px;
}
.mact-disp-status.pending       { color: var(--rose); background: rgba(184,48,80,0.08); border: 1px solid rgba(184,48,80,0.22); }
.mact-disp-status.under-review  { color: var(--gold); background: rgba(160,114,10,0.08); border: 1px solid rgba(160,114,10,0.28); }
.mact-disp-status.resolved      { color: var(--jade); background: rgba(26,138,90,0.08); border: 1px solid rgba(26,138,90,0.28); }
.mact-disp-fields {
  display: flex; flex-wrap: wrap; gap: 0.35rem;
  margin-bottom: 0.5rem;
}
.mact-disp-field-chip {
  font-family: 'DM Mono', monospace; font-size: 0.58rem;
  letter-spacing: 0.04em;
  color: var(--rose);
  background: rgba(184,48,80,0.06);
  border: 1px solid rgba(184,48,80,0.22);
  border-radius: 2px;
  padding: 0.12rem 0.45rem;
}
.mact-disp-rationale {
  font-family: 'Cormorant Garamond', serif; font-size: 0.95rem;
  color: var(--ink); line-height: 1.55;
  margin-bottom: 0.55rem;
  white-space: pre-wrap;
}
.mact-disp-foot {
  display: flex; align-items: center; justify-content: space-between;
  gap: 0.5rem;
  font-family: 'DM Mono', monospace; font-size: 0.62rem;
  color: var(--dim);
  padding-top: 0.4rem;
  border-top: 1px solid rgba(0,0,0,0.06);
}
.mact-disp-date { opacity: 0.8; }
.mact-disp-anon-tag {
  font-size: 0.55rem; color: var(--dim);
  letter-spacing: 0.06em; text-transform: uppercase;
  margin-left: 0.4rem; opacity: 0.6;
}
.mact-disp-delete {
  font-family: 'DM Mono', monospace; font-size: 0.62rem;
  color: var(--dim); background: none; border: none;
  cursor: pointer; padding: 0.18rem 0.4rem;
  transition: color 0.15s;
}
.mact-disp-delete:hover { color: var(--rose); }

/* ── AFFIRMATION LIST ── */
.mact-aff-list { display: flex; flex-direction: column; gap: 0.5rem; }
.mact-aff-row {
  display: flex; align-items: baseline; gap: 0.75rem;
  padding: 0.75rem 0.9rem;
  border: 1px solid var(--border); border-radius: 4px;
  text-decoration: none; color: inherit;
  transition: background 0.12s, border-color 0.12s;
}
.mact-aff-row:hover { background: rgba(0,0,0,0.015); border-color: var(--accent); }
.mact-aff-char {
  font-family: 'Noto Serif TC', serif; font-size: 1.35rem;
  color: var(--ink); min-width: 2.2rem;
}
.mact-aff-body { flex: 1; min-width: 0; }
.mact-aff-top {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim); letter-spacing: 0.03em;
  margin-bottom: 0.15rem;
}
.mact-aff-pos { color: var(--accent); }
.mact-aff-def {
  font-family: 'Cormorant Garamond', serif; font-size: 0.95rem;
  color: var(--ink); line-height: 1.35;
  overflow: hidden; text-overflow: ellipsis;
}
.mact-aff-date {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  color: var(--dim); letter-spacing: 0.04em;
  white-space: nowrap;
}
</style>
</head>
<body>
<script>
  window.__AUTH = @json($authUser);
</script>

@include('partials.lexicon._site-header', ['backUrl' => route('lexicon.index'), 'backLabel' => 'Lexicon'])

<div class="mact-main">
  <div class="mact-header">
    <h1 class="mact-title">My Activity</h1>
    <p class="mact-sub">Everything you've contributed to the Living Lexicon — writings, disputations, affirmations.</p>
  </div>

  {{-- ── TABS ── --}}
  <div class="mact-tabs">
    <a class="mact-tab {{ $tab === 'writings' ? 'active' : '' }}"
       href="{{ route('my-activity', ['tab' => 'writings']) }}">
      📖 Writings
    </a>
    <a class="mact-tab {{ $tab === 'disputations' ? 'active' : '' }}"
       href="{{ route('my-activity', ['tab' => 'disputations']) }}">
      💬 Disputations
    </a>
    <a class="mact-tab {{ $tab === 'affirmations' ? 'active' : '' }}"
       href="{{ route('my-activity', ['tab' => 'affirmations']) }}">
      👍 Affirmations
    </a>
  </div>

  {{-- ── PANELS ── --}}
  <div class="mact-panel">
    @if ($tab === 'writings')
      <div class="mact-empty">
        Your writings live at <a href="{{ route('my-writings') }}">My Writings</a> — open any one to manage its community visibility with the 🌐 / 🔒 chip.
      </div>
    @elseif ($tab === 'disputations')
      @if (empty($disputations))
        <div class="mact-empty">
          No disputations yet. Tap 👎 on any sense in the lexicon to flag a field for review.
        </div>
      @else
        <div class="mact-disp-list">
          @foreach ($disputations as $d)
            <div class="mact-disp-row" id="mact-disp-{{ $d['id'] }}">
              <div class="mact-disp-head">
                <a class="mact-disp-char" href="{{ route('lexicon.show', $d['smartId']) }}">{{ $d['traditional'] }}</a>
                <div>
                  @if ($d['pinyin']) <span class="mact-disp-meta">{{ $d['pinyin'] }}</span> @endif
                  @if ($d['pos']) <span class="mact-disp-meta mact-disp-meta-pos">· {{ $d['pos'] }}</span> @endif
                </div>
                <span class="mact-disp-status {{ str_replace('_', '-', $d['status']) }}">
                  {{ str_replace('_', ' ', $d['status']) }}@if ($d['status'] === 'resolved' && $d['verdict']) · {{ str_replace('_', ' ', $d['verdict']) }}@endif
                </span>
              </div>
              @if (count($d['fields']))
                <div class="mact-disp-fields">
                  @foreach ($d['fields'] as $f)
                    <span class="mact-disp-field-chip">{{ str_replace(['attribute:', 'domain:', 'example:', '_'], ['', '', 'ex ', ' '], $f) }}</span>
                  @endforeach
                </div>
              @endif
              <div class="mact-disp-rationale">{{ $d['rationale'] }}</div>
              <div class="mact-disp-foot">
                <span>
                  <span class="mact-disp-date">{{ $d['createdAt']->diffForHumans() }}</span>
                  @if ($d['isAnonymous'])
                    <span class="mact-disp-anon-tag">anonymous to others</span>
                  @endif
                </span>
                @if ($d['canDelete'])
                  <button class="mact-disp-delete" onclick="mactDeleteDispute({{ $d['id'] }})">✕ withdraw</button>
                @endif
              </div>
            </div>
          @endforeach
        </div>

        <script>
        function mactDeleteDispute(id) {
          if (!confirm('Withdraw this disputation? This cannot be undone.')) return;
          fetch('/api/disputations/' + id, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json'
            }
          }).then(r => r.ok ? r.json() : Promise.reject(r))
            .then(data => {
              if (data.ok) {
                const el = document.getElementById('mact-disp-' + id);
                if (el) { el.style.transition = 'opacity 0.2s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 250); }
              } else {
                alert(data.message || 'Could not withdraw this disputation.');
              }
            }).catch(() => alert('Network error; please try again.'));
        }
        </script>
      @endif
    @else
      @if (empty($affirmations))
        <div class="mact-empty">
          No affirmations yet. Tap 👍 on any sense in the lexicon to affirm it — you're vouching that the definition, examples, and nuance ring true.
        </div>
      @else
        <div class="mact-aff-list">
          @foreach ($affirmations as $a)
            <a class="mact-aff-row" href="{{ route('lexicon.show', $a['smartId']) }}">
              <div class="mact-aff-char">{{ $a['traditional'] }}</div>
              <div class="mact-aff-body">
                <div class="mact-aff-top">
                  @if ($a['pinyin']) <span>{{ $a['pinyin'] }}</span> @endif
                  @if ($a['pos']) <span class="mact-aff-pos">· {{ $a['pos'] }}</span> @endif
                </div>
                <div class="mact-aff-def">{{ $a['definition'] }}</div>
              </div>
              <div class="mact-aff-date">{{ $a['affirmedAt']->diffForHumans() }}</div>
            </a>
          @endforeach
        </div>
      @endif
    @endif
  </div>
</div>

@include('partials.lexicon._site-footer')
</body>
</html>
