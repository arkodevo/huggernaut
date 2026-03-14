<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $word['traditional'] }} — 流動 Living Lexicon</title>
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

body::before {
  content: '';
  position: fixed; inset: 0;
  background:
    radial-gradient(ellipse 60% 40% at 15% 20%, rgba(98,64,200,0.05) 0%, transparent 70%),
    radial-gradient(ellipse 50% 60% at 85% 80%, rgba(26,138,90,0.04) 0%, transparent 70%);
  pointer-events: none; z-index: 0;
}

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
.wd-header-char {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0.2rem 0;
}
.wd-hero-char {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 2.4rem; font-weight: 300;
  color: var(--ink); line-height: 1.1;
}
.wd-hero-pinyin {
  font-style: italic; font-family: 'Cormorant Garamond', serif;
  font-size: 1.1rem; color: var(--accent);
  letter-spacing: 0.05em;
}
.wd-hero-domain {
  display: inline-block;
  font-family: 'DM Mono', monospace;
  font-size: 0.72rem; letter-spacing: 0.04em;
  color: var(--gold); background: rgba(160,114,10,0.08);
  border: 1px solid rgba(160,114,10,0.28);
  border-radius: 2px; padding: 0.1rem 0.5rem;
  cursor: pointer; user-select: none;
  transition: background 0.15s;
}
.wd-hero-domain:hover { background: rgba(160,114,10,0.15); }
.wd-hero-right {
  display: flex; flex-direction: column; gap: 0.2rem;
}

/* ── SETTINGS BAR ── */
.wd-settings-bar {
  display: flex; flex-wrap: wrap; gap: 0.5rem 0.8rem;
  padding: 0.6rem 1rem;
  background: #eae8f2;
  border-bottom: 1px solid var(--border);
  align-items: center;
}
.wd-setting-group {
  display: flex; align-items: center; gap: 0.3rem;
}
.wd-setting-label {
  font-size: 0.55rem; letter-spacing: 0.15em; text-transform: uppercase;
  color: var(--dim); white-space: nowrap;
}
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
  display: flex; align-items: center; flex-wrap: wrap; gap: 0.4rem;
}
.wd-sense-badge {
  display: inline-flex; align-items: center; justify-content: center;
  width: 1.6rem; height: 1.6rem;
  font-size: 0.72rem; font-weight: 500;
  color: white; background: var(--accent);
  border-radius: 50%;
  flex-shrink: 0;
}
.wd-sense-pinyin {
  font-style: italic; font-family: 'Cormorant Garamond', serif;
  font-size: 1rem; color: var(--accent);
  letter-spacing: 0.05em;
}
.wd-sense-domain {
  display: inline-block;
  font-family: 'DM Mono', monospace;
  font-size: 0.68rem; letter-spacing: 0.04em;
  color: var(--gold); background: rgba(160,114,10,0.08);
  border: 1px solid rgba(160,114,10,0.28);
  border-radius: 2px; padding: 0.1rem 0.45rem;
  cursor: pointer; user-select: none;
}
.wd-sense-domain:hover { background: rgba(160,114,10,0.15); }
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
.wd-def-row { display: block; }
.wd-def-row + .wd-def-row { margin-top: 0.3rem; }
.wd-pos {
  display: inline-block;
  margin-right: 0.4rem; vertical-align: baseline;
  font-family: 'DM Mono', monospace;
  font-size: 0.78rem; letter-spacing: 0.04em;
  color: #7060a8; background: rgba(98,64,200,0.07);
  border: 1px solid rgba(98,64,200,0.18);
  border-radius: 2px; padding: 1px 7px;
  cursor: pointer; user-select: none;
  transition: background 0.15s, border-color 0.15s;
}
.wd-pos:hover { background: rgba(98,64,200,0.13); border-color: rgba(98,64,200,0.35); }
.wd-definition {
  font-family: 'Cormorant Garamond', serif;
  font-size: var(--fs-defn, 1.5rem); font-weight: 300;
  color: var(--ink); line-height: 1.4;
}
.wd-formula {
  font-size: var(--fs-formula, 1rem);
  background: rgba(98,64,200,0.05);
  border: 1px solid rgba(98,64,200,0.15);
  padding: 0.3rem 0.6rem; border-radius: 2px;
  color: var(--accent);
  font-family: 'DM Mono', monospace;
  display: inline-block; margin-top: 0.15rem;
}
.wd-usage-note {
  font-size: var(--fs-note, 0.9rem); color: var(--dim); line-height: 1.5;
  margin-top: 0.1rem;
}

/* Attribute chips — reuse card-attr pattern */
.wd-attrs {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.4rem;
  padding-top: 0.5rem;
  border-top: 1px solid var(--border);
}
.card-attr {
  display: flex; flex-direction: column;
  border-radius: 3px; overflow: hidden;
  border: 1px solid var(--border);
  cursor: pointer; user-select: none;
}
.card-attr-header {
  font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase;
  font-family: 'DM Mono', monospace;
  padding: 0.22rem 0.5rem 0.18rem;
  border-bottom: 1px solid var(--border);
  white-space: nowrap;
}
.card-attr-value {
  display: flex; flex-direction: row; align-items: center; gap: 0.35rem;
  padding: 0.25rem 0.5rem;
  font-family: 'DM Mono', monospace; font-size: 0.78rem;
}
.card-attr-value .attr-icon { font-size: 1.05rem; line-height: 1; flex-shrink: 0; }
.card-attr-value.multi { flex-direction: column; align-items: flex-start; gap: 0.2rem; }
.attr-val-item { display: inline-flex; align-items: center; gap: 0.35rem; flex-shrink: 0; white-space: nowrap; }
.card-attr:hover .card-attr-header { opacity: 0.72; }
.card-attr:hover .attr-label { opacity: 0.72; }

/* Attribute colour tints */
.card-attr.attr-register   { background: rgba(20,140,80,0.05);  border-color: rgba(20,140,80,0.2); }
.card-attr.attr-register   .card-attr-header { color: #148c50; background: rgba(20,140,80,0.08); border-color: rgba(20,140,80,0.15); }
.card-attr.attr-register   .card-attr-value  { color: #148c50; }

.card-attr.attr-connotation.conno-pos .card-attr-header { color: #8a6000; background: rgba(232,160,32,0.1); border-color: rgba(232,160,32,0.2); }
.card-attr.attr-connotation.conno-pos { background: rgba(232,160,32,0.07); border-color: rgba(232,160,32,0.3); }
.card-attr.attr-connotation.conno-pos .card-attr-value  { color: #8a6000; }
.card-attr.attr-connotation.conno-neg { background: rgba(80,96,160,0.07); border-color: rgba(80,96,160,0.3); }
.card-attr.attr-connotation.conno-neg .card-attr-header { color: #3a4880; background: rgba(80,96,160,0.1); border-color: rgba(80,96,160,0.2); }
.card-attr.attr-connotation.conno-neg .card-attr-value  { color: #3a4880; }
.card-attr.attr-connotation.conno-neu { background: rgba(112,144,176,0.07); border-color: rgba(112,144,176,0.3); }
.card-attr.attr-connotation.conno-neu .card-attr-header { color: #4a6880; background: rgba(112,144,176,0.1); border-color: rgba(112,144,176,0.2); }
.card-attr.attr-connotation.conno-neu .card-attr-value  { color: #4a6880; }
.card-attr.attr-connotation.conno-ctx { background: rgba(96,160,112,0.07); border-color: rgba(96,160,112,0.3); }
.card-attr.attr-connotation.conno-ctx .card-attr-header { color: #3a7850; background: rgba(96,160,112,0.1); border-color: rgba(96,160,112,0.2); }
.card-attr.attr-connotation.conno-ctx .card-attr-value  { color: #3a7850; }

.card-attr.attr-channel   { background: rgba(160,114,10,0.05); border-color: rgba(160,114,10,0.2); }
.card-attr.attr-channel   .card-attr-header { color: var(--gold); background: rgba(160,114,10,0.08); border-color: rgba(160,114,10,0.15); }
.card-attr.attr-channel   .card-attr-value  { color: var(--gold); }

.card-attr.attr-tocfl     { background: rgba(160,114,10,0.05); border-color: rgba(160,114,10,0.2); }
.card-attr.attr-tocfl     .card-attr-header { color: var(--gold); background: rgba(160,114,10,0.08); border-color: rgba(160,114,10,0.15); }
.card-attr.attr-tocfl     .card-attr-value  { color: var(--gold); }

.card-attr.attr-dimension { background: rgba(60,80,180,0.05); border-color: rgba(60,80,180,0.2); }
.card-attr.attr-dimension .card-attr-header { color: #3c50b4; background: rgba(60,80,180,0.08); border-color: rgba(60,80,180,0.15); }
.card-attr.attr-dimension .card-attr-value  { color: #3c50b4; }

.card-attr.attr-intensity { background: rgba(180,60,120,0.05); border-color: rgba(180,60,120,0.2); }
.card-attr.attr-intensity .card-attr-header { color: #a03070; background: rgba(180,60,120,0.08); border-color: rgba(180,60,120,0.15); }
.card-attr.attr-intensity .card-attr-value  { color: #a03070; }

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
.wd-example {
  display: flex; align-items: flex-start; gap: 0.5rem;
  padding: 0.45rem 0.6rem;
  background: rgba(255,255,255,0.7);
  border: 1px solid rgba(98,64,200,0.08);
  border-radius: 2px;
}
.wd-ex-num { font-size: 0.55rem; color: var(--accent); opacity: 0.6; margin-top: 0.15rem; flex-shrink: 0; }
.wd-ex-body { display: flex; flex-direction: column; gap: 0.15rem; flex: 1; min-width: 0; }
.wd-ex-cn {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: var(--fs-ex-cn, 1.8rem); color: var(--ink); line-height: 1.5;
}
.wd-ex-cn .highlight { color: var(--gold); font-weight: 600; }
.wd-ex-en { font-size: var(--fs-ex-en, 1rem); color: var(--dim); font-style: italic; }
.wd-ex-source { font-size: 0.55rem; color: var(--dim); opacity: 0.6; }
.wd-ex-theme {
  display: inline-block;
  font-size: 0.55rem; color: var(--jade);
  background: rgba(26,138,90,0.06);
  border: 1px solid rgba(26,138,90,0.15);
  border-radius: 2px; padding: 0 0.3rem;
  margin-top: 0.1rem;
}

/* Segmented word spans */
.wd-seg-known {
  cursor: pointer;
  border-bottom: 1px dashed transparent;
  transition: border-color 0.15s, color 0.15s;
  position: relative;
}
.wd-seg-known:hover { border-color: var(--accent); color: var(--accent); }

/* Vertical text mode */
.wd-vertical .wd-ex-cn {
  writing-mode: vertical-rl;
  text-orientation: mixed;
  letter-spacing: 0.08em;
  max-height: 20rem;
}
.wd-vertical .wd-examples {
  flex-direction: row-reverse;
  overflow-x: auto;
  gap: 0.75rem;
}
.wd-vertical .wd-example {
  flex-direction: column;
  min-width: auto;
}

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

/* ── FAMILY TREE ── */
.wd-family {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 2px;
  overflow: hidden;
}
.wd-family-toggle {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0.65rem 1rem;
  cursor: pointer; background: transparent; border: none;
  width: 100%; text-align: left;
  font-family: 'DM Mono', monospace;
  font-size: 0.72rem; letter-spacing: 0.08em;
  color: var(--dim);
  transition: color 0.15s, background 0.15s;
}
.wd-family-toggle:hover { color: var(--text); background: rgba(98,64,200,0.04); }
.wd-family-arrow {
  font-size: 0.65rem; transition: transform 0.2s; display: inline-block;
}
.wd-family-body {
  display: none;
  padding: 0 1rem 1rem;
  flex-direction: column; gap: 0.6rem;
}
.wd-family-body.open { display: flex; }
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

/* ── PHASE 2 STUBS ── */
.wd-phase-stub {
  background: var(--surface);
  border: 1px dashed var(--border);
  border-radius: 2px;
  padding: 1.2rem 1rem;
  text-align: center;
}
.wd-phase-stub-title {
  font-size: 0.55rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--dim); margin-bottom: 0.3rem;
}
.wd-phase-stub-text {
  font-family: 'Cormorant Garamond', serif;
  font-size: 0.95rem; color: var(--dim); font-style: italic;
}

/* ── POPOVER ── */
.wd-popover {
  position: fixed; z-index: 500;
  background: white;
  border: 1px solid var(--border-active);
  border-radius: 4px;
  box-shadow: 0 8px 28px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.08);
  padding: 0.6rem 0.8rem;
  min-width: 180px; max-width: 280px;
  display: none;
  flex-direction: column; gap: 0.2rem;
  animation: wdPopIn 0.15s ease;
}
.wd-popover.open { display: flex; }
@keyframes wdPopIn {
  from { opacity: 0; transform: translateY(-4px); }
  to   { opacity: 1; transform: translateY(0); }
}
.wd-popover-char {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 1.4rem; font-weight: 300; color: var(--ink); line-height: 1.2;
}
.wd-popover-pinyin {
  font-style: italic; font-family: 'Cormorant Garamond', serif;
  font-size: 0.82rem; color: var(--accent);
}
.wd-popover-pos {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: #7060a8;
}
.wd-popover-def {
  font-family: 'Cormorant Garamond', serif;
  font-size: 0.85rem; color: var(--text); line-height: 1.3;
}
.wd-popover-link {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--accent); text-decoration: none;
  margin-top: 0.15rem;
}
.wd-popover-link:hover { text-decoration: underline; }

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
.wd-no-pinyin .wd-hero-pinyin,
.wd-no-pinyin .wd-sense-pinyin,
.wd-no-pinyin .wd-rel-card-pinyin,
.wd-no-pinyin .wd-popover-pinyin { display: none; }

/* ── RESPONSIVE ── */
@media (min-width: 768px) {
  .wd-main { padding: 1.5rem 2rem; }
  .wd-attrs { grid-template-columns: repeat(3, 1fr); }
}
</style>
</head>
<body>

<!-- Popover (singleton) -->
<div class="wd-popover" id="wdPopover">
  <div class="wd-popover-char" id="wdPopChar"></div>
  <div class="wd-popover-pinyin" id="wdPopPinyin"></div>
  <div class="wd-popover-pos" id="wdPopPos"></div>
  <div class="wd-popover-def" id="wdPopDef"></div>
  <a class="wd-popover-link" id="wdPopLink" href="#">Open &rarr;</a>
</div>

<!-- Sticky Header -->
<div class="wd-header" id="wdHeader">
  <div class="wd-header-top">
    <button class="wd-back-btn" onclick="goBack()">&larr; Back</button>
    <div class="wd-breadcrumb" id="wdBreadcrumb"></div>
  </div>
  <div class="wd-header-char">
    <span class="wd-hero-char" id="wdHeroChar"></span>
    <div class="wd-hero-right">
      <span class="wd-hero-pinyin" id="wdHeroPinyin"></span>
      <div id="wdHeroDomains"></div>
    </div>
  </div>
</div>

<!-- Settings Bar -->
<div class="wd-settings-bar">
  <div class="wd-setting-group">
    <span class="wd-setting-label">Script</span>
    <div class="wd-toggle" id="wdScriptToggle">
      <button class="wd-toggle-btn active" id="wdBtnTrad" onclick="wdSetScript('traditional')">繁</button>
      <button class="wd-toggle-btn" id="wdBtnSimp" onclick="wdSetScript('simplified')">简</button>
    </div>
  </div>
  <div class="wd-setting-group">
    <span class="wd-setting-label">Lang</span>
    <div class="wd-toggle" id="wdLangToggle">
      <button class="wd-toggle-btn" id="wdBtnLangEn" onclick="wdSetLang('en')">EN</button>
      <button class="wd-toggle-btn" id="wdBtnLangZh" onclick="wdSetLang('zh')">中文</button>
      <button class="wd-toggle-btn active" id="wdBtnLangBoth" onclick="wdSetLang('both')">EN+中文</button>
    </div>
  </div>
  <div class="wd-setting-group">
    <span class="wd-setting-label">Icons</span>
    <div class="wd-toggle" id="wdIconsToggle">
      <button class="wd-toggle-btn active" id="wdBtnIconsOn" onclick="wdSetIcons('on')">On</button>
      <button class="wd-toggle-btn" id="wdBtnIconsOff" onclick="wdSetIcons('off')">Off</button>
    </div>
  </div>
  <div class="wd-setting-group">
    <span class="wd-setting-label">Pinyin</span>
    <div class="wd-toggle" id="wdPinyinToggle">
      <button class="wd-toggle-btn active" id="wdBtnPinyinOn" onclick="wdSetPinyin('on')">On</button>
      <button class="wd-toggle-btn" id="wdBtnPinyinOff" onclick="wdSetPinyin('off')">Off</button>
    </div>
  </div>
  <div class="wd-setting-group">
    <span class="wd-setting-label">Text</span>
    <div class="wd-toggle" id="wdTextDirToggle">
      <button class="wd-toggle-btn active" id="wdBtnHoriz" onclick="wdSetTextDir('horizontal')">橫</button>
      <button class="wd-toggle-btn" id="wdBtnVert" onclick="wdSetTextDir('vertical')">直</button>
    </div>
  </div>
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

const POS_RENAME = {
  'Verb': 'Verb (all)', 'Intransitive Verb': 'Verb - intransitive',
  'Process Verb': 'Verb - process', 'Vp-sep / Separable Process Verb': 'Verb - process (sep.)',
  'Process Verb (Telic)': 'Verb - process (telic)', 'Stative Verb': 'Verb - stative',
  'Vs-attr / Stative Verb (Attributive)': 'Verb - stative (attr.)',
  'Vs-pred / Stative Verb (Predicative)': 'Verb - stative (pred.)',
  'Vs-sep / Separable Stative Verb': 'Verb - stative (sep.)',
  'State-Transitive Verb': 'Verb - state-transitive',
  'Auxiliary Verb': 'Verb - auxiliary', 'V-sep / Separable Verb': 'Verb - separable',
};

const POS_ABBR = {
  'Verb':'V', 'Intransitive Verb':'Vi', 'Process Verb':'Vp',
  'Vp-sep / Separable Process Verb':'Vp-sep', 'Process Verb (Telic)':'Vpt',
  'Stative Verb':'Vs', 'Vs-attr / Stative Verb (Attributive)':'Vs-attr',
  'Vs-pred / Stative Verb (Predicative)':'Vs-pred',
  'Vs-sep / Separable Stative Verb':'Vs-sep', 'State-Transitive Verb':'Vst',
  'Auxiliary Verb':'Vaux', 'V-sep / Separable Verb':'V-sep',
  'Noun':'N', 'Measure Word':'M', 'Adverb':'Adv', 'Preposition':'Prep',
  'Conjunction':'Conj', 'Particle':'Ptcl', 'Determiner':'Det',
  'Pronoun':'Prn', 'Number':'Num', 'Idiomatic Expression':'IE', 'Phrase':'Ph',
};

const POS_ZH = {
  'Verb':'動詞（全部）', 'Intransitive Verb':'不及物動詞', 'Process Verb':'過程動詞',
  'Vp-sep / Separable Process Verb':'離合過程動詞', 'Process Verb (Telic)':'完結動詞',
  'Stative Verb':'狀態動詞', 'Vs-attr / Stative Verb (Attributive)':'狀態動詞（定語）',
  'Vs-pred / Stative Verb (Predicative)':'狀態動詞（謂語）',
  'Vs-sep / Separable Stative Verb':'離合狀態動詞', 'State-Transitive Verb':'狀態及物動詞',
  'Auxiliary Verb':'助動詞', 'V-sep / Separable Verb':'離合詞',
  'Noun':'名詞', 'Measure Word':'量詞', 'Adverb':'副詞', 'Preposition':'介詞',
  'Conjunction':'連詞', 'Particle':'助詞', 'Determiner':'限定詞',
  'Pronoun':'代詞', 'Number':'數詞', 'Idiomatic Expression':'成語', 'Phrase':'詞組',
};

const ATTR_LABELS = {
  register:    { literary:['🦋','Literary'], formal:['🐝','Formal'], neutral:['🐞','Standard'], colloquial:['🪲','Colloquial'], informal:['🦗','Informal'], slang:['🕷️','Slang'] },
  connotation: { positive:['☀️','Positive'], neutral:['⛅','Neutral'], negative:['⛈️','Negative'], 'context-dependent':['🌦️','Context'] },
  channel:     { 'spoken-only':['🦎','Spoken Only'], 'spoken-dominant':['🐍','Spoken Dominant'], fluid:['🦜','Fluid'], 'written-dominant':['🦚','Written Dominant'], 'written-only':['🐉','Written Only'] },
  dimension:   { abstract:['🐙','Abstract'], concrete:['🐢','Concrete'], internal:['🐟','Internal'], external:['🦂','External'], fluid:['🦀','Fluid'] },
  intensity:   { 1:['🌸','Faint'], 2:['🌼','Mild'], 3:['🪷','Moderate'], 4:['🌻','Strong'], 5:['🌺','Blazing'] },
  tocfl:       { prep:['🌑','Prep'], entry:['🌒','Entry'], basic:['🌓','Basic'], intermediate:['🌔','Intermediate'], advanced:['🌕','Advanced'], high:['🌖','High'], fluency:['🌝','Fluency'] },
};

const ATTR_ZH = {
  register:    { literary:'文學體', formal:'正式', neutral:'標準', colloquial:'口語', informal:'非正式', slang:'俚語' },
  connotation: { positive:'褒義', neutral:'中性', negative:'貶義', 'context-dependent':'隨境' },
  channel:     { 'spoken-only':'純口語', 'spoken-dominant':'偏口語', fluid:'流動', 'written-dominant':'偏書面', 'written-only':'純書面' },
  dimension:   { abstract:'抽象', concrete:'具體', internal:'內在', external:'外在', fluid:'流動' },
  intensity:   { 1:'微', 2:'淡', 3:'中', 4:'濃', 5:'烈' },
  tocfl:       { prep:'準備', entry:'入門', basic:'基礎', advanced:'高階', high:'精通', fluency:'流利' },
};

const ATTR_HEADER_ZH = {
  register: '語域', connotation: '感情色彩', channel: '媒介',
  dimension: '維度', intensity: '強度', tocfl: '華測',
};

const connoClass = { positive: 'conno-pos', neutral: 'conno-neu', negative: 'conno-neg', 'context-dependent': 'conno-ctx' };

const LEVEL_FONTS = {
  beginner:   { hanzi: 3.8, simp: 1.9, pinyin: 1.2, defn: 2.0, note: 1.1, formula: 1.1, exCn: 2.0, exEn: 1.1, scale: 130 },
  learner:    { hanzi: 3.2, simp: 1.6, pinyin: 1.1, defn: 1.9, note: 1.0, formula: 1.0, exCn: 1.9, exEn: 1.0, scale: 115 },
  developing: { hanzi: 2.8, simp: 1.4, pinyin: 1.0, defn: 1.5, note: 0.9, formula: 1.0, exCn: 1.8, exEn: 1.0, scale: 100 },
  advanced:   { hanzi: 2.4, simp: 1.2, pinyin: 0.9, defn: 1.6, note: 0.9, formula: 0.9, exCn: 1.6, exEn: 0.9, scale: 90  },
  native:     { hanzi: 2.0, simp: 1.0, pinyin: 0.8, defn: 1.4, note: 0.85,formula: 0.85,exCn: 1.4, exEn: 0.85,scale: 85  },
};
const FONT_STEPS = [75, 85, 100, 115, 130, 150];

// ── SETTINGS STATE ──
let scriptMode   = localStorage.getItem('scriptMode')   || 'traditional';
let langMode     = localStorage.getItem('langMode')     || 'both';
let iconsMode    = localStorage.getItem('iconsMode')     || 'on';
let pinyinMode   = localStorage.getItem('pinyinMode')    || 'on';
let currentLevel = localStorage.getItem('currentLevel')  || 'developing';
let fontScale    = parseInt(localStorage.getItem('fontScale')) || 100;
let textDir      = 'horizontal';

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
  } else {
    window.location.href = '/lexicon';
  }
}

// Push current word onto trail
pushTrail(SMART_ID, WORD.traditional);

// ── SENTENCE SEGMENTATION ──
function segmentSentence(text) {
  const segments = [];
  let i = 0;
  while (i < text.length) {
    let matched = false;
    for (let len = Math.min(4, text.length - i); len >= 1; len--) {
      const slice = text.substring(i, i + len);
      if (WORD_INDEX[slice]) {
        segments.push({ text: slice, data: WORD_INDEX[slice], known: true });
        i += len;
        matched = true;
        break;
      }
    }
    if (!matched) {
      segments.push({ text: text[i], known: false });
      i++;
    }
  }
  return segments;
}

function segmentedHTML(text) {
  const segs = segmentSentence(text);
  return segs.map(s => {
    if (s.known) {
      const d = s.data;
      return `<span class="wd-seg-known" data-smart-id="${d.smartId}" data-trad="${d.trad || s.text}" data-pinyin="${d.pinyin || ''}" data-pos="${d.pos || ''}" data-def="${(d.def || '').replace(/"/g, '&quot;')}" data-tocfl="${d.tocfl || ''}">${s.text}</span>`;
    }
    return s.text;
  }).join('');
}

// ── POPOVER ──
let popoverTarget = null;
let popoverTapCount = 0;

function showPopover(el) {
  const pop = document.getElementById('wdPopover');
  const rect = el.getBoundingClientRect();
  document.getElementById('wdPopChar').textContent = el.dataset.trad || el.textContent;
  document.getElementById('wdPopPinyin').textContent = el.dataset.pinyin || '';
  document.getElementById('wdPopPos').textContent = el.dataset.pos ? (POS_ABBR[el.dataset.pos] || el.dataset.pos) : '';
  const def = el.dataset.def || '';
  document.getElementById('wdPopDef').textContent = def.length > 40 ? def.substring(0, 40) + '...' : def;
  document.getElementById('wdPopLink').href = '/lexicon/' + el.dataset.smartId;
  // Position
  let top = rect.bottom + 6;
  let left = rect.left;
  if (left + 280 > window.innerWidth) left = window.innerWidth - 290;
  if (left < 4) left = 4;
  if (top + 160 > window.innerHeight) top = rect.top - 160;
  pop.style.top = top + 'px';
  pop.style.left = left + 'px';
  pop.classList.add('open');
}

function hidePopover() {
  document.getElementById('wdPopover').classList.remove('open');
  popoverTarget = null;
  popoverTapCount = 0;
}

document.addEventListener('click', function(e) {
  const seg = e.target.closest('.wd-seg-known');
  if (seg) {
    e.preventDefault();
    e.stopPropagation();
    if (popoverTarget === seg) {
      // Second tap: navigate
      popoverTapCount++;
      if (popoverTapCount >= 2) {
        pushTrail(seg.dataset.smartId, seg.dataset.trad || seg.textContent);
        window.location.href = '/lexicon/' + seg.dataset.smartId;
        return;
      }
    } else {
      popoverTarget = seg;
      popoverTapCount = 1;
      showPopover(seg);
    }
    return;
  }
  if (!e.target.closest('.wd-popover')) {
    hidePopover();
  }
});

// ── ATTRIBUTE CHIP BUILDERS ──
function metaAttrLabel(cat, key) {
  return ATTR_LABELS[cat]?.[key] || ['', String(key)];
}

function cardAttr(cat, key, header, labelPair, extraClass) {
  extraClass = extraClass || '';
  const [icon, label] = labelPair;
  const showIcon  = iconsMode === 'on';
  const showLabel = uiMode !== 'icon-only';
  const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
  const isBoth  = (uiMode === 'all' || uiMode === 'en-zh');
  const zhLabel = ATTR_ZH[cat]?.[key] || label;
  const hdrZh   = ATTR_HEADER_ZH[cat] || header;
  const initLabel = preferred === 'zh' ? zhLabel : isBoth ? `${label} \u00b7 ${zhLabel}` : label;
  const initHdr   = preferred === 'zh' ? hdrZh   : isBoth ? `${header} \u00b7 ${hdrZh}`  : header;
  return `<div class="card-attr attr-${cat} ${extraClass}" onclick="toggleAttrLang(event)">
    <div class="card-attr-header" data-en="${header}" data-zh="${hdrZh}" data-state="${preferred}">${initHdr}</div>
    <div class="card-attr-value">
      ${showIcon && icon ? `<span class="attr-icon">${icon}</span>` : ''}
      ${showLabel ? `<span class="attr-label" data-en="${label}" data-zh="${zhLabel}" data-state="${preferred}">${initLabel}</span>` : ''}
    </div>
  </div>`;
}

function cardAttrMulti(cat, keys, header) {
  const showIcon  = iconsMode === 'on';
  const showLabel = uiMode !== 'icon-only';
  const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
  const isBoth  = (uiMode === 'all' || uiMode === 'en-zh');
  const hdrZh   = ATTR_HEADER_ZH[cat] || header;
  const initHdr = preferred === 'zh' ? hdrZh : isBoth ? `${header} \u00b7 ${hdrZh}` : header;
  const valueHTML = keys.map(k => {
    const [icon, label] = metaAttrLabel(cat, k);
    const zhLabel = ATTR_ZH[cat]?.[k] || label;
    const initLabel = preferred === 'zh' ? zhLabel : isBoth ? `${label} \u00b7 ${zhLabel}` : label;
    return `<span class="attr-val-item">${showIcon && icon ? `<span class="attr-icon">${icon}</span>` : ''}${showLabel ? `<span class="attr-label" data-en="${label}" data-zh="${zhLabel}" data-state="${preferred}">${initLabel}</span>` : ''}</span>`;
  }).join('');
  return `<div class="card-attr attr-${cat}" onclick="toggleAttrLang(event)">
    <div class="card-attr-header" data-en="${header}" data-zh="${hdrZh}" data-state="${preferred}">${initHdr}</div>
    <div class="card-attr-value multi">${valueHTML}</div>
  </div>`;
}

function toggleAttrLang(e) {
  e.stopPropagation();
  e.preventDefault();
  const chip = e.currentTarget;
  const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
  const alt = preferred === 'zh' ? 'en' : 'zh';
  chip.querySelectorAll('[data-en][data-zh]').forEach(el => {
    const current = el.dataset.state || preferred;
    const next = current === preferred ? alt : preferred;
    el.dataset.state = next;
    el.textContent = el.dataset[next] || el.dataset.en;
  });
}

function cyclePosChip(e, chip) {
  e.stopPropagation();
  e.preventDefault();
  const order = ['abbr', 'full', 'zh'];
  const current = chip.dataset.state || 'abbr';
  const next = order[(order.indexOf(current) + 1) % 3];
  chip.dataset.state = next;
  chip.textContent = chip.dataset[next] || chip.dataset.abbr;
}

function toggleLangChip(e, chip) {
  e.stopPropagation();
  const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
  const alt = preferred === 'zh' ? 'en' : 'zh';
  const current = chip.dataset.state || preferred;
  const next = current === preferred ? alt : preferred;
  chip.dataset.state = next;
  chip.textContent = chip.dataset[next] || chip.dataset.en;
}

function posLabel(raw) { return POS_ABBR[raw] || raw; }
function posDisplay(raw) { return POS_RENAME[raw] || raw; }

// ── LEVEL FONTS ──
function applyLevelFonts(level) {
  currentLevel = level;
  const f = LEVEL_FONTS[level];
  if (!f) return;
  const r = document.documentElement;
  r.style.setProperty('--fs-hanzi',   f.hanzi   + 'rem');
  r.style.setProperty('--fs-simp',    f.simp    + 'rem');
  r.style.setProperty('--fs-pinyin',  f.pinyin  + 'rem');
  r.style.setProperty('--fs-defn',    f.defn    + 'rem');
  r.style.setProperty('--fs-note',    f.note    + 'rem');
  r.style.setProperty('--fs-formula', f.formula + 'rem');
  r.style.setProperty('--fs-ex-cn',   f.exCn    + 'rem');
  r.style.setProperty('--fs-ex-en',   f.exEn    + 'rem');
  document.documentElement.style.fontSize = f.scale + '%';
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
  document.getElementById('wdBtnTrad').classList.toggle('active', mode === 'traditional');
  document.getElementById('wdBtnSimp').classList.toggle('active', mode === 'simplified');
  wdUpdatePill('wdScriptToggle');
  renderPage();
}

function wdSetLang(mode) {
  langMode = mode;
  localStorage.setItem('langMode', mode);
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
  document.getElementById('wdBtnIconsOn').classList.toggle('active', mode === 'on');
  document.getElementById('wdBtnIconsOff').classList.toggle('active', mode === 'off');
  uiMode = deriveUiMode();
  wdUpdatePill('wdIconsToggle');
  renderPage();
}

function wdSetPinyin(mode) {
  pinyinMode = mode;
  localStorage.setItem('pinyinMode', mode);
  document.getElementById('wdBtnPinyinOn').classList.toggle('active', mode === 'on');
  document.getElementById('wdBtnPinyinOff').classList.toggle('active', mode === 'off');
  wdUpdatePill('wdPinyinToggle');
  document.body.classList.toggle('wd-no-pinyin', mode === 'off');
}

function wdSetTextDir(mode) {
  textDir = mode;
  document.getElementById('wdBtnHoriz').classList.toggle('active', mode === 'horizontal');
  document.getElementById('wdBtnVert').classList.toggle('active', mode === 'vertical');
  wdUpdatePill('wdTextDirToggle');
  document.getElementById('wdMain').classList.toggle('wd-vertical', mode === 'vertical');
  // Update all writing textareas
  document.querySelectorAll('.wd-writing-area').forEach(el => {
    el.classList.toggle('vertical-mode', mode === 'vertical');
  });
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

// ── FAMILY TREE TOGGLE ──
function wdToggleFamily() {
  const body = document.getElementById('wdFamilyBody');
  const arrow = document.getElementById('wdFamilyArrow');
  if (!body) return;
  const isOpen = body.classList.contains('open');
  body.classList.toggle('open', !isOpen);
  if (arrow) arrow.style.transform = isOpen ? '' : 'rotate(180deg)';
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

// ── RENDER: HEADER ──
function renderHeader() {
  document.getElementById('wdHeroChar').textContent = charDisplay();
  document.getElementById('wdHeroPinyin').textContent = primaryPinyin();

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

  // Domain chips in header
  const domainsEl = document.getElementById('wdHeroDomains');
  const senses = WORD.senses || [];
  const domains = [];
  senses.forEach(s => {
    if (s.domain) {
      const key = s.domain.slug;
      if (!domains.find(d => d.slug === key)) domains.push(s.domain);
    }
  });
  domainsEl.innerHTML = domains.map(d => {
    const en = d.en || d.slug;
    const zh = d.zh || '';
    const display = langMode === 'en' ? en : langMode === 'zh' ? zh : `${en} \u00b7 ${zh}`;
    return `<span class="wd-hero-domain" data-en="${en}" data-zh="${zh}" data-state="${langMode === 'zh' ? 'zh' : 'en'}" onclick="toggleLangChip(event,this)">${display}</span>`;
  }).join(' ');
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

  // Sense header
  const headerItems = [`<span class="wd-sense-badge">${idx + 1}</span>`];
  if (sense.pinyin) headerItems.push(`<span class="wd-sense-pinyin">${sense.pinyin}</span>`);
  if (sense.domain) {
    const en = sense.domain.en || sense.domain.slug;
    const zh = sense.domain.zh || '';
    const display = langMode === 'en' ? en : langMode === 'zh' ? zh : `${en} \u00b7 ${zh}`;
    headerItems.push(`<span class="wd-sense-domain" data-en="${en}" data-zh="${zh}" data-state="${langMode === 'zh' ? 'zh' : 'en'}" onclick="toggleLangChip(event,this)">${display}</span>`);
  }
  if (sense.secondaryDomain) {
    const en = sense.secondaryDomain.en || sense.secondaryDomain.slug;
    const zh = sense.secondaryDomain.zh || '';
    const display = langMode === 'en' ? en : langMode === 'zh' ? zh : `${en} \u00b7 ${zh}`;
    headerItems.push(`<span class="wd-sense-domain" data-en="${en}" data-zh="${zh}" data-state="${langMode === 'zh' ? 'zh' : 'en'}" onclick="toggleLangChip(event,this)">${display}</span>`);
  }
  if (sense.tocfl) {
    const tl = LABELS.tocfl[sense.tocfl];
    if (tl) headerItems.push(`<span class="wd-sense-tocfl">${iconsMode === 'on' ? tl.icon + ' ' : ''}${langMode === 'zh' ? tl.zh : langMode === 'en' ? tl.en : tl.en + ' \u00b7 ' + tl.zh}</span>`);
  }
  parts.push(`<div class="wd-sense-header">${headerItems.join('')}</div>`);

  // Definitions
  if (isSectionVisible('definitions') && sense.definitions && sense.definitions.length) {
    const defs = sense.definitions.map(d => {
      const fml = d.formula || '';
      const fmlDisplay = scriptMode === 'simplified' && WORD.traditional !== WORD.simplified ? fml.replace(new RegExp(WORD.traditional.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), WORD.simplified) : fml;
      return `<div class="wd-def-row">
        ${d.pos ? `<span class="wd-pos" data-abbr="${POS_ABBR[d.pos] || d.pos}" data-full="${posDisplay(d.pos)}" data-zh="${POS_ZH[d.pos] || posDisplay(d.pos)}" data-state="abbr" onclick="cyclePosChip(event, this)">${posLabel(d.pos)}</span>` : ''}
        <span class="wd-definition">${d.def}</span>
      </div>
      ${fmlDisplay ? `<div class="wd-formula">${fmlDisplay}</div>` : ''}
      ${d.usageNote ? `<div class="wd-usage-note">${d.usageNote}</div>` : ''}`;
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

  // Examples
  if (isSectionVisible('examples') && sense.examples && sense.examples.length) {
    const exHTML = sense.examples.filter(ex => !ex.isSuppressed).map((ex, i) => {
      return `<div class="wd-example">
        <span class="wd-ex-num">${i + 1}</span>
        <div class="wd-ex-body">
          <div class="wd-ex-cn">${segmentedHTML(ex.cn)}</div>
          <div class="wd-ex-en">${ex.en}</div>
          ${ex.source ? `<span class="wd-ex-source">${ex.source}</span>` : ''}
          ${ex.theme ? `<span class="wd-ex-theme">${ex.theme}</span>` : ''}
        </div>
      </div>`;
    }).join('');
    parts.push(`<div class="wd-examples">
      <div class="wd-examples-title">${langText('Examples', '例句')}</div>
      ${exHTML}
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

  // Related words
  if (isSectionVisible('relations') && sense.relations) {
    const groups = [
      { key: 'synonymClose',     en: 'Close Synonyms',    zh: '近義詞' },
      { key: 'synonymRelated',   en: 'Related Synonyms',  zh: '相關近義' },
      { key: 'antonym',          en: 'Antonyms',          zh: '反義詞' },
      { key: 'contrast',         en: 'Contrasts',         zh: '對比' },
      { key: 'registerVariant',  en: 'Register Variants', zh: '語域變體' },
    ];
    const relHTML = groups.map(g => {
      const items = sense.relations[g.key];
      if (!items || !items.length) return '';
      return `<div>
        <div class="wd-relation-group-title">${langText(g.en, g.zh)}</div>
        <div class="wd-relation-cards">
          ${items.map(r => renderRelCard(r)).join('')}
        </div>
      </div>`;
    }).filter(Boolean).join('');
    if (relHTML) {
      parts.push(`<div class="wd-relations">${relHTML}</div>`);
    }
  }

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

  // Default examples tab
  const defaultExHTML = (sense.examples || []).filter(ex => !ex.isSuppressed).map((ex, i) => {
    return `<div class="wd-example">
      <span class="wd-ex-num">${i + 1}</span>
      <div class="wd-ex-body">
        <div class="wd-ex-cn">${segmentedHTML(ex.cn)}</div>
        <div class="wd-ex-en">${ex.en}</div>
      </div>
    </div>`;
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
        ${defaultExHTML}
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

// ── RENDER: FAMILY TREE ──
function renderFamilyTree() {
  if (!isSectionVisible('familyTree')) return '';
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

  return `<div class="wd-family">
    <button class="wd-family-toggle" onclick="wdToggleFamily()">
      <span>${langText('Word Family Tree', '詞族樹')}</span>
      <span class="wd-family-arrow" id="wdFamilyArrow">&#9662;</span>
    </button>
    <div class="wd-family-body" id="wdFamilyBody">
      ${groups.join('')}
    </div>
  </div>`;
}

// ── RENDER: ACTIONS ──
function renderActions() {
  return `<div class="wd-actions">
    <button class="wd-action-btn" onclick="wdSaveToCollection()">+ ${langText('Save to Collection', '加入收藏')}</button>
    <button class="wd-action-btn" id="wdShareBtn" onclick="wdShare()">&nearr; ${langText('Share', '分享')}</button>
  </div>`;
}

// ── RENDER: PHASE 2 STUBS ──
function renderPhaseStubs() {
  return `
    <div class="wd-phase-stub">
      <div class="wd-phase-stub-title">${langText('Collections', '收藏')}</div>
      <div class="wd-phase-stub-text">${langText('Personal collections coming in Phase 2', '個人收藏功能將在第二階段推出')}</div>
    </div>
    <div class="wd-phase-stub">
      <div class="wd-phase-stub-title">${langText('Learning History', '學習紀錄')}</div>
      <div class="wd-phase-stub-text">${langText('Learning history coming in Phase 2', '學習紀錄將在第二階段推出')}</div>
    </div>
  `;
}

// ── MAIN RENDER ──
function renderPage() {
  renderHeader();

  const sections = [];

  // Identity
  sections.push(renderIdentity());

  // Senses
  (WORD.senses || []).forEach((sense, i) => {
    sections.push(renderSense(sense, i));
  });

  // Family tree
  sections.push(renderFamilyTree());

  // Actions
  sections.push(renderActions());

  // Phase stubs
  sections.push(renderPhaseStubs());

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

  applyLevelFonts(currentLevel);
  renderPage();

  // Init pills after render
  requestAnimationFrame(() => {
    ['wdScriptToggle','wdLangToggle','wdIconsToggle','wdPinyinToggle','wdTextDirToggle'].forEach(wdUpdatePill);
  });

  // Back to top
  const btt = document.getElementById('wdBackToTop');
  window.addEventListener('scroll', function() {
    btt.classList.toggle('visible', window.scrollY > 300);
  }, { passive: true });
});
</script>
</body>
</html>
