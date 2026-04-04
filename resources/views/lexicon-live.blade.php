<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>流動 Living Lexicon — Flow 流</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@300;400;600;700&family=DM+Mono:ital,wght@0,300;0,400;1,300&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
@include('partials.lexicon._foundations')
@include('partials.lexicon._attr-chip-css')
@include('partials.lexicon._definition-css')
@include('partials.lexicon._word-header-css')
@include('partials.lexicon._example-sentence-css')
@include('partials.lexicon._workshop-css')
<style>
/* ── HEADER (uses shared _site-header partial) ── */
.site-header + .search-bar-wrap { padding-top: 0.75rem; }

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

/* ── FILTER BAR — desktop: 2 rows of 3 + reset full-width ── */
.filter-bar {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  background: #f5f4f8;
}

/* Each filter group fills its cell */
.filter-row {
  display: flex; flex-direction: column;
  align-items: flex-start;
  position: relative;
  cursor: pointer;
  border-right: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  user-select: none;
  transition: background 0.15s;
}
/* Remove right border on every 3rd column */
.filter-bar .filter-row:nth-child(3n) { border-right: none; }
/* Remove bottom border on last row (4th, 5th, 6th) */
.filter-bar .filter-row:nth-child(n+4) { border-bottom: none; }

/* Reset as the 7th grid item — full width below */
.filter-reset-btn {
  grid-column: 1 / -1;
  display: flex; align-items: center; justify-content: center;
  padding: 0.65rem 1rem;
  border: none; border-top: 1px solid var(--border);
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

/* Per-group permanent label colours — spectrum cascade (rich) */
.frow-register   .filter-row-label,
.frow-register   .filter-row-chevron { color: #12a84e; }
.frow-connotation .filter-row-label,
.frow-connotation .filter-row-chevron { color: #2468d0; }
.frow-channel    .filter-row-label,
.frow-channel    .filter-row-chevron { color: #d03030; }
.frow-dimension  .filter-row-label,
.frow-dimension  .filter-row-chevron { color: #d47818; }
.frow-intensity  .filter-row-label,
.frow-intensity  .filter-row-chevron { color: #a0602a; }
.frow-tocfl      .filter-row-label,
.frow-tocfl      .filter-row-chevron { color: #c4a808; }

/* Full strength on open/active */
.frow-register.open   .filter-row-label, .frow-register.has-selection   .filter-row-label,
.frow-register.open   .filter-row-chevron { color: #12a84e; }
.frow-connotation.open .filter-row-label, .frow-connotation.has-selection .filter-row-label,
.frow-connotation.open .filter-row-chevron { color: #2468d0; }
.frow-channel.open    .filter-row-label, .frow-channel.has-selection    .filter-row-label,
.frow-channel.open    .filter-row-chevron { color: #d03030; }
.frow-dimension.open  .filter-row-label, .frow-dimension.has-selection  .filter-row-label,
.frow-dimension.open  .filter-row-chevron { color: #d47818; }
.frow-intensity.open  .filter-row-label, .frow-intensity.has-selection  .filter-row-label,
.frow-intensity.open  .filter-row-chevron { color: #a0602a; }
.frow-tocfl.open      .filter-row-label, .frow-tocfl.has-selection      .filter-row-label,
.frow-tocfl.open      .filter-row-chevron { color: #c4a808; }

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
  position: fixed;
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
  width: 2.4rem; height: 2.4rem; flex-shrink: 0;
  font-size: 1.72rem; line-height: 1; overflow: hidden;
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
  max-height: 1400px;
  border-bottom: 1px solid var(--border);
}
.acc-panel-inner {
  padding: 1.4rem 1rem 1.2rem;
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
.sc-delete {
  position: absolute; top: -0.3rem; right: -0.3rem;
  width: 1.2rem; height: 1.2rem; border-radius: 50%;
  background: var(--surface); border: 1px solid var(--border);
  color: var(--dim); font-size: 0.7rem; line-height: 1;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: color 0.15s, border-color 0.15s;
  z-index: 2;
}
.sc-delete:hover { color: var(--danger, #c00); border-color: var(--danger, #c00); }
.acc-close-btn {
  display: block; margin: 0.9rem 0 0 auto; padding: 0.25rem 0.6rem;
  font-family: 'DM Mono', monospace; font-size: 0.62rem;
  color: var(--dim); background: transparent; border: none;
  cursor: pointer; transition: color 0.15s;
}
.acc-close-btn:hover { color: var(--text); }

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

/* ── FILTER BAR — PER-GROUP COLOUR REINFORCEMENT (spectrum cascade) ───────── */
/* Register — green */
.frow-register                                   { background: rgba(18,168,78,0.06); }
.frow-register:hover                             { background: rgba(18,168,78,0.12); }
.frow-register.has-selection,
.frow-register.open                              { background: rgba(18,168,78,0.18); }
.frow-register.has-selection .filter-row-label,
.frow-register.open .filter-row-label            { color: #12a84e; }
.frow-register.has-selection .filter-row-chevron,
.frow-register.open .filter-row-chevron          { color: #12a84e; }
.frow-register .preview-text                     { background: rgba(18,168,78,0.08); border-color: rgba(18,168,78,0.2); color: #12a84e; }
.frow-register .d-chip:hover                     { background: rgba(18,168,78,0.04); border-color: rgba(18,168,78,0.25); color: #12a84e; }
.frow-register .d-chip.selected                  { background: rgba(18,168,78,0.12); border-color: #12a84e; color: #12a84e; }

/* Connotation — blue */
.frow-connotation                                { background: rgba(36,104,208,0.06); }
.frow-connotation:hover                          { background: rgba(36,104,208,0.12); }
.frow-connotation.has-selection,
.frow-connotation.open                           { background: rgba(36,104,208,0.18); }
.frow-connotation.has-selection .filter-row-label,
.frow-connotation.open .filter-row-label         { color: #2468d0; }
.frow-connotation.has-selection .filter-row-chevron,
.frow-connotation.open .filter-row-chevron       { color: #2468d0; }
.frow-connotation .preview-text                  { background: rgba(36,104,208,0.08); border-color: rgba(36,104,208,0.2); color: #2468d0; }
.frow-connotation .d-chip:hover                  { background: rgba(36,104,208,0.04); border-color: rgba(36,104,208,0.25); color: #2468d0; }
.frow-connotation .d-chip.selected               { background: rgba(36,104,208,0.12); border-color: #2468d0; color: #2468d0; }

/* Channel — red */
.frow-channel                                    { background: rgba(208,48,48,0.06); }
.frow-channel:hover                              { background: rgba(208,48,48,0.12); }
.frow-channel.has-selection,
.frow-channel.open                               { background: rgba(208,48,48,0.18); }
.frow-channel.has-selection .filter-row-label,
.frow-channel.open .filter-row-label             { color: #d03030; }
.frow-channel.has-selection .filter-row-chevron,
.frow-channel.open .filter-row-chevron           { color: #d03030; }
.frow-channel .preview-text                      { background: rgba(208,48,48,0.08); border-color: rgba(208,48,48,0.2); color: #d03030; }
.frow-channel .d-chip:hover                      { background: rgba(208,48,48,0.04); border-color: rgba(208,48,48,0.25); color: #d03030; }
.frow-channel .d-chip.selected                   { background: rgba(208,48,48,0.1);  border-color: #d03030; color: #d03030; }

/* Dimension — orange */
.frow-dimension                                  { background: rgba(212,120,24,0.06); }
.frow-dimension:hover                            { background: rgba(212,120,24,0.12); }
.frow-dimension.has-selection,
.frow-dimension.open                             { background: rgba(212,120,24,0.18); }
.frow-dimension.has-selection .filter-row-label,
.frow-dimension.open .filter-row-label           { color: #d47818; }
.frow-dimension.has-selection .filter-row-chevron,
.frow-dimension.open .filter-row-chevron         { color: #d47818; }
.frow-dimension .preview-text                    { background: rgba(212,120,24,0.08); border-color: rgba(212,120,24,0.2); color: #d47818; }
.frow-dimension .d-chip:hover                    { background: rgba(212,120,24,0.04); border-color: rgba(212,120,24,0.25); color: #d47818; }
.frow-dimension .d-chip.selected                 { background: rgba(212,120,24,0.1);  border-color: #d47818; color: #d47818; }

/* Intensity — brown */
.frow-intensity                                  { background: rgba(160,96,42,0.06); }
.frow-intensity:hover                            { background: rgba(160,96,42,0.12); }
.frow-intensity.has-selection,
.frow-intensity.open                             { background: rgba(160,96,42,0.18); }
.frow-intensity.has-selection .filter-row-label,
.frow-intensity.open .filter-row-label           { color: #a0602a; }
.frow-intensity.has-selection .filter-row-chevron,
.frow-intensity.open .filter-row-chevron         { color: #a0602a; }
.frow-intensity .preview-text                    { background: rgba(160,96,42,0.08); border-color: rgba(160,96,42,0.2); color: #a0602a; }
.frow-intensity .d-chip:hover                    { background: rgba(160,96,42,0.04); border-color: rgba(160,96,42,0.25); color: #a0602a; }
.frow-intensity .d-chip.selected                 { background: rgba(160,96,42,0.1);  border-color: #a0602a; color: #a0602a; }

/* TOCFL — yellow */
.frow-tocfl                                      { background: rgba(196,168,8,0.06); }
.frow-tocfl:hover                                { background: rgba(196,168,8,0.12); }
.frow-tocfl.has-selection,
.frow-tocfl.open                                 { background: rgba(196,168,8,0.18); }
.frow-tocfl.has-selection .filter-row-label,
.frow-tocfl.open .filter-row-label               { color: #c4a808; }
.frow-tocfl.has-selection .filter-row-chevron,
.frow-tocfl.open .filter-row-chevron             { color: #c4a808; }
.frow-tocfl .preview-text                        { background: rgba(196,168,8,0.08); border-color: rgba(196,168,8,0.2); color: #c4a808; }
.frow-tocfl .d-chip:hover                        { background: rgba(196,168,8,0.04); border-color: rgba(196,168,8,0.25); color: #c4a808; }
.frow-tocfl .d-chip.selected                     { background: rgba(196,168,8,0.1);  border-color: #c4a808; color: #c4a808; }

/* Domain + character header styles loaded from shared partial */

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
  padding: 0.6rem 0.75rem;
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

/* ── SENTENCE SEARCH CARDS ── */
.sentence-results-header {
  font-family: 'DM Mono', monospace;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
  color: var(--dim);
  margin-bottom: 0.5rem;
}
.sentence-results {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}
.sentence-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 2px;
  padding: 0.6rem 0.75rem;
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 0 0.75rem;
  align-items: start;
  cursor: pointer;
  transition: border-color 0.2s, transform 0.15s;
  animation: cardIn 0.25s ease both;
}
.sentence-card:hover { border-color: rgba(98,64,200,0.25); transform: translateY(-1px); }
.sentence-card--unknown { opacity: 0.5; cursor: default; }
.sentence-card--unknown:hover { border-color: var(--border); transform: none; }
.sentence-card-char {
  font-family: BiauKai, STKaiti, KaiTi, '楷體-繁', 'Noto Serif TC', serif;
  font-size: var(--fs-char, 2.6rem);
  line-height: 1.15;
  color: var(--ink);
}
.sentence-mode .results-refine { display: none !important; }
.sentence-mode .results-count { display: none !important; }
.sentence-mode .scenario-bar { display: none !important; }
.sentence-mode .filter-strip { display: none !important; }
.sentence-card-char.vertical {
  writing-mode: vertical-rl;
  text-orientation: mixed;
  letter-spacing: 0.08em;
}
.sentence-card-body {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding-top: 0.2rem;
}
.sentence-card-def {
  display: flex;
  align-items: baseline;
  gap: 0.35rem;
  font-size: 0.95rem;
  line-height: 1.4;
}
.sentence-card-def .card-pos {
  font-size: 0.65rem;
  flex-shrink: 0;
}
.sentence-card-def-text {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.05rem;
  color: var(--ink);
}
.sentence-card-def--dim {
  color: var(--dim);
  font-style: italic;
  font-size: 0.85rem;
}
.sentence-card-pinyin {
  margin-top: 0.15rem;
}
.sentence-card-pinyin .pinyin {
  font-size: 0.85rem;
  color: var(--dim);
}

/* ── Slim search result cards ────────────────────────────────────────────── */
.word-card--slim {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 2px;
  padding: 0.6rem 0.75rem;
  display: grid;
  position: relative;
  overflow: visible;
  grid-template-columns: auto 1fr;
  gap: 0 0.75rem;
  align-items: start;
  cursor: pointer;
  transition: border-color 0.2s, transform 0.15s;
  animation: cardIn 0.25s ease both;
}
.word-card--slim:hover { border-color: rgba(98,64,200,0.25); transform: translateY(-1px); }
.slim-card-char-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.2rem;
}
.slim-card-char-wrap {
  display: flex;
  align-items: flex-start;
  gap: 0.15rem;
}
.slim-card-char-col .hanzi-secondary {
  font-family: BiauKai, STKaiti, KaiTi, '楷體-繁', 'Noto Serif TC', serif;
  font-size: calc(var(--fs-char, 2.6rem) * 0.45);
  color: var(--dim);
  line-height: 1.15;
  writing-mode: vertical-rl;
  text-orientation: mixed;
}
.slim-card-char {
  font-family: BiauKai, STKaiti, KaiTi, '楷體-繁', 'Noto Serif TC', serif;
  font-size: var(--fs-char, 2.6rem);
  line-height: 1.15;
  color: var(--ink);
}
.slim-card-char.vertical {
  writing-mode: vertical-rl;
  text-orientation: mixed;
  letter-spacing: 0.08em;
}
.slim-script-toggle {
  background: none;
  border: 1px solid var(--border);
  border-radius: 3px;
  color: var(--dim);
  font-size: 0.75rem;
  padding: 0.1rem 0.3rem;
  cursor: pointer;
  line-height: 1;
  transition: color 0.2s, border-color 0.2s;
}
.slim-script-toggle:hover {
  color: var(--ink);
  border-color: var(--ink);
}
.slim-card-body {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding-top: 0.2rem;
}
.slim-card-pinyin {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.15rem;
}
.slim-card-pinyin .pinyin {
  font-size: 0.85rem;
  color: var(--dim);
}
.slim-level-badge {
  font-size: 0.6rem;
  font-weight: 600;
  text-transform: uppercase;
  padding: 0.1rem 0.35rem;
  border-radius: 2px;
  letter-spacing: 0.05em;
  color: #fff;
  background: var(--dim);
}
.slim-level-novice1  { background: #8ca0b8; }
.slim-level-novice2  { background: #8ca0b8; }
.slim-level-entry    { background: #7b9a6d; }
.slim-level-basic    { background: #b88c3f; }
.slim-level-advanced { background: #b06030; }
.slim-level-high     { background: #9c4060; }
.slim-level-fluency  { background: #6840a0; }

/* Character display, domain, POS summary, pinyin styles loaded from shared partial */
.card-divider {
  grid-column: 1 / -1;
  border: none; border-top: 1px solid var(--border);
  margin: 0.6rem 0 0.45rem;
}
.card-body { grid-column: 1 / -1; }
.card-meta { grid-column: 1 / -1; }

/* Sense block: groups definitions + their attribute chips per sense */
.card-sense-block + .card-sense-block {
  margin-top: 0.6rem;
  padding-top: 0.5rem;
  border-top: 1px dashed var(--border);
}
/* Per-sense attribute chips: slightly smaller spacing than standalone card-meta */
.card-meta.card-meta-sense {
  margin-top: 0.35rem;
  padding-top: 0.35rem;
  border-top: none;
}

/* Pinyin off — hide all pronunciation rows without re-rendering */
#cardContainer.no-pinyin .card-pinyin-row { display: none; }

.card-content { display: flex; flex-direction: column; }
.card-right {
  display: flex; flex-direction: column; gap: 0;
}
.card-body { display: flex; flex-direction: column; gap: 0.5rem; }
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

/* Card hero actions — save + share beneath pinyin */
.card-hero-actions {
  display: flex; gap: 0.5rem; margin-top: 0.35rem;
  position: relative;
}
.card-hero-btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 2.8rem; height: 2.2rem;
  background: var(--surface); border: 1.5px solid var(--accent);
  border-radius: 4px; cursor: pointer;
  font-size: 1.3rem; color: var(--accent);
  transition: all 0.15s; opacity: 0.6;
}
.card-hero-btn:hover { opacity: 1; background: rgba(98,64,200,0.06); }
.card-hero-btn.saved { color: var(--accent); opacity: 1; background: rgba(98,64,200,0.08); }

/* Legacy card action buttons (kept for reference) */
.card-actions {
  grid-column: 1 / -1;
  display: none; /* moved to hero */
  gap: 0.5rem;
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
.card-action-btn.saved { color: var(--accent); border-color: var(--accent); }
/* Collection picker popover (SRP) */
.card-hero-actions { position: relative; }
.srp-cp {
  position: absolute; top: 100%; left: 0; z-index: 9999;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 3px; min-width: 200px; padding: 0.4rem 0;
  box-shadow: 0 8px 28px rgba(0,0,0,0.12);
  animation: srpCpIn 0.15s ease;
  margin-bottom: 0.4rem;
}
@keyframes srpCpIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:none; } }
.srp-cp-title {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--dim); padding: 0.3rem 0.65rem 0.2rem;
}
.srp-cp-item {
  display: flex; align-items: center; gap: 0.4rem;
  padding: 0.3rem 0.65rem;
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  color: var(--text); cursor: pointer; transition: background 0.12s;
}
.srp-cp-item:hover { background: rgba(0,0,0,0.03); }
.srp-cp-item input[type="checkbox"] { accent-color: var(--accent); margin: 0; flex-shrink: 0; }
.srp-cp-empty {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--dim); padding: 0.3rem 0.65rem; font-style: italic;
}
.srp-cp-new {
  border-top: 1px solid var(--border); margin-top: 0.25rem;
  padding: 0.4rem 0.65rem 0.2rem;
  display: flex; align-items: center; gap: 0.3rem;
}
.srp-cp-new input {
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  border: 1px solid var(--border); border-radius: 2px;
  padding: 0.25rem 0.4rem; flex: 1; outline: none;
  background: var(--surface); color: var(--text);
}
.srp-cp-new input:focus { border-color: var(--accent); }
.srp-cp-new button {
  font-size: 0.9rem; background: none; border: none;
  color: var(--accent); cursor: pointer; padding: 0; line-height: 1;
}

/* Character in hero is the IWP link — show pointer on hover */
.card-hanzi { cursor: pointer; transition: opacity 0.15s; }
.card-hanzi:hover { opacity: 0.7; }
.word-card { position: relative; }
.card-saved-star {
  position: absolute; top: 0.45rem; right: 0.5rem;
  font-size: 0.7rem; color: var(--accent); opacity: 0.75;
  pointer-events: none; line-height: 1;
}

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

/* ── Workshop styles: loaded from shared partial _workshop-css ── */

/* ── PROFILE PRESETS ── */
/* profiles-bar removed — merged into scenario-bar */

/* ── RESPONSIVE ── */
@media (max-width: 700px) {
  main { grid-template-columns: 1fr; }

  /* Filter bar: 2 columns on mobile, 3 rows of 2 */
  .filter-bar { grid-template-columns: repeat(2, 1fr); }
  /* Reset nth-child rules for 2-col layout */
  .filter-bar .filter-row:nth-child(3n) { border-right: 1px solid var(--border); }
  .filter-bar .filter-row:nth-child(2n) { border-right: none; }
  .filter-bar .filter-row:nth-child(n+4) { border-bottom: 1px solid var(--border); }
  .filter-bar .filter-row:nth-child(n+5) { border-bottom: none; }

  /* Refine bar: selects stack and each takes full row width */
  .results-refine { gap: 0.4rem; }
  .refine-select { flex: 1 1 100%; min-width: 0; }
  .refine-label { width: 100%; }

  /* Results panel tighter on small screens */
  .results-panel { padding: 1rem; }

  /* Workshop responsive rules now in shared partial */
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
    grid-column: 1; grid-row: 1 / 3;  /* span hdr-mid + body rows */
    border-right: 1px solid var(--border);
    padding-right: 1.2rem;
  }
  .card-hdr-mid    { grid-column: 2; grid-row: 1; }
  .card-body       { grid-column: 2; grid-row: 2; }
  /* Per-sense meta chips now live inside card-body; 3-col grid on desktop */
  .card-meta.card-meta-sense { grid-template-columns: repeat(3, 1fr); }
  .card-divider    { display: none; }
  /* Restore domain chip to normal inline size */
  .card-hdr-mid .card-domain {
    display: inline-block; width: auto;
    font-size: 0.81rem; padding: 0.15rem 0.6rem; text-align: left;
  }
  .card-hdr-mid .card-domain-stack {
    width: auto; align-items: flex-start;
  }
  .card-hdr-mid .card-domain-row { margin-bottom: 0.3rem; }
  /* POS chips: match domain chip sizing on desktop */
  .card-pos-hdr {
    display: inline-block; width: auto;
  }
  /* Workshop submit btn responsive now in shared partial */
}
</style>
</head>
<body>
<script>window.__AUTH = @json($authUser);</script>

@include('partials.lexicon._site-header')
<div class="search-bar-wrap" style="text-align:center;padding:0.5rem 1rem;">
  <div style="display:inline-flex;align-items:center;gap:0.4rem;width:min(420px,calc(100vw - 3rem));">
    <div style="position:relative;flex:1;">
      <input
        id="searchInput"
        type="text"
        placeholder="Search 流動…"
        style="font-family:'DM Mono',monospace;font-size:0.78rem;padding:0.4rem 2rem 0.4rem 0.8rem;border:1px solid rgba(0,0,0,0.15);border-radius:2px;width:100%;background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;"
        onblur="logSearchFinal();"
        onkeydown="if(event.key==='Enter'){triggerSearch();}"
      />
      <button id="searchClear" onclick="var i=document.getElementById('searchInput');i.value='';searchQuery='';render();i.focus();"
        style="display:none;position:absolute;right:0.4rem;top:50%;transform:translateY(-50%);border:none;background:transparent;color:var(--dim);font-size:0.85rem;cursor:pointer;padding:0 0.2rem;line-height:1;"
        title="Clear search">&times;</button>
    </div>
    <button id="searchBtn" onclick="triggerSearch();"
      style="font-family:'DM Mono',monospace;font-size:0.65rem;letter-spacing:0.06em;padding:0.4rem 0.9rem;border:1px solid rgba(0,0,0,0.2);border-radius:2px;background:var(--accent);color:white;cursor:pointer;white-space:nowrap;transition:opacity 0.15s;flex-shrink:0;"
      onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">Search</button>
  </div>
  <div style="margin-top:0.4rem;">
    <button id="analyzeBtn" style="display:none;font-family:'DM Mono',monospace;font-size:0.65rem;letter-spacing:0.06em;padding:0.3rem 0.8rem;border:1px solid var(--accent);border-radius:2px;background:transparent;color:var(--accent);cursor:pointer;transition:all 0.15s;" onmouseover="this.style.background='var(--accent)';this.style.color='white'" onmouseout="this.style.background='transparent';this.style.color='var(--accent)'" onclick="analyzeWithShifu()">Analyze with 師父</button>
  </div>
</div>

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
      <button class="scenario-card" data-scenario="beginner" onclick="applyScenarioPreset('beginner')">
        <span class="sc-icon">🌱</span>
        <span class="sc-name">The Beginner</span>
        <span class="sc-desc">Everyday spoken words, positive tone, prep-level TOCFL</span>
      </button>
      <button class="scenario-card" data-scenario="exchange" onclick="applyScenarioPreset('exchange')">
        <span class="sc-icon">🗣️</span>
        <span class="sc-name">Language Exchange</span>
        <span class="sc-desc">Colloquial, neutral, spoken — natural conversation words</span>
      </button>
      <button class="scenario-card" data-scenario="essay" onclick="applyScenarioPreset('essay')">
        <span class="sc-icon">✏️</span>
        <span class="sc-name">Essay Writing</span>
        <span class="sc-desc">Formal written register, basic TOCFL and above</span>
      </button>
      <button class="scenario-card" data-scenario="business" onclick="applyScenarioPreset('business')">
        <span class="sc-icon">💼</span>
        <span class="sc-name">Business</span>
        <span class="sc-desc">Formal, positive, spoken — confident professional register</span>
      </button>
      <button class="scenario-card" data-scenario="literature" onclick="applyScenarioPreset('literature')">
        <span class="sc-icon">📚</span>
        <span class="sc-name">Classical Reading</span>
        <span class="sc-desc">Literary register, high-intensity written words</span>
      </button>
      <button class="scenario-card" data-scenario="classicist" onclick="applyScenarioPreset('classicist')">
        <span class="sc-icon">📜</span>
        <span class="sc-name">The Classicist</span>
        <span class="sc-desc">Literary, dark-toned, advanced — for prose and poetry</span>
      </button>
      <button class="scenario-card" data-scenario="creative" onclick="applyScenarioPreset('creative')">
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
    <button class="acc-close-btn" onclick="closeAccordion()">&times; Close</button>
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
    <button class="acc-close-btn" onclick="closeAccordion()">&times; Close</button>
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
        <div class="iface-group-label">Verb Presentation</div>
        <div class="script-toggle" id="verbPresentationToggle">
          <button class="script-btn active" id="btnVerbConsolidated" onclick="setVerbPresentation('consolidated')">Consolidated</button>
          <button class="script-btn"        id="btnVerbIntricate"    onclick="setVerbPresentation('intricate')">Intricate 精細</button>
        </div>
        <div class="iface-hint">Consolidated collapses verb subtypes to transitive, intransitive, or separable. Intricate shows the full taxonomy: Vp, Vpt, Vs, Vst, Vsep…</div>
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
        <div class="iface-group-label">Symbols</div>
        <div class="script-toggle" id="iconsToggle">
          <button class="script-btn active" id="btnIconsOn"  onclick="setIconsMode('on')">On</button>
          <button class="script-btn"        id="btnIconsOff" onclick="setIconsMode('off')">Off</button>
        </div>
        <div class="iface-hint">Nature symbols on filter chips and word card attribute tags</div>
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
        <div class="iface-group-label">Pinyin Format</div>
        <div class="script-toggle" id="pinyinDisplayToggle">
          <button class="script-btn active" id="btnPinyinAccented" onclick="setPinyinDisplay('accented')">Diacritic biǎo</button>
          <button class="script-btn"        id="btnPinyinNumeric"  onclick="setPinyinDisplay('numeric')">Numeric biao3</button>
        </div>
        <div class="iface-hint">Tone marks (biǎo) or numeric tones (biao3)</div>
      </div>

      <div class="iface-group">
        <div class="iface-group-label">Workshop</div>
        <div class="script-toggle" id="workshopToggle">
          <button class="script-btn"        id="btnWorkshopExpanded"  onclick="setWorkshopDefault('expanded')">Expanded</button>
          <button class="script-btn active" id="btnWorkshopCollapsed" onclick="setWorkshopDefault('collapsed')">Collapsed</button>
        </div>
        <div class="iface-hint">Default state of Writing Conservatory 寫作院 on each card</div>
      </div>

      <div class="iface-group">
        <div class="iface-group-label">Text Orientation</div>
        <div class="script-toggle" id="textDirToggle">
          <button class="script-btn active" id="btnTextHoriz" onclick="setTextDir('horizontal')">Horizontal 橫</button>
          <button class="script-btn"        id="btnTextVert"  onclick="setTextDir('vertical')">Vertical 直</button>
        </div>
        <div class="iface-hint">Chinese text flows left-to-right (horizontal) or top-to-bottom right-to-left (vertical)</div>
      </div>

      <div class="iface-group">
        <div class="iface-group-label">POS Alignment</div>
        <div style="display:flex;flex-direction:column;gap:0.3rem">
          <label style="display:flex;align-items:center;gap:0.4rem;font-family:'DM Mono',monospace;font-size:0.72rem;color:var(--dim);cursor:pointer">
            <input type="checkbox" id="alignShowPartial" checked onchange="setAlignmentFilter()"> 🤨 Partial
          </label>
          <label style="display:flex;align-items:center;gap:0.4rem;font-family:'DM Mono',monospace;font-size:0.72rem;color:var(--dim);cursor:pointer">
            <input type="checkbox" id="alignShowDisputed" checked onchange="setAlignmentFilter()"> 😵‍💫 Disputed
          </label>
        </div>
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
    <button class="acc-close-btn" onclick="closeAccordion()">&times; Close</button>
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
        <div class="d-chip" data-group="tocfl" data-val="novice1">🌑 準備一 Novice 1</div>
        <div class="d-chip" data-group="tocfl" data-val="novice2">🌑 準備二 Novice 2</div>
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
let langMode  = localStorage.getItem('langMode') || 'en';
let iconsMode = localStorage.getItem('iconsMode') || 'on';
let uiMode   = 'en-icon'; // derived — do not set directly

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
  localStorage.setItem('langMode', mode);
  if (window.syncPref) syncPref('langMode', mode);
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
  localStorage.setItem('iconsMode', mode);
  if (window.syncPref) syncPref('iconsMode', mode);
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
    novice1:      { en: 'Novice 1',    zh: '準備一', icon: '🌑' },
    novice2:      { en: 'Novice 2',    zh: '準備二', icon: '🌑' },
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
    case 'en-zh':    return `<span>${l.en} ${l.zh}</span>`;
    case 'all':      return ico ? `${ico}<span>${l.en} ${l.zh}</span>` : `<span>${l.en} ${l.zh}</span>`;
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
    case 'en-zh':    return `<span class="row-label-text">${l.en} ${l.zh}</span>`;
    case 'all':      return `<span class="row-label-text">${l.en} ${l.zh}</span>`;
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
    case 'en-zh': case 'all': label = l.en + ' ' + l.zh; break;
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
  const zaojuLabel = langMode === 'zh' ? '寫作院' : langMode === 'both' ? 'Writing Conservatory 寫作院' : 'Writing Conservatory';
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
  panel.classList.toggle('open', attrFiltersOpen);
  if (!attrFiltersOpen) closeAllDropdowns();
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

function closeAccordion() {
  ['Scenario','Level','Interface'].forEach(n => {
    document.getElementById('accPanel' + n)?.classList.remove('open');
    document.getElementById('accTab'   + n)?.classList.remove('open');
  });
  openPanel = null;
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
  localStorage.setItem('currentLevel', level);
  if (window.syncPref) syncPref('currentLevel', level);
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
    // Persist active scenario (localStorage only — session-level, not synced to DB)
    localStorage.setItem('activeScenario', key);
  }
}

function rebuildCustomScenariosGrid() {
  const grid = document.getElementById('customScenariosGrid');
  if (!grid) return;
  grid.innerHTML = '';
  if (customScenarios.length === 0) { grid.style.display = 'none'; return; }
  grid.style.display = 'flex';
  customScenarios.forEach((s, i) => {
    const wrap = document.createElement('div');
    wrap.style.cssText = 'position:relative';
    const btn = document.createElement('button');
    btn.className = 'scenario-card';
    btn.innerHTML = `<span class="sc-icon">⭐</span><span class="sc-name">${s.name}</span><span class="sc-desc">Custom scenario</span>`;
    btn.onclick = () => {
      applyScenario(s.filters);
      // Restore saved interface settings if present
      if (s.settings) {
        if (s.settings.scriptMode)      setScript(s.settings.scriptMode);
        if (s.settings.posMode)         setPosMode(s.settings.posMode);
        if (s.settings.langMode)        setLangMode(s.settings.langMode);
        if (s.settings.iconsMode)       setIconsMode(s.settings.iconsMode);
        if (s.settings.pinyinMode)      setPinyinMode(s.settings.pinyinMode);
        if (s.settings.workshopDefault) setWorkshopDefault(s.settings.workshopDefault);
        if (s.settings.textDir)         setTextDir(s.settings.textDir);
        if (s.settings.currentLevel)    setLevel(s.settings.currentLevel);
        if (s.settings.fontScale)       applyFontScale(s.settings.fontScale);
      }
      document.querySelectorAll('.scenario-card').forEach(c => c.classList.remove('active'));
      btn.classList.add('active');
    };
    // Delete button
    const del = document.createElement('button');
    del.className = 'sc-delete';
    del.innerHTML = '&times;';
    del.title = 'Delete scenario';
    del.onclick = (e) => { e.stopPropagation(); deleteCustomScenario(i); };
    wrap.appendChild(btn);
    wrap.appendChild(del);
    grid.appendChild(wrap);
  });
}


// ── SCRIPT MODE ───────────────────────────────────────────────────────────────
let scriptMode      = localStorage.getItem('scriptMode') || 'traditional';
let pinyinMode      = localStorage.getItem('pinyinMode') || 'on';
let workshopDefault = localStorage.getItem('workshopDefault') || 'collapsed';
let textDir         = localStorage.getItem('textDir') || 'vertical';

function setWorkshopDefault(mode) {
  workshopDefault = mode;
  localStorage.setItem('workshopDefault', mode);
  if (window.syncPref) syncPref('workshopDefault', mode);
  document.getElementById('btnWorkshopExpanded').classList.toggle('active', mode === 'expanded');
  document.getElementById('btnWorkshopCollapsed').classList.toggle('active', mode === 'collapsed');
  updateTogglePill('workshopToggle');
  render();
}

function setTextDir(mode) {
  textDir = mode;
  localStorage.setItem('textDir', mode);
  if (window.syncPref) syncPref('textDir', mode);
  document.getElementById('btnTextHoriz').classList.toggle('active', mode === 'horizontal');
  document.getElementById('btnTextVert').classList.toggle('active', mode === 'vertical');
  updateTogglePill('textDirToggle');
  document.getElementById('cardContainer').classList.toggle('vertical-mode', mode === 'vertical');
  render();
}

function setPinyinMode(mode) {
  pinyinMode = mode;
  localStorage.setItem('pinyinMode', mode);
  if (window.syncPref) syncPref('pinyinMode', mode);
  document.getElementById('btnPinyinOn').classList.toggle('active', mode === 'on');
  document.getElementById('btnPinyinOff').classList.toggle('active', mode === 'off');
  updateTogglePill('pinyinToggle');
  document.getElementById('cardContainer').classList.toggle('no-pinyin', mode === 'off');
}

function setPinyinDisplay(mode) {
  pinyinDisplay = mode;
  localStorage.setItem('pinyinDisplay', mode);
  if (window.syncPref) syncPref('pinyinDisplay', mode);
  document.getElementById('btnPinyinAccented').classList.toggle('active', mode === 'accented');
  document.getElementById('btnPinyinNumeric').classList.toggle('active', mode === 'numeric');
  updateTogglePill('pinyinDisplayToggle');
  render();
}

function setScript(mode) {
  scriptMode = mode;
  localStorage.setItem('scriptMode', mode);
  if (window.syncPref) syncPref('scriptMode', mode);
  document.getElementById('btnTrad').classList.toggle('active', mode === 'traditional');
  document.getElementById('btnSimp').classList.toggle('active', mode === 'simplified');
  updateTogglePill('scriptToggle');
  render();
}

let posMode = localStorage.getItem('posMode') || 'abbr';
let verbPresentation = localStorage.getItem('verbPresentation') || 'consolidated';
let pinyinDisplay = localStorage.getItem('pinyinDisplay') || 'accented';

// POS Alignment filter — stored in localStorage, shared across SRP and IWP
let alignShowPartial = localStorage.getItem('alignShowPartial') !== 'false';
let alignShowDisputed = localStorage.getItem('alignShowDisputed') !== 'false';

function setAlignmentFilter() {
  const partialEl = document.getElementById('alignShowPartial');
  const disputedEl = document.getElementById('alignShowDisputed');
  alignShowPartial = partialEl ? partialEl.checked : true;
  alignShowDisputed = disputedEl ? disputedEl.checked : true;
  localStorage.setItem('alignShowPartial', alignShowPartial);
  localStorage.setItem('alignShowDisputed', alignShowDisputed);
  if (window.syncPref) {
    syncPref('alignShowPartial', alignShowPartial);
    syncPref('alignShowDisputed', alignShowDisputed);
  }
  // Re-render search results if any
  if (typeof runSearch === 'function' && document.getElementById('searchInput')?.value) {
    runSearch();
  }
}

// Check if a word should be shown based on alignment filter
function alignmentVisible(alignment) {
  if (!alignment || alignment === 'full') return true;
  if (alignment === 'partial') return alignShowPartial;
  if (alignment === 'disputed') return alignShowDisputed;
  return true;
}
</script>
@include('partials.lexicon._pos-data')
<script>

function setPosMode(mode) {
  posMode = mode;
  localStorage.setItem('posMode', mode);
  if (window.syncPref) syncPref('posMode', mode);
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

function setVerbPresentation(mode) {
  verbPresentation = mode;
  localStorage.setItem('verbPresentation', mode);
  if (window.syncPref) syncPref('verbPresentation', mode);
  document.getElementById('btnVerbConsolidated').classList.toggle('active', mode === 'consolidated');
  document.getElementById('btnVerbIntricate').classList.toggle('active', mode === 'intricate');
  updateTogglePill('verbPresentationToggle');
  populatePosRefine();
  render();
}

function triggerSearch() {
  const input = document.getElementById('searchInput');
  searchQuery = input ? input.value : '';
  logSearchFinal();
  render();
}

let currentLevel = localStorage.getItem('currentLevel') || 'developing';
let fontScale = 100;
</script>
@include('partials.lexicon._level-fonts')
<script>

document.getElementById('levelSelect')?.addEventListener('change', function() {
  applyLevelFonts(this.value);
});

document.getElementById('fontUp').addEventListener('click', () => {
  const idx = FONT_STEPS.indexOf(fontScale);
  if (idx < FONT_STEPS.length - 1) applyFontScale(FONT_STEPS[idx + 1]);
});
document.getElementById('fontDown').addEventListener('click', () => {
  const idx = FONT_STEPS.indexOf(fontScale);
  if (idx > 0) applyFontScale(FONT_STEPS[idx - 1]);
});

// Init at stored level (or default 'developing')
applyLevelFonts(currentLevel);
setLevel(currentLevel);

// ── Restore UI toggle states from localStorage ──────────────────────────────
(function restoreToggles() {
  // Script
  if (scriptMode !== 'traditional') {
    document.getElementById('btnTrad')?.classList.remove('active');
    document.getElementById('btnSimp')?.classList.add('active');
  }
  // POS
  if (posMode !== 'abbr') {
    document.getElementById('btnPosAbbr')?.classList.remove('active');
    document.getElementById('btnPosFull')?.classList.add('active');
  }
  // Verb complexity
  if (verbPresentation !== 'consolidated') {
    document.getElementById('btnVerbConsolidated')?.classList.remove('active');
    document.getElementById('btnVerbIntricate')?.classList.add('active');
  }
  // Language
  if (langMode !== 'en') {
    document.getElementById('btnLangEn')?.classList.remove('active');
    var lmap = { zh: 'btnLangZh', both: 'btnLangBoth' };
    document.getElementById(lmap[langMode])?.classList.add('active');
    uiMode = deriveUiMode();
    setSidebarWidth(uiMode);
  }
  // Icons
  if (iconsMode !== 'on') {
    document.getElementById('btnIconsOn')?.classList.remove('active');
    document.getElementById('btnIconsOff')?.classList.add('active');
    uiMode = deriveUiMode();
    setSidebarWidth(uiMode);
  }
  // Pinyin
  if (pinyinMode !== 'on') {
    document.getElementById('btnPinyinOn')?.classList.remove('active');
    document.getElementById('btnPinyinOff')?.classList.add('active');
    document.getElementById('cardContainer')?.classList.add('no-pinyin');
  }
  // Pinyin format
  if (pinyinDisplay !== 'accented') {
    document.getElementById('btnPinyinAccented')?.classList.remove('active');
    document.getElementById('btnPinyinNumeric')?.classList.add('active');
  }
  // POS Alignment filter
  const alignPartialEl = document.getElementById('alignShowPartial');
  const alignDisputedEl = document.getElementById('alignShowDisputed');
  if (alignPartialEl) alignPartialEl.checked = alignShowPartial;
  if (alignDisputedEl) alignDisputedEl.checked = alignShowDisputed;
  // Workshop
  if (workshopDefault !== 'collapsed') {
    document.getElementById('btnWorkshopCollapsed')?.classList.remove('active');
    document.getElementById('btnWorkshopExpanded')?.classList.add('active');
  }
  // Text direction
  if (textDir !== 'horizontal') {
    document.getElementById('btnTextHoriz')?.classList.remove('active');
    document.getElementById('btnTextVert')?.classList.add('active');
    document.getElementById('cardContainer')?.classList.add('vertical-mode');
  }
  // Update all toggle pills
  ['scriptToggle','posToggle','verbPresentationToggle','langToggle','iconsToggle','pinyinToggle','pinyinDisplayToggle','workshopToggle','textDirToggle'].forEach(function(id) {
    updateTogglePill(id);
  });
  // Note: rerenderLabels() is called later in INIT after state & DOMAIN_GROUPS are defined
})();

const PREVIEW_ICONS = {
  register:    { literary:'🦋', formal:'🐝', neutral:'🐞', colloquial:'🪲', informal:'🦗', slang:'🕷️' },
  connotation: { positive:'☀️', neutral:'⛅', negative:'⛈️', 'context-dependent':'🌦️' },
  channel:     { spoken:'🦜', both:'🐉', written:'🐍' },
  dimension:   { abstract:'🐙', concrete:'🐢', internal:'🐟', external:'🦂', fluid:'🦀' },
  intensity:   { '1':'🌸', '2':'🌼', '3':'🪷', '4':'🌻', '5':'🌺' },
  hsk:         { '1':'🌰', '2':'🌱', '3':'🌿', '4':'🌳', '5':'🌲', '6':'🎋' },
  tocfl:       { novice1:'🌑', novice2:'🌑', entry:'🌒', basic:'🌓', advanced:'🌔', high:'🌕', fluency:'🌝' },
};

let openDropdown = null;

const PREVIEW_LABELS = {
  register:    { literary:'🦋 Literary', formal:'🐝 Formal', neutral:'🐞 Standard', colloquial:'🪲 Colloquial', informal:'🦗 Informal', slang:'🕷️ Slang' },
  connotation: { positive:'☀️ Positive', neutral:'⛅ Neutral', negative:'⛈️ Negative', 'context-dependent':'🌦️ Context' },
  channel:     { 'spoken-only':'🦎 Spoken Only', 'spoken-dominant':'🐍 Spoken Dominant', fluid:'🦜 Fluid', 'written-dominant':'🦚 Written Dominant', 'written-only':'🐉 Written Only' },
  dimension:   { abstract:'🐙 Abstract', concrete:'🐢 Concrete', internal:'🐟 Internal', external:'🦂 External', fluid:'🦀 Fluid' },
  intensity:   { '1':'🌸 Faint', '2':'🌼 Mild', '3':'🪷 Moderate', '4':'🌻 Strong', '5':'🌺 Blazing' },
  tocfl:       { novice1:'🌑 Novice 1', novice2:'🌑 Novice 2', entry:'🌒 Entry', basic:'🌓 Basic', advanced:'🌔 Advanced', high:'🌕 High', fluency:'🌝 Fluency' },
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
      // Position fixed dropdown beneath the trigger row
      const rect = row.getBoundingClientRect();
      drop.style.top = rect.bottom + 'px';
      drop.style.left = rect.left + 'px';
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

// ── WORD INDEX for sentence segmentation ──
const WORD_INDEX = {};
WORDS.forEach(w => {
  if (w.traditional && !WORD_INDEX[w.traditional]) {
    WORD_INDEX[w.traditional] = {
      smartId: w.smart_id,
      trad:    w.traditional,
      simp:    w.simplified || w.traditional,
      pinyin:  w.pinyin || '',
      pos:     (w.definitions && w.definitions[0]) ? w.definitions[0].pos : '',
      def:     (w.definitions && w.definitions[0]) ? w.definitions[0].def : (w.definition || ''),
    };
  }
  if (w.simplified && w.simplified !== w.traditional && !WORD_INDEX[w.simplified]) {
    WORD_INDEX[w.simplified] = WORD_INDEX[w.traditional];
  }
});

</script>
@include('partials.lexicon._segmentation')
<script>

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

// Wire up POS refine change listener
(function() {
  const sel = document.getElementById('posRefineSelect');
  if (!sel) return;
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

// Populate POS refine select — MUST be after posFilter/relFilter/domainFilter declarations
populatePosRefine();

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
// ── Search logging: fires on blur or Enter (captures final query only) ───────
let _lastLoggedQuery = '';
function logSearchFinal() {
  const q = searchQuery.trim();
  if (!q || q === _lastLoggedQuery) return;
  _lastLoggedQuery = q;

  const isSentence = typeof isSentenceInput === 'function' && isSentenceInput(q);
  const matched = WORDS.filter(matchWord);
  const payload = { query: q, results_count: matched.length, search_type: 'word' };

  if (isSentence && typeof segmentSentence === 'function') {
    const segs = segmentSentence(q);
    const wordSegs = segs.filter(s => !isPunctuation(s.text));
    payload.search_type = 'sentence';
    payload.known_count = wordSegs.filter(s => s.known).length;
    payload.unknown_count = wordSegs.filter(s => !s.known).length;
    payload.not_found = wordSegs.filter(s => !s.known).map(s => s.text);
  }

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  fetch('/api/lexicon/search-log', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf || '' },
    body: JSON.stringify(payload),
  }).catch(() => {});
}
// Pre-fill search from URL ?q= (e.g. returning from IWP sentence breadcrumb)
(function() {
  const urlQ = new URLSearchParams(window.location.search).get('q');
  if (urlQ) {
    searchQuery = urlQ;
    requestAnimationFrame(function() {
      const input = document.getElementById('searchInput');
      if (input) input.value = urlQ;
      if (typeof render === 'function') render();
    });
  }
})();

// ── Analyze with 師父 ─────────────────────────────────────────────────────────
async function analyzeWithShifu() {
  const q = searchQuery.trim();
  if (!q) return;

  const container = document.getElementById('cardContainer');
  const analyzeBtn = document.getElementById('analyzeBtn');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  // Log the search on analyze click
  logSearchFinal();

  analyzeBtn.disabled = true;
  analyzeBtn.textContent = '分析中…';
  container.innerHTML = '<div style="text-align:center;padding:2rem;font-family:\'Cormorant Garamond\',serif;font-size:1rem;color:var(--dim);font-style:italic;">師父 is analyzing…</div>';

  try {
    const res = await fetch('/api/workshop/analyze', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf || '', 'Accept': 'application/json' },
      body: JSON.stringify({ text: q }),
    });

    const data = await res.json();

    if (data.error) {
      container.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--rose);">Analysis failed. Please try again.</div>';
      analyzeBtn.disabled = false;
      analyzeBtn.textContent = 'Analyze with 師父';
      return;
    }

    let html = '<div style="max-width:640px;margin:0 auto;padding:0.5rem;">';

    // Translation
    html += '<div style="background:var(--surface);border:1px solid var(--border);border-radius:4px;padding:1rem;margin-bottom:1rem;">';
    html += '<div style="font-family:\'DM Mono\',monospace;font-size:0.6rem;letter-spacing:0.1em;text-transform:uppercase;color:var(--accent);margin-bottom:0.4rem;">Translation</div>';
    html += '<div style="font-family:\'Cormorant Garamond\',serif;font-size:1.1rem;color:var(--ink);line-height:1.6;">' + escHtml(data.translation || '') + '</div>';
    html += '</div>';

    // Feedback
    if (data.feedback) {
      html += '<div style="background:var(--surface);border:1px solid var(--border);border-radius:4px;padding:1rem;margin-bottom:1rem;">';
      html += '<div style="font-family:\'DM Mono\',monospace;font-size:0.6rem;letter-spacing:0.1em;text-transform:uppercase;color:var(--accent);margin-bottom:0.4rem;">師父 Feedback</div>';
      html += '<div style="font-family:\'Cormorant Garamond\',serif;font-size:0.95rem;color:var(--text);line-height:1.6;">' + escHtml(data.feedback) + '</div>';
      html += '</div>';
    }

    // Word notes
    if (data.word_notes && data.word_notes.length) {
      html += '<div style="font-family:\'DM Mono\',monospace;font-size:0.6rem;letter-spacing:0.1em;text-transform:uppercase;color:var(--accent);margin-bottom:0.5rem;">Word Notes</div>';
      const isVertical = textDir === 'vertical';
      data.word_notes.forEach(function(wn) {
        const charStyle = isVertical
          ? "font-family:BiauKai,STKaiti,KaiTi,'楷體-繁','Noto Serif TC',serif;font-size:1.8rem;font-weight:400;color:var(--ink);flex-shrink:0;writing-mode:vertical-rl;line-height:1.2;padding:0.3rem 0;"
          : "font-family:BiauKai,STKaiti,KaiTi,'楷體-繁','Noto Serif TC',serif;font-size:1.8rem;font-weight:400;color:var(--ink);flex-shrink:0;";
        html += '<div style="background:var(--surface);border:1px solid var(--border);border-radius:4px;padding:0.8rem;margin-bottom:0.5rem;display:flex;gap:0.8rem;align-items:flex-start;">';
        html += '<div style="' + charStyle + '">' + escHtml(wn.word || '') + '</div>';
        html += '<div>';
        if (wn.pinyin) html += '<div style="font-family:\'Cormorant Garamond\',serif;font-size:0.85rem;color:var(--accent);font-style:italic;">' + escHtml(wn.pinyin) + '</div>';
        html += '<div style="font-family:\'Cormorant Garamond\',serif;font-size:0.9rem;color:var(--text);line-height:1.5;">' + escHtml(wn.note || '') + '</div>';
        html += '</div></div>';
      });
    }

    // Sentence cards (reuse existing segmentation)
    if (typeof segmentSentence === 'function') {
      html += '<div style="font-family:\'DM Mono\',monospace;font-size:0.6rem;letter-spacing:0.1em;text-transform:uppercase;color:var(--accent);margin:1rem 0 0.5rem;">Word Breakdown</div>';
      html += renderSentenceResults(q);
    }

    // Back button
    html += '<div style="text-align:center;margin-top:1.5rem;">';
    html += '<button onclick="render()" style="font-family:\'DM Mono\',monospace;font-size:0.65rem;padding:0.3rem 0.8rem;border:1px solid var(--border);border-radius:2px;background:var(--surface);color:var(--dim);cursor:pointer;">← Back to search</button>';
    html += '</div>';

    html += '</div>';
    container.innerHTML = html;

  } catch (e) {
    container.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--rose);">Connection error. Please try again.</div>';
  }

  analyzeBtn.disabled = false;
  analyzeBtn.textContent = 'Analyze with 師父';
}

function escHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

function wordMatchesSearch(w) {
  if (!searchQuery.trim()) return true;
  const q = searchQuery.trim().toLowerCase();
  const surfaces = [
    w.traditional, w.simplified, w.pinyin, w.pinyinToneless,
    ...(w.definitions || []).flatMap(d => [d.def, d.pos]),
  ];
  return surfaces.some(s => s && s.toLowerCase().includes(q));
}

function matchWord(w) {
  // POS Alignment filter
  if (!alignmentVisible(w.alignment)) return false;
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
  // Domain refine: matches any domain across all senses
  if (domainFilter && !(w.allDomains || []).includes(domainFilter)) return false;
  return true;
}

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

// ── _csrf kept for non-workshop usage ──
function _csrf() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.content : '';
}
// ── Workshop functions (callWorkshopAPI, showAuthPrompt, restoreWorkshopPending,
//    getCritiquePrompt, getThemePrompt, renderSavedDeck, confirmDelete, removeSaved)
//    now live in shared partial _workshop-js ──

// ── CARD ATTRIBUTE COLUMN HELPERS ─────────────────────────────────────────────
</script>
@include('partials.lexicon._attr-data')
@include('partials.lexicon._word-header-js')
@include('partials.lexicon._example-sentence-js')
@include('partials.lexicon._workshop-js')
<script>
// Legacy aliases for code referencing old function names
const savedDeck = wsSavedDeck;
function getSavedForWord(key) { return wsGetSaved(key); }
function saveToWord(key, item) { return wsSaveToWord(key, item); }
function removeFromWord(key, idx) { return wsRemoveFromWord(key, idx); }
function refreshDeckWrap(wordKey) { wsRefreshDeck(wordKey); }

// Workshop adapter: look up word data from WORDS array
window.wsGetWordData = function(wordKey) {
  return typeof WORDS !== 'undefined' ? WORDS.find(w => w.traditional === wordKey) : null;
};
window.wsResolveWordKey = function(ex) {
  if (typeof WORDS === 'undefined') return null;
  const w = WORDS.find(w => (w.senseIds || []).includes(ex.word_sense_id) || w.wordObjectId === ex.word_object_id);
  return w ? w.traditional : null;
};
</script>
<script>

// Slug→full-name reverse map for slim card POS cycling
const SLIM_POS_SLUG_TO_FULL = {};
const SLIM_POS_SLUG_TO_ZH = {};
Object.entries(POS_ABBR).forEach(([full, abbr]) => {
  SLIM_POS_SLUG_TO_FULL[abbr] = full;
  if (POS_ZH[full]) SLIM_POS_SLUG_TO_ZH[abbr] = POS_ZH[full];
});

function renderCard(w, idx) {
  return renderSlimCard(w, { delay: idx });
}

// ── Workshop toggle/interactions now in shared partial _workshop-js ──
// Legacy alias
function toggleZaoju(key) { wsTogglePanel(key); }

// toggleSecondaryChar loaded from shared partial

function handleSaveToCollection(e, wordKey) {
  e.stopPropagation();
  if (!window.__AUTH) { window.location.href = '/login'; return; }

  // If popover already open, clicking the button again dismisses it
  const existing = document.getElementById('srpCollectionPicker');
  if (existing && existing.dataset.wordKey === wordKey) {
    srpDismissCollectionPicker();
    return;
  }

  const word = WORDS.find(w => w.traditional === wordKey || w.smart_id === wordKey);
  if (!word || !word.wordObjectId) return;
  const wordObjectId = word.wordObjectId;
  const btn = e.currentTarget;  // capture before async
  const isSaved = (window.__AUTH.savedWordIds || []).includes(wordObjectId);

  if (isSaved) {
    // Already saved — show collection picker directly
    srpShowCollectionPicker(wordKey, wordObjectId, btn);
    return;
  }

  // Not saved — save first, then show picker
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  fetch('/api/saved-words/' + wordObjectId, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
  })
  .then(r => r.json())
  .then(data => {
    if (data.saved) {
      if (!window.__AUTH.savedWordIds.includes(wordObjectId)) {
        window.__AUTH.savedWordIds.push(wordObjectId);
      }
      const card = document.getElementById('card-' + wordKey);
      if (card) {
        const star = card.querySelector('.card-saved-star');
        if (!star) {
          const s = document.createElement('span');
          s.className = 'card-saved-star';
          s.title = 'Saved';
          s.innerHTML = '&#9733;';
          card.insertBefore(s, card.firstChild);
        }
      }
      if (btn) { btn.innerHTML = '&#9733;'; btn.title = 'Saved'; btn.classList.add('saved'); }
      srpShowCollectionPicker(wordKey, wordObjectId, btn);
    }
  });
}

// ── SRP COLLECTION PICKER ──────────────────────────────────────────────────
var _srpCpDismissHandler = null;

function _srpCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function _srpEscHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

function srpShowCollectionPicker(wordKey, wordObjectId, anchorBtn) {
  srpDismissCollectionPicker();
  const collections = window.__AUTH.collections || [];
  const inAnyCollection = collections.some(c => (c.wordObjectIds || []).includes(wordObjectId));
  let html = '<div class="srp-cp-title">Manage collections</div>';

  // Uncategorized option
  const uncatChecked = !inAnyCollection ? ' checked' : '';
  html += `<label class="srp-cp-item srp-cp-uncat">
    <input type="checkbox" id="srpCpUncat-${wordObjectId}"${uncatChecked} onchange="srpHandleUncategorized(this,${wordObjectId},'${wordKey}')">
    <span>Uncategorized</span></label>`;

  // Collection list
  collections.forEach(c => {
    const checked = (c.wordObjectIds || []).includes(wordObjectId) ? ' checked' : '';
    html += `<label class="srp-cp-item">
      <input type="checkbox"${checked} onchange="srpToggleCollectionWord(${c.id},${wordObjectId},this)">
      <span>${_srpEscHtml(c.name)}</span></label>`;
  });

  html += `<div class="srp-cp-new">
    <input type="text" id="srpCpNewInput-${wordObjectId}" placeholder="New collection…"
      onkeydown="if(event.key==='Enter')srpCreateCollection(${wordObjectId},'${wordKey}')">
    <button onclick="srpCreateCollection(${wordObjectId},'${wordKey}')" title="Create">+</button>
  </div>`;

  const popover = document.createElement('div');
  popover.className = 'srp-cp';
  popover.id = 'srpCollectionPicker';
  popover.dataset.wordKey = wordKey;
  popover.innerHTML = html;

  // Anchor to the card-hero-actions div, lift parent card above siblings
  const actionsDiv = anchorBtn ? anchorBtn.closest('.card-hero-actions') : null;
  if (actionsDiv) {
    actionsDiv.appendChild(popover);
    const parentCard = anchorBtn.closest('.word-card--slim');
    if (parentCard) parentCard.style.zIndex = '100';
  }

  // Click outside to dismiss
  setTimeout(() => {
    _srpCpDismissHandler = (e) => {
      if (!e.target.closest('.srp-cp') && !e.target.closest('.card-action-btn')) {
        srpDismissCollectionPicker();
      }
    };
    document.addEventListener('click', _srpCpDismissHandler);
  }, 10);
}

function srpDismissCollectionPicker() {
  const el = document.getElementById('srpCollectionPicker');
  if (el) {
    const parentCard = el.closest('.word-card--slim');
    if (parentCard) parentCard.style.zIndex = '';
    el.remove();
  }
  if (_srpCpDismissHandler) {
    document.removeEventListener('click', _srpCpDismissHandler);
    _srpCpDismissHandler = null;
  }
}

function srpToggleCollectionWord(collectionId, wordObjectId, checkbox) {
  const method = checkbox.checked ? 'POST' : 'DELETE';
  fetch('/api/collections/' + collectionId + '/words/' + wordObjectId, {
    method: method,
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _srpCsrf() },
  }).then(r => r.json()).then(data => {
    const c = (window.__AUTH.collections || []).find(c => c.id === collectionId);
    if (!c) return;
    if (checkbox.checked) {
      if (!(c.wordObjectIds || []).includes(wordObjectId)) c.wordObjectIds.push(wordObjectId);
      // Uncheck Uncategorized
      const uncat = document.getElementById('srpCpUncat-' + wordObjectId);
      if (uncat) uncat.checked = false;
    } else {
      c.wordObjectIds = (c.wordObjectIds || []).filter(id => id !== wordObjectId);
      // Check if still in any collection
      const inAny = (window.__AUTH.collections || []).some(col => (col.wordObjectIds || []).includes(wordObjectId));
      if (!inAny) {
        const uncat = document.getElementById('srpCpUncat-' + wordObjectId);
        if (uncat) uncat.checked = true;
      }
    }
  });
}

function srpHandleUncategorized(checkbox, wordObjectId, wordKey) {
  if (!checkbox.checked) {
    // Unchecking Uncategorized with no collections = unsave entirely
    const inAny = (window.__AUTH.collections || []).some(c => (c.wordObjectIds || []).includes(wordObjectId));
    if (!inAny) {
      fetch('/api/saved-words/' + wordObjectId, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _srpCsrf() },
      }).then(r => r.json()).then(() => {
        window.__AUTH.savedWordIds = (window.__AUTH.savedWordIds || []).filter(id => id !== wordObjectId);
        // Update star button
        const card = document.getElementById('card-' + wordKey);
        if (card) {
          const heroBtn = card.querySelector('.card-hero-btn.saved');
          if (heroBtn) { heroBtn.classList.remove('saved'); heroBtn.innerHTML = '&#9734;'; heroBtn.title = 'Save'; }
        }
        srpDismissCollectionPicker();
      });
    }
  }
}

function srpCreateCollection(wordObjectId, wordKey) {
  const input = document.getElementById('srpCpNewInput-' + wordObjectId);
  if (!input || !input.value.trim()) return;
  const name = input.value.trim();
  input.disabled = true;

  fetch('/api/collections', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _srpCsrf() },
    body: JSON.stringify({ name }),
  })
  .then(r => r.json())
  .then(collection => {
    return fetch('/api/collections/' + collection.id + '/words/' + wordObjectId, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _srpCsrf() },
    }).then(() => collection);
  })
  .then(collection => {
    if (!window.__AUTH.collections) window.__AUTH.collections = [];
    window.__AUTH.collections.push({ id: collection.id, name: collection.name, wordObjectIds: [wordObjectId] });
    // Find the anchor button and re-show picker
    const card = document.getElementById('card-' + wordKey);
    const btn = card ? card.querySelector('.card-action-btn.saved') : null;
    srpShowCollectionPicker(wordKey, wordObjectId, btn);
  });
}

function handleShare(e, wordKey) {
  e.stopPropagation();
  const word = WORDS.find(w => w.traditional === wordKey || w.smart_id === wordKey);
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
  const tocflLabels = { novice1:'準備一', novice2:'準備二', entry:'入門', basic:'基礎', advanced:'進階', high:'高階', fluency:'流利' };
  state.tocfl.forEach(v => items.push({ group:'tocfl', val:v, html: tocflLabels[v] || v }));
  return items.map(t => `<span class="active-filter-tag removable" data-group="${t.group}" data-val="${t.val}" title="Click to remove">${t.html}<span class="tag-remove">✕</span></span>`).join('');
}

// ── SENTENCE SEARCH: detect if input is a sentence vs a word ──
function isSentenceInput(query) {
  if (!query.trim()) return false;
  const q = query.trim();
  // If exact match in WORD_INDEX, it's a word
  if (WORD_INDEX[q]) return false;
  // If any WORDS entry matches traditional or simplified, it's a word search
  if (WORDS.some(w => w.traditional === q || w.simplified === q)) return false;
  // Segment it — if 2+ segments with at least 1 known, it's a sentence
  const segs = segmentSentence(q);
  return segs.length >= 2 && segs.some(s => s.known);
}

// Punctuation / whitespace regex — CJK + Western punctuation, spaces
const PUNCT_RE = /^[\s\u3000-\u303F\uFF00-\uFF0F\uFF1A-\uFF20\uFF3B-\uFF40\uFF5B-\uFF65\u2000-\u206F\u0021-\u002F\u003A-\u0040\u005B-\u0060\u007B-\u007E\u00A0-\u00BF]+$/;

function isPunctuation(text) { return PUNCT_RE.test(text); }

function renderSentenceResults(query) {
  const segs = segmentSentence(query.trim());
  // Filter out punctuation for counting
  const wordSegs = segs.filter(s => !isPunctuation(s.text));
  const knownCount = wordSegs.filter(s => s.known).length;
  const totalCount = wordSegs.length;
  const vertical = textDir === 'vertical';

  const headerLang = langMode === 'zh' ? `共 ${totalCount} 個詞 · ${knownCount} 個在詞典中` :
    `${totalCount} words from sentence · ${knownCount} in lexicon`;

  let html = `<div class="sentence-results-header">${headerLang}</div>`;
  html += `<div class="sentence-results">`;
  segs.forEach(seg => {
    // Skip punctuation entirely — don't render as unknown cards
    if (!seg.known && isPunctuation(seg.text)) return;
    if (seg.known) {
      // Find the full word object
      const w = WORDS.find(wd => wd.traditional === seg.text || wd.simplified === seg.text);
      if (w) {
        html += renderSentenceCard(w, seg, vertical);
      } else {
        // Known in WORD_INDEX but not in full WORDS array (shouldn't happen but safe fallback)
        html += renderSentenceKnownMini(seg, vertical);
      }
    } else {
      html += renderUnknownCard(seg.text, vertical);
    }
  });
  html += `</div>`;
  return html;
}

function sentenceNavTo(smartId, label, sentenceQuery) {
  // Store sentence context so IWP breadcrumb can link back
  sessionStorage.setItem('lexiconSentence', sentenceQuery);
  // Build abbreviated sentence label: 「first…last」
  const q = sentenceQuery.trim();
  const first = q.charAt(0);
  const last = q.charAt(q.length - 1);
  const sentenceLabel = q.length > 2 ? '\u300C' + first + '\u2026' + last + '\u300D' : '\u300C' + q + '\u300D';
  // Reset trail and push sentence as origin
  sessionStorage.setItem('lexiconTrail', JSON.stringify([
    { smartId: '__sentence__', label: sentenceLabel, sentence: sentenceQuery },
    { smartId: smartId, label: label }
  ]));
  window.location.href = '/lexicon/' + encodeURIComponent(smartId);
}

// ── Unified slim card for both sentence segmentation and search results ──
// Renders the same component with optional sentence breadcrumb navigation.
function renderSlimCard(w, opts = {}) {
  const charDisplay = scriptMode === 'simplified' ? (w.simplified || w.traditional) : w.traditional;
  const altChar = w.traditional !== w.simplified
    ? (scriptMode === 'simplified' ? w.traditional : w.simplified)
    : '';
  const vertical = textDir === 'vertical';
  const smartId = w.smart_id;

  // Click handler: sentence mode uses breadcrumb nav, search mode navigates directly
  const clickHandler = opts.sentenceQuery
    ? `sentenceNavTo('${smartId}', '${charDisplay.replace(/'/g, "\\'")}', '${opts.sentenceQuery.replace(/'/g, "\\'")}')`
    : `window.location.href='/lexicon/${encodeURIComponent(smartId)}'`;

  // POS + definition lines with 3-way cycling chips
  const defs = w.definitions || [];
  const defsHTML = defs.map(d => {
    if (!d.pos) return `<div class="sentence-card-def"><span class="sentence-card-def-text">${d.def}</span></div>`;
    const abbr = posLabel(d.pos);
    const fullDisplay = POS_RENAME[d.pos] || d.pos;
    const zh = POS_ZH[d.pos] || fullDisplay;
    const alignIcon = posAlignIcon(w.alignment);
    return `<div class="sentence-card-def"><span class="card-pos" data-abbr="${abbr}" data-full="${fullDisplay}" data-zh="${zh}" data-state="abbr" title="Tap to cycle: abbr → EN → 中文" onclick="event.stopPropagation(); cyclePosChip(event, this)">${abbr}${alignIcon ? '<span class="pos-align-icon">' + alignIcon + '</span>' : ''}</span> <span class="sentence-card-def-text">${d.def}</span></div>`;
  }).join('');

  // Level badge
  const levelBadge = w.tocfl ? `<span class="slim-level-badge slim-level-${w.tocfl}">${w.tocfl}</span>` : '';

  // Script toggle (⇌)
  const toggleBtn = altChar
    ? `<button class="slim-script-toggle" data-secondary="${altChar}" onclick="event.stopPropagation(); toggleSecondaryChar(event,this)" title="Reveal ${scriptMode === 'simplified' ? 'traditional' : 'simplified'}">⇌</button>`
    : '';

  // Star + Share buttons (only for authenticated users)
  const isSaved = window.__AUTH && (window.__AUTH.savedWordIds || []).includes(w.wordObjectId);
  const heroActions = window.__AUTH
    ? `<div class="card-hero-actions">
        <button class="card-action-btn${isSaved ? ' saved' : ''}" onclick="event.stopPropagation(); handleSaveToCollection(event, '${smartId}')" title="${isSaved ? 'Saved' : 'Save'}">&#${isSaved ? '9733' : '9734'};</button>
        <button class="card-action-btn" onclick="event.stopPropagation(); handleShare(event, '${smartId}')" title="Share">&nearr;</button>
      </div>`
    : '';

  return `
  <div class="word-card word-card--slim" id="card-${smartId}" style="${opts.delay ? 'animation-delay:' + (opts.delay * 0.02) + 's;' : ''} cursor:pointer;"
       onclick="${clickHandler}">
    <div class="slim-card-char-col">
      <div class="slim-card-char-wrap">
        <div class="slim-card-char${vertical ? ' vertical' : ''}">${charDisplay}</div>
      </div>
      ${toggleBtn}
    </div>
    <div class="slim-card-body">
      ${defsHTML}
      <div class="slim-card-pinyin"><span class="pinyin">${formatPinyin(w.pinyin)}</span> ${levelBadge}</div>
      ${heroActions}
    </div>
  </div>`;
}

function renderSentenceCard(w, seg, vertical) {
  const sentenceQ = searchQuery.trim();
  return renderSlimCard(w, { sentenceQuery: sentenceQ });
}

function renderSentenceKnownMini(seg, vertical) {
  // Word found in WORD_INDEX but not in full WORDS array — build a minimal word object
  const d = seg.data;
  const miniWord = {
    smart_id: d.smartId,
    traditional: d.trad || seg.text,
    simplified: d.simp || seg.text,
    pinyin: d.pinyin || '',
    definitions: d.pos ? [{ pos: d.pos, def: d.def || '' }] : [{ pos: '', def: d.def || '' }],
    tocfl: null,
  };
  return renderSlimCard(miniWord);
}

function renderUnknownCard(text, vertical) {
  return `
  <div class="sentence-card sentence-card--unknown">
    <div class="sentence-card-char${vertical ? ' vertical' : ''}">${text}</div>
    <div class="sentence-card-body">
      <div class="sentence-card-def sentence-card-def--dim">${langMode === 'zh' ? '尚未收錄' : 'Not yet in lexicon'}</div>
    </div>
  </div>`;
}

// Debounce render for search performance with large word sets
let _renderTimer = null;
function debouncedRender() {
  clearTimeout(_renderTimer);
  _renderTimer = setTimeout(render, 150);
}

function render() {
  rerenderLabels();
  const _clr = document.getElementById('searchClear');
  if (_clr) _clr.style.display = searchQuery ? 'block' : 'none';
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

  const analyzeBtn = document.getElementById('analyzeBtn');

  if (!hasQuery && !hasFilter) {
    container.innerHTML = '';
    countEl.textContent = '—';
    if (countQuery) countQuery.textContent = '';
    tagsEl.innerHTML = '';
    if (analyzeBtn) analyzeBtn.style.display = 'none';
    return;
  }

  // Show/hide Analyze button: visible when query looks like a sentence or multi-word
  if (analyzeBtn) {
    const showAnalyze = hasQuery && !hasFilter && searchQuery.trim().length > 1 &&
      (isSentenceInput(searchQuery) || searchQuery.trim().length >= 2);
    analyzeBtn.style.display = showAnalyze ? 'inline-block' : 'none';
  }

  // ── SENTENCE DETECTION ──
  if (hasQuery && !hasFilter && isSentenceInput(searchQuery)) {
    document.body.classList.add('sentence-mode');
    const segs = segmentSentence(searchQuery.trim());
    const knownCount = segs.filter(s => s.known).length;
    countEl.textContent = '';
    if (countQuery) countQuery.textContent = '';
    tagsEl.innerHTML = '';
    container.innerHTML = renderSentenceResults(searchQuery);
    return;
  }
  document.body.classList.remove('sentence-mode');

  // ── NORMAL WORD SEARCH ──
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
  localStorage.removeItem('activeScenario');
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
  beginner:   { register: ['colloquial'], connotation: ['positive'], channel: ['spoken'],  dimension: [], intensity: [], tocfl: ['novice1','novice2'] },
  classicist: { register: ['literary'],   connotation: ['negative'], channel: ['written'], dimension: [], intensity: ['3','4','5'], hsk: [],             tocfl: ['advanced','fluency'] },
  essay:      { register: ['formal'],     connotation: [],           channel: ['written'], dimension: [], intensity: [], tocfl: ['basic'] },
  exchange:   { register: ['colloquial'], connotation: ['neutral'],  channel: ['spoken'],  dimension: [], intensity: [],          hsk: [],               tocfl: ['novice1','novice2','entry','basic'] },
  literature: { register: ['literary'],   connotation: [],           channel: ['written'], dimension: [], intensity: ['3','4','5'], hsk: [],             tocfl: ['advanced','fluency'] },
  business:   { register: ['formal'],     connotation: ['positive'], channel: ['spoken'],  dimension: [], intensity: ['2','3','4','5'], tocfl: [] },
  creative:   { register: ['literary'],   connotation: [],           channel: ['both'],    dimension: [], intensity: [],          tocfl: [] },
};

// Custom scenarios: restore from localStorage (synced to DB via ui_preferences)
let customScenarios = JSON.parse(localStorage.getItem('customScenarios') || '[]');

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
  localStorage.removeItem('activeScenario');
  resetState(); syncUI(); render();
}

function rebuildCustomOptgroup() {
  // Legacy select-based UI — only run if elements exist
  const og = document.getElementById('customOptgroup');
  if (og) {
    og.innerHTML = '';
    customScenarios.forEach((s, i) => {
      const opt = document.createElement('option');
      opt.value = '__custom__' + i;
      opt.textContent = '⭐ ' + s.name;
      og.appendChild(opt);
    });
    og.style.display = customScenarios.length ? '' : 'none';
    const sel = document.getElementById('scenarioSelect');
    if (sel) {
      let createOpt = sel.querySelector('[value="__create__"]');
      if (createOpt) sel.appendChild(createOpt);
    }
  }
  rebuildCustomScenariosGrid();
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
  customScenarios.push({
    name,
    filters: { ...state },
    settings: {
      scriptMode, posMode, langMode, iconsMode, pinyinMode,
      workshopDefault, textDir, currentLevel, fontScale
    }
  });
  persistCustomScenarios();
  rebuildCustomOptgroup();
  // Select the newly saved scenario
  const sel = document.getElementById('scenarioSelect');
  if (sel) sel.value = '__custom__' + (customScenarios.length - 1);
  closeSaveDialog();
}

function persistCustomScenarios() {
  localStorage.setItem('customScenarios', JSON.stringify(customScenarios));
  if (window.syncPref) syncPref('customScenarios', JSON.stringify(customScenarios));
}

function deleteCustomScenario(idx) {
  customScenarios.splice(idx, 1);
  persistCustomScenarios();
  rebuildCustomOptgroup();
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
  if (event.target.closest('button, .ws-ai-workspace, .ws-panel, .seg-popover, .card-pos, .card-def-row, .card-hanzi, .card-hero-actions, .card-actions')) return;
  const word = WORDS.find(w => w.traditional === wordKey || w.smart_id === wordKey);
  // Clear exploration trail — clicking a search result starts a fresh path
  sessionStorage.removeItem('lexiconTrail');
  window.location.href = '/lexicon/' + encodeURIComponent(word?.smart_id || wordKey);
}

// ── INIT ──────────────────────────────────────────────────────────────────────
if (customScenarios.length) rebuildCustomOptgroup();
// Restore active scenario from localStorage
(function() {
  const saved = localStorage.getItem('activeScenario');
  if (saved) {
    const preset = BUILT_IN_SCENARIOS[saved];
    if (preset) {
      applyScenario(preset);
      const card = document.querySelector('.scenario-card[data-scenario="' + saved + '"]');
      if (card) card.classList.add('active');
    }
  }
})();
setSidebarWidth(uiMode);
rerenderLabels();
// Sync workshop toggle buttons with persisted preference
document.getElementById('btnWorkshopExpanded').classList.toggle('active', workshopDefault === 'expanded');
document.getElementById('btnWorkshopCollapsed').classList.toggle('active', workshopDefault === 'collapsed');
// Sync text direction toggle with persisted preference
document.getElementById('btnTextHoriz').classList.toggle('active', textDir === 'horizontal');
document.getElementById('btnTextVert').classList.toggle('active', textDir === 'vertical');
if (textDir === 'vertical') document.getElementById('cardContainer').classList.add('vertical-mode');
// Initialise all sliding pills (no transition on first paint — just snap into position)
['scriptToggle','posToggle','verbPresentationToggle','langToggle','iconsToggle','pinyinToggle','pinyinDisplayToggle','workshopToggle','textDirToggle'].forEach(updateTogglePill);
if (INITIAL_SEARCH) {
  const si = document.getElementById('searchInput');
  if (si) si.value = INITIAL_SEARCH;
  searchQuery = INITIAL_SEARCH;
}
render();

// Hydrate saved deck from DB (shared partial)
wsHydrateSavedDeck();
Object.keys(wsSavedDeck).forEach(key => wsRefreshDeck(key));

// Restore pending workshop data after login redirect
wsRestorePending();

// Re-render on bfcache restore (back button from IWP)
window.addEventListener('pageshow', function(e) {
  if (e.persisted) {
    const si = document.getElementById('searchInput');
    if (si && si.value) searchQuery = si.value;
    render();
  }
});

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

@include('partials.lexicon._popover')
@include('partials.lexicon._preference-sync')
<button id="backToTop" aria-label="Back to top">⌃</button>
@include('partials.lexicon._site-footer')
</body>
</html>
