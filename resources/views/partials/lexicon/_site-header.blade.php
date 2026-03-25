{{-- Shared site header — @include('partials.lexicon._site-header', ['backUrl' => '...', 'backLabel' => '...']) --}}
<style>
.site-header {
  position: sticky; top: 0; z-index: 250;
  background: rgba(255,255,255,0.95); backdrop-filter: blur(8px);
  border-bottom: 1px solid var(--border);
  padding: 0.8rem 1.2rem;
  display: flex; align-items: center; gap: 0.6rem;
  overflow: visible;
}
.site-header-back {
  font-family: 'DM Mono', monospace; font-size: 0.75rem;
  color: var(--dim); text-decoration: none;
  transition: color 0.15s;
  flex-shrink: 0;
}
.site-header-back:hover { color: var(--accent); }
.site-header-logo {
  font-family: 'DM Mono', monospace;
  font-size: 0.6rem; letter-spacing: 0.35em; text-transform: uppercase;
  color: var(--accent); opacity: 0.7;
  flex: 1; text-align: center;
  white-space: nowrap;
  text-decoration: none;
}
.site-header-logo:hover { opacity: 1; }
.site-header > .user-menu {
  position: absolute; right: 1rem; top: 50%;
  transform: translateY(-50%);
}
.site-nav {
  display: flex; justify-content: center; gap: 0.15rem;
  padding: 0.3rem 1rem;
  border-bottom: 1px solid var(--border);
  background: var(--surface);
}
.site-nav a {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  letter-spacing: 0.06em; text-transform: uppercase;
  color: var(--dim); text-decoration: none;
  padding: 0.25rem 0.6rem; border-radius: 2px;
  transition: color 0.15s, background 0.15s;
}
.site-nav a:hover { color: var(--ink); background: rgba(0,0,0,0.03); }
.site-nav a.active { color: var(--accent); font-weight: 500; }
.site-nav a.coming-soon { opacity: 0.4; cursor: default; }
.site-nav a.coming-soon:hover { color: var(--dim); background: none; }
</style>

<header class="site-header">
  @if(!empty($backUrl))
    <a href="{{ $backUrl }}" class="site-header-back">&larr; {{ $backLabel ?? 'Back' }}</a>
  @endif
  <a href="{{ route('lexicon.index') }}" class="site-header-logo">流動 · Living Lexicon</a>
  @include('partials.lexicon._user-menu')
</header>
<nav class="site-nav">
  <a href="{{ route('lexicon.index') }}" class="{{ request()->routeIs('lexicon.*') ? 'active' : '' }}">Dictionary</a>
  <a href="{{ route('chinese-names') }}" class="{{ request()->routeIs('chinese-names') ? 'active' : '' }}">Chinese Names</a>
  <a href="{{ route('translation') }}" class="{{ request()->routeIs('translation') ? 'active' : '' }} coming-soon" title="Coming soon">Translation</a>
  <a href="{{ route('idioms') }}" class="{{ request()->routeIs('idioms') ? 'active' : '' }} coming-soon" title="Coming soon">Idioms</a>
</nav>
