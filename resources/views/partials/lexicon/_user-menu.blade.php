<style>
.user-menu {
  position: relative;
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  letter-spacing: 0.04em;
  white-space: nowrap;
  z-index: 210;
}
.user-menu a {
  color: var(--accent); text-decoration: none;
  transition: opacity 0.15s;
}
.user-menu a:hover { opacity: 0.7; }
.user-menu-sep { color: var(--dim); margin: 0 0.15rem; }

/* Logged-in dropdown */
.user-menu-name {
  font-size: 0.65rem; color: var(--dim);
  cursor: pointer; padding: 0.2rem 0;
  display: inline-flex; align-items: center; gap: 0.25rem;
  transition: color 0.15s;
}
.user-menu-name:hover { color: var(--ink); }
.user-menu-name::after {
  content: '▾'; font-size: 0.55rem; margin-top: 1px;
}
.user-menu-dropdown {
  display: none; position: absolute; right: 0; top: 100%;
  margin-top: 0.3rem;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 3px; padding: 0.4rem 0;
  min-width: 140px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  z-index: 300;
}
.user-menu-dropdown.open { display: block; }
.user-menu-dropdown a,
.user-menu-dropdown button {
  display: block; width: 100%;
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--dim); background: none; border: none;
  padding: 0.35rem 0.75rem; text-align: left;
  cursor: pointer; transition: all 0.12s;
  text-decoration: none;
}
.user-menu-dropdown a:hover,
.user-menu-dropdown button:hover {
  color: var(--ink); background: rgba(0,0,0,0.03);
}
</style>

<div class="user-menu" id="userMenu">
  @auth
    @php $shifuEmoji = config('shifu-personas.' . (Auth::user()->shifu_persona ?? 'dragon') . '.emoji', '🐉'); @endphp
    <span class="user-menu-name" onclick="document.getElementById('userDropdown').classList.toggle('open')">
      <a href="{{ route('profile') }}#shifu-style" style="text-decoration:none;font-size:0.85rem;line-height:1" title="師父 Style">{{ $shifuEmoji }}</a>
      {{ Auth::user()->chinese_name ?? Auth::user()->name }}
    </span>
    <div class="user-menu-dropdown" id="userDropdown">
      <a href="{{ route('my-words') }}">My Words</a>
      <a href="{{ route('my-writings') }}">My Writings</a>
      <a href="{{ route('profile') }}">Profile</a>
      <a href="{{ route('profile') }}#shifu-style">師父 Style</a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Log out</button>
      </form>
    </div>
  @else
    <a href="{{ route('login') }}">Log in</a>
    <span class="user-menu-sep">&middot;</span>
    <a href="{{ route('register') }}">Register</a>
  @endauth
</div>

<script>
// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
  var dd = document.getElementById('userDropdown');
  if (dd && !e.target.closest('.user-menu')) dd.classList.remove('open');
});
</script>
