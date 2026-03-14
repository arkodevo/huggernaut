<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>流動 Living Lexicon — Flow 流</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@300;400;600;700&family=DM+Mono:ital,wght@0,300;0,400;1,300&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
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

/* ── BACKGROUND ── */
body::before {
  content: '';
  position: fixed; inset: 0;
  background:
    radial-gradient(ellipse 60% 40% at 15% 20%, rgba(98,64,200,0.05) 0%, transparent 70%),
    radial-gradient(ellipse 50% 60% at 85% 80%, rgba(26,138,90,0.04) 0%, transparent 70%);
  pointer-events: none; z-index: 0;
}

/* ── HEADER ── */
header {
  position: relative; z-index: 10;
  padding: 1rem 2rem 1.1rem;
  border-bottom: 1px solid var(--border);
  display: flex; flex-direction: column; align-items: center;
  gap: 0.65rem;
}
.logo-tag {
  font-size: 0.6rem; letter-spacing: 0.35em; text-transform: uppercase;
  color: var(--accent); opacity: 0.7;
}

/* ── SCENARIO BUTTONS ── */
.scenario-select {
  font-family: 'DM Mono', monospace; font-size: 0.78rem;
  color: var(--ink); background: var(--surface);
  border: 1px solid var(--border); border-radius: 2px;
  padding: 0.35rem 2rem 0.35rem 0.7rem;
  cursor: pointer; flex: 1; min-width: 180px; max-width: 320px;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23888'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 0.6rem center;
  transition: border-color 0.2s;
}
.scenario-select:focus { outline: none; border-color: var(--accent); }
.scenario-save-btn {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--accent); background: none;
  border: 1px solid rgba(155,127,240,0.35); border-radius: 2px;
  padding: 0.35rem 0.75rem; cursor: pointer;
  white-space: nowrap; transition: all 0.2s; flex-shrink: 0;
}
.scenario-save-btn:hover { background: rgba(155,127,240,0.08); border-color: var(--accent); }
.scenario-clear-btn {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--dim); background: none; border: none;
  cursor: pointer; padding: 0.35rem 0.4rem;
  transition: color 0.2s; flex-shrink: 0;
}
.scenario-clear-btn:hover { color: var(--text); }
/* Save name dialog */
.scenario-dialog-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.35); z-index: 9000;
  align-items: center; justify-content: center;
}
.scenario-dialog-overlay.open { display: flex; }
.scenario-dialog {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 4px; padding: 1.4rem 1.6rem;
  min-width: 280px; display: flex; flex-direction: column; gap: 0.8rem;
  box-shadow: 0 8px 32px rgba(0,0,0,0.18);
}
.scenario-dialog-title {
  font-family: 'DM Mono', monospace; font-size: 0.8rem;
  color: var(--ink); letter-spacing: 0.05em;
}
.scenario-dialog-input {
  font-family: 'DM Mono', monospace; font-size: 0.85rem;
  color: var(--ink); background: #f5f4f8;
  border: 1px solid var(--border); border-radius: 2px;
  padding: 0.5rem 0.7rem; width: 100%;
}
.scenario-dialog-input:focus { outline: none; border-color: var(--accent); }
.scenario-dialog-btns { display: flex; gap: 0.5rem; justify-content: flex-end; }
.scenario-dialog-confirm {
  font-family: 'DM Mono', monospace; font-size: 0.75rem;
  background: var(--accent); color: #fff; border: none;
  border-radius: 2px; padding: 0.4rem 1rem; cursor: pointer;
}
.scenario-dialog-cancel {
  font-family: 'DM Mono', monospace; font-size: 0.75rem;
  background: none; color: var(--dim); border: 1px solid var(--border);
  border-radius: 2px; padding: 0.4rem 0.8rem; cursor: pointer;
}

/* ── MAIN LAYOUT ── */
main {
  position: relative; z-index: 5;
  display: block;
  min-height: calc(100vh - 320px);
  overflow: visible;
}

/* ── FILTER PANEL ── */
/* ── FILTER PANEL — COMPACT ── */
.filter-panel {
  display: none; /* .filter-panel no longer used */
}

/* ── ATTRIBUTE FILTERS TOGGLE BAR ── */
.attr-bar {
  background: #eae8f2;
  border-bottom: 1px solid var(--border);
  position: relative; z-index: 200;
}
.attr-bar-tab {
  width: 100%;
  display: flex; align-items: center; justify-content: center; gap: 0.5rem;
  padding: 0.65rem 1rem;
  font-family: 'DM Mono', monospace;
  font-size: 0.72rem; letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--dim); background: transparent;
  border: none; cursor: pointer;
  transition: background 0.15s, color 0.15s;
}
.attr-bar-tab:hover { background: rgba(98,64,200,0.06); color: var(--text); }
.attr-bar-tab.open  { background: white; color: var(--accent); }

/* Panel — display:block when open so dropdowns can overflow freely */
.attr-filter-panel {
  max-height: 0; overflow: hidden;
  border-bottom: 0px solid var(--border);
  position: relative; z-index: 199;
  transition: max-height 0.28s ease, border-bottom-width 0.28s;
}
.attr-filter-panel.open {
  max-height: 900px;
  border-bottom: 1px solid var(--border);
}

/* ── FILTER BAR — desktop: 6 equal cols + reset auto ── */
.filter-bar {
  display: grid;
  grid-template-columns: repeat(6, 1fr) auto;
  background: #f5f4f8;
}

/* Each filter group fills its cell */
.filter-row {
  display: flex; flex-direction: column;
  align-items: flex-start;
  position: relative;
  cursor: pointer;
  border-right: 1px solid var(--border);
  user-select: none;
  transition: background 0.15s;
}
/* TOCFL is the 6th — no right border before the reset button */
.filter-bar .filter-row:nth-child(6) { border-right: none; }

/* Reset as the 7th grid item */
.filter-reset-btn {
  display: flex; align-items: center; justify-content: center;
  padding: 0 1rem;
  border: none; border-left: 1px solid var(--border);
  background: transparent;
  font-family: 'DM Mono', monospace; font-size: 0.58rem;
  letter-spacing: 0.1em; text-transform: uppercase;
  color: var(--dim); cursor: pointer; white-space: nowrap;
  transition: all 0.15s;
}
.filter-reset-btn:hover { color: var(--text); background: rgba(0,0,0,0.03); }
.filter-row:hover { background: rgba(98,64,200,0.04); }
.filter-row.has-selection { background: rgba(155,127,240,0.06); }
.filter-row.open { background: rgba(98,64,200,0.08); }

/* Top trigger row — label + chevron */
.filter-row-trigger {
  display: flex; align-items: center; gap: 0.35rem;
  padding: 0.55rem 0.75rem 0.45rem;
  width: 100%;
}
.filter-row-label {
  color: var(--dim); white-space: nowrap;
  font-size: 0.6rem; letter-spacing: 0.18em; text-transform: uppercase;
  font-family: 'DM Mono', monospace;
  transition: color 0.15s;
}
.filter-row.has-selection .filter-row-label,
.filter-row.open .filter-row-label { color: var(--accent); }
.filter-row-chevron {
  font-size: 0.55rem; color: var(--dim); flex-shrink: 0;
  transition: transform 0.2s; margin-left: auto;
}
.filter-row.open .filter-row-chevron { transform: rotate(180deg); color: var(--accent); }

/* Per-group permanent label colours */
.frow-register   .filter-row-label,
.frow-register   .filter-row-chevron { color: rgba(20,140,80,0.7); }
.frow-connotation .filter-row-label,
.frow-connotation .filter-row-chevron { color: rgba(154,104,0,0.7); }
.frow-channel    .filter-row-label,
.frow-channel    .filter-row-chevron { color: rgba(140,100,8,0.7); }
.frow-dimension  .filter-row-label,
.frow-dimension  .filter-row-chevron { color: rgba(60,80,180,0.7); }
.frow-intensity  .filter-row-label,
.frow-intensity  .filter-row-chevron { color: rgba(180,60,120,0.7); }
.frow-tocfl      .filter-row-label,
.frow-tocfl      .filter-row-chevron { color: rgba(140,100,8,0.7); }

/* Full strength on open/active */
.frow-register.open   .filter-row-label, .frow-register.has-selection   .filter-row-label,
.frow-register.open   .filter-row-chevron { color: #148c50; }
.frow-connotation.open .filter-row-label, .frow-connotation.has-selection .filter-row-label,
.frow-connotation.open .filter-row-chevron { color: #9a6800; }
.frow-channel.open    .filter-row-label, .frow-channel.has-selection    .filter-row-label,
.frow-channel.open    .filter-row-chevron { color: var(--gold); }
.frow-dimension.open  .filter-row-label, .frow-dimension.has-selection  .filter-row-label,
.frow-dimension.open  .filter-row-chevron { color: #3c50b4; }
.frow-intensity.open  .filter-row-label, .frow-intensity.has-selection  .filter-row-label,
.frow-intensity.open  .filter-row-chevron { color: #a03070; }
.frow-tocfl.open      .filter-row-label, .frow-tocfl.has-selection      .filter-row-label,
.frow-tocfl.open      .filter-row-chevron { color: var(--gold); }

/* Active tags under the trigger */
.filter-row-preview {
  display: flex; flex-direction: column; gap: 0.2rem;
  padding: 0 0.65rem 0.6rem;
  min-height: 0;
}
.filter-row-preview:empty { display: none; }
.preview-all { display: none; } /* hidden in horizontal mode */
.preview-text {
  font-size: 0.78rem; color: var(--accent); letter-spacing: 0.03em;
  font-family: 'DM Mono', monospace;
  background: var(--tag-bg); border: 1px solid rgba(155,127,240,0.3);
  border-radius: 2px; padding: 0.2rem 0.5rem;
  white-space: nowrap; cursor: pointer;
  transition: opacity 0.15s, background 0.15s;
  display: inline-flex; align-items: center; gap: 0.25rem;
}
.preview-text:hover { opacity: 0.7; }
.preview-text::after { content: '✕'; font-size: 0.5rem; opacity: 0.45; margin-left: 0.2rem; }
.preview-icon { font-size: 1.1rem; line-height: 1; }

/* Reset button in filter bar */
.filter-bar-reset {
  display: flex; align-items: center;
  padding: 0 0.75rem;
  margin-left: auto;
  flex-shrink: 0;
}

/* ── FILTER DROPDOWN ── */
.filter-dropdown {
  position: absolute; left: 0; top: 100%;
  z-index: 1000;
  background: #fff;
  border: 1px solid var(--border-active);
  border-radius: 4px;
  box-shadow: 0 8px 28px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.08);
  padding: 0.75rem;
  min-width: 210px;
  display: none;
  flex-direction: column; gap: 0.35rem;
  animation: dropIn 0.15s ease;
}
.filter-dropdown.open { display: flex; }

@keyframes dropIn {
  from { opacity: 0; transform: translateY(-4px); }
  to   { opacity: 1; transform: translateY(0); }
}

.dropdown-header {
  font-size: 0.55rem; letter-spacing: 0.3em; text-transform: uppercase;
  color: var(--dim); padding-bottom: 0.4rem;
  border-bottom: 1px solid var(--border);
  margin-bottom: 0.15rem;
}
.dropdown-hint {
  font-size: 0.55rem; color: var(--dim); font-style: italic;
  padding-top: 0.35rem; border-top: 1px solid var(--border);
  margin-top: 0.1rem;
}
.dropdown-chips {
  display: flex; flex-direction: column; gap: 0.25rem;
}
.d-chip {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0.65rem 0.8rem; border-radius: 4px;
  border: 1px solid var(--border); cursor: pointer;
  font-size: 0.81rem;
  font-family: 'DM Mono', monospace; font-size: 0.78rem;
  color: var(--text); background: transparent;
  transition: all 0.14s; user-select: none;
  min-height: 64px;
}
/* chip-icon sized in .chip-icon rule */
.d-chip:hover    { border-color: rgba(98,64,200,0.2); background: rgba(155,127,240,0.05); color: var(--accent); }
.d-chip.selected { border-color: var(--accent); background: var(--tag-bg); color: var(--accent); font-weight: 500; }
.d-chip.selected::after { content: '✓'; margin-left: auto; font-size: 0.65rem; opacity: 0.7; }
.chip-icon {
  display: inline-flex; align-items: center; justify-content: center;
  width: 3.2rem; height: 3.2rem; flex-shrink: 0;
  font-size: 2.3rem; line-height: 1; overflow: hidden;
}

/* Connotation chip colour overrides — weather palette */
.d-chip[data-val="positive"]:hover    { border-color: rgba(232,160,32,0.25); color: #b07800; background: rgba(232,160,32,0.04); }
.d-chip[data-val="positive"].selected { border-color: #e8a020; color: #b07800; background: rgba(232,160,32,0.12); }
.d-chip[data-val="positive"].selected::after { color: #b07800; }
.d-chip[data-val="neutral"]:hover     { border-color: rgba(112,144,176,0.25); color: #4a6880; background: rgba(112,144,176,0.04); }
.d-chip[data-val="neutral"].selected  { border-color: #7090b0; color: #4a6880; background: rgba(112,144,176,0.12); }
.d-chip[data-val="negative"]:hover    { border-color: rgba(80,96,160,0.25); color: #3a4880; background: rgba(80,96,160,0.04); }
.d-chip[data-val="negative"].selected { border-color: #5060a0; color: #3a4880; background: rgba(80,96,160,0.14); }
.d-chip[data-val="context-dependent"]:hover    { border-color: rgba(96,160,112,0.25); color: #3a7850; background: rgba(96,160,112,0.04); }
.d-chip[data-val="context-dependent"].selected { border-color: #60a070; color: #3a7850; background: rgba(96,160,112,0.12); }

/* Channel chip colour overrides */
.d-chip[data-group="channel"]:hover    { border-color: rgba(160,114,10,0.2); color: var(--gold); background: rgba(160,114,10,0.04); }
.d-chip[data-group="channel"].selected { border-color: var(--gold); color: var(--gold); background: rgba(160,114,10,0.1); }

/* Reset */
.reset-btn {
  margin-top: auto; padding: 0.55rem; border-radius: 2px;
  border: 1px solid var(--border); cursor: pointer;
  font-family: 'DM Mono', monospace; font-size: 0.58rem;
  letter-spacing: 0.15em; text-transform: uppercase;
  color: var(--dim); background: transparent;
  transition: all 0.2s;
}
.reset-btn:hover { border-color: rgba(0,0,0,0.3); color: var(--text); }

/* ── INTENSITY CHIPS — inside dropdown ── */
.int-char-sm {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 1.05rem; line-height: 1;
  min-width: 1.2rem; text-align: center;
}

/* Warming colour progression on selected */
/* Intensity chip colours — always visible, deepen on selected */
.d-chip-intensity[data-val="1"] .chip-icon { color: #e8a0b0; }
.d-chip-intensity[data-val="2"] .chip-icon { color: #c8a820; }
.d-chip-intensity[data-val="3"] .chip-icon { color: #c060a0; }
.d-chip-intensity[data-val="4"] .chip-icon { color: #c88010; }
.d-chip-intensity[data-val="5"] .chip-icon { color: #c83020; }



.d-chip-intensity[data-val="1"].selected { border-color: #e8a0b0; background: rgba(232,160,176,0.12); opacity: 1; }
.d-chip-intensity[data-val="2"].selected { border-color: #c8a820; background: rgba(200,168,32,0.1);   opacity: 1; }
.d-chip-intensity[data-val="3"].selected { border-color: #c060a0; background: rgba(192,96,160,0.1);   opacity: 1; }
.d-chip-intensity[data-val="4"].selected { border-color: #c88010; background: rgba(200,128,16,0.1);   opacity: 1; box-shadow: 0 0 6px rgba(200,128,16,0.15); }
.d-chip-intensity[data-val="5"].selected { border-color: #c83020; background: rgba(200,48,32,0.12);   opacity: 1; box-shadow: 0 0 8px rgba(200,48,32,0.2); }


/* ── UI MODE SELECTOR ── */
/* ── Accordion header ───────────────────────────── */
.acc-header {
  display: flex; align-items: stretch;
  background: #e4e1f0;
  border-bottom: 1px solid var(--border);
  position: relative; z-index: 20;
}
.acc-tab {
  flex: 1; display: flex; align-items: center; justify-content: center;
  gap: 0.45rem; padding: 0.65rem 1rem;
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--dim); background: transparent; border: none; cursor: pointer;
  border-right: 1px solid var(--border);
  transition: background 0.15s, color 0.15s;
}
.acc-tab:last-child { border-right: none; }
.acc-tab:hover { background: rgba(98,64,200,0.06); color: var(--text); }
.acc-tab.open { background: white; color: var(--accent); font-weight: 500; }
.acc-tab-icon { font-size: 1rem; line-height: 1; }
.acc-arrow { font-size: 0.7rem; transition: transform 0.2s; display: inline-block; }
.acc-tab.open .acc-arrow { transform: rotate(180deg); }

/* ── Accordion panels ───────────────────────────── */
.acc-panel {
  max-height: 0; overflow: hidden;
  background: white;
  border-bottom: 0px solid var(--border);
  transition: max-height 0.28s ease, border-bottom-width 0.28s;
  position: relative; z-index: 19;
}
.acc-panel.open {
  max-height: 900px;
  border-bottom: 1px solid var(--border);
}
.acc-panel-inner {
  padding: 1.4rem 2rem 1.2rem;
}

/* ── Scenario grid ──────────────────────────────── */
.scenario-grid {
  display: flex; flex-wrap: wrap; gap: 0.6rem;
}
.scenario-card {
  display: flex; flex-direction: column; align-items: flex-start;
  gap: 0.2rem; padding: 0.7rem 0.9rem;
  flex: 1 1 calc(50% - 0.3rem); /* 2 per row on mobile; lone last card fills full width */
  background: var(--light); border: 1px solid var(--border);
  border-radius: 4px; cursor: pointer; text-align: left;
  transition: border-color 0.15s, background 0.15s;
}
.scenario-card:hover { border-color: var(--accent); background: rgba(98,64,200,0.04); }
.scenario-card.active { border-color: var(--accent); background: rgba(98,64,200,0.07); }
.sc-icon { font-size: 1.4rem; line-height: 1; }
.sc-name { font-family: 'DM Mono', monospace; font-size: 0.72rem; font-weight: 500; color: var(--text); letter-spacing: 0.04em; }
.sc-desc { font-family: 'Cormorant Garamond', serif; font-size: 0.85rem; color: var(--dim); line-height: 1.35; }
.sc-save { border-style: dashed; }
.acc-clear-btn {
  margin-top: 0.9rem; padding: 0.3rem 0.8rem;
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  color: var(--dim); background: transparent;
  border: 1px solid var(--border); border-radius: 2px; cursor: pointer;
  transition: color 0.15s, border-color 0.15s;
}
.acc-clear-btn:hover { color: var(--text); border-color: var(--dim); }

/* ── Level grid ─────────────────────────────────── */
.level-grid {
  display: flex; gap: 0.6rem; flex-wrap: wrap;
}
.level-card {
  display: flex; flex-direction: column; align-items: flex-start;
  gap: 0.25rem; padding: 0.75rem 1rem;
  flex: 1 1 calc(50% - 0.3rem); /* 2 per row on mobile; lone last card fills full width */
  background: var(--light); border: 1px solid var(--border);
  border-radius: 4px; cursor: pointer; text-align: left;
  transition: border-color 0.15s, background 0.15s;
}
.level-card:hover { border-color: var(--accent); background: rgba(98,64,200,0.04); }
.level-card.active { border-color: var(--accent); background: rgba(98,64,200,0.07); font-weight: 500; }
.lv-icon { font-size: 1.5rem; line-height: 1; }
.lv-name { font-family: 'DM Mono', monospace; font-size: 0.72rem; font-weight: 500; color: var(--text); letter-spacing: 0.04em; }
.lv-zh { font-family: 'BiauKai','STKaiti','KaiTi', serif; font-size: 0.85rem; letter-spacing: 0; font-weight: 300; }
.lv-desc { font-family: 'Cormorant Garamond', serif; font-size: 0.85rem; color: var(--dim); line-height: 1.3; }

/* ── Interface grid ─────────────────────────────── */
.iface-grid {
  display: flex; flex-wrap: wrap; gap: 1.4rem 2.4rem; align-items: flex-start;
}
.iface-group {
  display: flex; flex-direction: column; gap: 0.4rem; align-items: flex-start;
}
.iface-group-label {
  font-family: 'DM Mono', monospace; font-size: 0.62rem;
  letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--dim);
}
.iface-hint {
  font-family: 'Cormorant Garamond', serif; font-size: 0.82rem;
  color: var(--dim); max-width: 220px; line-height: 1.3;
}

/* keep old ui-mode-bar/scenario-bar selectors harmless if referenced elsewhere */
.scenario-bar { display: none; }
.ui-mode-bar { display: none; }
.ui-mode-label {
  font-size: 0.62rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--dim); margin-right: 0.3rem; white-space: nowrap;
}
.mode-select {
  padding: 0.35rem 2rem 0.35rem 0.7rem;
  border-radius: 2px;
  border: 1px solid var(--border);
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  color: var(--text); background: white; cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23999'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.6rem center;
  transition: border-color 0.18s;
}
.script-toggle {
  display: inline-flex; border-radius: 3px; overflow: hidden;
  border: 1px solid var(--border); flex-shrink: 0;
  position: relative; /* for sliding pill */
}
/* Sliding pill — JS positions it under the active button */
.script-toggle-pill {
  position: absolute; top: 0; height: 100%;
  background: var(--accent); border-radius: 2px;
  transition: left 0.2s cubic-bezier(0.4, 0, 0.2, 1),
              width 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  pointer-events: none;
}
.script-btn {
  padding: 0.35rem 0.7rem;
  font-family: 'DM Mono', monospace;
  font-size: 0.68rem; letter-spacing: 0.04em;
  cursor: pointer; border: none;
  background: transparent; color: var(--dim);
  transition: color 0.18s ease;
  line-height: 1; white-space: nowrap;
  position: relative; z-index: 1;
}
#posToggle .script-btn,
#langToggle .script-btn,
#iconsToggle .script-btn {
  font-family: 'DM Mono', 'Noto Serif TC', monospace;
  font-size: 0.72rem;
  letter-spacing: 0.02em;
  padding: 0.35rem 0.6rem;
}
.script-btn.active { color: white; }
.script-btn:not(.active):hover { background: rgba(98,64,200,0.06); }
.mode-select:focus { outline: none; border-color: var(--gold); }
.mode-select:hover { border-color: rgba(0,0,0,0.3); }
.font-size-control {
  display: flex; align-items: center; gap: 0.35rem;
  border-left: 1px solid var(--border); padding-left: 0.8rem;
}
.font-btn {
  padding: 0.3rem 0.55rem; border-radius: 2px;
  border: 1px solid var(--border); cursor: pointer;
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  color: var(--text); background: transparent;
  transition: all 0.15s;
}
.font-btn:hover { border-color: var(--accent); color: var(--accent); background: var(--tag-bg); }
.font-val {
  font-size: 0.62rem; color: var(--dim); min-width: 36px;
  text-align: center; font-family: 'DM Mono', monospace;
}

/* Reset */
.reset-btn {
  margin-top: auto; padding: 0.6rem; border-radius: 2px;
  border: 1px solid var(--border); cursor: pointer;
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  letter-spacing: 0.15em; text-transform: uppercase;
  color: var(--dim); background: transparent;
  transition: all 0.2s;
}
.reset-btn:hover { border-color: rgba(255,255,255,0.2); color: var(--text); }

/* ── FILTER BAR — PER-GROUP COLOUR REINFORCEMENT ─────────────────────────── */
/* Register — forest green */
.frow-register                                   { background: rgba(20,140,80,0.04); }
.frow-register:hover                             { background: rgba(20,140,80,0.08); }
.frow-register.has-selection,
.frow-register.open                              { background: rgba(20,140,80,0.12); }
.frow-register.has-selection .filter-row-label,
.frow-register.open .filter-row-label            { color: #148c50; }
.frow-register.has-selection .filter-row-chevron,
.frow-register.open .filter-row-chevron          { color: #148c50; }
.frow-register .preview-text                     { background: rgba(20,140,80,0.08); border-color: rgba(20,140,80,0.2); color: #148c50; }
.frow-register .d-chip:hover                     { background: rgba(20,140,80,0.04); border-color: rgba(20,140,80,0.25); color: #148c50; }
.frow-register .d-chip.selected                  { background: rgba(20,140,80,0.12); border-color: #148c50; color: #148c50; }

/* Connotation — amber */
.frow-connotation                                { background: rgba(200,140,20,0.05); }
.frow-connotation:hover                              { background: rgba(200,140,20,0.09); }
.frow-connotation.has-selection,
.frow-connotation.open                               { background: rgba(200,140,20,0.13); }
.frow-connotation.has-selection .filter-row-label,
.frow-connotation.open .filter-row-label             { color: #9a6800; }
.frow-connotation.has-selection .filter-row-chevron,
.frow-connotation.open .filter-row-chevron           { color: #9a6800; }
.frow-connotation .preview-text                      { background: rgba(200,140,20,0.08); border-color: rgba(200,140,20,0.25); color: #9a6800; }

/* Channel — gold */
.frow-channel                                    { background: rgba(160,114,10,0.05); }
.frow-channel:hover                              { background: rgba(160,114,10,0.09); }
.frow-channel.has-selection,
.frow-channel.open                               { background: rgba(160,114,10,0.13); }
.frow-channel.has-selection .filter-row-label,
.frow-channel.open .filter-row-label             { color: var(--gold); }
.frow-channel.has-selection .filter-row-chevron,
.frow-channel.open .filter-row-chevron           { color: var(--gold); }
.frow-channel .preview-text                      { background: rgba(160,114,10,0.08); border-color: rgba(160,114,10,0.25); color: var(--gold); }
.frow-channel .d-chip:hover                      { background: rgba(160,114,10,0.04); border-color: rgba(160,114,10,0.25); color: var(--gold); }
.frow-channel .d-chip.selected                   { background: rgba(160,114,10,0.1);  border-color: var(--gold); color: var(--gold); }

/* Dimension — blue */
.frow-dimension                                  { background: rgba(60,80,180,0.05); }
.frow-dimension:hover                              { background: rgba(60,80,180,0.09); }
.frow-dimension.has-selection,
.frow-dimension.open                               { background: rgba(60,80,180,0.13); }
.frow-dimension.has-selection .filter-row-label,
.frow-dimension.open .filter-row-label             { color: #3c50b4; }
.frow-dimension.has-selection .filter-row-chevron,
.frow-dimension.open .filter-row-chevron           { color: #3c50b4; }
.frow-dimension .preview-text                      { background: rgba(60,80,180,0.08); border-color: rgba(60,80,180,0.25); color: #3c50b4; }
.frow-dimension .d-chip:hover                      { background: rgba(60,80,180,0.04); border-color: rgba(60,80,180,0.25); color: #3c50b4; }
.frow-dimension .d-chip.selected                   { background: rgba(60,80,180,0.1);  border-color: #3c50b4; color: #3c50b4; }

/* Intensity — rose */
.frow-intensity                                  { background: rgba(180,60,120,0.05); }
.frow-intensity:hover                              { background: rgba(180,60,120,0.09); }
.frow-intensity.has-selection,
.frow-intensity.open                               { background: rgba(180,60,120,0.13); }
.frow-intensity.has-selection .filter-row-label,
.frow-intensity.open .filter-row-label             { color: #a03070; }
.frow-intensity.has-selection .filter-row-chevron,
.frow-intensity.open .filter-row-chevron           { color: #a03070; }
.frow-intensity .preview-text                      { background: rgba(180,60,120,0.08); border-color: rgba(180,60,120,0.25); color: #a03070; }


/* TOCFL — gold */
.frow-tocfl                                      { background: rgba(160,114,10,0.05); }
.frow-tocfl:hover                              { background: rgba(160,114,10,0.09); }
.frow-tocfl.has-selection,
.frow-tocfl.open                               { background: rgba(160,114,10,0.13); }
.frow-tocfl.has-selection .filter-row-label,
.frow-tocfl.open .filter-row-label             { color: var(--gold); }
.frow-tocfl.has-selection .filter-row-chevron,
.frow-tocfl.open .filter-row-chevron           { color: var(--gold); }
.frow-tocfl .preview-text                      { background: rgba(160,114,10,0.08); border-color: rgba(160,114,10,0.25); color: var(--gold); }
.frow-tocfl .d-chip:hover                      { background: rgba(160,114,10,0.04); border-color: rgba(160,114,10,0.25); color: var(--gold); }
.frow-tocfl .d-chip.selected                   { background: rgba(160,114,10,0.1);  border-color: var(--gold); color: var(--gold); }

/* Domain — pill chip above definition, styled like POS */
.card-domain-row { margin-bottom: 0.3rem; }
.card-domain {
  display: inline-block;
  font-family: 'DM Mono', monospace;
  font-size: 0.81rem; letter-spacing: 0.04em;
  color: var(--gold); background: rgba(160,114,10,0.08);
  border: 1px solid rgba(160,114,10,0.28);
  border-radius: 2px; padding: 0.15rem 0.6rem;
  cursor: pointer; user-select: none;
  transition: background 0.15s, border-color 0.15s;
}
.card-domain:hover { background: rgba(160,114,10,0.15); border-color: rgba(160,114,10,0.5); }


.results-panel {
  padding: 1.5rem;
  display: flex; flex-direction: column; gap: 1rem;
}
.results-header {
  display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem;
  padding-bottom: 0.75rem; border-bottom: 1px solid var(--border);
}
.results-count {
  font-size: 0.62rem; color: var(--dim); letter-spacing: 0.15em;
}
.results-count strong { color: var(--accent); }

/* ── Results refine bar — secondary filters applied to current result set ── */
.results-refine {
  display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem 1rem;
  padding: 0.4rem 0;
}
.refine-select { flex: 1 1 100%; min-width: 0; max-width: 100%; }
.refine-label-row {
  display: flex; align-items: center; justify-content: space-between;
  flex: 1 1 100%; min-width: 0;
}
.refine-label {
  font-size: 0.55rem; font-family: 'DM Mono', monospace;
  letter-spacing: 0.12em; text-transform: uppercase; color: var(--dim);
  flex-shrink: 0;
}
.refine-select {
  font-family: 'DM Mono', monospace; font-size: 0.81rem;
  color: var(--text); background: var(--surface);
  border: 1px solid var(--border); border-radius: 2px;
  padding: 0.25rem 0.5rem; cursor: pointer; outline: none;
  transition: border-color 0.15s, color 0.15s;
}
.refine-select:focus,
.refine-select.active { border-color: #148c50; color: #148c50; }
.refine-reset {
  background: none; border: none; cursor: pointer;
  font-family: 'DM Mono', monospace; font-size: 0.55rem;
  letter-spacing: 0.08em; text-transform: uppercase;
  color: var(--dim); text-decoration: none;
  padding: 0; flex-shrink: 0;
  opacity: 0; pointer-events: none;
  transition: opacity 0.2s, color 0.18s;
}
.refine-reset.visible { opacity: 0.45; pointer-events: auto; }
@media (hover: hover) {
  .refine-reset:hover { opacity: 1; color: var(--accent); }
}
.active-filters {
  display: none;
}
.active-filter-tag {
  font-size: 1rem; padding: 0.35rem 0.8rem;
  background: var(--tag-bg); border: 1px solid rgba(155,127,240,0.25);
  border-radius: 2px; color: var(--accent);
  display: inline-flex; align-items: center; gap: 0.4rem;
}
.active-filter-tag.removable { cursor: pointer; transition: opacity 0.15s, background 0.15s; }
.active-filter-tag.removable:hover { opacity: 0.75; }
.tag-remove { font-size: 0.55rem; opacity: 0.5; margin-left: 0.2rem; }
.active-filter-tag .tag-icon { font-size: 1.6rem; line-height: 1; }

/* ── WORD CARDS ── */
.word-cards { display: flex; flex-direction: column; gap: 0.75rem; }

/* ── Back to top button ── */
#backToTop {
  position: fixed; bottom: 1.25rem; left: 50%;
  transform: translateX(-50%) translateY(8px);
  z-index: 900;
  background: none; border: none; cursor: pointer;
  color: var(--dim); font-size: 1.1rem; line-height: 1;
  padding: 0.35rem 0.75rem;
  opacity: 0; pointer-events: none;
  transition: opacity 0.3s ease, transform 0.3s ease, color 0.18s;
  letter-spacing: 0.06em;
  font-family: 'DM Mono', monospace;
}
#backToTop.visible { opacity: 0.32; pointer-events: auto; transform: translateX(-50%) translateY(0); }
@media (hover: hover) {
  #backToTop:hover { opacity: 0.72; color: var(--accent); }
}

.word-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 2px;
  padding: 1.2rem 1.4rem;
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 0 0.75rem;
  align-items: start;
  transition: border-color 0.2s, transform 0.15s;
  animation: cardIn 0.25s ease both;
}
@keyframes cardIn {
  from { opacity: 0; transform: translateY(6px); }
  to   { opacity: 1; transform: translateY(0); }
}
.word-card:hover { border-color: rgba(98,64,200,0.25); transform: translateY(-1px); }

.card-hanzi {
  display: flex; flex-direction: row; align-items: flex-start;
  gap: 0.4rem;
  /* border-right and padding-right applied on desktop only */
}
/* Wrapper: character stacked above the switch icon */
.hanzi-primary-wrap {
  display: flex; flex-direction: column; align-items: center; gap: 0.25rem;
}
/* ⇌ switch button — only rendered when trad ≠ simp */
.script-switch-btn {
  font-size: 1.2rem; font-family: 'DM Mono', monospace;
  color: var(--accent); opacity: 0.45;
  background: none; border: none; cursor: pointer;
  padding: 0.4rem 0; line-height: 1;
  transition: opacity 0.35s ease;
}
@media (hover: hover) {
  .script-switch-btn:hover { opacity: 0.85; transition: opacity 0.15s; }
}
/* Secondary character — slides in from the right */
.hanzi-secondary {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: calc(var(--fs-hanzi, 2.8rem) * 0.65);
  font-weight: 300; color: var(--dim); line-height: 1.1;
  writing-mode: vertical-rl; text-orientation: mixed; letter-spacing: 0.1em;
}
.hanzi-secondary.entering { animation: charSlideIn 0.22s ease forwards; }
.hanzi-secondary.leaving  { animation: charSlideOut 0.15s ease forwards; }
@keyframes charSlideIn {
  from { opacity: 0; transform: translateX(10px); }
  to   { opacity: 1; transform: translateX(0);    }
}
@keyframes charSlideOut {
  from { opacity: 1; transform: translateX(0);    }
  to   { opacity: 0; transform: translateX(10px); }
}

/* ── Mobile card header zones ── */
.card-hdr-mid {
  display: flex; flex-direction: column; gap: 0.4rem; min-width: 0;
}
.card-hdr-mid .card-domain-row { margin-bottom: 0; width: 100%; }
.card-hdr-mid .card-domain {
  display: block; width: 100%; text-align: center;
  font-size: 0.81rem;
  padding: 0.3rem 0.6rem;
}
.card-pos-summary { display: flex; flex-direction: column; gap: 0.3rem; }
/* Header POS label: always shows full · abbr, non-interactive, full-width */
.card-pos-hdr {
  display: block; width: 100%;
  font-family: 'DM Mono', monospace;
  font-size: 0.81rem; letter-spacing: 0.04em;
  color: #7060a8; background: rgba(98,64,200,0.07);
  border: 1px solid rgba(98,64,200,0.18);
  border-radius: 2px; padding: 0.15rem 0.6rem;
  cursor: pointer; user-select: none;
  transition: background 0.15s, border-color 0.15s;
}
.card-pos-hdr:hover { background: rgba(98,64,200,0.13); border-color: rgba(98,64,200,0.35); }
.card-divider {
  grid-column: 1 / -1;
  border: none; border-top: 1px solid var(--border);
  margin: 0.6rem 0 0.45rem;
}
.card-body { grid-column: 1 / -1; }
.card-meta { grid-column: 1 / -1; }

.hanzi-char {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: var(--fs-hanzi, 2.8rem); font-weight: 300;
  color: var(--ink); line-height: 1.1;
  writing-mode: vertical-rl; text-orientation: mixed;
  letter-spacing: 0.1em;
}
.hanzi-char.simp {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: var(--fs-simp, 1.4rem); font-weight: 300;
  color: var(--dim); line-height: 1.1;
  writing-mode: vertical-rl; text-orientation: mixed;
  letter-spacing: 0.1em;
}
.pinyin {
  font-style: italic; font-family: 'Cormorant Garamond', serif;
  font-size: var(--fs-pinyin, 1.05rem); color: var(--accent);
  letter-spacing: 0.05em;
}
.pinyin-h { writing-mode: horizontal-tb; }
/* Row wrapper so pinyin doesn't stretch full width in the flex column */
.card-pinyin-row { display: flex; align-items: center; margin-top: 0.15rem; }
/* Pinyin off — hide all pronunciation rows without re-rendering */
#cardContainer.no-pinyin .card-pinyin-row { display: none; }

.card-content { display: flex; flex-direction: column; }
.card-right {
  display: flex; flex-direction: column; gap: 0;
}
.card-body { display: flex; flex-direction: column; gap: 0.5rem; }
.card-def-row {
  display: block;
}
.card-def-row + .card-def-row { margin-top: 0.4rem; }
.card-pos {
  display: inline-block;
  margin-right: 0.45rem; vertical-align: baseline;
  font-family: 'DM Mono', monospace;
  font-size: 0.81rem; letter-spacing: 0.04em;
  color: #7060a8; background: rgba(98,64,200,0.07);
  border: 1px solid rgba(98,64,200,0.18);
  border-radius: 2px; padding: 1px 8px;
  cursor: pointer; user-select: none;
  transition: background 0.15s, border-color 0.15s;
}
.card-pos:hover { background: rgba(98,64,200,0.13); border-color: rgba(98,64,200,0.35); }
.card-pos[data-overridden] { border-style: dashed; }
.card-definition {
  font-family: 'Cormorant Garamond', serif;
  font-size: var(--fs-defn, 1.5rem); font-weight: 300;
  color: var(--ink);
  line-height: 1.4;
}
.card-usage-note {
  font-size: var(--fs-note, 0.9rem); color: var(--dim); line-height: 1.5;
}
.card-formula {
  font-size: var(--fs-formula, 1rem);
  background: rgba(98,64,200,0.05);
  border: 1px solid rgba(98,64,200,0.15);
  padding: 0.3rem 0.6rem; border-radius: 2px;
  color: var(--accent);
  font-family: 'DM Mono', monospace;
  display: inline-block; margin-top: 0.15rem;
}
.card-example {
  font-size: 0.85rem;
  padding-top: 0.3rem; border-top: 1px solid var(--border);
}
.card-example .ex-cn { font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif; font-size: var(--fs-ex-cn, 1.8rem); color: var(--text); }
.card-example .ex-en { font-style: italic; color: var(--dim); font-size: var(--fs-ex-en, 1rem); }

.card-meta {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.4rem;
  padding-top: 0.6rem; margin-top: 0.5rem;
  border-top: 1px solid var(--border);
}

/* Card action buttons — save + share */
.card-actions {
  grid-column: 1 / -1;
  display: flex; gap: 0.5rem;
  padding-top: 0.6rem; margin-top: 0.5rem;
  border-top: 1px solid var(--border);
}
.card-action-btn {
  flex: 1;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; color: var(--dim);
  background: none; border: 1px solid var(--border); border-radius: 2px;
  padding: 0.4rem 0.6rem; cursor: pointer;
  transition: color 0.18s, border-color 0.18s;
}
@media (hover: hover) {
  .card-action-btn:hover { color: var(--accent); border-color: var(--accent); }
}

/* Attribute chip: label header stacked above icon+value */
.card-attr {
  display: flex; flex-direction: column;
  border-radius: 3px; overflow: hidden;
  border: 1px solid var(--border);
}
.card-attr-header {
  font-size: 0.65rem; letter-spacing: 0.18em; text-transform: uppercase;
  font-family: 'DM Mono', monospace;
  padding: 0.25rem 0.55rem 0.2rem;
  border-bottom: 1px solid var(--border);
  white-space: nowrap;
}
.card-attr-value {
  display: flex; flex-direction: row; align-items: center; gap: 0.35rem;
  padding: 0.3rem 0.55rem;
  font-family: 'DM Mono', monospace; font-size: 0.82rem;
}
.card-attr-value .attr-icon { font-size: 1.1rem; line-height: 1; flex-shrink: 0; }
.card-attr-value.multi { flex-direction: column; align-items: flex-start; gap: 0.25rem; }
.attr-val-item { display: inline-flex; align-items: center; gap: 0.35rem; flex-shrink: 0; white-space: nowrap; }
.attr-val-sep { color: var(--dim); font-size: 0.65rem; margin: 0 0.1rem; }
.card-attr-value .attr-sep  { color: var(--dim); font-size: 0.65rem; }
.card-attr-value .attr-label { }
.card-attr { cursor: pointer; user-select: none; }
.card-attr:hover .card-attr-header { opacity: 0.72; }
.card-attr:hover .attr-label { opacity: 0.72; }

/* Per-attribute colours — header + value tinted together */
.card-attr.attr-register   { background: rgba(20,140,80,0.05);  border-color: rgba(20,140,80,0.2); }
.card-attr.attr-register   .card-attr-header { color: #148c50; background: rgba(20,140,80,0.08); border-color: rgba(20,140,80,0.15); }
.card-attr.attr-register   .card-attr-value  { color: #148c50; }

.card-attr.attr-connotation.conno-pos { background: rgba(232,160,32,0.07);  border-color: rgba(232,160,32,0.3); }
.card-attr.attr-connotation.conno-pos .card-attr-header { color: #8a6000; background: rgba(232,160,32,0.1); border-color: rgba(232,160,32,0.2); }
.card-attr.attr-connotation.conno-pos .card-attr-value  { color: #8a6000; }
.card-attr.attr-connotation.conno-neg { background: rgba(80,96,160,0.07);   border-color: rgba(80,96,160,0.3); }
.card-attr.attr-connotation.conno-neg .card-attr-header { color: #3a4880; background: rgba(80,96,160,0.1); border-color: rgba(80,96,160,0.2); }
.card-attr.attr-connotation.conno-neg .card-attr-value  { color: #3a4880; }
.card-attr.attr-connotation.conno-neu { background: rgba(112,144,176,0.07); border-color: rgba(112,144,176,0.3); }
.card-attr.attr-connotation.conno-neu .card-attr-header { color: #4a6880; background: rgba(112,144,176,0.1); border-color: rgba(112,144,176,0.2); }
.card-attr.attr-connotation.conno-neu .card-attr-value  { color: #4a6880; }
.card-attr.attr-connotation.conno-ctx { background: rgba(96,160,112,0.07);  border-color: rgba(96,160,112,0.3); }
.card-attr.attr-connotation.conno-ctx .card-attr-header { color: #3a7850; background: rgba(96,160,112,0.1); border-color: rgba(96,160,112,0.2); }
.card-attr.attr-connotation.conno-ctx .card-attr-value  { color: #3a7850; }

.card-attr.attr-channel   { background: rgba(160,114,10,0.05);  border-color: rgba(160,114,10,0.2); }
.card-attr.attr-channel   .card-attr-header { color: var(--gold); background: rgba(160,114,10,0.08); border-color: rgba(160,114,10,0.15); }
.card-attr.attr-channel   .card-attr-value  { color: var(--gold); }

.card-attr.attr-tocfl     { background: rgba(160,114,10,0.05);  border-color: rgba(160,114,10,0.2); }
.card-attr.attr-tocfl     .card-attr-header { color: var(--gold); background: rgba(160,114,10,0.08); border-color: rgba(160,114,10,0.15); }
.card-attr.attr-tocfl     .card-attr-value  { color: var(--gold); }

.card-attr.attr-dimension { background: rgba(60,80,180,0.05);   border-color: rgba(60,80,180,0.2); }
.card-attr.attr-dimension .card-attr-header { color: #3c50b4; background: rgba(60,80,180,0.08); border-color: rgba(60,80,180,0.15); }
.card-attr.attr-dimension .card-attr-value  { color: #3c50b4; }

.card-attr.attr-intensity { background: rgba(180,60,120,0.05);  border-color: rgba(180,60,120,0.2); }
.card-attr.attr-intensity .card-attr-header { color: #a03070; background: rgba(180,60,120,0.08); border-color: rgba(180,60,120,0.15); }
.card-attr.attr-intensity .card-attr-value  { color: #a03070; }


.word-card:hover::after {
  content: '↗ Open word page';
  position: absolute; top: 0.5rem; right: 0.75rem;
  font-size: 0.5rem; letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--accent); opacity: 0.6;
  pointer-events: none;
}
.word-card { position: relative; }

/* Level chips — compact horizontal layout */
.d-chip.level-chip {
  flex: none;
  padding: 0.35rem 0.5rem;
  font-size: 0.65rem;
  min-width: 0;
}

/* TOCFL chips — use Chinese character colour */
.d-chip[data-group="tocfl"].selected {
  border-color: var(--accent);
  background: var(--tag-bg);
  color: var(--accent);
}
.d-chip[data-group="tocfl"][data-val="advanced"].selected,
.d-chip[data-group="tocfl"][data-val="fluency"].selected {
  border-color: var(--jade);
  background: rgba(26,138,90,0.08);
  color: var(--jade);
}
.d-chip[data-group="tocfl"][data-val="advanced"].selected::after,
.d-chip[data-group="tocfl"][data-val="fluency"].selected::after { color: var(--jade); }

/* Empty state */
.empty-state {
  text-align: center; padding: 3rem 2rem;
  color: var(--dim); font-family: 'Cormorant Garamond', serif;
  font-style: italic; font-size: 1.1rem;
  border: 1px dashed rgba(0,0,0,0.1); border-radius: 2px;
}
.empty-state small { display: block; font-size: 0.6rem; font-style: normal; font-family: 'DM Mono', monospace; margin-top: 0.5rem; letter-spacing: 0.2em; }

/* ── 造句 SENTENCE WORKSHOP ── */
.zaoju-panel {
  grid-column: 1 / -1;
  margin: 0.75rem -1.4rem -1.2rem;
  background: rgba(98,64,200,0.04);
  border-top: 1px solid rgba(98,64,200,0.1);
  padding: 0.75rem 1.4rem 1.2rem;
  display: flex; flex-direction: column; gap: 0.75rem;
  border-radius: 0 0 2px 2px;
}
.zaoju-header {
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;
}
.zaoju-title {
  font-family: 'DM Mono', monospace;
  font-size: 0.81rem; color: var(--accent);
  letter-spacing: 0.08em;
}
.zaoju-toggle {
  font-size: 0.81rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--dim); cursor: pointer; background: none; border: none;
  font-family: 'DM Mono', monospace; padding: 0.2rem 0;
  transition: color 0.2s;
}
.zaoju-toggle:hover { color: var(--accent); }

/* Default sentences */
.default-sentences { display: flex; flex-direction: column; gap: 0.4rem; }
.default-sent {
  display: flex; align-items: flex-start; gap: 0.6rem;
  padding: 0.5rem 0.7rem;
  background: rgba(255,255,255,0.7);
  border: 1px solid rgba(98,64,200,0.08);
  border-radius: 2px;
  position: relative;
}
.sent-num { font-size: 0.55rem; color: var(--accent); opacity: 0.6; margin-top: 0.15rem; flex-shrink: 0; }
.sent-body { display: flex; flex-direction: column; gap: 0.15rem; flex: 1; }
.sent-cn { font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif; font-size: var(--fs-ex-cn, 1.8rem); color: var(--ink); line-height: 1.5; }
.sent-cn .highlight { color: var(--gold); font-weight: 600; }
.sent-en { font-size: var(--fs-ex-en, 1rem); color: var(--dim); font-style: italic; }
.save-sent-btn {
  font-size: 0.81rem; padding: 0.2rem 0.5rem;
  border: 1px solid rgba(62,180,137,0.25); border-radius: 2px;
  color: var(--jade); background: rgba(62,180,137,0.05);
  cursor: pointer; font-family: 'DM Mono', monospace;
  transition: all 0.2s; flex-shrink: 0; margin-top: 0.1rem;
  white-space: nowrap;
}
.save-sent-btn:hover { background: rgba(62,180,137,0.12); border-color: var(--jade); }
.save-sent-btn.saved { color: var(--dim); border-color: var(--border); cursor: default; }

/* AI tabs */
.ai-tabs {
  display: flex; gap: 0.4rem; flex-wrap: wrap;
}
.ai-tab {
  padding: 0.4rem 0.75rem; border-radius: 2px;
  border: 1px solid var(--border);
  font-family: 'DM Mono', monospace; font-size: 1rem;
  color: var(--dim); background: transparent; cursor: pointer;
  transition: all 0.18s;
}
.ai-tab:hover { border-color: rgba(255,255,255,0.2); color: var(--text); }
.ai-tab.active { border-color: var(--accent); background: var(--tag-bg); color: var(--accent); }

/* AI input areas */
.ai-workspace {
  display: flex; flex-direction: column; gap: 0.6rem;
  padding: 0.75rem;
  background: #f0eef8;
  border: 1px solid rgba(98,64,200,0.15);
  border-radius: 2px;
}
.ai-instruction {
  font-size: 0.81rem; color: var(--dim); line-height: 1.6;
}
.ai-input-row { display: flex; gap: 0.5rem; align-items: flex-start; flex-wrap: wrap; }
.ai-textarea {
  flex: 1; min-width: 200px;
  background: #ffffff;
  border: 1px solid rgba(98,64,200,0.25);
  color: var(--ink); font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 0.9rem; padding: 0.6rem 0.75rem;
  border-radius: 2px; outline: none; resize: vertical;
  min-height: 60px; line-height: 1.6;
  transition: border-color 0.2s;
}
.ai-textarea::placeholder { font-family: 'DM Mono', monospace; font-size: 0.65rem; color: rgba(26,24,40,0.3); }
.ai-textarea:focus { border-color: var(--accent); }
.ai-theme-input {
  flex: 1; min-width: 160px;
  background: #ffffff;
  border: 1px solid rgba(98,64,200,0.25);
  color: var(--ink); font-family: 'DM Mono', monospace;
  font-size: 0.72rem; padding: 0.5rem 0.75rem;
  border-radius: 2px; outline: none;
  transition: border-color 0.2s;
}
.ai-theme-input::placeholder { color: rgba(26,24,40,0.3); }
.ai-theme-input:focus { border-color: var(--accent); }
.ai-submit-btn {
  padding: 0.5rem 1rem; border-radius: 2px;
  border: 1px solid var(--accent);
  background: var(--tag-bg); color: var(--accent);
  font-family: 'DM Mono', monospace; font-size: 0.81rem;
  letter-spacing: 0.1em; cursor: pointer;
  transition: all 0.2s; white-space: nowrap;
}
.ai-submit-btn:hover { background: rgba(155,127,240,0.2); }
.ai-submit-btn:disabled { opacity: 0.4; cursor: wait; }

/* AI response */
.ai-response {
  padding: 0.65rem 0.75rem;
  background: rgba(98,64,200,0.04);
  border: 1px solid rgba(98,64,200,0.15);
  border-radius: 2px;
  display: flex; flex-direction: column; gap: 0.4rem;
  animation: cardIn 0.2s ease both;
}
.ai-response-label {
  font-size: 0.55rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--accent);
}
.ai-response-text {
  font-size: 0.72rem; color: var(--text); line-height: 1.7;
}
.ai-response-text .resp-cn { font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif; font-size: 0.95rem; color: var(--ink); display: block; margin-bottom: 0.2rem; }
.ai-response-text .resp-cn .highlight { color: var(--gold); font-weight: 600; }
.ai-response-text .resp-en { color: var(--dim); font-style: italic; font-size: 0.65rem; display: block; margin-bottom: 0.3rem; }
.ai-response-text .resp-note { color: var(--dim); font-size: 0.65rem; border-top: 1px solid var(--border); padding-top: 0.3rem; margin-top: 0.1rem; }
.ai-response-actions { display: flex; gap: 0.4rem; flex-wrap: wrap; }

/* Saved deck */
.saved-deck-section {
  margin-top: 0.5rem;
  padding: 0.6rem 0.75rem;
  background: rgba(26,138,90,0.04);
  border: 1px solid rgba(26,138,90,0.2);
  border-radius: 2px;
}
.saved-deck-label {
  font-size: 0.55rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--jade); margin-bottom: 0.4rem; display: block;
}
.saved-item {
  display: flex; justify-content: space-between; align-items: flex-start;
  gap: 0.5rem; padding: 0.3rem 0;
  border-bottom: 1px solid rgba(26,138,90,0.1);
  font-size: 0.72rem;
}
.saved-item:last-child { border-bottom: none; }
.saved-item-cn { font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif; font-size: 0.85rem; color: var(--ink); }
.saved-item-source { font-size: 0.55rem; color: var(--dim); white-space: nowrap; }
.remove-btn {
  font-size: 0.55rem; color: var(--dim); cursor: pointer;
  background: none; border: none; padding: 0 0.2rem;
  transition: color 0.2s;
}
.remove-btn:hover { color: var(--rose); }

/* ── PROFILE PRESETS ── */
/* profiles-bar removed — merged into scenario-bar */

/* ── RESPONSIVE ── */
@media (max-width: 700px) {
  main { grid-template-columns: 1fr; }

  /* Filter bar: 2 columns, 3 rows of 2, reset full-width 7th */
  .filter-bar { grid-template-columns: repeat(2, 1fr); }
  .filter-row  { border-bottom: 1px solid var(--border); }
  /* Restore TOCFL right border (overridden from desktop), then remove even cols */
  .filter-bar .filter-row:nth-child(6) { border-right: 1px solid var(--border); }
  .filter-bar .filter-row:nth-child(2n) { border-right: none; }
  /* Last row (5th + 6th) gets no bottom border */
  .filter-bar .filter-row:nth-child(n+5) { border-bottom: none; }
  /* Reset button spans full width below the 6 */
  .filter-reset-btn {
    grid-column: 1 / -1;
    border-left: none; border-top: 1px solid var(--border);
    padding: 0.65rem;
  }

  /* Refine bar: selects stack and each takes full row width */
  .results-refine { gap: 0.4rem; }
  .refine-select { flex: 1 1 100%; min-width: 0; }
  .refine-label { width: 100%; }

  /* Results panel tighter on small screens */
  .results-panel { padding: 1rem; }

  /* Sentence workshop tabs: full-width stacked */
  .ai-tabs { flex-direction: column; }
  .ai-tab  { width: 100%; text-align: center; }
}

@media (min-width: 701px) {
  /* Scenario + Level chips: fluid on desktop (3-4 per row naturally) */
  .scenario-card,
  .level-card {
    flex: 1 1 180px;
    max-width: 240px;
  }

  /* ── Desktop word card: revert to 2-col layout ── */
  .word-card {
    grid-template-columns: auto 1fr;
    gap: 0 1.2rem;
  }
  .card-hanzi {
    grid-column: 1; grid-row: 1 / 4;  /* span hdr-mid + body + meta rows */
    border-right: 1px solid var(--border);
    padding-right: 1.2rem;
  }
  .card-hdr-mid    { grid-column: 2; grid-row: 1; }
  .card-body       { grid-column: 2; grid-row: 2; }
  .card-meta       { grid-column: 2; grid-row: 3; grid-template-columns: repeat(3, 1fr); }
  .card-divider    { display: none; }
  /* Restore domain chip to normal inline size */
  .card-hdr-mid .card-domain {
    display: inline-block; width: auto;
    font-size: 0.81rem; padding: 0.15rem 0.6rem; text-align: left;
  }
  .card-hdr-mid .card-domain-row { margin-bottom: 0.3rem; }
  .card-pos-summary { display: none; } /* POS shown inline with definitions on desktop */
}
</style>
</head>
<body>

<header>
  <div class="logo-tag">流動 · Living Lexicon</div>
  <input
    id="searchInput"
    type="text"
    placeholder="Search 流動…"
    style="font-family:'DM Mono',monospace;font-size:0.78rem;padding:0.4rem 0.8rem;border:1px solid rgba(0,0,0,0.15);border-radius:2px;width:min(320px,calc(100vw - 3rem));background:var(--surface);color:var(--text);outline:none;"
    oninput="searchQuery=this.value;render();"
  />
</header>

<!-- SCENARIO PRESETS -->
<!-- SCENARIO BAR -->
<!-- SAVE NAME DIALOG -->
<div class="scenario-dialog-overlay" id="scenarioDialogOverlay">
  <div class="scenario-dialog">
    <div class="scenario-dialog-title">Name this scenario</div>
    <input class="scenario-dialog-input" id="scenarioNameInput" type="text" placeholder="e.g. Exam prep · Poetry · Travel…" maxlength="40">
    <div class="scenario-dialog-btns">
      <button class="scenario-dialog-cancel" onclick="closeSaveDialog()">Cancel</button>
      <button class="scenario-dialog-confirm" onclick="confirmSaveScenario()">Save</button>
    </div>
  </div>
</div>

<!-- ACCORDION HEADER -->
<div class="acc-header">
  <button class="acc-tab" id="accTabScenario" onclick="toggleAccordion('scenario')">
    Scenario <span class="acc-arrow" id="accArrowScenario">▾</span>
  </button>
  <button class="acc-tab" id="accTabLevel" onclick="toggleAccordion('level')">
    Level <span class="acc-arrow" id="accArrowLevel">▾</span>
  </button>
  <button class="acc-tab" id="accTabInterface" onclick="toggleAccordion('interface')">
    Interface <span class="acc-arrow" id="accArrowInterface">▾</span>
  </button>
</div>

<!-- SCENARIO PANEL -->
<div class="acc-panel" id="accPanelScenario">
  <div class="acc-panel-inner">
    <div class="scenario-grid">
      <button class="scenario-card" onclick="applyScenarioPreset('beginner')">
        <span class="sc-icon">🌱</span>
        <span class="sc-name">The Beginner</span>
        <span class="sc-desc">Everyday spoken words, positive tone, prep-level TOCFL</span>
      </button>
      <button class="scenario-card" onclick="applyScenarioPreset('exchange')">
        <span class="sc-icon">🗣️</span>
        <span class="sc-name">Language Exchange</span>
        <span class="sc-desc">Colloquial, neutral, spoken — natural conversation words</span>
      </button>
      <button class="scenario-card" onclick="applyScenarioPreset('essay')">
        <span class="sc-icon">✏️</span>
        <span class="sc-name">Essay Writing</span>
        <span class="sc-desc">Formal written register, basic TOCFL and above</span>
      </button>
      <button class="scenario-card" onclick="applyScenarioPreset('business')">
        <span class="sc-icon">💼</span>
        <span class="sc-name">Business</span>
        <span class="sc-desc">Formal, positive, spoken — confident professional register</span>
      </button>
      <button class="scenario-card" onclick="applyScenarioPreset('literature')">
        <span class="sc-icon">📚</span>
        <span class="sc-name">Classical Reading</span>
        <span class="sc-desc">Literary register, high-intensity written words</span>
      </button>
      <button class="scenario-card" onclick="applyScenarioPreset('classicist')">
        <span class="sc-icon">📜</span>
        <span class="sc-name">The Classicist</span>
        <span class="sc-desc">Literary, dark-toned, advanced — for prose and poetry</span>
      </button>
      <button class="scenario-card" onclick="applyScenarioPreset('creative')">
        <span class="sc-icon">🎨</span>
        <span class="sc-name">Creative Writing</span>
        <span class="sc-desc">Literary, any channel — expressive and stylistic words</span>
      </button>
      <button class="scenario-card sc-save" onclick="openSaveDialog()">
        <span class="sc-icon">＋</span>
        <span class="sc-name">Save Current</span>
        <span class="sc-desc">Save your active filter selection as a custom scenario</span>
      </button>
    </div>
    <div id="customScenariosGrid" class="scenario-grid" style="margin-top:0.75rem; display:none"></div>
    <button class="acc-clear-btn" onclick="clearScenario()">↺ Clear filters</button>
  </div>
</div>

<!-- LEVEL PANEL -->
<div class="acc-panel" id="accPanelLevel">
  <div class="acc-panel-inner">
    <div class="level-grid">
      <button class="level-card" id="lvlBtnBeginner"   onclick="setLevel('beginner')">
        <span class="lv-icon">🌱</span>
        <span class="lv-name">Beginner <span class="lv-zh">新手</span></span>
        <span class="lv-desc">Large characters, full pinyin, gentle pace</span>
      </button>
      <button class="level-card" id="lvlBtnLearner"    onclick="setLevel('learner')">
        <span class="lv-icon">🌿</span>
        <span class="lv-name">Learner <span class="lv-zh">學習者</span></span>
        <span class="lv-desc">Comfortable reading size with support</span>
      </button>
      <button class="level-card active" id="lvlBtnDeveloping" onclick="setLevel('developing')">
        <span class="lv-icon">🍃</span>
        <span class="lv-name">Developing <span class="lv-zh">進階</span></span>
        <span class="lv-desc">Balanced — the default working view</span>
      </button>
      <button class="level-card" id="lvlBtnAdvanced"   onclick="setLevel('advanced')">
        <span class="lv-icon">🌳</span>
        <span class="lv-name">Advanced <span class="lv-zh">流利</span></span>
        <span class="lv-desc">Denser layout for fluent readers</span>
      </button>
      <button class="level-card" id="lvlBtnNative"     onclick="setLevel('native')">
        <span class="lv-icon">🀄</span>
        <span class="lv-name">Native <span class="lv-zh">母語</span></span>
        <span class="lv-desc">Compact — minimal scaffolding, maximum density</span>
      </button>
    </div>
    <!-- hidden select kept for JS compatibility -->
    <select id="levelSelect" style="display:none">
      <option value="beginner">Beginner</option>
      <option value="learner">Learner</option>
      <option value="developing" selected>Developing</option>
      <option value="advanced">Advanced</option>
      <option value="native">Native</option>
    </select>
  </div>
</div>

<!-- INTERFACE PANEL -->
<div class="acc-panel" id="accPanelInterface">
  <div class="acc-panel-inner">
    <div class="iface-grid">

      <div class="iface-group">
        <div class="iface-group-label">Character Set</div>
        <div class="script-toggle" id="scriptToggle">
          <button class="script-btn active" id="btnTrad" onclick="setScript('traditional')">Traditional 繁</button>
          <button class="script-btn" id="btnSimp" onclick="setScript('simplified')">Simplified 簡</button>
        </div>
        <div class="iface-hint">Primary script displayed on each word card</div>
      </div>

      <div class="iface-group">
        <div class="iface-group-label">Part of Speech</div>
        <div class="script-toggle" id="posToggle">
          <button class="script-btn active" id="btnPosAbbr" onclick="setPosMode('abbr')">Vt · Vi · N</button>
          <button class="script-btn" id="btnPosFull" onclick="setPosMode('full')">Full description</button>
        </div>
        <div class="iface-hint">How grammar labels appear on each definition row</div>
      </div>

      <div class="iface-group">
        <div class="iface-group-label">Language</div>
        <div class="script-toggle" id="langToggle">
          <button class="script-btn active" id="btnLangEn"   onclick="setLangMode('en')">English</button>
          <button class="script-btn"        id="btnLangZh"   onclick="setLangMode('zh')">中文</button>
          <button class="script-btn"        id="btnLangBoth" onclick="setLangMode('both')">EN + 中文</button>
        </div>
        <div class="iface-hint">Language shown on filter chips and word card labels</div>
      </div>

      <div class="iface-group">
        <div class="iface-group-label">Icons</div>
        <div class="script-toggle" id="iconsToggle">
          <button class="script-btn active" id="btnIconsOn"  onclick="setIconsMode('on')">On</button>
          <button class="script-btn"        id="btnIconsOff" onclick="setIconsMode('off')">Off</button>
        </div>
        <div class="iface-hint">Nature emoji on filter chips and word card attribute tags</div>
      </div>

      <div class="iface-group">
        <div class="iface-group-label">Pinyin</div>
        <div class="script-toggle" id="pinyinToggle">
          <button class="script-btn active" id="btnPinyinOn"  onclick="setPinyinMode('on')">On</button>
          <button class="script-btn"        id="btnPinyinOff" onclick="setPinyinMode('off')">Off</button>
        </div>
        <div class="iface-hint">Hide pronunciation to train character recognition</div>
      </div>

      <div class="iface-group">
        <div class="iface-group-label">Workshop</div>
        <div class="script-toggle" id="workshopToggle">
          <button class="script-btn"        id="btnWorkshopExpanded"  onclick="setWorkshopDefault('expanded')">Expanded</button>
          <button class="script-btn active" id="btnWorkshopCollapsed" onclick="setWorkshopDefault('collapsed')">Collapsed</button>
        </div>
        <div class="iface-hint">Default state of 造句 Sentence Workshop on each card</div>
      </div>

      <div class="iface-group">
        <div class="iface-group-label">Font Size</div>
        <div class="font-size-control" style="border:none; padding:0">
          <button class="font-btn" id="fontDown">A−</button>
          <span class="font-val" id="fontVal">100%</span>
          <button class="font-btn" id="fontUp">A+</button>
        </div>
        <div class="iface-hint">Scales all text proportionally across the page</div>
      </div>

    </div>
  </div>
</div>

<!-- ATTRIBUTE FILTERS TOGGLE -->
<div class="attr-bar">
  <button class="attr-bar-tab" id="attrFilterTab" onclick="toggleAttrFilters()">
    Attribute Filters <span class="acc-arrow" id="attrFilterArrow">▾</span>
  </button>
</div>

<div id="attrFilterPanel" class="attr-filter-panel">
<div class="filter-bar">

  <!-- REGISTER -->
  <div class="filter-row frow-register" id="row-register" data-group="register">
    <div class="filter-row-trigger">
      <span class="filter-row-label">Register</span>
      <span class="filter-row-chevron">▾</span>
    </div>
    <div class="filter-row-preview" id="preview-register"></div>
    <div class="filter-dropdown" id="drop-register">
      <div class="dropdown-chips">
        <div class="d-chip" data-group="register" data-val="literary">🖌️ <span>文學體 Literary</span></div>
        <div class="d-chip" data-group="register" data-val="formal">🎀 <span>正式 Formal</span></div>
        <div class="d-chip" data-group="register" data-val="neutral">⚪ <span>標準 Standard</span></div>
        <div class="d-chip" data-group="register" data-val="colloquial">💬 <span>口語 Colloquial</span></div>
        <div class="d-chip" data-group="register" data-val="informal">🛋️ <span>非正式 Informal</span></div>
        <div class="d-chip" data-group="register" data-val="slang">🔥 <span>俚語 Slang</span></div>
      </div>
      <div class="dropdown-hint">點選篩選 · 再點取消</div>
    </div>
  </div>

  <!-- CONNOTATION -->
  <div class="filter-row frow-connotation" id="row-connotation" data-group="connotation">
    <div class="filter-row-trigger">
      <span class="filter-row-label">Connotation</span>
      <span class="filter-row-chevron">▾</span>
    </div>
    <div class="filter-row-preview" id="preview-connotation"></div>
    <div class="filter-dropdown" id="drop-connotation">
      <div class="dropdown-chips">
        <div class="d-chip" data-group="connotation" data-val="positive">＋ <span>褒義 Positive</span></div>
        <div class="d-chip" data-group="connotation" data-val="neutral">○ <span>中性 Neutral</span></div>
        <div class="d-chip" data-group="connotation" data-val="negative">− <span>貶義 Negative</span></div>
        <div class="d-chip" data-group="connotation" data-val="context-dependent">◈ <span>隨境 Context-Dependent</span></div>
      </div>
      <div class="dropdown-hint">可複選</div>
    </div>
  </div>

  <!-- CHANNEL -->
  <div class="filter-row frow-channel" id="row-channel" data-group="channel">
    <div class="filter-row-trigger">
      <span class="filter-row-label">Channel</span>
      <span class="filter-row-chevron">▾</span>
    </div>
    <div class="filter-row-preview" id="preview-channel"></div>
    <div class="filter-dropdown" id="drop-channel">
      <div class="dropdown-chips">
        <div class="d-chip" data-group="channel" data-val="spoken-only">🦎 <span>純口語 Spoken Only</span></div>
        <div class="d-chip" data-group="channel" data-val="spoken-dominant">🐍 <span>偏口語 Spoken Dominant</span></div>
        <div class="d-chip" data-group="channel" data-val="fluid">🦜 <span>流動 Fluid</span></div>
        <div class="d-chip" data-group="channel" data-val="written-dominant">🦚 <span>偏書面 Written Dominant</span></div>
        <div class="d-chip" data-group="channel" data-val="written-only">🐉 <span>純書面 Written Only</span></div>
      </div>
      <div class="dropdown-hint">可複選</div>
    </div>
  </div>

  <!-- DIMENSION -->
  <div class="filter-row frow-dimension" id="row-dimension" data-group="dimension">
    <div class="filter-row-trigger">
      <span class="filter-row-label">Dimension</span>
      <span class="filter-row-chevron">▾</span>
    </div>
    <div class="filter-row-preview" id="preview-dimension"></div>
    <div class="filter-dropdown" id="drop-dimension">
      <div class="dropdown-chips">
        <div class="d-chip" data-group="dimension" data-val="abstract">◇ <span>抽象 Abstract</span></div>
        <div class="d-chip" data-group="dimension" data-val="concrete">◆ <span>具體 Concrete</span></div>
        <div class="d-chip" data-group="dimension" data-val="internal">內 <span>內在 Internal</span></div>
        <div class="d-chip" data-group="dimension" data-val="external">外 <span>外在 External</span></div>
        <div class="d-chip" data-group="dimension" data-val="fluid">⇌ <span>流動 Fluid</span></div>
      </div>
      <div class="dropdown-hint">例：美 = 抽象 · 內在</div>
    </div>
  </div>

  <!-- INTENSITY -->
  <div class="filter-row frow-intensity" id="row-intensity" data-group="intensity">
    <div class="filter-row-trigger">
      <span class="filter-row-label">Intensity</span>
      <span class="filter-row-chevron">▾</span>
    </div>
    <div class="filter-row-preview" id="preview-intensity"></div>
    <div class="filter-dropdown" id="drop-intensity">
      <div class="dropdown-chips">
        <div class="d-chip d-chip-intensity" data-group="intensity" data-val="1"><span class="chip-icon">🌸</span><span>faint</span></div>
        <div class="d-chip d-chip-intensity" data-group="intensity" data-val="2"><span class="chip-icon">🌼</span><span>mild</span></div>
        <div class="d-chip d-chip-intensity" data-group="intensity" data-val="3"><span class="chip-icon">🪷</span><span>moderate</span></div>
        <div class="d-chip d-chip-intensity" data-group="intensity" data-val="4"><span class="chip-icon">🌻</span><span>strong</span></div>
        <div class="d-chip d-chip-intensity" data-group="intensity" data-val="5"><span class="chip-icon">🌺</span><span>blazing</span></div>
      </div>
      <div class="dropdown-hint">可複選 · 例：選濃·烈顯示高強度詞</div>
    </div>
  </div>

  <!-- TOCFL -->
  <div class="filter-row frow-tocfl" id="row-tocfl" data-group="tocfl">
    <div class="filter-row-trigger">
      <span class="filter-row-label">TOCFL</span>
      <span class="filter-row-chevron">▾</span>
    </div>
    <div class="filter-row-preview" id="preview-tocfl"></div>
    <div class="filter-dropdown" id="drop-tocfl">
      <div class="dropdown-chips">
        <div class="d-chip" data-group="tocfl" data-val="prep">🌑 準備 Prep</div>
        <div class="d-chip" data-group="tocfl" data-val="entry">🌒 入門 Entry</div>
        <div class="d-chip" data-group="tocfl" data-val="basic">🌓 基礎 Basic</div>
        <div class="d-chip" data-group="tocfl" data-val="intermediate">🌔 進階 Intermediate</div>
        <div class="d-chip" data-group="tocfl" data-val="advanced">🌕 高階 Advanced</div>
        <div class="d-chip" data-group="tocfl" data-val="high">🌖 精通 High</div>
        <div class="d-chip" data-group="tocfl" data-val="fluency">🌝 流利 Fluency</div>
      </div>
      <div class="dropdown-hint">可複選</div>
    </div>
  </div>


  <button class="filter-reset-btn" id="resetBtn">↺ Reset</button>

</div>
</div><!-- /attrFilterPanel -->

<main>
  <div class="results-panel">
    <div class="results-header">
      <div class="results-count">Showing <strong id="countNum">0</strong> words<span id="countQuery"></span></div>
      <div class="active-filters" id="activeTags"></div>
    </div>
    <div class="results-refine">
      <div class="refine-label-row">
        <span class="refine-label">Refine</span>
        <a class="refine-reset" id="refineReset" href="javascript:void(0)" onclick="resetRefineFilters()" translate="no">reset</a>
      </div>
      <select class="refine-select" id="posRefineSelect">
        <option value="">POS — all</option>
      </select>
      <select class="refine-select" id="relRefineSelect">
        <option value="">Relatives — all</option>
        <option value="immediate">Immediate</option>
        <option value="close">Close</option>
        <option value="distant">Distant</option>
      </select>
      <select class="refine-select" id="domainRefineSelect">
        <option value="">Domains — all</option>
      </select>
    </div>
    <div class="word-cards" id="cardContainer"></div>
  </div>
</main>

<script>
// ── UI MODE ───────────────────────────────────────────────────────────────────
let uiMode   = 'en-icon'; // derived — do not set directly
let langMode  = 'en';  // 'en' | 'zh' | 'both'
let iconsMode = 'on';  // 'on' | 'off'

function deriveUiMode() {
  if (iconsMode === 'on') {
    if (langMode === 'en')   return 'en-icon';
    if (langMode === 'zh')   return 'zh-icon';
    return 'all';
  } else {
    if (langMode === 'en')   return 'en-only';
    if (langMode === 'zh')   return 'zh-only';
    return 'en-zh';
  }
}

function updateTogglePill(toggleId) {
  const toggle = document.getElementById(toggleId);
  if (!toggle) return;
  let pill = toggle.querySelector('.script-toggle-pill');
  if (!pill) {
    pill = document.createElement('div');
    pill.className = 'script-toggle-pill';
    toggle.prepend(pill);
  }
  const active = toggle.querySelector('.script-btn.active');
  if (active) {
    pill.style.left  = active.offsetLeft + 'px';
    pill.style.width = active.offsetWidth + 'px';
  }
}

function setLangMode(mode) {
  langMode = mode;
  ['btnLangEn','btnLangZh','btnLangBoth'].forEach(id => {
    const btn = document.getElementById(id);
    if (btn) btn.classList.remove('active');
  });
  const map = { en: 'btnLangEn', zh: 'btnLangZh', both: 'btnLangBoth' };
  document.getElementById(map[mode])?.classList.add('active');
  uiMode = deriveUiMode();
  setSidebarWidth(uiMode);
  updateTogglePill('langToggle');
  rerenderLabels();
  render();
}

function setIconsMode(mode) {
  iconsMode = mode;
  document.getElementById('btnIconsOn')?.classList.toggle('active', mode === 'on');
  document.getElementById('btnIconsOff')?.classList.toggle('active', mode === 'off');
  uiMode = deriveUiMode();
  setSidebarWidth(uiMode);
  updateTogglePill('iconsToggle');
  rerenderLabels();
  render();
}

const LABELS = {
  register: {
    groupLabel: { en: 'Register',    zh: '語域',   icon: '🦋' },
    literary:   { en: 'Literary',    zh: '文學體', icon: '🦋' },
    formal:     { en: 'Formal',      zh: '正式',   icon: '🐝' },
    neutral:    { en: 'Standard',    zh: '標準',   icon: '🐞' },
    colloquial: { en: 'Colloquial',  zh: '口語',   icon: '🪲' },
    informal:   { en: 'Informal',    zh: '非正式', icon: '🦗' },
    slang:      { en: 'Slang',       zh: '俚語',   icon: '🕷️' },
  },
  connotation: {
    groupLabel:          { en: 'Connotation',      zh: '感情色彩', icon: '☀️' },
    positive:            { en: 'Positive',          zh: '褒義',   icon: '☀️' },
    neutral:             { en: 'Neutral',           zh: '中性',   icon: '⛅' },
    negative:            { en: 'Negative',          zh: '貶義',   icon: '⛈️' },
    'context-dependent': { en: 'Context-Dependent', zh: '隨境',   icon: '🌦️' },
  },
  channel: {
    groupLabel:         { en: 'Channel',          zh: '媒介',   icon: '🦜' },
    'spoken-only':      { en: 'Spoken Only',      zh: '純口語', icon: '🦎' },
    'spoken-dominant':  { en: 'Spoken Dominant',  zh: '偏口語', icon: '🐍' },
    fluid:              { en: 'Fluid',            zh: '流動',   icon: '🦜' },
    'written-dominant': { en: 'Written Dominant', zh: '偏書面', icon: '🦚' },
    'written-only':     { en: 'Written Only',     zh: '純書面', icon: '🐉' },
  },
  dimension: {
    groupLabel: { en: 'Dimension', zh: '維度',  icon: '🌊' },
    abstract:   { en: 'Abstract', zh: '抽象',  icon: '🐙' },
    concrete:   { en: 'Concrete', zh: '具體',  icon: '🐢' },
    internal:   { en: 'Internal', zh: '內在',  icon: '🐟' },
    external:   { en: 'External', zh: '外在',  icon: '🦂' },
    fluid:      { en: 'Fluid',    zh: '流動',  icon: '🦀' },
  },
  intensity: {
    groupLabel: { en: 'Intensity', zh: '強度', icon: '💐' },
    '1': { en: 'faint',    zh: '微', icon: '🌸' },
    '2': { en: 'mild',     zh: '淡', icon: '🌼' },
    '3': { en: 'moderate', zh: '中', icon: '🪷' },
    '4': { en: 'strong',   zh: '濃', icon: '🌻' },
    '5': { en: 'blazing',  zh: '烈', icon: '🌺' },
  },
  hsk: {
    groupLabel: { en: 'HSK',   zh: '等級', icon: '🌱' },
    '1': { en: 'HSK 1', zh: 'HSK 1', icon: '🌰' },
    '2': { en: 'HSK 2', zh: 'HSK 2', icon: '🌱' },
    '3': { en: 'HSK 3', zh: 'HSK 3', icon: '🌿' },
    '4': { en: 'HSK 4', zh: 'HSK 4', icon: '🌳' },
    '5': { en: 'HSK 5', zh: 'HSK 5', icon: '🌲' },
    '6': { en: 'HSK 6', zh: 'HSK 6', icon: '🎋' },
  },
  tocfl: {
    groupLabel:   { en: 'TOCFL',        zh: '華測', icon: '🌙' },
    prep:         { en: 'Preparatory',  zh: '準備', icon: '🌑' },
    entry:        { en: 'Entry',        zh: '入門', icon: '🌒' },
    basic:        { en: 'Basic',        zh: '基礎', icon: '🌓' },
    intermediate: { en: 'Intermediate', zh: '進階', icon: '🌔' },
    advanced:     { en: 'Advanced',     zh: '高階', icon: '🌕' },
    high:         { en: 'High',         zh: '精通', icon: '🌖' },
    fluency:      { en: 'Fluency',      zh: '流利', icon: '🌝' },
  },
};

function chipHTML(l) {
  if (!l) return '';
  const ico = l.icon ? `<span class="chip-icon">${l.icon}</span>` : '';
  switch (uiMode) {
    case 'en-icon':  return ico ? `${ico}<span>${l.en}</span>` : `<span>${l.en}</span>`;
    case 'zh-icon':  return ico ? `${ico}<span>${l.zh}</span>` : `<span>${l.zh}</span>`;
    case 'en-zh':    return `<span>${l.zh} ${l.en}</span>`;
    case 'all':      return ico ? `${ico}<span>${l.zh} · ${l.en}</span>` : `<span>${l.zh} · ${l.en}</span>`;
    case 'zh-only':  return `<span>${l.zh}</span>`;
    case 'en-only':  return `<span>${l.en}</span>`;
    case 'icon-only':return ico ? ico : `<span>${l.zh}</span>`;
    default:         return ico ? `${ico}<span>${l.en}</span>` : `<span>${l.en}</span>`;
  }
}

function allText() {
  switch (uiMode) {
    case 'zh-only': case 'zh-icon': return '全部';
    case 'en-only': return 'All';
    case 'icon-only': return '·';
    default: return 'All';
  }
}

const SECTION_TITLE = {
  'en-icon':  'Filters',
  'zh-icon':  '篩選',
  'en-zh':    '篩選 Filters',
  'all':      '篩選 Filters',
  'zh-only':  '篩選',
  'icon-only':'≡',
  'en-only':  'Filters',
};

function rowLabelText(l) {
  if (!l) return '';
  const icon = l.icon ? `<span class="row-icon">${l.icon}</span>` : '';
  switch (uiMode) {
    case 'en-icon':  return `<span class="row-label-text">${l.en}</span>`;
    case 'zh-icon':  return `<span class="row-label-text">${l.zh}</span>`;
    case 'en-zh':    return `<span class="row-label-text">${l.zh} ${l.en}</span>`;
    case 'all':      return `<span class="row-label-text">${l.zh} ${l.en}</span>`;
    case 'zh-only':  return `<span class="row-label-text">${l.zh}</span>`;
    case 'en-only':  return `<span class="row-label-text">${l.en}</span>`;
    case 'icon-only': return icon || `<span class="row-label-text">${l.zh}</span>`;
    default:         return `<span class="row-label-text">${l.en}</span>`;
  }
}

function formatLabel(cat, key) {
  const l = LABELS[cat]?.[key];
  if (!l) return key;
  return chipHTML(l);
}

// For word card meta-tags: label LEFT, icon RIGHT, consistent grid
function metaTagHTML(l) {
  if (!l) return '';
  const ico = l.icon ? `<span class="tag-ico">${l.icon}</span>` : '<span class="tag-ico"></span>';
  let label = '';
  switch (uiMode) {
    case 'zh-icon': case 'zh-only': label = l.zh; break;
    case 'en-zh': case 'all': label = l.zh + ' · ' + l.en; break;
    case 'icon-only': label = ''; break;
    default: label = l.en;
  }
  return `<span class="tag-label">${label}</span>${ico}`;
}

function metaLabel(cat, key) {
  const l = LABELS[cat]?.[key];
  if (!l) return `<span class="tag-label">${key}</span><span class="tag-ico"></span>`;
  return metaTagHTML(l);
}

function rerenderLabels() {
  // Section title
  const titleEl = document.getElementById('filterSectionTitle');
  if (titleEl) titleEl.textContent = SECTION_TITLE[uiMode] || 'Filters';
  // Update filter row labels
  document.querySelectorAll('.filter-row[data-group]').forEach(row => {
    const group = row.dataset.group;
    const labelEl = row.querySelector('.filter-row-label');
    if (!labelEl || !LABELS[group]) return;
    labelEl.innerHTML = rowLabelText(LABELS[group].groupLabel);
  });
  // Update dropdown chip content
  document.querySelectorAll('.d-chip[data-group][data-val]').forEach(chip => {
    const l = LABELS[chip.dataset.group]?.[chip.dataset.val];
    if (l) chip.innerHTML = chipHTML(l);
  });
  // Update all previews (so All/全部 text matches current mode)
  Object.keys(state).forEach(updatePreview);
  // Update 造句 workshop titles to match current language
  const zaojuLabel = langMode === 'zh' ? '造句' : langMode === 'both' ? 'Sentence Workshop · 造句' : 'Sentence Workshop';
  document.querySelectorAll('.zaoju-title').forEach(el => el.textContent = zaojuLabel);
}

function setSidebarWidth(mode) {
  const widths = {
    'icon-only': '68px',
    'zh-icon':   '200px',
    'en-icon':   '220px',
    'zh-only':   '200px',
    'en-zh':     '280px',
    'all':       '300px',
    'en-only':   '220px',
  };
  document.querySelector('main').style.setProperty('--sidebar-w', widths[mode] || '280px');
  document.querySelector('main').classList.toggle('icons-only', mode === 'icon-only');
}

// modeSelect removed — interface now controlled by Language + Icons toggles

// ── ATTRIBUTE FILTERS TOGGLE ──────────────────────────────────────────────
let attrFiltersOpen = false;

function toggleAttrFilters() {
  attrFiltersOpen = !attrFiltersOpen;
  const panel = document.getElementById('attrFilterPanel');
  if (attrFiltersOpen) {
    panel.style.overflow = 'hidden'; // ensure hidden while expanding
    panel.classList.add('open');
    // Once expanded, allow dropdowns to overflow the panel
    panel.addEventListener('transitionend', () => {
      panel.style.overflow = 'visible';
    }, { once: true });
  } else {
    panel.style.overflow = 'hidden'; // lock before collapsing
    panel.classList.remove('open');
  }
  document.getElementById('attrFilterTab').classList.toggle('open', attrFiltersOpen);
  document.getElementById('attrFilterArrow').style.transform = attrFiltersOpen ? 'rotate(180deg)' : '';
}

// ── ACCORDION ──────────────────────────────────────────────────────────────
let openPanel = null;

function toggleAccordion(name) {
  const panel = document.getElementById('accPanel' + name.charAt(0).toUpperCase() + name.slice(1));
  const tab   = document.getElementById('accTab'   + name.charAt(0).toUpperCase() + name.slice(1));
  if (!panel || !tab) return;

  const isOpen = panel.classList.contains('open');

  // Close all
  ['Scenario','Level','Interface'].forEach(n => {
    document.getElementById('accPanel' + n)?.classList.remove('open');
    document.getElementById('accTab'   + n)?.classList.remove('open');
  });

  // Re-open if it was closed
  if (!isOpen) {
    panel.classList.add('open');
    tab.classList.add('open');
    openPanel = name;
  } else {
    openPanel = null;
  }
}

// Close accordion when clicking outside
document.addEventListener('click', function(e) {
  if (!e.target.closest('.acc-header') && !e.target.closest('.acc-panel')) {
    ['Scenario','Level','Interface'].forEach(n => {
      document.getElementById('accPanel' + n)?.classList.remove('open');
      document.getElementById('accTab'   + n)?.classList.remove('open');
    });
    openPanel = null;
  }
});

// ── LEVEL (accordion version) ───────────────────────────────────────────────
function setLevel(level) {
  // Update level cards
  ['Beginner','Learner','Developing','Advanced','Native'].forEach(l => {
    document.getElementById('lvlBtn' + l)?.classList.remove('active');
  });
  const key = level.charAt(0).toUpperCase() + level.slice(1);
  document.getElementById('lvlBtn' + key)?.classList.add('active');
  // Keep hidden select in sync (applyLevelFonts reads it)
  const sel = document.getElementById('levelSelect');
  if (sel) sel.value = level;
  applyLevelFonts(level);
}

// ── SCENARIO (accordion version) ────────────────────────────────────────────
function applyScenarioPreset(key) {
  const preset = BUILT_IN_SCENARIOS[key];
  if (preset) {
    applyScenario(preset);
    // Highlight active scenario card
    document.querySelectorAll('.scenario-card').forEach(c => c.classList.remove('active'));
    event.currentTarget.classList.add('active');
  }
}

function rebuildCustomScenariosGrid() {
  const grid = document.getElementById('customScenariosGrid');
  if (!grid) return;
  grid.innerHTML = '';
  if (customScenarios.length === 0) { grid.style.display = 'none'; return; }
  grid.style.display = 'flex';
  customScenarios.forEach((s, i) => {
    const btn = document.createElement('button');
    btn.className = 'scenario-card';
    btn.innerHTML = `<span class="sc-icon">⭐</span><span class="sc-name">${s.name}</span><span class="sc-desc">Custom scenario</span>`;
    btn.onclick = () => applyScenario(s.filters);
    grid.appendChild(btn);
  });
}


// ── SCRIPT MODE ───────────────────────────────────────────────────────────────
let scriptMode      = 'traditional'; // 'traditional' | 'simplified'
let pinyinMode      = 'on';          // 'on' | 'off'
let workshopDefault = localStorage.getItem('workshopDefault') || 'collapsed'; // 'expanded' | 'collapsed'

function setWorkshopDefault(mode) {
  workshopDefault = mode;
  localStorage.setItem('workshopDefault', mode);
  document.getElementById('btnWorkshopExpanded').classList.toggle('active', mode === 'expanded');
  document.getElementById('btnWorkshopCollapsed').classList.toggle('active', mode === 'collapsed');
  updateTogglePill('workshopToggle');
  render();
}

function setPinyinMode(mode) {
  pinyinMode = mode;
  document.getElementById('btnPinyinOn').classList.toggle('active', mode === 'on');
  document.getElementById('btnPinyinOff').classList.toggle('active', mode === 'off');
  updateTogglePill('pinyinToggle');
  document.getElementById('cardContainer').classList.toggle('no-pinyin', mode === 'off');
}

function setScript(mode) {
  scriptMode = mode;
  document.getElementById('btnTrad').classList.toggle('active', mode === 'traditional');
  document.getElementById('btnSimp').classList.toggle('active', mode === 'simplified');
  updateTogglePill('scriptToggle');
  render();
}

let posMode = 'abbr'; // 'full' | 'abbr' — abbreviation is default next to definitions

// DB name → display name (verb labels use "Verb - [descriptor]" format)
// DB full name → learner-friendly display name (verb types use "Verb - [descriptor]" format)
const POS_RENAME = {
  'Verb':                                 'Verb (all)',
  'Intransitive Verb':                    'Verb - intransitive',
  'Process Verb':                         'Verb - process',
  'Vp-sep / Separable Process Verb':      'Verb - process (sep.)',
  'Process Verb (Telic)':                 'Verb - process (telic)',
  'Stative Verb':                         'Verb - stative',
  'Vs-attr / Stative Verb (Attributive)': 'Verb - stative (attr.)',
  'Vs-pred / Stative Verb (Predicative)': 'Verb - stative (pred.)',
  'Vs-sep / Separable Stative Verb':      'Verb - stative (sep.)',
  'State-Transitive Verb':                'Verb - state-transitive',
  'Auxiliary Verb':                       'Verb - auxiliary',
  'V-sep / Separable Verb':               'Verb - separable',
};

// DB full name → abbreviation
const POS_ABBR = {
  'Verb':                                 'V',
  'Intransitive Verb':                    'Vi',
  'Process Verb':                         'Vp',
  'Vp-sep / Separable Process Verb':      'Vp-sep',
  'Process Verb (Telic)':                 'Vpt',
  'Stative Verb':                         'Vs',
  'Vs-attr / Stative Verb (Attributive)': 'Vs-attr',
  'Vs-pred / Stative Verb (Predicative)': 'Vs-pred',
  'Vs-sep / Separable Stative Verb':      'Vs-sep',
  'State-Transitive Verb':                'Vst',
  'Auxiliary Verb':                       'Vaux',
  'V-sep / Separable Verb':               'V-sep',
  'Noun':                                 'N',
  'Measure Word':                         'M',
  'Adverb':                               'Adv',
  'Preposition':                          'Prep',
  'Conjunction':                          'Conj',
  'Particle':                             'Ptcl',
  'Determiner':                           'Det',
  'Pronoun':                              'Prn',
  'Number':                               'Num',
  'Idiomatic Expression':                 'IE',
  'Phrase':                               'Ph',
};

// Chinese POS names — shown when learner taps the header or definition POS chip
const POS_ZH = {
  'Verb':                                 '動詞（全部）',
  'Intransitive Verb':                    '不及物動詞',
  'Process Verb':                         '過程動詞',
  'Vp-sep / Separable Process Verb':      '離合過程動詞',
  'Process Verb (Telic)':                 '完結動詞',
  'Stative Verb':                         '狀態動詞',
  'Vs-attr / Stative Verb (Attributive)': '狀態動詞（定語）',
  'Vs-pred / Stative Verb (Predicative)': '狀態動詞（謂語）',
  'Vs-sep / Separable Stative Verb':      '離合狀態動詞',
  'State-Transitive Verb':                '狀態及物動詞',
  'Auxiliary Verb':                       '助動詞',
  'V-sep / Separable Verb':              '離合詞',
  'Noun':                                 '名詞',
  'Measure Word':                         '量詞',
  'Adverb':                               '副詞',
  'Preposition':                          '介詞',
  'Conjunction':                          '連詞',
  'Particle':                             '助詞',
  'Determiner':                           '限定詞',
  'Pronoun':                              '代詞',
  'Number':                               '數詞',
  'Idiomatic Expression':                 '成語',
  'Phrase':                               '詞組',
};

// Returns the display name for a raw DB POS label
function posDisplay(raw) {
  return POS_RENAME[raw] || raw;
}

function posLabel(raw) {
  if (posMode === 'abbr') return POS_ABBR[raw] || raw;
  return posDisplay(raw); // full display name
}

// POS group membership — selecting 'Verb' matches all verb subtypes
const POS_GROUPS = {
  'Verb': Object.keys(POS_ABBR).filter(k => k !== 'Verb' && POS_ABBR[k].startsWith('V')),
};

function posMatchesFilter(pos, filter) {
  if (pos === filter) return true;
  const group = POS_GROUPS[filter];
  return group ? group.includes(pos) : false;
}

function setPosMode(mode) {
  posMode = mode;
  document.getElementById('btnPosAbbr').classList.toggle('active', mode === 'abbr');
  document.getElementById('btnPosFull').classList.toggle('active', mode === 'full');
  updateTogglePill('posToggle');
  // Clear any per-chip overrides so they follow the new global mode
  document.querySelectorAll('.card-pos[data-overridden]').forEach(el => {
    el.removeAttribute('data-overridden');
    el.textContent = posMode === 'abbr' ? el.dataset.abbr : el.dataset.full;
  });
  render();
}

// Language toggle for domain chip and header POS chips.
// Cycles: preferred language ↔ Chinese.
// Preferred language defaults to 'en' unless langMode is zh/zh-icon/zh-only.
function toggleLangChip(e, chip) {
  e.stopPropagation();
  const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
  const alt = preferred === 'zh' ? 'en' : 'zh';
  const current = chip.dataset.state || preferred;
  const next = current === preferred ? alt : preferred;
  chip.dataset.state = next;
  chip.textContent = chip.dataset[next] || chip.dataset.en;
}

// Attribute chip toggle — toggles ALL translatable elements within the chip
// (both .card-attr-header and .attr-label spans) between preferred language and Chinese.
function toggleAttrLang(e) {
  e.stopPropagation();
  e.preventDefault();
  const chip = e.currentTarget; // the .card-attr div
  const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
  const alt = preferred === 'zh' ? 'en' : 'zh';
  chip.querySelectorAll('[data-en][data-zh]').forEach(el => {
    const current = el.dataset.state || preferred;
    const next = current === preferred ? alt : preferred;
    el.dataset.state = next;
    el.textContent = el.dataset[next] || el.dataset.en;
  });
}

// Per-definition POS chip — 3-way cycle: abbr → full EN name → Chinese name → abbr
function cyclePosChip(e, chip) {
  e.stopPropagation();
  e.preventDefault();
  const order = ['abbr', 'full', 'zh'];
  const current = chip.dataset.state || 'abbr';
  const next = order[(order.indexOf(current) + 1) % 3];
  chip.dataset.state = next;
  chip.textContent = chip.dataset[next] || chip.dataset.abbr;
}

// ── FLUENCY LEVEL — font sizes per element ────────────────────────────────────
// Each level defines rem sizes for: [hanzi-trad, hanzi-simp, pinyin, definition, usage-note, formula, ex-cn, ex-en]
const LEVEL_FONTS = {
  beginner:   { hanzi: 3.8, simp: 1.9, pinyin: 1.2, defn: 2.0, note: 1.1, formula: 1.1, exCn: 2.0, exEn: 1.1, scale: 130 },
  learner:    { hanzi: 3.2, simp: 1.6, pinyin: 1.1, defn: 1.9, note: 1.0, formula: 1.0, exCn: 1.9, exEn: 1.0, scale: 115 },
  developing: { hanzi: 2.8, simp: 1.4, pinyin: 1.0, defn: 1.5, note: 0.9, formula: 1.0, exCn: 1.8, exEn: 1.0, scale: 100 },
  advanced:   { hanzi: 2.4, simp: 1.2, pinyin: 0.9, defn: 1.6, note: 0.9, formula: 0.9, exCn: 1.6, exEn: 0.9, scale: 90  },
  native:     { hanzi: 2.0, simp: 1.0, pinyin: 0.8, defn: 1.4, note: 0.85,formula: 0.85,exCn: 1.4, exEn: 0.85,scale: 85  },
};

let currentLevel = 'developing';
let fontScale = 100;
const FONT_STEPS = [75, 85, 100, 115, 130, 150];

function applyLevelFonts(level) {
  currentLevel = level;
  const f = LEVEL_FONTS[level];
  if (!f) return;
  // Apply CSS vars on root for live update
  const r = document.documentElement;
  r.style.setProperty('--fs-hanzi',   f.hanzi   + 'rem');
  r.style.setProperty('--fs-simp',    f.simp    + 'rem');
  r.style.setProperty('--fs-pinyin',  f.pinyin  + 'rem');
  r.style.setProperty('--fs-defn',    f.defn    + 'rem');
  r.style.setProperty('--fs-note',    f.note    + 'rem');
  r.style.setProperty('--fs-formula', f.formula + 'rem');
  r.style.setProperty('--fs-ex-cn',   f.exCn    + 'rem');
  r.style.setProperty('--fs-ex-en',   f.exEn    + 'rem');
  applyFontScale(f.scale);
}

document.getElementById('levelSelect')?.addEventListener('change', function() {
  applyLevelFonts(this.value);
});

// ── FONT SIZE MANUAL OVERRIDE ─────────────────────────────────────────────────
function applyFontScale(scale) {
  fontScale = scale;
  document.documentElement.style.fontSize = scale + '%';
  document.getElementById('fontVal').textContent = scale + '%';
}

document.getElementById('fontUp').addEventListener('click', () => {
  const idx = FONT_STEPS.indexOf(fontScale);
  if (idx < FONT_STEPS.length - 1) applyFontScale(FONT_STEPS[idx + 1]);
});
document.getElementById('fontDown').addEventListener('click', () => {
  const idx = FONT_STEPS.indexOf(fontScale);
  if (idx > 0) applyFontScale(FONT_STEPS[idx - 1]);
});

// Init at developing level
applyLevelFonts('developing');

const PREVIEW_ICONS = {
  register:    { literary:'🦋', formal:'🐝', neutral:'🐞', colloquial:'🪲', informal:'🦗', slang:'🕷️' },
  connotation: { positive:'☀️', neutral:'⛅', negative:'⛈️', 'context-dependent':'🌦️' },
  channel:     { spoken:'🦜', both:'🐉', written:'🐍' },
  dimension:   { abstract:'🐙', concrete:'🐢', internal:'🐟', external:'🦂', fluid:'🦀' },
  intensity:   { '1':'🌸', '2':'🌼', '3':'🪷', '4':'🌻', '5':'🌺' },
  hsk:         { '1':'🌰', '2':'🌱', '3':'🌿', '4':'🌳', '5':'🌲', '6':'🎋' },
  tocfl:       { prep:'🌑', entry:'🌒', basic:'🌓', intermediate:'🌔', advanced:'🌕', fluency:'🌝' },
};

let openDropdown = null;

const PREVIEW_LABELS = {
  register:    { literary:'🦋 Literary', formal:'🐝 Formal', neutral:'🐞 Standard', colloquial:'🪲 Colloquial', informal:'🦗 Informal', slang:'🕷️ Slang' },
  connotation: { positive:'☀️ Positive', neutral:'⛅ Neutral', negative:'⛈️ Negative', 'context-dependent':'🌦️ Context' },
  channel:     { 'spoken-only':'🦎 Spoken Only', 'spoken-dominant':'🐍 Spoken Dominant', fluid:'🦜 Fluid', 'written-dominant':'🦚 Written Dominant', 'written-only':'🐉 Written Only' },
  dimension:   { abstract:'🐙 Abstract', concrete:'🐢 Concrete', internal:'🐟 Internal', external:'🦂 External', fluid:'🦀 Fluid' },
  intensity:   { '1':'🌸 Faint', '2':'🌼 Mild', '3':'🪷 Moderate', '4':'🌻 Strong', '5':'🌺 Blazing' },
  tocfl:       { prep:'🌑 Prep', entry:'🌒 Entry', basic:'🌓 Basic', intermediate:'🌔 Intermediate', advanced:'🌕 Advanced', high:'🌖 High', fluency:'🌝 Fluency' },
};

function updatePreview(group) {
  const selected = state[group];
  const preview  = document.getElementById('preview-' + group);
  const row      = document.getElementById('row-' + group);
  if (!preview || !row) return;

  row.classList.toggle('has-selection', selected && selected.length > 0);

  if (!selected || selected.length === 0) {
    preview.innerHTML = '';
    return;
  }
  const labels = PREVIEW_LABELS[group] || {};
  preview.innerHTML = selected.map(v => {
    const txt = labels[v] || v;
    return `<span class="preview-text" data-group="${group}" data-val="${v}">${txt}</span>`;
  }).join('');
}

function closeAllDropdowns() {
  document.querySelectorAll('.filter-row').forEach(r => r.classList.remove('open'));
  document.querySelectorAll('.filter-dropdown').forEach(d => d.classList.remove('open'));
  openDropdown = null;
}

// Wire up filter rows — full row is the click target, not just the trigger label.
// Guard: ignore clicks on preview remove-tags (they have their own handler).
document.querySelectorAll('.filter-row').forEach(row => {
  row.addEventListener('click', e => {
    // Let preview-tag remove clicks pass through to their own handler untouched
    if (e.target.closest('.filter-row-preview')) return;
    // Ignore clicks that originate inside the already-open dropdown chips
    if (e.target.closest('.filter-dropdown')) return;
    const group = row.dataset.group;
    const drop = document.getElementById('drop-' + group);
    if (!drop) return;
    const isOpen = row.classList.contains('open');
    closeAllDropdowns();
    if (!isOpen) {
      row.classList.add('open');
      drop.classList.add('open');
      openDropdown = group;
    }
  });
});

// Wire up dropdown chips
document.querySelectorAll('.d-chip').forEach(chip => {
  chip.addEventListener('click', e => {
    e.stopPropagation();
    const group = chip.dataset.group;
    const val = chip.dataset.val;
    if (!group || !val) return;

    const arr = state[group];
    const idx = arr.indexOf(val);
    if (idx === -1) arr.push(val);
    else arr.splice(idx, 1);

    // Update chip selected state
    chip.classList.toggle('selected', arr.includes(val));
    updatePreview(group);
    render();
  });
});

// Click preview tags to remove that filter
document.querySelectorAll('.filter-row-preview').forEach(preview => {
  preview.addEventListener('click', e => {
    const tag = e.target.closest('.preview-text');
    if (!tag) return;
    const group = tag.dataset.group;
    const val   = tag.dataset.val;
    if (!group || !val || !state[group]) return;
    const idx = state[group].indexOf(val);
    if (idx !== -1) state[group].splice(idx, 1);
    // Deselect matching chip
    const chip = document.querySelector(`.d-chip[data-group="${group}"][data-val="${val}"]`);
    if (chip) chip.classList.remove('selected');
    updatePreview(group);
    render();
  });
});


// Close dropdowns on outside click
document.addEventListener('click', e => {
  if (!e.target.closest('.filter-bar')) {
    closeAllDropdowns();
  }
});

// Close on Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeAllDropdowns();
});

// ── DATA ──────────────────────────────────────────────────────────────────────
const WORDS = {!! json_encode($words, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!};
const INITIAL_SEARCH = {!! json_encode($initialSearch) !!};
const DOMAIN_GROUPS = {!! json_encode($domainGroups, JSON_UNESCAPED_UNICODE) !!};

// Flat slug→label maps for domain chips (EN + ZH-TW)
const DOMAIN_LABEL_MAP    = {};
const DOMAIN_LABEL_MAP_ZH = {};
DOMAIN_GROUPS.forEach(g => g.domains.forEach(d => {
  DOMAIN_LABEL_MAP[d.slug]    = d.label;
  DOMAIN_LABEL_MAP_ZH[d.slug] = d.label_zh || d.label;
}));

// Populate domain refine select from DOMAIN_GROUPS (optgroup per group)
(function() {
  const sel = document.getElementById('domainRefineSelect');
  if (!sel) return;
  DOMAIN_GROUPS.forEach(g => {
    const og = document.createElement('optgroup');
    og.label = g.label;
    g.domains.forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.slug;
      opt.textContent = d.label;
      og.appendChild(opt);
    });
    sel.appendChild(og);
  });
  sel.addEventListener('change', () => {
    domainFilter = sel.value;
    sel.classList.toggle('active', !!domainFilter);
    syncRefineReset();
    render();
  });
})();

// Populate POS refine select from distinct POS values present in WORDS
(function() {
  const sel = document.getElementById('posRefineSelect');
  if (!sel) return;
  const posOrder = Object.keys(POS_ABBR);
  const seen = new Set();
  WORDS.forEach(w => (w.definitions || []).forEach(d => { if (d.pos) seen.add(d.pos); }));
  [...seen]
    .sort((a, b) => posOrder.indexOf(a) - posOrder.indexOf(b))
    .forEach(pos => {
      const opt = document.createElement('option');
      opt.value = pos;
      opt.textContent = (POS_ABBR[pos] || pos) + ' — ' + posDisplay(pos);
      sel.appendChild(opt);
    });
  sel.addEventListener('change', () => {
    posFilter = sel.value;
    sel.classList.toggle('active', !!posFilter);
    syncRefineReset();
    render();
  });
})();

// Wire up Lexical Relative select
(function() {
  const sel = document.getElementById('relRefineSelect');
  if (!sel) return;
  sel.addEventListener('change', () => {
    relFilter = sel.value;
    sel.classList.toggle('active', !!relFilter);
    syncRefineReset();
    render();
  });
})();

// ── SECONDARY FILTER STATE (single-select, outside main state object) ─────────
let domainFilter = ''; // '' = all; domain slug = refine to that primary domain
let posFilter    = ''; // '' = all; full POS name (e.g. 'Intransitive Verb')
let relFilter    = ''; // '' = all; 'immediate' | 'close' | 'distant'

// ── STATE ──────────────────────────────────────────────────────────────────────
let state = {
  register:        [],   // [] = all; populated = must match one
  connotation:     [],   // [] = all
  channel:         [],   // [] = all
  dimension:       [],   // [] = all
  intensity:       [],   // [] = all; exact match
  hsk:             [],   // [] = all; exact match
  tocfl:           [],   // [] = all; exact match
};

// ── RENDER ─────────────────────────────────────────────────────────────────────
function levelLabel(v) {
  if (v >= 6) return 'Any';
  return 'HSK ' + (v + 1);
}

// ── SEARCH ────────────────────────────────────────────────────────────────────
// searchQuery is set by the search input UI (not yet built).
// Searches across: traditional, simplified, pinyin, all definitions[].def,
// all definitions[].pos, usageNote, and all family members (word + def).
// Returns a word object if ANY of its surfaces match the query string.
// POS is not a filter — a match on any definition row returns the whole word.
let searchQuery = '';

function wordMatchesSearch(w) {
  if (!searchQuery.trim()) return true;
  const q = searchQuery.trim().toLowerCase();
  const surfaces = [
    w.traditional, w.simplified, w.pinyin,
    ...(w.definitions || []).flatMap(d => [d.def, d.pos, d.usageNote]),
    ...Object.values(w.family || {}).flat().flatMap(f => [f.word, f.trad, f.pinyin, f.def]),
  ];
  return surfaces.some(s => s && s.toLowerCase().includes(q));
}

function matchWord(w) {
  if (!wordMatchesSearch(w)) return false;
  // Register: if any selected, word must match one
  if (state.register.length && !state.register.includes(w.register)) return false;
  // Connotation: multi-select
  if (state.connotation.length && !state.connotation.includes(w.connotation)) return false;
  // Channel: multi-select (spoken/written both match 'both' channel words)
  if (state.channel.length) {
    const ch = w.channel;
    const match = state.channel.some(sel =>
      sel === ch || (sel === 'spoken' && ch === 'both') || (sel === 'written' && ch === 'both')
    );
    if (!match) return false;
  }
  // Dimension: multi-select (word must have at least one matching dimension)
  if (state.dimension.length) {
    const wDims = w.dimension || [];
    if (!state.dimension.some(d => wDims.includes(d))) return false;
  }
  // Intensity: multi-select exact match
  if (state.intensity.length && !state.intensity.includes(String(w.intensity))) return false;
  // HSK: multi-select exact match
  // TOCFL: multi-select exact match
  if (state.tocfl.length && !state.tocfl.includes(w.tocfl)) return false;
  // POS refine: match any definition with the selected POS (group-aware)
  if (posFilter && !(w.definitions || []).some(d => posMatchesFilter(d.pos, posFilter))) return false;
  // Relative proximity refine: match if word has the selected proximity bucket
  if (relFilter && !(w.relProximity || []).includes(relFilter)) return false;
  // Domain refine: single-select — matches primary OR secondary domain
  if (domainFilter && w.domain !== domainFilter && w.secondaryDomain !== domainFilter) return false;
  return true;
}

const connoClass = { positive: 'conno-pos', neutral: 'conno-neu', negative: 'conno-neg', 'context-dependent': 'conno-ctx' };

function intensityBars(n) {
  const heights = [4, 7, 11, 15, 19];
  const colors  = ['#a0b4d0','#8878b4','#c08020','#c84020','#c0283c'];
  return `<div class="int-bars">${
    Array.from({length: 5}, (_, i) => {
      const lit = i < n;
      const style = lit
        ? `height:${heights[i]}px;background:${colors[i]};border-color:${colors[i]}`
        : `height:${heights[i]}px`;
      return `<div class="int-bar" style="${style}"></div>`;
    }).join('')
  }</div>`;
}

// ── SAVED DECK (in-memory) ─────────────────────────────────────────────────────
const savedDeck = {}; // { wordKey: [{cn, en, source}] }

function getSavedForWord(key) { return savedDeck[key] || []; }

function saveToWord(key, item) {
  if (!savedDeck[key]) savedDeck[key] = [];
  // Avoid duplicates
  if (savedDeck[key].some(s => s.cn === item.cn)) return false;
  savedDeck[key].push(item);
  return true;
}

function removeFromWord(key, idx) {
  if (savedDeck[key]) savedDeck[key].splice(idx, 1);
}

// ── ADDITIONAL DEFAULT SENTENCES PER WORD ─────────────────────────────────────
const EXTRA_SENTENCES = {
  "擴大": [
    { cn: "政府計劃擴大醫療保障範圍。", en: "The government plans to expand healthcare coverage." },
    { cn: "我們必須擴大視野，才能解決問題。", en: "We must broaden our horizons to solve this problem." },
  ],
  "發展": [
    { cn: "科技迅速發展，改變了我們的生活。", en: "Technology has developed rapidly, changing our lives." },
    { cn: "這個地區的經濟正在穩定發展。", en: "The economy of this region is developing steadily." },
  ],
  "拓展": [
    { cn: "她努力拓展自己的人際網絡。", en: "She works hard to expand her personal network." },
    { cn: "公司決定拓展亞洲市場。", en: "The company decided to expand into Asian markets." },
  ],
  "蔓延": [
    { cn: "謠言如野火般蔓延。", en: "Rumours spread like wildfire." },
    { cn: "憂傷在心中蔓延，久久不散。", en: "Sorrow spread through the heart, lingering long." },
  ],
  "膨脹": [
    { cn: "通貨膨脹使生活成本上升。", en: "Inflation caused the cost of living to rise." },
    { cn: "他的自尊心在成功後急劇膨脹。", en: "His ego swelled dramatically after his success." },
  ],
  "弘揚": [
    { cn: "學校致力弘揚中華文化精神。", en: "The school is dedicated to promoting the spirit of Chinese culture." },
    { cn: "藝術家用作品弘揚人道主義價值。", en: "The artist uses their work to carry forward humanist values." },
  ],
  "延伸": [
    { cn: "這條鐵路向南延伸至邊境。", en: "This railway extends southward to the border." },
    { cn: "討論從語言學延伸至哲學領域。", en: "The discussion extended from linguistics into philosophy." },
  ],
  "擴充": [
    { cn: "學校擴充了師資隊伍。", en: "The school expanded its teaching staff." },
    { cn: "他們正在擴充倉庫的儲存容量。", en: "They are expanding the storage capacity of the warehouse." },
  ],
  "散佈": [
    { cn: "請勿在網上散佈未經核實的消息。", en: "Please do not spread unverified news online." },
    { cn: "小販在廣場上散佈傳單。", en: "Vendors scattered flyers across the square." },
  ],
  "越來越大": [
    { cn: "孩子的夢想越來越大，令父母感到驕傲。", en: "The child's dreams keep expanding, making their parents proud." },
    { cn: "這個社區的影響力越來越大。", en: "This community's influence keeps growing." },
  ],
};

// ── AI CALL ────────────────────────────────────────────────────────────────────
async function callAI(systemPrompt, userMessage) {
  const response = await fetch("https://api.anthropic.com/v1/messages", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      model: "claude-sonnet-4-20250514",
      max_tokens: 1000,
      system: systemPrompt,
      messages: [{ role: "user", content: userMessage }]
    })
  });
  const data = await response.json();
  return data.content.map(b => b.text || '').join('');
}

// ── AI SYSTEM PROMPTS ──────────────────────────────────────────────────────────
function getCritiquePrompt(word) {
  return `You are an expert Chinese language tutor for the Living Lexicon 流動, a precision Chinese vocabulary app focused on fluency, nuance, and expressive accuracy. 

The user has written a sentence using the word "${word.traditional}" (${word.simplified}, ${word.pinyin}) — meaning: ${word.definition}.

Word metadata:
- Register: ${word.register}
- Connotation: ${word.connotation}  
- Channel: ${word.channel}
- HSK Level: ${word.level}
- Syntactic formula: ${word.formula}

Your task: Evaluate the user's sentence with warmth and precision. Respond ONLY in this exact JSON format (no markdown, no extra text):
{
  "verdict": "correct" | "minor_issues" | "needs_work",
  "corrected_cn": "The corrected Traditional Chinese sentence (use Traditional characters), or the original if already correct",
  "corrected_en": "English translation of the corrected sentence",
  "highlight_word": "${word.traditional}",
  "feedback": "2-3 sentences of warm, precise feedback in English. Note what was done well. If correcting, explain WHY — grammar, register mismatch, valency error, colocation issue, etc. Be encouraging but intellectually honest.",
  "register_note": "One sentence: does this sentence match the word's register (${word.register})? If not, explain gently."
}`;
}

function getThemePrompt(word) {
  return `You are an expert Chinese language tutor for the Living Lexicon 流動, a precision Chinese vocabulary app.

Generate a vivid, natural sentence using the word "${word.traditional}" (${word.simplified}) based on the user's requested theme/subject. 

Word metadata:
- Register: ${word.register} — STRICTLY match this register in your sentence
- Connotation: ${word.connotation}
- Channel: ${word.channel}
- HSK Level: ${word.level}
- Syntactic formula: ${word.formula}

Rules:
- Use Traditional Chinese characters throughout
- The sentence must feel natural and engaging, not textbook-dry
- Match the register precisely (${word.register})
- Make the headword prominent and natural in context
- The sentence should connect emotionally to the user's theme

Respond ONLY in this exact JSON format (no markdown, no extra text):
{
  "cn": "The Traditional Chinese sentence",
  "en": "Natural English translation",
  "note": "One sentence explaining why this sentence fits the theme and demonstrates the word's nuance well"
}`;
}

// ── RENDER CARD WITH 造句 ──────────────────────────────────────────────────────
function highlightWord(text, word) {
  // Highlight the headword in a sentence
  const variants = [word.traditional, word.simplified].filter(Boolean);
  let result = text;
  variants.forEach(v => {
    result = result.split(v).join(`<span class="highlight">${v}</span>`);
  });
  return result;
}

function renderSavedDeck(wordKey) {
  const items = getSavedForWord(wordKey);
  if (!items.length) return '';
  return `
    <div class="saved-deck-section" id="deck-${wordKey}">
      <span class="saved-deck-label">✦ 我的詞典 My Saved Sentences (${items.length})</span>
      ${items.map((item, i) => `
        <div class="saved-item">
          <div>
            <div class="saved-item-cn">${highlightWord(item.cn, {traditional: wordKey, simplified: ''})}</div>
            <div class="sent-en">${item.en}</div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:0.2rem">
            <span class="saved-item-source">${item.source}</span>
            <button class="remove-btn" onclick="removeSaved('${wordKey}', ${i})">✕</button>
          </div>
        </div>
      `).join('')}
    </div>`;
}

function removeSaved(wordKey, idx) {
  removeFromWord(wordKey, idx);
  // Re-render just the saved deck section
  const deckEl = document.getElementById(`deck-${wordKey}`);
  const items = getSavedForWord(wordKey);
  if (deckEl) {
    if (!items.length) {
      deckEl.remove();
    } else {
      const parent = deckEl.parentElement;
      deckEl.outerHTML = renderSavedDeck(wordKey);
    }
  }
}

// ── CARD ATTRIBUTE COLUMN HELPERS ─────────────────────────────────────────────
const ATTR_LABELS = {
  register:    { literary:['🦋','Literary'], formal:['🐝','Formal'], neutral:['🐞','Standard'], colloquial:['🪲','Colloquial'], informal:['🦗','Informal'], slang:['🕷️','Slang'] },
  connotation: { positive:['☀️','Positive'], neutral:['⛅','Neutral'], negative:['⛈️','Negative'], 'context-dependent':['🌦️','Context'] },
  channel:     { 'spoken-only':['🦎','Spoken Only'], 'spoken-dominant':['🐍','Spoken Dominant'], fluid:['🦜','Fluid'], 'written-dominant':['🦚','Written Dominant'], 'written-only':['🐉','Written Only'] },
  dimension:   { abstract:['🐙','Abstract'], concrete:['🐢','Concrete'], internal:['🐟','Internal'], external:['🦂','External'], fluid:['🦀','Fluid'] },
  intensity:   { 1:['🌸','Faint'], 2:['🌼','Mild'], 3:['🪷','Moderate'], 4:['🌻','Strong'], 5:['🌺','Blazing'] },
  tocfl:       { prep:['🌑','Prep'], entry:['🌒','Entry'], basic:['🌓','Basic'], intermediate:['🌔','Intermediate'], advanced:['🌕','Advanced'], high:['🌖','High'], fluency:['🌝','Fluency'] },
};

function metaAttrLabel(cat, key) {
  return ATTR_LABELS[cat]?.[key] || ['', String(key)];
}

// Shared ZH label lookup for attribute chips (used in both cardAttr and cardAttrMulti)
const ATTR_ZH = {
  register:    { literary:'文學體', formal:'正式', neutral:'標準', colloquial:'口語', informal:'非正式', slang:'俚語' },
  connotation: { positive:'褒義', neutral:'中性', negative:'貶義', 'context-dependent':'隨境' },
  channel:     { 'spoken-only':'純口語', 'spoken-dominant':'偏口語', fluid:'流動', 'written-dominant':'偏書面', 'written-only':'純書面' },
  dimension:   { abstract:'抽象', concrete:'具體', internal:'內在', external:'外在', fluid:'流動' },
  intensity:   { 1:'微', 2:'淡', 3:'中', 4:'濃', 5:'烈' },
  tocfl:       { prep:'準備', entry:'入門', basic:'基礎', advanced:'高階', high:'精通', fluency:'流利' },
};

// Chinese header names for attribute chips
const ATTR_HEADER_ZH = {
  register: '語域', connotation: '感情色彩', channel: '媒介',
  dimension: '維度', intensity: '強度', tocfl: '華測',
};

// Multi-value chip — e.g. Dimension: 🐙 Abstract · 🐢 Concrete
// Clicking anywhere on the chip toggles header + all values to/from Chinese.
function cardAttrMulti(cat, keys, header) {
  const showIcon  = iconsMode === 'on';
  const showLabel = uiMode !== 'icon-only';
  const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
  const isBoth  = (uiMode === 'all' || uiMode === 'en-zh');
  const hdrZh   = ATTR_HEADER_ZH[cat] || header;
  const initHdr = preferred === 'zh' ? hdrZh : isBoth ? `${header} · ${hdrZh}` : header;
  const valueHTML = keys.map(k => {
    const [icon, label] = metaAttrLabel(cat, k);
    const zhLabel = ATTR_ZH[cat]?.[k] || label;
    const initLabel = preferred === 'zh' ? zhLabel : isBoth ? `${label} · ${zhLabel}` : label;
    return `<span class="attr-val-item">${showIcon && icon ? `<span class="attr-icon">${icon}</span>` : ''}${showLabel ? `<span class="attr-label" data-en="${label}" data-zh="${zhLabel}" data-state="${preferred}">${initLabel}</span>` : ''}</span>`;
  }).join('');
  return `<div class="card-attr attr-${cat}" onclick="toggleAttrLang(event)">
    <div class="card-attr-header" data-en="${header}" data-zh="${hdrZh}" data-state="${preferred}">${initHdr}</div>
    <div class="card-attr-value multi">${valueHTML}</div>
  </div>`;
}

function cardAttr(cat, key, header, labelPair, extraClass = '') {
  const [icon, label] = labelPair;
  const showIcon  = iconsMode === 'on';
  const showLabel = uiMode !== 'icon-only';
  const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
  const isBoth  = (uiMode === 'all' || uiMode === 'en-zh');
  const zhLabel = ATTR_ZH[cat]?.[key] || label;
  const hdrZh   = ATTR_HEADER_ZH[cat] || header;
  const initLabel = preferred === 'zh' ? zhLabel : isBoth ? `${label} · ${zhLabel}` : label;
  const initHdr   = preferred === 'zh' ? hdrZh   : isBoth ? `${header} · ${hdrZh}`  : header;
  return `<div class="card-attr attr-${cat} ${extraClass}" onclick="toggleAttrLang(event)">
    <div class="card-attr-header" data-en="${header}" data-zh="${hdrZh}" data-state="${preferred}">${initHdr}</div>
    <div class="card-attr-value">
      ${showIcon && icon ? `<span class="attr-icon">${icon}</span>` : ''}
      ${showLabel ? `<span class="attr-label" data-en="${label}" data-zh="${zhLabel}" data-state="${preferred}">${initLabel}</span>` : ''}
    </div>
  </div>`;
}

function renderCard(w, idx) {
  const wordKey = w.traditional;
  const extras = [...(w.extraExamples || []), ...(EXTRA_SENTENCES[wordKey] || [])];
  const allDefaults = [w.example, ...extras];

  const defaultSentsHTML = allDefaults.map((s, i) => `
    <div class="default-sent">
      <span class="sent-num">${i + 1}</span>
      <div class="sent-body">
        <div class="sent-cn">${highlightWord(s.cn, w)}</div>
        <div class="sent-en">${s.en}</div>
      </div>
      <button class="save-sent-btn" onclick="saveDefault('${wordKey}', ${i})">＋ Save</button>
    </div>
  `).join('');

  // Collect all unique POS across definitions for the mobile header summary
  const allPOS = [...new Set((w.definitions || []).map(d => d.pos).filter(Boolean))];
  // Domain chip HTML (used in card-hdr-mid) — tappable to toggle EN ↔ ZH
  const domainChipHTML = (() => {
    const p    = DOMAIN_LABEL_MAP[w.domain];
    const pZh  = DOMAIN_LABEL_MAP_ZH[w.domain];
    const s    = w.secondaryDomain ? DOMAIN_LABEL_MAP[w.secondaryDomain]    : null;
    const sZh  = w.secondaryDomain ? DOMAIN_LABEL_MAP_ZH[w.secondaryDomain] : null;
    if (!p) return '';
    const enLabel = [p,   s  ].filter(Boolean).join(' ~ ');
    const zhLabel = [pZh, sZh].filter(Boolean).join(' ~ ');
    const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
    const display   = preferred === 'zh' ? zhLabel : (uiMode === 'all' || uiMode === 'en-zh') ? `${enLabel} · ${zhLabel}` : enLabel;
    return `<div class="card-domain-row"><span class="card-domain" data-en="${enLabel}" data-zh="${zhLabel}" data-state="${preferred}" onclick="toggleLangChip(event,this)" title="Tap to toggle 中文">${display}</span></div>`;
  })();
  // Simplified char (different from traditional)
  const simpCharVal = w.traditional !== w.simplified
    ? (scriptMode === 'simplified' ? w.traditional : w.simplified)
    : '';

  return `
  <div class="word-card" style="animation-delay:${idx * 0.04}s; cursor:pointer;" id="card-${wordKey}" onclick="openCard(event, '${wordKey}')">

    <!-- Zone 1: primary char + optional ⇌ switch icon -->
    <div class="card-hanzi">
      <div class="hanzi-primary-wrap">
        <span class="hanzi-char">${scriptMode === 'simplified' ? w.simplified : w.traditional}</span>
        ${simpCharVal ? `<button class="script-switch-btn" data-secondary="${simpCharVal}" onclick="toggleSecondaryChar(event,this)" title="Reveal ${scriptMode === 'simplified' ? 'traditional' : 'simplified'}">⇌</button>` : ''}
      </div>
    </div>

    <!-- Zone 2 (mobile only): domain chip + all-POS summary row + pinyin -->
    <div class="card-hdr-mid">
      ${domainChipHTML}
      ${allPOS.length ? `<div class="card-pos-summary">${allPOS.map(p => {
        const enText = `${posDisplay(p)} · ${POS_ABBR[p]||p}`;
        const zhText = `${POS_ZH[p]||posDisplay(p)} · ${POS_ABBR[p]||p}`;
        const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
        const display = preferred === 'zh' ? zhText : (uiMode === 'all' || uiMode === 'en-zh') ? `${enText} · ${zhText}` : enText;
        return `<span class="card-pos-hdr" data-en="${enText}" data-zh="${zhText}" data-state="${preferred}" onclick="toggleLangChip(event,this)" title="Tap to toggle 中文">${display}</span>`;
      }).join('')}</div>` : ''}
      ${w.pinyin ? `<div class="card-pinyin-row"><span class="pinyin pinyin-h">${w.pinyin}</span></div>` : ''}
    </div>

    <!-- Divider (mobile only) -->
    <hr class="card-divider">

    <!-- Definitions — full-width on mobile, col 2 on desktop -->
    <div class="card-body">
      ${(w.definitions || [{ pos: w.pos, def: w.definition }]).map(d => {
          const fml = d.formula || '';
          const fmlDisplay = scriptMode === 'simplified' && w.traditional !== w.simplified ? fml.replace(w.traditional, w.simplified) : fml;
          return `
          <div class="card-def-row">
            ${d.pos ? `<span class="card-pos" data-abbr="${POS_ABBR[d.pos] || d.pos}" data-full="${posDisplay(d.pos)}" data-zh="${POS_ZH[d.pos] || posDisplay(d.pos)}" data-state="abbr" title="Tap to cycle: abbr → EN → 中文" onclick="cyclePosChip(event, this)">${posLabel(d.pos)}</span>` : ''}
            <span class="card-definition">${d.def}</span>
          </div>
          ${fmlDisplay ? `<div class="card-formula">${fmlDisplay}</div>` : ''}
          ${d.usageNote ? `<div class="card-usage-note">${d.usageNote}</div>` : ''}`;
        }).join('')}
    </div>

    <!-- Meta attribute chips — full-width on mobile, col 2 on desktop -->
    <div class="card-meta">
      ${cardAttr('register',    w.register,    'Register',    metaAttrLabel('register', w.register))}
      ${cardAttr('connotation', w.connotation, 'Connotation', metaAttrLabel('connotation', w.connotation), connoClass[w.connotation])}
      ${cardAttr('channel',     w.channel,     'Channel',     metaAttrLabel('channel', w.channel))}
      ${(w.dimension||[]).length ? cardAttrMulti('dimension', w.dimension, 'Dimension') : ''}
      ${w.intensity ? cardAttr('intensity', w.intensity, 'Intensity', metaAttrLabel('intensity', w.intensity)) : ''}
      ${w.tocfl ? cardAttr('tocfl', w.tocfl, 'TOCFL', metaAttrLabel('tocfl', w.tocfl)) : ''}
    </div>

    <!-- CARD ACTIONS — save + share -->
    <div class="card-actions">
      <button class="card-action-btn" onclick="handleSaveToCollection(event, '${wordKey}')" title="Save to collection">＋</button>
      <button class="card-action-btn" onclick="handleShare(event, '${wordKey}')" title="Share this word">↗</button>
    </div>

    <!-- 造句 PANEL -->
    <div class="zaoju-panel">
      <div class="zaoju-header">
        <div class="zaoju-title">${
          langMode === 'zh' ? '造句' :
          langMode === 'both' ? 'Sentence Workshop · 造句' :
          'Sentence Workshop'
        }</div>
        <button class="zaoju-toggle" onclick="toggleZaoju('${wordKey}')">${workshopDefault === 'expanded' ? '▴ collapse' : '▾ expand'}</button>
      </div>

      <div id="zaoju-body-${wordKey}" style="display:${workshopDefault === 'expanded' ? 'flex' : 'none'}; flex-direction:column; gap:0.75rem;">

        <!-- Default sentences -->
        <div class="default-sentences">${defaultSentsHTML}</div>

        <!-- Saved deck -->
        <div id="deck-wrap-${wordKey}">${renderSavedDeck(wordKey)}</div>

        <!-- AI Tabs -->
        <div class="ai-tabs">
          <button class="ai-tab active" onclick="switchTab('${wordKey}', 'critique', this)">✍️ Write &amp; Get Feedback</button>
          <button class="ai-tab" onclick="switchTab('${wordKey}', 'theme', this)">🎯 Request a Themed Sentence</button>
        </div>

        <!-- CRITIQUE TAB -->
        <div id="tab-critique-${wordKey}" class="ai-workspace">
          <div class="ai-instruction">Write your own sentence using <strong style="color:var(--gold)">${w.traditional}</strong>. AI will check grammar, register, and naturalness — then you can save the result.</div>
          <div class="ai-input-row">
            <textarea class="ai-textarea" id="critique-input-${wordKey}" placeholder="在這裡寫你的句子… Write your sentence here…" rows="2"></textarea>
            <button class="ai-submit-btn" onclick="runCritique('${wordKey}')">分析 Analyse →</button>
          </div>
          <div id="critique-result-${wordKey}"></div>
        </div>

        <!-- THEME TAB -->
        <div id="tab-theme-${wordKey}" class="ai-workspace" style="display:none">
          <div class="ai-instruction">Ask AI to write a sentence using <strong style="color:var(--gold)">${w.traditional}</strong> around any theme, topic, or subject you love.</div>
          <div class="ai-input-row">
            <input type="text" class="ai-theme-input" id="theme-input-${wordKey}" placeholder="e.g. soccer, cooking, my grandmother, space travel…">
            <button class="ai-submit-btn" onclick="runTheme('${wordKey}')">生成 Generate →</button>
          </div>
          <div id="theme-result-${wordKey}"></div>
        </div>

      </div>
    </div>
  </div>`;
}

// ── ZAOJU INTERACTIONS ─────────────────────────────────────────────────────────
const zaojuOpen = {};

function toggleZaoju(key) {
  // Initialise from default if first toggle on this card
  if (zaojuOpen[key] === undefined) zaojuOpen[key] = workshopDefault === 'expanded';
  zaojuOpen[key] = !zaojuOpen[key];
  const body = document.getElementById(`zaoju-body-${key}`);
  const btn = body.closest('.zaoju-panel').querySelector('.zaoju-toggle');
  if (body) {
    body.style.display = zaojuOpen[key] ? 'flex' : 'none';
    if (btn) btn.textContent = zaojuOpen[key] ? '▴ collapse' : '▾ expand';
  }
}

function toggleSecondaryChar(e, btn) {
  e.stopPropagation();
  const secondary = btn.dataset.secondary;
  if (!secondary) return;
  const container = btn.closest('.card-hanzi');
  const existing = container.querySelector('.hanzi-secondary');
  if (existing) {
    existing.classList.add('leaving');
    existing.addEventListener('animationend', () => existing.remove(), { once: true });
    btn.style.opacity = '0.45';
  } else {
    const span = document.createElement('span');
    span.className = 'hanzi-secondary entering';
    span.textContent = secondary;
    container.appendChild(span);
    span.addEventListener('animationend', () => span.classList.remove('entering'), { once: true });
    btn.style.opacity = '1';
  }
}

function handleSaveToCollection(e, wordKey) {
  e.stopPropagation();
  // Phase 2: open collection picker dropdown
  alert('Collections coming soon — sign up to save words!');
}

function handleShare(e, wordKey) {
  e.stopPropagation();
  const word = WORDS.find(w => (w.smart_id || w.traditional) === wordKey);
  const text = word ? `${word.traditional} — ${(word.definitions||[])[0]?.def || ''}` : wordKey;
  if (navigator.share) {
    navigator.share({ title: '流動 Living Lexicon', text, url: window.location.href });
  } else {
    navigator.clipboard.writeText(window.location.href).then(() => {
      const btn = e.currentTarget;
      const orig = btn.innerHTML;
      btn.innerHTML = '✓ Copied';
      setTimeout(() => btn.innerHTML = orig, 1500);
    });
  }
}

function switchTab(wordKey, tab, btn) {
  const critiqueEl = document.getElementById(`tab-critique-${wordKey}`);
  const themeEl = document.getElementById(`tab-theme-${wordKey}`);
  const tabs = btn.closest('.ai-tabs').querySelectorAll('.ai-tab');
  tabs.forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  if (tab === 'critique') {
    critiqueEl.style.display = 'flex';
    themeEl.style.display = 'none';
  } else {
    critiqueEl.style.display = 'none';
    themeEl.style.display = 'flex';
  }
}

function saveDefault(wordKey, sentIdx) {
  const w = WORDS.find(x => x.traditional === wordKey);
  if (!w) return;
  const extras = EXTRA_SENTENCES[wordKey] || [];
  const allDefaults = [w.example, ...extras];
  const sent = allDefaults[sentIdx];
  const saved = saveToWord(wordKey, { cn: sent.cn, en: sent.en, source: 'Default example' });
  refreshDeckWrap(wordKey);
  // Grey out button
  const btn = document.querySelector(`#card-${wordKey} .default-sent:nth-child(${sentIdx + 1}) .save-sent-btn`);
  if (btn) { btn.textContent = '✓ Saved'; btn.classList.add('saved'); btn.disabled = true; }
}

function refreshDeckWrap(wordKey) {
  const wrap = document.getElementById(`deck-wrap-${wordKey}`);
  if (wrap) wrap.innerHTML = renderSavedDeck(wordKey);
}

async function runCritique(wordKey) {
  const w = WORDS.find(x => x.traditional === wordKey);
  if (!w) return;
  const input = document.getElementById(`critique-input-${wordKey}`);
  const resultEl = document.getElementById(`critique-result-${wordKey}`);
  const btn = input.closest('.ai-workspace').querySelector('.ai-submit-btn');
  const sentence = input.value.trim();
  if (!sentence) { resultEl.innerHTML = `<div class="ai-response"><div class="ai-response-text" style="color:var(--rose)">Please write a sentence first.</div></div>`; return; }

  btn.disabled = true; btn.textContent = '分析中…';
  resultEl.innerHTML = `<div class="ai-response"><div class="ai-response-text" style="color:var(--dim);font-style:italic">AI is reading your sentence…</div></div>`;

  try {
    const raw = await callAI(getCritiquePrompt(w), sentence);
    const clean = raw.replace(/```json|```/g, '').trim();
    const data = JSON.parse(clean);

    const verdictColor = data.verdict === 'correct' ? 'var(--jade)' : data.verdict === 'minor_issues' ? 'var(--gold)' : 'var(--rose)';
    const verdictLabel = data.verdict === 'correct' ? '✓ Correct' : data.verdict === 'minor_issues' ? '△ Minor issues' : '✗ Needs work';

    resultEl.innerHTML = `
      <div class="ai-response">
        <div class="ai-response-label" style="color:${verdictColor}">${verdictLabel}</div>
        <div class="ai-response-text">
          <span class="resp-cn">${highlightWord(data.corrected_cn, w)}</span>
          <span class="resp-en">${data.corrected_en}</span>
          <span class="resp-note">${data.feedback}</span>
          ${data.register_note ? `<span class="resp-note" style="margin-top:0.2rem">${data.register_note}</span>` : ''}
        </div>
        <div class="ai-response-actions">
          <button class="save-sent-btn" onclick="saveAIResult('${wordKey}', ${JSON.stringify(data.corrected_cn).replace(/'/g,"\\'")} , ${JSON.stringify(data.corrected_en).replace(/'/g,"\\'")} , 'My sentence (AI verified)')">＋ Save to My Dictionary</button>
        </div>
      </div>`;
  } catch(e) {
    resultEl.innerHTML = `<div class="ai-response"><div class="ai-response-text" style="color:var(--rose)">Something went wrong. Please try again.</div></div>`;
  }
  btn.disabled = false; btn.textContent = '分析 Analyse →';
}

async function runTheme(wordKey) {
  const w = WORDS.find(x => x.traditional === wordKey);
  if (!w) return;
  const input = document.getElementById(`theme-input-${wordKey}`);
  const resultEl = document.getElementById(`theme-result-${wordKey}`);
  const btn = input.closest('.ai-workspace').querySelector('.ai-submit-btn');
  const theme = input.value.trim();
  if (!theme) { resultEl.innerHTML = `<div class="ai-response"><div class="ai-response-text" style="color:var(--rose)">Please enter a theme or subject.</div></div>`; return; }

  btn.disabled = true; btn.textContent = '生成中…';
  resultEl.innerHTML = `<div class="ai-response"><div class="ai-response-text" style="color:var(--dim);font-style:italic">Crafting your sentence…</div></div>`;

  try {
    const raw = await callAI(getThemePrompt(w), `Theme/subject: ${theme}`);
    const clean = raw.replace(/```json|```/g, '').trim();
    const data = JSON.parse(clean);

    resultEl.innerHTML = `
      <div class="ai-response">
        <div class="ai-response-label">✦ AI-Generated · Theme: ${theme}</div>
        <div class="ai-response-text">
          <span class="resp-cn">${highlightWord(data.cn, w)}</span>
          <span class="resp-en">${data.en}</span>
          <span class="resp-note">${data.note}</span>
        </div>
        <div class="ai-response-actions">
          <button class="save-sent-btn" onclick="saveAIResult('${wordKey}', ${JSON.stringify(data.cn).replace(/'/g,"\\'")} , ${JSON.stringify(data.en).replace(/'/g,"\\'")} , 'AI · ${theme}')">＋ Save to My Dictionary</button>
        </div>
      </div>`;
  } catch(e) {
    resultEl.innerHTML = `<div class="ai-response"><div class="ai-response-text" style="color:var(--rose)">Something went wrong. Please try again.</div></div>`;
  }
  btn.disabled = false; btn.textContent = '生成 Generate →';
}

function saveAIResult(wordKey, cn, en, source) {
  saveToWord(wordKey, { cn, en, source });
  refreshDeckWrap(wordKey);
  // Find and update save button
  event.target.textContent = '✓ Saved';
  event.target.classList.add('saved');
  event.target.disabled = true;
}



function buildActiveTags() {
  const tags = [];
  function tagged(icon, label) {
    return `<span class="tag-icon">${icon}</span>${label}`;
  }
  const labels = {
    register:    { literary: tagged('🦋','Literary'), formal: tagged('🐝','Formal'), neutral: tagged('🐞','Standard'), colloquial: tagged('🪲','Colloquial'), informal: tagged('🦗','Informal'), slang: tagged('🕷️','Slang') },
    connotation: { positive: tagged('☀️','Positive'), neutral: tagged('⛅','Neutral'), 'context-dependent': tagged('🌦️','Context'), negative: tagged('⛈️','Negative'), 'positive-dominant': tagged('🌤️','Pos-Dom'), 'negative-dominant': tagged('🌧️','Neg-Dom') },
    channel:     { 'spoken-only': tagged('🦎','Spoken Only'), 'spoken-dominant': tagged('🐍','Spoken Dominant'), fluid: tagged('🦜','Fluid'), 'written-dominant': tagged('🦚','Written Dominant'), 'written-only': tagged('🐉','Written Only') },
    dimension:   { abstract: tagged('🐙','Abstract'), concrete: tagged('🐢','Concrete'), internal: tagged('🐟','Internal'), external: tagged('🦂','External'), fluid: tagged('🦀','Fluid') },
  };
  const items = [];
  state.register.forEach(v    => items.push({ group:'register',    val:v, html: labels.register[v]    || v }));
  state.connotation.forEach(v => items.push({ group:'connotation', val:v, html: labels.connotation[v] || v }));
  state.channel.forEach(v     => items.push({ group:'channel',     val:v, html: labels.channel[v]     || v }));
  state.dimension.forEach(v   => items.push({ group:'dimension',   val:v, html: labels.dimension[v]   || v }));
  const intIcons  = ['','🌸','🌼','🪷','🌻','🌺'];
  const intLabels = ['','微','淡','中','濃','烈'];
  state.intensity.forEach(v => items.push({ group:'intensity', val:v, html: `<span class="tag-icon">${intIcons[v]}</span>${intLabels[v]}` }));
  const tocflLabels = { prep:'準備', entry:'入門', basic:'基礎', intermediate:'進階', advanced:'高階', high:'精通', fluency:'流利' };
  state.tocfl.forEach(v => items.push({ group:'tocfl', val:v, html: tocflLabels[v] || v }));
  return items.map(t => `<span class="active-filter-tag removable" data-group="${t.group}" data-val="${t.val}" title="Click to remove">${t.html}<span class="tag-remove">✕</span></span>`).join('');
}

function render() {
  rerenderLabels();
  const container = document.getElementById('cardContainer');
  const countEl    = document.getElementById('countNum');
  const countQuery = document.getElementById('countQuery');
  const tagsEl    = document.getElementById('activeTags');

  // Nothing to show until the user provides a search query or applies a filter
  const hasQuery  = searchQuery.trim().length > 0;
  const hasFilter = state.register.length || state.connotation.length ||
                    state.channel.length  || state.dimension.length  ||
                    state.intensity.length || (state.tocfl||[]).length ||
                    (state.hsk||[]).length ||
                    posFilter || domainFilter || relFilter;

  if (!hasQuery && !hasFilter) {
    container.innerHTML = '';
    countEl.textContent = '—';
    if (countQuery) countQuery.textContent = '';
    tagsEl.innerHTML = '';
    return;
  }

  const matched = WORDS.filter(matchWord);
  countEl.textContent = matched.length;
  if (countQuery) countQuery.innerHTML = searchQuery.trim() ? ` for <em>"${searchQuery.trim()}"</em>` : '';
  tagsEl.innerHTML = buildActiveTags();
  if (matched.length === 0) {
    container.innerHTML = `<div class="empty-state">No words match this combination.<small>Try relaxing a filter above</small></div>`;
  } else {
    container.innerHTML = matched.map(renderCard).join('');
  }
}

// ── INTENSITY — handled by standard d-chip dropdown system above ──────────────
const INT_CHARS = { '1':'微', '2':'淡', '3':'中', '4':'濃', '5':'烈' };

// Clear scenario UI selection (select dropdown + active card highlight).
// Called by the Reset button to deactivate any active scenario without
// re-running resetState/syncUI/render (those follow separately).
function clearScenes() {
  const sel = document.getElementById('scenarioSelect');
  if (sel) sel.value = '';
  document.querySelectorAll('.scenario-card').forEach(c => c.classList.remove('active'));
}

// Reset
document.getElementById('resetBtn').addEventListener('click', () => {
  resetState(); clearScenes(); syncUI(); render();
});

// Click active filter tags to remove them
document.getElementById('activeTags').addEventListener('click', e => {
  const tag = e.target.closest('.active-filter-tag.removable');
  if (!tag) return;
  const group = tag.dataset.group;
  const val   = tag.dataset.val;
  if (!group || !val || !state[group]) return;
  const idx = state[group].indexOf(val);
  if (idx !== -1) state[group].splice(idx, 1);
  // Deselect the corresponding chip
  const chip = document.querySelector(`.d-chip[data-group="${group}"][data-val="${val}"]`);
  if (chip) chip.classList.remove('selected');
  updatePreview(group);
  render();
});

function resetState() {
  state = { register: [], connotation: [], channel: [], dimension: [], intensity: [], hsk: [], tocfl: [] };
  resetRefineFilters();
}

function resetRefineFilters() {
  posFilter = ''; relFilter = ''; domainFilter = '';
  ['posRefineSelect', 'relRefineSelect', 'domainRefineSelect'].forEach(id => {
    const sel = document.getElementById(id);
    if (sel) { sel.value = ''; sel.classList.remove('active'); }
  });
  syncRefineReset();
  render();
}

function syncRefineReset() {
  const btn = document.getElementById('refineReset');
  if (btn) btn.classList.toggle('visible', !!(posFilter || relFilter || domainFilter));
}

function syncUI() {
  // Update all d-chip selected states (covers intensity now too)
  document.querySelectorAll('.d-chip[data-group]').forEach(c => {
    const arr = state[c.dataset.group] || [];
    c.classList.toggle('selected', arr.includes(c.dataset.val));
  });
  // Update all previews
  ['register', 'connotation', 'channel', 'dimension', 'intensity', 'tocfl'].forEach(updatePreview);
  // Close any open dropdown
  closeAllDropdowns();
}

// ── SCENARIOS ─────────────────────────────────────────────────────────────────
// ── SCENARIO SYSTEM (merged presets + custom saved) ──────────────────────────
const BUILT_IN_SCENARIOS = {
  beginner:   { register: ['colloquial'], connotation: ['positive'], channel: ['spoken'],  dimension: [], intensity: [], tocfl: ['prep'] },
  classicist: { register: ['literary'],   connotation: ['negative'], channel: ['written'], dimension: [], intensity: ['3','4','5'], hsk: [],             tocfl: ['advanced','fluency'] },
  essay:      { register: ['formal'],     connotation: [],           channel: ['written'], dimension: [], intensity: [], tocfl: ['basic'] },
  exchange:   { register: ['colloquial'], connotation: ['neutral'],  channel: ['spoken'],  dimension: [], intensity: [],          hsk: [],               tocfl: ['prep','entry','basic'] },
  literature: { register: ['literary'],   connotation: [],           channel: ['written'], dimension: [], intensity: ['3','4','5'], hsk: [],             tocfl: ['advanced','fluency'] },
  business:   { register: ['formal'],     connotation: ['positive'], channel: ['spoken'],  dimension: [], intensity: ['2','3','4','5'], tocfl: [] },
  creative:   { register: ['literary'],   connotation: [],           channel: ['both'],    dimension: [], intensity: [],          tocfl: [] },
};

// Custom scenarios stored in memory
let customScenarios = [];

function applyScenario(filters) {
  state = {
    register:        filters.register        || [],
    connotation:     filters.connotation     || [],
    channel:         filters.channel         || [],
    dimension:       filters.dimension       || [],
    intensity:       filters.intensity       || [],
    hsk:             filters.hsk             || [],
    tocfl:           filters.tocfl           || [],
  };
  syncUI();
  render();
}

function clearScenario() {
  const _ss = document.getElementById('scenarioSelect');
  if (_ss) _ss.value = '';
  document.querySelectorAll('.scenario-card').forEach(c => c.classList.remove('active'));
  resetState(); syncUI(); render();
}

function rebuildCustomOptgroup() {
  const og = document.getElementById('customOptgroup');
  og.innerHTML = '';
  customScenarios.forEach((s, i) => {
    const opt = document.createElement('option');
    opt.value = '__custom__' + i;
    opt.textContent = '⭐ ' + s.name;
    og.appendChild(opt);
  });
  og.style.display = customScenarios.length ? '' : 'none';
  rebuildCustomScenariosGrid();
  // Re-add the "create" option at end (it may have been displaced)
  const sel = document.getElementById('scenarioSelect');
  if (sel) {
    let createOpt = sel.querySelector('[value="__create__"]');
    if (createOpt) sel.appendChild(createOpt);
  }
}

document.getElementById('scenarioSelect')?.addEventListener('change', function() {
  const val = this.value;
  if (!val) return;
  if (val === '__create__') {
    this.value = '';
    openSaveDialog();
    return;
  }
  if (val.startsWith('__custom__')) {
    const idx = parseInt(val.replace('__custom__', ''));
    applyScenario(customScenarios[idx].filters);
    return;
  }
  const preset = BUILT_IN_SCENARIOS[val];
  if (preset) applyScenario(preset);
});

// ── SAVE DIALOG ───────────────────────────────────────────────────────────────
function openSaveDialog() {
  document.getElementById('scenarioNameInput').value = '';
  document.getElementById('scenarioDialogOverlay').classList.add('open');
  setTimeout(() => document.getElementById('scenarioNameInput').focus(), 50);
}

function closeSaveDialog() {
  document.getElementById('scenarioDialogOverlay').classList.remove('open');
}

function confirmSaveScenario() {
  const name = document.getElementById('scenarioNameInput').value.trim();
  if (!name) return;
  customScenarios.push({ name, filters: { ...state } });
  rebuildCustomOptgroup();
  // Select the newly saved scenario
  const sel = document.getElementById('scenarioSelect');
  if (sel) sel.value = '__custom__' + (customScenarios.length - 1);
  closeSaveDialog();
}

// Close dialog on overlay click
document.getElementById('scenarioDialogOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeSaveDialog();
});

// Enter key in name input confirms
document.getElementById('scenarioNameInput').addEventListener('keydown', function(e) {
  if (e.key === 'Enter') confirmSaveScenario();
  if (e.key === 'Escape') closeSaveDialog();
});

// ── OPEN WORD PAGE ───────────────────────────────────────────────────────────
function openCard(event, wordKey) {
  // Don't navigate if user clicked a button inside the card (save, AI, etc.)
  if (event.target.closest('button, .ai-workspace, .zaoju-panel, .card-pos, .card-def-row, .card-hanzi, .card-actions')) return;
  const word = WORDS.find(w => w.traditional === wordKey || w.smart_id === wordKey);
  window.location.href = '/lexicon/' + encodeURIComponent(word?.smart_id || wordKey);
}

// ── INIT ──────────────────────────────────────────────────────────────────────
setSidebarWidth(uiMode);
rerenderLabels();
// Sync workshop toggle buttons with persisted preference
document.getElementById('btnWorkshopExpanded').classList.toggle('active', workshopDefault === 'expanded');
document.getElementById('btnWorkshopCollapsed').classList.toggle('active', workshopDefault === 'collapsed');
// Initialise all sliding pills (no transition on first paint — just snap into position)
['scriptToggle','posToggle','langToggle','iconsToggle','pinyinToggle','workshopToggle'].forEach(updateTogglePill);
if (INITIAL_SEARCH) {
  const si = document.getElementById('searchInput');
  if (si) si.value = INITIAL_SEARCH;
  searchQuery = INITIAL_SEARCH;
}
render();

// ── BACK TO TOP ───────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('backToTop');
  if (!btn) return;
  window.addEventListener('scroll', () => {
    btn.classList.toggle('visible', window.scrollY > 280);
  }, { passive: true });
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
});
</script>

<button id="backToTop" aria-label="Back to top">⌃</button>
</body>
</html>
