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

  // ── Open All / Close All ──
  html += '<div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-bottom:0.5rem">';
  html += '<button class="mw-action-btn" style="color:var(--accent)" onclick="mwExpandAll()">Open All</button>';
  html += '<button class="mw-action-btn" onclick="mwCollapseAll()">Close All</button>';
  html += '</div>';

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
    html += '<button class="mw-action-btn danger" onclick="mwDeleteCollection(' + c.id + ')">Delete</button>';
    html += '</div>';

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
  return '<a href="/lexicon/' + s.smartId + '" class="mw-entry">'
    + '<span class="mw-hanzi">' + escHtml(s.traditional) + '</span>'
    + '<div class="mw-mid">'
    + '<span class="mw-pinyin">' + escHtml(s.pinyin) + '</span>'
    + '<span class="mw-def">' + escHtml(s.definition) + '</span>'
    + (s.domain ? '<span class="mw-domain-chip">' + escHtml(s.domain) + '</span>' : '')
    + '</div>'
    + '<button class="mw-unsave" onclick="mwUnsave(event,' + s.wordObjectId + ')" title="Unsave">&#9733;</button>'
    + '</a>';
}

function mwCollEntryHTML(s, collectionId) {
  return '<a href="/lexicon/' + s.smartId + '" class="mw-entry">'
    + '<span class="mw-hanzi">' + escHtml(s.traditional) + '</span>'
    + '<div class="mw-mid">'
    + '<span class="mw-pinyin">' + escHtml(s.pinyin) + '</span>'
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
  showDeleteConfirm(body, 'Delete this collection? Words stay in your library.', function() {
    fetch('/api/collections/' + collectionId, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
    }).then(function() {
      MW_COLLECTIONS = MW_COLLECTIONS.filter(function(c) { return c.id !== collectionId; });
      mwRender();
    });
  });
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
