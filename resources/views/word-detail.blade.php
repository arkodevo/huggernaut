<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $word['traditional'] }} — 流動 Living Lexicon</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@300;400;600;700&family=DM+Mono:ital,wght@0,300;0,400;1,300&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
@include('partials.lexicon._foundations')
@include('partials.lexicon._attr-chip-css')
@include('partials.lexicon._definition-css')
@include('partials.lexicon._word-header-css')
@include('partials.lexicon._example-sentence-css')
<style>
/* ── WD PREFIX STYLES ── */

/* Sticky header */
.wd-header {
  position: sticky; top: 0; z-index: 100;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid var(--border);
  padding: 0.6rem 1rem;
  display: flex; flex-direction: column; gap: 0.4rem;
}
.wd-header-top {
  display: flex; align-items: center; gap: 0.6rem;
}
.wd-back-btn {
  font-family: 'DM Mono', monospace; font-size: 0.75rem;
  color: var(--accent); background: none; border: none;
  cursor: pointer; padding: 0.2rem 0;
  transition: opacity 0.15s;
}
.wd-back-btn:hover { opacity: 0.7; }
.wd-breadcrumb {
  font-size: 0.65rem; color: var(--dim);
  letter-spacing: 0.05em;
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
  flex: 1;
}
.wd-breadcrumb a {
  color: var(--accent); text-decoration: none;
  transition: opacity 0.15s;
}
.wd-breadcrumb a:hover { opacity: 0.7; }
/* Word header card — matches SRP .word-card styling */
.wd-header-char {
  display: flex; align-items: flex-start; gap: 0.75rem;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 2px;
  padding: 1rem 1.2rem;
  margin-top: 5px;
}
.wd-header-char .hanzi-char {
  font-size: 2.4rem;
}
.wd-header-char .hanzi-secondary {
  font-size: calc(2.4rem * 0.65);
}
.wd-header-char .script-switch-btn {
  font-size: 1rem; padding: 0.25rem 0;
}
.wd-header-char .card-hdr-mid {
  flex: 1; gap: 0.25rem;
}
/* Domain: full-width, centered (matches SRP mobile) */
.wd-header-char .card-hdr-mid .card-domain-row { margin-bottom: 0; width: 100%; }
.wd-header-char .card-hdr-mid .card-domain {
  display: block; width: 100%;
  font-size: 0.81rem; padding: 0.3rem 0.6rem; text-align: center;
}
/* POS: full-width, left-justified, one per line */
.wd-header-char .card-pos-summary {
  flex-direction: column; gap: 0.2rem;
}
.wd-header-char .card-pos-hdr {
  display: block; width: 100%;
  font-size: 0.81rem; padding: 0.15rem 0.6rem; text-align: left;
}
.wd-header-char .card-pinyin-row {
  margin-top: 0.1rem;
}
.wd-header-char .pinyin {
  font-size: 1.05rem;
}

/* ── GEAR BUTTON ── */
.wd-gear-btn {
  margin-left: auto;
  background: none; border: none; cursor: pointer;
  font-size: 1.1rem; color: var(--dim);
  padding: 0.2rem 0.3rem; line-height: 1;
  transition: color 0.15s, transform 0.2s;
  flex-shrink: 0;
}
.wd-gear-btn:hover { color: var(--accent); }
.wd-gear-btn.open { color: var(--accent); transform: rotate(60deg); }

/* ── GEAR PANEL (drops down inside sticky header, above word card) ── */
.wd-gear-panel {
  max-height: 0; overflow: hidden;
  border-bottom: 0px solid var(--border);
  transition: max-height 0.28s ease, border-bottom-width 0.28s;
}
.wd-gear-panel.open {
  max-height: 900px;
  border-bottom: 1px solid var(--border);
}
.wd-gear-panel-inner {
  padding: 1.2rem 1rem 1rem;
  position: relative;
}
.wd-gear-close {
  position: absolute; bottom: 0.6rem; right: 0.8rem;
  background: none; border: 1px solid var(--border);
  border-radius: 2px; cursor: pointer;
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--dim); padding: 0.2rem 0.5rem;
  transition: color 0.15s, border-color 0.15s;
}
.wd-gear-close:hover { color: var(--accent); border-color: var(--accent); }

/* ── INTERFACE GRID (same layout as lexicon-live iface-grid) ── */
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
  font-size: 0.6rem; color: var(--dim); font-style: italic;
  max-width: 12rem; line-height: 1.3;
}

/* ── TOGGLE (shared by gear panel groups) ── */
.wd-toggle {
  display: inline-flex; border-radius: 3px; overflow: hidden;
  border: 1px solid var(--border); flex-shrink: 0;
  position: relative;
}
.wd-toggle-pill {
  position: absolute; top: 0; height: 100%;
  background: var(--accent); border-radius: 2px;
  transition: left 0.2s cubic-bezier(0.4, 0, 0.2, 1),
              width 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  pointer-events: none;
}
.wd-toggle-btn {
  padding: 0.3rem 0.55rem;
  font-family: 'DM Mono', monospace;
  font-size: 0.62rem; letter-spacing: 0.04em;
  cursor: pointer; border: none;
  background: transparent; color: var(--dim);
  transition: color 0.18s ease;
  line-height: 1; white-space: nowrap;
  position: relative; z-index: 1;
}
.wd-toggle-btn.active { color: white; }
.wd-toggle-btn:not(.active):hover { background: rgba(98,64,200,0.06); }
.wd-toggle-btn.disabled {
  opacity: 0.35; cursor: not-allowed;
  pointer-events: none;
}

/* ── SECTION VISIBILITY TOGGLES ── */
.iface-section-toggles {
  display: flex; flex-direction: column; gap: 0.45rem;
}
.iface-section-toggle {
  display: flex; align-items: center; gap: 0.5rem;
  cursor: pointer; user-select: none;
}
.iface-section-toggle input[type="checkbox"] {
  accent-color: var(--accent);
  cursor: pointer;
  width: 13px; height: 13px;
}
.iface-section-toggle-label {
  font-family: 'DM Mono', monospace;
  font-size: 0.72rem; color: var(--text);
}

/* ── MAIN CONTENT ── */
.wd-main {
  position: relative; z-index: 5;
  max-width: 640px;
  margin: 0 auto;
  padding: 1rem;
  display: flex; flex-direction: column; gap: 1.2rem;
}

/* ── CHARACTER IDENTITY ── */
.wd-identity {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 2px;
  padding: 1rem 1.2rem;
  display: flex; flex-direction: column; gap: 0.6rem;
}
.wd-identity-title {
  font-size: 0.55rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--dim);
}
.wd-char-grid {
  display: flex; flex-wrap: wrap; gap: 0.5rem;
}
.wd-char-cell {
  display: flex; flex-direction: column; align-items: center; gap: 0.15rem;
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--border);
  border-radius: 2px;
  cursor: pointer;
  transition: border-color 0.15s, background 0.15s;
  text-decoration: none;
}
.wd-char-cell:hover { border-color: var(--accent); background: rgba(98,64,200,0.04); }
.wd-char-cell-char {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 2rem; font-weight: 300; color: var(--ink); line-height: 1.1;
}
.wd-char-cell-label {
  font-size: 0.55rem; color: var(--dim); letter-spacing: 0.1em;
}
.wd-identity-row {
  display: flex; flex-wrap: wrap; gap: 0.3rem 1rem;
  font-size: 0.78rem; color: var(--dim);
}
.wd-identity-row strong { color: var(--text); font-weight: 500; }

/* ── SENSE BLOCK ── */
.wd-sense {
  background: var(--surface);
  border: 1px solid var(--border);
  border-left: 3px solid var(--accent);
  border-radius: 2px;
  padding: 1rem 1.2rem;
  display: flex; flex-direction: column; gap: 0.75rem;
}
.wd-sense-header {
  display: flex; flex-direction: column; gap: 0.4rem;
  position: relative;
}
.wd-sense-badge {
  display: inline-flex; align-items: center; justify-content: center;
  width: 1.6rem; height: 1.6rem;
  font-size: 0.72rem; font-weight: 500;
  color: white; background: var(--accent);
  border-radius: 50%;
  flex-shrink: 0;
}
.wd-save-btn {
  font-size: 1.1rem; background: none; border: none;
  cursor: pointer; color: var(--dim); padding: 0 0.2rem;
  transition: color 0.15s; flex-shrink: 0;
  line-height: 1;
}
.wd-save-btn:hover { color: var(--gold); }
.wd-save-btn.saved { color: var(--gold); }

/* Collection picker popover */
.wd-cp {
  position: absolute; top: 2rem; left: 0; z-index: 200;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 3px; min-width: 200px; padding: 0.4rem 0;
  box-shadow: 0 8px 28px rgba(0,0,0,0.12);
  animation: wdCpIn 0.15s ease;
  max-height: 50vh; overflow-y: auto;
}
@keyframes wdCpIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:none; } }
.wd-cp-title {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--dim); padding: 0.3rem 0.65rem 0.2rem;
}
.wd-cp-item {
  display: flex; align-items: center; gap: 0.4rem;
  padding: 0.3rem 0.65rem;
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  color: var(--ink); cursor: pointer;
  transition: background 0.1s;
}
.wd-cp-item:hover { background: rgba(0,0,0,0.03); }
.wd-cp-item input[type="checkbox"] { accent-color: var(--accent); margin: 0; flex-shrink: 0; }
.wd-cp-empty {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--dim); padding: 0.3rem 0.65rem; font-style: italic;
}
.wd-cp-new {
  border-top: 1px solid var(--border); margin-top: 0.25rem;
  padding: 0.4rem 0.65rem 0.2rem;
  display: flex; align-items: center; gap: 0.3rem;
}
.wd-cp-new input {
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  border: 1px solid var(--border); border-radius: 2px;
  padding: 0.25rem 0.4rem; flex: 1; outline: none;
  background: var(--bg); color: var(--ink);
}
.wd-cp-new input:focus { border-color: var(--accent); }
.wd-cp-new button {
  font-size: 0.9rem; background: none; border: none;
  color: var(--accent); cursor: pointer; padding: 0; line-height: 1;
  transition: opacity 0.15s;
}
.wd-cp-new button:hover { opacity: 0.7; }
/* Sense pinyin: uses shared .pinyin class */
/* Sense domain chip: full-width centered, matching header card */
.wd-sense-header .card-domain-row { width: 100%; }
.wd-sense-header .card-domain {
  display: block; width: 100%;
  font-size: 0.81rem; padding: 0.3rem 0.6rem; text-align: center;
}
.wd-sense-tocfl {
  display: inline-block;
  font-family: 'DM Mono', monospace;
  font-size: 0.68rem;
  color: var(--gold); background: rgba(160,114,10,0.08);
  border: 1px solid rgba(160,114,10,0.28);
  border-radius: 2px; padding: 0.1rem 0.45rem;
}

/* Definitions */
.wd-defs { display: flex; flex-direction: column; gap: 0.5rem; }
/* Definition row spacing (uses shared card-def-row, card-pos, etc. from partial) */
.card-usage-note { margin-top: 0.1rem; }

/* Attribute chips — grid container (page-specific) */
.wd-attrs {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.4rem;
  padding-top: 0.5rem;
  border-top: 1px solid var(--border);
}

/* ── EXAMPLES ── */
.wd-examples {
  display: flex; flex-direction: column; gap: 0.5rem;
  padding-top: 0.5rem;
  border-top: 1px solid var(--border);
}
.wd-examples-title {
  font-size: 0.55rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--dim);
}
/* Example sentence styles loaded from shared partial (_example-sentence-css) */

/* ── LEARNER NOTE ── */
.wd-learner-note {
  padding-top: 0.5rem;
  border-top: 1px solid var(--border);
}
.wd-learner-note-label {
  font-size: 0.55rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--dim); margin-bottom: 0.3rem;
}
.wd-learner-note-area {
  width: 100%; min-height: 48px;
  background: #fff;
  border: 1px solid rgba(98,64,200,0.18);
  color: var(--ink);
  font-family: 'DM Mono', monospace;
  font-size: 0.82rem; padding: 0.5rem 0.6rem;
  border-radius: 2px; outline: none; resize: vertical;
  line-height: 1.5;
  transition: border-color 0.2s;
}
.wd-learner-note-area::placeholder {
  font-size: 0.72rem; color: rgba(26,24,40,0.3);
}
.wd-learner-note-area:focus { border-color: var(--accent); }
.wd-learner-note-actions {
  display: flex; align-items: center; gap: 0.5rem; margin-top: 0.3rem;
}
.wd-note-save-btn {
  align-self: flex-start;
  padding: 0.3rem 0.7rem; border-radius: 2px;
  border: 1px solid var(--accent);
  background: var(--tag-bg); color: var(--accent);
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  cursor: pointer; transition: all 0.2s;
}
.wd-note-save-btn:hover { background: rgba(155,127,240,0.2); }

/* Segmented word spans */
/* Segmentation styles loaded from shared partial */

/* Vertical example text mode — handled by shared partial (.ex-sent.vertical) */

/* ── LEARNER TRAPS ── */
.wd-traps {
  background: rgba(184,48,80,0.04);
  border: 1px solid rgba(184,48,80,0.15);
  border-left: 3px solid var(--rose);
  border-radius: 2px;
  padding: 0.75rem 1rem;
}
.wd-traps-title {
  font-size: 0.6rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--rose); margin-bottom: 0.3rem;
}
.wd-traps-text {
  font-size: 0.85rem; color: var(--text); line-height: 1.6;
}

/* ── COLLOCATIONS ── */
.wd-collocations {
  display: flex; flex-wrap: wrap; gap: 0.35rem;
  padding-top: 0.5rem;
  border-top: 1px solid var(--border);
}
.wd-collocations-title {
  font-size: 0.55rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--dim); width: 100%; margin-bottom: 0.1rem;
}
.wd-colloc-chip {
  display: inline-block;
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 1.1rem; color: var(--text);
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 2px; padding: 0.2rem 0.6rem;
  cursor: pointer; text-decoration: none;
  transition: border-color 0.15s, background 0.15s;
}
.wd-colloc-chip:hover { border-color: var(--accent); background: rgba(98,64,200,0.04); color: var(--accent); }

/* ── RELATED WORDS ── */
.wd-relations {
  display: flex; flex-direction: column; gap: 0.6rem;
  padding-top: 0.5rem;
  border-top: 1px solid var(--border);
}
.wd-relation-group-title {
  font-size: 0.6rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--dim);
}
.wd-relation-cards {
  display: flex; flex-wrap: wrap; gap: 0.4rem;
}
.wd-rel-card {
  display: flex; flex-direction: column; gap: 0.1rem;
  padding: 0.5rem 0.7rem;
  background: rgba(255,255,255,0.7);
  border: 1px solid var(--border);
  border-radius: 2px;
  cursor: pointer; text-decoration: none;
  transition: border-color 0.15s, transform 0.1s;
  min-width: 80px;
}
.wd-rel-card:hover { border-color: var(--accent); transform: translateY(-1px); }
.wd-rel-card-char {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 1.4rem; font-weight: 300; color: var(--ink); line-height: 1.2;
}
.wd-rel-card-pinyin {
  font-style: italic; font-family: 'Cormorant Garamond', serif;
  font-size: 0.78rem; color: var(--accent);
}
.wd-rel-card-def {
  font-family: 'Cormorant Garamond', serif;
  font-size: 0.82rem; color: var(--dim); line-height: 1.3;
  overflow: hidden; text-overflow: ellipsis;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
}
.wd-rel-card-pos {
  font-size: 0.6rem; color: #7060a8;
  font-family: 'DM Mono', monospace;
}
.wd-rel-card-tocfl {
  font-size: 0.55rem; color: var(--gold);
}

/* ── WORKSHOP ── */
.wd-workshop {
  background: rgba(98,64,200,0.04);
  border: 1px solid rgba(98,64,200,0.1);
  border-radius: 2px;
  padding: 0.75rem 1rem;
  display: flex; flex-direction: column; gap: 0.6rem;
}
.wd-workshop-header {
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.4rem;
}
.wd-workshop-title {
  font-family: 'DM Mono', monospace;
  font-size: 0.78rem; color: var(--accent);
  letter-spacing: 0.08em;
}
.wd-workshop-tabs {
  display: flex; gap: 0.3rem; flex-wrap: wrap;
}
.wd-workshop-tab {
  padding: 0.35rem 0.65rem; border-radius: 2px;
  border: 1px solid var(--border);
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim); background: transparent; cursor: pointer;
  transition: all 0.18s;
}
.wd-workshop-tab:hover { border-color: rgba(98,64,200,0.3); color: var(--text); }
.wd-workshop-tab.active { border-color: var(--accent); background: var(--tag-bg); color: var(--accent); }
.wd-workshop-content {
  display: flex; flex-direction: column; gap: 0.5rem;
}
.wd-workshop-panel { display: none; flex-direction: column; gap: 0.5rem; }
.wd-workshop-panel.active { display: flex; }

/* My Writing textarea */
.wd-writing-area {
  background: #fff;
  border: 1px solid rgba(98,64,200,0.25);
  color: var(--ink);
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 1.1rem; padding: 0.6rem 0.75rem;
  border-radius: 2px; outline: none; resize: vertical;
  min-height: 80px; line-height: 1.6;
  transition: border-color 0.2s;
}
.wd-writing-area::placeholder { font-family: 'DM Mono', monospace; font-size: 0.62rem; color: rgba(26,24,40,0.3); }
.wd-writing-area:focus { border-color: var(--accent); }
.wd-writing-area.vertical-mode {
  writing-mode: vertical-rl;
  text-orientation: mixed;
  min-height: 200px; min-width: 100%;
  resize: horizontal;
}
.wd-save-writing-btn {
  align-self: flex-start;
  padding: 0.4rem 0.8rem; border-radius: 2px;
  border: 1px solid var(--accent);
  background: var(--tag-bg); color: var(--accent);
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  cursor: pointer; transition: all 0.2s;
}
.wd-save-writing-btn:hover { background: rgba(155,127,240,0.2); }

/* Stub panels */
.wd-stub {
  font-family: 'Cormorant Garamond', serif;
  font-size: 0.9rem; color: var(--dim);
  font-style: italic; padding: 0.6rem 0;
}

/* ── FAMILY TREE (content inside section wrapper) ── */
.wd-family-content {
  display: flex; flex-direction: column; gap: 0.6rem;
}
.wd-family-group-title {
  font-size: 0.55rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--dim);
}
.wd-family-cards {
  display: flex; flex-wrap: wrap; gap: 0.4rem;
}

/* ── ACTIONS ── */
.wd-actions {
  display: flex; gap: 0.5rem;
}
.wd-action-btn {
  flex: 1;
  display: flex; align-items: center; justify-content: center; gap: 0.3rem;
  font-family: 'DM Mono', monospace; font-size: 0.78rem;
  color: var(--dim);
  background: none; border: 1px solid var(--border); border-radius: 2px;
  padding: 0.5rem 0.7rem; cursor: pointer;
  transition: color 0.18s, border-color 0.18s;
}
.wd-action-btn:hover { color: var(--accent); border-color: var(--accent); }

/* Popover styles loaded from shared partial */

/* ── BACK TO TOP ── */
#wdBackToTop {
  position: fixed; bottom: 1.25rem; left: 50%;
  transform: translateX(-50%) translateY(8px);
  z-index: 900;
  background: none; border: none; cursor: pointer;
  color: var(--dim); font-size: 1.1rem; line-height: 1;
  padding: 0.35rem 0.75rem;
  opacity: 0; pointer-events: none;
  transition: opacity 0.3s ease, transform 0.3s ease, color 0.18s;
  font-family: 'DM Mono', monospace;
}
#wdBackToTop.visible { opacity: 0.32; pointer-events: auto; transform: translateX(-50%) translateY(0); }
@media (hover: hover) {
  #wdBackToTop:hover { opacity: 0.72; color: var(--accent); }
}

/* ── SECTION HIDDEN ── */
.wd-hidden { display: none !important; }

/* ── NO-PINYIN MODE ── */
.wd-no-pinyin .card-pinyin-row,
.wd-no-pinyin .wd-sense-pinyin,
.wd-no-pinyin .wd-rel-card-pinyin,
.wd-no-pinyin .seg-pop-pinyin { display: none; }

/* ── SECTION WRAPPERS ── */
.wd-section {
  border: 1px solid var(--border);
  border-radius: 3px;
  overflow: hidden;
}
.wd-section-toggle {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0.65rem 1rem;
  cursor: pointer; background: transparent; border: none;
  width: 100%; text-align: left;
  font-family: 'DM Mono', monospace;
  font-size: 0.72rem; letter-spacing: 0.08em;
  color: var(--dim);
  transition: color 0.15s, background 0.15s;
}
.wd-section-toggle:hover { color: var(--text); background: rgba(98,64,200,0.04); }
.wd-section-body {
  display: none;
  flex-direction: column; gap: 0.75rem;
  padding: 1rem;
}
.wd-section-body.open {
  display: flex;
}
.wd-section-arrow {
  font-size: 0.65rem; transition: transform 0.2s; display: inline-block;
}
.wd-section-stub {
  font-family: 'Cormorant Garamond', serif;
  font-size: 0.9rem; color: var(--dim);
  font-style: italic; padding: 0.4rem 0;
  line-height: 1.5;
}

/* ── RESPONSIVE ── */
@media (min-width: 768px) {
  .wd-main { padding: 1.5rem 2rem; }
  .wd-attrs { grid-template-columns: repeat(3, 1fr); }
}
</style>
</head>
<body>
<script>window.__AUTH = @json($authUser);</script>

<!-- Popover (singleton) -->
@include('partials.lexicon._popover')

<!-- Sticky Header -->
<div class="wd-header" id="wdHeader">
  <div class="wd-header-top">
    <button class="wd-back-btn" onclick="goBack()">&larr; Back</button>
    <div class="wd-breadcrumb" id="wdBreadcrumb"></div>
    @include('partials.lexicon._user-menu')
    <button class="wd-gear-btn" id="wdGearBtn" onclick="wdToggleGear()" title="Settings">&#9881;</button>
  </div>

  <!-- Gear Settings Panel (drops down between nav row and word header) -->
  <div class="wd-gear-panel" id="wdGearPanel">
    <div class="wd-gear-panel-inner">
      <div class="iface-grid">

        <div class="iface-group">
          <div class="iface-group-label">CHARACTER SET</div>
          <div class="wd-toggle" id="wdScriptToggle">
            <button class="wd-toggle-btn active" id="wdBtnTrad" onclick="wdSetScript('traditional')">Traditional 繁</button>
            <button class="wd-toggle-btn" id="wdBtnSimp" onclick="wdSetScript('simplified')">Simplified 簡</button>
          </div>
        </div>

        <div class="iface-group">
          <div class="iface-group-label">LANGUAGE</div>
          <div class="wd-toggle" id="wdLangToggle">
            <button class="wd-toggle-btn" id="wdBtnLangEn" onclick="wdSetLang('en')">EN</button>
            <button class="wd-toggle-btn" id="wdBtnLangZh" onclick="wdSetLang('zh')">中文</button>
            <button class="wd-toggle-btn active" id="wdBtnLangBoth" onclick="wdSetLang('both')">EN+中文</button>
          </div>
        </div>

        <div class="iface-group">
          <div class="iface-group-label">ICONS</div>
          <div class="wd-toggle" id="wdIconsToggle">
            <button class="wd-toggle-btn active" id="wdBtnIconsOn" onclick="wdSetIcons('on')">On</button>
            <button class="wd-toggle-btn" id="wdBtnIconsOff" onclick="wdSetIcons('off')">Off</button>
          </div>
        </div>

        <div class="iface-group">
          <div class="iface-group-label">PINYIN</div>
          <div class="wd-toggle" id="wdPinyinToggle">
            <button class="wd-toggle-btn active" id="wdBtnPinyinOn" onclick="wdSetPinyin('on')">On</button>
            <button class="wd-toggle-btn" id="wdBtnPinyinOff" onclick="wdSetPinyin('off')">Off</button>
          </div>
        </div>

        <div class="iface-group">
          <div class="iface-group-label">TEXT ORIENTATION</div>
          <div class="wd-toggle" id="wdTextDirToggle">
            <button class="wd-toggle-btn active" id="wdBtnHoriz" onclick="wdSetTextDir('horizontal')">Horizontal 橫</button>
            <button class="wd-toggle-btn" id="wdBtnVert" onclick="wdSetTextDir('vertical')">Vertical 直</button>
          </div>
        </div>

        <div class="iface-group">
          <div class="iface-group-label">SECTIONS</div>
          <div class="iface-section-toggles" id="wdSectionToggles"></div>
          <div class="iface-hint">Hide sections globally</div>
        </div>

        <div class="iface-group">
          <div class="iface-group-label">VIEW MODE</div>
          <div class="wd-toggle" id="wdViewModeToggle">
            <button class="wd-toggle-btn active" id="wdBtnScroll" onclick="wdSetViewMode('scroll')">Full Scroll</button>
            <button class="wd-toggle-btn disabled" id="wdBtnTabs" title="Coming soon">Tabs</button>
          </div>
          <div class="iface-hint">Tabs view coming soon</div>
        </div>

      </div>
      <button class="wd-gear-close" onclick="wdToggleGear()" title="Close settings">&times; Close</button>
    </div>
  </div>

  <div class="wd-header-char" id="wdHeaderChar"></div>
</div>

<!-- Main Content Container -->
<div class="wd-main" id="wdMain"></div>

<!-- Back to Top -->
<button id="wdBackToTop" onclick="window.scrollTo({top:0})">&#8963;</button>

<script>
// ── DATA ──
const WORD = @json($word);
const SMART_ID = @json($smartId);
const WORD_INDEX = @json($wordIndex);

// ── CONSTANTS (from lexicon-live) ──
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

// ── SETTINGS STATE ──
let scriptMode   = localStorage.getItem('scriptMode')   || 'traditional';
let langMode     = localStorage.getItem('langMode')     || 'both';
let iconsMode    = localStorage.getItem('iconsMode')     || 'on';
let pinyinMode   = localStorage.getItem('pinyinMode')    || 'on';
let currentLevel = localStorage.getItem('currentLevel')  || 'developing';
let fontScale    = parseInt(localStorage.getItem('fontScale')) || 100;
let textDir      = localStorage.getItem('textDir') || 'horizontal';

// Derived UI mode
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
let uiMode = deriveUiMode();
</script>
@include('partials.lexicon._pos-data')
@include('partials.lexicon._attr-data')
@include('partials.lexicon._level-fonts')
<script>

// ── SECTION VISIBILITY ──
const SECTION_VISIBILITY = {
  identity:      { minLevel: 'beginner' },
  definitions:   { minLevel: 'beginner' },
  attributes:    { minLevel: 'beginner' },
  examples:      { minLevel: 'beginner' },
  learnerTraps:  { minLevel: 'developing' },
  collocations:  { minLevel: 'learner' },
  relations:     { minLevel: 'learner' },
  familyTree:    { minLevel: 'developing' },
  workshop:      { minLevel: 'beginner' },
  communityTab:  { minLevel: 'learner' },
};
const LEVEL_ORDER = ['beginner','learner','developing','advanced','native'];

function isSectionVisible(key) {
  const rule = SECTION_VISIBILITY[key];
  if (!rule) return true;
  return LEVEL_ORDER.indexOf(currentLevel) >= LEVEL_ORDER.indexOf(rule.minLevel);
}

// ── EXPLORATION TRAIL ──
const TRAIL_KEY = 'lexiconTrail';

function getTrail() {
  try { return JSON.parse(sessionStorage.getItem(TRAIL_KEY)) || []; }
  catch { return []; }
}

function pushTrail(smartId, label) {
  const trail = getTrail();
  // Avoid duplicate at end
  if (trail.length && trail[trail.length - 1].smartId === smartId) return;
  trail.push({ smartId, label });
  // Keep max 10
  if (trail.length > 10) trail.shift();
  sessionStorage.setItem(TRAIL_KEY, JSON.stringify(trail));
}

function popTrail() {
  const trail = getTrail();
  if (trail.length < 2) return null;
  trail.pop(); // remove current
  sessionStorage.setItem(TRAIL_KEY, JSON.stringify(trail));
  return trail[trail.length - 1] || null;
}

function goBack() {
  const prev = popTrail();
  if (prev) {
    window.location.href = '/lexicon/' + prev.smartId;
  } else if (window.history.length > 1) {
    window.history.back();
  } else {
    window.location.href = '/lexicon';
  }
}

// Push current word onto trail
pushTrail(SMART_ID, WORD.traditional);

// Navigate hook: push to breadcrumb trail before navigating
window.onSegNavigate = function(smartId, trad) {
  pushTrail(smartId, trad);
};
</script>
@include('partials.lexicon._segmentation')
@include('partials.lexicon._word-header-js')
@include('partials.lexicon._example-sentence-js')
<script>

// ── GEAR PANEL ──
function wdToggleGear() {
  const panel = document.getElementById('wdGearPanel');
  const btn = document.getElementById('wdGearBtn');
  const isOpen = panel.classList.toggle('open');
  btn.classList.toggle('open', isOpen);
}

// Click outside to close gear panel
document.addEventListener('click', function(e) {
  const panel = document.getElementById('wdGearPanel');
  const btn = document.getElementById('wdGearBtn');
  if (!panel || !panel.classList.contains('open')) return;
  if (panel.contains(e.target) || btn.contains(e.target)) return;
  panel.classList.remove('open');
  btn.classList.remove('open');
});

// ── SECTION VISIBILITY (global hide/show) ──
const HIDEABLE_SECTIONS = [
  { key: 'stroke',     en: 'Stroke',       zh: '筆順' },
  { key: 'characters', en: 'Characters',   zh: '字形' },
  { key: 'words',      en: 'Words',        zh: '詞彙' },
  { key: 'familyTree', en: 'Constellation', zh: '詞族' },
  { key: 'community',  en: 'Community',    zh: '社群' },
];
let sectionVisibility = {};
HIDEABLE_SECTIONS.forEach(s => {
  sectionVisibility[s.key] = localStorage.getItem('wdSection_' + s.key) !== 'hidden';
});

function wdSetSectionVisible(key, visible) {
  sectionVisibility[key] = visible;
  localStorage.setItem('wdSection_' + key, visible ? 'visible' : 'hidden');
  if (window.syncPref) syncPref('wdSection_' + key, visible ? 'visible' : 'hidden');
  renderPage();
}

function renderSectionToggles() {
  const container = document.getElementById('wdSectionToggles');
  if (!container) return;
  container.innerHTML = HIDEABLE_SECTIONS.map(s => {
    const checked = sectionVisibility[s.key] ? 'checked' : '';
    const label = langMode === 'en' ? s.en : langMode === 'zh' ? s.zh : s.en + ' · ' + s.zh;
    return `<label class="iface-section-toggle">
      <input type="checkbox" ${checked} onchange="wdSetSectionVisible('${s.key}', this.checked)">
      <span class="iface-section-toggle-label">${label}</span>
    </label>`;
  }).join('');
}

// ── SECTION COLLAPSE STATE ──
const SECTIONS = [
  { key: 'stroke',     en: 'Stroke',          zh: '筆順' },
  { key: 'characters', en: 'Characters',      zh: '字形資訊' },
  { key: 'words',      en: 'Words',           zh: '詞彙' },
  { key: 'familyTree', en: 'Constellation', zh: '詞族' },
  { key: 'community',  en: 'Community',       zh: '社群' },
];
let sectionOpenState = {};
HIDEABLE_SECTIONS.forEach(s => {
  sectionOpenState[s.key] = localStorage.getItem('wdOpen_' + s.key) !== 'false';
});

function wdToggleSectionCollapse(key) {
  const body = document.getElementById('wdSectionBody-' + key);
  const arrow = document.getElementById('wdSectionArrow-' + key);
  if (!body) return;
  const isOpen = body.classList.toggle('open');
  sectionOpenState[key] = isOpen;
  localStorage.setItem('wdOpen_' + key, isOpen);
  if (window.syncPref) syncPref('wdOpen_' + key, String(isOpen));
  if (arrow) arrow.style.transform = isOpen ? 'rotate(180deg)' : '';
}

function renderSection(key, contentHTML) {
  if (!sectionVisibility[key]) return '';
  const sec = SECTIONS.find(s => s.key === key);
  if (!sec) return '';
  const isOpen = sectionOpenState[key] !== false;
  return `<div class="wd-section" id="wdSection-${key}">
    <button class="wd-section-toggle" onclick="wdToggleSectionCollapse('${key}')">
      <span>${langText(sec.en, sec.zh)}</span>
      <span class="wd-section-arrow" id="wdSectionArrow-${key}" style="transform:${isOpen ? 'rotate(180deg)' : ''}">&#9662;</span>
    </button>
    <div class="wd-section-body${isOpen ? ' open' : ''}" id="wdSectionBody-${key}">
      ${contentHTML}
    </div>
  </div>`;
}

// ── VIEW MODE (scaffold) ──
let viewMode = localStorage.getItem('wdViewMode') || 'scroll';

function wdSetViewMode(mode) {
  viewMode = mode;
  localStorage.setItem('wdViewMode', mode);
  if (window.syncPref) syncPref('wdViewMode', mode);
  document.getElementById('wdBtnScroll').classList.toggle('active', mode === 'scroll');
  document.getElementById('wdBtnTabs').classList.toggle('active', mode === 'tabs');
  wdUpdatePill('wdViewModeToggle');
}

// ── TOGGLE PILL ──
function wdUpdatePill(toggleId) {
  const toggle = document.getElementById(toggleId);
  if (!toggle) return;
  let pill = toggle.querySelector('.wd-toggle-pill');
  if (!pill) {
    pill = document.createElement('div');
    pill.className = 'wd-toggle-pill';
    toggle.prepend(pill);
  }
  const active = toggle.querySelector('.wd-toggle-btn.active');
  if (active) {
    pill.style.left  = active.offsetLeft + 'px';
    pill.style.width = active.offsetWidth + 'px';
  }
}

// ── SETTINGS HANDLERS ──
function wdSetScript(mode) {
  scriptMode = mode;
  localStorage.setItem('scriptMode', mode);
  if (window.syncPref) syncPref('scriptMode', mode);
  document.getElementById('wdBtnTrad').classList.toggle('active', mode === 'traditional');
  document.getElementById('wdBtnSimp').classList.toggle('active', mode === 'simplified');
  wdUpdatePill('wdScriptToggle');
  renderPage();
}

function wdSetLang(mode) {
  langMode = mode;
  localStorage.setItem('langMode', mode);
  if (window.syncPref) syncPref('langMode', mode);
  ['wdBtnLangEn','wdBtnLangZh','wdBtnLangBoth'].forEach(id => document.getElementById(id)?.classList.remove('active'));
  const map = { en: 'wdBtnLangEn', zh: 'wdBtnLangZh', both: 'wdBtnLangBoth' };
  document.getElementById(map[mode])?.classList.add('active');
  uiMode = deriveUiMode();
  wdUpdatePill('wdLangToggle');
  renderPage();
}

function wdSetIcons(mode) {
  iconsMode = mode;
  localStorage.setItem('iconsMode', mode);
  if (window.syncPref) syncPref('iconsMode', mode);
  document.getElementById('wdBtnIconsOn').classList.toggle('active', mode === 'on');
  document.getElementById('wdBtnIconsOff').classList.toggle('active', mode === 'off');
  uiMode = deriveUiMode();
  wdUpdatePill('wdIconsToggle');
  renderPage();
}

function wdSetPinyin(mode) {
  pinyinMode = mode;
  localStorage.setItem('pinyinMode', mode);
  if (window.syncPref) syncPref('pinyinMode', mode);
  document.getElementById('wdBtnPinyinOn').classList.toggle('active', mode === 'on');
  document.getElementById('wdBtnPinyinOff').classList.toggle('active', mode === 'off');
  wdUpdatePill('wdPinyinToggle');
  document.body.classList.toggle('wd-no-pinyin', mode === 'off');
}

function wdSetTextDir(mode) {
  textDir = mode;
  localStorage.setItem('textDir', mode);
  if (window.syncPref) syncPref('textDir', mode);
  document.getElementById('wdBtnHoriz').classList.toggle('active', mode === 'horizontal');
  document.getElementById('wdBtnVert').classList.toggle('active', mode === 'vertical');
  wdUpdatePill('wdTextDirToggle');
  renderPage();
}

// ── SAVE/UNSAVE SENSE ──
function _csrfHeader() {
  return document.querySelector('meta[name="csrf-token"]').content;
}

function wdToggleSave(senseId) {
  if (!window.__AUTH) return;
  fetch('/api/saved-senses/' + senseId, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfHeader() },
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    var btn = document.getElementById('wdSaveBtn-' + senseId);
    if (!btn) return;
    if (data.saved) {
      btn.classList.add('saved');
      btn.innerHTML = '&#9733;';
      btn.title = 'Unsave';
      if (!window.__AUTH.savedSenseIds.includes(senseId)) {
        window.__AUTH.savedSenseIds.push(senseId);
      }
      wdShowCollectionPicker(senseId);
    } else {
      btn.classList.remove('saved');
      btn.innerHTML = '&#9734;';
      btn.title = 'Save';
      window.__AUTH.savedSenseIds = window.__AUTH.savedSenseIds.filter(function(id) { return id !== senseId; });
      // Remove from all collections in memory
      (window.__AUTH.collections || []).forEach(function(c) {
        c.senseIds = (c.senseIds || []).filter(function(id) { return id !== senseId; });
      });
      wdDismissCollectionPicker();
    }
  });
}

// ── COLLECTION PICKER POPOVER ──
var _cpDismissHandler = null;

function wdShowCollectionPicker(senseId) {
  wdDismissCollectionPicker();
  var collections = window.__AUTH.collections || [];
  var html = '<div class="wd-cp-title">Add to collection</div>';

  if (collections.length === 0) {
    html += '<div class="wd-cp-empty">No collections yet</div>';
  } else {
    collections.forEach(function(c) {
      var checked = (c.senseIds || []).includes(senseId) ? ' checked' : '';
      html += '<label class="wd-cp-item">'
        + '<input type="checkbox"' + checked + ' onchange="wdToggleCollectionSense(' + c.id + ',' + senseId + ',this)">'
        + '<span>' + escHtml(c.name) + '</span></label>';
    });
  }

  html += '<div class="wd-cp-new">'
    + '<input type="text" id="wdCpNewInput-' + senseId + '" placeholder="New collection…" '
    + 'onkeydown="if(event.key===\'Enter\')wdCreateCollection(' + senseId + ')">'
    + '<button onclick="wdCreateCollection(' + senseId + ')" title="Create">+</button>'
    + '</div>';

  var popover = document.createElement('div');
  popover.className = 'wd-cp';
  popover.id = 'wdCollectionPicker';
  popover.innerHTML = html;

  var header = document.getElementById('wdSaveBtn-' + senseId);
  if (header && header.parentElement) {
    header.parentElement.appendChild(popover);
  }

  // Click outside to dismiss
  setTimeout(function() {
    _cpDismissHandler = function(e) {
      if (!e.target.closest('.wd-cp') && !e.target.closest('.wd-save-btn')) {
        wdDismissCollectionPicker();
      }
    };
    document.addEventListener('click', _cpDismissHandler);
  }, 10);
}

function wdDismissCollectionPicker() {
  var el = document.getElementById('wdCollectionPicker');
  if (el) el.remove();
  if (_cpDismissHandler) {
    document.removeEventListener('click', _cpDismissHandler);
    _cpDismissHandler = null;
  }
}

function wdToggleCollectionSense(collectionId, senseId, checkbox) {
  var method = checkbox.checked ? 'POST' : 'DELETE';
  fetch('/api/collections/' + collectionId + '/senses/' + senseId, {
    method: method,
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfHeader() },
  }).then(function() {
    // Update __AUTH.collections in memory
    var c = (window.__AUTH.collections || []).find(function(c) { return c.id === collectionId; });
    if (!c) return;
    if (checkbox.checked) {
      if (!(c.senseIds || []).includes(senseId)) c.senseIds.push(senseId);
    } else {
      c.senseIds = (c.senseIds || []).filter(function(id) { return id !== senseId; });
    }
  });
}

function wdCreateCollection(senseId) {
  var input = document.getElementById('wdCpNewInput-' + senseId);
  if (!input || !input.value.trim()) return;
  var name = input.value.trim();
  input.disabled = true;

  fetch('/api/collections', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfHeader() },
    body: JSON.stringify({ name: name }),
  })
  .then(function(r) { return r.json(); })
  .then(function(collection) {
    // Add sense to the new collection
    return fetch('/api/collections/' + collection.id + '/senses/' + senseId, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfHeader() },
    }).then(function() { return collection; });
  })
  .then(function(collection) {
    // Update __AUTH
    if (!window.__AUTH.collections) window.__AUTH.collections = [];
    window.__AUTH.collections.push({
      id: collection.id,
      name: collection.name,
      senseIds: [senseId],
    });
    // Re-show picker with updated data
    wdShowCollectionPicker(senseId);
  });
}

function escHtml(str) {
  var div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

// ── LEARNER NOTE ──
var _noteSyncTimer = {};
function wdSaveNote(senseId) {
  const ta = document.getElementById('wdNote-' + senseId);
  const noteVal = ta?.value || '';
  localStorage.setItem('wd_note_' + SMART_ID + '_' + senseId, noteVal);
  // Debounced DB sync for logged-in users
  if (window.__AUTH) {
    clearTimeout(_noteSyncTimer[senseId]);
    _noteSyncTimer[senseId] = setTimeout(function() {
      var csrfToken = document.querySelector('meta[name="csrf-token"]');
      fetch('/api/saved-senses/' + senseId + '/note', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken.content },
        body: JSON.stringify({ note: noteVal }),
      });
    }, 500);
  }
}

function wdSaveNoteBtn(senseId) {
  wdSaveNote(senseId);
  const btn = document.getElementById('wdNoteSaveBtn-' + senseId);
  if (btn) {
    const orig = btn.textContent;
    btn.textContent = langMode === 'zh' ? '已儲存!' : 'Saved!';
    setTimeout(() => btn.textContent = orig, 1500);
  }
}

// ── WORKSHOP STATE ──
const workshopWriting = {};

function wdSaveWriting(senseId) {
  const ta = document.getElementById('wdWriting-' + senseId);
  if (!ta) return;
  const key = 'wd_writing_' + SMART_ID + '_' + senseId;
  localStorage.setItem(key, ta.value);
  const btn = document.getElementById('wdSaveBtn-' + senseId);
  if (btn) {
    const orig = btn.textContent;
    btn.textContent = 'Saved!';
    setTimeout(() => btn.textContent = orig, 1500);
  }
}

function wdLoadWriting(senseId) {
  const key = 'wd_writing_' + SMART_ID + '_' + senseId;
  return localStorage.getItem(key) || '';
}

function wdSwitchWorkshopTab(senseId, tabName, btn) {
  const panels = document.querySelectorAll(`[data-workshop-sense="${senseId}"] .wd-workshop-panel`);
  panels.forEach(p => p.classList.remove('active'));
  const target = document.getElementById(`wdWsPanel-${tabName}-${senseId}`);
  if (target) target.classList.add('active');
  btn.closest('.wd-workshop-tabs').querySelectorAll('.wd-workshop-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
}

// ── SHARE / SAVE ──
function wdShare() {
  const text = `${WORD.traditional} — 流動 Living Lexicon`;
  if (navigator.share) {
    navigator.share({ title: '流動 Living Lexicon', text, url: window.location.href });
  } else {
    navigator.clipboard.writeText(window.location.href).then(() => {
      const btn = document.getElementById('wdShareBtn');
      if (btn) {
        const orig = btn.innerHTML;
        btn.innerHTML = 'Copied!';
        setTimeout(() => btn.innerHTML = orig, 1500);
      }
    });
  }
}

function wdSaveToCollection() {
  alert('Collections coming soon \u2014 sign up to save words!');
}

// ── RENDER HELPERS ──
function langText(en, zh) {
  if (langMode === 'en') return en;
  if (langMode === 'zh') return zh;
  return en + ' \u00b7 ' + zh;
}

function charDisplay() {
  return scriptMode === 'simplified' ? (WORD.simplified || WORD.traditional) : WORD.traditional;
}

function primaryPinyin() {
  const p = (WORD.pronunciations || []).find(p => p.isPrimary);
  return p ? p.text : '';
}

// ── Shared domain chip builder: two-tier layout ────────────────────────
// Primary on top, chevron + secondaries beneath
function buildDomainChipHTML(primaryObj, secondariesArr) {
  if (!primaryObj) return '';
  const pEn = primaryObj.en || primaryObj.slug;
  const pZh = primaryObj.zh || '';
  const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
  const pDisplay = preferred === 'zh' ? pZh : (uiMode === 'all' || uiMode === 'en-zh') ? `${pEn} ${pZh}` : pEn;
  const secs = secondariesArr || [];
  if (!secs.length) {
    return `<span class="card-domain" data-en="${pEn}" data-zh="${pZh}" data-state="${preferred}" onclick="toggleLangChip(event,this)">${pDisplay}</span>`;
  }
  const secEn = secs.map(sd => sd.en || sd.slug).join(', ');
  const secZh = secs.map(sd => sd.zh || sd.slug).join(', ');
  const sDisplay = preferred === 'zh' ? secZh : (uiMode === 'all' || uiMode === 'en-zh') ? `${secEn} ${secZh}` : secEn;
  return `<div class="card-domain-stack" data-p-en="${pEn}" data-p-zh="${pZh}" data-s-en="${secEn}" data-s-zh="${secZh}" data-state="${preferred}" onclick="toggleLangChip(event,this)">
    <span class="card-domain-primary">${pDisplay}</span>
    <span class="card-domain-chevron">⌄</span>
    <span class="card-domain-secondary">${sDisplay}</span>
  </div>`;
}

// ── RENDER: HEADER ──
function renderHeader() {
  // Breadcrumb
  const trail = getTrail();
  const bc = document.getElementById('wdBreadcrumb');
  if (trail.length > 1) {
    bc.innerHTML = trail.map((t, i) => {
      if (i === trail.length - 1) return `<strong>${t.label}</strong>`;
      return `<a href="/lexicon/${t.smartId}" onclick="event.preventDefault(); pushTrail('${t.smartId}','${t.label}'); window.location.href='/lexicon/${t.smartId}'">${t.label}</a>`;
    }).join(' <span style="opacity:0.4">\u203a</span> ');
  } else {
    bc.innerHTML = '';
  }

  // Build header using shared card-hanzi + card-hdr-mid pattern
  const primaryChar = charDisplay();
  const simpCharVal = scriptMode === 'simplified'
    ? (WORD.traditional !== WORD.simplified ? WORD.traditional : '')
    : (WORD.simplified && WORD.simplified !== WORD.traditional ? WORD.simplified : '');

  // Collect domain chips for header: group by primary, union secondaries
  const senses = WORD.senses || [];
  const domainGroups = {};
  senses.forEach(s => {
    if (!s.domain) return;
    const pSlug = s.domain.slug;
    if (!domainGroups[pSlug]) domainGroups[pSlug] = { primary: s.domain, secMap: {} };
    (s.secondaryDomains || []).forEach(sd => {
      if (!domainGroups[pSlug].secMap[sd.slug]) domainGroups[pSlug].secMap[sd.slug] = sd;
    });
  });
  const domainHTML = Object.values(domainGroups).map(g =>
    buildDomainChipHTML(g.primary, Object.values(g.secMap))
  ).join('');

  // Collect unique POS across all definitions
  const allPOS = [];
  const seenPOS = {};
  senses.forEach(s => {
    (s.definitions || []).forEach(d => {
      if (d.pos && !seenPOS[d.pos]) {
        seenPOS[d.pos] = true;
        allPOS.push(d.pos);
      }
    });
  });
  const posHTML = allPOS.length ? `<div class="card-pos-summary">${allPOS.map(p => {
    const enText = posDisplay(p) + ' \u00b7 ' + (POS_ABBR[p] || p);
    const zhText = POS_ZH[p] || posDisplay(p);
    const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
    const display = preferred === 'zh' ? zhText : (uiMode === 'all' || uiMode === 'en-zh') ? enText + ' ' + zhText : enText;
    return `<span class="card-pos-hdr" data-en="${enText}" data-zh="${zhText}" data-state="${preferred}" onclick="toggleLangChip(event,this)">${display}</span>`;
  }).join('')}</div>` : '';

  const pinyin = primaryPinyin();

  document.getElementById('wdHeaderChar').innerHTML = `
    <div class="card-hanzi">
      <div class="hanzi-primary-wrap">
        <span class="hanzi-char">${primaryChar}</span>
        ${simpCharVal ? `<button class="script-switch-btn" data-secondary="${simpCharVal}" onclick="toggleSecondaryChar(event,this)" title="Reveal ${scriptMode === 'simplified' ? 'traditional' : 'simplified'}">⇌</button>` : ''}
      </div>
    </div>
    <div class="card-hdr-mid">
      ${domainHTML ? `<div class="card-domain-row">${domainHTML}</div>` : ''}
      ${posHTML}
      ${pinyin ? `<div class="card-pinyin-row"><span class="pinyin pinyin-h">${pinyin}</span></div>` : ''}
    </div>`;
}

// ── RENDER: IDENTITY SECTION ──
function renderIdentity() {
  if (!isSectionVisible('identity')) return '';
  const w = WORD;
  const chars = w.characters || [];
  const isMultiChar = chars.length > 1;

  let charGrid = '';
  if (isMultiChar) {
    charGrid = `<div class="wd-char-grid">${chars.map(c => {
      const display = scriptMode === 'simplified' ? (c.simp || c.char) : c.char;
      return `<a class="wd-char-cell" href="/lexicon/${c.smartId}" onclick="pushTrail('${c.smartId}','${display}')">
        <span class="wd-char-cell-char">${display}</span>
        <span class="wd-char-cell-label">${c.smartId}</span>
      </a>`;
    }).join('')}</div>`;
  }

  const rows = [];
  if (w.radical) {
    const rMeaning = langMode === 'en' ? w.radical.meaning : langMode === 'zh' ? w.radical.meaningZh : `${w.radical.meaning} \u00b7 ${w.radical.meaningZh}`;
    rows.push(`<span>${langText('Radical', '部首')}: <strong>${w.radical.character}</strong> ${rMeaning} (${w.radical.strokeCount} ${langText('strokes', '筆畫')})</span>`);
  }
  if (w.strokesTrad) rows.push(`<span>${langText('Strokes (trad.)', '筆畫（繁）')}: <strong>${w.strokesTrad}</strong></span>`);
  if (w.strokesSimp && w.strokesSimp !== w.strokesTrad) rows.push(`<span>${langText('Strokes (simp.)', '筆畫（簡）')}: <strong>${w.strokesSimp}</strong></span>`);
  if (w.structure) rows.push(`<span>${langText('Structure', '結構')}: <strong>${w.structure}</strong></span>`);

  if (!charGrid && !rows.length) return '';

  return `<div class="wd-identity">
    <div class="wd-identity-title">${langText('Character Identity', '字形資訊')}</div>
    ${charGrid}
    ${rows.length ? `<div class="wd-identity-row">${rows.join('')}</div>` : ''}
  </div>`;
}

// ── RENDER: SENSE ──
function renderSense(sense, idx) {
  const parts = [];

  // Sense header: badge + save button, then domain chip (full-width, matching header), then pinyin
  const isSaved = window.__AUTH && (window.__AUTH.savedSenseIds || []).includes(sense.id);
  const saveBtn = window.__AUTH
    ? `<button class="wd-save-btn${isSaved ? ' saved' : ''}" id="wdSaveBtn-${sense.id}" onclick="wdToggleSave(${sense.id})" title="${isSaved ? 'Unsave' : 'Save'}">${isSaved ? '&#9733;' : '&#9734;'}</button>`
    : '';
  let senseHdrHTML = `<span class="wd-sense-badge">${idx + 1}</span>${saveBtn}`;

  // Domain chip: two-tier (primary + chevron + secondaries)
  if (sense.domain) {
    senseHdrHTML += `<div class="card-domain-row">${buildDomainChipHTML(sense.domain, sense.secondaryDomains)}</div>`;
  }

  if (sense.pinyin) senseHdrHTML += `<div class="card-pinyin-row"><span class="pinyin pinyin-h">${sense.pinyin}</span></div>`;

  parts.push(`<div class="wd-sense-header">${senseHdrHTML}</div>`);

  // Definitions
  if (isSectionVisible('definitions') && sense.definitions && sense.definitions.length) {
    const defs = sense.definitions.map(d => {
      const fml = d.formula || '';
      const fmlDisplay = scriptMode === 'simplified' && WORD.traditional !== WORD.simplified ? fml.replace(new RegExp(WORD.traditional.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), WORD.simplified) : fml;
      return `<div class="card-def-row">
        ${d.pos ? `<span class="card-pos" data-abbr="${POS_ABBR[d.pos] || d.pos}" data-full="${posDisplay(d.pos)}" data-zh="${POS_ZH[d.pos] || posDisplay(d.pos)}" data-state="abbr" onclick="cyclePosChip(event, this)">${posLabel(d.pos)}</span>` : ''}
        <span class="card-definition">${d.def}</span>
      </div>
      ${fmlDisplay ? `<div class="card-formula">${fmlDisplay}</div>` : ''}
      ${d.usageNote ? `<div class="card-usage-note">${d.usageNote}</div>` : ''}`;
    }).join('');
    parts.push(`<div class="wd-defs">${defs}</div>`);
  }

  // Attribute chips
  if (isSectionVisible('attributes')) {
    const chips = [];
    if (sense.register) chips.push(cardAttr('register', sense.register, 'Register', metaAttrLabel('register', sense.register)));
    if (sense.connotation) chips.push(cardAttr('connotation', sense.connotation, 'Connotation', metaAttrLabel('connotation', sense.connotation), connoClass[sense.connotation]));
    if (sense.channel) chips.push(cardAttr('channel', sense.channel, 'Channel', metaAttrLabel('channel', sense.channel)));
    if ((sense.dimensions || []).length) chips.push(cardAttrMulti('dimension', sense.dimensions, 'Dimension'));
    if (sense.intensity) chips.push(cardAttr('intensity', sense.intensity, 'Intensity', metaAttrLabel('intensity', sense.intensity)));
    if (sense.tocfl) chips.push(cardAttr('tocfl', sense.tocfl, 'TOCFL', metaAttrLabel('tocfl', sense.tocfl)));
    if (chips.length) {
      parts.push(`<div class="wd-attrs">${chips.join('')}</div>`);
    }
  }

  // Examples — uses shared renderExSentence()
  if (isSectionVisible('examples') && sense.examples && sense.examples.length) {
    const sensePosForEx = (sense.definitions || [])[0]?.pos || '';
    const sensePosAbbrForEx = sensePosForEx ? (POS_ABBR[sensePosForEx] || sensePosForEx) : '';
    const exHTML = sense.examples.filter(ex => !ex.isSuppressed).map((ex, i) => {
      return renderExSentence(ex, {
        pos: sensePosAbbrForEx,
        vertical: textDir === 'vertical',
        segFn: segmentedHTML,
      });
    }).join('');
    parts.push(`<div class="wd-examples">
      <div class="wd-examples-title">${langText('Examples', '例句')}</div>
      <div class="ex-sentences">${exHTML}</div>
    </div>`);
  }

  // Learner Traps
  if (isSectionVisible('learnerTraps') && sense.learnerTraps) {
    parts.push(`<div class="wd-traps">
      <div class="wd-traps-title">${langText('Learner Traps', '學習陷阱')}</div>
      <div class="wd-traps-text">${sense.learnerTraps}</div>
    </div>`);
  }

  // Collocations
  if (isSectionVisible('collocations') && sense.collocations && sense.collocations.length) {
    const chips = sense.collocations.map(c => {
      return `<a class="wd-colloc-chip" href="/lexicon/${c.smartId}" onclick="pushTrail('${c.smartId}','${c.traditional}')">${c.traditional}</a>`;
    }).join('');
    parts.push(`<div class="wd-collocations">
      <div class="wd-collocations-title">${langText('Collocations', '搭配')}</div>
      ${chips}
    </div>`);
  }

  // Relations moved to Words section (renderWordsSection)

  // Workshop
  if (isSectionVisible('workshop')) {
    parts.push(renderWorkshop(sense, idx));
  }

  return `<div class="wd-sense">${parts.join('')}</div>`;
}

// ── RENDER: RELATION CARD ──
function renderRelCard(r) {
  const tocflLabel = r.tocfl && LABELS.tocfl[r.tocfl] ? (iconsMode === 'on' ? LABELS.tocfl[r.tocfl].icon : LABELS.tocfl[r.tocfl].en) : '';
  return `<a class="wd-rel-card" href="/lexicon/${r.smartId}" onclick="pushTrail('${r.smartId}','${r.traditional}')">
    <span class="wd-rel-card-char">${r.traditional}</span>
    <span class="wd-rel-card-pinyin">${r.pinyin || ''}</span>
    ${r.posAbbr ? `<span class="wd-rel-card-pos">${r.posAbbr}</span>` : ''}
    <span class="wd-rel-card-def">${r.def || ''}</span>
    ${tocflLabel ? `<span class="wd-rel-card-tocfl">${tocflLabel}</span>` : ''}
  </a>`;
}

// ── RENDER: WORKSHOP ──
function renderWorkshop(sense, idx) {
  const senseId = sense.id || idx;
  const title = langText('Writing Workshop', '寫作工坊');
  const savedText = wdLoadWriting(senseId);

  // Primary POS for this sense's examples
  const sensePOS = (sense.definitions || [])[0]?.pos || '';
  const sensePosAbbr = sensePOS ? (POS_ABBR[sensePOS] || sensePOS) : '';

  // Default examples tab — uses shared renderExSentence()
  const defaultExHTML = (sense.examples || []).filter(ex => !ex.isSuppressed).map((ex, i) => {
    return renderExSentence(ex, {
      pos: sensePosAbbr,
      vertical: textDir === 'vertical',
      segFn: segmentedHTML,
    });
  }).join('') || `<div class="wd-stub">${langText('No examples yet.', '尚無例句。')}</div>`;

  // Tabs visibility
  const showCommunity = isSectionVisible('communityTab');

  return `<div class="wd-workshop" data-workshop-sense="${senseId}">
    <div class="wd-workshop-header">
      <div class="wd-workshop-title">${title}</div>
    </div>
    <div class="wd-workshop-tabs">
      <button class="wd-workshop-tab active" onclick="wdSwitchWorkshopTab('${senseId}','examples',this)">${langText('Default Examples', '預設例句')}</button>
      <button class="wd-workshop-tab" onclick="wdSwitchWorkshopTab('${senseId}','writing',this)">${langText('My Writing', '我的寫作')}</button>
      ${showCommunity ? `<button class="wd-workshop-tab" onclick="wdSwitchWorkshopTab('${senseId}','community',this)">${langText('Community', '社群')}</button>` : ''}
      <button class="wd-workshop-tab" onclick="wdSwitchWorkshopTab('${senseId}','ai',this)">${langText('AI Feedback', 'AI 回饋')}</button>
    </div>
    <div class="wd-workshop-content">
      <div class="wd-workshop-panel active" id="wdWsPanel-examples-${senseId}">
        <div class="ex-sentences">${defaultExHTML}</div>
      </div>
      <div class="wd-workshop-panel" id="wdWsPanel-writing-${senseId}">
        <textarea class="wd-writing-area ${textDir === 'vertical' ? 'vertical-mode' : ''}" id="wdWriting-${senseId}" placeholder="${langMode === 'zh' ? '在這裡寫你的句子…' : 'Write your sentence here...'}">${savedText}</textarea>
        <button class="wd-save-writing-btn" id="wdSaveBtn-${senseId}" onclick="wdSaveWriting('${senseId}')">${langText('Save', '儲存')}</button>
      </div>
      ${showCommunity ? `<div class="wd-workshop-panel" id="wdWsPanel-community-${senseId}">
        <div class="wd-stub">${langText('Community contributions coming soon', '社群內容即將推出')}</div>
      </div>` : ''}
      <div class="wd-workshop-panel" id="wdWsPanel-ai-${senseId}">
        <div class="wd-stub">${langText('AI writing feedback coming soon', 'AI 寫作回饋即將推出')}</div>
      </div>
    </div>
  </div>`;
}

// ── RENDER: FAMILY TREE (inner content) ──
function renderFamilyTreeContent() {
  const family = WORD.family;
  if (!family) return '';
  const { derivatives, familyMembers, compounds } = family;
  const hasContent = (derivatives && derivatives.length) || (familyMembers && familyMembers.length) || (compounds && compounds.length);
  if (!hasContent) return '';

  const groups = [];
  if (compounds && compounds.length) {
    groups.push(`<div>
      <div class="wd-family-group-title">${langText('Compounds', '複合詞')}</div>
      <div class="wd-family-cards">${compounds.map(r => renderRelCard(r)).join('')}</div>
    </div>`);
  }
  if (derivatives && derivatives.length) {
    groups.push(`<div>
      <div class="wd-family-group-title">${langText('Derivatives', '衍生詞')}</div>
      <div class="wd-family-cards">${derivatives.map(r => renderRelCard(r)).join('')}</div>
    </div>`);
  }
  if (familyMembers && familyMembers.length) {
    groups.push(`<div>
      <div class="wd-family-group-title">${langText('Family Members', '詞族成員')}</div>
      <div class="wd-family-cards">${familyMembers.map(r => renderRelCard(r)).join('')}</div>
    </div>`);
  }

  return `<div class="wd-family-content">${groups.join('')}</div>`;
}

// ── RENDER: ACTIONS ──
function renderActions() {
  return `<div class="wd-actions">
    <button class="wd-action-btn" onclick="wdSaveToCollection()">+ ${langText('Save to Collection', '加入收藏')}</button>
    <button class="wd-action-btn" id="wdShareBtn" onclick="wdShare()">&nearr; ${langText('Share', '分享')}</button>
  </div>`;
}

// ── RENDER: WORDS SECTION (relations + beginning/containing) ──
function renderWordsSection() {
  const parts = [];

  // Aggregate relations across all senses
  const allRelations = { synonymClose: [], synonymRelated: [], antonym: [], contrast: [], registerVariant: [] };
  const seen = {};
  (WORD.senses || []).forEach(sense => {
    if (!sense.relations) return;
    Object.keys(allRelations).forEach(key => {
      const items = sense.relations[key];
      if (!items) return;
      items.forEach(r => {
        if (!seen[key + '_' + r.smartId]) {
          seen[key + '_' + r.smartId] = true;
          allRelations[key].push(r);
        }
      });
    });
  });

  // Sub-sections for relation groups
  const groups = [
    { key: 'synonymClose',    en: 'Close Synonyms',    zh: '近義詞',   items: allRelations.synonymClose },
    { key: 'synonymRelated',  en: 'Related Synonyms',  zh: '相關近義', items: allRelations.synonymRelated },
    { key: 'antonym',         en: 'Antonyms',          zh: '反義詞',   items: allRelations.antonym },
    { key: 'contrast',        en: 'Contrasts',         zh: '對比',     items: allRelations.contrast },
    { key: 'registerVariant', en: 'Register Variants', zh: '語域變體', items: allRelations.registerVariant },
  ];

  groups.forEach(g => {
    if (!g.items.length) return;
    parts.push(`<div>
      <div class="wd-relation-group-title">${langText(g.en, g.zh)}</div>
      <div class="wd-relation-cards">${g.items.map(r => renderRelCard(r)).join('')}</div>
    </div>`);
  });

  // Placeholders for Words Beginning / Words Containing (filled by loadRelatedWords)
  parts.push(`<div id="wdWordsBeginning"></div>`);
  parts.push(`<div id="wdWordsContaining"></div>`);

  return parts.join('') || `<div class="wd-section-stub">${langText('No related words found', '未找到相關詞彙')}</div>`;
}

// ── FETCH & RENDER: Words Beginning / Containing ──
let relatedWordsCache = null;

async function loadRelatedWords() {
  if (relatedWordsCache) {
    renderRelatedWords(relatedWordsCache);
    return;
  }
  const chars = [...new Set((WORD.traditional || '').split(''))];
  const results = { beginning: [], containing: [] };
  const seenB = {}, seenC = {};

  for (const c of chars) {
    try {
      const resp = await fetch('/api/lexicon/related-words/' + encodeURIComponent(c));
      const data = await resp.json();
      (data.beginning || []).forEach(w => {
        if (!seenB[w.smartId]) { seenB[w.smartId] = true; results.beginning.push(w); }
      });
      (data.containing || []).forEach(w => {
        if (!seenC[w.smartId]) { seenC[w.smartId] = true; results.containing.push(w); }
      });
    } catch (e) { /* silently skip failed fetches */ }
  }
  relatedWordsCache = results;
  renderRelatedWords(results);
}

function renderRelatedWords(results) {
  const bEl = document.getElementById('wdWordsBeginning');
  const cEl = document.getElementById('wdWordsContaining');
  if (bEl && results.beginning.length) {
    bEl.innerHTML = `<div>
      <div class="wd-relation-group-title">${langText('Words Beginning With', '以此字開頭')}</div>
      <div class="wd-relation-cards">${results.beginning.map(r => renderRelCard(r)).join('')}</div>
    </div>`;
  }
  if (cEl && results.containing.length) {
    cEl.innerHTML = `<div>
      <div class="wd-relation-group-title">${langText('Words Containing', '包含此字')}</div>
      <div class="wd-relation-cards">${results.containing.map(r => renderRelCard(r)).join('')}</div>
    </div>`;
  }
}

// ── MAIN RENDER ──
function renderPage() {
  renderHeader();

  const sections = [];

  // Section 1: Core (always visible — senses)
  (WORD.senses || []).forEach((sense, i) => {
    sections.push(renderSense(sense, i));
  });

  // Section 2: Stroke (stub)
  sections.push(renderSection('stroke',
    `<div class="wd-section-stub">${langText('Stroke animation coming soon', '筆順動畫即將推出')}</div>`
  ));

  // Section 3: Characters
  const identityContent = renderIdentity();
  if (identityContent) {
    sections.push(renderSection('characters', identityContent));
  } else {
    // Still render the section shell even if no identity data
    sections.push(renderSection('characters',
      `<div class="wd-section-stub">${langText('Character information not available', '字形資訊暫無')}</div>`
    ));
  }

  // Section 4: Words (relations aggregated — placeholder, Phase 4 fills this)
  sections.push(renderSection('words',
    renderWordsSection()
  ));

  // Section 5: Constellation (family tree)
  const familyContent = renderFamilyTreeContent();
  sections.push(renderSection('familyTree',
    familyContent || `<div class="wd-section-stub">${langText('No constellation data yet', '尚無詞族資料')}</div>`
  ));

  // Section 6: Community (stub)
  sections.push(renderSection('community',
    `<div class="wd-section-stub">${langText(
      'Community contributions coming soon — public learner writing, AI-verified examples, ratings, and more.',
      '社群內容即將推出 — 學習者公開寫作、AI 驗證例句、評分等功能。'
    )}</div>`
  ));

  // Actions
  sections.push(renderActions());

  document.getElementById('wdMain').innerHTML = sections.filter(Boolean).join('');

  // Apply pinyin mode
  document.body.classList.toggle('wd-no-pinyin', pinyinMode === 'off');
  // Apply text dir
  document.getElementById('wdMain').classList.toggle('wd-vertical', textDir === 'vertical');
}

// ── INIT ──
document.addEventListener('DOMContentLoaded', function() {
  // Restore settings to toggles
  if (scriptMode === 'simplified') {
    document.getElementById('wdBtnTrad').classList.remove('active');
    document.getElementById('wdBtnSimp').classList.add('active');
  }
  if (langMode !== 'both') {
    ['wdBtnLangEn','wdBtnLangZh','wdBtnLangBoth'].forEach(id => document.getElementById(id)?.classList.remove('active'));
    const map = { en: 'wdBtnLangEn', zh: 'wdBtnLangZh', both: 'wdBtnLangBoth' };
    document.getElementById(map[langMode])?.classList.add('active');
  }
  if (iconsMode === 'off') {
    document.getElementById('wdBtnIconsOn').classList.remove('active');
    document.getElementById('wdBtnIconsOff').classList.add('active');
  }
  if (pinyinMode === 'off') {
    document.getElementById('wdBtnPinyinOn').classList.remove('active');
    document.getElementById('wdBtnPinyinOff').classList.add('active');
  }
  if (textDir === 'vertical') {
    document.getElementById('wdBtnHoriz').classList.remove('active');
    document.getElementById('wdBtnVert').classList.add('active');
  }

  applyLevelFonts(currentLevel);
  renderSectionToggles();
  renderPage();

  // Load related words asynchronously (once)
  loadRelatedWords();

  // Init pills after render
  requestAnimationFrame(() => {
    ['wdScriptToggle','wdLangToggle','wdIconsToggle','wdPinyinToggle','wdTextDirToggle','wdViewModeToggle'].forEach(wdUpdatePill);
  });

  // Back to top
  const btt = document.getElementById('wdBackToTop');
  window.addEventListener('scroll', function() {
    btt.classList.toggle('visible', window.scrollY > 300);
  }, { passive: true });
});
</script>
@include('partials.lexicon._preference-sync')
</body>
</html>
