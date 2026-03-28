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

/* ── Zodiac Dial ────────────────────────────────────────── */
.zd-view-tabs {
  display: flex; gap: 0; justify-content: center; margin-bottom: 1.2rem;
}
.zd-view-tab {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  letter-spacing: 0.08em; text-transform: uppercase;
  padding: 0.35rem 0.8rem; border: 1px solid var(--border);
  background: var(--surface); color: var(--dim); cursor: pointer;
  transition: all 0.15s;
}
.zd-view-tab:first-child { border-radius: 2px 0 0 2px; }
.zd-view-tab:last-child { border-radius: 0 2px 2px 0; }
.zd-view-tab.active { background: var(--accent); color: white; border-color: var(--accent); }

.zd-dial-wrap {
  position: relative; width: 300px; height: 300px;
  margin: 0 auto 1.2rem;
}
.zd-dial {
  width: 100%; height: 100%; position: relative;
  border-radius: 50%;
  background: var(--surface);
  border: 2px solid var(--border);
}
.zd-segment {
  position: absolute; top: 50%; left: 50%;
  width: 50%; height: 2px;
  transform-origin: 0% 50%;
  cursor: pointer;
}
.zd-segment-hit {
  position: absolute; top: 50%; left: 50%;
  width: 42%; height: 0; padding: 0;
  transform-origin: 0% 50%;
  cursor: pointer; z-index: 2;
}
.zd-segment-hit::before {
  content: ''; display: block;
  width: 100%; height: 40px;
  margin-top: -20px;
}
.zd-node {
  position: absolute;
  width: 44px; height: 44px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.3rem;
  border: 2px solid var(--border);
  background: var(--surface);
  transition: all 0.25s;
  cursor: pointer; z-index: 3;
  transform: translate(-50%, -50%);
}
.zd-node:hover { transform: translate(-50%, -50%) scale(1.15); }
.zd-node.selected {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
  transform: translate(-50%, -50%) scale(1.2);
}
.zd-center {
  position: absolute; top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 60px; height: 60px;
  border-radius: 50%;
  background: var(--surface);
  border: 2px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.6rem; z-index: 4;
  pointer-events: none;
}

/* Info panel */
.zd-info {
  text-align: center; padding: 1rem 0.5rem;
  transition: opacity 0.2s;
}
.zd-info-animal {
  font-size: 2.2rem; margin-bottom: 0.3rem;
}
.zd-info-name {
  font-family: 'DM Mono', monospace; font-size: 0.85rem;
  font-weight: 600; color: var(--ink);
}
.zd-info-branch {
  font-family: 'Noto Serif TC', serif; font-size: 1.4rem;
  color: var(--accent); margin: 0.2rem 0;
}
.zd-info-archetype {
  font-family: 'Cormorant Garamond', serif; font-size: 1.1rem;
  font-style: italic; color: var(--accent); margin: 0.3rem 0;
}
.zd-info-badges {
  display: flex; gap: 0.4rem; justify-content: center;
  margin: 0.5rem 0;
}
.zd-badge {
  font-family: 'DM Mono', monospace; font-size: 0.55rem;
  letter-spacing: 0.06em; text-transform: uppercase;
  padding: 0.2rem 0.5rem; border-radius: 2px;
  border: 1px solid var(--border);
}
.zd-badge-el { }
.zd-badge-pol { }
.zd-badge-time { color: var(--dim); }
.zd-info-desc {
  font-family: 'Cormorant Garamond', serif; font-size: 0.95rem;
  color: var(--text); line-height: 1.6;
  margin: 0.6rem 0;
}
.zd-select-btn {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  letter-spacing: 0.06em;
  padding: 0.4rem 1.2rem; border: 1px solid var(--accent);
  border-radius: 2px; background: var(--accent);
  color: white; cursor: pointer; transition: all 0.15s;
}
.zd-select-btn:hover { opacity: 0.85; }
.zd-select-btn:disabled { opacity: 0.4; cursor: default; }
.zd-select-btn.current {
  background: var(--surface); color: var(--accent);
  cursor: default;
}

/* Element colors */
.zd-el-water { background: #E3F2FD; border-color: #2196F3; color: #1565C0; }
.zd-el-wood { background: #E8F5E9; border-color: #4CAF50; color: #2E7D32; }
.zd-el-fire { background: #FFEBEE; border-color: #F44336; color: #C62828; }
.zd-el-earth { background: #FFF8E1; border-color: #C8A415; color: #8D6E00; }
.zd-el-metal { background: #F5F5F5; border-color: #9E9E9E; color: #424242; }

/* Polarity styling */
.zd-pol-yin { background: #f0f0f0; }
.zd-pol-yang { background: #fffde7; }
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

  {{-- 師父 Style --}}
  <div class="pf-section" id="shifu-style">
    <div class="pf-section-title">師父 Style · 十二地支</div>
    <div class="pf-card" style="padding:1.5rem 1rem;">

      {{-- View tabs --}}
      <div class="zd-view-tabs">
        <button class="zd-view-tab active" onclick="zdSwitchView('branches')">地支 Branches</button>
        <button class="zd-view-tab" onclick="zdSwitchView('elements')">五行 Elements</button>
        <button class="zd-view-tab" onclick="zdSwitchView('yinyang')">陰陽 Yin-Yang</button>
      </div>

      {{-- Dial --}}
      <div class="zd-dial-wrap">
        <div class="zd-dial" id="zdDial"></div>
        <div class="zd-center">☯</div>
      </div>

      {{-- Info panel --}}
      <div class="zd-info" id="zdInfo"></div>

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

{{-- Zodiac Dial Script --}}
<script>
const ZD_PERSONAS = @json(config('shifu-personas'));
const ZD_ORDER = ['rat','ox','tiger','rabbit','dragon','snake','horse','goat','monkey','rooster','dog','pig'];
let zdCurrentView = 'branches';
let zdSelected = '{{ $user->shifu_persona ?? "dragon" }}';
let zdSaved = zdSelected;

const ZD_EL_COLORS = {
  water: { bg: '#E3F2FD', border: '#2196F3', text: '#1565C0' },
  wood:  { bg: '#E8F5E9', border: '#4CAF50', text: '#2E7D32' },
  fire:  { bg: '#FFEBEE', border: '#F44336', text: '#C62828' },
  earth: { bg: '#FFF8E1', border: '#C8A415', text: '#8D6E00' },
  metal: { bg: '#F5F5F5', border: '#9E9E9E', text: '#424242' },
};

function zdRenderDial() {
  const dial = document.getElementById('zdDial');
  dial.innerHTML = '';

  const cx = 150, cy = 150, r = 120;

  ZD_ORDER.forEach((slug, i) => {
    const p = ZD_PERSONAS[slug];
    // Place at clock positions: horse at 12 (top), rat at 6 (bottom)
    // Horse is index 6 in ZD_ORDER, so offset by +180° (6*30) to put it at top (-90°)
    const angle = (i * 30 + 90) * (Math.PI / 180);
    const x = cx + r * Math.cos(angle);
    const y = cy + r * Math.sin(angle);

    const node = document.createElement('div');
    node.className = 'zd-node' + (slug === zdSelected ? ' selected' : '');
    node.style.left = x + 'px';
    node.style.top = y + 'px';
    node.dataset.slug = slug;
    node.onclick = () => zdSelect(slug);

    // Content based on view
    if (zdCurrentView === 'branches') {
      node.textContent = p.emoji;
      node.title = p.animal_en + ' · ' + p.branch;
    } else if (zdCurrentView === 'elements') {
      const ec = ZD_EL_COLORS[p.element];
      node.textContent = p.element_zh;
      node.style.background = ec.bg;
      node.style.borderColor = ec.border;
      node.style.color = ec.text;
      node.style.fontSize = '1.1rem';
      node.style.fontFamily = "'Noto Serif TC', serif";
      node.style.fontWeight = '600';
      node.title = p.animal_en + ' · ' + p.element;
    } else { // yinyang
      const isYin = p.polarity === 'yin';
      node.textContent = isYin ? '陰' : '陽';
      node.style.background = isYin ? '#2d2d2d' : '#fffde7';
      node.style.color = isYin ? '#e0e0e0' : '#333';
      node.style.borderColor = isYin ? '#555' : '#ddd';
      node.style.fontSize = '0.9rem';
      node.style.fontFamily = "'Noto Serif TC', serif";
      node.style.fontWeight = '600';
      node.title = p.animal_en + ' · ' + (isYin ? 'Yin' : 'Yang');
    }

    dial.appendChild(node);
  });

  zdRenderInfo(zdSelected);
}

function zdSelect(slug) {
  zdSelected = slug;
  // Update node classes
  document.querySelectorAll('.zd-node').forEach(n => {
    n.classList.toggle('selected', n.dataset.slug === slug);
  });
  zdRenderInfo(slug);
}

function zdRenderInfo(slug) {
  const p = ZD_PERSONAS[slug];
  const ec = ZD_EL_COLORS[p.element];
  const isCurrent = slug === zdSaved;

  const elBadgeStyle = `background:${ec.bg};border-color:${ec.border};color:${ec.text}`;
  const polLabel = p.polarity === 'yin' ? '陰 Yin' : '陽 Yang';
  const polStyle = p.polarity === 'yin'
    ? 'background:#2d2d2d;color:#e0e0e0;border-color:#555'
    : 'background:#fffde7;color:#333;border-color:#ddd';

  document.getElementById('zdInfo').innerHTML = `
    <div class="zd-info-animal">${p.emoji}</div>
    <div class="zd-info-branch">${p.branch}</div>
    <div class="zd-info-name">${p.animal_en} · ${p.animal_zh}</div>
    <div class="zd-info-archetype">${p.archetype}</div>
    <div class="zd-info-badges">
      <span class="zd-badge" style="${elBadgeStyle}">${p.element_zh} ${p.element}</span>
      <span class="zd-badge" style="${polStyle}">${polLabel}</span>
      <span class="zd-badge zd-badge-time">${p.time}</span>
    </div>
    <div class="zd-info-desc">${p.description}</div>
    <button class="zd-select-btn ${isCurrent ? 'current' : ''}" ${isCurrent ? 'disabled' : ''}
            onclick="zdSave('${slug}')">
      ${isCurrent ? 'Current 師父' : 'Select this 師父'}
    </button>
    <div class="pf-msg" id="zdMsg" style="text-align:center;margin-top:0.4rem;"></div>
  `;
}

async function zdSave(slug) {
  const btn = document.querySelector('.zd-select-btn');
  const msg = document.getElementById('zdMsg');
  if (!btn) return;

  btn.disabled = true;
  btn.textContent = 'Saving...';

  try {
    const res = await fetch('/profile/shifu-persona', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': pfCsrf(), 'Accept': 'application/json' },
      body: JSON.stringify({ persona: slug }),
    });

    if (res.ok) {
      zdSaved = slug;
      btn.textContent = 'Current 師父';
      btn.classList.add('current');
      msg.textContent = '師父 style updated';
      msg.style.color = 'var(--jade)';
      msg.style.display = 'block';
      setTimeout(() => msg.style.display = 'none', 2500);
    } else {
      throw new Error('failed');
    }
  } catch (e) {
    btn.disabled = false;
    btn.textContent = 'Select this 師父';
    msg.textContent = 'Error saving';
    msg.style.color = 'var(--rose)';
    msg.style.display = 'block';
  }
}

function zdSwitchView(view) {
  zdCurrentView = view;
  document.querySelectorAll('.zd-view-tab').forEach(t => {
    t.classList.toggle('active', t.textContent.toLowerCase().includes(
      view === 'branches' ? '地支' : view === 'elements' ? '五行' : '陰陽'
    ));
  });
  // More reliable: match by onclick content
  document.querySelectorAll('.zd-view-tab').forEach(t => {
    const onclick = t.getAttribute('onclick') || '';
    t.classList.toggle('active', onclick.includes("'" + view + "'"));
  });
  zdRenderDial();
}

// Init dial on load
zdRenderDial();
</script>

@include('partials.lexicon._site-footer')
</body>
</html>
