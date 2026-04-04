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
html { scroll-behavior: smooth; overflow-x: clip; }

body {
  background: var(--bg);
  color: var(--text);
  font-family: 'DM Mono', monospace;
  min-height: 100vh;
  line-height: 1.6;
  overflow-x: clip;
}

body::before {
  content: '';
  position: fixed; inset: 0;
  background:
    radial-gradient(ellipse 60% 40% at 15% 20%, rgba(98,64,200,0.05) 0%, transparent 70%),
    radial-gradient(ellipse 50% 60% at 85% 80%, rgba(26,138,90,0.04) 0%, transparent 70%);
  pointer-events: none; z-index: 0;
}
/* ── SHARED DELETE CONFIRMATION ── */
.confirm-delete-bar {
  display: flex; align-items: center; gap: 0.6rem;
  padding: 0.5rem 0.6rem; margin-top: 0.4rem;
  background: rgba(200,60,60,0.04);
  border: 1px solid rgba(200,60,60,0.2);
  border-radius: 2px;
  animation: confirmFadeIn 0.15s ease;
}
@keyframes confirmFadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
.confirm-delete-msg {
  font-family: 'Cormorant Garamond', serif;
  font-size: 0.9rem; color: var(--text); flex: 1;
}
.confirm-delete-yes {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: #fff; background: var(--rose);
  border: none; border-radius: 2px;
  padding: 0.3rem 0.7rem; cursor: pointer;
  transition: opacity 0.2s;
}
.confirm-delete-yes:hover { opacity: 0.8; }
.confirm-delete-no {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim); background: none;
  border: 1px solid var(--border); border-radius: 2px;
  padding: 0.3rem 0.7rem; cursor: pointer;
  transition: all 0.2s;
}
.confirm-delete-no:hover { border-color: var(--accent); color: var(--text); }
</style>
<script>
// Shared delete confirmation — call from anywhere
function showDeleteConfirm(anchorEl, message, onConfirm) {
  // Don't show if already showing
  var existing = anchorEl.querySelector('.confirm-delete-bar');
  if (existing) { existing.remove(); return; }
  var bar = document.createElement('div');
  bar.className = 'confirm-delete-bar';
  bar.innerHTML = '<span class="confirm-delete-msg">' + message + '</span>'
    + '<button class="confirm-delete-yes">Delete</button>'
    + '<button class="confirm-delete-no">Cancel</button>';
  bar.querySelector('.confirm-delete-yes').onclick = function(e) { e.stopPropagation(); bar.remove(); onConfirm(); };
  bar.querySelector('.confirm-delete-no').onclick = function(e) { e.stopPropagation(); bar.remove(); };
  bar.onclick = function(e) { e.stopPropagation(); };
  anchorEl.appendChild(bar);
}
</script>
