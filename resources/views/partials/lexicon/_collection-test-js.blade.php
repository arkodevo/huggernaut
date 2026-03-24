{{-- Collection Testing JS — @included in collection-test.blade.php --}}
<script>
// ── CONSTANTS ────────────────────────────────────────────────────────────────
const CT_POS_ABBR = {
  'V': 'Verb', 'Vi': 'Verb - intransitive', 'Vp': 'Process Verb',
  'N': 'Noun', 'M': 'Measure Word', 'Adv': 'Adverb',
  'Prep': 'Preposition', 'Conj': 'Conjunction', 'Ptc': 'Particle',
  'Det': 'Determiner', 'Prn': 'Pronoun', 'Num': 'Number',
  'IE': 'Idiomatic Expression', 'Ph': 'Phrase',
  'Vs': 'Stative Verb', 'Vaux': 'Auxiliary Verb', 'Vsep': 'Separable Verb',
};

const CT_ATTR_ENUMS = {
  register:    ['neutral', 'literary', 'formal', 'informal', 'colloquial', 'slang'],
  connotation: ['positive', 'negative', 'neutral', 'contextual'],
  channel:     ['fluid', 'spoken-only', 'spoken-dominant', 'written-dominant', 'written-only'],
  dimension:   ['abstract', 'concrete', 'internal', 'external', 'fluid'],
  intensity:   [1, 2, 3, 4, 5],
};

const CT_ATTR_ICONS = {
  register:    { neutral: '🦗', literary: '🦋', formal: '🐝', informal: '🐛', colloquial: '🐜', slang: '🕷️' },
  connotation: { positive: '☀️', negative: '⛈️', neutral: '🌤️', contextual: '🌦️' },
  channel:     { fluid: '🐉', 'spoken-only': '🦜', 'spoken-dominant': '🦜', 'written-dominant': '🐍', 'written-only': '🐍' },
  intensity:   { 1: '🌱', 2: '🌸', 3: '💐', 4: '🌺', 5: '🔥' },
};

const CT_MODES = [
  { key: 'domain',     label: 'Domain',            desc: 'You will be shown a word and asked to identify its subject domain.' },
  { key: 'pos',        label: 'Part of Speech',    desc: 'You will be given a selection of Parts of Speech \u2014 select all that apply.' },
  { key: 'pinyin',     label: 'Pinyin',             desc: 'You will be shown a word and asked to select its correct pronunciation.' },
  { key: 'definition', label: 'Definition',         desc: 'You will be shown a word and asked to select its correct definition.' },
  { key: 'attribute',  label: 'Attribute',           desc: 'You will be tested on a word\u2019s register, connotation, channel, dimension, or intensity.' },
  { key: 'usage',      label: 'Writing with \u5e2b\u7236', desc: 'You will be presented with a word that you must use to compose a writing. \u5e2b\u7236 will grade your writing and give you brief feedback. You can try submitting your writing three times.' },
];

const CT_ATTR_CHIPS = [
  { slug: 'register',    label: 'Register' },
  { slug: 'connotation', label: 'Connotation' },
  { slug: 'channel',     label: 'Channel' },
  { slug: 'dimension',   label: 'Dimension' },
  { slug: 'intensity',   label: 'Intensity' },
];

// ── UTILITIES ────────────────────────────────────────────────────────────────
function ctCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function ctShuffle(arr) {
  const a = [...arr];
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

function ctEsc(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

function ctEl(id) { return document.getElementById(id); }

// ── STATE ────────────────────────────────────────────────────────────────────
let ctState = {
  mode: null,           // 'domain' | 'pos' | 'pinyin' | 'definition' | 'attribute' | 'usage'
  attrSlug: null,       // for attribute mode: 'register' | 'connotation' | etc
  senses: [],           // shuffled copy of CT_SENSES
  currentIndex: 0,
  testId: null,
  scores: { clean: 0, assisted: 0, learning: 0 },
  answers: [],          // { senseId, chosen, correct, tier, hintsUsed }
  hintsUsed: [],        // slugs for current question
  retryQueue: [],       // senses to retry
  isRetry: false,
  locked: false,        // prevent double-click during transition
  usageAttempts: 0,     // attempts for current usage question (max 3)
};

// ── SETUP SCREEN ─────────────────────────────────────────────────────────────
function ctSetup() {
  const app = ctEl('ctApp');
  if (!app) return;

  let html = '<div class="ct-setup">';
  html += `<div class="ct-collection-name">${ctEsc(CT_COLLECTION.name)}</div>`;
  html += `<div class="ct-collection-count">${CT_SENSES.length} word${CT_SENSES.length !== 1 ? 's' : ''}</div>`;
  html += '<div class="ct-setup-question">What would you like to be tested on?</div>';

  // Mode list
  html += '<div class="ct-mode-list">';
  CT_MODES.forEach(m => {
    html += `<button class="ct-mode-btn" data-mode="${m.key}" onclick="ctSelectMode('${m.key}')">`;
    html += `<span class="ct-mode-label">${m.label}</span>`;
    html += '</button>';
    // Attribute sub-selector sits right beneath the Attribute button
    if (m.key === 'attribute') {
      html += '<div id="ctAttrSub" class="ct-attr-sub" style="display:none;">';
      CT_ATTR_CHIPS.forEach(a => {
        html += `<button class="ct-attr-chip" data-attr="${a.slug}" onclick="ctSelectAttr('${a.slug}')">${a.label}</button>`;
      });
      html += '</div>';
    }
  });
  html += '</div>';

  // Description area (shown when a mode is selected)
  html += '<div id="ctModeDesc" class="ct-mode-desc" style="display:none;"></div>';

  // Start button
  html += '<button class="ct-start-btn" id="ctStartBtn" disabled onclick="ctStartTest()">Start Test</button>';
  html += '</div>';

  app.innerHTML = html;
}

function ctSelectMode(mode) {
  ctState.mode = mode;
  ctState.attrSlug = null;

  // Highlight selected mode button
  document.querySelectorAll('.ct-mode-btn').forEach(btn => {
    btn.classList.toggle('selected', btn.dataset.mode === mode);
  });

  // Show description
  const descEl = ctEl('ctModeDesc');
  const modeObj = CT_MODES.find(m => m.key === mode);
  if (descEl && modeObj) {
    descEl.textContent = modeObj.desc;
    descEl.style.display = 'block';
  }

  // Show/hide attribute sub-selector
  const sub = ctEl('ctAttrSub');
  if (sub) {
    sub.style.display = mode === 'attribute' ? 'flex' : 'none';
    // Clear sub-selection when switching away
    if (mode !== 'attribute') {
      sub.querySelectorAll('.ct-attr-chip').forEach(c => c.classList.remove('selected'));
    }
  }

  // Enable start button (unless attribute mode needs sub-selection)
  const startBtn = ctEl('ctStartBtn');
  if (startBtn) {
    startBtn.disabled = (mode === 'attribute');
  }
}

function ctSelectAttr(slug) {
  ctState.attrSlug = slug;
  document.querySelectorAll('.ct-attr-chip').forEach(c => {
    c.classList.toggle('selected', c.dataset.attr === slug);
  });
  const startBtn = ctEl('ctStartBtn');
  if (startBtn) startBtn.disabled = false;
}

// ── START TEST ───────────────────────────────────────────────────────────────
async function ctStartTest() {
  ctState.senses = ctShuffle([...CT_SENSES]);
  ctState.currentIndex = 0;
  ctState.scores = { clean: 0, assisted: 0, learning: 0 };
  ctState.answers = [];
  ctState.retryQueue = [];
  ctState.isRetry = false;
  ctState.locked = false;

  // Create test session on server
  try {
    const res = await fetch('/api/collection-tests', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ctCsrf(),
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        collection_id: CT_COLLECTION.id,
        test_mode: ctState.mode,
        attribute_slug: ctState.attrSlug,
        total_questions: ctState.senses.length,
      }),
    });
    if (res.ok) {
      const data = await res.json();
      ctState.testId = data.testId || data.id || null;
    }
  } catch (_) {
    // Continue offline — testId stays null
  }

  ctRenderQuestion();
}

// ── QUESTION SCREEN (IWP-style layout) ───────────────────────────────────────
function ctRenderQuestion() {
  const app = ctEl('ctApp');
  if (!app) return;

  const sense = ctState.senses[ctState.currentIndex];
  if (!sense) { ctRenderResults(); return; }

  ctState.hintsUsed = [];
  ctState.usageAttempts = 0;
  ctState.locked = false;

  const total = ctState.senses.length;
  const idx = ctState.currentIndex;
  const pct = ((idx / total) * 100).toFixed(1);
  const mode = ctState.mode;
  const attrSlug = ctState.attrSlug;

  // Which characteristics are being tested (exclude from header, show as question)
  const testing = new Set();
  if (mode === 'domain') testing.add('domain');
  if (mode === 'pos') testing.add('pos');
  if (mode === 'pinyin') testing.add('pinyin');
  if (mode === 'definition') testing.add('definition');
  if (mode === 'attribute' && attrSlug) testing.add(attrSlug);

  let html = '<div class="ct-question">';

  // ── Progress bar + tally ──
  html += '<div class="ct-progress"><div class="ct-progress-fill" style="width:' + pct + '%"></div></div>';
  html += '<div class="ct-tally">';
  html += `<span class="ct-tally-item ct-tally-clean">🍎 ${ctState.scores.clean}</span>`;
  html += `<span class="ct-tally-item ct-tally-assisted">🌸 ${ctState.scores.assisted}</span>`;
  html += `<span class="ct-tally-item ct-tally-learning">🌱 ${ctState.scores.learning}</span>`;
  html += `<span class="ct-tally-of">${idx + 1} / ${total}</span>`;
  html += '</div>';

  // ── HEADER BLOCK (IWP-style: character + domain + POS + pinyin) ──
  html += '<div class="ct-card word-card">';

  // Character
  html += `<div class="ct-card-hanzi card-hanzi"><span class="hanzi-char">${ctEsc(sense.traditional)}</span></div>`;

  // Right side: Domain, POS, Pinyin — each masked or shown based on test mode
  html += '<div class="ct-card-header-meta">';

  // Domain chip
  if (testing.has('domain')) {
    html += '<div class="ct-header-chip ct-tested">Domain ?</div>';
  } else {
    const domainVal = sense.domain + (sense.secondaryDomains?.length ? ', ' + sense.secondaryDomains.map(d => d.en).join(', ') : '');
    html += `<button class="ct-header-chip ct-hintable" data-hint="domain" onclick="ctRevealHint(this,'domain')">
      <span class="ct-hint-q">Domain ?</span>
      <span class="ct-hint-val">${ctEsc(domainVal)}</span>
    </button>`;
  }

  // POS chip
  if (testing.has('pos')) {
    html += '<div class="ct-header-chip ct-tested">Part of Speech ?</div>';
  } else {
    const posVal = sense.definitions.map(d => (CT_POS_ABBR[d.pos] || d.pos) + ' · ' + d.pos).join(' | ');
    html += `<button class="ct-header-chip ct-hintable" data-hint="pos" onclick="ctRevealHint(this,'pos')">
      <span class="ct-hint-q">Part of Speech ?</span>
      <span class="ct-hint-val">${ctEsc(posVal)}</span>
    </button>`;
  }

  // Pinyin
  if (testing.has('pinyin')) {
    html += '<div class="ct-header-chip ct-tested" style="font-style:italic">Pinyin ?</div>';
  } else {
    html += `<button class="ct-header-chip ct-hintable" data-hint="pinyin" onclick="ctRevealHint(this,'pinyin')">
      <span class="ct-hint-q" style="font-style:italic">Pinyin ?</span>
      <span class="ct-hint-val pinyin">${ctEsc(sense.pinyin)}</span>
    </button>`;
  }

  html += '</div>'; // close header-meta
  html += '</div>'; // close card header

  // ── QUESTION + ANSWER BLOCK ──
  const prompts = {
    domain:     'What domain does this word belong to?',
    pos:        'What part of speech is this word?',
    pinyin:     'What is the correct pinyin?',
    definition: 'Which definition matches this word?',
    attribute:  attrSlug ? `What is this word's ${attrSlug}?` : 'Select the correct attribute.',
    usage:      'Write a sentence using this word.',
  };
  html += `<div class="ct-question-block">`;
  html += `<div class="ct-prompt">${prompts[mode]}</div>`;

  // Answer area
  if (mode === 'usage') {
    html += ctBuildUsageInput(sense);
  } else {
    html += ctBuildOptions(sense);
  }
  // ── Navigation area (right after answers) ──
  html += '<div class="ct-question-nav" id="ctQuestionNav">';
  if (idx > 0) {
    html += `<button class="ct-back-btn" onclick="ctBack()">Back</button>`;
  }
  html += '</div>';

  html += '</div>';

  // ── HINT BLOCK (below question: definition, attributes, examples) ──
  html += '<div class="ct-hint-block">';

  // Definition hint
  if (!testing.has('definition') && sense.definitions.length > 0) {
    const defVal = sense.definitions.map(d => d.def).join('; ');
    html += `<button class="ct-hint-row ct-hintable" data-hint="definition" onclick="ctRevealHint(this,'definition')">
      <span class="ct-hint-q">📖 Definition ?</span>
      <span class="ct-hint-val">${ctEsc(defVal)}</span>
    </button>`;
  }

  // Attribute hints (IWP grid style)
  html += '<div class="ct-attr-hints card-meta">';
  const attrList = [
    { slug: 'register', label: 'Register', labelZh: '語域', val: sense.register, icons: CT_ATTR_ICONS.register },
    { slug: 'connotation', label: 'Connotation', labelZh: '感情色彩', val: sense.connotation, icons: CT_ATTR_ICONS.connotation },
    { slug: 'channel', label: 'Channel', labelZh: '媒介', val: sense.channel, icons: CT_ATTR_ICONS.channel },
    { slug: 'dimension', label: 'Dimension', labelZh: '維度', val: sense.dimensions?.join(', ') || '' },
    { slug: 'intensity', label: 'Intensity', labelZh: '強度', val: sense.intensity != null ? sense.intensity : '' },
  ];
  attrList.forEach(attr => {
    if (testing.has(attr.slug)) return; // skip the tested attribute
    const icon = attr.icons ? (attr.icons[attr.val] || '') : (CT_ATTR_ICONS.intensity?.[attr.val] || '');
    const displayVal = icon ? icon + ' ' + attr.val : attr.val;
    html += `<button class="ct-attr-hint card-attr" data-hint="${attr.slug}" onclick="ctRevealHint(this,'${attr.slug}')">
      <div class="card-attr-header ct-hint-q">${attr.label.toUpperCase()} · ${attr.labelZh}</div>
      <div class="card-attr-value ct-hint-val">${ctEsc(String(displayVal))}</div>
      <div class="card-attr-value ct-hint-placeholder">?</div>
    </button>`;
  });
  html += '</div>';

  // Conservatory examples (collapsed)
  if (sense.examples && sense.examples.length > 0) {
    html += '<details class="ct-examples-hint" id="ctExamplesDetail">';
    html += '<summary>Writing Conservatory Examples</summary>';
    html += '<div class="ct-examples-list">';
    sense.examples.forEach(ex => {
      html += `<div class="ct-example"><div class="ct-example-cn">${ctEsc(ex.cn)}</div>`;
      html += `<div class="ct-example-en">${ctEsc(ex.en)}</div></div>`;
    });
    html += '</div></details>';
  }

  html += '</div>'; // close hint-block

  html += '</div>'; // close ct-question
  app.innerHTML = html;

  // Track examples detail opening
  const detailEl = ctEl('ctExamplesDetail');
  if (detailEl) {
    detailEl.addEventListener('toggle', function handler() {
      if (detailEl.open && !ctState.hintsUsed.includes('examples')) {
        ctState.hintsUsed.push('examples');
      }
    });
  }
}

// ── HINT REVEAL ──────────────────────────────────────────────────────────────
function ctRevealHint(el, slug) {
  if (el.classList.contains('revealed')) return;
  el.classList.add('revealed');
  if (!ctState.hintsUsed.includes(slug)) {
    ctState.hintsUsed.push(slug);
  }
}

// ── OPTION GENERATORS ────────────────────────────────────────────────────────
function ctGetDomainOptions(sense) {
  const correctSet = new Set();
  if (sense.domain) correctSet.add(sense.domain);
  if (sense.secondaryDomains) {
    sense.secondaryDomains.forEach(d => correctSet.add(d.en));
  }
  const correct = sense.domain || [...correctSet][0];

  // Gather wrong domains from distractors
  const wrongPool = [];
  CT_DISTRACTORS.forEach(d => {
    if (d.domain && !correctSet.has(d.domain)) wrongPool.push(d.domain);
    if (d.secondaryDomains) {
      d.secondaryDomains.forEach(sd => {
        if (!correctSet.has(sd.en)) wrongPool.push(sd.en);
      });
    }
  });
  const unique = [...new Set(wrongPool)];
  const wrong = ctShuffle(unique).slice(0, 3);
  return ctShuffle([correct, ...wrong]);
}

function ctGetPosOptions(sense) {
  const correctPosSet = new Set(sense.definitions.map(d => d.pos));
  const correctDisplay = sense.definitions[0]
    ? sense.definitions[0].pos + ' — ' + (CT_POS_ABBR[sense.definitions[0].pos] || sense.definitions[0].posFull)
    : [...correctPosSet][0];

  const allPos = Object.keys(CT_POS_ABBR);
  const wrongAbbrs = allPos.filter(p => !correctPosSet.has(p));
  const picked = ctShuffle(wrongAbbrs).slice(0, 5);
  const options = [correctDisplay];
  picked.forEach(p => options.push(p + ' — ' + CT_POS_ABBR[p]));
  return ctShuffle(options);
}

function ctGetPinyinOptions(sense) {
  const correct = sense.pinyin;
  const wrongPool = CT_DISTRACTORS
    .filter(d => d.pinyin && d.pinyin !== correct)
    .map(d => d.pinyin);
  const unique = [...new Set(wrongPool)];
  const wrong = ctShuffle(unique).slice(0, 3);
  return ctShuffle([correct, ...wrong]);
}

function ctGetDefinitionOptions(sense) {
  const correct = sense.definitions.map(d => `[${d.pos}] ${d.def}`).join('; ');
  const wrongPool = CT_DISTRACTORS
    .filter(d => d.definitions && d.definitions.length > 0)
    .map(d => d.definitions.map(dd => `[${dd.pos}] ${dd.def}`).join('; '))
    .filter(s => s !== correct);
  const unique = [...new Set(wrongPool)];
  const wrong = ctShuffle(unique).slice(0, 3);
  return ctShuffle([correct, ...wrong]);
}

function ctGetAttributeOptions(sense, attrSlug) {
  const enums = CT_ATTR_ENUMS[attrSlug];
  if (!enums) return [];

  return enums.map(val => {
    const icons = CT_ATTR_ICONS[attrSlug];
    const icon = icons ? (icons[val] || '') : '';
    return icon ? icon + ' ' + val : String(val);
  });
}

// ── BUILD OPTIONS / USAGE ────────────────────────────────────────────────────
function ctBuildOptions(sense) {
  let options = [];
  let correctVal = '';

  switch (ctState.mode) {
    case 'domain':
      options = ctGetDomainOptions(sense);
      correctVal = sense.domain;
      break;
    case 'pos': {
      options = ctGetPosOptions(sense);
      const firstDef = sense.definitions[0];
      correctVal = firstDef
        ? firstDef.pos + ' — ' + (CT_POS_ABBR[firstDef.pos] || firstDef.posFull)
        : '';
      break;
    }
    case 'pinyin':
      options = ctGetPinyinOptions(sense);
      correctVal = sense.pinyin;
      break;
    case 'definition':
      options = ctGetDefinitionOptions(sense);
      correctVal = sense.definitions.map(d => `[${d.pos}] ${d.def}`).join('; ');
      break;
    case 'attribute': {
      options = ctGetAttributeOptions(sense, ctState.attrSlug);
      const raw = ctState.attrSlug === 'dimension'
        ? (sense.dimensions || [])
        : [sense[ctState.attrSlug]];
      const val = raw[0];
      const icons = CT_ATTR_ICONS[ctState.attrSlug];
      const icon = icons ? (icons[val] || '') : '';
      correctVal = icon ? icon + ' ' + val : String(val);
      break;
    }
  }

  let html = '<div class="ct-options" id="ctOptions">';
  options.forEach((opt, i) => {
    html += `<button class="ct-option" data-idx="${i}" onclick="ctSelectAnswer(this, ${JSON.stringify(opt).replace(/"/g, '&quot;')}, ${JSON.stringify(correctVal).replace(/"/g, '&quot;')})">${ctEsc(String(opt))}</button>`;
  });
  html += '</div>';
  return html;
}

function ctBuildUsageInput(sense) {
  let html = '<div class="ct-usage-area">';
  html += `<textarea class="ct-usage-textarea" id="ctUsageInput" placeholder="Write a sentence using ${sense.traditional}..."></textarea>`;
  html += `<button class="ct-usage-submit" id="ctUsageSubmit" onclick="ctSubmitUsage()">Submit</button>`;
  html += '<div id="ctUsageResult"></div>';
  html += '</div>';
  return html;
}

// ── ANSWER HANDLING ──────────────────────────────────────────────────────────
function ctSelectAnswer(optionEl, chosen, correct) {
  if (ctState.locked) return;
  ctState.locked = true;

  const sense = ctState.senses[ctState.currentIndex];
  let isCorrect = false;

  if (ctState.mode === 'domain') {
    // Multiple correct: primary + secondary
    const correctSet = new Set();
    if (sense.domain) correctSet.add(sense.domain);
    if (sense.secondaryDomains) {
      sense.secondaryDomains.forEach(d => correctSet.add(d.en));
    }
    isCorrect = correctSet.has(chosen);
  } else if (ctState.mode === 'attribute' && ctState.attrSlug === 'dimension') {
    // Dimension can have multiple values
    const vals = (sense.dimensions || []).map(v => {
      const icons = CT_ATTR_ICONS.dimension;
      const icon = icons ? (icons[v] || '') : '';
      return icon ? icon + ' ' + v : String(v);
    });
    isCorrect = vals.includes(chosen);
  } else {
    isCorrect = (chosen === correct);
  }

  // Determine tier
  let tier;
  if (isCorrect && ctState.hintsUsed.length === 0) {
    tier = 'clean';
  } else if (isCorrect) {
    tier = 'assisted';
  } else {
    tier = 'learning';
  }

  ctState.scores[tier]++;

  // Record answer
  ctState.answers.push({
    senseId: sense.senseId,
    traditional: sense.traditional,
    chosen: chosen,
    correct: correct,
    tier: tier,
    hintsUsed: [...ctState.hintsUsed],
  });

  // Visual feedback
  const allOptions = document.querySelectorAll('#ctOptions .ct-option');
  allOptions.forEach(btn => {
    btn.classList.add('ct-disabled');
    // Highlight correct answer
    const btnText = btn.textContent;
    if (btnText === String(correct)) {
      btn.classList.add('ct-correct');
    }
  });

  if (isCorrect) {
    optionEl.classList.remove('ct-disabled');
    optionEl.classList.add('ct-correct');
  } else {
    optionEl.classList.add('ct-incorrect');
    // Push to retry queue
    ctState.retryQueue.push(sense);
  }

  // Post answer to server
  if (ctState.testId) {
    fetch(`/api/collection-tests/${ctState.testId}/answers`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ctCsrf(),
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        word_sense_id: sense.senseId,
        question_index: ctState.currentIndex,
        correct_value: String(correct),
        chosen_value: String(chosen),
        is_correct: isCorrect,
        score_tier: tier,
        hints_used: ctState.hintsUsed,
      }),
    }).catch(() => {});
  }

  // Show Next button
  const navArea = ctEl('ctQuestionNav');
  if (navArea) {
    navArea.innerHTML = `<button class="ct-next-btn" onclick="ctNext()">Next</button>`;
  }
}

// ── USAGE MODE ───────────────────────────────────────────────────────────────
async function ctSubmitUsage() {
  const input = ctEl('ctUsageInput');
  const submitBtn = ctEl('ctUsageSubmit');
  const resultDiv = ctEl('ctUsageResult');
  if (!input || !submitBtn || !resultDiv) return;

  const sentence = input.value.trim();
  if (!sentence) return;

  ctState.usageAttempts++;
  submitBtn.disabled = true;
  submitBtn.textContent = 'Checking...';

  const sense = ctState.senses[ctState.currentIndex];

  try {
    const res = await fetch('/api/collection-tests/usage-check', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ctCsrf(),
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        word_sense_id: sense.senseId,
        word: sense.traditional,
        pinyin: sense.pinyin || '',
        pos: (sense.definitions || []).map(d => (d.posFull || d.pos || '')).join(', '),
        definition: (sense.definitions || []).map(d => d.def).join('; '),
        register: sense.register || '',
        connotation: sense.connotation || '',
        channel: sense.channel || '',
        domain: sense.domain || '',
        sentence: sentence,
      }),
    });

    const data = await res.json();
    const isCorrect = data.is_correct || data.correct || false;
    const explanation = data.explanation || data.feedback || '';
    const canRetry = !isCorrect && ctState.usageAttempts < 3;

    // Only record score/answer on final attempt or correct
    if (isCorrect || !canRetry) {
      let tier;
      if (isCorrect && ctState.usageAttempts === 1 && ctState.hintsUsed.length === 0) {
        tier = 'clean';
      } else if (isCorrect) {
        tier = 'assisted';
      } else {
        tier = 'learning';
      }

      ctState.scores[tier]++;
      ctState.answers.push({
        senseId: sense.senseId,
        traditional: sense.traditional,
        chosen: sentence,
        correct: '(usage)',
        tier: tier,
        hintsUsed: [...ctState.hintsUsed],
      });

      if (!isCorrect) {
        ctState.retryQueue.push(sense);
      }

      // Save answer to server
      if (ctState.testId) {
        fetch(`/api/collection-tests/${ctState.testId}/answers`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': ctCsrf(),
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            word_sense_id: sense.senseId,
            question_index: ctState.currentIndex,
            correct_value: '(usage)',
            chosen_value: sentence,
            is_correct: isCorrect,
            score_tier: tier,
            hints_used: ctState.hintsUsed,
            ai_feedback: explanation || null,
          }),
        }).catch(() => {});
      }
    }

    // Show result
    const resultIcon = isCorrect ? '🍎' : '🌱';
    const resultColor = isCorrect ? 'var(--jade)' : 'var(--rose)';
    let resultHtml = `<div style="margin-top:0.6rem;padding:0.6rem 0.8rem;border:1px solid var(--border);border-radius:2px;">`;
    resultHtml += `<div style="font-family:'DM Mono',monospace;font-size:0.75rem;color:${resultColor};margin-bottom:0.3rem;">${resultIcon} ${isCorrect ? 'Correct' : 'Incorrect (' + ctState.usageAttempts + '/3)'}</div>`;
    if (explanation) {
      resultHtml += `<div style="font-family:'Cormorant Garamond',serif;font-size:0.95rem;color:var(--text);line-height:1.5;">${ctEsc(explanation)}</div>`;
    }
    resultHtml += '</div>';

    if (canRetry) {
      resultHtml += `<div style="font-family:'DM Mono',monospace;font-size:0.7rem;color:var(--dim);margin-top:0.4rem;">${3 - ctState.usageAttempts} attempt${3 - ctState.usageAttempts !== 1 ? 's' : ''} remaining</div>`;
      submitBtn.disabled = false;
      submitBtn.textContent = 'Try Again';
    } else {
      resultHtml += `<button class="ct-next-btn" style="margin-top:0.6rem;" onclick="ctAdvanceUsage()">Next</button>`;
      submitBtn.style.display = 'none';
    }
    resultDiv.innerHTML = resultHtml;

  } catch (err) {
    resultDiv.innerHTML = `<div style="margin-top:0.6rem;color:var(--rose);font-family:'DM Mono',monospace;font-size:0.75rem;">Error checking usage. <button class="ct-next-btn" style="margin-top:0.4rem;" onclick="ctAdvanceUsage()">Skip</button></div>`;

    // Record as learning on error
    ctState.scores.learning++;
    ctState.answers.push({
      senseId: sense.senseId,
      traditional: sense.traditional,
      chosen: sentence,
      correct: '(error)',
      tier: 'learning',
      hintsUsed: [...ctState.hintsUsed],
    });
    ctState.retryQueue.push(sense);
  }
}

function ctAdvanceUsage() {
  ctState.currentIndex++;
  ctRenderQuestion();
}

function ctNext() {
  ctState.currentIndex++;
  ctRenderQuestion();
}

function ctBack() {
  if (ctState.currentIndex <= 0) return;
  ctState.currentIndex--;
  ctRenderReview();
}

function ctRenderReview() {
  const app = ctEl('ctApp');
  if (!app) return;

  const sense = ctState.senses[ctState.currentIndex];
  if (!sense) return;

  const answer = ctState.answers.find(a => a.senseId === sense.senseId);
  const total = ctState.senses.length;
  const idx = ctState.currentIndex;
  const pct = ((idx / total) * 100).toFixed(1);

  let html = '<div class="ct-question ct-review">';

  // Progress bar + tally
  html += '<div class="ct-progress"><div class="ct-progress-fill" style="width:' + pct + '%"></div></div>';
  html += '<div class="ct-tally">';
  html += `<span class="ct-tally-item ct-tally-clean">🍎 ${ctState.scores.clean}</span>`;
  html += `<span class="ct-tally-item ct-tally-assisted">🌸 ${ctState.scores.assisted}</span>`;
  html += `<span class="ct-tally-item ct-tally-learning">🌱 ${ctState.scores.learning}</span>`;
  html += `<span class="ct-tally-of">${idx + 1} / ${total}</span>`;
  html += '</div>';

  // Review label
  html += '<div class="ct-review-label">Reviewing</div>';

  // Character
  html += '<div class="ct-card word-card">';
  html += `<div class="ct-card-hanzi card-hanzi"><span class="hanzi-char">${ctEsc(sense.traditional)}</span></div>`;
  html += '<div class="ct-card-header-meta">';
  const domainVal = sense.domain + (sense.secondaryDomains?.length ? ', ' + sense.secondaryDomains.map(d => d.en).join(', ') : '');
  html += `<div class="ct-header-chip">${ctEsc(domainVal)}</div>`;
  const posVal = sense.definitions.map(d => d.posFull || d.pos).join(', ');
  html += `<div class="ct-header-chip">${ctEsc(posVal)}</div>`;
  html += `<div class="ct-header-chip pinyin">${ctEsc(sense.pinyin)}</div>`;
  html += '</div></div>';

  // Show answer
  if (answer) {
    const tierIcon = answer.tier === 'clean' ? '🍎' : answer.tier === 'assisted' ? '🌸' : '🌱';
    const tierColor = answer.tier === 'learning' ? 'var(--rose)' : 'var(--jade)';
    html += `<div style="padding:0.6rem 0.8rem;border:1px solid var(--border);border-radius:2px;">`;
    html += `<div style="font-family:'DM Mono',monospace;font-size:0.75rem;color:${tierColor};margin-bottom:0.3rem;">${tierIcon} ${answer.tier}</div>`;
    html += `<div style="font-family:'DM Mono',monospace;font-size:0.8rem;color:var(--dim);">Your answer: ${ctEsc(String(answer.chosen))}</div>`;
    html += `<div style="font-family:'DM Mono',monospace;font-size:0.8rem;color:var(--accent);margin-top:0.2rem;">Correct: ${ctEsc(String(answer.correct))}</div>`;
    html += '</div>';
  }

  // Navigation
  html += '<div class="ct-question-nav" id="ctQuestionNav">';
  if (idx > 0) {
    html += `<button class="ct-back-btn" onclick="ctBack()">Back</button>`;
  }
  // Find the furthest answered index to know where to resume
  const furthestAnswered = ctState.answers.length;
  if (idx < furthestAnswered - 1) {
    html += `<button class="ct-next-btn" onclick="ctState.currentIndex++;ctRenderReview()">Next</button>`;
  } else {
    html += `<button class="ct-next-btn" onclick="ctState.currentIndex=${furthestAnswered};ctRenderQuestion()">Resume</button>`;
  }
  html += '</div>';

  html += '</div>';
  app.innerHTML = html;
}

// ── RESULTS SCREEN ───────────────────────────────────────────────────────────
function ctRenderResults() {
  const app = ctEl('ctApp');
  if (!app) return;

  const total = ctState.answers.length;
  const { clean, assisted, learning } = ctState.scores;
  const pctCorrect = total > 0 ? Math.round(((clean + assisted) / total) * 100) : 0;

  let html = '<div class="ct-results">';
  html += '<div class="ct-results-title">Test Complete</div>';

  // Percentage
  html += `<div style="font-family:'Cormorant Garamond',serif;font-size:2rem;color:var(--ink);">${pctCorrect}%</div>`;

  // Three score boxes
  html += '<div class="ct-scores">';
  html += ctScoreBox('🍎', clean,    'Clean',    'fruit');
  html += ctScoreBox('🌸', assisted, 'Assisted', 'bud');
  html += ctScoreBox('🌱', learning, 'Learning', 'seed');
  html += '</div>';

  // Per-word breakdown
  html += '<div class="ct-breakdown">';
  html += '<div class="ct-breakdown-title">Breakdown</div>';
  ctState.answers.forEach(ans => {
    const tierEmoji = ans.tier === 'clean' ? '🍎' : ans.tier === 'assisted' ? '🌸' : '🌱';
    const hintsCount = ans.hintsUsed.length;
    const hintsLabel = hintsCount > 0 ? ` (${hintsCount} hint${hintsCount !== 1 ? 's' : ''})` : '';
    html += '<div class="ct-breakdown-item">';
    html += `<span class="ct-breakdown-char">${ctEsc(ans.traditional)}</span>`;
    html += `<span class="ct-breakdown-word">${ctEsc(String(ans.chosen))}${hintsLabel}</span>`;
    html += `<span class="ct-breakdown-result">${tierEmoji}</span>`;
    html += '</div>';
  });
  html += '</div>';

  // Retry button
  if (ctState.retryQueue.length > 0) {
    html += `<button class="ct-retry-btn" onclick="ctRetryMissed()">Retry Missed Words (${ctState.retryQueue.length})</button>`;
  }

  // Back link
  html += '<a href="/my-words" class="ct-back-link">Back to My Words</a>';
  html += '</div>';

  app.innerHTML = html;

  // Post completion to server
  if (ctState.testId) {
    fetch(`/api/collection-tests/${ctState.testId}/complete`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ctCsrf(),
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        scores: ctState.scores,
        total: total,
      }),
    }).catch(() => {});
  }
}

function ctScoreBox(icon, count, label, tier) {
  return `<div class="ct-score-box ct-score-${tier}">` +
    `<span class="ct-score-icon">${icon}</span>` +
    `<span class="ct-score-num">${count}</span>` +
    `<span class="ct-score-label">${label}</span>` +
    '</div>';
}

// ── RETRY ────────────────────────────────────────────────────────────────────
function ctRetryMissed() {
  ctState.senses = ctShuffle([...ctState.retryQueue]);
  ctState.retryQueue = [];
  ctState.currentIndex = 0;
  ctState.scores = { clean: 0, assisted: 0, learning: 0 };
  ctState.answers = [];
  ctState.isRetry = true;
  ctState.locked = false;
  ctRenderQuestion();
}

// ── INIT ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', ctSetup);
</script>
