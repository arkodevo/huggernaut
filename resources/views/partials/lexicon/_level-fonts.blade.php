{{-- Shared font scaling per learner level --}}
{{-- Depends on: currentLevel, fontScale (defined by each page before include) --}}
<script>
const LEVEL_FONTS = {
  beginner:   { hanzi: 3.8, simp: 1.9, pinyin: 1.2, defn: 2.0, note: 1.1, formula: 1.1, exCn: 2.0, exEn: 1.1, scale: 130 },
  learner:    { hanzi: 3.2, simp: 1.6, pinyin: 1.1, defn: 1.9, note: 1.0, formula: 1.0, exCn: 1.9, exEn: 1.0, scale: 115 },
  developing: { hanzi: 2.8, simp: 1.4, pinyin: 1.0, defn: 1.5, note: 0.9, formula: 1.0, exCn: 1.8, exEn: 1.0, scale: 100 },
  advanced:   { hanzi: 2.4, simp: 1.2, pinyin: 0.9, defn: 1.6, note: 0.9, formula: 0.9, exCn: 1.6, exEn: 0.9, scale: 90  },
  native:     { hanzi: 2.0, simp: 1.0, pinyin: 0.8, defn: 1.4, note: 0.85,formula: 0.85,exCn: 1.4, exEn: 0.85,scale: 85  },
};

const FONT_STEPS = [75, 85, 100, 115, 130, 150];

function applyLevelFonts(level) {
  currentLevel = level;
  var f = LEVEL_FONTS[level];
  if (!f) return;
  var r = document.documentElement;
  r.style.setProperty('--fs-hanzi',   f.hanzi   + 'rem');
  r.style.setProperty('--fs-simp',    f.simp    + 'rem');
  r.style.setProperty('--fs-pinyin',  f.pinyin  + 'rem');
  r.style.setProperty('--fs-defn',    f.defn    + 'rem');
  r.style.setProperty('--fs-note',    f.note    + 'rem');
  r.style.setProperty('--fs-formula', f.formula + 'rem');
  r.style.setProperty('--fs-ex-cn',   f.exCn    + 'rem');
  r.style.setProperty('--fs-ex-en',   f.exEn    + 'rem');
  // Use stored fontScale if available (from A+/A− adjustment), otherwise level default
  var storedScale = localStorage.getItem('fontScale');
  applyFontScale(storedScale ? parseInt(storedScale, 10) : f.scale);
}

function applyFontScale(scale) {
  fontScale = scale;
  // Apply scale only to card content area — not the whole page UI
  var cardContainer = document.getElementById('cardContainer');
  if (cardContainer) cardContainer.style.fontSize = (scale / 100) + 'em';
  // Also scale the IWP content area if on that page
  var wdContent = document.getElementById('wdContent');
  if (wdContent) wdContent.style.fontSize = (scale / 100) + 'em';
  var fontValEl = document.getElementById('fontVal');
  if (fontValEl) fontValEl.textContent = scale + '%';
  localStorage.setItem('fontScale', String(scale));
  if (window.syncPref) syncPref('fontScale', String(scale));
}
</script>
