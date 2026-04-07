{{-- Shared word header display: character + switch, domain, POS summary, pinyin --}}
<style>
/* ── Character display ── */
.card-hanzi {
  display: flex; flex-direction: row; align-items: flex-start;
  gap: 0.4rem;
}
.hanzi-primary-wrap {
  display: flex; flex-direction: column; align-items: center; gap: 0.25rem;
}
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

/* ── Header mid zone: domain + POS summary + pinyin ── */
.card-hdr-mid {
  display: flex; flex-direction: column; gap: 0.4rem; min-width: 0;
}

/* Per-sense domain + POS pairs */
.card-sense-pairs { display: flex; flex-direction: column; gap: 0.5rem; width: 100%; }
.card-sense-pair { display: flex; flex-direction: column; gap: 0.2rem; width: 100%; }

/* Flat domain display: all domains in centered sequence */
.card-domain-flat {
  display: block; width: 100%; text-align: center;
  font-family: 'DM Mono', monospace;
  font-size: 0.81rem; letter-spacing: 0.04em;
  color: var(--gold); background: rgba(160,114,10,0.08);
  border: 1px solid rgba(160,114,10,0.18);
  border-radius: 2px; padding: 0.3rem 0.6rem;
  cursor: pointer; user-select: none;
  transition: background 0.15s, border-color 0.15s;
}
.card-domain-flat:hover { background: rgba(160,114,10,0.15); border-color: rgba(160,114,10,0.5); }
.card-domain-item { white-space: nowrap; }

/* Domain chip */
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
/* Two-tier domain stack: Primary ⌄ Secondaries */
.card-domain-stack {
  display: flex; flex-direction: column; align-items: center;
  width: 100%; cursor: pointer;
  background: rgba(160,114,10,0.08);
  border: 1px solid rgba(160,114,10,0.18);
  border-radius: 2px;
  padding: 0.35rem 0.6rem 0.3rem;
  user-select: none;
  transition: background 0.15s, border-color 0.15s;
}
.card-domain-stack:hover { background: rgba(160,114,10,0.15); border-color: rgba(160,114,10,0.5); }
.card-domain-primary {
  font-family: 'DM Mono', monospace;
  font-size: 0.81rem; color: var(--gold);
  letter-spacing: 0.04em;
}
.card-domain-chevron {
  font-size: 1.1rem; line-height: 0.55;
  color: var(--gold);
  margin: 0 0 9px 0;
}
.card-domain-secondary {
  font-family: 'DM Mono', monospace;
  font-size: 0.81rem; color: rgba(160,114,10,0.75);
  letter-spacing: 0.02em;
}
.card-hdr-mid .card-domain-row { margin-bottom: 0; width: 100%; }
.card-hdr-mid .card-domain {
  display: block; width: 100%; text-align: center;
  font-size: 0.81rem;
  padding: 0.3rem 0.6rem;
}

/* POS summary header chips */
.card-pos-summary { display: flex; flex-direction: column; gap: 0.3rem; }
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

/* Pinyin */
.pinyin {
  font-style: italic; font-family: 'Cormorant Garamond', serif;
  font-size: var(--fs-pinyin, 1.05rem); color: var(--accent);
  letter-spacing: 0.05em;
}
.pinyin-h { writing-mode: horizontal-tb; }
.card-pinyin-row { display: flex; align-items: center; margin-top: 0.15rem; }
</style>
