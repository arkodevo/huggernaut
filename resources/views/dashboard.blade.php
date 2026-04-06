@php use App\Helpers\PinyinHelper; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Dashboard — 流動 Living Lexicon</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@300;400;600;700&family=DM+Mono:ital,wght@0,300;0,400;1,300&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
@include('partials.lexicon._foundations')
<style>
/* ── MAIN ── */
.db-main { max-width: 680px; margin: 0 auto; padding: 1.5rem 1rem 3rem; }

/* ── SHIFU MESSAGE ── */
.db-shifu {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 4px;
  padding: 1.2rem;
  margin-bottom: 1rem;
  position: relative;
}
.db-shifu-header {
  display: flex; align-items: center; gap: 0.5rem;
  margin-bottom: 0.75rem;
}
.db-shifu-emoji { font-size: 1.6rem; line-height: 1; }
.db-shifu-label {
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--accent);
}
.db-shifu-msg {
  font-family: 'Cormorant Garamond', serif; font-size: 1.1rem;
  line-height: 1.6; color: var(--ink);
}
.db-shifu-loading {
  font-family: 'DM Mono', monospace; font-size: 0.75rem;
  color: var(--dim); font-style: italic;
}
.db-shifu-error {
  font-family: 'DM Mono', monospace; font-size: 0.75rem;
  color: var(--rose);
}
.db-feedback {
  display: flex; gap: 0.5rem;
  margin-top: 0.75rem;
  padding-top: 0.6rem;
  border-top: 1px solid var(--border);
}
.db-feedback-btn {
  font-size: 1.1rem; cursor: pointer;
  background: none; border: 1px solid var(--border);
  border-radius: 3px; padding: 0.2rem 0.5rem;
  opacity: 0.5; transition: all 0.15s;
}
.db-feedback-btn:hover { opacity: 0.85; border-color: var(--accent); }
.db-feedback-btn.active { opacity: 1; border-color: var(--accent); background: rgba(98,64,200,0.06); }
.db-feedback-btn.used { pointer-events: none; }

/* ── STREAK ── */
.db-streak {
  font-family: 'Cormorant Garamond', serif; font-size: 1rem;
  color: var(--dim); text-align: center;
  padding: 0.6rem 0; margin-bottom: 1rem;
}

/* ── CARD ── */
.db-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 4px;
  padding: 1rem;
}
.db-card-header {
  display: flex; align-items: center; gap: 0.4rem;
  margin-bottom: 0.6rem;
}
.db-card-title {
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--accent); flex: 1;
}

/* ── COUNT SELECTOR ── */
.db-count-btn {
  font-family: 'DM Mono', monospace; font-size: 0.55rem;
  color: var(--dim); background: none; border: 1px solid var(--border);
  border-radius: 2px; padding: 0.1rem 0.3rem;
  cursor: pointer; transition: all 0.12s;
  position: relative;
}
.db-count-btn:hover { color: var(--accent); border-color: var(--accent); }
.db-count-dropdown {
  display: none; position: absolute; right: 0; top: 100%;
  margin-top: 0.2rem; z-index: 50;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 3px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  padding: 0.2rem 0;
}
.db-count-dropdown.open { display: block; }
.db-count-option {
  display: block; width: 100%;
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--dim); background: none; border: none;
  padding: 0.25rem 0.6rem; cursor: pointer;
  text-align: right; white-space: nowrap;
  transition: all 0.1s;
}
.db-count-option:hover { color: var(--accent); background: rgba(98,64,200,0.04); }
.db-count-option.active { color: var(--accent); font-weight: 600; }

/* ── WORD GRID (two columns) ── */
.db-word-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
  margin-bottom: 1rem;
}
@media (max-width: 580px) {
  .db-word-grid { grid-template-columns: 1fr; }
}

/* ── WORD ENTRIES ── */
.db-word-list { list-style: none; padding: 0; margin: 0; }
.db-word-item {
  display: flex; align-items: center; gap: 0.5rem;
  padding: 0.35rem 0;
  border-bottom: 1px solid rgba(0,0,0,0.04);
  text-decoration: none; color: var(--ink);
  transition: background 0.12s;
}
.db-word-item:last-child { border-bottom: none; }
.db-word-item:hover { background: rgba(98,64,200,0.03); }
.db-hanzi {
  font-family: 'Noto Serif TC', serif; font-size: 1.1rem;
  font-weight: 500; min-width: 2.5rem;
}
.db-pinyin {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim); flex: 1;
}
.db-date {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  color: var(--dim); opacity: 0.7;
}

/* ── PROGRESS DOTS ── */
.db-dots { display: flex; gap: 0.2rem; }
.db-dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: var(--border);
}
.db-dot.pass { background: var(--jade); }

/* ── WRONG COUNT ── */
.db-wrong {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  color: var(--rose); opacity: 0.8;
}

/* ── TEST LIST ── */
.db-test-list { list-style: none; padding: 0; margin: 0; }
.db-test-item {
  display: flex; align-items: center; gap: 0.5rem;
  padding: 0.4rem 0;
  border-bottom: 1px solid rgba(0,0,0,0.04);
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
}
.db-test-item:last-child { border-bottom: none; }
.db-test-collection {
  flex: 1; color: var(--ink);
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.db-test-mode {
  font-size: 0.6rem; color: var(--dim);
  background: rgba(0,0,0,0.04); padding: 0.1rem 0.35rem;
  border-radius: 2px;
}
.db-score-clean { color: var(--jade); }
.db-score-assisted { color: var(--gold); }
.db-score-learning { color: var(--rose); }
.db-test-date { font-size: 0.6rem; color: var(--dim); opacity: 0.7; }

/* ── EMPTY STATE ── */
.db-empty {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim); padding: 0.6rem 0;
}

/* ── KUNGFU CARD ── */
.db-kungfu-header {
  display: flex; align-items: center; gap: 0.4rem;
  margin-bottom: 0.6rem;
}
.db-kungfu-title {
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--rose); flex: 1;
}
.db-kungfu-link {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  color: var(--dim); text-decoration: none;
  transition: color 0.12s;
}
.db-kungfu-link:hover { color: var(--accent); }
</style>
</head>
<body>
@include('partials.lexicon._site-header', ['backUrl' => '/lexicon', 'backLabel' => 'Lexicon'])

<main class="db-main">

  {{-- ── Shifu Daily Message ───────────────────────────────────────── --}}
  <section class="db-shifu" id="dbShifu">
    <div class="db-shifu-header">
      <span class="db-shifu-emoji" id="dbShifuEmoji">{{ $persona['emoji'] ?? '🐉' }}</span>
      <span class="db-shifu-label">師父 says</span>
    </div>

    <div id="dbShifuMsg">
      @if ($todayMessage)
        <p class="db-shifu-msg">{{ $todayMessage->message_text }}</p>
      @else
        <p class="db-shifu-loading" id="dbShifuLoading">Consulting 師父...</p>
      @endif
    </div>

    <div class="db-feedback" id="dbFeedback" style="{{ $todayMessage ? '' : 'display:none' }}">
      <button class="db-feedback-btn {{ $todayMessage?->feedback === 'up' ? 'active used' : '' }}" id="dbFbUp"
              onclick="dbFeedback('up')" {{ $todayMessage?->feedback ? 'disabled' : '' }}>👍</button>
      <button class="db-feedback-btn {{ $todayMessage?->feedback === 'down' ? 'active used' : '' }}" id="dbFbDown"
              onclick="dbFeedback('down')" {{ $todayMessage?->feedback ? 'disabled' : '' }}>👎</button>
    </div>
  </section>

  {{-- ── Days Since Last Active ────────────────────────────────────── --}}
  <section class="db-streak">
    @if ($daysSince === null)
      Welcome to 流動! This is the beginning of your journey.
    @elseif ($daysSince === 0)
      You're here again today — 好棒!
    @elseif ($daysSince === 1)
      Welcome back! You were here yesterday.
    @elseif ($daysSince <= 3)
      Welcome back! It's been {{ $daysSince }} days since you last checked in.
    @elseif ($daysSince <= 7)
      It's been {{ $daysSince }} days — glad to see you again!
    @else
      It's been {{ $daysSince }} days since your last visit. Welcome back!
    @endif
  </section>

  {{-- ── Recently Learned + Needs Attention ────────────────────────── --}}
  <div class="db-word-grid">

    {{-- Recently Learned --}}
    <div class="db-card">
      <div class="db-card-header">
        <div class="db-card-title">Recently Learned</div>
        <div style="position:relative">
          <button class="db-count-btn" onclick="dbToggleCount(this, 'learned')">{{ $widgetCounts['learned'] }} ▾</button>
          <div class="db-count-dropdown" data-key="dashboard_learned_count">
            @foreach ([5, 10, 15, 20] as $n)
              <button class="db-count-option {{ $widgetCounts['learned'] === $n ? 'active' : '' }}" onclick="dbSetCount('dashboard_learned_count', {{ $n }}, this)">{{ $n }}</button>
            @endforeach
          </div>
        </div>
      </div>
      @if (empty($recentLearned))
        <p class="db-empty">No words fully learned yet. Keep practicing!</p>
      @else
        <ul class="db-word-list">
          @foreach ($recentLearned as $w)
            <a href="{{ route('lexicon.show', $w['smart_id']) }}" class="db-word-item">
              <span class="db-hanzi">{{ $w['traditional'] }}</span>
              <span class="db-pinyin">{{ PinyinHelper::toMarked($w['pinyin']) }}</span>
              <span class="db-date">{{ \Carbon\Carbon::parse($w['learned_at'])->format('M j') }}</span>
            </a>
          @endforeach
        </ul>
      @endif
    </div>

    {{-- Needs Attention --}}
    <div class="db-card">
      <div class="db-card-header">
        <div class="db-card-title">Needs Attention</div>
        <div style="position:relative">
          <button class="db-count-btn" onclick="dbToggleCount(this, 'attention')">{{ $widgetCounts['attention'] }} ▾</button>
          <div class="db-count-dropdown" data-key="dashboard_attention_count">
            @foreach ([5, 10, 15, 20] as $n)
              <button class="db-count-option {{ $widgetCounts['attention'] === $n ? 'active' : '' }}" onclick="dbSetCount('dashboard_attention_count', {{ $n }}, this)">{{ $n }}</button>
            @endforeach
          </div>
        </div>
      </div>
      @if (empty($needsAttention))
        <p class="db-empty">All saved words are fully learned!</p>
      @else
        <ul class="db-word-list">
          @foreach ($needsAttention as $w)
            <a href="{{ route('lexicon.show', $w['smart_id']) }}" class="db-word-item">
              <span class="db-hanzi">{{ $w['traditional'] }}</span>
              <span class="db-pinyin">{{ PinyinHelper::toMarked($w['pinyin']) }}</span>
              <span class="db-dots">
                <span class="db-dot {{ $w['pinyin_passed'] ? 'pass' : '' }}" title="Pinyin"></span>
                <span class="db-dot {{ $w['def_passed'] ? 'pass' : '' }}" title="Definition"></span>
                <span class="db-dot {{ $w['usage_passed'] ? 'pass' : '' }}" title="Usage"></span>
              </span>
            </a>
          @endforeach
        </ul>
      @endif
    </div>

  </div>

  {{-- ── 需功夫 Struggling Words ───────────────────────────────────── --}}
  <section class="db-card" style="margin-bottom: 1rem;">
    <div class="db-kungfu-header">
      <div class="db-kungfu-title">需功夫 Needs Kung Fu</div>
      <a href="{{ route('my-words') }}" class="db-kungfu-link">My Words →</a>
      <div style="position:relative">
        <button class="db-count-btn" onclick="dbToggleCount(this, 'kungfu')">{{ $widgetCounts['kungfu'] }} ▾</button>
        <div class="db-count-dropdown" data-key="dashboard_kungfu_count">
          @foreach ([5, 10, 15, 20] as $n)
            <button class="db-count-option {{ $widgetCounts['kungfu'] === $n ? 'active' : '' }}" onclick="dbSetCount('dashboard_kungfu_count', {{ $n }}, this)">{{ $n }}</button>
          @endforeach
        </div>
      </div>
    </div>
    @if (empty($kungfuWords))
      <p class="db-empty">No struggling words yet. Keep testing!</p>
    @else
      <ul class="db-word-list">
        @foreach ($kungfuWords as $w)
          <a href="{{ route('lexicon.show', $w['smart_id']) }}" class="db-word-item">
            <span class="db-hanzi">{{ $w['traditional'] }}</span>
            <span class="db-pinyin">{{ PinyinHelper::toMarked($w['pinyin']) }}</span>
            <span class="db-wrong">{{ $w['wrong_count'] }}x</span>
          </a>
        @endforeach
      </ul>
    @endif
  </section>

  {{-- ── Recent Tests ──────────────────────────────────────────────── --}}
  <section class="db-card" style="margin-bottom: 1rem;">
    <div class="db-card-header">
      <div class="db-card-title">Recent Tests</div>
    </div>
    @if ($recentTests->isEmpty())
      <p class="db-empty">No tests taken yet. Try testing a collection!</p>
    @else
      <ul class="db-test-list">
        @foreach ($recentTests as $test)
          <li class="db-test-item">
            <span class="db-test-collection">{{ $test->collection?->name ?? 'Deleted collection' }}</span>
            <span class="db-test-mode">{{ $test->test_mode }}</span>
            <span>
              <span class="db-score-clean">{{ $test->clean_count }}</span>
              <span style="color:var(--dim)">/</span>
              <span class="db-score-assisted">{{ $test->assisted_count }}</span>
              <span style="color:var(--dim)">/</span>
              <span class="db-score-learning">{{ $test->learning_count }}</span>
            </span>
            <span class="db-test-date">{{ $test->completed_at->format('M j') }}</span>
          </li>
        @endforeach
      </ul>
    @endif
  </section>

</main>

@include('partials.lexicon._site-footer')

<script>
function dbCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

// ── Generate daily message if not yet loaded ───────────────────────
@if (! $todayMessage)
(function() {
  fetch('/api/dashboard/daily-message', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': dbCsrf(),
      'Accept': 'application/json',
    },
  })
  .then(r => r.json())
  .then(data => {
    var msgEl = document.getElementById('dbShifuMsg');
    var fbEl  = document.getElementById('dbFeedback');

    if (data.error) {
      msgEl.innerHTML = '<p class="db-shifu-error">' + data.error + '</p>';
      return;
    }

    if (data.emoji) {
      document.getElementById('dbShifuEmoji').textContent = data.emoji;
    }

    msgEl.innerHTML = '<p class="db-shifu-msg"></p>';
    msgEl.querySelector('.db-shifu-msg').textContent = data.message_text;
    fbEl.style.display = 'flex';
  })
  .catch(function() {
    document.getElementById('dbShifuMsg').innerHTML =
      '<p class="db-shifu-error">Could not reach 師父 right now.</p>';
  });
})();
@endif

// ── Feedback ───────────────────────────────────────────────────────
function dbFeedback(vote) {
  var upBtn   = document.getElementById('dbFbUp');
  var downBtn = document.getElementById('dbFbDown');

  fetch('/api/dashboard/daily-message/feedback', {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': dbCsrf(),
      'Accept': 'application/json',
    },
    body: JSON.stringify({ feedback: vote }),
  })
  .then(function() {
    if (vote === 'up') {
      upBtn.classList.add('active', 'used');
      downBtn.classList.add('used');
    } else {
      downBtn.classList.add('active', 'used');
      upBtn.classList.add('used');
    }
    upBtn.disabled = true;
    downBtn.disabled = true;
  });
}

// ── Count selector ────────────────────────────────────────────────
function dbToggleCount(btn, key) {
  var dropdown = btn.nextElementSibling || btn.parentElement.querySelector('.db-count-dropdown');
  // Close all other dropdowns first
  document.querySelectorAll('.db-count-dropdown.open').forEach(function(d) {
    if (d !== dropdown) d.classList.remove('open');
  });
  dropdown.classList.toggle('open');
}

function dbSetCount(prefKey, value, optionBtn) {
  var dropdown = optionBtn.closest('.db-count-dropdown');
  var trigger  = dropdown.previousElementSibling;

  // Update active state
  dropdown.querySelectorAll('.db-count-option').forEach(function(b) { b.classList.remove('active'); });
  optionBtn.classList.add('active');

  // Update trigger label
  trigger.textContent = value + ' ▾';

  // Close dropdown
  dropdown.classList.remove('open');

  // Save preference
  var body = {};
  body[prefKey] = value;

  fetch('/api/preferences', {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': dbCsrf(),
      'Accept': 'application/json',
    },
    body: JSON.stringify(body),
  }).then(function() {
    // Reload to reflect new count
    window.location.reload();
  });
}

// Close count dropdowns when clicking outside
document.addEventListener('click', function(e) {
  if (!e.target.closest('.db-count-btn') && !e.target.closest('.db-count-dropdown')) {
    document.querySelectorAll('.db-count-dropdown.open').forEach(function(d) {
      d.classList.remove('open');
    });
  }
});
</script>
</body>
</html>
