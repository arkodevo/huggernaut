{{-- Shared POS lookup tables + display functions --}}
{{-- Depends on: posMode (defined by each page before include) --}}
<script>
// DB full name → display-friendly name
// Pattern: Verb - [Action|Process|State] - [transitive|intransitive|separable|attributive|predicative|auxiliary|complement]
const POS_RENAME = {
  'Verb':                                 'Verb - Action - transitive',
  'Intransitive Verb':                    'Verb - Action - intransitive',
  'V-sep / Separable Verb':               'Verb - Action - separable',
  'Process Verb (Transitive)':            'Verb - Process - transitive',
  'Process Verb (Intransitive)':          'Verb - Process - intransitive',
  'Vp-sep / Separable Process Verb':      'Verb - Process - separable',
  'State-Transitive Verb':                'Verb - State - transitive',
  'Stative Verb':                         'Verb - State - intransitive',
  'Vs-sep / Separable Stative Verb':      'Verb - State - separable',
  'Vs-attr / Stative Verb (Attributive)': 'Verb - State - attributive',
  'Vs-pred / Stative Verb (Predicative)': 'Verb - State - predicative',
  'Auxiliary Verb':                       'Verb - auxiliary',
  'Verbal Complement':                    'Verb - complement',
  'Auxiliary':                            'Auxiliary',
  'Interjection':                         'Interjection',
};

// DB full name → intricate abbreviation
// DB slugs (V, Vi, Vp, Vs…) follow TOCFL notation and never change.
// Display abbreviations use the systematic Va-t/Vp-i/Vs-sep scheme:
//   V[type]-[transitivity] — type: a=action, p=process, s=state; transitivity: t, i, sep
const POS_ABBR = {
  'Verb':                                 'Va-t',
  'Intransitive Verb':                    'Va-i',
  'V-sep / Separable Verb':               'Va-sep',
  'Process Verb (Transitive)':            'Vp-t',
  'Process Verb (Intransitive)':          'Vp-i',
  'Vp-sep / Separable Process Verb':      'Vp-sep',
  'State-Transitive Verb':                'Vs-t',
  'Stative Verb':                         'Vs-i',
  'Vs-sep / Separable Stative Verb':      'Vs-sep',
  'Vs-attr / Stative Verb (Attributive)': 'Vs-attr',
  'Vs-pred / Stative Verb (Predicative)': 'Vs-pred',
  'Auxiliary Verb':                       'Vaux',
  'Noun':                                 'N',
  'Measure Word':                         'M',
  'Adverb':                               'Adv',
  'Preposition':                          'Prep',
  'Conjunction':                          'Conj',
  'Particle':                             'Ptc',
  'Determiner':                           'Det',
  'Pronoun':                              'Prn',
  'Number':                               'Num',
  'Verbal Complement':                    'Vcomp',
  'Auxiliary':                            'Aux',
  'Interjection':                         'Intj',
  'Idiomatic Expression':                 'IE',
  'Phrase':                               'Ph',
  'Chengyu':                              'CE',
};

// DB full name → consolidated abbreviation
// Collapses all verb subtypes to three learner-friendly labels: V-t, V-i, V-sep
const POS_ABBR_CONSOLIDATED = {
  'Verb':                                 'V-t',
  'Process Verb (Transitive)':            'V-t',
  'State-Transitive Verb':                'V-t',
  'Auxiliary Verb':                       'V-t',
  'Intransitive Verb':                    'V-i',
  'Process Verb (Intransitive)':          'V-i',
  'Stative Verb':                         'V-i',
  'Vs-attr / Stative Verb (Attributive)': 'V-i',
  'Vs-pred / Stative Verb (Predicative)': 'V-i',
  'Verbal Complement':                    'V-i',
  'V-sep / Separable Verb':               'V-sep',
  'Vp-sep / Separable Process Verb':      'V-sep',
  'Vs-sep / Separable Stative Verb':      'V-sep',
};

// DB full name → consolidated display label (full English, learner-friendly)
// Mirrors POS_ABBR_CONSOLIDATED but returns the readable label instead of the abbreviation.
const POS_DISPLAY_CONSOLIDATED = {
  'Verb':                                 'Verb - transitive',
  'Process Verb (Transitive)':            'Verb - transitive',
  'State-Transitive Verb':                'Verb - transitive',
  'Auxiliary Verb':                       'Verb - transitive',
  'Intransitive Verb':                    'Verb - intransitive',
  'Process Verb (Intransitive)':          'Verb - intransitive',
  'Stative Verb':                         'Verb - intransitive',
  'Vs-attr / Stative Verb (Attributive)': 'Verb - intransitive',
  'Vs-pred / Stative Verb (Predicative)': 'Verb - intransitive',
  'Verbal Complement':                    'Verb - intransitive',
  'V-sep / Separable Verb':               'Verb - separable',
  'Vp-sep / Separable Process Verb':      'Verb - separable',
  'Vs-sep / Separable Stative Verb':      'Verb - separable',
};

// Chinese POS names — shown when learner taps the header or definition POS chip
const POS_ZH = {
  'Verb':                                 '及物動詞',
  'Intransitive Verb':                    '不及物動詞',
  'Process Verb (Intransitive)':          '過程不及物動詞',
  'Vp-sep / Separable Process Verb':      '離合詞',
  'Process Verb (Transitive)':            '過程及物動詞',
  'Stative Verb':                         '狀態動詞',
  'Vs-attr / Stative Verb (Attributive)': '狀態動詞（定語）',
  'Vs-pred / Stative Verb (Predicative)': '狀態動詞（謂語）',
  'Vs-sep / Separable Stative Verb':      '離合詞',
  'State-Transitive Verb':                '狀態及物動詞',
  'Auxiliary Verb':                       '助動詞',
  'V-sep / Separable Verb':              '離合詞',
  'Noun':                                 '名詞',
  'Measure Word':                         '量詞',
  'Adverb':                               '副詞',
  'Preposition':                          '介詞',
  'Conjunction':                          '連詞',
  'Particle':                             '助詞',
  'Determiner':                           '限定詞',
  'Pronoun':                              '代詞',
  'Number':                               '數詞',
  'Verbal Complement':                    '動補結構',
  'Auxiliary':                            '助詞',
  'Interjection':                         '感嘆詞',
  'Idiomatic Expression':                 '慣用語',
  'Phrase':                               '片語',
  'Chengyu':                              '成語',
};

// Returns the display name for a raw DB POS label (always intricate — for the cycle chip's data-full)
function posDisplay(raw) {
  return POS_RENAME[raw] || raw;
}

// Returns the display name respecting current verbPresentation setting.
// In consolidated mode, verb subtypes collapse to "Verb - transitive/intransitive/separable".
// Use this wherever a human-readable label is shown alongside the abbreviation.
function posDisplayLabel(raw) {
  const isConsolidated = typeof verbPresentation !== 'undefined' && verbPresentation === 'consolidated';
  if (isConsolidated && POS_DISPLAY_CONSOLIDATED[raw]) return POS_DISPLAY_CONSOLIDATED[raw];
  return posDisplay(raw);
}

// Returns label based on current posMode (abbr or full) and verbPresentation (intricate or consolidated)
// Falls back to abbr if posMode is not defined (e.g. word-detail page)
function posLabel(raw) {
  if (typeof posMode !== 'undefined' && posMode === 'full') return posDisplay(raw);
  const isConsolidated = typeof verbPresentation !== 'undefined' && verbPresentation === 'consolidated';
  if (isConsolidated && POS_ABBR_CONSOLIDATED[raw]) return POS_ABBR_CONSOLIDATED[raw];
  return POS_ABBR[raw] || raw;
}

// POS group membership
// V is now specifically Verb - transitive, not a catch-all for all verb types.
// V-sep groups Vpsep + Vssep (both displayed as V-sep to learners).
const POS_GROUPS = {};
const POS_SLUG_GROUPS = {
  'V-sep': ['Vpsep', 'Vssep'], // TOCFL distinguishes Vp-sep (13) + Vs-sep (7); displayed as V-sep to learners
};

// Consolidated verb groups — used when verbPresentation === 'consolidated'
// Keys are the filter values shown in the dropdown; values are arrays of full DB POS names.
const POS_CONSOLIDATED_GROUPS = {
  'Transitive Verb': [
    'Verb',
    'Process Verb (Transitive)',
    'State-Transitive Verb',
    'Auxiliary Verb',
  ],
  'Intransitive Verb': [
    'Intransitive Verb',
    'Process Verb (Intransitive)',
    'Stative Verb',
    'Vs-attr / Stative Verb (Attributive)',
    'Vs-pred / Stative Verb (Predicative)',
    'Verbal Complement',
  ],
  'Separable Verb': [
    'V-sep / Separable Verb',
    'Vp-sep / Separable Process Verb',
    'Vs-sep / Separable Stative Verb',
  ],
};

// All full DB verb names — used to exclude verbs from the non-verb POS list
const ALL_VERB_FULL_NAMES = Object.values(POS_CONSOLIDATED_GROUPS).flat();

function posMatchesFilter(pos, filter) {
  if (pos === filter) return true;
  // Consolidated groups (Transitive Verb / Intransitive Verb / Separable Verb)
  const consolidated = POS_CONSOLIDATED_GROUPS[filter];
  if (consolidated && consolidated.includes(pos)) return true;
  // Slug-based groups (V-sep etc.)
  const slugGroup = POS_SLUG_GROUPS[filter];
  if (slugGroup && slugGroup.includes(pos)) return true;
  // Fallback: full-name groups (legacy)
  const group = POS_GROUPS[filter];
  return group ? group.includes(pos) : false;
}

// Populate (or repopulate) the POS refine dropdown.
// Call on init and whenever verbPresentation changes.
function populatePosRefine() {
  const sel = document.getElementById('posRefineSelect');
  if (!sel || typeof WORDS === 'undefined') return;

  const previousVal = (typeof posFilter !== 'undefined') ? posFilter : '';

  // Clear all options except the first ("POS — all")
  while (sel.options.length > 1) sel.remove(1);

  const posOrder = Object.keys(POS_ABBR);
  const seen = new Set();
  WORDS.forEach(w => (w.definitions || []).forEach(d => { if (d.pos) seen.add(d.pos); }));

  if (typeof verbPresentation !== 'undefined' && verbPresentation === 'intricate') {
    // Intricate: show every individual POS present in WORDS
    [...seen]
      .sort((a, b) => posOrder.indexOf(a) - posOrder.indexOf(b))
      .forEach(pos => {
        const opt = document.createElement('option');
        opt.value = pos;
        opt.textContent = (POS_ABBR[pos] || pos) + ' — ' + posDisplay(pos);
        sel.appendChild(opt);
      });
  } else {
    // Consolidated: three verb buckets + individual non-verb POS
    const verbBuckets = [
      { val: 'Transitive Verb',   label: 'V-t \u2014 Verb - transitive' },
      { val: 'Intransitive Verb', label: 'V-i \u2014 Verb - intransitive' },
      { val: 'Separable Verb',    label: 'V-sep \u2014 Verb - separable' },
    ];
    verbBuckets.forEach(bucket => {
      const members = POS_CONSOLIDATED_GROUPS[bucket.val];
      const hasAny = WORDS.some(w => (w.definitions || []).some(d => members.includes(d.pos)));
      if (!hasAny) return;
      const opt = document.createElement('option');
      opt.value = bucket.val;
      opt.textContent = bucket.label;
      sel.appendChild(opt);
    });
    // Non-verb POS (everything not in a consolidated group)
    [...seen]
      .filter(p => !ALL_VERB_FULL_NAMES.includes(p))
      .sort((a, b) => posOrder.indexOf(a) - posOrder.indexOf(b))
      .forEach(pos => {
        const opt = document.createElement('option');
        opt.value = pos;
        opt.textContent = (POS_ABBR[pos] || pos) + ' — ' + posDisplay(pos);
        sel.appendChild(opt);
      });
  }

  // Restore previous selection if still valid; otherwise reset
  if (previousVal && [...sel.options].some(o => o.value === previousVal)) {
    sel.value = previousVal;
  } else if (previousVal) {
    posFilter = '';
    sel.value = '';
    sel.classList.remove('active');
    if (typeof syncRefineReset === 'function') syncRefineReset();
  }
}

// ── Pinyin conversion ──────────────────────────────────────────────────────
// Mirrors App\Helpers\PinyinHelper::toMarked() for client-side rendering.
// DB stores numeric-tone, no separator: "biao3bai2"
// toMarked() returns tone-marked, space-separated:  "biǎo bái"
//
// Tone-mark placement (official Hànyǔ Pīnyīn standard):
//   1. a or e → always takes the mark
//   2. ou     → o takes the mark
//   3. otherwise, last vowel takes the mark
// Tone 5 (neutral) = no digit → no diacritic
const PINYIN_MARKS = {
  a: ['ā','á','ǎ','à','a'], e: ['ē','é','ě','è','e'],
  i: ['ī','í','ǐ','ì','i'], o: ['ō','ó','ǒ','ò','o'],
  u: ['ū','ú','ǔ','ù','u'], v: ['ǖ','ǘ','ǚ','ǜ','ü'],
  ü: ['ǖ','ǘ','ǚ','ǜ','ü'],
};
const PINYIN_VOWELS = new Set(['a','e','i','o','u','ü','v']);

function _markSyllable(syl) {
  const m = syl.match(/^([a-züv]+)([1-5])$/);
  if (!m) return syl;                       // neutral tone — no mark
  const [, letters, toneStr] = m;
  const tone = parseInt(toneStr, 10);
  if (tone === 5) return letters;

  // Rule 1: a or e
  for (const v of ['a', 'e']) {
    if (letters.includes(v)) return letters.replace(v, PINYIN_MARKS[v][tone - 1]);
  }
  // Rule 2: ou → o
  if (letters.includes('ou')) return letters.replace('o', PINYIN_MARKS.o[tone - 1]);
  // Rule 3: last vowel
  for (let i = letters.length - 1; i >= 0; i--) {
    const ch = letters[i];
    if (PINYIN_VOWELS.has(ch)) {
      return letters.slice(0, i) + PINYIN_MARKS[ch][tone - 1] + letters.slice(i + 1);
    }
  }
  return letters;
}

/**
 * Convert numeric-tone pinyin to tone-marked display form.
 * pinyinToMarked('biao3bai2')  → 'biǎo bái'
 * pinyinToMarked('ba1ba')      → 'bā ba'
 * Pass sep='' for no space between syllables.
 */
function pinyinToMarked(numeric, sep = ' ') {
  if (!numeric) return '';
  return numeric.toLowerCase()
    .split(/(?<=[1-5])/)
    .filter(Boolean)
    .map(_markSyllable)
    .join(sep);
}

/**
 * Format pinyin respecting the current pinyinDisplay setting.
 * pinyinDisplay === 'accented' (default) → tone-marked:  'biǎo bái'
 * pinyinDisplay === 'numeric'            → spaced digits: 'biao3 bai2'
 * Falls back to accented if pinyinDisplay is not defined.
 */
function formatPinyin(raw, sep = ' ') {
  if (!raw) return '';
  const isNumeric = typeof pinyinDisplay !== 'undefined' && pinyinDisplay === 'numeric';
  if (isNumeric) {
    return raw.toLowerCase()
      .split(/(?<=[1-5])/)
      .filter(Boolean)
      .join(' ');
  }
  return pinyinToMarked(raw, sep);
}

// POS alignment icon — appended after POS label inside the chip
// 🤓 full (all three sources agree) · 🤨 partial · 😵‍💫 disputed
const POS_ALIGN_ICON = { 'full': '🤓', 'partial': '🤨', 'disputed': '😵‍💫' };
function posAlignIcon(alignment) {
  return alignment ? (POS_ALIGN_ICON[alignment] || '') : '';
}

// Per-definition POS chip — 3-way cycle: abbr → full EN name → Chinese name → abbr
function cyclePosChip(e, chip) {
  e.stopPropagation();
  e.preventDefault();
  const order = ['abbr', 'full', 'zh'];
  const current = chip.dataset.state || 'abbr';
  const next = order[(order.indexOf(current) + 1) % 3];
  chip.dataset.state = next;
  // Preserve alignment icon child span before overwriting text content
  const iconEl = chip.querySelector('.pos-align-icon');
  chip.textContent = chip.dataset[next] || chip.dataset.abbr;
  if (iconEl) chip.appendChild(iconEl);
}

// Language toggle for domain chip and header POS chips.
// Cycles: preferred language ↔ Chinese.
// Preferred language defaults to 'en' unless uiMode is zh/zh-icon/zh-only.
function toggleLangChip(e, chip) {
  e.stopPropagation();
  const preferred = (typeof uiMode !== 'undefined' && (uiMode === 'zh-icon' || uiMode === 'zh-only')) ? 'zh' : 'en';
  const alt = preferred === 'zh' ? 'en' : 'zh';
  const current = chip.dataset.state || preferred;
  const next = current === preferred ? alt : preferred;
  chip.dataset.state = next;
  // Flat domain display: toggle all domain-item children together
  if (chip.classList.contains('card-domain-flat')) {
    const items = chip.querySelectorAll('.card-domain-item');
    const sep = items.length > 1 ? ', ' : '';
    items.forEach((item, i) => {
      item.textContent = (next === 'zh' ? item.dataset.zh : item.dataset.en) || item.dataset.en;
    });
    return;
  }
  // Two-tier domain stack: update primary + secondary child spans
  if (chip.classList.contains('card-domain-stack')) {
    const pSpan = chip.querySelector('.card-domain-primary');
    const sSpan = chip.querySelector('.card-domain-secondary');
    if (pSpan) pSpan.textContent = (next === 'zh' ? chip.dataset.pZh : chip.dataset.pEn) || chip.dataset.pEn;
    if (sSpan) sSpan.textContent = (next === 'zh' ? chip.dataset.sZh : chip.dataset.sEn) || chip.dataset.sEn;
  } else {
    // Preserve alignment icon child span before overwriting text content
    const iconEl = chip.querySelector('.pos-align-icon');
    chip.textContent = chip.dataset[next] || chip.dataset.en;
    if (iconEl) chip.appendChild(iconEl);
  }
}

</script>
