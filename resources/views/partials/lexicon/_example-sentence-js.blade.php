{{-- Shared example sentence renderer
     Expects: segmentedHTML(cn, wordContext), POS_ABBR, textDir to be defined by host page --}}
<script>
/**
 * Render a single example sentence card.
 * @param {Object} ex         - { cn, en, translations?, source?, theme? }
 * @param {Object} opts
 * @param {string} opts.pos   - POS abbreviation slug (e.g. 'Vi')
 * @param {boolean} opts.vertical - true → vertical layout
 * @param {Function} opts.segFn - segmentation function (cn, ctx) → HTML
 * @param {Object}  [opts.segCtx] - context passed to segFn (word object)
 * @param {string}  [opts.saveId] - if set, renders a save button with this ID
 * @returns {string} HTML
 */
function renderExSentence(ex, opts = {}) {
  if (!ex || !ex.cn) return '';
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

  // Resolve translation based on langMode (language IDs: 1=EN, 2=ZH-TW)
  const lm = (typeof langMode !== 'undefined') ? langMode : 'en';
  const trans = ex.translations || {};
  const enText = trans['1'] || ex.en || '';

  // For examples, translation is always non-Chinese (source is Chinese).
  // 'en' mode: show EN; 'zh' mode: hide translation; 'both' mode: show EN.
  const translationHTML = (lm === 'zh')
    ? ''
    : (enText ? `<div class="ex-sent-en">${enText}</div>` : '');

  // 🔊 audio button — only rendered when ex.id exists (dictionary examples,
  // not user-generated). Shared audioButton() owns the chip; we just place it.
  const audioBtn = (ex.id && typeof audioButton === 'function')
    ? ` ${audioButton('examples', ex.id, ex.hasAudio || {}, ex.cn)}`
    : '';

  return `<div class="ex-sent${vertical ? ' vertical' : ''}">
    ${posChip}
    <div class="ex-sent-body">
      <div class="ex-sent-cn">${cnHTML}</div>
      ${translationHTML}
      ${themeTag}${sourceTag}
      ${audioBtn ? `<div class="ex-sent-audio">${audioBtn}</div>` : ''}
    </div>
    ${saveBtn}
  </div>`;
}
</script>
