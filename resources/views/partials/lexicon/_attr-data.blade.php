{{-- Shared attribute label maps + chip renderer functions --}}
{{-- Depends on: iconsMode, uiMode (defined by each page before include) --}}
<script>
const ATTR_LABELS = {
  register:    { literary:['🦋','Literary'], formal:['🐝','Formal'], neutral:['🐞','Standard'], colloquial:['🪲','Colloquial'], informal:['🦗','Informal'], slang:['🕷️','Slang'] },
  connotation: { positive:['☀️','Positive'], neutral:['⛅','Neutral'], negative:['⛈️','Negative'], 'context-dependent':['🌦️','Context'] },
  channel:     { 'spoken-only':['🦎','Spoken Only'], 'spoken-dominant':['🐍','Spoken Dominant'], fluid:['🦜','Fluid'], 'written-dominant':['🦚','Written Dominant'], 'written-only':['🐉','Written Only'] },
  dimension:   { abstract:['🐙','Abstract'], concrete:['🐢','Concrete'], internal:['🐟','Internal'], external:['🦂','External'], fluid:['🦀','Fluid'] },
  intensity:   { 1:['🌸','Faint'], 2:['🌼','Mild'], 3:['🪷','Moderate'], 4:['🌻','Strong'], 5:['🌺','Blazing'] },
  tocfl:       { prep:['🌑','Prep'], entry:['🌒','Entry'], basic:['🌓','Basic'], intermediate:['🌔','Intermediate'], advanced:['🌕','Advanced'], high:['🌖','High'], fluency:['🌝','Fluency'] },
};

function metaAttrLabel(cat, key) {
  return ATTR_LABELS[cat]?.[key] || ['', String(key)];
}

const ATTR_ZH = {
  register:    { literary:'文學體', formal:'正式', neutral:'標準', colloquial:'口語', informal:'非正式', slang:'俚語' },
  connotation: { positive:'褒義', neutral:'中性', negative:'貶義', 'context-dependent':'隨境' },
  channel:     { 'spoken-only':'純口語', 'spoken-dominant':'偏口語', fluid:'流動', 'written-dominant':'偏書面', 'written-only':'純書面' },
  dimension:   { abstract:'抽象', concrete:'具體', internal:'內在', external:'外在', fluid:'流動' },
  intensity:   { 1:'微', 2:'淡', 3:'中', 4:'濃', 5:'烈' },
  tocfl:       { prep:'準備', entry:'入門', basic:'基礎', advanced:'高階', high:'精通', fluency:'流利' },
};

const ATTR_HEADER_ZH = {
  register: '語域', connotation: '感情色彩', channel: '媒介',
  dimension: '維度', intensity: '強度', tocfl: '華測',
};

const connoClass = { positive: 'conno-pos', neutral: 'conno-neu', negative: 'conno-neg', 'context-dependent': 'conno-ctx' };

// Attribute chip toggle — toggles ALL translatable elements within the chip
// (both header and label spans) between preferred language and Chinese.
function toggleAttrLang(e) {
  e.stopPropagation();
  e.preventDefault();
  const chip = e.currentTarget;
  const preferred = (typeof uiMode !== 'undefined' && (uiMode === 'zh-icon' || uiMode === 'zh-only')) ? 'zh' : 'en';
  const alt = preferred === 'zh' ? 'en' : 'zh';
  chip.querySelectorAll('[data-en][data-zh]').forEach(el => {
    const current = el.dataset.state || preferred;
    const next = current === preferred ? alt : preferred;
    el.dataset.state = next;
    el.textContent = el.dataset[next] || el.dataset.en;
  });
}

// Single-value attribute chip (e.g. Register: 🦋 Literary)
function cardAttr(cat, key, header, labelPair, extraClass) {
  extraClass = extraClass || '';
  const [icon, label] = labelPair;
  const showIcon  = iconsMode === 'on';
  const showLabel = uiMode !== 'icon-only';
  const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
  const isBoth  = (uiMode === 'all' || uiMode === 'en-zh');
  const zhLabel = ATTR_ZH[cat]?.[key] || label;
  const hdrZh   = ATTR_HEADER_ZH[cat] || header;
  const initLabel = preferred === 'zh' ? zhLabel : isBoth ? `${label} ${zhLabel}` : label;
  const initHdr   = preferred === 'zh' ? hdrZh   : isBoth ? `${header} · ${hdrZh}`  : header;
  return `<div class="card-attr attr-${cat} ${extraClass}" onclick="toggleAttrLang(event)">
    <div class="card-attr-header" data-en="${header}" data-zh="${hdrZh}" data-state="${preferred}">${initHdr}</div>
    <div class="card-attr-value">
      ${showIcon && icon ? `<span class="attr-icon">${icon}</span>` : ''}
      ${showLabel ? `<span class="attr-label" data-en="${label}" data-zh="${zhLabel}" data-state="${preferred}">${initLabel}</span>` : ''}
    </div>
  </div>`;
}

// Multi-value attribute chip (e.g. Dimension: 🐙 Abstract · 🐢 Concrete)
function cardAttrMulti(cat, keys, header) {
  const showIcon  = iconsMode === 'on';
  const showLabel = uiMode !== 'icon-only';
  const preferred = (uiMode === 'zh-icon' || uiMode === 'zh-only') ? 'zh' : 'en';
  const isBoth  = (uiMode === 'all' || uiMode === 'en-zh');
  const hdrZh   = ATTR_HEADER_ZH[cat] || header;
  const initHdr = preferred === 'zh' ? hdrZh : isBoth ? `${header} · ${hdrZh}` : header;
  const valueHTML = keys.map(k => {
    const [icon, label] = metaAttrLabel(cat, k);
    const zhLabel = ATTR_ZH[cat]?.[k] || label;
    const initLabel = preferred === 'zh' ? zhLabel : isBoth ? `${label} ${zhLabel}` : label;
    return `<span class="attr-val-item">${showIcon && icon ? `<span class="attr-icon">${icon}</span>` : ''}${showLabel ? `<span class="attr-label" data-en="${label}" data-zh="${zhLabel}" data-state="${preferred}">${initLabel}</span>` : ''}</span>`;
  }).join('');
  return `<div class="card-attr attr-${cat}" onclick="toggleAttrLang(event)">
    <div class="card-attr-header" data-en="${header}" data-zh="${hdrZh}" data-state="${preferred}">${initHdr}</div>
    <div class="card-attr-value multi">${valueHTML}</div>
  </div>`;
}
</script>
