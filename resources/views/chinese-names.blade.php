<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Chinese Names — 流動 Living Lexicon</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@300;400;600;700&family=DM+Mono:ital,wght@0,300;0,400;1,300&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
@include('partials.lexicon._foundations')
<style>
.cn-main { max-width: 580px; margin: 0 auto; padding: 1.5rem 1rem 3rem; }
.cn-intro { text-align: center; margin-bottom: 2rem; }
.cn-intro-title {
  font-family: 'Cormorant Garamond', serif; font-size: 1.6rem;
  color: var(--ink); margin-bottom: 0.5rem;
}
.cn-intro-desc {
  font-family: 'Cormorant Garamond', serif; font-size: 0.95rem;
  color: var(--dim); line-height: 1.6; max-width: 420px; margin: 0 auto;
}

/* Form */
.cn-form { margin-bottom: 2rem; }
.cn-label {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  letter-spacing: 0.08em; text-transform: uppercase;
  color: var(--dim); margin-bottom: 0.3rem;
}
.cn-input {
  font-family: 'DM Mono', monospace; font-size: 0.85rem;
  padding: 0.5rem 0.7rem; border: 1px solid var(--border);
  border-radius: 2px; width: 100%; background: var(--surface);
  color: var(--text); outline: none; margin-bottom: 1rem;
}
.cn-input:focus { border-color: var(--accent); }
.cn-textarea {
  font-family: 'Cormorant Garamond', serif; font-size: 0.95rem;
  padding: 0.6rem 0.7rem; border: 1px solid var(--border);
  border-radius: 2px; width: 100%; min-height: 80px; resize: vertical;
  background: var(--surface); color: var(--text); outline: none;
  line-height: 1.5; margin-bottom: 1rem;
}
.cn-textarea:focus { border-color: var(--accent); }
.cn-submit {
  font-family: 'DM Mono', monospace; font-size: 0.75rem;
  letter-spacing: 0.06em;
  padding: 0.5rem 1.5rem; border: none; border-radius: 2px;
  background: var(--accent); color: white; cursor: pointer;
  transition: opacity 0.15s; width: 100%;
}
.cn-submit:hover { opacity: 0.85; }
.cn-submit:disabled { opacity: 0.4; cursor: default; }

/* Results */
.cn-results { display: none; }
.cn-results-title {
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--accent); margin-bottom: 1rem;
}
.cn-name-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 4px; padding: 1.2rem; margin-bottom: 1rem;
  transition: border-color 0.15s;
}
.cn-name-card:hover { border-color: var(--accent); }
.cn-name-chars {
  font-family: 'Noto Serif TC', serif; font-size: 2.5rem;
  font-weight: 600; color: var(--ink); line-height: 1.2;
}
.cn-name-pinyin {
  font-family: 'Cormorant Garamond', serif; font-size: 1.1rem;
  color: var(--accent); font-style: italic; margin-top: 0.2rem;
}
.cn-name-meaning {
  font-family: 'Cormorant Garamond', serif; font-size: 0.9rem;
  color: var(--dim); line-height: 1.5; margin-top: 0.6rem;
}
.cn-choose-btn {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  letter-spacing: 0.06em;
  padding: 0.35rem 1rem; border: 1px solid var(--accent);
  border-radius: 2px; background: transparent;
  color: var(--accent); cursor: pointer; margin-top: 0.8rem;
  transition: all 0.15s;
}
.cn-choose-btn:hover { background: var(--accent); color: white; }

/* Confirmation */
.cn-confirmed {
  display: none; text-align: center; padding: 2rem 0;
}
.cn-confirmed-name {
  font-family: 'Noto Serif TC', serif; font-size: 3rem;
  font-weight: 600; color: var(--ink);
}
.cn-confirmed-msg {
  font-family: 'Cormorant Garamond', serif; font-size: 1rem;
  color: var(--dim); margin-top: 0.5rem;
}

/* Existing name display */
.cn-existing { text-align: center; margin-bottom: 2rem; }
.cn-existing-name {
  font-family: 'Noto Serif TC', serif; font-size: 3rem;
  font-weight: 600; color: var(--ink);
}
.cn-existing-pinyin {
  font-family: 'Cormorant Garamond', serif; font-size: 1.2rem;
  color: var(--accent); font-style: italic;
}
.cn-existing-meaning {
  font-family: 'Cormorant Garamond', serif; font-size: 0.9rem;
  color: var(--dim); line-height: 1.5; margin-top: 0.5rem;
  max-width: 400px; margin-left: auto; margin-right: auto;
}
.cn-try-again {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--dim); margin-top: 1rem; display: inline-block;
  text-decoration: none;
}
.cn-try-again:hover { color: var(--accent); }

.cn-loading {
  font-family: 'Cormorant Garamond', serif; font-size: 1rem;
  color: var(--dim); font-style: italic; text-align: center;
  padding: 2rem 0;
}
</style>
</head>
<body>
@include('partials.lexicon._site-header')

<div class="cn-main">

  <div class="cn-intro">
    <h1 class="cn-intro-title">Your Chinese Name</h1>
    <p class="cn-intro-desc">師父 will craft a Chinese name for you — matching the sounds of your name with characters that carry the meaning you choose.</p>
  </div>

  @auth
    @if(Auth::user()->chinese_name)
      {{-- Show existing name --}}
      <div class="cn-existing" id="cnExisting">
        <div class="cn-existing-name">{{ Auth::user()->chinese_name }}</div>
        @if(Auth::user()->chinese_name_pinyin)
          <div class="cn-existing-pinyin">{{ Auth::user()->chinese_name_pinyin }}</div>
        @endif
        @if(Auth::user()->chinese_name_meaning)
          <div class="cn-existing-meaning">{{ Auth::user()->chinese_name_meaning }}</div>
        @endif
        <a href="#" class="cn-try-again" onclick="document.getElementById('cnExisting').style.display='none';document.getElementById('cnFormWrap').style.display='block';return false;">Try a different name →</a>
      </div>
    @endif

    <div id="cnFormWrap" style="{{ Auth::user()->chinese_name ? 'display:none' : '' }}">
      <div class="cn-form">
        <div class="cn-label">Your Name</div>
        <input type="text" id="cnPllName" class="cn-input" value="{{ Auth::user()->pll_name ?? Auth::user()->name }}" placeholder="Your full name in English">

        <div class="cn-label">How would you like your name to feel?</div>
        <textarea id="cnGuidance" class="cn-textarea" placeholder="Example: I want a name that feels like a warrior monk — strong but wise and calm."></textarea>

        <button class="cn-submit" id="cnSubmitBtn" onclick="generateNames()">Ask 師父</button>
      </div>

      <div class="cn-loading" id="cnLoading" style="display:none">師父 is crafting your name…</div>

      <div class="cn-results" id="cnResults">
        <div class="cn-results-title">師父 suggests</div>
        <div id="cnNameCards"></div>
      </div>
    </div>

    <div class="cn-confirmed" id="cnConfirmed">
      <div class="cn-confirmed-name" id="cnConfirmedName"></div>
      <div class="cn-confirmed-msg">Your Chinese name has been saved to your profile.</div>
      <a href="{{ route('profile') }}" class="cn-try-again" style="margin-top:1rem">View Profile →</a>
    </div>
  @else
    <div style="text-align:center;padding:2rem 0;">
      <p style="font-family:'Cormorant Garamond',serif;font-size:1rem;color:var(--dim);margin-bottom:1rem;">Log in to get your Chinese name from 師父.</p>
      <a href="{{ route('login') }}" style="font-family:'DM Mono',monospace;font-size:0.75rem;color:var(--accent);">Log in →</a>
    </div>
  @endauth

</div>

@auth
<script>
function cnCsrf() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }

let cnEngagementId = null;

async function generateNames() {
  const pllName = document.getElementById('cnPllName').value.trim();
  const guidance = document.getElementById('cnGuidance').value.trim();
  const btn = document.getElementById('cnSubmitBtn');
  const loading = document.getElementById('cnLoading');
  const results = document.getElementById('cnResults');
  const cards = document.getElementById('cnNameCards');

  if (!pllName || !guidance) {
    alert('Please enter your name and describe how you want it to feel.');
    return;
  }

  btn.disabled = true;
  loading.style.display = 'block';
  results.style.display = 'none';

  try {
    const res = await fetch('/api/chinese-names/generate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cnCsrf(), 'Accept': 'application/json' },
      body: JSON.stringify({ pll_name: pllName, guidance: guidance }),
    });

    const data = await res.json();

    if (data.error || !data.names) {
      loading.innerHTML = '<span style="color:var(--rose)">Something went wrong. Please try again.</span>';
      btn.disabled = false;
      return;
    }

    cnEngagementId = data.engagement_id;

    cards.innerHTML = data.names.map((n, i) => `
      <div class="cn-name-card">
        <div class="cn-name-chars">${esc(n.chinese)}</div>
        <div class="cn-name-pinyin">${esc(n.pinyin)}</div>
        <div class="cn-name-meaning">${esc(n.meaning)}</div>
        <button class="cn-choose-btn" onclick="chooseName(${i})">Choose this name</button>
      </div>
    `).join('');

    // Store for choosing
    window._cnNames = data.names;

    loading.style.display = 'none';
    results.style.display = 'block';
    btn.disabled = false;
    btn.textContent = 'Ask again';

  } catch (e) {
    loading.innerHTML = '<span style="color:var(--rose)">Connection error. Please try again.</span>';
    btn.disabled = false;
  }
}

async function chooseName(idx) {
  const name = window._cnNames[idx];
  if (!name) return;

  const pllName = document.getElementById('cnPllName').value.trim();

  try {
    const res = await fetch('/api/chinese-names/choose', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cnCsrf(), 'Accept': 'application/json' },
      body: JSON.stringify({
        chinese_name: name.chinese,
        pinyin: name.pinyin,
        meaning: name.meaning,
        pll_name: pllName,
        engagement_id: cnEngagementId,
      }),
    });

    const data = await res.json();
    if (data.ok) {
      document.getElementById('cnFormWrap').style.display = 'none';
      document.getElementById('cnConfirmed').style.display = 'block';
      document.getElementById('cnConfirmedName').textContent = name.chinese;

      // Update user menu name
      const menuName = document.querySelector('.user-menu-name');
      if (menuName) menuName.childNodes[0].textContent = name.chinese + ' ';
    }
  } catch (e) {
    alert('Error saving name. Please try again.');
  }
}

function esc(s) {
  const d = document.createElement('div');
  d.textContent = s || '';
  return d.innerHTML;
}
</script>
@endauth

@include('partials.lexicon._site-footer')
</body>
</html>
