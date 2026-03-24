{{-- Shared site footer --}}
<style>
.site-footer {
  border-top: 1px solid var(--border);
  padding: 1.5rem 1.2rem;
  margin-top: 2rem;
  text-align: center;
  font-family: 'DM Mono', monospace;
  font-size: 0.6rem;
  letter-spacing: 0.06em;
  color: var(--dim);
  opacity: 0.7;
}
.site-footer a {
  color: var(--accent);
  text-decoration: none;
  transition: opacity 0.15s;
}
.site-footer a:hover { opacity: 0.7; }
.site-footer-sep { margin: 0 0.4rem; }
</style>

<footer class="site-footer">
  &copy; {{ date('Y') }} 流動 Living Lexicon
  <span class="site-footer-sep">&middot;</span>
  <a href="mailto:support@livinglexicon.com">Help</a>
  <span class="site-footer-sep">&middot;</span>
  <a href="#">Privacy</a>
</footer>
