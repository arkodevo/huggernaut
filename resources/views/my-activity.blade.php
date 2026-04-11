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
      <div class="mact-coming-soon">
        <div class="cs-label">COMING SOON</div>
        <div class="cs-body">
          When you dispute a word sense, it will appear here with its status — pending, under review by 三人行, or resolved — along with the verdict and rationale.
        </div>
      </div>
    @else
      <div class="mact-coming-soon">
        <div class="cs-label">COMING SOON</div>
        <div class="cs-body">
          Every sense you affirm will appear here. You'll be able to un-affirm with a single click and see which of your affirmations have boosted a word's community confidence score.
        </div>
      </div>
    @endif
  </div>
</div>

@include('partials.lexicon._site-footer')
</body>
</html>
