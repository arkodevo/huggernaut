{{-- Shared CSS variables + base reset --}}
<style>
:root {
  --bg: #ffffff;
  --surface: #f5f4f8;
  --surface2: #eeecf4;
  --border: rgba(0,0,0,0.12);
  --border-active: rgba(100,70,200,0.4);
  --text: #1a1828;
  --dim: rgba(26,24,40,0.72);
  --accent: #6240c8;
  --gold: #a0720a;
  --jade: #1a8a5a;
  --rose: #b83050;
  --ink: #0a0816;
  --tag-bg: rgba(98,64,200,0.08);
}

* { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; overflow-x: hidden; }

body {
  background: var(--bg);
  color: var(--text);
  font-family: 'DM Mono', monospace;
  min-height: 100vh;
  line-height: 1.6;
  overflow-x: hidden;
}

body::before {
  content: '';
  position: fixed; inset: 0;
  background:
    radial-gradient(ellipse 60% 40% at 15% 20%, rgba(98,64,200,0.05) 0%, transparent 70%),
    radial-gradient(ellipse 50% 60% at 85% 80%, rgba(26,138,90,0.04) 0%, transparent 70%);
  pointer-events: none; z-index: 0;
}
</style>
