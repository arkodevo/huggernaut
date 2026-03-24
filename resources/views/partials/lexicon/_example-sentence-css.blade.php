{{-- Shared example sentence styles: POS chip + Chinese + English --}}
<style>
/* ── Example sentence card ── */
.ex-sentences { display: flex; flex-direction: column; gap: 0.4rem; }

.ex-sent {
  display: flex; align-items: flex-start; gap: 0.6rem;
  padding: 0.5rem 0.7rem;
  background: rgba(255,255,255,0.7);
  border: 1px solid rgba(98,64,200,0.08);
  border-radius: 2px;
  position: relative;
}
.ex-sent-pos {
  font-family: 'DM Mono', monospace;
  font-size: 0.68rem; letter-spacing: 0.04em;
  color: #7060a8; background: rgba(98,64,200,0.07);
  border: 1px solid rgba(98,64,200,0.18);
  border-radius: 2px; padding: 0.1rem 0.45rem;
  flex-shrink: 0; align-self: flex-start;
  white-space: nowrap;
}
.ex-sent-body { display: flex; flex-direction: column; gap: 0.15rem; flex: 1; min-width: 0; }
.ex-sent-cn {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: var(--fs-ex-cn, 1.8rem); color: var(--ink); line-height: 1.5;
}
.ex-sent-cn .highlight { color: var(--accent); font-weight: 600; }
.ex-sent-en { font-size: var(--fs-ex-en, 1rem); color: var(--dim); }
.ex-sent-source { font-size: 0.55rem; color: var(--dim); opacity: 0.6; }
.ex-sent-theme {
  display: inline-block;
  font-size: 0.55rem; color: var(--jade);
  background: rgba(26,138,90,0.06);
  border: 1px solid rgba(26,138,90,0.15);
  border-radius: 2px; padding: 0 0.3rem;
  margin-top: 0.1rem;
}
.ex-sent-save {
  font-size: 0.81rem; padding: 0.2rem 0.5rem;
  border: 1px solid rgba(62,180,137,0.25); border-radius: 2px;
  color: var(--jade); background: rgba(62,180,137,0.05);
  cursor: pointer; font-family: 'DM Mono', monospace;
  transition: all 0.2s; flex-shrink: 0; margin-top: 0.1rem;
  white-space: nowrap;
}
.ex-sent-save:hover { background: rgba(62,180,137,0.12); border-color: var(--jade); }
.ex-sent-save.saved { color: var(--dim); border-color: var(--border); cursor: default; }

/* ── Horizontal: POS on first line, Chinese breaks below filling width ── */
.ex-sent:not(.vertical) {
  flex-wrap: wrap;
}
.ex-sent:not(.vertical) .ex-sent-pos {
  /* POS chip sits on its own conceptual row with Chinese wrapping below */
}
.ex-sent:not(.vertical) .ex-sent-body {
  flex-basis: 100%; /* Forces body to new line, filling full width */
}

/* ── Vertical mode ── */
.ex-sent.vertical {
  flex-direction: column;
  align-items: flex-start;
}
.ex-sent.vertical .ex-sent-pos {
  align-self: flex-start;
  margin-bottom: 0.35rem;
}
.ex-sent.vertical .ex-sent-body {
  display: flex; flex-direction: column;
  gap: 0; width: 100%;
  align-items: stretch;
}
.ex-sent.vertical .ex-sent-cn {
  writing-mode: vertical-rl;
  text-orientation: mixed;
  letter-spacing: 0.08em;
  max-height: 20rem;
  max-width: 100%;
  line-height: 1.8;
  margin-bottom: 0.5rem;
  overflow-x: auto;
  overflow-y: hidden;
  padding-bottom: 0.4rem;
}
.ex-sent.vertical .ex-sent-en {
  writing-mode: horizontal-tb;
  text-align: left;
  width: 100%;
  border-top: 1px solid var(--border);
  padding-top: 0.4rem;
  font-size: var(--fs-ex-en, 0.9rem);
}
</style>
