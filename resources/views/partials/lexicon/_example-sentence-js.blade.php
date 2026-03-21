{{-- Shared example sentence renderer
     Expects: segmentedHTML(cn, wordContext), POS_ABBR, textDir to be defined by host page --}}
<script>
/**
 * Render a single example sentence card.
 * @param {Object} ex         - { cn, en, source?, theme? }
 * @param {Object} opts
 * @param {string} opts.pos   - POS abbreviation slug (e.g. 'Vi')
 * @param {boolean} opts.vertical - true → vertical layout
 * @param {Function} opts.segFn - segmentation function (cn, ctx) → HTML
 * @param {Object}  [opts.segCtx] - context passed to segFn (word object)
 * @param {string}  [opts.saveId] - if set, renders a save button with this ID
 * @returns {string} HTML
 */
function renderExSentence(ex, opts = {}) {
  const vertical = opts.vertical || false;
  const posChip = opts.pos
    ? `<span class="ex-sent-pos">${(typeof POS_ABBR !== 'undefined' && POS_ABBR[opts.pos]) || opts.pos}</span>`
    : '';
  const cnHTML = opts.segFn ? opts.segFn(ex.cn, opts.segCtx) : ex.cn;
  const saveBtn = opts.saveId
    ? `<button class="ex-sent-save" id="${opts.saveId}">Save</button>`
    : '';
  const themeTag = ex.theme
    ? `<span class="ex-sent-theme">${ex.theme}</span>`
    : '';
  const sourceTag = ex.source
    ? `<div class="ex-sent-source">${ex.source}</div>`
    : '';

  return `<div class="ex-sent${vertical ? ' vertical' : ''}">
    ${posChip}
    <div class="ex-sent-body">
      <div class="ex-sent-cn">${cnHTML}</div>
      <div class="ex-sent-en">${ex.en}</div>
      ${themeTag}${sourceTag}
    </div>
    ${saveBtn}
  </div>`;
}
</script>
