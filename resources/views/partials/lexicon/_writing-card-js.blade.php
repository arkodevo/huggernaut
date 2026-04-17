{{-- Shared writing-card renderer — single source of truth.

    Called from:
      - Workshop deck (_workshop-js.blade.php → wsRenderSavedDeck)
      - LWP Community section (word-detail.blade.php → renderPublicWritings, renderMyWritings)

    Modularity rule (feedback_modularity.md):
      One writing card, one renderer. Bug fixes, design changes, new badges
      — all happen here. Callers normalize their data into the shared shape
      and pass opts for variant behavior. No copy-paste.

    Input shape (item):
      {
        cn: string,                  // Chinese text
        en: string,                  // English translation
        source: string,              // '師父 verified' | '師父 generated' | 'My writing' | '⚠ Unverified draft'
        date: string,                // human-readable date
        pos: string,                 // full POS name (e.g. 'Stative Verb')
        posAbbr: string,             // pre-computed POS abbr (optional; falls back to POS_ABBR[pos])
        target: { traditional, simplified },  // word being taught (for segmentation)
        assessedLevel?: string,      // 'beginner'|...|'fluent'
        assessedMastery?: string,    // 'seed'|...|'fruit'
        grammarPatterns?: Array,     // [{slug, status, note}]
        feedback?: string,           // 師父 critique
        originalCn?: string,         // user's original submission (if corrected)
        masteryGuidance?: string,
        author?: string,             // display name for community views
        isMine?: bool,               // render 'You' instead of author
        isPublic?: bool,             // when false + isMine, show private chip
      }

    Options (opts):
      vertical:       bool   (default: read textDir)
      showDate:       bool   (default true)
      showAuthor:     bool   (default false — workshop doesn't show author)
      showVisibility: bool   (default false — only community 'my writings' view)
      rightAction:    string (HTML for a right-side action button, e.g. delete)
      extraClass:     string (additional class on .ex-sent root)
      dataAttrs:      object (extra data-* attributes on root)
      segFn:          function (defaults to window.segmentedHTML)
--}}
<script>
function renderWritingCard(item, opts) {
  opts = opts || {};
  const vertical       = typeof opts.vertical === 'boolean' ? opts.vertical : (typeof textDir !== 'undefined' && textDir === 'vertical');
  const showDate       = opts.showDate !== false;
  const showAuthor     = opts.showAuthor === true;
  const showVisibility = opts.showVisibility === true;
  const rightAction    = opts.rightAction || '';
  const extraClass     = opts.extraClass || '';
  const dataAttrs      = opts.dataAttrs || {};
  const segFn          = opts.segFn || (typeof segmentedHTML === 'function' ? segmentedHTML : null);
  const zhMode         = (typeof langMode !== 'undefined') && langMode === 'zh';

  // POS chip — prefer precomputed abbr, else map from full POS name
  const posAbbr = item.posAbbr || (item.pos && typeof POS_ABBR !== 'undefined' ? (POS_ABBR[item.pos] || item.pos) : (item.pos || ''));
  const posChip = posAbbr ? `<span class="ex-sent-pos">${posAbbr}</span>` : '';

  // Source badge (師父 verified / generated / unverified)
  let sourceChip = '';
  let sourceType = 'mine';
  if (item.source === '師父 generated') {
    sourceChip = '<span class="ws-shifu-chip">🙏 師父 generated</span>';
    sourceType = 'shifu';
  } else if (item.source === '師父 verified') {
    sourceChip = '<span class="ws-shifu-chip">👏 師父 verified</span>';
  } else if (item.source && item.source.indexOf('Unverified') === 0 || (item.source && item.source.indexOf('未驗證') === 0)) {
    sourceChip = `<span class="ws-shifu-chip ws-shifu-chip-warn">${item.source}</span>`;
  } else if (item.source === '師父 needs work' || (item.source && item.source.indexOf('⚠') === 0)) {
    sourceChip = `<span class="ws-shifu-chip ws-shifu-chip-warn">${item.source}</span>`;
  }

  // Level + mastery row (conditional)
  const hasAssess = item.assessedLevel || item.assessedMastery;
  const levelChip = item.assessedLevel && typeof wsLevelLabel === 'function'
    ? (() => { const l = wsLevelLabel(item.assessedLevel); return l ? `<span class="ws-level-chip">${l.icon} ${zhMode ? l.zh : l.en}</span>` : ''; })()
    : '';
  const masteryChip = item.assessedMastery && typeof wsMasteryLabel === 'function'
    ? (() => { const m = wsMasteryLabel(item.assessedMastery); return m ? `<span class="ws-mastery-chip">${m.icon} ${zhMode ? m.zh : m.en}</span>` : ''; })()
    : '';

  // Grammar patterns row (rare — from Workshop save)
  let grammarRow = '';
  if (Array.isArray(item.grammarPatterns) && item.grammarPatterns.length && typeof WS_GRAMMAR_PATTERNS !== 'undefined') {
    const chips = item.grammarPatterns.map(gp => {
      const ref = WS_GRAMMAR_PATTERNS.find(p => p.slug === gp.slug);
      if (!ref) return '';
      const icon = gp.status === 'correct' ? '✓' : gp.status === 'almost' ? '△' : '✗';
      const label = zhMode ? ref.zh_label : ref.en_label;
      const noteAttr = gp.note ? ` title="${String(gp.note).replace(/"/g,'&quot;')}"` : '';
      return `<span class="ws-grammar-chip ws-grammar-${gp.status || 'correct'}"${noteAttr}>${icon} ${label}</span>`;
    }).filter(Boolean).join(' ');
    if (chips) {
      grammarRow = `<div class="ws-grammar-patterns ws-saved-grammar"><span class="ws-grammar-label">${zhMode ? '句型' : 'Grammar'}:</span> ${chips}</div>`;
    }
  }

  // Feedback disclosure (師父 critique + original submission)
  let feedbackBlock = '';
  if (item.feedback || item.originalCn) {
    const originalBlock = item.originalCn
      ? `<div class="ws-original-submission"><div class="ws-original-label">${zhMode ? '你的原稿' : 'Your original'}:</div><div class="ws-original-text">${item.originalCn}</div></div>`
      : '';
    const masteryBlock = item.masteryGuidance
      ? `<div class="ws-mastery-guidance">${item.masteryGuidance}</div>`
      : '';
    feedbackBlock = `<details class="ws-saved-feedback"><summary>師父 feedback</summary><div class="ws-saved-feedback-text">${originalBlock}${item.feedback || ''}${masteryBlock}</div></details>`;
  }

  // Chinese body — segmented if segFn provided
  const target = item.target || {};
  const zhBody = segFn
    ? segFn(item.cn || '', { traditional: target.traditional || '', simplified: target.simplified || '' })
    : (item.cn || '');

  // Meta row (author + date + visibility chip + right action)
  const metaParts = [];
  if (showAuthor) {
    const authorLabel = item.isMine
      ? `<span class="ws-saved-author">— ${zhMode ? '您' : 'You'}</span>`
      : `<span class="ws-saved-author">— ${item.author || 'Anonymous'}</span>`;
    metaParts.push(authorLabel);
  }
  if (showDate && item.date) {
    metaParts.push(`<span class="ws-saved-date">${item.date}</span>`);
  }
  if (showVisibility && item.isMine && item.isPublic === false) {
    metaParts.push(`<span class="ws-visibility-chip">${zhMode ? '私人' : 'private'}</span>`);
  }
  if (rightAction) {
    metaParts.push(rightAction);
  }
  const metaRow = metaParts.length ? `<div class="ws-saved-meta">${metaParts.join('')}</div>` : '';

  // Root-level data attributes
  const dataAttrStr = Object.entries(dataAttrs)
    .map(([k, v]) => ` data-${k}="${String(v).replace(/"/g,'&quot;')}"`)
    .join('');

  return `
    <div class="ex-sent${vertical ? ' vertical' : ''} saved-writing${extraClass ? ' ' + extraClass : ''}" data-source-type="${sourceType}"${dataAttrStr}>
      <div class="ws-saved-writing-chips">
        ${posChip}
        ${sourceChip}
      </div>
      ${hasAssess ? `<div class="ws-saved-writing-chips ws-assess-row">${levelChip}${masteryChip}</div>` : ''}
      ${grammarRow}
      <div class="ex-sent-body">
        <div class="ex-sent-cn">${zhBody}</div>
        <div class="ex-sent-en">${item.en || ''}</div>
        ${feedbackBlock}
        ${metaRow}
      </div>
    </div>
  `;
}
</script>
