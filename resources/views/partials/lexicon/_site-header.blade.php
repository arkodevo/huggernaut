{{-- Shared site header — @include('partials.lexicon._site-header', ['backUrl' => '...', 'backLabel' => '...']) --}}
{{-- Hamburger ☰ on the left opens _site-drawer; identity chip on the right links to profile. --}}
<style>
.site-header {
  position: sticky; top: 0; z-index: 250;
  background: rgba(255,255,255,0.95); backdrop-filter: blur(8px);
  border-bottom: 1px solid var(--border);
  padding: 0.8rem 1.2rem;
  display: flex; align-items: center; gap: 0.75rem;
  overflow: visible;
}
.site-header-burger {
  background: none; border: none; cursor: pointer;
  padding: 0.2rem 0.35rem; margin: 0;
  display: inline-flex; align-items: center; justify-content: center;
  color: var(--dim);
  border-radius: 2px;
  transition: color 0.15s, background 0.15s;
  flex-shrink: 0;
}
.site-header-burger:hover { color: var(--accent); background: rgba(98,64,200,0.05); }
.site-header-burger svg { display: block; }
.site-header-back {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
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

/* Identity chip (right side, replaces the old dropdown) */
.site-identity {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--dim); text-decoration: none;
  display: inline-flex; align-items: center; gap: 0.35rem;
  padding: 0.2rem 0.4rem;
  border-radius: 2px;
  transition: color 0.15s, background 0.15s;
  flex-shrink: 0;
  max-width: 140px;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.site-identity:hover { color: var(--accent); background: rgba(98,64,200,0.04); }
.site-identity-emoji { font-size: 0.95rem; line-height: 1; }
.site-identity-guest {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--accent); text-decoration: none;
  flex-shrink: 0;
}
.site-identity-guest:hover { opacity: 0.75; }
.site-identity-sep { color: var(--dim); margin: 0 0.2rem; font-size: 0.65rem; }
</style>

<header class="site-header">
  <button type="button"
          class="site-header-burger"
          id="drawerToggle"
          onclick="openDrawer()"
          aria-label="Open menu"
          aria-expanded="false"
          aria-controls="siteDrawer">
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
      <path d="M3 6h14M3 10h14M3 14h14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
    </svg>
  </button>

  @if(!empty($backUrl))
    <a href="{{ $backUrl }}" class="site-header-back">&larr; {{ $backLabel ?? 'Back' }}</a>
  @endif

  <a href="{{ route('lexicon.index') }}" class="site-header-logo">流動 · Living Lexicon</a>

  @auth
    @php $shifuEmoji = config('shifu-personas.' . (Auth::user()->shifu_persona ?? 'dragon') . '.emoji', '🐉'); @endphp
    <a href="{{ route('profile') }}" class="site-identity" title="{{ Auth::user()->chinese_name ?? Auth::user()->name }} — Profile">
      <span class="site-identity-emoji">{{ $shifuEmoji }}</span>
      <span>{{ Auth::user()->chinese_name ?? Auth::user()->name }}</span>
    </a>
  @else
    <a href="{{ route('login') }}" class="site-identity-guest">Log in</a>
    <span class="site-identity-sep">·</span>
    <a href="{{ route('register') }}" class="site-identity-guest">Register</a>
  @endauth
</header>

@include('partials.lexicon._site-drawer')
