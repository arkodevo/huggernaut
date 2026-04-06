{{-- Admin attribute chip styles — mirrors learner-side _attr-chip-css.blade.php --}}
<style>
.admin-attrs {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.4rem;
}
.card-attr {
  display: flex; flex-direction: column;
  border-radius: 3px; overflow: hidden;
  border: 1px solid var(--border, #e5e7eb);
}
.card-attr-header {
  font-size: 0.65rem; letter-spacing: 0.18em; text-transform: uppercase;
  font-family: 'DM Mono', ui-monospace, monospace;
  padding: 0.25rem 0.55rem 0.2rem;
  border-bottom: 1px solid var(--border, #e5e7eb);
  white-space: nowrap;
}
.card-attr-value {
  display: flex; flex-direction: row; align-items: center; gap: 0.35rem;
  padding: 0.3rem 0.55rem;
  font-family: 'DM Mono', ui-monospace, monospace; font-size: 0.82rem;
}
.card-attr-value .attr-icon { font-size: 1.26rem; line-height: 1; flex-shrink: 0; }
.card-attr-value.multi { flex-direction: column; align-items: flex-start; gap: 0.25rem; }
.attr-val-item { display: inline-flex; align-items: center; gap: 0.35rem; flex-shrink: 0; white-space: nowrap; }

/* Per-attribute colours */
.card-attr.attr-register   { background: rgba(18,168,78,0.05);  border-color: rgba(18,168,78,0.2); }
.card-attr.attr-register   .card-attr-header { color: #12a84e; background: rgba(18,168,78,0.08); border-color: rgba(18,168,78,0.15); }
.card-attr.attr-register   .card-attr-value  { color: #12a84e; }

.card-attr.attr-connotation.conno-pos { background: rgba(40,120,220,0.07);  border-color: rgba(40,120,220,0.3); }
.card-attr.attr-connotation.conno-pos .card-attr-header { color: #1e5cb0; background: rgba(40,120,220,0.1); border-color: rgba(40,120,220,0.2); }
.card-attr.attr-connotation.conno-pos .card-attr-value  { color: #1e5cb0; }
.card-attr.attr-connotation.conno-neg { background: rgba(30,80,180,0.07);   border-color: rgba(30,80,180,0.3); }
.card-attr.attr-connotation.conno-neg .card-attr-header { color: #1a3c90; background: rgba(30,80,180,0.1); border-color: rgba(30,80,180,0.2); }
.card-attr.attr-connotation.conno-neg .card-attr-value  { color: #1a3c90; }
.card-attr.attr-connotation.conno-neu { background: rgba(60,100,190,0.07);  border-color: rgba(60,100,190,0.3); }
.card-attr.attr-connotation.conno-neu .card-attr-header { color: #2c58a0; background: rgba(60,100,190,0.1); border-color: rgba(60,100,190,0.2); }
.card-attr.attr-connotation.conno-neu .card-attr-value  { color: #2c58a0; }
.card-attr.attr-connotation.conno-ctx { background: rgba(36,104,208,0.07);  border-color: rgba(36,104,208,0.3); }
.card-attr.attr-connotation.conno-ctx .card-attr-header { color: #2468d0; background: rgba(36,104,208,0.1); border-color: rgba(36,104,208,0.2); }
.card-attr.attr-connotation.conno-ctx .card-attr-value  { color: #2468d0; }

.card-attr.attr-channel   { background: rgba(208,48,48,0.05);   border-color: rgba(208,48,48,0.2); }
.card-attr.attr-channel   .card-attr-header { color: #d03030; background: rgba(208,48,48,0.08); border-color: rgba(208,48,48,0.15); }
.card-attr.attr-channel   .card-attr-value  { color: #d03030; }

.card-attr.attr-tocfl     { background: rgba(196,168,8,0.05);   border-color: rgba(196,168,8,0.2); }
.card-attr.attr-tocfl     .card-attr-header { color: #c4a808; background: rgba(196,168,8,0.08); border-color: rgba(196,168,8,0.15); }
.card-attr.attr-tocfl     .card-attr-value  { color: #c4a808; }

.card-attr.attr-dimension { background: rgba(212,120,24,0.05);  border-color: rgba(212,120,24,0.2); }
.card-attr.attr-dimension .card-attr-header { color: #d47818; background: rgba(212,120,24,0.08); border-color: rgba(212,120,24,0.15); }
.card-attr.attr-dimension .card-attr-value  { color: #d47818; }

.card-attr.attr-intensity { background: rgba(160,96,42,0.05);   border-color: rgba(160,96,42,0.2); }
.card-attr.attr-intensity .card-attr-header { color: #a0602a; background: rgba(160,96,42,0.08); border-color: rgba(160,96,42,0.15); }
.card-attr.attr-intensity .card-attr-value  { color: #a0602a; }

.card-attr.attr-semantic-mode { background: rgba(128,90,160,0.05); border-color: rgba(128,90,160,0.2); }
.card-attr.attr-semantic-mode .card-attr-header { color: #805aa0; background: rgba(128,90,160,0.08); border-color: rgba(128,90,160,0.15); }
.card-attr.attr-semantic-mode .card-attr-value  { color: #805aa0; }

.card-attr.attr-sensitivity { background: rgba(100,120,140,0.05); border-color: rgba(100,120,140,0.2); }
.card-attr.attr-sensitivity .card-attr-header { color: #64788c; background: rgba(100,120,140,0.08); border-color: rgba(100,120,140,0.15); }
.card-attr.attr-sensitivity .card-attr-value  { color: #64788c; }
</style>
