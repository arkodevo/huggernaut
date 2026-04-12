<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Dispute {{ $sense['traditional'] }} — 流動 Living Lexicon</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@300;400;600;700&family=DM+Mono:ital,wght@0,300;0,400;1,300&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
@include('partials.lexicon._foundations')
<style>
.dc-main {
  max-width: 680px; margin: 0 auto; padding: 1.25rem 1rem 3rem;
}

/* ── HEADER ── */
.dc-header {
  margin-bottom: 1.1rem;
  padding-bottom: 0.9rem;
  border-bottom: 1px solid var(--border);
}
.dc-title {
  font-family: 'DM Mono', monospace; font-size: 1.05rem;
  color: var(--rose); letter-spacing: 0.03em;
  margin: 0 0 0.35rem;
}
.dc-sub {
  font-family: 'Cormorant Garamond', serif; font-size: 1rem;
  color: var(--dim); line-height: 1.5;
  margin: 0;
  font-style: italic;
}

/* ── SENSE CARD (what's being disputed) ── */
.dc-sense-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 4px;
  padding: 1rem 1.1rem;
  margin-bottom: 1.3rem;
}
.dc-sense-head {
  display: flex; align-items: baseline; gap: 0.75rem;
  margin-bottom: 0.45rem;
}
.dc-sense-char {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', serif;
  font-size: 2.2rem; color: var(--ink);
  line-height: 1;
}
.dc-sense-meta {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim); letter-spacing: 0.03em;
}
.dc-sense-meta-pos { color: var(--accent); margin-left: 0.5rem; }
.dc-sense-back {
  margin-left: auto;
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--dim); text-decoration: none;
}
.dc-sense-back:hover { color: var(--accent); }

/* ── FORM ── */
.dc-form-intro {
  font-family: 'Cormorant Garamond', serif; font-size: 0.98rem;
  color: var(--ink); line-height: 1.55;
  margin: 0 0 1rem;
}
.dc-form-intro strong { color: var(--rose); }

.dc-section {
  margin-bottom: 1.4rem;
}
.dc-section-label {
  font-family: 'DM Mono', monospace;
  font-size: 0.6rem; letter-spacing: 0.1em; text-transform: uppercase;
  color: var(--dim); opacity: 0.75;
  margin: 0 0 0.55rem;
}

/* ── FIELD CHECKBOX ROW ── */
.dc-field {
  display: flex; align-items: flex-start; gap: 0.65rem;
  padding: 0.65rem 0.8rem;
  border: 1px solid var(--border);
  border-radius: 3px;
  margin-bottom: 0.4rem;
  transition: border-color 0.15s, background 0.15s;
  cursor: pointer;
}
.dc-field:hover {
  border-color: rgba(184, 48, 80, 0.3);
  background: rgba(184, 48, 80, 0.02);
}
.dc-field.checked {
  border-color: var(--rose);
  background: rgba(184, 48, 80, 0.05);
}
.dc-field-checkbox {
  margin-top: 0.2rem;
  accent-color: var(--rose);
  cursor: pointer;
  flex-shrink: 0;
}
.dc-field-body { flex: 1; min-width: 0; }
.dc-field-label {
  font-family: 'DM Mono', monospace; font-size: 0.62rem;
  color: var(--dim); letter-spacing: 0.06em; text-transform: uppercase;
  margin-bottom: 0.15rem;
}
.dc-field-content {
  font-family: 'Cormorant Garamond', serif; font-size: 0.98rem;
  color: var(--ink); line-height: 1.5;
}
.dc-field-content-zh {
  font-family: 'Noto Serif TC', serif; font-size: 1rem;
  color: var(--ink);
}
.dc-field-content-en {
  font-family: 'Cormorant Garamond', serif; font-size: 0.92rem;
  color: var(--dim); margin-top: 0.15rem;
  font-style: italic;
}

/* Attribute chip variant — denser, inline-ish */
.dc-field.is-chip { align-items: center; padding: 0.5rem 0.8rem; }
.dc-field.is-chip .dc-field-content {
  display: flex; align-items: baseline; gap: 0.5rem;
  font-size: 0.85rem;
}
.dc-field.is-chip .dc-field-content strong {
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  color: var(--accent); text-transform: uppercase; letter-spacing: 0.05em;
  font-weight: 600;
}

/* ── RATIONALE SECTION ── */
.dc-rationale {
  margin-top: 1.5rem;
  padding-top: 1.2rem;
  border-top: 1px dashed var(--border);
  display: none;
}
.dc-rationale.visible { display: block; }
.dc-rationale textarea {
  width: 100%;
  min-height: 140px;
  box-sizing: border-box;
  font-family: 'Cormorant Garamond', serif; font-size: 1rem;
  line-height: 1.55;
  color: var(--ink);
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 3px;
  padding: 0.75rem 0.9rem;
  outline: none;
  resize: vertical;
  transition: border-color 0.15s;
}
.dc-rationale textarea:focus { border-color: var(--rose); }
.dc-rationale-hint {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  color: var(--dim); letter-spacing: 0.03em;
  margin-top: 0.35rem; opacity: 0.75;
}

/* ── ANONYMITY TOGGLE ── */
.dc-anon {
  display: flex; align-items: center; gap: 0.6rem;
  margin-top: 1.3rem;
  padding: 0.75rem 0.9rem;
  background: var(--surface2);
  border-radius: 3px;
}
.dc-anon input {
  accent-color: var(--rose);
  cursor: pointer;
}
.dc-anon-label {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--ink); letter-spacing: 0.02em;
  cursor: pointer;
}
.dc-anon-hint {
  font-family: 'Cormorant Garamond', serif; font-size: 0.85rem;
  color: var(--dim); font-style: italic;
  margin-top: 0.2rem;
  padding: 0 0.9rem;
}

/* ── ACTIONS ── */
.dc-actions {
  margin-top: 1.75rem;
  display: flex; gap: 0.6rem; align-items: center;
}
.dc-submit {
  font-family: 'DM Mono', monospace; font-size: 0.75rem;
  letter-spacing: 0.04em;
  color: #fff; background: var(--rose);
  border: 1px solid var(--rose);
  border-radius: 2px;
  padding: 0.55rem 1.4rem;
  cursor: pointer;
  transition: opacity 0.15s;
}
.dc-submit:hover { opacity: 0.88; }
.dc-submit:disabled {
  opacity: 0.4; cursor: not-allowed;
}
.dc-cancel {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim); text-decoration: none;
  padding: 0.55rem 0.9rem;
}
.dc-cancel:hover { color: var(--accent); }

.dc-empty-hint {
  font-family: 'Cormorant Garamond', serif; font-size: 0.9rem;
  color: var(--dim); font-style: italic;
  padding: 1rem 0; text-align: center;
}
</style>
</head>
<body>
<script>
  window.__AUTH = @json($authUser);
</script>

@include('partials.lexicon._site-header', [
  'backUrl'   => route('lexicon.show', $sense['smartId']),
  'backLabel' => $sense['traditional'],
])

<div class="dc-main">
  <div class="dc-header">
    <h1 class="dc-title">Dispute a sense</h1>
    <p class="dc-sub">Flag which fields you believe need review, and explain your reasoning. Once filed, your dispute enters the queue for 三人行 adjudication.</p>
  </div>

  {{-- ── SENSE BEING DISPUTED ── --}}
  <div class="dc-sense-card">
    <div class="dc-sense-head">
      <span class="dc-sense-char">{{ $sense['traditional'] }}</span>
      <div>
        @if ($sense['pinyin'])
          <span class="dc-sense-meta">{{ $sense['pinyin'] }}</span>
        @endif
        @if ($sense['posAbbr'])
          <span class="dc-sense-meta-pos">· {{ $sense['posAbbr'] }}</span>
        @endif
      </div>
      <a class="dc-sense-back" href="{{ route('lexicon.show', $sense['smartId']) }}">← back to sense</a>
    </div>
    <div style="font-family: 'Cormorant Garamond', serif; font-size: 1rem; color: var(--ink); line-height: 1.5;">
      {{ $sense['definition'] }}
    </div>
  </div>

  <p class="dc-form-intro">
    Check every field you wish to dispute. A rationale becomes required once you check <strong>at least one</strong>. Cite evidence — a dictionary, a native informant, a usage example — so 三人行 can weigh your case fairly.
  </p>

  <form id="disputeForm" method="POST" action="{{ route('disputations.store') }}">
    @csrf
    <input type="hidden" name="word_sense_id" value="{{ $sense['id'] }}">

    {{-- ── CORE CONTENT ── --}}
    <div class="dc-section">
      <div class="dc-section-label">Core content</div>

      @if ($sense['definition'])
        <label class="dc-field">
          <input type="checkbox" class="dc-field-checkbox" name="fields_disputed[]" value="definition">
          <div class="dc-field-body">
            <div class="dc-field-label">Definition</div>
            <div class="dc-field-content">{{ $sense['definition'] }}</div>
          </div>
        </label>
      @endif

      @if (! empty($sense['formula']))
        <label class="dc-field">
          <input type="checkbox" class="dc-field-checkbox" name="fields_disputed[]" value="formula">
          <div class="dc-field-body">
            <div class="dc-field-label">Formula</div>
            <div class="dc-field-content dc-field-content-zh">{{ $sense['formula'] }}</div>
          </div>
        </label>
      @endif

      @if (! empty($sense['usage_note']))
        <label class="dc-field">
          <input type="checkbox" class="dc-field-checkbox" name="fields_disputed[]" value="usage_note">
          <div class="dc-field-body">
            <div class="dc-field-label">Usage note</div>
            <div class="dc-field-content dc-field-content-zh">{{ $sense['usage_note'] }}</div>
          </div>
        </label>
      @endif

      @if (! empty($sense['learner_traps']))
        <label class="dc-field">
          <input type="checkbox" class="dc-field-checkbox" name="fields_disputed[]" value="learner_traps">
          <div class="dc-field-body">
            <div class="dc-field-label">Learner traps</div>
            <div class="dc-field-content dc-field-content-zh">{{ $sense['learner_traps'] }}</div>
          </div>
        </label>
      @endif
    </div>

    {{-- ── EXAMPLES ── --}}
    @if (count($sense['examples']))
      <div class="dc-section">
        <div class="dc-section-label">Examples</div>
        @foreach ($sense['examples'] as $ex)
          <label class="dc-field">
            <input type="checkbox" class="dc-field-checkbox" name="fields_disputed[]" value="{{ $ex['key'] }}">
            <div class="dc-field-body">
              <div class="dc-field-label">Example {{ $loop->iteration }}</div>
              <div class="dc-field-content dc-field-content-zh">{{ $ex['chinese'] }}</div>
              @if ($ex['english'])
                <div class="dc-field-content-en">{{ $ex['english'] }}</div>
              @endif
            </div>
          </label>
        @endforeach
      </div>
    @endif

    {{-- ── ATTRIBUTES ── --}}
    @if (count($sense['attributes']))
      <div class="dc-section">
        <div class="dc-section-label">Attributes</div>
        @foreach ($sense['attributes'] as $attr)
          <label class="dc-field is-chip">
            <input type="checkbox" class="dc-field-checkbox" name="fields_disputed[]" value="{{ $attr['key'] }}">
            <div class="dc-field-body">
              <div class="dc-field-content">
                <strong>{{ $attr['label'] }}</strong>
                <span>{{ $attr['value'] }}</span>
              </div>
            </div>
          </label>
        @endforeach
      </div>
    @endif

    {{-- ── DOMAINS ── --}}
    @if (count($sense['domains']))
      <div class="dc-section">
        <div class="dc-section-label">Domains</div>
        @foreach ($sense['domains'] as $dom)
          <label class="dc-field is-chip">
            <input type="checkbox" class="dc-field-checkbox" name="fields_disputed[]" value="{{ $dom['key'] }}">
            <div class="dc-field-body">
              <div class="dc-field-content">
                <strong>Domain</strong>
                <span>{{ $dom['label'] }}</span>
              </div>
            </div>
          </label>
        @endforeach
      </div>
    @endif

    {{-- ── RATIONALE (appears after first check) ── --}}
    <div class="dc-rationale" id="rationaleSection">
      <div class="dc-section-label">Your rationale</div>
      <textarea name="rationale"
                id="rationaleTextarea"
                placeholder="Explain why the checked fields need review. Cite evidence where you can — a reputable source, a native informant's counterexample, a usage that contradicts the current entry."></textarea>
      <div class="dc-rationale-hint">Minimum 10 characters. Aim for specifics, not impressions.</div>
    </div>

    {{-- ── ANONYMITY ── --}}
    <div class="dc-anon">
      <input type="hidden" name="is_anonymous" value="0">
      <input type="checkbox"
             id="isAnonymous"
             name="is_anonymous"
             value="1"
             {{ $user->default_disputes_anonymous ? 'checked' : '' }}>
      <label class="dc-anon-label" for="isAnonymous">
        Submit anonymously (hide my name on the community feed)
      </label>
    </div>
    <div class="dc-anon-hint">
      Defaulted from your profile preference. 三人行 still sees your identity during adjudication — anonymity only affects how this dispute appears to other learners.
    </div>

    {{-- ── ACTIONS ── --}}
    <div class="dc-actions">
      <button type="submit" class="dc-submit" id="dcSubmit" disabled>File disputation</button>
      <a class="dc-cancel" href="{{ route('lexicon.show', $sense['smartId']) }}">Cancel</a>
    </div>
  </form>
</div>

<script>
(function() {
  var form       = document.getElementById('disputeForm');
  var checkboxes = form.querySelectorAll('.dc-field-checkbox');
  var rationale  = document.getElementById('rationaleSection');
  var textarea   = document.getElementById('rationaleTextarea');
  var submit     = document.getElementById('dcSubmit');

  function updateState() {
    var anyChecked = false;
    checkboxes.forEach(function(cb) {
      var row = cb.closest('.dc-field');
      if (cb.checked) {
        row.classList.add('checked');
        anyChecked = true;
      } else {
        row.classList.remove('checked');
      }
    });

    // Rationale reveals once any field is checked
    rationale.classList.toggle('visible', anyChecked);

    // Submit is gated on: at least one field checked AND rationale >= 10 chars
    var rLen = textarea.value.trim().length;
    submit.disabled = !(anyChecked && rLen >= 10);
  }

  checkboxes.forEach(function(cb) { cb.addEventListener('change', updateState); });
  textarea.addEventListener('input', updateState);

  updateState();
})();
</script>

@include('partials.lexicon._site-footer')
</body>
</html>
