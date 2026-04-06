<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>My Words — 流動 Living Lexicon</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@300;400;600;700&family=DM+Mono:ital,wght@0,300;0,400;1,300&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
@include('partials.lexicon._foundations')
<style>
/* ── MAIN ── */
.mw-main { max-width: 640px; margin: 0 auto; padding: 1.5rem 1rem 3rem; }

/* ── SECTION ── */
.mw-section { margin-bottom: 1.5rem; }
.mw-section-body {
  background: var(--surface);
  border-radius: 0 0 4px 4px;
  padding: 0.75rem;
}
.mw-section-header {
  display: flex; align-items: center; gap: 0.5rem;
  margin-bottom: 0.5rem;
}
.mw-section-title {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  letter-spacing: 0.12em;
  color: var(--accent); flex: 1;
}
.mw-section-count {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--accent); opacity: 0.7;
}
.mw-action-btn {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  color: var(--dim); background: none; border: none;
  cursor: pointer; padding: 0.15rem 0.3rem;
  transition: color 0.15s;
}
.mw-action-btn:hover { color: var(--accent); }
.mw-action-btn.danger:hover { color: #c44; }

/* ── ENTRIES ── */
.mw-list { display: flex; flex-direction: column; gap: 0.5rem; }
.mw-entry {
  display: grid; grid-template-columns: auto 1fr auto;
  gap: 0.5rem; align-items: center;
  padding: 0.75rem 0.85rem;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 3px;
  text-decoration: none; color: var(--ink);
  transition: background 0.1s, border-color 0.15s;
}
.mw-entry:hover { border-color: rgba(98,64,200,0.25); }
.mw-hanzi {
  font-family: 'BiauKai', 'STKaiti', 'KaiTi', '楷體-繁', 'Noto Serif TC', serif;
  font-size: 1.3rem;
  font-weight: 400; line-height: 1.2;
}
/* ── Vertical mode ── */
body.vertical-mode .mw-hanzi {
  writing-mode: vertical-rl;
  text-orientation: mixed;
  font-size: 1.6rem;
  line-height: 1.6;
  letter-spacing: 0.08em;
}
.mw-mid {
  display: flex; flex-direction: column; gap: 0.15rem;
  min-width: 0;
}
.mw-pinyin {
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  color: var(--dim);
}
.mw-def {
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--ink); white-space: nowrap;
  overflow: hidden; text-overflow: ellipsis;
}
.mw-domain-chip {
  font-family: 'DM Mono', monospace; font-size: 0.55rem;
  letter-spacing: 0.05em; text-transform: uppercase;
  color: var(--accent); opacity: 0.8;
}
.mw-unsave {
  font-size: 1rem; background: none; border: none;
  cursor: pointer; color: var(--accent); padding: 0 0.2rem;
  transition: opacity 0.15s; line-height: 1;
}
.mw-unsave:hover { opacity: 0.6; }
.mw-remove {
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  background: none; border: none; cursor: pointer;
  color: var(--dim); padding: 0 0.2rem;
  transition: color 0.15s; line-height: 1;
}
.mw-remove:hover { color: #c44; }

/* ── MY WORDS COLLECTION PICKER ── */
.mw-entry-wrap { position: relative; }
.mw-cp {
  position: absolute; top: 100%; right: 0; z-index: 9999;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 3px; min-width: 200px; padding: 0.4rem 0;
  box-shadow: 0 8px 28px rgba(0,0,0,0.12);
  animation: mwCpIn 0.15s ease;
}
@keyframes mwCpIn { from { opacity: 0; transform: translateY(-4px); } }
.mw-cp-title {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  letter-spacing: 0.1em; text-transform: uppercase;
  color: var(--dim); padding: 0.3rem 0.6rem 0.2rem;
}
.mw-cp-item {
  display: flex; align-items: center; gap: 0.4rem;
  padding: 0.25rem 0.6rem; cursor: pointer;
  font-family: 'DM Mono', monospace; font-size: 0.72rem;
  color: var(--ink);
}
.mw-cp-item:hover { background: rgba(98,64,200,0.06); }
.mw-cp-new {
  display: flex; gap: 0.3rem; padding: 0.3rem 0.5rem;
  border-top: 1px solid var(--border); margin-top: 0.2rem;
}
.mw-cp-new input {
  flex: 1; font-family: 'DM Mono', monospace; font-size: 0.7rem;
  border: 1px solid var(--border); border-radius: 2px;
  padding: 0.2rem 0.4rem; background: var(--bg); color: var(--ink);
}
.mw-cp-new button {
  font-family: 'DM Mono', monospace; font-size: 0.85rem;
  background: none; border: 1px solid var(--border); border-radius: 2px;
  color: var(--accent); cursor: pointer; padding: 0 0.4rem;
}

/* ── LEARNING STATUS ── */
.mw-status { font-size: 0.7rem; cursor: help; flex-shrink: 0; }
.mw-pinyin-row { display: flex; align-items: center; gap: 0.3rem; }
.mw-pinyin-row .mw-pinyin { flex: 1; }
.mw-pinyin-row .mw-status { margin-left: auto; }

/* ── PROGRESS BAR ── */
.mw-progress-wrap {
  padding: 0.4rem 0 0.6rem;
  display: flex; align-items: center; gap: 0.5rem;
}
.mw-progress-bar {
  flex: 1; height: 6px; border-radius: 3px;
  background: linear-gradient(to right, #e74c3c, #f1c40f, #2ecc71);
  overflow: hidden; position: relative;
}
.mw-progress-cover {
  position: absolute; top: 0; right: 0; height: 100%;
  background: #d5d5d8;
  z-index: 1;
  transition: width 0.4s ease;
}
.mw-progress-label {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  color: var(--dim); letter-spacing: 0.05em;
  white-space: nowrap;
}

/* ── BUILD PANEL ── */
.mw-build-row {
  display: flex; gap: 0.5rem; align-items: center;
  flex-wrap: wrap; margin-bottom: 0.4rem;
}
.mw-build-row label {
  font-family: 'Cormorant Garamond', serif; font-size: 0.85rem;
  color: var(--dim);
}
.mw-build-input, .mw-build-select {
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  padding: 0.3rem 0.5rem;
  border: 1px solid var(--border); border-radius: 2px;
  background: var(--bg); color: var(--ink);
}
.mw-build-input { width: 60px; }
.mw-build-radio {
  display: flex; flex-direction: column; gap: 0.3rem;
  margin: 0.3rem 0;
}
.mw-build-radio label {
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  color: var(--dim); display: flex; align-items: center; gap: 0.3rem;
  cursor: pointer;
}
.mw-build-checks {
  display: flex; flex-direction: column; gap: 0.2rem;
  padding-left: 1.2rem; margin-bottom: 0.4rem;
}
.mw-build-checks label {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  color: var(--dim); display: flex; align-items: center; gap: 0.3rem;
  cursor: pointer;
}

/* ── EMPTY STATE ── */
.mw-empty {
  font-family: 'DM Mono', monospace; font-size: 0.75rem;
  color: var(--dim); text-align: center; padding: 1.5rem 1rem;
  background: var(--bg); border-radius: 3px;
}
.mw-empty a { color: var(--accent); text-decoration: none; }
.mw-empty a:hover { text-decoration: underline; }

/* ── ACCORDION ── */
.mw-accordion-header {
  display: flex; align-items: center; gap: 0.5rem;
  cursor: pointer; user-select: none;
  padding: 0.55rem 0.75rem;
  background: var(--surface2);
  border-radius: 4px 4px 0 0;
}
.mw-accordion-header.collapsed {
  border-radius: 4px;
}
.mw-accordion-header:hover { opacity: 0.85; }
.mw-accordion-arrow {
  font-size: 0.85rem; color: var(--accent);
  flex-shrink: 0;
  margin-left: auto;
}
/* Arrow content is set via JS: ▼ open, ▲ closed */
.mw-accordion-body { overflow: hidden; transition: max-height 0.25s ease; }
.mw-accordion-body.collapsed { display: none; }
.mw-section-actions {
  display: flex; gap: 0.5rem; align-items: center;
  justify-content: flex-end;
  padding: 0 0 0.5rem 0;
  border-bottom: 1px solid var(--border);
  margin-bottom: 0.5rem;
}
.mw-view-all {
  font-family: 'DM Mono', monospace; font-size: 0.68rem;
  color: var(--accent); text-decoration: none;
  letter-spacing: 0.08em;
}
.mw-view-all:hover { text-decoration: underline; }

/* ── INLINE RENAME ── */
.mw-rename-input {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  letter-spacing: 0.15em; text-transform: uppercase;
  color: var(--ink); background: var(--bg);
  border: 1px solid var(--accent); border-radius: 2px;
  padding: 0.15rem 0.4rem; outline: none;
}
</style>
</head>
<body>
<script>
  window.__AUTH = @json($authUser);
  var MW_SAVED = @json($savedWords);
  var MW_COLLECTIONS = @json($collections);
  var MW_PROGRESS = @json($wordProgress);
  var MW_KUNGFU = @json($kungfuWords);
</script>

@include('partials.lexicon._site-header', ['backUrl' => route('lexicon.index'), 'backLabel' => 'Lexicon'])

<div class="mw-main" id="mwMain"></div>

<script>
var csrf = document.querySelector('meta[name="csrf-token"]').content;

// Apply text direction preference
var textDir = localStorage.getItem('textDir') || 'horizontal';
if (textDir === 'vertical') document.body.classList.add('vertical-mode');

// Track accordion state across re-renders
var _accordionState = {};

function mwRender() {
  var main = document.getElementById('mwMain');
  if (!main) return;
  var html = '';

  // ── Compute uncategorized words (saved but not in any collection) ──
  var categorizedIds = [];
  MW_COLLECTIONS.forEach(function(c) {
    (c.words || []).forEach(function(w) {
      if (categorizedIds.indexOf(w.wordObjectId) === -1) categorizedIds.push(w.wordObjectId);
    });
  });
  var uncategorized = MW_SAVED.filter(function(s) {
    return categorizedIds.indexOf(s.wordObjectId) === -1;
  });

  // ── Open All / Close All + New Collection ──
  html += '<div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-bottom:0.5rem">';
  html += '<button class="mw-action-btn" style="color:var(--accent)" onclick="mwNewCollection()">+ New Collection</button>';
  html += '<span style="flex:1"></span>';
  html += '<button class="mw-action-btn" style="color:var(--accent)" onclick="mwExpandAll()">Open All</button>';
  html += '<button class="mw-action-btn" onclick="mwCollapseAll()">Close All</button>';
  html += '</div>';
  html += '<div id="mwNewCollForm"></div>';

  // ── 1. Uncategorized (accordion, open by default) ──
  var uncatOpen = _accordionState['uncat'] !== undefined ? _accordionState['uncat'] : (localStorage.getItem('mw_accordion_uncat') !== 'false');
  html += '<div class="mw-section">';
  html += '<div class="mw-accordion-header' + (uncatOpen ? '' : ' collapsed') + '" onclick="mwToggleAccordion(\'uncat\', this)">';
  html += '<div class="mw-section-title">Uncategorized (' + uncategorized.length + ')</div>';
  html += '<span class="mw-accordion-arrow">' + (uncatOpen ? '▼' : '▲') + '</span>';
  html += '</div>';
  html += '<div class="mw-accordion-body' + (uncatOpen ? '' : ' collapsed') + '" id="mwAccordion-uncat">';
  html += '<div class="mw-section-body">';

  if (MW_SAVED.length === 0) {
    html += '<div class="mw-empty">No saved words yet. <a href="{{ route('lexicon.index') }}">Explore the lexicon</a> and tap ☆ to save.</div>';
  } else if (uncategorized.length === 0) {
    html += '<div class="mw-empty">All words are in collections.</div>';
  } else {
    html += '<div class="mw-list">';
    uncategorized.forEach(function(s) {
      html += mwEntryHTML(s);
    });
    html += '</div>';
  }
  html += '</div></div></div>';

  // ── 1b. 需功夫 Needs Kung Fu (accordion, collapsed by default) ──
  if (MW_KUNGFU.length > 0) {
    var kfKey = 'kungfu';
    var kfOpen = _accordionState[kfKey] !== undefined ? _accordionState[kfKey] : (localStorage.getItem('mw_accordion_' + kfKey) === 'true');
    html += '<div class="mw-section">';
    html += '<div class="mw-accordion-header' + (kfOpen ? '' : ' collapsed') + '" onclick="mwToggleAccordion(\'' + kfKey + '\', this)">';
    html += '<div class="mw-section-title" style="color:var(--rose)">需功夫 Needs Kung Fu (' + MW_KUNGFU.length + ')</div>';
    html += '<span class="mw-accordion-arrow">' + (kfOpen ? '▼' : '▲') + '</span>';
    html += '</div>';
    html += '<div class="mw-accordion-body' + (kfOpen ? '' : ' collapsed') + '" id="mwAccordion-' + kfKey + '">';
    html += '<div class="mw-section-body">';
    html += '<div class="mw-list">';
    MW_KUNGFU.forEach(function(s) {
      html += mwEntryHTML(s);
    });
    html += '</div>';
    html += '</div></div></div>';
  }

  // ── 2. Collections (accordions, collapsed by default) ──
  MW_COLLECTIONS.forEach(function(c) {
    var cKey = 'coll-' + c.id;
    var cOpen = _accordionState[cKey] !== undefined ? _accordionState[cKey] : (localStorage.getItem('mw_accordion_' + cKey) === 'true');
    html += '<div class="mw-section" data-collection-id="' + c.id + '">';
    html += '<div class="mw-accordion-header' + (cOpen ? '' : ' collapsed') + '" onclick="mwToggleAccordion(\'' + cKey + '\', this)">';
    html += '<div class="mw-section-title" id="mwCollTitle-' + c.id + '">' + escHtml(c.name) + (c.nameZh ? ' · ' + escHtml(c.nameZh) : '') + ' (' + c.words.length + ')</div>';
    html += '<span class="mw-accordion-arrow">' + (cOpen ? '▼' : '▲') + '</span>';
    html += '</div>';
    html += '<div class="mw-accordion-body' + (cOpen ? '' : ' collapsed') + '" id="mwAccordion-' + cKey + '">';
    html += '<div class="mw-section-body">';
    html += '<div class="mw-section-actions">';
    if (c.words.length >= 2) {
      html += '<a class="mw-action-btn" href="/my-words/test/' + c.id + '" style="color:var(--accent);text-decoration:none">Test</a>';
    }
    html += '<button class="mw-action-btn" onclick="event.stopPropagation();mwRenameCollection(' + c.id + ')">Rename</button>';
    html += '<button class="mw-action-btn" onclick="event.stopPropagation();mwImportCollection(' + c.id + ')">Import</button>';
    html += '<button class="mw-action-btn" onclick="event.stopPropagation();mwBuildCollection(' + c.id + ')">Build</button>';
    html += '<button class="mw-action-btn danger" onclick="event.stopPropagation();mwDeleteCollection(' + c.id + ')">Delete</button>';
    html += '</div>';

    // ── Progress bar ──
    if (c.words.length > 0) {
      var learned = 0;
      c.words.forEach(function(w) {
        var p = MW_PROGRESS[w.wordObjectId];
        if (p && p.pinyin_passed && p.definition_passed && p.usage_passed) learned++;
      });
      var pct = Math.round((learned / c.words.length) * 100);
      var coverPct = 100 - pct;
      html += '<div class="mw-progress-wrap">';
      html += '<div class="mw-progress-bar"><div class="mw-progress-cover" style="width:' + coverPct + '%"></div></div>';
      html += '<span class="mw-progress-label">' + learned + '/' + c.words.length + ' learned</span>';
      html += '</div>';
    }

    if (c.words.length === 0) {
      html += '<div class="mw-empty">No words in this collection yet.</div>';
    } else {
      html += '<div class="mw-list">';
      c.words.forEach(function(s) {
        html += mwCollEntryHTML(s, c.id);
      });
      html += '</div>';
    }
    html += '</div></div></div>';
  });

  // ── 3. All Saved (accordion, collapsed by default) ──
  var allOpen = _accordionState['all'] !== undefined ? _accordionState['all'] : (localStorage.getItem('mw_accordion_all') === 'true');
  html += '<div class="mw-section">';
  html += '<div class="mw-accordion-header' + (allOpen ? '' : ' collapsed') + '" onclick="mwToggleAccordion(\'all\', this)">';
  html += '<div class="mw-section-title">All Saved (' + MW_SAVED.length + ')</div>';
  html += '<span class="mw-accordion-arrow">' + (allOpen ? '▼' : '▲') + '</span>';
  html += '</div>';
  html += '<div class="mw-accordion-body' + (allOpen ? '' : ' collapsed') + '" id="mwAccordion-all">';
  html += '<div class="mw-section-body">';

  if (MW_SAVED.length === 0) {
    html += '<div class="mw-empty">No saved words yet.</div>';
  } else {
    html += '<div class="mw-list">';
    MW_SAVED.forEach(function(s) {
      html += mwEntryHTML(s);
    });
    html += '</div>';
  }
  html += '</div></div></div>';

  main.innerHTML = html;
}

function mwToggleAccordion(key, headerEl) {
  var body = document.getElementById('mwAccordion-' + key);
  if (!body) return;
  var arrow = headerEl.querySelector('.mw-accordion-arrow');
  var isCollapsed = body.classList.contains('collapsed');
  if (isCollapsed) {
    body.classList.remove('collapsed');
    headerEl.classList.remove('collapsed');
    if (arrow) arrow.textContent = '▼';
    _accordionState[key] = true;
    localStorage.setItem('mw_accordion_' + key, 'true');
  } else {
    body.classList.add('collapsed');
    headerEl.classList.add('collapsed');
    if (arrow) arrow.textContent = '▲';
    _accordionState[key] = false;
    localStorage.setItem('mw_accordion_' + key, 'false');
  }
}

function mwExpandAll() {
  document.querySelectorAll('.mw-accordion-body.collapsed').forEach(function(body) {
    body.classList.remove('collapsed');
  });
  document.querySelectorAll('.mw-accordion-header.collapsed').forEach(function(header) {
    header.classList.remove('collapsed');
  });
  document.querySelectorAll('.mw-accordion-arrow').forEach(function(arrow) {
    arrow.textContent = '▼';
  });
  _accordionState['uncat'] = true;
  localStorage.setItem('mw_accordion_uncat', 'true');
  _accordionState['all'] = true;
  localStorage.setItem('mw_accordion_all', 'true');
  MW_COLLECTIONS.forEach(function(c) {
    var cKey = 'coll-' + c.id;
    _accordionState[cKey] = true;
    localStorage.setItem('mw_accordion_' + cKey, 'true');
  });
}

function mwCollapseAll() {
  document.querySelectorAll('.mw-accordion-body').forEach(function(body) {
    body.classList.add('collapsed');
  });
  document.querySelectorAll('.mw-accordion-header').forEach(function(header) {
    header.classList.add('collapsed');
  });
  document.querySelectorAll('.mw-accordion-arrow').forEach(function(arrow) {
    arrow.textContent = '▲';
  });
  _accordionState['uncat'] = false;
  localStorage.setItem('mw_accordion_uncat', 'false');
  _accordionState['all'] = false;
  localStorage.setItem('mw_accordion_all', 'false');
  MW_COLLECTIONS.forEach(function(c) {
    var cKey = 'coll-' + c.id;
    _accordionState[cKey] = false;
    localStorage.setItem('mw_accordion_' + cKey, 'false');
  });
}

function mwEntryHTML(s) {
  return '<div class="mw-entry-wrap" id="mwEntry-' + s.wordObjectId + '">'
    + '<a href="/lexicon/' + s.smartId + '" class="mw-entry">'
    + '<span class="mw-hanzi">' + escHtml(s.traditional) + '</span>'
    + '<div class="mw-mid">'
    + '<span class="mw-pinyin">' + escHtml(s.pinyin) + '</span>'
    + '<span class="mw-def">' + escHtml(s.definition) + '</span>'
    + (s.domain ? '<span class="mw-domain-chip">' + escHtml(s.domain) + '</span>' : '')
    + '</div>'
    + '<button class="mw-unsave" onclick="mwShowCollectionPicker(event,' + s.wordObjectId + ')" title="Manage collections">&#9733;</button>'
    + '</a>'
    + '</div>';
}

function mwLearningStatus(wordObjectId) {
  var p = MW_PROGRESS[wordObjectId];
  if (!p) return '<span class="mw-status" title="Not yet tested (0/3)">🟥</span>';
  var passed = (p.pinyin_passed ? 1 : 0) + (p.definition_passed ? 1 : 0) + (p.usage_passed ? 1 : 0);
  var details = (p.pinyin_passed ? '✓' : '✗') + ' Pinyin  '
              + (p.definition_passed ? '✓' : '✗') + ' Definition  '
              + (p.usage_passed ? '✓' : '✗') + ' Usage';
  if (passed >= 3) return '<span class="mw-status" title="Learned! ' + details + '">💚</span>';
  if (passed >= 2) return '<span class="mw-status" title="' + passed + '/3 — ' + details + '">🟡</span>';
  if (passed >= 1) return '<span class="mw-status" title="' + passed + '/3 — ' + details + '">🔶</span>';
  return '<span class="mw-status" title="0/3 — ' + details + '">🟥</span>';
}

function mwCollEntryHTML(s, collectionId) {
  return '<a href="/lexicon/' + s.smartId + '" class="mw-entry">'
    + '<span class="mw-hanzi">' + escHtml(s.traditional) + '</span>'
    + '<div class="mw-mid">'
    + '<div class="mw-pinyin-row"><span class="mw-pinyin">' + escHtml(s.pinyin) + '</span>' + mwLearningStatus(s.wordObjectId) + '</div>'
    + '<span class="mw-def">' + escHtml(s.definition) + '</span>'
    + (s.domain ? '<span class="mw-domain-chip">' + escHtml(s.domain) + '</span>' : '')
    + '</div>'
    + '<button class="mw-remove" onclick="mwRemoveFromCollection(event,' + s.wordObjectId + ',' + collectionId + ')" title="Remove from collection">&times;</button>'
    + '</a>';
}

function mwRemoveFromCollection(event, wordObjectId, collectionId) {
  event.preventDefault();
  event.stopPropagation();
  fetch('/api/collections/' + collectionId + '/words/' + wordObjectId, {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
  }).then(function(r) { return r.json(); }).then(function(data) {
    var c = MW_COLLECTIONS.find(function(c) { return c.id === collectionId; });
    if (c) c.words = c.words.filter(function(s) { return s.wordObjectId !== wordObjectId; });
    // If backend confirmed the word is no longer in any collection, remove from All Saved too
    if (data.unsaved) {
      MW_SAVED = MW_SAVED.filter(function(s) { return s.wordObjectId !== wordObjectId; });
    }
    mwRender();
  });
}

function mwUnsave(event, wordObjectId) {
  event.preventDefault();
  event.stopPropagation();
  fetch('/api/saved-words/' + wordObjectId, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
  }).then(function(r) { return r.json(); }).then(function() {
    // Remove from saved
    MW_SAVED = MW_SAVED.filter(function(s) { return s.wordObjectId !== wordObjectId; });
    // Remove from collections
    MW_COLLECTIONS.forEach(function(c) {
      c.words = c.words.filter(function(s) { return s.wordObjectId !== wordObjectId; });
    });
    mwRender();
  });
}

function mwRenameCollection(collectionId) {
  var titleEl = document.getElementById('mwCollTitle-' + collectionId);
  if (!titleEl) return;
  var c = MW_COLLECTIONS.find(function(c) { return c.id === collectionId; });
  if (!c) return;

  var input = document.createElement('input');
  input.type = 'text';
  input.className = 'mw-rename-input';
  input.value = c.name;
  titleEl.innerHTML = '';
  titleEl.appendChild(input);
  input.focus();
  input.select();

  function save() {
    var newName = input.value.trim();
    if (!newName || newName === c.name) { mwRender(); return; }
    fetch('/api/collections/' + collectionId, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({ name: newName }),
    }).then(function(r) { return r.json(); }).then(function() {
      c.name = newName;
      mwRender();
    });
  }

  input.addEventListener('blur', save);
  input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { input.blur(); }
    if (e.key === 'Escape') { mwRender(); }
  });
}

function mwDeleteCollection(collectionId) {
  var section = document.querySelector('[data-collection-id="' + collectionId + '"]');
  var body = section ? section.querySelector('.mw-section-body') : null;
  if (!body) return;
  // Remove any existing confirm bar first
  var existing = body.querySelector('.confirm-delete-bar');
  if (existing) { existing.remove(); return; }
  // Insert right after the actions bar
  var actions = body.querySelector('.mw-section-actions');
  var bar = document.createElement('div');
  bar.className = 'confirm-delete-bar';
  bar.innerHTML = '<span class="confirm-delete-msg">Delete this collection? Words stay in your library.</span>'
    + '<button class="confirm-delete-yes">Delete</button>'
    + '<button class="confirm-delete-no">Cancel</button>';
  bar.onclick = function(e) { e.stopPropagation(); };
  if (actions && actions.nextSibling) {
    body.insertBefore(bar, actions.nextSibling);
  } else {
    body.insertBefore(bar, body.firstChild);
  }
  bar.querySelector('.confirm-delete-yes').onclick = function(e) { e.stopPropagation(); bar.remove(); doDelete(); };
  bar.querySelector('.confirm-delete-no').onclick = function(e) { e.stopPropagation(); bar.remove(); };
  function doDelete() {
    fetch('/api/collections/' + collectionId, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
    }).then(function() {
      MW_COLLECTIONS = MW_COLLECTIONS.filter(function(c) { return c.id !== collectionId; });
      mwRender();
    });
  }
}

// ── IMPORT WORDS INTO COLLECTION ─────────────────────────────────────────────
function mwImportCollection(collectionId) {
  var section = document.querySelector('[data-collection-id="' + collectionId + '"]');
  var body = section ? section.querySelector('.mw-section-body') : null;
  if (!body) return;

  // Remove existing import panel if any
  var existing = body.querySelector('.mw-import-panel');
  if (existing) { existing.remove(); return; }

  var panel = document.createElement('div');
  panel.className = 'mw-import-panel';
  panel.innerHTML = ''
    + '<div style="border:1px solid var(--border);border-radius:4px;padding:0.8rem;margin:0.5rem 0;background:var(--surface)">'
    + '<div style="font-family:\'DM Mono\',monospace;font-size:0.7rem;color:var(--accent);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.05em">Import Words</div>'
    + '<div style="font-family:\'Cormorant Garamond\',serif;font-size:0.85rem;color:var(--dim);margin-bottom:0.6rem">'
    + 'Upload a <strong>.txt</strong> file (one word per line) or a <strong>.csv</strong> file (comma-separated words).'
    + '</div>'
    + '<div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap">'
    + '<input type="file" accept=".txt,.csv" id="mwImportFile-' + collectionId + '" style="font-family:\'DM Mono\',monospace;font-size:0.7rem">'
    + '<select id="mwImportMode-' + collectionId + '" style="font-family:\'DM Mono\',monospace;font-size:0.7rem;padding:0.3rem 0.5rem;border:1px solid var(--border);border-radius:2px">'
    + '<option value="append">Append</option>'
    + '<option value="overwrite">Overwrite</option>'
    + '</select>'
    + '<button onclick="mwDoImport(' + collectionId + ')" style="font-family:\'DM Mono\',monospace;font-size:0.7rem;padding:0.3rem 0.8rem;background:var(--accent);color:white;border:none;border-radius:2px;cursor:pointer">Import</button>'
    + '<button onclick="this.closest(\'.mw-import-panel\').remove()" style="font-family:\'DM Mono\',monospace;font-size:0.7rem;padding:0.3rem 0.5rem;background:none;border:1px solid var(--border);border-radius:2px;cursor:pointer;color:var(--dim)">Cancel</button>'
    + '</div>'
    + '<div id="mwImportResult-' + collectionId + '" style="margin-top:0.5rem"></div>'
    + '</div>';

  body.insertBefore(panel, body.querySelector('.mw-list') || body.querySelector('.mw-empty'));
}

function mwDoImport(collectionId) {
  var fileInput = document.getElementById('mwImportFile-' + collectionId);
  var modeSelect = document.getElementById('mwImportMode-' + collectionId);
  var resultDiv = document.getElementById('mwImportResult-' + collectionId);
  if (!fileInput || !fileInput.files.length) {
    resultDiv.innerHTML = '<span style="color:var(--rose);font-size:0.75rem">Please select a file.</span>';
    return;
  }

  var file = fileInput.files[0];
  var mode = modeSelect.value;
  var reader = new FileReader();

  reader.onload = function(e) {
    var text = e.target.result.trim();
    var words = [];

    if (file.name.endsWith('.csv')) {
      // CSV: split by commas, newlines, or both
      words = text.split(/[,\n\r]+/).map(function(w) { return w.trim(); }).filter(Boolean);
    } else {
      // TXT: one word per line
      words = text.split(/\n+/).map(function(w) { return w.trim(); }).filter(Boolean);
    }

    // Remove duplicates
    words = words.filter(function(w, i) { return words.indexOf(w) === i; });

    if (words.length === 0) {
      resultDiv.innerHTML = '<span style="color:var(--rose);font-size:0.75rem">No words found in file.</span>';
      return;
    }

    resultDiv.innerHTML = '<span style="color:var(--dim);font-size:0.75rem;font-style:italic">Importing ' + words.length + ' words...</span>';

    fetch('/api/collections/' + collectionId + '/import', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ words: words, mode: mode }),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.error) {
        resultDiv.innerHTML = '<span style="color:var(--rose);font-size:0.75rem">' + data.error + '</span>';
        return;
      }
      var msg = '<span style="font-size:0.75rem;font-family:\'DM Mono\',monospace">';
      msg += '<span style="color:var(--jade)">Added: ' + data.added + '</span>';
      if (data.already_in > 0) msg += ' · <span style="color:var(--dim)">Already in: ' + data.already_in + '</span>';
      if (data.not_found.length > 0) msg += ' · <span style="color:var(--rose)">Not found: ' + data.not_found.join(', ') + '</span>';
      msg += '</span>';
      resultDiv.innerHTML = msg;

      // Reload page to reflect changes
      if (data.added > 0) {
        setTimeout(function() { window.location.reload(); }, 1500);
      }
    })
    .catch(function() {
      resultDiv.innerHTML = '<span style="color:var(--rose);font-size:0.75rem">Import failed.</span>';
    });
  };

  reader.readAsText(file);
}

// ── MY WORDS COLLECTION PICKER ──────────────────────────────────────────────
var _mwCpDismissHandler = null;

function mwShowCollectionPicker(event, wordObjectId) {
  event.preventDefault();
  event.stopPropagation();
  mwDismissCollectionPicker();

  var wrap = document.getElementById('mwEntry-' + wordObjectId);
  if (!wrap) return;

  var html = '<div class="mw-cp-title">Manage collections</div>';

  // Unsave option
  html += '<label class="mw-cp-item" style="color:var(--dim);border-bottom:1px solid var(--border);padding-bottom:0.35rem;margin-bottom:0.15rem">'
    + '<input type="checkbox" onchange="mwCpUnsave(this,' + wordObjectId + ')">'
    + '<span>Unsave word</span></label>';

  // Collection list
  MW_COLLECTIONS.forEach(function(c) {
    var inColl = c.words.some(function(w) { return w.wordObjectId === wordObjectId; });
    html += '<label class="mw-cp-item">'
      + '<input type="checkbox"' + (inColl ? ' checked' : '') + ' onchange="mwCpToggle(' + c.id + ',' + wordObjectId + ',this)">'
      + '<span>' + escHtml(c.name) + '</span></label>';
  });

  // New collection
  html += '<div class="mw-cp-new">'
    + '<input type="text" id="mwCpNewInput-' + wordObjectId + '" placeholder="New collection…" '
    + 'onkeydown="if(event.key===\'Enter\')mwCpCreate(' + wordObjectId + ')">'
    + '<button onclick="mwCpCreate(' + wordObjectId + ')" title="Create">+</button>'
    + '</div>';

  var popover = document.createElement('div');
  popover.className = 'mw-cp';
  popover.id = 'mwCollectionPicker';
  popover.innerHTML = html;
  wrap.appendChild(popover);

  setTimeout(function() {
    _mwCpDismissHandler = function(e) {
      if (!e.target.closest('.mw-cp') && !e.target.closest('.mw-unsave')) {
        mwDismissCollectionPicker();
      }
    };
    document.addEventListener('click', _mwCpDismissHandler);
  }, 10);
}

function mwDismissCollectionPicker() {
  var el = document.getElementById('mwCollectionPicker');
  if (el) el.remove();
  if (_mwCpDismissHandler) {
    document.removeEventListener('click', _mwCpDismissHandler);
    _mwCpDismissHandler = null;
  }
}

function mwCpToggle(collectionId, wordObjectId, checkbox) {
  var method = checkbox.checked ? 'POST' : 'DELETE';
  fetch('/api/collections/' + collectionId + '/words/' + wordObjectId, {
    method: method,
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
  }).then(function(r) { return r.json(); }).then(function() {
    // Find the word data from MW_SAVED
    var wordData = MW_SAVED.find(function(s) { return s.wordObjectId === wordObjectId; });
    var c = MW_COLLECTIONS.find(function(c) { return c.id === collectionId; });
    if (!c) return;

    if (checkbox.checked && wordData) {
      if (!c.words.some(function(w) { return w.wordObjectId === wordObjectId; })) {
        c.words.push(wordData);
      }
    } else {
      c.words = c.words.filter(function(w) { return w.wordObjectId !== wordObjectId; });
    }
    mwDismissCollectionPicker();
    mwRender();
  });
}

function mwCpUnsave(checkbox, wordObjectId) {
  if (!checkbox.checked) return;
  fetch('/api/saved-words/' + wordObjectId, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
  }).then(function(r) { return r.json(); }).then(function() {
    MW_SAVED = MW_SAVED.filter(function(s) { return s.wordObjectId !== wordObjectId; });
    MW_COLLECTIONS.forEach(function(c) {
      c.words = c.words.filter(function(s) { return s.wordObjectId !== wordObjectId; });
    });
    mwDismissCollectionPicker();
    mwRender();
  });
}

function mwCpCreate(wordObjectId) {
  var input = document.getElementById('mwCpNewInput-' + wordObjectId);
  if (!input || !input.value.trim()) return;
  var name = input.value.trim();
  input.disabled = true;

  fetch('/api/collections', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
    body: JSON.stringify({ name: name }),
  })
  .then(function(r) { return r.json(); })
  .then(function(collection) {
    return fetch('/api/collections/' + collection.id + '/words/' + wordObjectId, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
    }).then(function() { return collection; });
  })
  .then(function(collection) {
    var wordData = MW_SAVED.find(function(s) { return s.wordObjectId === wordObjectId; });
    MW_COLLECTIONS.push({ id: collection.id, name: collection.name, nameZh: collection.name_zh, words: wordData ? [wordData] : [] });
    mwDismissCollectionPicker();
    mwRender();
  });
}

// ── NEW COLLECTION ──────────────────────────────────────────────────────────
function mwNewCollection() {
  var formDiv = document.getElementById('mwNewCollForm');
  if (!formDiv) return;
  // Toggle — if form already showing, remove it
  if (formDiv.innerHTML.trim()) { formDiv.innerHTML = ''; return; }

  formDiv.innerHTML = ''
    + '<div style="display:flex;gap:0.5rem;align-items:center;margin-bottom:0.75rem;padding:0.5rem 0.6rem;border:1px solid var(--border);border-radius:4px;background:var(--surface)">'
    + '<input type="text" id="mwNewCollInput" class="mw-rename-input" placeholder="Collection name…" style="flex:1;text-transform:none" onkeydown="if(event.key===\'Enter\')mwDoCreateCollection()">'
    + '<button onclick="mwDoCreateCollection()" style="font-family:\'DM Mono\',monospace;font-size:0.7rem;padding:0.3rem 0.6rem;background:var(--accent);color:white;border:none;border-radius:2px;cursor:pointer">Create</button>'
    + '<button onclick="document.getElementById(\'mwNewCollForm\').innerHTML=\'\'" style="font-family:\'DM Mono\',monospace;font-size:0.7rem;padding:0.3rem 0.5rem;background:none;border:1px solid var(--border);border-radius:2px;cursor:pointer;color:var(--dim)">Cancel</button>'
    + '</div>';

  var input = document.getElementById('mwNewCollInput');
  if (input) input.focus();
}

function mwDoCreateCollection() {
  var input = document.getElementById('mwNewCollInput');
  if (!input) return;
  var name = input.value.trim();
  if (!name) return;
  input.disabled = true;

  fetch('/api/collections', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
    body: JSON.stringify({ name: name }),
  })
  .then(function(r) { return r.json(); })
  .then(function(collection) {
    MW_COLLECTIONS.push({ id: collection.id, name: collection.name, nameZh: collection.name_zh, words: [] });
    document.getElementById('mwNewCollForm').innerHTML = '';
    mwRender();
    // Auto-open the new collection accordion
    var cKey = 'coll-' + collection.id;
    _accordionState[cKey] = true;
    localStorage.setItem('mw_accordion_' + cKey, 'true');
    mwRender();
  })
  .catch(function() {
    input.disabled = false;
    input.style.borderColor = '#c44';
  });
}

// ── BUILD COLLECTION ─────────────────────────────────────────────────────────
function mwBuildCollection(collectionId) {
  var section = document.querySelector('[data-collection-id="' + collectionId + '"]');
  var body = section ? section.querySelector('.mw-section-body') : null;
  if (!body) return;

  var existing = body.querySelector('.mw-build-panel');
  if (existing) { existing.remove(); return; }

  // Build collection checklist (exclude current)
  var collChecks = '';
  MW_COLLECTIONS.forEach(function(c) {
    if (c.id !== collectionId) {
      collChecks += '<label><input type="checkbox" value="' + c.id + '" checked> ' + escHtml(c.name) + ' (' + c.words.length + ')</label>';
    }
  });

  var panel = document.createElement('div');
  panel.className = 'mw-build-panel';
  panel.innerHTML = ''
    + '<div style="border:1px solid var(--border);border-radius:4px;padding:0.8rem;margin:0.5rem 0;background:var(--surface)">'
    + '<div style="font-family:\'DM Mono\',monospace;font-size:0.7rem;color:var(--accent);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.05em">Build Collection</div>'
    + '<div class="mw-build-row">'
    + '<label>Randomly select</label>'
    + '<input type="number" class="mw-build-input" id="mwBuildCount-' + collectionId + '" value="10" min="1" max="200">'
    + '<label>words</label>'
    + '</div>'
    + '<div class="mw-build-row">'
    + '<label>from</label>'
    + '<select class="mw-build-select" id="mwBuildLevel-' + collectionId + '">'
    + '<option value="novice1">Novice 1 準備級一級</option>'
    + '<option value="novice2">Novice 2 準備級二級</option>'
    + '<option value="entry">Entry 入門級</option>'
    + '<option value="basic">Basic 基礎級</option>'
    + '<option value="advanced">Advanced 進階級</option>'
    + '<option value="high">High 高階級</option>'
    + '<option value="fluency">Fluency 流利級</option>'
    + '</select>'
    + '</div>'
    + '<div class="mw-build-row"><label>that are not in:</label></div>'
    + '<div class="mw-build-radio" id="mwBuildExclusion-' + collectionId + '">'
    + '<label><input type="radio" name="mwBuildMode-' + collectionId + '" value="all" checked onchange="mwBuildModeChange(' + collectionId + ')"> Any of my collections</label>'
    + '<label><input type="radio" name="mwBuildMode-' + collectionId + '" value="selected" onchange="mwBuildModeChange(' + collectionId + ')"> Selected collections:</label>'
    + '</div>'
    + '<div class="mw-build-checks" id="mwBuildChecks-' + collectionId + '" style="display:none">'
    + collChecks
    + '</div>'
    + '<div style="display:flex;gap:0.5rem;margin-top:0.3rem">'
    + '<button onclick="mwDoBuild(' + collectionId + ')" style="font-family:\'DM Mono\',monospace;font-size:0.7rem;padding:0.3rem 0.8rem;background:var(--accent);color:white;border:none;border-radius:2px;cursor:pointer">Build</button>'
    + '<button onclick="this.closest(\'.mw-build-panel\').remove()" style="font-family:\'DM Mono\',monospace;font-size:0.7rem;padding:0.3rem 0.5rem;background:none;border:1px solid var(--border);border-radius:2px;cursor:pointer;color:var(--dim)">Cancel</button>'
    + '</div>'
    + '<div id="mwBuildResult-' + collectionId + '" style="margin-top:0.5rem"></div>'
    + '</div>';

  body.insertBefore(panel, body.querySelector('.mw-progress-wrap') || body.querySelector('.mw-list') || body.querySelector('.mw-empty'));
}

function mwBuildModeChange(collectionId) {
  var mode = document.querySelector('input[name="mwBuildMode-' + collectionId + '"]:checked').value;
  var checks = document.getElementById('mwBuildChecks-' + collectionId);
  if (checks) checks.style.display = mode === 'selected' ? 'flex' : 'none';
}

function mwDoBuild(collectionId) {
  var count = parseInt(document.getElementById('mwBuildCount-' + collectionId).value) || 10;
  var level = document.getElementById('mwBuildLevel-' + collectionId).value;
  var mode = document.querySelector('input[name="mwBuildMode-' + collectionId + '"]:checked').value;
  var resultDiv = document.getElementById('mwBuildResult-' + collectionId);

  var excludedIds = [];
  if (mode === 'selected') {
    var checks = document.querySelectorAll('#mwBuildChecks-' + collectionId + ' input[type="checkbox"]:checked');
    checks.forEach(function(cb) { excludedIds.push(parseInt(cb.value)); });
  }

  resultDiv.innerHTML = '<span style="color:var(--dim);font-size:0.75rem;font-style:italic">Building...</span>';

  fetch('/api/collections/' + collectionId + '/build', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf,
      'Accept': 'application/json',
    },
    body: JSON.stringify({
      count: count,
      tocfl_level: level,
      exclusion_mode: mode,
      excluded_collection_ids: excludedIds,
    }),
  })
  .then(function(r) {
    if (!r.ok) return r.text().then(function(t) { throw new Error('HTTP ' + r.status + ': ' + t); });
    return r.json();
  })
  .then(function(data) {
    if (data.error) {
      resultDiv.innerHTML = '<span style="color:var(--rose);font-size:0.75rem">' + data.error + '</span>';
      return;
    }
    var msg = '<span style="font-size:0.75rem;font-family:\'DM Mono\',monospace">';
    msg += '<span style="color:var(--jade)">Added: ' + data.added + '</span>';
    if (data.added < data.requested) {
      msg += ' · <span style="color:var(--dim)">Only ' + data.available + ' available at this level</span>';
    }
    msg += ' · <span style="color:var(--dim)">Collection total: ' + data.total + '</span>';
    msg += '</span>';
    resultDiv.innerHTML = msg;
    if (data.added > 0) {
      setTimeout(function() { window.location.reload(); }, 1500);
    }
  })
  .catch(function(err) {
    resultDiv.innerHTML = '<span style="color:var(--rose);font-size:0.75rem">Build failed: ' + err.message + '</span>';
  });
}

function escHtml(str) {
  if (!str) return '';
  var div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

mwRender();
</script>
@include('partials.lexicon._site-footer')
</body>
</html>
