{{-- Shared POS lookup tables + display functions --}}
{{-- Depends on: posMode (defined by each page before include) --}}
<script>
// DB full name → display-friendly name
const POS_RENAME = {
  'Verb':                                 'Verb (all)',
  'Intransitive Verb':                    'Verb - intransitive',
  'Process Verb':                         'Verb - process',
  'Vp-sep / Separable Process Verb':      'Verb - process (sep.)',
  'Process Verb (Telic)':                 'Verb - process (telic)',
  'Stative Verb':                         'Verb - stative',
  'Vs-attr / Stative Verb (Attributive)': 'Verb - stative (attr.)',
  'Vs-pred / Stative Verb (Predicative)': 'Verb - stative (pred.)',
  'Vs-sep / Separable Stative Verb':      'Verb - stative (sep.)',
  'State-Transitive Verb':                'Verb - state-transitive',
  'Auxiliary Verb':                       'Verb - auxiliary',
  'V-sep / Separable Verb':               'Verb - separable',
};

// DB full name → abbreviation
const POS_ABBR = {
  'Verb':                                 'V',
  'Intransitive Verb':                    'Vi',
  'Process Verb':                         'Vp',
  'Vp-sep / Separable Process Verb':      'Vp-sep',
  'Process Verb (Telic)':                 'Vpt',
  'Stative Verb':                         'Vs',
  'Vs-attr / Stative Verb (Attributive)': 'Vs-attr',
  'Vs-pred / Stative Verb (Predicative)': 'Vs-pred',
  'Vs-sep / Separable Stative Verb':      'Vs-sep',
  'State-Transitive Verb':                'Vst',
  'Auxiliary Verb':                       'Vaux',
  'V-sep / Separable Verb':               'V-sep',
  'Noun':                                 'N',
  'Measure Word':                         'M',
  'Adverb':                               'Adv',
  'Preposition':                          'Prep',
  'Conjunction':                          'Conj',
  'Particle':                             'Ptcl',
  'Determiner':                           'Det',
  'Pronoun':                              'Prn',
  'Number':                               'Num',
  'Idiomatic Expression':                 'IE',
  'Phrase':                               'Ph',
};

// Chinese POS names — shown when learner taps the header or definition POS chip
const POS_ZH = {
  'Verb':                                 '動詞（全部）',
  'Intransitive Verb':                    '不及物動詞',
  'Process Verb':                         '過程動詞',
  'Vp-sep / Separable Process Verb':      '離合過程動詞',
  'Process Verb (Telic)':                 '完結動詞',
  'Stative Verb':                         '狀態動詞',
  'Vs-attr / Stative Verb (Attributive)': '狀態動詞（定語）',
  'Vs-pred / Stative Verb (Predicative)': '狀態動詞（謂語）',
  'Vs-sep / Separable Stative Verb':      '離合狀態動詞',
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
  'Idiomatic Expression':                 '成語',
  'Phrase':                               '詞組',
};

// Returns the display name for a raw DB POS label
function posDisplay(raw) {
  return POS_RENAME[raw] || raw;
}

// Returns label based on current posMode (abbr or full)
// Falls back to abbr if posMode is not defined (e.g. word-detail page)
function posLabel(raw) {
  if (typeof posMode !== 'undefined' && posMode === 'full') return posDisplay(raw);
  return POS_ABBR[raw] || raw;
}

// POS group membership — selecting 'Verb' matches all verb subtypes
const POS_GROUPS = {
  'Verb': Object.keys(POS_ABBR).filter(k => k !== 'Verb' && POS_ABBR[k].startsWith('V')),
};

function posMatchesFilter(pos, filter) {
  if (pos === filter) return true;
  const group = POS_GROUPS[filter];
  return group ? group.includes(pos) : false;
}

// Per-definition POS chip — 3-way cycle: abbr → full EN name → Chinese name → abbr
function cyclePosChip(e, chip) {
  e.stopPropagation();
  e.preventDefault();
  const order = ['abbr', 'full', 'zh'];
  const current = chip.dataset.state || 'abbr';
  const next = order[(order.indexOf(current) + 1) % 3];
  chip.dataset.state = next;
  chip.textContent = chip.dataset[next] || chip.dataset.abbr;
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
  // Two-tier domain stack: update primary + secondary child spans
  if (chip.classList.contains('card-domain-stack')) {
    const pSpan = chip.querySelector('.card-domain-primary');
    const sSpan = chip.querySelector('.card-domain-secondary');
    if (pSpan) pSpan.textContent = (next === 'zh' ? chip.dataset.pZh : chip.dataset.pEn) || chip.dataset.pEn;
    if (sSpan) sSpan.textContent = (next === 'zh' ? chip.dataset.sZh : chip.dataset.sEn) || chip.dataset.sEn;
  } else {
    chip.textContent = chip.dataset[next] || chip.dataset.en;
  }
}

</script>
