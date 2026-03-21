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
/* ── HEADER ── */
.mw-header {
  position: sticky; top: 0; z-index: 100;
  background: rgba(255,255,255,0.95); backdrop-filter: blur(8px);
  border-bottom: 1px solid var(--border);
  padding: 0.8rem 1.2rem;
  display: flex; align-items: center; gap: 0.6rem;
}
.mw-back {
  font-family: 'DM Mono', monospace; font-size: 0.75rem;
  color: var(--accent); background: none; border: none;
  cursor: pointer; text-decoration: none;
  transition: opacity 0.15s;
}
.mw-back:hover { opacity: 0.7; }
.mw-title {
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--dim); flex: 1;
}

/* ── MAIN ── */
.mw-main { max-width: 640px; margin: 0 auto; padding: 1.5rem 1rem 3rem; }

/* ── SECTION ── */
.mw-section { margin-bottom: 2rem; }
.mw-section-header {
  display: flex; align-items: center; gap: 0.5rem;
  margin-bottom: 0.5rem;
}
.mw-section-title {
  font-family: 'DM Mono', monospace; font-size: 0.65rem;
  letter-spacing: 0.15em; text-transform: uppercase;
  color: var(--dim); flex: 1;
}
.mw-section-count {
  font-family: 'DM Mono', monospace; font-size: 0.6rem;
  color: var(--dim); opacity: 0.7;
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
.mw-list { border: 1px solid var(--border); border-radius: 3px; overflow: hidden; }
.mw-entry {
  display: grid; grid-template-columns: auto 1fr auto;
  gap: 0.5rem; align-items: center;
  padding: 0.6rem 0.75rem;
  border-bottom: 1px solid var(--border);
  text-decoration: none; color: var(--ink);
  transition: background 0.1s;
}
.mw-entry:last-child { border-bottom: none; }
.mw-entry:hover { background: rgba(0,0,0,0.015); }
.mw-hanzi {
  font-family: 'Noto Serif TC', serif; font-size: 1.3rem;
  font-weight: 400; line-height: 1.2;
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
  color: var(--dim); text-align: center; padding: 2rem 1rem;
}
.mw-empty a { color: var(--accent); text-decoration: none; }
.mw-empty a:hover { text-decoration: underline; }

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
  var MW_SAVED = @json($savedSenses);
  var MW_COLLECTIONS = @json($collections);
</script>

<div class="mw-header">
  <a href="{{ route('lexicon.index') }}" class="mw-back">&larr; Lexicon</a>
  <div class="mw-title">My Words</div>
  @include('partials.lexicon._user-menu')
</div>

<div class="mw-main" id="mwMain"></div>

<script>
var csrf = document.querySelector('meta[name="csrf-token"]').content;

function mwRender() {
  var main = document.getElementById('mwMain');
  if (!main) return;
  var html = '';

  // ── All Saved ──
  html += '<div class="mw-section">';
  html += '<div class="mw-section-header">';
  html += '<div class="mw-section-title">All Saved</div>';
  html += '<span class="mw-section-count">' + MW_SAVED.length + '</span>';
  html += '</div>';

  if (MW_SAVED.length === 0) {
    html += '<div class="mw-empty">No saved words yet. <a href="{{ route('lexicon.index') }}">Explore the lexicon</a> and tap ☆ to save.</div>';
  } else {
    html += '<div class="mw-list">';
    MW_SAVED.forEach(function(s) {
      html += mwEntryHTML(s);
    });
    html += '</div>';
  }
  html += '</div>';

  // ── Collections ──
  MW_COLLECTIONS.forEach(function(c) {
    html += '<div class="mw-section" data-collection-id="' + c.id + '">';
    html += '<div class="mw-section-header">';
    html += '<div class="mw-section-title" id="mwCollTitle-' + c.id + '">' + escHtml(c.name) + (c.nameZh ? ' · ' + escHtml(c.nameZh) : '') + '</div>';
    html += '<span class="mw-section-count">' + c.senses.length + '</span>';
    html += '<button class="mw-action-btn" onclick="mwRenameCollection(' + c.id + ')">Rename</button>';
    html += '<button class="mw-action-btn danger" onclick="mwDeleteCollection(' + c.id + ')">Delete</button>';
    html += '</div>';

    if (c.senses.length === 0) {
      html += '<div class="mw-empty">No words in this collection yet.</div>';
    } else {
      html += '<div class="mw-list">';
      c.senses.forEach(function(s) {
        html += mwCollEntryHTML(s, c.id);
      });
      html += '</div>';
    }
    html += '</div>';
  });

  main.innerHTML = html;
}

function mwEntryHTML(s) {
  return '<a href="/lexicon/' + s.smartId + '" class="mw-entry">'
    + '<span class="mw-hanzi">' + escHtml(s.traditional) + '</span>'
    + '<div class="mw-mid">'
    + '<span class="mw-pinyin">' + escHtml(s.pinyin) + '</span>'
    + '<span class="mw-def">' + escHtml(s.definition) + '</span>'
    + (s.domain ? '<span class="mw-domain-chip">' + escHtml(s.domain) + '</span>' : '')
    + '</div>'
    + '<button class="mw-unsave" onclick="mwUnsave(event,' + s.senseId + ')" title="Unsave">&#9733;</button>'
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
    + '<button class="mw-remove" onclick="mwRemoveFromCollection(event,' + s.senseId + ',' + collectionId + ')" title="Remove from collection">&times;</button>'
    + '</a>';
}

function mwRemoveFromCollection(event, senseId, collectionId) {
  event.preventDefault();
  event.stopPropagation();
  fetch('/api/collections/' + collectionId + '/senses/' + senseId, {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
  }).then(function(r) { return r.json(); }).then(function() {
    var c = MW_COLLECTIONS.find(function(c) { return c.id === collectionId; });
    if (c) c.senses = c.senses.filter(function(s) { return s.senseId !== senseId; });
    mwRender();
  });
}

function mwUnsave(event, senseId) {
  event.preventDefault();
  event.stopPropagation();
  fetch('/api/saved-senses/' + senseId, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
  }).then(function(r) { return r.json(); }).then(function() {
    // Remove from saved
    MW_SAVED = MW_SAVED.filter(function(s) { return s.senseId !== senseId; });
    // Remove from collections
    MW_COLLECTIONS.forEach(function(c) {
      c.senses = c.senses.filter(function(s) { return s.senseId !== senseId; });
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
  if (!confirm('Delete this collection? Saved words will remain in your library.')) return;
  fetch('/api/collections/' + collectionId, {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
  }).then(function() {
    MW_COLLECTIONS = MW_COLLECTIONS.filter(function(c) { return c.id !== collectionId; });
    mwRender();
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
</body>
</html>
