<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Profile — 流動 Living Lexicon</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@300;400;600;700&family=DM+Mono:ital,wght@0,300;0,400;1,300&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
@include('partials.lexicon._foundations')
<style>
.pf-main { max-width: 540px; margin: 0 auto; padding: 1.5rem 1rem 3rem; }
.pf-section { margin-bottom: 2rem; }
.pf-section-title {
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--accent); margin-bottom: 0.8rem;
}
.pf-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 4px; padding: 1.2rem;
}
.pf-field { margin-bottom: 1rem; }
.pf-field:last-child { margin-bottom: 0; }
.pf-label {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  letter-spacing: 0.08em; text-transform: uppercase;
  color: var(--dim); margin-bottom: 0.3rem;
}
.pf-value {
  font-family: 'Cormorant Garamond', serif; font-size: 1.1rem;
  color: var(--ink); line-height: 1.5;
}
.pf-value-cn {
  font-family: 'Noto Serif TC', serif; font-size: 2rem;
  color: var(--ink); font-weight: 600;
}
.pf-input {
  font-family: 'DM Mono', monospace; font-size: 0.8rem;
  padding: 0.4rem 0.6rem; border: 1px solid var(--border);
  border-radius: 2px; width: 100%; background: var(--surface);
  color: var(--text); outline: none;
}
.pf-input:focus { border-color: var(--accent); }
.pf-btn {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  letter-spacing: 0.06em;
  padding: 0.35rem 0.8rem; border: 1px solid var(--border);
  border-radius: 2px; background: var(--surface);
  color: var(--accent); cursor: pointer;
  transition: all 0.15s;
}
.pf-btn:hover { background: var(--accent); color: white; border-color: var(--accent); }
.pf-btn-primary {
  background: var(--accent); color: white; border-color: var(--accent);
}
.pf-btn-primary:hover { opacity: 0.85; }
.pf-name-link {
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  color: var(--accent); text-decoration: none;
}
.pf-name-link:hover { text-decoration: underline; }
.pf-meaning {
  font-family: 'Cormorant Garamond', serif; font-size: 0.9rem;
  color: var(--dim); font-style: italic; line-height: 1.5;
  margin-top: 0.5rem;
}
.pf-pinyin {
  font-family: 'Cormorant Garamond', serif; font-size: 1rem;
  color: var(--accent); font-style: italic;
}
.pf-inline { display: flex; gap: 0.5rem; align-items: center; }
.pf-msg {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--jade); margin-top: 0.3rem; display: none;
}
</style>
</head>
<body>
@include('partials.lexicon._site-header', ['backUrl' => route('lexicon.index'), 'backLabel' => 'Lexicon'])

<div class="pf-main">

  {{-- Chinese Name --}}
  <div class="pf-section">
    <div class="pf-section-title">Chinese Name</div>
    <div class="pf-card">
      @if($user->chinese_name)
        <div class="pf-field">
          <div class="pf-value-cn">{{ $user->chinese_name }}</div>
          @if($user->chinese_name_pinyin)
            <div class="pf-pinyin">{{ $user->chinese_name_pinyin }}</div>
          @endif
          @if($user->chinese_name_meaning)
            <div class="pf-meaning">{{ $user->chinese_name_meaning }}</div>
          @endif
        </div>
      @else
        <div class="pf-field">
          <p class="pf-value" style="color:var(--dim)">No Chinese name yet.</p>
          <a href="{{ route('chinese-names') }}" class="pf-name-link" style="margin-top:0.5rem;display:inline-block;">Get your Chinese name from 師父 →</a>
        </div>
      @endif
    </div>
  </div>

  {{-- PLL Name --}}
  <div class="pf-section">
    <div class="pf-section-title">Name (English)</div>
    <div class="pf-card">
      <div class="pf-field">
        <div class="pf-label">Display Name</div>
        <div class="pf-value">{{ $user->name }}</div>
      </div>
      <div class="pf-field">
        <div class="pf-label">Full Name (PLL)</div>
        <div class="pf-inline">
          <input type="text" id="pllNameInput" class="pf-input" value="{{ $user->pll_name ?? '' }}" placeholder="Your full name in your primary language">
          <button class="pf-btn" onclick="savePllName()">Save</button>
        </div>
        <div class="pf-msg" id="pllMsg">Saved</div>
      </div>
    </div>
  </div>

  {{-- Account --}}
  <div class="pf-section">
    <div class="pf-section-title">Account</div>
    <div class="pf-card">
      <div class="pf-field">
        <div class="pf-label">Email</div>
        <div class="pf-value" style="font-size:0.9rem">{{ $user->email }}</div>
      </div>
      <div class="pf-field">
        <div class="pf-label">Fluency Level</div>
        <div class="pf-value" style="font-size:0.9rem">{{ ucfirst($user->fluency_level ?? 'Not set') }}</div>
      </div>
      <div class="pf-field">
        <div class="pf-label">Role</div>
        <div class="pf-value" style="font-size:0.9rem">{{ ucfirst($user->role) }}</div>
      </div>
    </div>
  </div>

</div>

<script>
function pfCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

async function savePllName() {
  const input = document.getElementById('pllNameInput');
  const msg = document.getElementById('pllMsg');
  const name = input.value.trim();
  if (!name) return;

  try {
    const res = await fetch('/profile/pll-name', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': pfCsrf(), 'Accept': 'application/json' },
      body: JSON.stringify({ pll_name: name }),
    });
    if (res.ok) {
      msg.style.display = 'block';
      msg.textContent = 'Saved';
      setTimeout(() => msg.style.display = 'none', 2000);
    }
  } catch (e) {
    msg.style.display = 'block';
    msg.textContent = 'Error saving';
    msg.style.color = 'var(--rose)';
  }
}
</script>

@include('partials.lexicon._site-footer')
</body>
</html>
