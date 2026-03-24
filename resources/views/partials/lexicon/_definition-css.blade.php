{{-- Shared POS chip, definition, formula, usage note styles --}}
<style>
.card-def-row { display: block; margin-bottom: 0.3rem; }
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
  margin-top: 0.45rem;
}

.card-formula {
  font-size: var(--fs-formula, 1rem);
  background: rgba(98,64,200,0.05);
  border: 1px solid rgba(98,64,200,0.15);
  padding: 0.3rem 0.6rem; border-radius: 2px;
  color: var(--accent);
  font-family: 'DM Mono', monospace;
  display: inline-block; margin-top: 0.5rem;
}
</style>
