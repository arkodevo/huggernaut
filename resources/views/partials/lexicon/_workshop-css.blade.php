{{-- Shared Writing Conservatory styles — used by lexicon card + IWP --}}
<style>
/* ── WORKSHOP PANEL ── */
.ws-panel {
  grid-column: 1 / -1;
  margin: 0.75rem -0.75rem -0.6rem;
  padding: 0.75rem 0.75rem 0.6rem;
  background: var(--surface2);
  border-top: 1px solid var(--border);
  border-radius: 0 0 2px 2px;
  display: flex; flex-direction: column; gap: 0.75rem;
}
.ws-panel--iwp {
  margin: 0 -0.6rem -0.75rem;
  border-radius: 0 0 2px 2px;
  padding: 0.5rem 0.6rem 0.6rem;
}
.ws-header {
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;
  background: var(--surface2);
  margin: -0.75rem -0.75rem 0.5rem;
  padding: 0.55rem 0.75rem;
  border-radius: 2px 2px 0 0;
}
.ws-panel--iwp .ws-header {
  margin: -0.5rem -0.6rem 0.5rem -0.6rem;
  padding: 0.55rem 0.6rem;
}
.ws-title {
  font-family: 'DM Mono', monospace;
  font-size: 0.81rem; color: var(--accent);
  letter-spacing: 0.08em;
}
.ws-toggle {
  font-size: 0.85rem;
  color: var(--accent); cursor: pointer; background: none; border: none;
  padding: 0.2rem 0; transition: color 0.2s; line-height: 1;
}
.ws-toggle:hover { color: var(--accent); }

/* ── EXAMPLE FILTER CHIPS ── */
.ws-filters {
  display: flex; gap: 0.35rem; flex-wrap: wrap; padding: 0.25rem 0;
}
.ws-filter-chip {
  padding: 0.25rem 0.6rem; border-radius: 2px;
  border: 1px solid rgba(98,64,200,0.2);
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  color: var(--dim); background: transparent; cursor: pointer;
  transition: all 0.18s; white-space: nowrap;
}
.ws-filter-chip:hover { border-color: rgba(98,64,200,0.4); color: var(--text); }
.ws-filter-chip.active { border-color: var(--accent); background: var(--tag-bg); color: var(--accent); }

/* ── SHIFU WRITING AREA ── */
.ws-shifu-area {
  background: rgba(98,64,200,0.04);
  border: 1px solid rgba(98,64,200,0.1);
  border-radius: 2px;
  padding: 0.75rem;
  display: flex; flex-direction: column; gap: 0.6rem;
}

/* ── AI TABS ── */
.ws-ai-tabs {
  display: flex; gap: 0.4rem; flex-wrap: wrap;
}
.ws-ai-tab {
  padding: 0.4rem 0.75rem; border-radius: 2px;
  border: 1px solid var(--border);
  font-family: 'DM Mono', monospace; font-size: 1rem;
  color: var(--dim); background: transparent; cursor: pointer;
  transition: all 0.18s;
}
.ws-ai-tab:hover { border-color: rgba(255,255,255,0.2); color: var(--text); }
.ws-ai-tab.active { border-color: var(--accent); background: var(--tag-bg); color: var(--accent); }

/* ── AI WORKSPACE ── */
.ws-ai-workspace {
  display: flex; flex-direction: column; gap: 0.6rem;
  padding: 0.75rem;
  background: #f0eef8;
  border: 1px solid rgba(98,64,200,0.15);
  border-radius: 2px;
}
.ws-ai-instruction {
  font-size: 0.81rem; color: var(--dim); line-height: 1.6;
}
.ws-ai-input-row { display: flex; flex-direction: column; gap: 0.5rem; align-items: stretch; }

/* ── AI TEXTAREA ── */
.ws-ai-textarea {
  flex: 1; min-width: 200px;
  background: #ffffff;
  border: 1px solid rgba(98,64,200,0.25);
  color: var(--ink);
  font-family: BiauKai, STKaiti, KaiTi, serif !important; font-size: 1.5rem;
  padding: 0.65rem; border-radius: 2px; outline: none;
  min-height: 60px; line-height: 1.6;
  transition: border-color 0.2s;
}
.ws-ai-textarea::placeholder { font-family: BiauKai, STKaiti, KaiTi, serif !important; font-size: 1.5rem; color: rgba(26,24,40,0.3); }
.ws-ai-textarea:focus { border-color: var(--accent); }
.ws-ai-textarea.vertical-mode {
  writing-mode: vertical-rl;
  text-orientation: mixed;
  letter-spacing: 0.08em;
  min-height: 280px;
  resize: horizontal;
  overflow: auto;
}

/* ── AI THEME INPUT ── */
.ws-ai-theme-input {
  flex: 1; min-width: 160px;
  background: #ffffff;
  border: 1px solid rgba(98,64,200,0.25);
  color: var(--ink);
  font-family: 'DM Mono', monospace; font-size: 0.95rem;
  padding: 0.65rem;
  border-radius: 2px; outline: none;
  transition: border-color 0.2s;
}
.ws-ai-theme-input::placeholder { color: rgba(26,24,40,0.3); }
.ws-ai-theme-input:focus { border-color: var(--accent); }

/* ── AI POS SELECT ── */
.ws-ai-pos-select {
  font-family: 'DM Mono', monospace; font-size: 0.78rem;
  color: var(--accent); background: var(--tag-bg);
  border: 1px solid rgba(98,64,200,0.25); border-radius: 2px;
  padding: 0.35rem 0.6rem; outline: none; cursor: pointer;
  transition: border-color 0.2s;
}
.ws-ai-pos-select:focus { border-color: var(--accent); }

/* ── AI SUBMIT BUTTON ── */
.ws-ai-submit-btn {
  padding: 0.5rem 1rem; border-radius: 2px;
  border: 1px solid var(--accent);
  background: var(--tag-bg); color: var(--accent);
  font-family: 'DM Mono', monospace; font-size: 0.85rem;
  cursor: pointer; transition: all 0.2s; white-space: nowrap;
  width: 100%;
}
.ws-ai-submit-btn:hover { background: rgba(155,127,240,0.2); }
.ws-ai-submit-btn:disabled { opacity: 0.4; cursor: wait; }

/* ── AI RESPONSE ── */
.ws-ai-response {
  padding: 0.65rem 0.75rem;
  background: rgba(98,64,200,0.04);
  border: 1px solid rgba(98,64,200,0.15);
  border-radius: 2px;
  display: flex; flex-direction: column; gap: 0.4rem;
  animation: wsCardIn 0.2s ease both;
}
@keyframes wsCardIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
.ws-ai-response-label {
  font-size: 0.55rem; letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--accent);
}
.ws-ai-response-text {
  font-size: 0.9rem; color: var(--text); line-height: 1.7;
}
.ws-ai-response-text .resp-cn {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: var(--fs-ex-cn, 1.8rem); color: var(--ink);
  display: block; margin-bottom: 0.2rem; line-height: 1.5;
}
.vertical-mode .ws-ai-response-text .resp-cn,
.ws-ai-response-text .resp-cn.ws-vertical {
  writing-mode: vertical-rl;
  text-orientation: mixed;
  letter-spacing: 0.08em;
  margin-bottom: 0.4rem;
  margin-left: auto;
  max-height: 320px;
  max-width: 100%;
  overflow-x: auto;
  overflow-y: hidden;
  padding-bottom: 0.3rem;
}
.ws-ai-response-text .resp-cn .highlight { color: var(--accent); font-weight: 600; }
.ws-ai-response-text .resp-en { color: var(--dim); font-style: italic; font-size: var(--fs-ex-en, 1rem); display: block; margin-bottom: 0.3rem; }
.ws-ai-response-text .resp-note { font-family: 'Cormorant Garamond', serif; color: var(--dim); font-size: 0.9rem; line-height: 1.6; border-top: 1px solid var(--border); padding-top: 0.3rem; margin-top: 0.1rem; }
.ws-ai-response-actions { display: flex; gap: 0.4rem; flex-wrap: wrap; align-items: center; }
.ws-share-community {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-family: 'DM Mono', monospace;
  font-size: 0.68rem;
  color: var(--dim);
  cursor: pointer;
  user-select: none;
  padding: 0.25rem 0.5rem;
  border-radius: 0.3rem;
  transition: color 0.15s;
}
.ws-share-community:hover { color: var(--ink); }
.ws-share-community input[type="checkbox"] {
  accent-color: var(--accent);
  cursor: pointer;
  margin: 0;
}

/* ── SAVED DECK ── */
.ws-saved-deck-section {
  margin-top: 0.5rem;
}
.ws-saved-deck-label {
  font-family: 'DM Mono', monospace;
  font-size: 0.78rem; letter-spacing: 0.08em;
  color: var(--accent); margin-bottom: 0.4rem; display: block;
}

/* ── SAVED WRITING CHIPS ── */
.ws-saved-writing-chips {
  display: flex; align-items: stretch; gap: 0.4rem;
  flex-wrap: wrap; margin-bottom: 0.15rem;
}
.ws-saved-writing-chips .ex-sent-pos,
.ws-saved-writing-chips .ws-shifu-chip,
.ws-saved-writing-chips .ws-level-chip,
.ws-saved-writing-chips .ws-mastery-chip {
  display: inline-flex; align-items: center;
  height: 1.5rem; box-sizing: border-box;
}
.ws-shifu-chip {
  font-family: 'DM Mono', monospace;
  font-size: 0.68rem; letter-spacing: 0.04em;
  color: var(--accent); background: rgba(98,64,200,0.07);
  border: 1px solid rgba(98,64,200,0.2);
  border-radius: 1px;
  padding: 0.3rem 0.5rem;
  white-space: nowrap;
}

/* ── SAVED ITEM FEEDBACK ── */
.ws-saved-feedback { margin-top: 0.6rem; }
.ws-saved-feedback summary {
  font-family: 'DM Mono', monospace; font-size: 0.85rem;
  color: var(--accent); cursor: pointer; user-select: none;
  list-style: none;
}
.ws-saved-feedback summary::before { content: '▸ '; }
.ws-saved-feedback[open] summary::before { content: '▾ '; }
.ws-saved-feedback-text {
  font-family: 'Cormorant Garamond', serif; font-size: 1rem;
  color: var(--dim); line-height: 1.6;
  border-left: 2px solid rgba(98,64,200,0.15);
  margin-top: 0.2rem;
  padding-left: 0.6rem;
}

/* ── ORIGINAL SUBMISSION ── */
.ws-original-submission {
  margin-bottom: 0.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(98,64,200,0.1);
}
.ws-original-label {
  font-family: 'DM Mono', monospace; font-size: 0.75rem;
  color: var(--dim); opacity: 0.8;
}
.ws-original-text {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 1.1rem; color: var(--ink); opacity: 0.7;
}

/* ── SAVED WRITING META ── */
.ws-saved-meta {
  display: flex; align-items: center; justify-content: space-between;
  margin-top: 0.4rem; gap: 0.5rem;
}
.ws-saved-date {
  font-family: 'DM Mono', monospace; font-size: 0.62rem;
  color: var(--dim); opacity: 0.7;
}

/* ── FLUENCY LEVEL SELECTOR ── */
.ws-shifu-header {
  display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;
  flex-wrap: wrap;
}
.ws-fluency-select {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--accent); background: var(--tag-bg);
  border: 1px solid rgba(98,64,200,0.25); border-radius: 2px;
  padding: 0.25rem 0.5rem; outline: none; cursor: pointer;
  transition: border-color 0.2s;
}
.ws-fluency-select:focus { border-color: var(--accent); }

/* ── ASSESSMENT CHIPS ── */
.ws-assess-chips {
  display: flex; gap: 0.3rem; flex-wrap: wrap; margin-bottom: 0.2rem;
}
.ws-level-chip {
  font-family: 'DM Mono', monospace;
  font-size: 0.68rem; letter-spacing: 0.04em;
  color: var(--jade); background: rgba(26,138,90,0.06);
  border: 1px solid rgba(26,138,90,0.18);
  border-radius: 2px; padding: 0.15rem 0.4rem;
  white-space: nowrap;
}
.ws-mastery-chip {
  font-family: 'DM Mono', monospace;
  font-size: 0.68rem; letter-spacing: 0.04em;
  color: var(--gold); background: rgba(180,140,60,0.06);
  border: 1px solid rgba(180,140,60,0.18);
  border-radius: 2px; padding: 0.15rem 0.4rem;
  white-space: nowrap;
}

/* ── GRAMMAR PATTERN CHIPS (師父-identified) ── */
.ws-grammar-patterns {
  display: flex; flex-wrap: wrap; align-items: center; gap: 0.3rem;
  margin-bottom: 0.3rem;
  font-family: 'DM Mono', monospace;
  font-size: 0.68rem; letter-spacing: 0.04em;
}
.ws-grammar-label {
  color: var(--dim); text-transform: uppercase;
}
.ws-grammar-chip {
  border-radius: 2px; padding: 0.15rem 0.4rem;
  white-space: nowrap; cursor: help;
  border: 1px solid var(--border);
}
.ws-grammar-correct {
  color: var(--jade);
  background: rgba(26,138,90,0.06);
  border-color: rgba(26,138,90,0.22);
}
.ws-grammar-almost {
  color: var(--gold);
  background: rgba(180,140,60,0.06);
  border-color: rgba(180,140,60,0.22);
}
.ws-grammar-misused {
  color: var(--rose);
  background: rgba(200,60,80,0.06);
  border-color: rgba(200,60,80,0.22);
}

/* ── MASTERY GUIDANCE (in feedback) ── */
.ws-mastery-guidance {
  margin-top: 0.4rem; padding-top: 0.3rem;
  border-top: 1px dashed rgba(98,64,200,0.12);
  font-style: italic;
}
.ws-mastery-guidance-note {
  color: var(--accent) !important;
}

/* ── TRY AGAIN BUTTON ── */
.ws-try-again-btn {
  padding: 0.3rem 0.7rem; border-radius: 2px;
  border: 1px solid var(--border);
  background: transparent; color: var(--dim);
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  cursor: pointer; transition: all 0.2s; white-space: nowrap;
}
.ws-try-again-btn:hover { border-color: var(--accent); color: var(--accent); }

/* Primary variant — used when Try Again is the recommended next step
   (needs_work / missing_target). Visually stronger than Save Anyway. */
.ws-try-again-btn.ws-try-again-primary {
  padding: 0.45rem 1rem;
  border: 1px solid var(--accent);
  background: var(--accent); color: #fff;
  font-size: 0.78rem; font-weight: 600;
}
.ws-try-again-btn.ws-try-again-primary:hover {
  filter: brightness(1.1);
}

/* ── SAVE ANYWAY BUTTON ── muted, secondary, deliberately understated
   so the learner's eye goes to Try Again first. This is the "I want to
   keep a record of my flawed attempt" escape hatch, not the main path. */
.ws-save-anyway-btn {
  padding: 0.3rem 0.6rem; border-radius: 2px;
  border: 1px dashed var(--border);
  background: transparent; color: var(--dim);
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  cursor: pointer; transition: all 0.2s; white-space: nowrap;
  opacity: 0.7;
}
.ws-save-anyway-btn:hover {
  opacity: 1; border-color: var(--dim); color: var(--ink);
}

/* ── REMOVE BUTTON ── */
.remove-btn {
  font-family: 'DM Mono', monospace;
  font-size: 0.65rem; color: var(--dim); cursor: pointer;
  background: none; border: none; padding: 0.2rem 0;
  transition: color 0.2s;
}
.remove-btn:hover { color: var(--rose); }

/* ── DELETE CONFIRMATION ── */
.ws-delete-confirm {
  display: flex; align-items: center; gap: 0.6rem;
  padding: 0.5rem 0.6rem; margin-top: 0.4rem;
  background: rgba(200,60,60,0.04);
  border: 1px solid rgba(200,60,60,0.2);
  border-radius: 2px;
  animation: wsFadeIn 0.15s ease;
}
@keyframes wsFadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
.ws-delete-confirm-msg {
  font-family: 'Cormorant Garamond', serif;
  font-size: 0.9rem; color: var(--text); flex: 1;
}
.ws-delete-confirm-yes {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: #fff; background: var(--rose);
  border: none; border-radius: 2px;
  padding: 0.3rem 0.7rem; cursor: pointer;
  transition: opacity 0.2s;
}
.ws-delete-confirm-yes:hover { opacity: 0.8; }
.ws-delete-confirm-no {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--dim); background: none;
  border: 1px solid var(--border); border-radius: 2px;
  padding: 0.3rem 0.7rem; cursor: pointer;
  transition: all 0.2s;
}
.ws-delete-confirm-no:hover { border-color: var(--accent); color: var(--text); }

/* ── MOBILE ── */
@media (max-width: 599px) {
  .ws-ai-tabs { flex-direction: column; }
  .ws-ai-tab  { width: 100%; text-align: center; }
}

/* ── DESKTOP ── */
@media (min-width: 600px) {
  .ws-ai-submit-btn {
    width: auto; max-width: 280px;
  }
}
</style>
