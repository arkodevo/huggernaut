{{-- Slide-in navigation drawer. Opened by the ☰ button in _site-header. --}}
<style>
/* ── BACKDROP ── */
.drawer-backdrop {
  position: fixed; inset: 0;
  background: rgba(26, 24, 40, 0.45);
  opacity: 0; pointer-events: none;
  transition: opacity 0.22s ease;
  z-index: 400;
}
.drawer-backdrop.open {
  opacity: 1; pointer-events: auto;
}

/* ── DRAWER PANEL ── */
.site-drawer {
  position: fixed; top: 0; left: 0; bottom: 0;
  width: 88%; max-width: 320px;
  background: var(--surface);
  border-right: 1px solid var(--border);
  box-shadow: 2px 0 16px rgba(0,0,0,0.08);
  transform: translateX(-100%);
  transition: transform 0.24s ease;
  z-index: 410;
  display: flex; flex-direction: column;
  overflow: hidden;
}
.site-drawer.open { transform: translateX(0); }

/* ── DRAWER HEADER ── */
.drawer-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1rem 1.1rem;
  border-bottom: 1px solid var(--border);
  flex-shrink: 0;
}
.drawer-logo {
  font-family: 'DM Mono', monospace;
  font-size: 0.6rem; letter-spacing: 0.35em; text-transform: uppercase;
  color: var(--accent); text-decoration: none;
  opacity: 0.8;
}
.drawer-logo:hover { opacity: 1; }
.drawer-close {
  background: none; border: none; cursor: pointer;
  font-family: 'DM Mono', monospace; font-size: 1.1rem;
  color: var(--dim); padding: 0.1rem 0.45rem;
  line-height: 1; border-radius: 2px;
  transition: color 0.15s, background 0.15s;
}
.drawer-close:hover { color: var(--ink); background: rgba(0,0,0,0.04); }

/* ── DRAWER BODY (scrollable) ── */
.drawer-body {
  flex: 1; overflow-y: auto;
  padding: 0.8rem 0;
}

/* ── SECTIONS ── */
.drawer-section { margin-bottom: 1.1rem; }
.drawer-section:last-child { margin-bottom: 0; }
.drawer-section-label {
  font-family: 'DM Mono', monospace;
  font-size: 0.56rem; letter-spacing: 0.14em; text-transform: uppercase;
  color: var(--dim); opacity: 0.6;
  padding: 0 1.2rem 0.45rem;
}
.drawer-link {
  display: flex; align-items: center;
  gap: 0.6rem;
  font-family: 'DM Mono', monospace; font-size: 0.78rem;
  letter-spacing: 0.02em;
  color: var(--ink); text-decoration: none;
  padding: 0.55rem 1.2rem;
  border-left: 2px solid transparent;
  transition: color 0.12s, background 0.12s, border-color 0.12s;
}
.drawer-link:hover {
  background: rgba(98, 64, 200, 0.04);
  color: var(--accent);
}
.drawer-link.active {
  color: var(--accent);
  background: rgba(98, 64, 200, 0.06);
  border-left-color: var(--accent);
  font-weight: 500;
}
.drawer-link.coming-soon {
  opacity: 0.45;
  cursor: default;
  pointer-events: none;
}
.drawer-link-icon {
  font-size: 0.85rem;
  width: 1.1rem; text-align: center;
  opacity: 0.75;
}
.drawer-link-soon {
  margin-left: auto;
  font-size: 0.55rem; color: var(--dim);
  letter-spacing: 0.08em; text-transform: uppercase;
  opacity: 0.6;
}

/* ── DRAWER FOOTER ── */
.drawer-footer {
  border-top: 1px solid var(--border);
  padding: 0.8rem 1.2rem;
  flex-shrink: 0;
  display: flex; align-items: center; gap: 0.75rem;
}
.drawer-footer form { margin: 0; display: inline; }
.drawer-footer-link,
.drawer-footer button {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim); text-decoration: none;
  background: none; border: none; cursor: pointer;
  padding: 0.2rem 0;
  transition: color 0.12s;
}
.drawer-footer-link:hover,
.drawer-footer button:hover { color: var(--accent); }
.drawer-footer-sep { color: var(--border); }

/* ── BODY SCROLL LOCK ── */
body.drawer-open { overflow: hidden; }
</style>

<div class="drawer-backdrop" id="drawerBackdrop" onclick="closeDrawer()" aria-hidden="true"></div>

<aside class="site-drawer" id="siteDrawer" aria-hidden="true" aria-label="Main navigation">
  <div class="drawer-header">
    <a href="{{ route('lexicon.index') }}" class="drawer-logo">流動 · Living Lexicon</a>
    <button type="button" class="drawer-close" onclick="closeDrawer()" aria-label="Close menu">✕</button>
  </div>

  <nav class="drawer-body">

    {{-- ── LEARN ── --}}
    <div class="drawer-section">
      <div class="drawer-section-label">Learn</div>
      <a href="{{ route('lexicon.index') }}"
         class="drawer-link {{ request()->routeIs('lexicon.*') ? 'active' : '' }}">
        <span class="drawer-link-icon">📖</span> Dictionary
      </a>
      <a href="{{ route('chinese-names') }}"
         class="drawer-link {{ request()->routeIs('chinese-names') ? 'active' : '' }}">
        <span class="drawer-link-icon">名</span> Chinese Names
      </a>
      <a href="{{ route('translation') }}"
         class="drawer-link coming-soon"
         title="Coming soon">
        <span class="drawer-link-icon">譯</span> Translation
        <span class="drawer-link-soon">Soon</span>
      </a>
      <a href="{{ route('idioms') }}"
         class="drawer-link coming-soon"
         title="Coming soon">
        <span class="drawer-link-icon">成</span> Idioms
        <span class="drawer-link-soon">Soon</span>
      </a>
    </div>

    @auth
      {{-- ── COMMUNITY ── --}}
      <div class="drawer-section">
        <div class="drawer-section-label">Community</div>
        <a href="{{ route('community') }}"
           class="drawer-link {{ request()->routeIs('community') ? 'active' : '' }}">
          <span class="drawer-link-icon">🏛</span> Community
        </a>
      </div>

      {{-- ── MY ── --}}
      <div class="drawer-section">
        <div class="drawer-section-label">My</div>
        <a href="{{ route('dashboard') }}"
           class="drawer-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <span class="drawer-link-icon">◉</span> Dashboard
        </a>
        <a href="{{ route('my-words') }}"
           class="drawer-link {{ request()->routeIs('my-words*') ? 'active' : '' }}">
          <span class="drawer-link-icon">字</span> My Words
        </a>
        <a href="{{ route('my-writings') }}"
           class="drawer-link {{ request()->routeIs('my-writings') ? 'active' : '' }}">
          <span class="drawer-link-icon">筆</span> My Writings
        </a>
        <a href="{{ route('my-activity') }}"
           class="drawer-link {{ request()->routeIs('my-activity') ? 'active' : '' }}">
          <span class="drawer-link-icon">流</span> My Activity
        </a>
      </div>

      {{-- ── SETTINGS ── --}}
      <div class="drawer-section">
        <div class="drawer-section-label">Settings</div>
        <a href="{{ route('profile') }}"
           class="drawer-link {{ request()->routeIs('profile') ? 'active' : '' }}">
          <span class="drawer-link-icon">人</span> Profile
        </a>
        @php $shifuEmoji = config('shifu-personas.' . (Auth::user()->shifu_persona ?? 'dragon') . '.emoji', '🐉'); @endphp
        <a href="{{ route('profile') }}#shifu-style" class="drawer-link">
          <span class="drawer-link-icon">{{ $shifuEmoji }}</span> 師父 Style
        </a>
      </div>
    @endauth

  </nav>

  <div class="drawer-footer">
    @auth
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Log out</button>
      </form>
    @else
      <a href="{{ route('login') }}" class="drawer-footer-link">Log in</a>
      <span class="drawer-footer-sep">·</span>
      <a href="{{ route('register') }}" class="drawer-footer-link">Register</a>
    @endauth
  </div>
</aside>

<script>
(function() {
  var drawer   = document.getElementById('siteDrawer');
  var backdrop = document.getElementById('drawerBackdrop');
  var toggleBtn;

  window.openDrawer = function() {
    drawer.classList.add('open');
    backdrop.classList.add('open');
    document.body.classList.add('drawer-open');
    drawer.setAttribute('aria-hidden', 'false');
    backdrop.setAttribute('aria-hidden', 'false');
    toggleBtn = document.getElementById('drawerToggle');
    if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
  };

  window.closeDrawer = function() {
    drawer.classList.remove('open');
    backdrop.classList.remove('open');
    document.body.classList.remove('drawer-open');
    drawer.setAttribute('aria-hidden', 'true');
    backdrop.setAttribute('aria-hidden', 'true');
    toggleBtn = document.getElementById('drawerToggle');
    if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
  };

  // ESC to close
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && drawer.classList.contains('open')) {
      window.closeDrawer();
    }
  });
})();
</script>
