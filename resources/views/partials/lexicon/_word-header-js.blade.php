{{-- Shared word header JS: toggleSecondaryChar for ⇌ switch button --}}
<script>
function toggleSecondaryChar(e, btn) {
  e.stopPropagation();
  const secondary = btn.dataset.secondary;
  if (!secondary) return;
  const container = btn.closest('.card-hanzi');
  const existing = container.querySelector('.hanzi-secondary');
  if (existing) {
    existing.classList.add('leaving');
    existing.addEventListener('animationend', () => existing.remove(), { once: true });
    btn.style.opacity = '0.45';
  } else {
    const span = document.createElement('span');
    span.className = 'hanzi-secondary entering';
    span.textContent = secondary;
    container.appendChild(span);
    span.addEventListener('animationend', () => span.classList.remove('entering'), { once: true });
    btn.style.opacity = '1';
  }
}
</script>
