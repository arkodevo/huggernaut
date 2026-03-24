{{-- Collection Testing feature styles --}}
<style>
/* ══════════════════════════════════════
   COLLECTION TEST — Setup / Question / Results
   ══════════════════════════════════════ */

/* ── LAYOUT SHELL ── */
.ct-page {
  max-width: 640px;
  margin: 0 auto;
  padding: 1.5rem 1rem 3rem;
}
.ct-wrap {
  max-width: 640px;
  margin: 0 auto;
  padding: 1.2rem 1rem 2rem;
}

/* ── SETUP SCREEN ── */
.ct-setup {
  display: flex; flex-direction: column;
  align-items: center; gap: 1.2rem;
  text-align: center;
}
.ct-setup-title {
  font-family: 'DM Mono', monospace;
  font-size: 0.78rem; letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--accent);
}
.ct-collection-name {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.6rem; color: var(--ink);
  line-height: 1.3;
}
.ct-collection-count {
  font-family: 'DM Mono', monospace;
  font-size: 0.72rem; color: var(--dim);
  letter-spacing: 0.04em;
}

/* Mode list: single column, compact buttons */
.ct-mode-list {
  display: flex; flex-direction: column;
  gap: 0.4rem;
  width: 100%;
}
.ct-mode-btn {
  display: flex;
  align-items: center; justify-content: center;
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--border);
  border-radius: 2px;
  background: transparent;
  cursor: pointer;
  transition: all 0.18s;
  user-select: none;
}
.ct-mode-btn:hover {
  border-color: rgba(98,64,200,0.35);
  background: rgba(98,64,200,0.03);
}
.ct-mode-btn:active {
  transform: scale(0.97);
}
.ct-mode-btn.selected {
  border-color: var(--accent);
  background: rgba(98,64,200,0.07);
}
.ct-mode-label {
  font-family: 'DM Mono', monospace;
  font-size: 0.75rem; letter-spacing: 0.04em;
  color: var(--text);
}
.ct-mode-btn.selected .ct-mode-label {
  color: var(--accent);
}
/* Description shown on mode select */
.ct-mode-desc {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1rem; line-height: 1.5;
  color: var(--dim);
  text-align: left;
  padding: 0.6rem 0.75rem;
  border-left: 2px solid rgba(98,64,200,0.2);
}
.ct-setup-question {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.15rem; color: var(--accent);
  margin-top: 0.5rem;
}

/* Attribute sub-selector */
.ct-attr-sub {
  display: flex; gap: 0.35rem;
  flex-wrap: wrap;
  justify-content: center;
  width: 100%;
  padding: 0.5rem 0;
  animation: ctFadeIn 0.18s ease both;
}
.ct-attr-chip {
  padding: 0.3rem 0.6rem;
  border: 1px solid var(--border);
  border-radius: 2px;
  font-family: 'DM Mono', monospace;
  font-size: 0.68rem; letter-spacing: 0.04em;
  color: var(--dim);
  background: transparent;
  cursor: pointer;
  transition: all 0.18s;
  white-space: nowrap;
}
.ct-attr-chip:hover {
  border-color: rgba(98,64,200,0.4);
  color: var(--text);
}
.ct-attr-chip.selected {
  border-color: var(--accent);
  background: var(--tag-bg);
  color: var(--accent);
}

/* Start button */
.ct-start-btn {
  width: 100%;
  padding: 0.7rem 1rem;
  border: 1px solid var(--accent);
  border-radius: 2px;
  background: var(--accent);
  color: #fff;
  font-family: 'DM Mono', monospace;
  font-size: 0.88rem; letter-spacing: 0.06em;
  cursor: pointer;
  transition: all 0.2s;
}
.ct-start-btn:hover {
  background: #5535b0;
  border-color: #5535b0;
}
.ct-start-btn:disabled {
  opacity: 0.4; cursor: not-allowed;
}

/* ── QUESTION SCREEN ── */
.ct-question {
  display: flex; flex-direction: column;
  gap: 1rem;
}

/* Progress bar */
.ct-progress {
  width: 100%; height: 4px;
  background: var(--surface2);
  border-radius: 2px;
  overflow: hidden;
}
.ct-progress-fill {
  height: 100%;
  background: var(--accent);
  border-radius: 2px;
  transition: width 0.35s ease;
}

/* Score tally */
.ct-tally {
  display: flex; align-items: center;
  gap: 0.75rem;
  font-family: 'DM Mono', monospace;
  font-size: 0.75rem; color: var(--dim);
  letter-spacing: 0.04em;
}
.ct-tally-item {
  display: flex; align-items: center; gap: 0.2rem;
}
.ct-tally-fruit  { color: var(--jade); }
.ct-tally-bud    { color: var(--gold); }
.ct-tally-seed   { color: var(--rose); }
.ct-tally-count {
  font-variant-numeric: tabular-nums;
  min-width: 1.2em; text-align: right;
}
.ct-tally-of {
  color: var(--dim); opacity: 0.6;
  margin-left: auto;
}

/* Question card — matches SRP word-card grid */
.ct-card {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 0.75rem 1rem;
  padding: 1rem 1.2rem;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 2px;
  animation: ctCardIn 0.22s ease both;
}
@keyframes ctCardIn {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}
.ct-card-char {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 2.8rem; font-weight: 300;
  color: var(--ink); line-height: 1.1;
  writing-mode: vertical-rl;
  text-orientation: mixed;
  letter-spacing: 0.1em;
}
.ct-card-meta {
  display: flex; flex-direction: column;
  gap: 0.35rem; justify-content: center;
  min-width: 0;
}
.ct-card-pinyin {
  font-family: 'Cormorant Garamond', serif;
  font-style: italic;
  font-size: 1.05rem; color: var(--accent);
  letter-spacing: 0.05em;
}
.ct-card-domain {
  font-family: 'DM Mono', monospace;
  font-size: 0.72rem; letter-spacing: 0.04em;
  color: var(--gold);
}
/* ── IWP-STYLE HEADER BLOCK ── */
.ct-card-hanzi {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 2.8rem; font-weight: 300;
  color: var(--ink); line-height: 1.1;
  writing-mode: vertical-rl;
  text-orientation: mixed;
  letter-spacing: 0.1em;
  grid-row: 1;
}
.ct-card-header-meta {
  display: flex; flex-direction: column;
  gap: 0.35rem; justify-content: center;
}
.ct-header-chip {
  display: block; text-align: left;
  font-family: 'DM Mono', monospace;
  font-size: 0.72rem; letter-spacing: 0.04em;
  padding: 0.35rem 0.6rem;
  border: 1px solid var(--border);
  border-radius: 2px;
  background: var(--surface);
  color: var(--dim);
  cursor: pointer;
  transition: all 0.25s ease;
  user-select: none;
}
.ct-header-chip.ct-tested {
  cursor: default;
  border-color: rgba(98,64,200,0.3);
  background: rgba(98,64,200,0.05);
  color: var(--accent);
  font-weight: 500;
}
.ct-header-chip.ct-hintable:hover {
  border-color: rgba(98,64,200,0.3);
}
.ct-header-chip .ct-hint-val { display: none; }
.ct-header-chip.revealed { background: var(--tag-bg); border-color: rgba(98,64,200,0.25); color: var(--ink); cursor: default; }
.ct-header-chip.revealed .ct-hint-q { display: none; }
.ct-header-chip.revealed .ct-hint-val { display: inline; }
.ct-header-chip.revealed .pinyin { color: var(--accent); font-family: 'Cormorant Garamond', serif; font-style: italic; }

/* ── QUESTION BLOCK ── */
.ct-question-block {
  padding: 0.75rem 0;
}
.ct-prompt {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.1rem; color: var(--accent);
  line-height: 1.5;
  margin-bottom: 0.6rem;
  font-weight: 600;
}

/* ── HINT BLOCK (below question) ── */
.ct-hint-block {
  border-top: 1px solid var(--border);
  padding-top: 0.75rem;
  margin-top: 0.5rem;
}
.ct-hint-row {
  display: block; width: 100%; text-align: left;
  font-family: 'DM Mono', monospace;
  font-size: 0.75rem; letter-spacing: 0.04em;
  padding: 0.4rem 0.6rem;
  border: 1px solid var(--border);
  border-radius: 2px;
  background: var(--surface);
  color: var(--dim);
  cursor: pointer;
  transition: all 0.25s ease;
  margin-bottom: 0.5rem;
}
.ct-hint-row .ct-hint-val { display: none; }
.ct-hint-row.revealed { background: var(--tag-bg); border-color: rgba(98,64,200,0.25); color: var(--ink); cursor: default; }
.ct-hint-row.revealed .ct-hint-q { display: none; }
.ct-hint-row.revealed .ct-hint-val { display: inline; font-family: 'Cormorant Garamond', serif; font-size: 0.95rem; }

/* ── ATTRIBUTE HINT GRID (IWP style) ── */
.ct-attr-hints {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.4rem;
  margin-bottom: 0.5rem;
}
.ct-attr-hint {
  cursor: pointer;
  transition: all 0.25s ease;
  user-select: none;
}
.ct-attr-hint .ct-hint-placeholder {
  font-size: 1.2rem;
  color: var(--dim);
  opacity: 0.4;
}
.ct-attr-hint .ct-hint-val { display: none; }
.ct-attr-hint.revealed .ct-hint-val { display: block; }
.ct-attr-hint.revealed .ct-hint-placeholder { display: none; }
.ct-attr-hint.revealed { cursor: default; }

/* Multiple choice options */
.ct-options {
  display: flex; flex-direction: column;
  gap: 0.45rem;
}
.ct-option {
  width: 100%;
  padding: 0.65rem 0.85rem;
  border: 1px solid var(--border);
  border-radius: 2px;
  background: transparent;
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.05rem; color: var(--text);
  text-align: left;
  cursor: pointer;
  transition: all 0.18s;
  line-height: 1.5;
}
.ct-option:hover {
  border-color: rgba(98,64,200,0.35);
  background: rgba(98,64,200,0.03);
}
.ct-option:active {
  transform: scale(0.99);
}
.ct-option.ct-correct {
  border-color: var(--jade);
  background: rgba(26,138,90,0.08);
  color: var(--jade);
  animation: ctFlashCorrect 0.5s ease;
  pointer-events: none;
}
.ct-option.ct-incorrect {
  border-color: var(--rose);
  background: rgba(184,48,80,0.08);
  color: var(--rose);
  animation: ctFlashIncorrect 0.5s ease;
  pointer-events: none;
}
.ct-option.ct-disabled {
  opacity: 0.4;
  pointer-events: none;
}

/* Correct / incorrect keyframe flashes */
@keyframes ctFlashCorrect {
  0%   { background: rgba(26,138,90,0.25); }
  100% { background: rgba(26,138,90,0.08); }
}
@keyframes ctFlashIncorrect {
  0%   { background: rgba(184,48,80,0.25); }
  100% { background: rgba(184,48,80,0.08); }
}

/* Usage mode textarea — mirrors workshop textarea */
.ct-usage-textarea {
  width: 100%;
  min-height: 80px;
  background: #ffffff;
  border: 1px solid rgba(98,64,200,0.25);
  color: var(--ink);
  font-family: BiauKai, STKaiti, KaiTi, serif;
  font-size: 1.5rem;
  padding: 0.65rem;
  border-radius: 2px;
  outline: none;
  line-height: 1.6;
  resize: vertical;
  transition: border-color 0.2s;
}
.ct-usage-textarea::placeholder {
  font-family: BiauKai, STKaiti, KaiTi, serif;
  font-size: 1.5rem;
  color: rgba(26,24,40,0.3);
}
.ct-usage-textarea:focus {
  border-color: var(--accent);
}

/* Submit usage button */
.ct-usage-submit {
  padding: 0.5rem 1rem;
  border: 1px solid var(--accent);
  border-radius: 2px;
  background: var(--tag-bg);
  color: var(--accent);
  font-family: 'DM Mono', monospace;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
  width: 100%;
}
.ct-usage-submit:hover { background: rgba(155,127,240,0.2); }
.ct-usage-submit:disabled { opacity: 0.4; cursor: wait; }

/* Examples hint — collapsed details */
.ct-examples-hint {
  margin-top: 0.3rem;
}
.ct-examples-hint summary {
  font-family: 'DM Mono', monospace;
  font-size: 0.75rem;
  color: var(--dim);
  cursor: pointer;
  user-select: none;
  list-style: none;
}
.ct-examples-hint summary::before { content: '▸ '; }
.ct-examples-hint[open] summary::before { content: '▾ '; }
.ct-examples-list {
  margin-top: 0.4rem;
  padding-left: 0.6rem;
  border-left: 2px solid rgba(98,64,200,0.12);
  display: flex; flex-direction: column; gap: 0.35rem;
}
.ct-example-cn {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 1.15rem; color: var(--ink);
  line-height: 1.5;
}
.ct-example-en {
  font-family: 'Cormorant Garamond', serif;
  font-size: 0.9rem; color: var(--dim);
  font-style: italic;
}

/* Next question / continue button */
.ct-next-btn {
  padding: 0.5rem 1rem;
  border: 1px solid var(--accent);
  border-radius: 2px;
  background: var(--accent);
  color: #fff;
  font-family: 'DM Mono', monospace;
  font-size: 0.82rem; letter-spacing: 0.04em;
  cursor: pointer;
  transition: all 0.2s;
  width: 100%;
}
.ct-next-btn:hover { background: #5535b0; border-color: #5535b0; }

/* ── Question navigation ── */
.ct-question-nav {
  display: flex; gap: 0.5rem; margin-top: 0.75rem;
}
.ct-question-nav .ct-next-btn { flex: 1; }
.ct-back-btn {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border);
  border-radius: 2px;
  background: transparent;
  color: var(--dim);
  font-family: 'DM Mono', monospace;
  font-size: 0.82rem; letter-spacing: 0.04em;
  cursor: pointer;
  transition: all 0.2s;
}
.ct-back-btn:hover { border-color: var(--accent); color: var(--accent); }
.ct-review-label {
  font-family: 'DM Mono', monospace;
  font-size: 0.7rem; letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--dim); text-align: center;
  margin-bottom: 0.3rem;
}

/* ── RESULTS SCREEN ── */
.ct-results {
  display: flex; flex-direction: column;
  align-items: center; gap: 1.2rem;
  text-align: center;
}
.ct-results-title {
  font-family: 'DM Mono', monospace;
  font-size: 0.78rem; letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--accent);
}

/* Three score displays side by side */
.ct-scores {
  display: flex;
  justify-content: center;
  gap: 1.5rem;
  width: 100%;
}
.ct-score-box {
  display: flex; flex-direction: column;
  align-items: center; gap: 0.25rem;
  flex: 1; max-width: 140px;
  padding: 0.85rem 0.5rem;
  border: 1px solid var(--border);
  border-radius: 2px;
}
.ct-score-icon {
  font-size: 1.6rem; line-height: 1;
}
.ct-score-num {
  font-family: 'DM Mono', monospace;
  font-size: 1.8rem; font-weight: 600;
  color: var(--ink);
  line-height: 1.1;
  font-variant-numeric: tabular-nums;
}
.ct-score-label {
  font-family: 'DM Mono', monospace;
  font-size: 0.62rem; letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--dim);
}
.ct-score-box.ct-score-fruit  { border-color: rgba(26,138,90,0.3); }
.ct-score-box.ct-score-bud    { border-color: rgba(160,114,10,0.3); }
.ct-score-box.ct-score-seed   { border-color: rgba(184,48,80,0.3); }
.ct-score-fruit  .ct-score-num { color: var(--jade); }
.ct-score-bud    .ct-score-num { color: var(--gold); }
.ct-score-seed   .ct-score-num { color: var(--rose); }

/* Per-word breakdown */
.ct-breakdown {
  width: 100%;
  display: flex; flex-direction: column;
  gap: 0.35rem;
  text-align: left;
}
.ct-breakdown-title {
  font-family: 'DM Mono', monospace;
  font-size: 0.72rem; letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--dim);
  margin-bottom: 0.2rem;
}
.ct-breakdown-item {
  display: flex; align-items: center;
  gap: 0.6rem;
  padding: 0.45rem 0.6rem;
  border: 1px solid var(--border);
  border-radius: 2px;
}
.ct-breakdown-char {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 1.3rem; color: var(--ink);
  min-width: 2.2em; text-align: center;
}
.ct-breakdown-word {
  font-family: 'Cormorant Garamond', serif;
  font-size: 0.95rem; color: var(--text);
  flex: 1; min-width: 0;
  overflow: hidden; text-overflow: ellipsis;
  white-space: nowrap;
}
.ct-breakdown-result {
  font-size: 1.1rem; line-height: 1;
  flex-shrink: 0;
}

/* Retry button */
.ct-retry-btn {
  width: 100%;
  padding: 0.7rem 1rem;
  border: 1px solid var(--accent);
  border-radius: 2px;
  background: var(--accent);
  color: #fff;
  font-family: 'DM Mono', monospace;
  font-size: 0.88rem; letter-spacing: 0.06em;
  cursor: pointer;
  transition: all 0.2s;
}
.ct-retry-btn:hover {
  background: #5535b0;
  border-color: #5535b0;
}

/* (back link now in shared _site-header partial) */

/* ── ANIMATIONS ── */
@keyframes ctFadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ── MOBILE (default) ── */
@media (max-width: 599px) {
  .ct-wrap { padding: 0.8rem 0.75rem 1.5rem; }
  .ct-card { padding: 0.75rem 0.9rem; gap: 0.6rem 0.75rem; }
  .ct-card-char { font-size: 2.2rem; }
  .ct-scores { gap: 0.75rem; }
  .ct-score-box { padding: 0.65rem 0.35rem; }
  .ct-score-num { font-size: 1.4rem; }
}

/* ── DESKTOP ── */
@media (min-width: 600px) {
  .ct-wrap { padding: 1.5rem 1.2rem 2.5rem; }
  .ct-mode-grid { gap: 0.75rem; }
  .ct-option:hover { transform: translateX(2px); }
}
</style>
