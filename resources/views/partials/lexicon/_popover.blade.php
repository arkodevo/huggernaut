{{-- Shared word popover (HTML + CSS + JS) --}}
{{-- Optional hook: define window.onSegNavigate(smartId, trad) before include for custom behavior on second click --}}
<div class="seg-popover" id="segPopover">
  <div class="seg-pop-char" id="segPopChar"></div>
  <div class="seg-pop-pinyin" id="segPopPinyin"></div>
  <div class="seg-pop-pos" id="segPopPos"></div>
  <div class="seg-pop-def" id="segPopDef"></div>
  <a class="seg-pop-link" id="segPopLink" href="#">Open &rarr;</a>
</div>
<style>
.seg-popover {
  position: fixed; z-index: 500;
  background: white;
  border: 1px solid var(--border-active, rgba(98,64,200,0.35));
  border-radius: 4px;
  box-shadow: 0 8px 28px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.08);
  padding: 0.6rem 0.8rem;
  min-width: 180px; max-width: 280px;
  display: none;
  flex-direction: column; gap: 0.2rem;
  animation: segPopIn 0.15s ease;
}
.seg-popover.open { display: flex; }
@keyframes segPopIn {
  from { opacity: 0; transform: translateY(-4px); }
  to   { opacity: 1; transform: translateY(0); }
}
.seg-pop-char {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 1.4rem; font-weight: 300; color: var(--ink); line-height: 1.2;
}
.seg-pop-pinyin {
  font-style: italic; font-family: 'Cormorant Garamond', serif;
  font-size: 0.82rem; color: var(--accent);
}
.seg-pop-pos {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: #7060a8;
}
.seg-pop-def {
  font-family: 'Cormorant Garamond', serif;
  font-size: 0.85rem; color: var(--text); line-height: 1.3;
}
.seg-pop-link {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--accent); text-decoration: none;
  margin-top: 0.15rem;
}
.seg-pop-link:hover { text-decoration: underline; }
</style>
<script>
let segPopTarget = null;
let segPopTapCount = 0;

function showSegPop(el) {
  const pop = document.getElementById('segPopover');
  const rect = el.getBoundingClientRect();
  document.getElementById('segPopChar').textContent = el.dataset.trad || el.textContent;
  document.getElementById('segPopPinyin').textContent = el.dataset.pinyin || '';
  document.getElementById('segPopPos').textContent = el.dataset.pos ? (POS_ABBR[el.dataset.pos] || el.dataset.pos) : '';
  const def = el.dataset.def || '';
  document.getElementById('segPopDef').textContent = def.length > 50 ? def.substring(0, 50) + '...' : def;
  document.getElementById('segPopLink').href = '/lexicon/' + el.dataset.smartId;
  let top = rect.bottom + 6;
  let left = rect.left;
  if (left + 280 > window.innerWidth) left = window.innerWidth - 290;
  if (left < 4) left = 4;
  if (top + 160 > window.innerHeight) top = rect.top - 160;
  pop.style.top = top + 'px';
  pop.style.left = left + 'px';
  pop.classList.add('open');
}

function hideSegPop() {
  document.getElementById('segPopover').classList.remove('open');
  segPopTarget = null;
  segPopTapCount = 0;
}

document.addEventListener('click', function(e) {
  const seg = e.target.closest('.seg-known');
  if (seg) {
    e.preventDefault();
    e.stopPropagation();
    if (segPopTarget === seg) {
      segPopTapCount++;
      if (segPopTapCount >= 2) {
        // Call optional navigate hook (e.g. word-detail pushTrail)
        if (typeof window.onSegNavigate === 'function') {
          window.onSegNavigate(seg.dataset.smartId, seg.dataset.trad || seg.textContent);
        }
        window.location.href = '/lexicon/' + seg.dataset.smartId;
        return;
      }
    } else {
      segPopTarget = seg;
      segPopTapCount = 1;
      showSegPop(seg);
    }
    return;
  }
  if (!e.target.closest('.seg-popover')) {
    hideSegPop();
  }
});
</script>
