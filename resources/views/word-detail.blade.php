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
@include('partials.lexicon._workshop-css')
<style>
/* ── WD PREFIX STYLES ── */

/* IWP settings header (below shared site header) */
.wd-header {
  position: sticky; top: 0; z-index: 90;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid var(--border);
  padding: 0.4rem 1rem;
  display: flex; flex-direction: column; gap: 0.4rem;
}
.wd-header-top {
  display: flex; align-items: center; gap: 0.6rem;
}
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
  padding: 0.5rem 0.6rem;
  margin-top: 5px;
}
.wd-header-char .hanzi-char {
  font-size: var(--fs-hanzi, 2.8rem);
}
.wd-header-char .hanzi-secondary {
  font-size: calc(2.8rem * 0.65);
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
  display: flex; flex-direction: column; gap: 0.3rem;
  font-size: 0.78rem; color: var(--dim);
}
.wd-identity-row strong { color: var(--text); font-weight: 500; }

/* ── SENSE BLOCK ── */
.wd-sense {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 2px;
  padding: 0.5rem 0.6rem 0.5rem 0.6rem;
  display: flex; flex-direction: column; gap: 0.5rem;
  overflow: hidden; word-wrap: break-word;
  position: relative;
  overflow: hidden;
}
.wd-sense.wd-sense-single { /* deprecated — kept for safety */
  border: none; background: none;
  padding: 0 0.75rem;
  margin-top: 0;
}
/* Tighten gap between hero and sense blocks */
.wd-main {
  padding-top: 0.6rem;
}
.wd-header {
  border-bottom: none;
}
.wd-sense-header {
  display: flex; flex-direction: column; gap: 0.4rem;
}
.wd-sense-stripe-row {
  display: flex;
  align-items: stretch;
  justify-content: space-between;
  margin: -0.5rem -0.6rem 0.25rem -0.6rem;
}
.wd-sense-stripe-row .wd-sense-stripe {
  margin: 0;
  flex: 1;
}
.wd-sense-stripe {
  background: var(--accent);
  color: #fff;
  font-family: 'DM Mono', monospace;
  font-size: 0.65rem;
  letter-spacing: 0.1em;
  padding: 0.25rem 0.75rem;
  margin: -0.5rem -0.6rem 0.25rem -0.6rem;
}
/* ── COMMUNITY SIGNAL BUTTONS (affirm + dispute) ──
   Layout: [purple stripe ========][👎][scores][👍]
   The inert .wd-signal-scores element sits between the two buttons as
   a physical gap so an inadvertent brush can't toggle either action. */
.wd-signal-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 2rem;
  padding: 0.15rem 0.6rem;
  background: #fff;
  font-size: 0.95rem;
  line-height: 1;
  cursor: pointer;
  transition: background 0.15s, color 0.15s, transform 0.08s;
  text-decoration: none;
}
.wd-signal-btn:active { transform: scale(0.94); }
.wd-signal-btn:disabled { opacity: 0.5; cursor: wait; }
.wd-signal-btn.guest { opacity: 0.5; cursor: pointer; }

/* Dispute — rose, flushes into the purple stripe on its left
   (no left border, so the purple → rose handoff reads as one element). */
.wd-dispute-btn {
  border: 1px solid var(--rose);
  border-left: none;
  color: var(--rose);
}
.wd-dispute-btn:hover { background: rgba(184, 48, 80, 0.06); }
.wd-dispute-btn.disputed {
  background: var(--rose);
  color: #fff;
}

/* Central inert scores panel — the gap that makes accidental clicks safe */
.wd-signal-scores {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.15rem 0.65rem;
  background: var(--surface2);
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  font-family: 'DM Mono', monospace;
  font-size: 0.68rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  cursor: default;
  user-select: none;
}
.wd-score-dispute { color: var(--rose); }
.wd-score-dot     { color: var(--dim); opacity: 0.5; }
.wd-score-affirm  { color: var(--accent); }

/* Affirm — accent purple, full border, sits at the far right */
.wd-affirm-btn {
  border: 1px solid var(--accent);
  color: var(--accent);
}
.wd-affirm-btn:hover { background: rgba(98, 64, 200, 0.06); }
.wd-affirm-btn.affirmed {
  background: var(--accent);
  color: #fff;
}
/* ── HERO ACTIONS (star + share beneath pinyin) ── */
.wd-word-meta {
  display: flex; align-items: center; flex-wrap: wrap; gap: 0.15rem 0.3rem;
  font-family: 'DM Mono', monospace; font-size: 0.62rem; letter-spacing: 0.04em;
  color: var(--dim); margin-top: 0.25rem; opacity: 0.75;
}
.wd-word-meta-dot { opacity: 0.4; margin: 0 0.1rem; }
.wd-hero-actions {
  display: flex; gap: 0.5rem; margin-top: 0.35rem;
}
.wd-save-btn, .wd-share-btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 2.8rem; height: 2.2rem;
  background: var(--surface); border: 1.5px solid var(--accent);
  border-radius: 4px; cursor: pointer;
  font-size: 1.3rem; color: var(--accent);
  transition: all 0.15s; opacity: 0.6;
}
.wd-save-btn:hover, .wd-share-btn:hover { opacity: 1; background: rgba(98,64,200,0.06); }
.wd-save-btn.saved { color: var(--accent); opacity: 1; background: rgba(98,64,200,0.08); }

/* Collection picker popover */
.wd-cp {
  position: absolute; top: 100%; left: 0; z-index: 200;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 3px; min-width: 200px; padding: 0.4rem 0;
  box-shadow: 0 8px 28px rgba(0,0,0,0.12);
  animation: wdCpIn 0.15s ease;
  max-height: 50vh; overflow-y: auto;
}
.wd-hero-actions { position: relative; }
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
  padding-top: 0.3rem;
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
.wd-colloc-item {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 1.15rem; color: var(--text);
  letter-spacing: 0.05em;
}
.wd-colloc-sep {
  display: inline-block;
  margin: 0 0.5rem;
  color: var(--dim); opacity: 0.4;
  font-size: 0.9rem;
}

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
.wd-rel-card-body {
  display: flex; flex-direction: column; gap: 0.1rem; min-width: 0;
}
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

/* ── STROKE SECTION (Hanzi Writer) ── */
.wd-stroke-chars {
  display: flex; flex-wrap: wrap;
  gap: 0.6rem;
  margin-bottom: 0.8rem;
}
.wd-vertical .wd-stroke-chars {
  flex-direction: column; align-items: flex-start;
}
.wd-stroke-char {
  width: 120px; height: 120px;
  background: rgba(255,255,255,0.7);
  border: 1px solid var(--border);
  border-radius: 2px;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: border-color 0.15s, transform 0.1s;
  overflow: hidden;
}
.wd-stroke-char:hover {
  border-color: var(--accent); transform: translateY(-1px);
}
.wd-stroke-char-missing {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 3rem; color: var(--dim);
}
.wd-stroke-controls {
  display: flex; gap: 0.5rem;
  margin-bottom: 0.4rem;
}
.wd-stroke-btn {
  background: none; border: 1px solid var(--border); border-radius: 2px;
  padding: 0.4rem 0.8rem; cursor: pointer;
  font-family: 'DM Mono', monospace; font-size: 0.72rem; color: var(--dim);
  transition: color 0.18s, border-color 0.18s;
}
.wd-stroke-btn:hover { color: var(--accent); border-color: var(--accent); }
.wd-stroke-status:empty { display: none; }

/* ── COMMUNITY SECTION ──
 * Writings use the shared writing-card renderer (_writing-card-js) — all card
 * styling lives in _example-sentence-css + _workshop-css. This block only
 * styles the Community section's own chrome: dropdown, trust strip, disputes
 * list, affirmed-senses list, empty states. Do NOT duplicate writing-card
 * styles here.
 */
.wd-community-header {
  display: flex; align-items: center; gap: 0.5rem;
  margin-bottom: 0.7rem;
}
.wd-community-select {
  font-family: 'DM Mono', monospace; font-size: 0.75rem;
  color: var(--ink); background: rgba(255,255,255,0.7);
  border: 1px solid var(--border); border-radius: 2px;
  padding: 0.35rem 0.7rem;
  cursor: pointer;
  flex: 1; max-width: 280px;
}
.wd-community-select:focus {
  outline: none; border-color: var(--accent);
}
.wd-community-trust-strip {
  display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 1rem;
  padding: 0.55rem 0.75rem;
  background: rgba(98,64,200,0.04);
  border: 1px solid rgba(98,64,200,0.12);
  border-radius: 2px;
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim);
  margin-bottom: 0.8rem;
}
.wd-community-trust-strip .wd-trust-num { color: var(--ink); font-weight: 500; }
.wd-community-trust-strip .wd-trust-dot { opacity: 0.35; }

.wd-community-empty {
  font-family: 'Cormorant Garamond', serif; font-style: italic;
  font-size: 0.9rem; color: var(--dim);
  padding: 0.6rem 0;
}
.wd-community-section-title {
  font-size: 0.55rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--dim);
  margin: 0.6rem 0 0.5rem;
}
.wd-community-section-title:first-child { margin-top: 0; }

/* ── Disputes list (not a writing card) ── */
.wd-dispute-list { display: flex; flex-direction: column; gap: 0.5rem; }
.wd-dispute-card {
  padding: 0.6rem 0.8rem;
  background: rgba(200,70,70,0.04);
  border: 1px solid rgba(200,70,70,0.15);
  border-left: 3px solid rgba(200,70,70,0.5);
  border-radius: 2px;
}
.wd-dispute-head {
  display: flex; flex-wrap: wrap; align-items: center; gap: 0.3rem 0.5rem;
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  color: var(--dim);
  margin-bottom: 0.4rem;
}
.wd-dispute-sense {
  font-family: 'Cormorant Garamond', serif; font-style: italic;
  color: var(--ink); font-size: 0.9rem;
  margin-bottom: 0.3rem;
}
.wd-dispute-rationale {
  font-family: 'Cormorant Garamond', serif;
  color: var(--ink); font-size: 0.85rem; line-height: 1.4;
}
.wd-dispute-fields {
  display: flex; flex-wrap: wrap; gap: 0.3rem;
  margin-top: 0.4rem;
}
.wd-dispute-field-chip {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  padding: 0.1rem 0.4rem; border-radius: 2px;
  color: #a04040; background: rgba(200,70,70,0.07);
  border: 1px solid rgba(200,70,70,0.2);
  letter-spacing: 0.05em;
}
.wd-dispute-status {
  font-family: 'DM Mono', monospace; font-size: 0.58rem;
  padding: 0.08rem 0.38rem; border-radius: 2px;
  letter-spacing: 0.06em; text-transform: uppercase;
  color: #a04040; background: rgba(200,70,70,0.07);
  border: 1px solid rgba(200,70,70,0.2);
}
.wd-dispute-status.under_review {
  color: var(--gold); background: rgba(160,114,10,0.08); border-color: rgba(160,114,10,0.2);
}

/* ── Affirmed senses ranked list ── */
.wd-affirmed-list { display: flex; flex-direction: column; gap: 0.4rem; }
.wd-affirmed-row {
  display: flex; align-items: center; gap: 0.7rem;
  padding: 0.5rem 0.7rem;
  background: rgba(255,255,255,0.7);
  border: 1px solid var(--border); border-radius: 2px;
}
.wd-affirmed-count {
  font-family: 'DM Mono', monospace; font-size: 1.1rem;
  color: var(--accent); font-weight: 600;
  min-width: 2.5rem;
}
.wd-affirmed-body { flex: 1; min-width: 0; }
.wd-affirmed-pos {
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  color: #7060a8; background: rgba(98,64,200,0.07);
  border: 1px solid rgba(98,64,200,0.18);
  border-radius: 2px; padding: 0.08rem 0.4rem;
  letter-spacing: 0.04em; margin-right: 0.5rem;
}
.wd-affirmed-def {
  font-family: 'Cormorant Garamond', serif;
  color: var(--ink); font-size: 0.9rem;
}
.wd-affirmed-me {
  font-size: 0.85rem; color: var(--accent);
}

/* ── Trust view (expanded detail) ── */
.wd-trust-detail {
  display: flex; flex-direction: column; gap: 0.4rem;
  font-family: 'Cormorant Garamond', serif; font-size: 0.95rem;
  color: var(--ink);
  padding: 0.4rem 0;
}
.wd-trust-detail-row { display: flex; gap: 0.5rem; align-items: baseline; }
.wd-trust-detail-num { font-family: 'DM Mono', monospace; color: var(--accent); font-weight: 600; }

/* ── Actions row ── */
.wd-community-actions {
  display: flex; justify-content: flex-end; gap: 0.6rem;
  margin-top: 0.7rem;
}
.wd-see-more, .wd-refresh-btn {
  background: none; border: 1px solid var(--border); border-radius: 2px;
  padding: 0.35rem 0.7rem; cursor: pointer;
  font-family: 'DM Mono', monospace; font-size: 0.7rem; color: var(--dim);
  transition: color 0.18s, border-color 0.18s;
}
.wd-see-more:hover, .wd-refresh-btn:hover { color: var(--accent); border-color: var(--accent); }

/* ── Ex-sent Community overrides ──
 * The shared writing card (_writing-card-js) renders .ex-sent.saved-writing.
 * In Community context, we want a modest background tint and the
 * "mine" indicator (left border accent) when it's the learner's own writing.
 */
.wd-community-content .ex-sent.saved-writing {
  background: rgba(255,255,255,0.7);
  border: 1px solid var(--border); border-radius: 2px;
  padding: 0.6rem 0.8rem;
  transition: border-color 0.15s, transform 0.1s;
}
.wd-community-content .ex-sent.saved-writing:hover {
  border-color: var(--accent); transform: translateY(-1px);
}
.wd-community-content .ex-sentences {
  display: flex; flex-direction: column; gap: 0.5rem;
}

/* ── VERTICAL TEXT ORIENTATION: char on left, body stacked to right ── */
.wd-vertical .wd-rel-card {
  flex-direction: row; align-items: flex-start; gap: 0.6rem;
}
.wd-vertical .wd-rel-card-char {
  writing-mode: vertical-rl; text-orientation: mixed;
  letter-spacing: 0.08em;
  flex-shrink: 0;
}
.wd-vertical .wd-rel-card-body {
  flex: 1; min-width: 0;
}

/* ── Workshop styles: loaded from shared partial _workshop-css ── */

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
  border-radius: 4px;
  overflow: hidden;
  border: 1px solid var(--border);
}
.wd-section-toggle {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0.55rem 0.75rem;
  cursor: pointer; background: var(--surface2); border: none;
  width: 100%; text-align: left;
  font-family: 'DM Mono', monospace;
  font-size: 0.81rem; letter-spacing: 0.08em;
  color: var(--accent);
  transition: opacity 0.15s;
  border-radius: 4px;
}
.wd-section-toggle[aria-expanded="true"] {
  border-radius: 4px 4px 0 0;
}
.wd-section-toggle:hover { opacity: 0.85; }
.wd-section-body {
  display: none;
  flex-direction: column; gap: 0.75rem;
  padding: 0.75rem;
  background: var(--surface);
  border-radius: 0 0 4px 4px;
}
.wd-section-body.open {
  display: flex;
}
.wd-section-arrow {
  font-size: 0.85rem; color: var(--accent);
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

<!-- Shared Header -->
@include('partials.lexicon._site-header', ['backUrl' => route('lexicon.index'), 'backLabel' => 'Lexicon'])

<!-- Sticky IWP Settings Header -->
<div class="wd-header" id="wdHeader">
  <div class="wd-header-top">
    <div class="wd-breadcrumb" id="wdBreadcrumb"></div>
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
          <div class="iface-group-label">VERB PRESENTATION</div>
          <div class="wd-toggle" id="wdVerbPresentationToggle">
            <button class="wd-toggle-btn active" id="wdBtnVerbConsolidated" onclick="wdSetVerbPresentation('consolidated')">Consolidated</button>
            <button class="wd-toggle-btn" id="wdBtnVerbIntricate" onclick="wdSetVerbPresentation('intricate')">Intricate 精細</button>
          </div>
          <div class="iface-hint">Consolidated: V-t · V-i · V-sep. Intricate: full taxonomy Va-t · Vp-i · Vs-sep…</div>
        </div>

        <div class="iface-group">
          <div class="iface-group-label">SYMBOLS</div>
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
          <div class="iface-group-label">PINYIN FORMAT</div>
          <div class="wd-toggle" id="wdPinyinDisplayToggle">
            <button class="wd-toggle-btn active" id="wdBtnPinyinAccented" onclick="wdSetPinyinDisplay('accented')">Diacritic biǎo</button>
            <button class="wd-toggle-btn" id="wdBtnPinyinNumeric" onclick="wdSetPinyinDisplay('numeric')">Numeric biao3</button>
          </div>
          <div class="iface-hint">Tone marks (biǎo) or numeric tones (biao3)</div>
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
          <div class="iface-group-label">POS ALIGNMENT</div>
          <div style="display:flex;flex-direction:column;gap:0.3rem">
            <label style="display:flex;align-items:center;gap:0.4rem;font-family:'DM Mono',monospace;font-size:0.72rem;color:var(--dim);cursor:pointer">
              <input type="checkbox" id="wdAlignShowPartial" checked onchange="wdSetAlignmentFilter()"> 🤨 Partial
            </label>
            <label style="display:flex;align-items:center;gap:0.4rem;font-family:'DM Mono',monospace;font-size:0.72rem;color:var(--dim);cursor:pointer">
              <input type="checkbox" id="wdAlignShowDisputed" checked onchange="wdSetAlignmentFilter()"> 😵‍💫 Disputed
            </label>
          </div>
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
let scriptMode      = localStorage.getItem('scriptMode')      || 'traditional';
let langMode        = localStorage.getItem('langMode')        || 'both';
let iconsMode       = localStorage.getItem('iconsMode')       || 'on';

/**
 * Resolve definitions from bilingual {en:[], zh:[]} structure based on langMode.
 * Falls back gracefully for legacy flat-array format.
 */
function getSenseDefs(defs) {
  if (!defs) return [];
  if (Array.isArray(defs)) return defs; // legacy flat array
  if (langMode === 'zh') return defs.zh?.length ? defs.zh : (defs.en || []);
  if (langMode === 'en') return defs.en?.length ? defs.en : (defs.zh || []);
  // 'both': EN then ZH
  return [...(defs.en || []), ...(defs.zh || [])];
}

/** Get all definitions across both languages (for search/POS matching). */
function getAllDefs(defs) {
  if (!defs) return [];
  if (Array.isArray(defs)) return defs;
  return [...(defs.en || []), ...(defs.zh || [])];
}

/** Look up a single note by slug from the new notes array shape. */
function getNoteBySlug(sense, lang, slug) {
  return (sense.notes?.[lang] || []).find(n => n.slug === slug)?.content || '';
}

/** Resolve formula from bilingual notes based on langMode (no fallbacks). */
function getFormula(sense) {
  const lang = langMode === 'zh' ? 'zh' : 'en';
  return getNoteBySlug(sense, lang, 'formula');
}
let pinyinMode      = localStorage.getItem('pinyinMode')      || 'on';
let currentLevel    = localStorage.getItem('currentLevel')    || 'developing';
let fontScale       = parseInt(localStorage.getItem('fontScale')) || 100;
let textDir         = localStorage.getItem('textDir')         || 'horizontal';
let verbPresentation  = localStorage.getItem('verbPresentation')  || 'consolidated';
let pinyinDisplay     = localStorage.getItem('pinyinDisplay')     || 'accented';

// POS Alignment filter — shared with SRP via localStorage
let wdAlignShowPartial = localStorage.getItem('alignShowPartial') !== 'false';
let wdAlignShowDisputed = localStorage.getItem('alignShowDisputed') !== 'false';

function wdAlignmentVisible(alignment) {
  if (!alignment || alignment === 'full') return true;
  if (alignment === 'partial') return wdAlignShowPartial;
  if (alignment === 'disputed') return wdAlignShowDisputed;
  return true;
}

function wdSetAlignmentFilter() {
  const partialEl = document.getElementById('wdAlignShowPartial');
  const disputedEl = document.getElementById('wdAlignShowDisputed');
  wdAlignShowPartial = partialEl ? partialEl.checked : true;
  wdAlignShowDisputed = disputedEl ? disputedEl.checked : true;
  localStorage.setItem('alignShowPartial', wdAlignShowPartial);
  localStorage.setItem('alignShowDisputed', wdAlignShowDisputed);
  wdRender();
}

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
@include('partials.lexicon._writing-card-js')
@include('partials.lexicon._workshop-js')
<script>
// Workshop adapter for IWP: look up word data from WORD global
window.wsGetWordData = function(wordKey) {
  if (typeof WORD === 'undefined') return null;
  // For IWP, wordKey may be the traditional character or a sense-specific key (e.g. '流_s42')
  if (WORD.traditional === wordKey) {
    // Single-sense or generic lookup — return first sense data
    const s = (WORD.senses || [])[0];
    if (!s) return WORD;
    return Object.assign({}, WORD, { senseId: s.id, senseIds: [s.id], wordObjectId: WORD.wordObjectId, definitions: getSenseDefs(s.definitions) });
  }
  // Match sense-specific key pattern: traditional + '_s' + senseId
  const senseMatch = wordKey.match(new RegExp('^' + WORD.traditional.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '_s(\\d+)$'));
  if (senseMatch) {
    const senseId = parseInt(senseMatch[1]);
    const sense = (WORD.senses || []).find(s => s.id === senseId);
    if (sense) {
      return {
        traditional: WORD.traditional,
        simplified: WORD.simplified || '',
        pinyin: sense.pinyin || WORD.pinyin || '',
        definition: getSenseDefs(sense.definitions)[0]?.def || '',
        register: sense.register || '',
        connotation: sense.connotation || '',
        channel: sense.channel || '',
        level: sense.tocfl || '',
        formula: getFormula(sense),
        senseId: sense.id,
        senseIds: [sense.id],
        wordObjectId: WORD.wordObjectId,
        definitions: getSenseDefs(sense.definitions),
      };
    }
  }
  return null;
};
window.wsResolveWordKey = function(ex) {
  if (typeof WORD === 'undefined') return null;
  // Return sense-specific key to match IWP panel keys (traditional + '_s' + senseId)
  const sense = (WORD.senses || []).find(s => s.id === ex.word_sense_id);
  if (sense) return WORD.traditional + '_s' + sense.id;
  return null;
};
</script>
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
  const toggle = body ? body.previousElementSibling : null;
  if (!body) return;
  const isOpen = body.classList.toggle('open');
  sectionOpenState[key] = isOpen;
  localStorage.setItem('wdOpen_' + key, isOpen);
  if (window.syncPref) syncPref('wdOpen_' + key, String(isOpen));
  if (arrow) arrow.textContent = isOpen ? '▼' : '▲';
  if (toggle) toggle.setAttribute('aria-expanded', isOpen);
}

function renderSection(key, contentHTML) {
  if (!sectionVisibility[key]) return '';
  const sec = SECTIONS.find(s => s.key === key);
  if (!sec) return '';
  const isOpen = sectionOpenState[key] !== false;
  return `<div class="wd-section" id="wdSection-${key}">
    <button class="wd-section-toggle" aria-expanded="${isOpen}" onclick="wdToggleSectionCollapse('${key}')">
      <span>${langText(sec.en, sec.zh)}</span>
      <span class="wd-section-arrow" id="wdSectionArrow-${key}">${isOpen ? '▼' : '▲'}</span>
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

function wdSetPinyinDisplay(mode) {
  pinyinDisplay = mode;
  localStorage.setItem('pinyinDisplay', mode);
  if (window.syncPref) syncPref('pinyinDisplay', mode);
  document.getElementById('wdBtnPinyinAccented').classList.toggle('active', mode === 'accented');
  document.getElementById('wdBtnPinyinNumeric').classList.toggle('active', mode === 'numeric');
  wdUpdatePill('wdPinyinDisplayToggle');
  renderPage();
}

function wdSetVerbPresentation(mode) {
  verbPresentation = mode;
  localStorage.setItem('verbPresentation', mode);
  if (window.syncPref) syncPref('verbPresentation', mode);
  document.getElementById('wdBtnVerbConsolidated').classList.toggle('active', mode === 'consolidated');
  document.getElementById('wdBtnVerbIntricate').classList.toggle('active', mode === 'intricate');
  wdUpdatePill('wdVerbPresentationToggle');
  renderPage();
}

// ── SAVE/UNSAVE WORD ──
function _csrfHeader() {
  return document.querySelector('meta[name="csrf-token"]').content;
}

async function wdToggleAffirm(senseId, btn) {
  if (!window.__AUTH) { window.location.href = '/login'; return; }
  if (btn.disabled) return;
  btn.disabled = true;
  try {
    const res = await fetch(`/api/affirmations/${senseId}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfHeader() },
    });
    if (!res.ok) throw new Error('request failed');
    const data = await res.json();

    // Mutate in-memory WORD so re-renders reflect the new state
    const sense = (WORD.senses || []).find(s => s.id === senseId);
    if (sense) {
      sense.affirmCount = data.count;
      sense.affirmedByMe = data.affirmed;
    }
    // Update all affirm buttons pointing to this sense (class state) AND
    // the central scores panel's affirm count (the inert display element).
    document.querySelectorAll(`.wd-affirm-btn[data-sense-id="${senseId}"]`).forEach(b => {
      b.classList.toggle('affirmed', !!data.affirmed);
    });
    document.querySelectorAll(`.wd-signal-scores[data-sense-id="${senseId}"] .wd-score-affirm`).forEach(el => {
      el.textContent = data.count;
    });
    // Signal the Community section to refresh its trust strip.
    window.dispatchEvent(new CustomEvent('hn:affirmation-changed', {
      detail: { senseId, affirmed: !!data.affirmed, count: data.count }
    }));
  } catch (e) {
    console.error('affirm toggle failed', e);
  } finally {
    btn.disabled = false;
  }
}

function wdToggleSave() {
  if (!window.__AUTH || !WORD.wordObjectId) return;
  var wordObjectId = WORD.wordObjectId;
  var isSaved = (window.__AUTH.savedWordIds || []).includes(wordObjectId);

  if (isSaved) {
    // Already saved — just show the picker (don't toggle)
    var existingPicker = document.getElementById('wdCollectionPicker');
    if (existingPicker) {
      wdDismissCollectionPicker();
    } else {
      wdShowCollectionPicker();
    }
  } else {
    // Not saved — save it, fill star, show picker
    fetch('/api/saved-words/' + wordObjectId, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfHeader() },
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.saved) {
        var btn = document.getElementById('wdSaveBtn');
        if (btn) {
          btn.classList.add('saved');
          btn.innerHTML = '&#9733;';
          btn.title = 'Manage';
        }
        if (!window.__AUTH.savedWordIds.includes(wordObjectId)) {
          window.__AUTH.savedWordIds.push(wordObjectId);
        }
        wdShowCollectionPicker();
      }
    });
  }
}

function wdUnsaveWord() {
  var wordObjectId = WORD.wordObjectId;
  fetch('/api/saved-words/' + wordObjectId, {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfHeader() },
  })
  .then(function(r) { return r.json(); })
  .then(function() {
    var btn = document.getElementById('wdSaveBtn');
    if (btn) {
      btn.classList.remove('saved');
      btn.innerHTML = '&#9734;';
      btn.title = 'Save';
    }
    window.__AUTH.savedWordIds = (window.__AUTH.savedWordIds || []).filter(function(id) { return id !== wordObjectId; });
    // Remove from all collections in memory
    (window.__AUTH.collections || []).forEach(function(c) {
      c.wordObjectIds = (c.wordObjectIds || []).filter(function(id) { return id !== wordObjectId; });
    });
    wdDismissCollectionPicker();
  });
}

// ── COLLECTION PICKER POPOVER ──
var _cpDismissHandler = null;

function wdShowCollectionPicker() {
  wdDismissCollectionPicker();
  var wordObjectId = WORD.wordObjectId;
  var collections = window.__AUTH.collections || [];
  var inAnyCollection = collections.some(function(c) { return (c.wordObjectIds || []).includes(wordObjectId); });
  var html = '<div class="wd-cp-title">Manage collections</div>';

  // Uncategorized option
  var uncatChecked = !inAnyCollection ? ' checked' : '';
  html += '<label class="wd-cp-item wd-cp-uncat">'
    + '<input type="checkbox" id="wdCpUncat"' + uncatChecked + ' onchange="wdHandleUncategorized(this,' + wordObjectId + ')">'
    + '<span>Uncategorized</span></label>';

  // Collection list
  collections.forEach(function(c) {
    var checked = (c.wordObjectIds || []).includes(wordObjectId) ? ' checked' : '';
    html += '<label class="wd-cp-item">'
      + '<input type="checkbox"' + checked + ' onchange="wdToggleCollectionWord(' + c.id + ',' + wordObjectId + ',this)">'
      + '<span>' + escHtml(c.name) + '</span></label>';
  });

  html += '<div class="wd-cp-new">'
    + '<input type="text" id="wdCpNewInput" placeholder="New collection…" '
    + 'onkeydown="if(event.key===\'Enter\')wdCreateCollection()">'
    + '<button onclick="wdCreateCollection()" title="Create">+</button>'
    + '</div>';

  var popover = document.createElement('div');
  popover.className = 'wd-cp';
  popover.id = 'wdCollectionPicker';
  popover.innerHTML = html;

  var header = document.getElementById('wdSaveBtn');
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

function wdToggleCollectionWord(collectionId, wordObjectId, checkbox) {
  var method = checkbox.checked ? 'POST' : 'DELETE';
  fetch('/api/collections/' + collectionId + '/words/' + wordObjectId, {
    method: method,
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfHeader() },
  }).then(function(r) { return r.json(); })
  .then(function(data) {
    // Update __AUTH.collections in memory
    var c = (window.__AUTH.collections || []).find(function(c) { return c.id === collectionId; });
    if (!c) return;
    if (checkbox.checked) {
      if (!(c.wordObjectIds || []).includes(wordObjectId)) c.wordObjectIds.push(wordObjectId);
      // Uncheck "Uncategorized" since word is now in a collection
      var uncat = document.getElementById('wdCpUncat');
      if (uncat) uncat.checked = false;
    } else {
      c.wordObjectIds = (c.wordObjectIds || []).filter(function(id) { return id !== wordObjectId; });
      // Check if word is still in any collection
      var inAny = (window.__AUTH.collections || []).some(function(col) {
        return (col.wordObjectIds || []).includes(wordObjectId);
      });
      if (!inAny) {
        // No collections left — check Uncategorized
        var uncat = document.getElementById('wdCpUncat');
        if (uncat) uncat.checked = true;
      }
    }
  });
}

function wdHandleUncategorized(checkbox, wordObjectId) {
  if (!checkbox.checked) {
    // Unchecking Uncategorized when no collections are checked = unsave entirely
    var inAny = (window.__AUTH.collections || []).some(function(c) {
      return (c.wordObjectIds || []).includes(wordObjectId);
    });
    if (!inAny) {
      // Truly unsave the word
      wdUnsaveWord();
    }
  } else {
    // Re-checking Uncategorized — word stays saved but not in any collection
    // (it's already saved, so nothing to do)
  }
}

function wdCreateCollection() {
  var wordObjectId = WORD.wordObjectId;
  var input = document.getElementById('wdCpNewInput');
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
    // Add word to the new collection
    return fetch('/api/collections/' + collection.id + '/words/' + wordObjectId, {
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
      wordObjectIds: [wordObjectId],
    });
    // Re-show picker with updated data
    wdShowCollectionPicker();
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
  if (window.__AUTH && WORD.wordObjectId) {
    clearTimeout(_noteSyncTimer[senseId]);
    _noteSyncTimer[senseId] = setTimeout(function() {
      var csrfToken = document.querySelector('meta[name="csrf-token"]');
      fetch('/api/saved-words/' + WORD.wordObjectId + '/note', {
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
// Workshop functions now in shared partial _workshop-js

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
  return p ? formatPinyin(p.text) : '';
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
  // Breadcrumb trail (shared header already has ← Lexicon)
  if (trail.length > 1) {
    const crumbs = trail.map((t, i) => {
      if (i === trail.length - 1) return `<strong>${t.label}</strong>`;
      // Sentence origin — link back to SRP with sentence pre-filled
      if (t.smartId === '__sentence__') {
        const sentenceQ = (t.sentence || sessionStorage.getItem('lexiconSentence') || '').replace(/'/g, "\\'");
        return `<a href="/lexicon?q=${encodeURIComponent(sentenceQ)}" onclick="event.preventDefault(); window.location.href='/lexicon?q=${encodeURIComponent(sentenceQ)}'">${t.label}</a>`;
      }
      return `<a href="/lexicon/${t.smartId}" onclick="event.preventDefault(); pushTrail('${t.smartId}','${t.label}'); window.location.href='/lexicon/${t.smartId}'">${t.label}</a>`;
    }).join(' <span style="opacity:0.4">\u203a</span> ');
    bc.innerHTML = crumbs;
  } else {
    bc.innerHTML = '';
  }

  // Build header using shared card-hanzi + card-hdr-mid pattern
  const primaryChar = charDisplay();
  const simpCharVal = scriptMode === 'simplified'
    ? (WORD.traditional !== WORD.simplified ? WORD.traditional : '')
    : (WORD.simplified && WORD.simplified !== WORD.traditional ? WORD.simplified : '');

  // Build per-sense domain + POS pairs for the hero (flat domain display)
  const senses = WORD.senses || [];
  const sensePairsHTML = senses.map((s, idx) => {
    // Flat domain list for this sense: primary + all secondaries in sequence
    const allDomains = [];
    if (s.domain) allDomains.push(s.domain);
    (s.secondaryDomains || []).forEach(sd => allDomains.push(sd));
    const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
    const domChip = allDomains.length ? `<div class="card-domain-flat" data-state="${preferred}" onclick="toggleLangChip(event,this)">${allDomains.map((d, di) => {
      const en = d.en || d.slug;
      const zh = d.zh || d.slug;
      const display = preferred === 'zh' ? zh : (uiMode === 'all' || uiMode === 'en-zh') ? `${en} ${zh}` : en;
      return `${di ? ', ' : ''}<span class="card-domain-item" data-en="${en}" data-zh="${zh}">${display}</span>`;
    }).join('')}</div>` : '';

    // POS badge for this sense (first definition's POS)
    const pos = getAllDefs(s.definitions).find(d => d.pos)?.pos;
    let posChip = '';
    if (pos) {
      const enText = posDisplayLabel(pos) + ' \u00b7 ' + posLabel(pos);
      const zhText = POS_ZH[pos] || posDisplay(pos);
      const display = preferred === 'zh' ? zhText : (uiMode === 'all' || uiMode === 'en-zh') ? enText + ' ' + zhText : enText;
      const alignIcon = posAlignIcon(s.alignment || WORD.alignment);
      const iconHTML = alignIcon ? `<span class="pos-align-icon">${alignIcon}</span>` : '';
      posChip = `<span class="card-pos-hdr" data-en="${enText}" data-zh="${zhText}" data-state="${preferred}" onclick="toggleLangChip(event,this)">${display}${iconHTML}</span>`;
    }

    return `<div class="card-sense-pair">${domChip}<div class="card-pos-summary">${posChip}</div></div>`;
  }).join('');

  const pinyin = primaryPinyin();

  // Word-level save star in hero
  const wordSaveBtn = window.__AUTH && WORD.wordObjectId ? (() => {
    const isSv = (window.__AUTH.savedWordIds || []).includes(WORD.wordObjectId);
    return `<button class="wd-save-btn${isSv ? ' saved' : ''}" id="wdSaveBtn" onclick="wdToggleSave()" title="${isSv ? 'Unsave' : 'Save'}">${isSv ? '&#9733;' : '&#9734;'}</button>`;
  })() : '';
  const wordShareBtn = `<button class="wd-share-btn" id="wdShareBtn" onclick="wdShare()" title="${langText('Share', '分享')}">&nearr;</button>`;

  document.getElementById('wdHeaderChar').innerHTML = `
    <div class="card-hanzi">
      <div class="hanzi-primary-wrap">
        <span class="hanzi-char">${primaryChar}</span>
        ${simpCharVal ? `<button class="script-switch-btn" data-secondary="${simpCharVal}" onclick="toggleSecondaryChar(event,this)" title="Reveal ${scriptMode === 'simplified' ? 'traditional' : 'simplified'}">⇌</button>` : ''}
      </div>
    </div>
    <div class="card-hdr-mid">
      ${sensePairsHTML ? `<div class="card-sense-pairs">${sensePairsHTML}</div>` : ''}
      ${pinyin ? `<div class="card-pinyin-row"><span class="pinyin pinyin-h">${pinyin}</span></div>` : ''}
      ${(() => {
        const bits = [];
        if (WORD.subtlexRank) bits.push(`<span title="${langText('Frequency rank (SUBTLEX-CH)', '使用頻率排名')}">#${WORD.subtlexRank.toLocaleString()}</span>`);
        if (WORD.radical) bits.push(`<span title="${langText('Radical', '部首')}">${WORD.radical.character} ${langText(WORD.radical.meaning, WORD.radical.meaningZh || WORD.radical.meaning)}</span>`);
        if (WORD.strokesTrad) bits.push(`<span>${WORD.strokesTrad}${langText(' strokes', ' 筆')}</span>`);
        return bits.length ? `<div class="wd-word-meta">${bits.join('<span class="wd-word-meta-dot">·</span>')}</div>` : '';
      })()}
      <div class="wd-hero-actions">
        ${wordSaveBtn}
        ${wordShareBtn}
      </div>
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

  // SUBTLEX-CH frequency
  if (w.subtlexRank) {
    const ppm = w.subtlexPpm != null ? (w.subtlexPpm >= 1 ? w.subtlexPpm.toFixed(1) : w.subtlexPpm.toFixed(2)) : null;
    rows.push(`<span>${langText('Frequency', '使用頻率')}: <strong>#${w.subtlexRank.toLocaleString()}</strong>${ppm ? ` <span style="opacity:0.55;font-size:0.8em">${ppm}/M</span>` : ''}</span>`);
  }
  if (w.subtlexCd != null) {
    rows.push(`<span>${langText('Contextual Diversity', '語境多樣性')}: <strong>${w.subtlexCd}%</strong> <span style="opacity:0.55;font-size:0.8em">${langText('of films/shows', '影視作品')}</span></span>`);
  }

  if (!charGrid && !rows.length) return '';

  return `<div class="wd-identity">
    <div class="wd-identity-title">${langText('Character Identity', '字形資訊')}</div>
    ${charGrid}
    ${rows.length ? `<div class="wd-identity-row">${rows.join('')}</div>` : ''}
  </div>`;
}

// ── RENDER: SENSE ──
function renderSense(sense, idx, totalOverride) {
  const parts = [];

  // Sense header: stripe + domain + pinyin (no character repetition — hero shows it)
  const totalSenses = totalOverride || (WORD.senses || []).length;

  // Sense-level community signal cluster — three elements after the stripe:
  // dispute button (👎), inert scores panel, affirm button (👍). The central
  // panel is non-clickable, giving the two action buttons physical separation
  // so a stray tap on the counts can't fire either toggle.
  const affirmCount = sense.affirmCount || 0;
  const affirmedByMe = !!sense.affirmedByMe;
  const disputeCount = sense.disputeCount || 0;
  const disputedByMe = !!sense.disputedByMe;
  const isAuthed = !!window.__AUTH;

  const affirmTitle = isAuthed
    ? (affirmedByMe
        ? langText('You affirm this sense', '您已肯定此義項')
        : langText('Affirm this sense', '肯定此義項'))
    : langText('Sign in to affirm', '登入後可肯定');
  const disputeTitle = isAuthed
    ? (disputedByMe
        ? langText('You have disputed this sense', '您已對此義項提出質疑')
        : langText('Dispute this sense', '對此義項提出質疑'))
    : langText('Sign in to dispute', '登入後可質疑');

  // Dispute button navigates to the composer page — not a toggle.
  // Disputes are heavy (fields + rationale), never a one-click commit.
  const disputeHref = isAuthed ? `/disputations/create?senseId=${sense.id}` : '/login';
  const disputeBtn = `<a href="${disputeHref}"
    class="wd-signal-btn wd-dispute-btn${disputedByMe ? ' disputed' : ''}${isAuthed ? '' : ' guest'}"
    data-sense-id="${sense.id}"
    title="${disputeTitle}">👎</a>`;

  const scoresPanel = `<div class="wd-signal-scores" data-sense-id="${sense.id}"
    title="${disputeCount} dispute${disputeCount === 1 ? '' : 's'} · ${affirmCount} affirmation${affirmCount === 1 ? '' : 's'}">
    <span class="wd-score-dispute">${disputeCount}</span><span class="wd-score-dot">·</span><span class="wd-score-affirm">${affirmCount}</span>
  </div>`;

  const affirmBtn = `<button type="button"
    class="wd-signal-btn wd-affirm-btn${affirmedByMe ? ' affirmed' : ''}${isAuthed ? '' : ' guest'}"
    data-sense-id="${sense.id}"
    ${isAuthed ? `onclick="wdToggleAffirm(${sense.id}, this)"` : 'onclick="window.location.href=\'/login\'"'}
    title="${affirmTitle}">👍</button>`;

  parts.push(`<div class="wd-sense-stripe-row">
    <div class="wd-sense-stripe">${idx + 1} of ${totalSenses}</div>
    ${disputeBtn}${scoresPanel}${affirmBtn}
  </div>`);

  // Flat domain display at sense level
  const senseDomains = [];
  if (sense.domain) senseDomains.push(sense.domain);
  (sense.secondaryDomains || []).forEach(sd => senseDomains.push(sd));
  const sdPreferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
  const domainHTML = senseDomains.length ? `<div class="card-domain-row"><div class="card-domain-flat" data-state="${sdPreferred}" onclick="toggleLangChip(event,this)">${senseDomains.map((d, di) => {
    const en = d.en || d.slug;
    const zh = d.zh || d.slug;
    const display = sdPreferred === 'zh' ? zh : (uiMode === 'all' || uiMode === 'en-zh') ? `${en} ${zh}` : en;
    return `${di ? ', ' : ''}<span class="card-domain-item" data-en="${en}" data-zh="${zh}">${display}</span>`;
  }).join('')}</div></div>` : '';
  const pinyinHTML = sense.pinyin ? `<div class="card-pinyin-row"><span class="pinyin pinyin-h">${formatPinyin(sense.pinyin)}</span></div>` : '';
  if (domainHTML || pinyinHTML) {
    parts.push(`<div style="padding:0.3rem 0">${domainHTML}${pinyinHTML}</div>`);
  }

  // Definitions + bilingual notes (formula, usage note)
  const resolvedDefs = getSenseDefs(sense.definitions);
  if (isSectionVisible('definitions') && resolvedDefs.length) {
    // Helper: apply simplified script swap to formula
    const fmlSwap = (f) => f && scriptMode === 'simplified' && WORD.traditional !== WORD.simplified
      ? f.replace(new RegExp(WORD.traditional.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), WORD.simplified) : f;

    const defs = resolvedDefs.map(d => {
      return `<div class="card-def-row">
        ${d.pos ? `<span class="card-pos" data-abbr="${posLabel(d.pos)}" data-full="${posDisplay(d.pos)}" data-zh="${POS_ZH[d.pos] || posDisplay(d.pos)}" data-state="abbr" onclick="cyclePosChip(event, this)">${posLabel(d.pos)}${posAlignIcon(sense.alignment || WORD.alignment) ? '<span class="pos-align-icon">' + posAlignIcon(sense.alignment || WORD.alignment) + '</span>' : ''}</span>` : ''}
        <span class="card-definition">${d.def}</span>
      </div>`;
    }).join('');

    let defBlock = `<div class="wd-defs">${defs}`;

    // Formula: always one version — LPL. No fallbacks, no legacy.
    const fml = getFormula(sense);
    const fmlDisplay = fmlSwap(fml);
    if (fmlDisplay) defBlock += `<div class="card-formula">${segmentedHTML(fmlDisplay, WORD)}</div>`;

    // Usage note: no fallbacks, no legacy fields.
    const usageEn = getNoteBySlug(sense, 'en', 'usage-note');
    const usageZh = getNoteBySlug(sense, 'zh', 'usage-note');
    if (langMode === 'both') {
      if (usageEn || usageZh) {
        defBlock += `<div class="card-usage-note">`;
        if (usageEn) defBlock += `<div>${segmentedHTML(usageEn, WORD)}</div>`;
        if (usageZh) defBlock += `<div class="card-usage-note-zh">${segmentedHTML(usageZh, WORD)}</div>`;
        defBlock += `</div>`;
      }
    } else {
      const usageNote = getNoteBySlug(sense, langMode === 'zh' ? 'zh' : 'en', 'usage-note');
      if (usageNote) defBlock += `<div class="card-usage-note">${segmentedHTML(usageNote, WORD)}</div>`;
    }

    defBlock += `</div>`;
    parts.push(defBlock);
  }

  // Attribute chips
  if (isSectionVisible('attributes')) {
    const chips = [];
    if (sense.register) chips.push(cardAttr('register', sense.register, 'Register', metaAttrLabel('register', sense.register)));
    if (sense.connotation) chips.push(cardAttr('connotation', sense.connotation, 'Connotation', metaAttrLabel('connotation', sense.connotation)));
    if (sense.channel) chips.push(cardAttr('channel', sense.channel, 'Channel', metaAttrLabel('channel', sense.channel)));
    if ((sense.dimensions || []).length) chips.push(cardAttrMulti('dimension', sense.dimensions, 'Dimension'));
    if (sense.intensity) chips.push(cardAttr('intensity', sense.intensity, 'Intensity', metaAttrLabel('intensity', sense.intensity)));
    if (sense.tocfl) chips.push(cardAttr('tocfl', sense.tocfl, 'TOCFL', metaAttrLabel('tocfl', sense.tocfl)));
    if (chips.length) {
      parts.push(`<div class="wd-attrs">${chips.join('')}</div>`);
    }
  }

  // Examples now live inside the Writing Conservatory panel (renderWorkshop),
  // so the standalone section is no longer rendered here.

  // Learner Traps (bilingual — pick by langMode, no fallbacks)
  if (isSectionVisible('learnerTraps')) {
    const trapsEn = getNoteBySlug(sense, 'en', 'learner-traps');
    const trapsZh = getNoteBySlug(sense, 'zh', 'learner-traps');

    if (langMode === 'both') {
      if (trapsEn || trapsZh) {
        let trapsHtml = `<div class="wd-traps">
          <div class="wd-traps-title">${langText('Learner Traps', '學習陷阱')}</div>`;
        if (trapsEn) trapsHtml += `<div class="wd-traps-text">${segmentedHTML(trapsEn, WORD)}</div>`;
        if (trapsZh) trapsHtml += `<div class="wd-traps-text wd-traps-zh">${segmentedHTML(trapsZh, WORD)}</div>`;
        trapsHtml += `</div>`;
        parts.push(trapsHtml);
      }
    } else {
      const traps = getNoteBySlug(sense, langMode === 'zh' ? 'zh' : 'en', 'learner-traps');
      if (traps) {
        parts.push(`<div class="wd-traps">
          <div class="wd-traps-title">${langText('Learner Traps', '學習陷阱')}</div>
          <div class="wd-traps-text">${segmentedHTML(traps, WORD)}</div>
        </div>`);
      }
    }
  }

  // Collocations
  if (isSectionVisible('collocations') && sense.collocations && sense.collocations.length) {
    const chips = sense.collocations.map(c => {
      return `<span class="wd-colloc-item">${segmentedHTML(c.text, WORD)}</span>`;
    }).join('<span class="wd-colloc-sep">·</span>');
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
    <span class="wd-rel-card-body">
      ${r.posAbbr ? `<span class="wd-rel-card-pos">${r.posAbbr}</span>` : ''}
      <span class="wd-rel-card-def">${r.def || ''}</span>
      <span class="wd-rel-card-pinyin">${formatPinyin(r.pinyin)}</span>
      ${tocflLabel ? `<span class="wd-rel-card-tocfl">${tocflLabel}</span>` : ''}
    </span>
  </a>`;
}

// ── RENDER: WORKSHOP ──
function renderWorkshop(sense, idx) {
  const wordKey = WORD.traditional;
  const sensePOS = getAllDefs(sense.definitions)[0]?.pos || '';
  const sensePosAbbr = sensePOS ? (POS_ABBR[sensePOS] || sensePOS) : '';
  const allPOS = [...new Set(getAllDefs(sense.definitions).map(d => d.pos).filter(Boolean))];

  // Build default examples HTML
  const defaultExHTML = (sense.examples || []).filter(ex => !ex.isSuppressed).map((ex, i) => {
    return renderExSentence(ex, {
      pos: sensePosAbbr,
      vertical: textDir === 'vertical',
      segFn: segmentedHTML,
    });
  }).join('') || `<div class="wd-stub">${langText('No examples yet.', '尚無例句。')}</div>`;

  // Build workshop word data object that matches what shared partial expects
  const wordData = {
    traditional: WORD.traditional,
    simplified: WORD.simplified || '',
    pinyin: sense.pinyin || WORD.pinyin || '',
    definition: getSenseDefs(sense.definitions)[0]?.def || '',
    register: sense.register || WORD.register || '',
    connotation: sense.connotation || WORD.connotation || '',
    channel: sense.channel || WORD.channel || '',
    level: sense.tocfl || WORD.tocfl || '',
    formula: getFormula(sense),
    senseId: sense.id,
    senseIds: [sense.id],
    wordObjectId: WORD.wordObjectId,
    definitions: getSenseDefs(sense.definitions),
  };

  // Use sense-specific key for IWP to avoid ID collisions with multi-sense words
  const panelKey = WORD.traditional + '_s' + sense.id;
  return wsRenderPanel(panelKey, wordData, {
    isIWP: true,
    allPOS: allPOS,
    defaultExamplesHTML: defaultExHTML,
  });
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

// ── STROKE SECTION (Hanzi Writer) ──
// Lazy-loads the Hanzi Writer CDN script on first use. Per-character data
// fetched on demand from jsDelivr (~2–10 KB per char). Each character in the
// word becomes a tap-to-animate 120x120 box. Layout respects text orientation:
// horizontal → row, vertical → column (wd-vertical class on #wdMain).
const HANZI_WRITER_CDN = 'https://cdn.jsdelivr.net/npm/hanzi-writer@3.7/dist/hanzi-writer.min.js';
let hanziWriterLoading = null; // Promise cache — ensures the script loads exactly once
let strokeWriters = {};         // map: char + index → HanziWriter instance

function renderStrokeContent() {
  const chars = [...(WORD.traditional || '')];
  if (!chars.length) {
    return `<div class="wd-section-stub">${langText('No characters to animate', '沒有可動畫的字')}</div>`;
  }
  const cells = chars.map((c, i) =>
    `<div class="wd-stroke-char" data-char="${c}" data-idx="${i}" id="wdStroke-${i}" onclick="wdAnimateChar(${i})" title="${langText('Tap to animate', '點擊播放')}"></div>`
  ).join('');
  return `
    <div class="wd-stroke-chars">${cells}</div>
    <div class="wd-stroke-controls">
      <button class="wd-stroke-btn" onclick="wdAnimateAll()">▶ ${langText('Animate All', '全部播放')}</button>
      <button class="wd-stroke-btn" onclick="wdReplayAll()">↻ ${langText('Replay', '重播')}</button>
    </div>
    <div id="wdStrokeStatus" class="wd-stroke-status"></div>
  `;
}

function loadHanziWriter() {
  if (typeof HanziWriter !== 'undefined') return Promise.resolve();
  if (hanziWriterLoading) return hanziWriterLoading;
  hanziWriterLoading = new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = HANZI_WRITER_CDN;
    s.async = true;
    s.onload = () => resolve();
    s.onerror = () => { hanziWriterLoading = null; reject(new Error('hanzi_writer_load_failed')); };
    document.head.appendChild(s);
  });
  return hanziWriterLoading;
}

async function loadStrokeAnimators() {
  const containers = document.querySelectorAll('.wd-stroke-char');
  if (!containers.length) return;
  try {
    await loadHanziWriter();
  } catch (e) {
    const statusEl = document.getElementById('wdStrokeStatus');
    if (statusEl) statusEl.innerHTML = `<div class="wd-community-empty">${langText('Stroke animation unavailable (offline or blocked).', '筆順動畫暫時無法載入。')}</div>`;
    return;
  }
  // Reset any previous writers (happens on re-renders after toggle)
  strokeWriters = {};
  containers.forEach(el => {
    const char = el.getAttribute('data-char');
    const idx  = el.getAttribute('data-idx');
    if (!char) return;
    // Clear any prior render (writers leave SVG children behind)
    el.innerHTML = '';
    try {
      const writer = HanziWriter.create(el, char, {
        width: 120,
        height: 120,
        padding: 5,
        strokeAnimationSpeed: 1,
        delayBetweenStrokes: 100,
        showCharacter: true,
        showOutline: true,
        strokeColor: '#1a1a1a',
        outlineColor: '#d8d8d8',
        radicalColor: '#7060a8',
        drawingColor: '#7060a8',
      });
      strokeWriters[idx] = writer;
    } catch (e) {
      // Character not in dataset — show as static text fallback
      el.textContent = char;
      el.classList.add('wd-stroke-char-missing');
    }
  });
}

function wdAnimateChar(idx) {
  const w = strokeWriters[idx];
  if (w) { try { w.animateCharacter(); } catch (e) {} }
}

function wdAnimateAll() {
  const keys = Object.keys(strokeWriters).sort((a, b) => Number(a) - Number(b));
  let i = 0;
  const next = () => {
    if (i >= keys.length) return;
    const w = strokeWriters[keys[i]];
    i++;
    if (!w) return next();
    try {
      w.animateCharacter({ onComplete: next });
    } catch (e) { next(); }
  };
  next();
}

function wdReplayAll() {
  Object.values(strokeWriters).forEach(w => {
    try { w.animateCharacter(); } catch (e) {}
  });
}

// ── COMMUNITY SECTION ──
// Dropdown-driven views backed by the shared writing-card renderer
// (_writing-card-js.blade.php). Views: writings · my-writings · disputes ·
// affirmed-senses · trust. Each view fetches from a single endpoint with a
// `view` query param. Last-selected view persists via localStorage.

const COMMUNITY_VIEWS = ['writings', 'my-writings', 'disputes', 'affirmed-senses', 'trust'];
const COMMUNITY_PAGE  = 3;

let communityCurrentView = localStorage.getItem('lwpCommunityView') || 'writings';
let communityOffset     = 0; // offset within the current view, for paginated writings
let communityLastData   = null; // last payload, used for "See more" append logic

function renderCommunityContent() {
  const options = [
    { value: 'writings',         en: 'Public Writings',  zh: '公開寫作' },
    { value: 'my-writings',      en: 'Your Writings',    zh: '我的寫作', authOnly: true },
    { value: 'disputes',         en: 'Active Disputes',  zh: '討論中' },
    { value: 'affirmed-senses',  en: 'Most Affirmed',    zh: '最多贊同' },
    { value: 'trust',            en: 'Trust Signals',    zh: '信任訊號' },
  ];
  const isAuthed = !!window.__AUTH;
  // Honor auth gating: drop 'my-writings' for unauth visitors. If their
  // last-selected view was 'my-writings', fall back to 'writings'.
  const visible = options.filter(o => !o.authOnly || isAuthed);
  if (!isAuthed && communityCurrentView === 'my-writings') communityCurrentView = 'writings';

  const optsHtml = visible.map(o => {
    const label = langMode === 'zh' ? o.zh : langMode === 'both' ? `${o.en} · ${o.zh}` : o.en;
    return `<option value="${o.value}"${o.value === communityCurrentView ? ' selected' : ''}>${label}</option>`;
  }).join('');

  return `
    <div class="wd-community-header">
      <select id="wdCommunityView" class="wd-community-select" onchange="wdCommunitySetView(this.value)">${optsHtml}</select>
      <button class="wd-refresh-btn" onclick="wdRefreshCommunity()" title="${langText('Refresh', '刷新')}">↻</button>
    </div>
    <div id="wdCommunityTrustStrip"></div>
    <div id="wdCommunityContent"></div>
    <div id="wdCommunityActions" class="wd-community-actions"></div>
  `;
}

function wdCommunitySetView(view) {
  if (!COMMUNITY_VIEWS.includes(view)) view = 'writings';
  communityCurrentView = view;
  localStorage.setItem('lwpCommunityView', view);
  communityOffset = 0;
  communityLastData = null;
  loadCommunityPanel({ force: true });
}

function wdRefreshCommunity() {
  communityOffset = 0;
  communityLastData = null;
  loadCommunityPanel({ force: true });
}

async function loadCommunityPanel({ append = false, force = false } = {}) {
  if (!WORD.wordObjectId) return;
  const view = communityCurrentView;
  const offset = append ? communityOffset : 0;
  const limit  = (view === 'writings') ? COMMUNITY_PAGE : 10;

  try {
    const url = `/api/community/word/${WORD.wordObjectId}?view=${encodeURIComponent(view)}&offset=${offset}&limit=${limit}`;
    const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!resp.ok) { renderCommunityError(); return; }
    const data = await resp.json();
    communityLastData = data;

    renderCommunityTrustStrip(data.trust);
    renderCommunityForView(view, data, { append });
    renderCommunityActions(view, data);
  } catch (e) {
    renderCommunityError();
  }
}

function renderCommunityError() {
  const el = document.getElementById('wdCommunityContent');
  if (!el) return;
  el.innerHTML = `<div class="wd-community-empty">${langText('Unable to load community content right now.', '社群內容暫時無法載入。')}</div>`;
}

function renderCommunityTrustStrip(trust) {
  const el = document.getElementById('wdCommunityTrustStrip');
  if (!el || !trust) return;
  const noContent = (trust.affirmTotal === 0 && trust.disputeTotal === 0 && (trust.publicWritingCount || 0) === 0 && (trust.myWritingCount || 0) === 0);
  if (noContent) { el.innerHTML = ''; return; }
  const parts = [];
  parts.push(`<span>👍 <span class="wd-trust-num">${trust.affirmTotal}</span> ${langText('affirmations', '贊同')}</span>`);
  parts.push(`<span class="wd-trust-dot">·</span>`);
  parts.push(`<span>👎 <span class="wd-trust-num">${trust.disputeTotal}</span> ${langText('active disputes', '討論中')}</span>`);
  parts.push(`<span class="wd-trust-dot">·</span>`);
  parts.push(`<span>✎ <span class="wd-trust-num">${trust.publicWritingCount || 0}</span> ${langText('public writings', '公開寫作')}</span>`);
  if (trust.senseCount > 0) {
    parts.push(`<span class="wd-trust-dot">·</span>`);
    parts.push(`<span>${langText(`${trust.senseCount} sense${trust.senseCount === 1 ? '' : 's'}`, `${trust.senseCount} 個義項`)}</span>`);
  }
  el.innerHTML = `<div class="wd-community-trust-strip">${parts.join('')}</div>`;
}

function renderCommunityForView(view, data, { append = false } = {}) {
  const el = document.getElementById('wdCommunityContent');
  if (!el) return;

  if (view === 'writings') {
    const items = (data.data && data.data.items) || [];
    communityOffset = (append ? communityOffset : 0) + items.length;
    if (!append) {
      if (!items.length) {
        el.innerHTML = `<div class="wd-community-empty">${langText(
          'No public writings yet — be the first to share one in the Writing Conservatory.',
          '尚無公開寫作 — 在寫作院分享第一篇。'
        )}</div>`;
        return;
      }
      el.innerHTML = `<div class="ex-sentences">${items.map(w => renderWritingCard(w, { showAuthor: true, showDate: true, showVisibility: true })).join('')}</div>`;
    } else {
      const list = el.querySelector('.ex-sentences');
      if (!list) return;
      list.insertAdjacentHTML('beforeend', items.map(w => renderWritingCard(w, { showAuthor: true, showDate: true, showVisibility: true })).join(''));
    }
    return;
  }

  if (view === 'my-writings') {
    const items = (data.data && data.data.items) || [];
    if (!window.__AUTH) {
      el.innerHTML = `<div class="wd-community-empty">${langText('Sign in to see your writings for this word.', '登入以查看您的寫作。')}</div>`;
      return;
    }
    if (!items.length) {
      el.innerHTML = `<div class="wd-community-empty">${langText(
        'You have not written about this word yet — try the Writing Conservatory.',
        '您還沒寫過這個詞 — 在寫作院試試。'
      )}</div>`;
      return;
    }
    el.innerHTML = `<div class="ex-sentences">${items.map(w => renderWritingCard(w, { showAuthor: false, showDate: true, showVisibility: true })).join('')}</div>`;
    return;
  }

  if (view === 'disputes') {
    const items = (data.data && data.data.items) || [];
    if (!items.length) {
      el.innerHTML = `<div class="wd-community-empty">${langText('No active disputes on this word.', '此詞目前沒有討論中的爭議。')}</div>`;
      return;
    }
    el.innerHTML = `<div class="wd-dispute-list">${items.map(renderDisputeCard).join('')}</div>`;
    return;
  }

  if (view === 'affirmed-senses') {
    const items = (data.data && data.data.items) || [];
    if (!items.length) {
      el.innerHTML = `<div class="wd-community-empty">${langText('No senses of this word have been affirmed yet.', '此詞的義項尚未有人贊同。')}</div>`;
      return;
    }
    el.innerHTML = `<div class="wd-affirmed-list">${items.map(renderAffirmedRow).join('')}</div>`;
    return;
  }

  if (view === 'trust') {
    const t = data.trust || {};
    el.innerHTML = `
      <div class="wd-trust-detail">
        <div class="wd-trust-detail-row"><span class="wd-trust-detail-num">${t.affirmTotal || 0}</span> ${langText('total affirmations across all senses of this word', '此詞所有義項的贊同總數')}</div>
        <div class="wd-trust-detail-row"><span class="wd-trust-detail-num">${t.disputeTotal || 0}</span> ${langText('active disputes (pending or under review)', '討論中的爭議（待處理或審核中）')}</div>
        <div class="wd-trust-detail-row"><span class="wd-trust-detail-num">${t.publicWritingCount || 0}</span> ${langText('public writings from the community', '社群公開寫作')}</div>
        ${window.__AUTH ? `<div class="wd-trust-detail-row"><span class="wd-trust-detail-num">${t.myWritingCount || 0}</span> ${langText('your writings on this word', '您對此詞的寫作')}</div>` : ''}
        <div class="wd-trust-detail-row"><span class="wd-trust-detail-num">${t.senseCount || 0}</span> ${langText('senses tracked', '已收錄義項')}</div>
      </div>
    `;
    return;
  }
}

function renderCommunityActions(view, data) {
  const el = document.getElementById('wdCommunityActions');
  if (!el) return;
  const parts = [];
  if (view === 'writings' && data.data && data.data.hasMore) {
    parts.push(`<button class="wd-see-more" onclick="wdLoadMoreCommunity()">${langText('See more', '看更多')} ↓</button>`);
  }
  el.innerHTML = parts.join('');
}

function wdLoadMoreCommunity() { loadCommunityPanel({ append: true }); }

// ── Disputes renderer (distinct shape from writings) ──
function renderDisputeCard(d) {
  const statusLabel = d.status === 'under_review'
    ? langText('under review', '審核中')
    : langText('pending', '待處理');
  const senseLine = [
    d.sensePosAbbr ? `<span class="ex-sent-pos">${d.sensePosAbbr}</span>` : '',
    d.sensePinyin ? `<span style="font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--accent)">${formatPinyin(d.sensePinyin)}</span>` : '',
  ].filter(Boolean).join(' ');
  const fieldsHtml = (d.fieldsDisputed || []).map(f =>
    `<span class="wd-dispute-field-chip">${f}</span>`
  ).join('');
  return `
    <div class="wd-dispute-card">
      <div class="wd-dispute-head">
        ${senseLine}
        <span class="wd-dispute-status ${d.status}">${statusLabel}</span>
        <span>— ${d.disputer}</span>
        <span>·</span>
        <span>${d.createdAt || ''}</span>
      </div>
      ${d.senseDefinition ? `<div class="wd-dispute-sense">${d.senseDefinition}</div>` : ''}
      ${d.rationale ? `<div class="wd-dispute-rationale">${d.rationale}</div>` : ''}
      ${fieldsHtml ? `<div class="wd-dispute-fields">${fieldsHtml}</div>` : ''}
    </div>
  `;
}

// ── Affirmed-senses row renderer ──
function renderAffirmedRow(s) {
  const posChip = s.posAbbr ? `<span class="wd-affirmed-pos">${s.posAbbr}</span>` : '';
  const pinyin = s.pinyin ? `<span style="font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--accent);margin-right:0.4rem">${formatPinyin(s.pinyin)}</span>` : '';
  const mine = s.affirmedByMe ? `<span class="wd-affirmed-me" title="${langText('You affirmed this', '您已贊同')}">★</span>` : '';
  return `
    <div class="wd-affirmed-row">
      <span class="wd-affirmed-count">${s.affirmCount}</span>
      <div class="wd-affirmed-body">
        ${posChip}${pinyin}<span class="wd-affirmed-def">${s.definition}</span>
      </div>
      ${mine}
    </div>
  `;
}

// ── MAIN RENDER ──
function renderPage() {
  renderHeader();

  const sections = [];

  // Section 1: Core (always visible — senses, filtered by alignment preference)
  const visibleSenses = (WORD.senses || []).filter(s => wdAlignmentVisible(s.alignment));
  visibleSenses.forEach((sense, i) => {
    sections.push(renderSense(sense, i, visibleSenses.length));
  });

  // Section 2: Stroke (async-loaded Hanzi Writer animators)
  sections.push(renderSection('stroke', renderStrokeContent()));

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

  // Section 6: Community (async-loaded placeholders, filled by loadCommunityPanel)
  sections.push(renderSection('community', renderCommunityContent()));

  // Actions — now in hero area

  document.getElementById('wdMain').innerHTML = sections.filter(Boolean).join('');

  // Apply single/multi-sense body class
  const senseCount = (WORD.senses || []).length;
  // Single/multi-sense now use the same layout — no body class needed

  // Apply pinyin mode
  document.body.classList.toggle('wd-no-pinyin', pinyinMode === 'off');
  // Apply text dir
  document.getElementById('wdMain').classList.toggle('wd-vertical', textDir === 'vertical');
  // Re-populate async related-words placeholders (cached after first fetch)
  loadRelatedWords();
  // Re-populate async community panel (uses cached state, fetches when cache is null)
  loadCommunityPanel();
  // Initialize Hanzi Writer animators for Stroke section (lazy-loads CDN on first call)
  loadStrokeAnimators();
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
  if (verbPresentation === 'intricate') {
    document.getElementById('wdBtnVerbConsolidated').classList.remove('active');
    document.getElementById('wdBtnVerbIntricate').classList.add('active');
  }
  if (pinyinDisplay === 'numeric') {
    document.getElementById('wdBtnPinyinAccented').classList.remove('active');
    document.getElementById('wdBtnPinyinNumeric').classList.add('active');
  }

  // POS Alignment filter
  const wdAlignPartialEl = document.getElementById('wdAlignShowPartial');
  const wdAlignDisputedEl = document.getElementById('wdAlignShowDisputed');
  if (wdAlignPartialEl) wdAlignPartialEl.checked = wdAlignShowPartial;
  if (wdAlignDisputedEl) wdAlignDisputedEl.checked = wdAlignShowDisputed;

  applyLevelFonts(currentLevel);
  renderSectionToggles();
  renderPage();

  // Hydrate workshop saved deck from DB
  wsHydrateSavedDeck();
  Object.keys(wsSavedDeck).forEach(key => wsRefreshDeck(key));
  wsRestorePending();

  // Load related words asynchronously (once)
  loadRelatedWords();

  // Community panel: listen for writing-save + affirmation-change events so
  // the section auto-refreshes without a reload. Both events reset offset
  // to reflect the new state.
  window.addEventListener('hn:writing-saved', e => {
    const targetWordId = e.detail?.wordObjectId;
    if (!targetWordId || targetWordId === WORD.wordObjectId) {
      communityOffset = 0;
      communityLastData = null;
      loadCommunityPanel({ force: true });
    }
  });
  window.addEventListener('hn:affirmation-changed', () => {
    communityOffset = 0;
    communityLastData = null;
    loadCommunityPanel({ force: true });
  });

  // Init pills after render
  requestAnimationFrame(() => {
    ['wdScriptToggle','wdLangToggle','wdIconsToggle','wdPinyinToggle','wdPinyinDisplayToggle','wdTextDirToggle','wdViewModeToggle','wdVerbPresentationToggle'].forEach(wdUpdatePill);
  });

  // Back to top
  const btt = document.getElementById('wdBackToTop');
  window.addEventListener('scroll', function() {
    btt.classList.toggle('visible', window.scrollY > 300);
  }, { passive: true });
});
</script>
@include('partials.lexicon._preference-sync')
@include('partials.lexicon._site-footer')
</body>
</html>
