@php use App\Helpers\PinyinHelper; @endphp
@extends('admin.layout')
@section('title', 'Review Import')

@push('styles')
    @include('admin.partials.attr-chips')
    <style>
    .csv-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 0.75rem; overflow: hidden; margin-bottom: 0.75rem; }
    .csv-card-header { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1.25rem; }
    .csv-card-body { border-top: 1px solid #f3f4f6; padding: 1rem 1.25rem; }
    .csv-loading { color: #9ca3af; font-family: 'DM Mono', monospace; font-size: 0.75rem; font-style: italic; padding: 1rem 1.25rem; border-top: 1px solid #f3f4f6; }
    .csv-loading::after { content: ''; animation: csvDots 1.5s infinite; }
    @keyframes csvDots { 0% { content: ''; } 33% { content: '.'; } 66% { content: '..'; } 100% { content: '...'; } }
    .csv-reviewed { background: #f0fdf4; border-color: #bbf7d0; }
    .csv-error { background: #fef2f2; border-color: #fecaca; }
    .csv-gaps { background: #fffbeb; border-top: 1px solid #fde68a; padding: 0.6rem 1.25rem; }
    .csv-complete { background: #f0fdf4; border-top: 1px solid #bbf7d0; padding: 0.6rem 1.25rem; }
    .csv-reviewed .csv-footer { background: #f0fdf4 !important; border-top-color: #bbf7d0 !important; }
    </style>
@endpush

@section('content')

<div class="max-w-4xl">
    <a href="{{ route('admin.words.csv-import') }}" class="text-sm text-gray-500 hover:text-gray-700">← Upload another</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2 mb-1">Review 師父's Enrichment</h1>
    <p class="text-sm text-gray-500 mb-2">{{ count($wordList) }} words in this batch. Words are processed one at a time.</p>

    @if ($hasMore)
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2 mb-4">
            <p class="text-xs text-indigo-800">
                <strong>{{ $remainingCount }} more words queued.</strong>
                After this batch, you'll proceed to the next.
            </p>
        </div>
    @endif

    {{-- Progress bar --}}
    <div class="mb-4">
        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
            <span id="csvProgress">Processing...</span>
            <span id="csvCount">0 / {{ count($wordList) }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-1.5">
            <div id="csvBar" class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
    </div>

    {{-- Word cards --}}
    <div id="csvWords">
        @foreach ($wordList as $i => $word)
            <div class="csv-card {{ $word['reviewed'] ? 'csv-reviewed' : '' }}" id="csvWord-{{ $i }}">
                <div class="csv-card-header">
                    <div class="flex items-center gap-3">
                        <span class="cn text-2xl font-bold text-gray-900">{{ $word['traditional'] }}</span>
                        @if ($word['exists'])
                            <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded font-medium">Exists</span>
                            <a href="{{ route('lexicon.show', collect(mb_str_split($word['traditional']))->map(fn($c) => 'u' . strtolower(dechex(mb_ord($c))))->join('_')) }}"
                               target="_blank" class="text-xs text-indigo-500 hover:text-indigo-700" title="View in Lexicon">↗ Lexicon</a>
                        @endif
                        @if ($word['reviewed'])
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded font-medium">
                                師父 reviewed {{ \Carbon\Carbon::parse($word['reviewed_at'])->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2" id="csvActions-{{ $i }}">
                        @if ($word['reviewed'])
                            <button onclick="csvForceReview({{ $i }}, '{{ $word['traditional'] }}')"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium" id="csvForce-{{ $i }}">
                                Force Review →
                            </button>
                        @endif
                    </div>
                </div>

                @if ($word['reviewed'])
                    {{-- Already reviewed — no loading needed --}}
                @else
                    <div class="csv-loading" id="csvLoading-{{ $i }}">
                        {{ $word['exists'] ? 'Auditing' : 'Enriching' }} with 師父
                    </div>
                @endif

                {{-- Content loads here via AJAX --}}
                <div id="csvContent-{{ $i }}" style="display:none"></div>
            </div>
        @endforeach
    </div>

    @if ($hasMore)
        <div class="mt-6">
            <a href="{{ route('admin.words.csv-import.next') }}"
               class="px-5 py-2.5 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors inline-block">
                Next Batch ({{ $remainingCount }} remaining) →
            </a>
        </div>
    @else
        <div class="mt-6">
            <a href="{{ route('admin.words.index') }}"
               class="px-5 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 transition-colors inline-block">
                ← Back to Words
            </a>
        </div>
    @endif
</div>

<script>
var CSV_WORDS = @json($wordList);
var CSV_CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
var csvProcessed = 0;
var csvTotal = CSV_WORDS.length;

// ── Emoji maps ────────────────────────────────────────────────────
var registerIcons    = {literary:'🦋',formal:'🐝',standard:'🐞',colloquial:'🪲',informal:'🦗',slang:'🕷️'};
var connotationIcons = {positive:'☀️','positive-dominant':'🌤️',neutral:'⛅','negative-dominant':'🌥️',negative:'⛈️','context-dependent':'🌦️'};
var channelIcons     = {'spoken-only':'🦎','spoken-dominant':'🐍','channel-balanced':'🦜','written-dominant':'🦚','written-only':'🐉'};
var dimensionIcons   = {abstract:'🐙',concrete:'🐢',internal:'🐟',external:'🦂','dim-fluid':'🦀',aspectual:'🐡',grammatical:'🪼',spatial:'🐚',pragmatic:'🦑',temporal:'🐠'};
var intensityIcons   = {1:'🌸',2:'🌼',3:'🪷',4:'🌻',5:'🌺'};
var intensityLabels  = {1:'Faint',2:'Mild',3:'Moderate',4:'Strong',5:'Blazing'};
var tocflIcons       = {'tocfl-novice1':'🌑','tocfl-novice2':'🌑','tocfl-entry':'🌒','tocfl-basic':'🌓','tocfl-advanced':'🌔','tocfl-high':'🌕','tocfl-fluency':'🌝'};

function escHtml(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function ucfirst(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
function slugLabel(s) { return ucfirst((s||'').replace(/-/g, ' ')); }

// ── Build attribute card HTML ─────────────────────────────────────
function attrCard(cat, slug, header, icon) {
  return '<div class="card-attr attr-' + cat + '">' +
    '<div class="card-attr-header">' + header + '</div>' +
    '<div class="card-attr-value">' +
      (icon ? '<span class="attr-icon">' + icon + '</span>' : '') +
      '<span class="attr-label">' + slugLabel(slug) + '</span>' +
    '</div></div>';
}

function attrCardMulti(cat, slugs, header, iconMap) {
  var items = slugs.map(function(s) {
    return '<span class="attr-val-item"><span class="attr-icon">' + (iconMap[s]||'') + '</span><span class="attr-label">' + slugLabel(s) + '</span></span>';
  }).join('');
  return '<div class="card-attr attr-' + cat + '">' +
    '<div class="card-attr-header">' + header + '</div>' +
    '<div class="card-attr-value multi">' + items + '</div></div>';
}

// ── Render a single enriched word ─────────────────────────────────
function csvRenderWord(idx, result) {
  var data = result.data;
  var gaps = result.gaps || [];
  var existing = result.existing;
  var card = document.getElementById('csvWord-' + idx);
  var content = document.getElementById('csvContent-' + idx);
  var loading = document.getElementById('csvLoading-' + idx);
  var actions = document.getElementById('csvActions-' + idx);

  if (loading) loading.style.display = 'none';
  content.style.display = 'block';

  var html = '';

  // Gaps banner
  if (existing && gaps.length > 0) {
    html += '<div class="csv-gaps"><p style="font-size:0.7rem;font-weight:600;color:#92400e;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.25rem">師父 found gaps:</p><ul style="font-size:0.72rem;color:#92400e">';
    gaps.forEach(function(g) { html += '<li>• ' + escHtml(g) + '</li>'; });
    html += '</ul></div>';
  } else if (existing && gaps.length === 0) {
    html += '<div class="csv-complete"><p style="font-size:0.72rem;color:#15803d">✓ Existing entry looks complete.</p></div>';
  }

  // Store existing sense keys from API response
  var existingKeys = result.existingKeys || [];
  card.dataset.existingKeys = JSON.stringify(existingKeys);

  // Senses
  html += '<div class="csv-card-body">';
  data.senses.forEach(function(sense, si) {
    if (si > 0) html += '<hr style="border:none;border-top:2px solid #e5e7eb;margin:1rem 0">';

    var senseKey = (sense.pinyin || '') + '|' + (sense.pos || '');
    // Match each existing key only ONCE — first match consumes it
    var matchIdx = existingKeys.indexOf(senseKey);
    var isExistingSense = existing && matchIdx !== -1;
    if (isExistingSense) {
      existingKeys.splice(matchIdx, 1); // consume this match
    }
    var isNewSense = existing && !isExistingSense;

    // Sense header with per-sense dropdown
    html += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem">';
    html += '<div style="display:flex;align-items:center;gap:0.5rem">';
    html += '<span style="font-size:0.85rem;font-weight:500;color:#374151">' + escHtml(sense.pinyin || '') + '</span>';
    html += '<span style="font-size:0.7rem;font-family:monospace;color:#4f46e5;background:#eef2ff;padding:0.1rem 0.4rem;border-radius:3px">' + escHtml(sense.pos || '?') + '</span>';
    if (sense.tocfl) html += '<span style="font-size:0.65rem;color:#9ca3af">' + escHtml(sense.tocfl) + '</span>';
    if (sense.hsk) html += '<span style="font-size:0.65rem;color:#9ca3af">' + escHtml(sense.hsk) + '</span>';
    if (sense._warning) html += '<span style="font-size:0.65rem;background:#fee2e2;color:#b91c1c;padding:0.1rem 0.4rem;border-radius:3px">⚠ ' + escHtml(sense._warning) + '</span>';
    if (isExistingSense) html += '<span style="font-size:0.6rem;background:#dbeafe;color:#1e40af;padding:0.1rem 0.4rem;border-radius:3px">Existing sense</span>';
    if (isNewSense) html += '<span style="font-size:0.6rem;background:#dcfce7;color:#166534;padding:0.1rem 0.4rem;border-radius:3px;font-weight:500">New sense</span>';
    html += '</div>';

    // Per-sense dropdown
    html += '<select class="csv-sense-decision rounded-lg border border-gray-300 px-2 py-1 text-xs" id="csvSenseDecision-' + idx + '-' + si + '" style="min-width:160px">';
    if (isExistingSense) {
      // For an existing sense, the source (tocfl/editorial) + TOCFL/HSK
      // level are preserved by the save path. The choice here is what
      // status the enrichment lands at.
      html += '<option value="enrich-publish" selected>✅ Enrich + Publish</option>';
      html += '<option value="enrich-draft">📝 Enrich + Save as Draft</option>';
      html += '<option value="reject">❌ Keep as-is</option>';
    } else if (isNewSense) {
      html += '<option value="draft" selected>📝 Add as Draft</option>';
      html += '<option value="publish">✅ Add & Publish</option>';
      html += '<option value="reject">❌ Skip</option>';
    } else {
      // New word — all senses are new
      html += '<option value="draft" selected>📝 Approve as Draft</option>';
      html += '<option value="publish">✅ Approve & Publish</option>';
      html += '<option value="reject">❌ Reject</option>';
    }
    html += '</select>';
    html += '</div>';

    // Definitions
    html += '<p style="font-size:0.85rem;color:#374151"><span style="font-size:0.65rem;font-family:monospace;color:#4f46e5">EN</span> ' + escHtml(sense.definitions?.en || '') + '</p>';
    if (sense.definitions?.['zh-TW']) {
      html += '<p style="font-size:0.85rem;color:#6b7280"><span style="font-size:0.65rem;font-family:monospace;color:#4f46e5">ZH</span> <span class="cn">' + escHtml(sense.definitions['zh-TW']) + '</span></p>';
    }

    // Formula (bilingual)
    var formulaEn = sense.formula_en || sense.formula || '';
    var formulaZh = sense.formula_zh || '';
    if (formulaEn || formulaZh) {
      html += '<div style="font-size:0.82rem;font-family:monospace;background:#f9fafb;border:1px solid #e5e7eb;border-radius:4px;padding:0.3rem 0.5rem;margin:0.4rem 0">';
      if (formulaEn) html += '<p><span style="font-size:0.6rem;color:#4f46e5;font-family:sans-serif">EN</span> ' + escHtml(formulaEn) + '</p>';
      if (formulaZh) html += '<p><span style="font-size:0.6rem;color:#4f46e5;font-family:sans-serif">ZH</span> ' + escHtml(formulaZh) + '</p>';
      html += '</div>';
    }

    // Attribute cards
    var hasCards = sense.channel || sense.connotation || (sense.register && sense.register.length) || (sense.dimension && sense.dimension.length) || sense.intensity || sense.tocfl;
    if (hasCards) {
      html += '<div class="admin-attrs" style="margin:0.5rem 0">';
      if (sense.register && sense.register.length) html += attrCardMulti('register', sense.register, 'Register', registerIcons);
      if (sense.connotation) html += attrCard('connotation', sense.connotation, 'Connotation', connotationIcons[sense.connotation]);
      if (sense.channel) html += attrCard('channel', sense.channel, 'Channel', channelIcons[sense.channel]);
      if (sense.dimension && sense.dimension.length) html += attrCardMulti('dimension', sense.dimension, 'Dimension', dimensionIcons);
      if (sense.intensity) {
        var intLabel = intensityLabels[sense.intensity] || String(sense.intensity);
        html += '<div class="card-attr attr-intensity"><div class="card-attr-header">Intensity</div><div class="card-attr-value"><span class="attr-icon">' + (intensityIcons[sense.intensity]||'') + '</span><span class="attr-label">' + intLabel + '</span></div></div>';
      }
      if (sense.tocfl) html += attrCard('tocfl', sense.tocfl, 'TOCFL', tocflIcons[sense.tocfl]);
      // semantic_mode retired 2026-04-20 — dimension covers the axis.
      html += '</div>';
    }

    // Domains
    if (sense.domains && sense.domains.length) {
      html += '<div style="display:flex;flex-wrap:wrap;gap:0.3rem;margin:0.4rem 0">';
      sense.domains.forEach(function(d, di) {
        html += '<span style="font-size:0.65rem;padding:0.1rem 0.4rem;border-radius:3px;' + (di===0 ? 'background:#ecfdf5;color:#047857;font-weight:500' : 'background:#f3f4f6;color:#4b5563') + '">' + escHtml(d) + '</span>';
      });
      html += '</div>';
    }

    // Relations
    var rels = sense.relations || {};
    var hasRels = (rels.synonym_close||[]).length || (rels.synonym_related||[]).length || (rels.antonym||[]).length || (rels.contrast||[]).length;
    if (hasRels) {
      html += '<div style="margin:0.4rem 0"><p style="font-size:0.65rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.2rem">Relations</p>';
      if ((rels.synonym_close||[]).length) html += '<p style="font-size:0.72rem"><span style="font-family:monospace;color:#15803d">synonym_close:</span> ' + rels.synonym_close.map(escHtml).join(', ') + '</p>';
      if ((rels.synonym_related||[]).length) html += '<p style="font-size:0.72rem"><span style="font-family:monospace;color:#374151">synonym_related:</span> ' + rels.synonym_related.map(escHtml).join(', ') + '</p>';
      if ((rels.antonym||[]).length) html += '<p style="font-size:0.72rem"><span style="font-family:monospace;color:#b91c1c">antonym:</span> ' + rels.antonym.map(escHtml).join(', ') + '</p>';
      if ((rels.contrast||[]).length) html += '<p style="font-size:0.72rem"><span style="font-family:monospace;color:#7c3aed">contrast:</span> ' + rels.contrast.map(escHtml).join(', ') + '</p>';
      html += '</div>';
    }

    // Collocations
    if (sense.collocations && sense.collocations.length) {
      html += '<p style="font-size:0.65rem;color:#6b7280;margin:0.3rem 0"><span style="font-weight:600;text-transform:uppercase;letter-spacing:0.1em">Collocations:</span> ' + sense.collocations.map(escHtml).join(', ') + '</p>';
    }

    // Examples
    if (sense.examples && sense.examples.length) {
      html += '<div style="margin:0.4rem 0"><p style="font-size:0.65rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.2rem">Examples</p>';
      sense.examples.forEach(function(ex) {
        html += '<div style="font-size:0.85rem;border-left:2px solid #e5e7eb;padding-left:0.6rem;margin-bottom:0.3rem">';
        html += '<p class="cn" style="color:#1f2937">' + escHtml(ex.chinese || '') + '</p>';
        html += '<p style="color:#6b7280;font-size:0.72rem">' + escHtml(ex.english || '') + '</p>';
        html += '</div>';
      });
      html += '</div>';
    }

    // Usage note (bilingual)
    var usageEn = sense.usage_note_en || sense.usage_note || '';
    var usageZh = sense.usage_note_zh || '';
    if (usageEn || usageZh) {
      html += '<div style="font-size:0.82rem;background:#fffbeb;border:1px solid #fde68a;border-radius:4px;padding:0.5rem 0.6rem;margin:0.4rem 0"><span style="font-size:0.65rem;font-weight:600;color:#d97706;text-transform:uppercase;letter-spacing:0.1em">Usage Note</span>';
      if (usageEn) html += '<p style="color:#92400e;margin-top:0.2rem"><span style="font-size:0.6rem;font-weight:600;color:#4f46e5">EN</span> ' + escHtml(usageEn) + '</p>';
      if (usageZh) html += '<p class="cn" style="color:#92400e;margin-top:0.2rem"><span style="font-size:0.6rem;font-weight:600;color:#4f46e5">ZH</span> ' + escHtml(usageZh) + '</p>';
      html += '</div>';
    }

    // Learner traps (bilingual)
    var trapsEn = sense.learner_traps_en || sense.learner_traps || '';
    var trapsZh = sense.learner_traps_zh || '';
    if (trapsEn || trapsZh) {
      html += '<div style="font-size:0.82rem;background:#fef2f2;border:1px solid #fecaca;border-radius:4px;padding:0.5rem 0.6rem;margin:0.4rem 0"><span style="font-size:0.65rem;font-weight:600;color:#dc2626;text-transform:uppercase;letter-spacing:0.1em">Learner Traps</span>';
      if (trapsEn) html += '<p style="color:#991b1b;margin-top:0.2rem"><span style="font-size:0.6rem;font-weight:600;color:#4f46e5">EN</span> ' + escHtml(trapsEn) + '</p>';
      if (trapsZh) html += '<p class="cn" style="color:#991b1b;margin-top:0.2rem"><span style="font-size:0.6rem;font-weight:600;color:#4f46e5">ZH</span> ' + escHtml(trapsZh) + '</p>';
      html += '</div>';
    }
  });

  // Bottom bar: word header repeated + Save button
  html += '<div class="csv-footer" style="display:flex;align-items:center;justify-content:space-between;padding:0.75rem 1.25rem;border-top:2px solid #e5e7eb;background:#f9fafb">';
  html += '<span class="cn" style="font-size:1.25rem;font-weight:700;color:#374151">' + escHtml(data.word.traditional) + '</span>';
  html += '<div style="display:flex;align-items:center;gap:0.5rem"><span class="csv-save-status"></span><button onclick="csvSaveWord(' + idx + ',this)" class="px-4 py-2 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-500">Save</button></div>';
  html += '</div>';

  html += '</div>';
  content.innerHTML = html;

  // Top actions: also a Save button
  actions.innerHTML =
    '<span class="csv-save-status"></span> <button onclick="csvSaveWord(' + idx + ',this)" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-xs font-semibold text-white hover:bg-indigo-500">Save</button>';

  // Store data, existing sense keys, and engagement_id for saving
  card.dataset.enriched = JSON.stringify(data);
  card.dataset.senseCount = data.senses.length;
  card.dataset.engagementId = result.engagement_id || '';
}

// ── Save a single word ────────────────────────────────────────────
function csvSaveWord(idx, btn) {
  var card = document.getElementById('csvWord-' + idx);
  var data = JSON.parse(card.dataset.enriched || '{}');
  var senseCount = parseInt(card.dataset.senseCount || '0');
  var existingKeys = JSON.parse(card.dataset.existingKeys || '[]');
  var engagementId = card.dataset.engagementId || null;
  var status = btn.parentElement.querySelector('.csv-save-status');

  // Collect per-sense decisions
  var senseDecisions = [];
  var allRejected = true;
  for (var si = 0; si < senseCount; si++) {
    var sel = document.getElementById('csvSenseDecision-' + idx + '-' + si);
    var decision = sel ? sel.value : 'reject';
    senseDecisions.push(decision);
    if (decision !== 'reject') allRejected = false;
  }

  if (allRejected) {
    // Still send to server so engagement gets closed as 'rejected'
    if (engagementId) {
      fetch('{{ route("admin.words.csv-import.save-word") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSV_CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ data: data, sense_decisions: senseDecisions, existing_keys: existingKeys, engagement_id: engagementId }),
      });
    }
    status.innerHTML = '<span style="font-size:0.72rem;color:#9ca3af">All senses rejected</span>';
    card.style.opacity = '0.5';
    card.querySelectorAll('button[onclick^="csvSaveWord"]').forEach(function(b) { b.style.display = 'none'; });
    return;
  }

  btn.disabled = true;
  btn.style.opacity = '0.5';
  status.innerHTML = '<span style="font-size:0.72rem;color:#6366f1">Saving...</span>';

  fetch('{{ route("admin.words.csv-import.save-word") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSV_CSRF, 'Accept': 'application/json' },
    body: JSON.stringify({
      data: data,
      sense_decisions: senseDecisions,
      existing_keys: existingKeys,
      engagement_id: engagementId,
    }),
  })
  .then(function(r) { return r.json(); })
  .then(function(res) {
    if (res.error) {
      status.innerHTML = '<span style="font-size:0.72rem;color:#dc2626">Error: ' + escHtml(res.error) + '</span>';
      btn.disabled = false;
      btn.style.opacity = '1';
    } else {
      var parts = [];
      if (res.created) parts.push(res.created + ' added');
      if (res.enriched) parts.push(res.enriched + ' enriched');
      if (res.rejected) parts.push(res.rejected + ' skipped');
      status.innerHTML = '<span style="font-size:0.82rem;color:#15803d;font-weight:600">✓ ' + parts.join(', ') + '</span>';
      // Hide all Save buttons on this card
      card.querySelectorAll('button[onclick^="csvSaveWord"]').forEach(function(b) { b.style.display = 'none'; });
      card.classList.add('csv-reviewed');
    }
  })
  .catch(function() {
    status.innerHTML = '<span style="font-size:0.72rem;color:#dc2626">Network error</span>';
    btn.disabled = false;
    btn.style.opacity = '1';
  });
}

// ── Force review for already-reviewed word ────────────────────────
function csvForceReview(idx, traditional) {
  var btn = document.getElementById('csvForce-' + idx);
  btn.textContent = 'Reviewing...';
  btn.disabled = true;

  csvEnrichOne(idx, traditional);
}

// ── Enrich a single word via AJAX (used by force review) ──────────
function csvEnrichOne(idx, traditional) {
  var card = document.getElementById('csvWord-' + idx);
  var content = document.getElementById('csvContent-' + idx);

  // Hide existing content, show loading
  content.style.display = 'none';
  content.innerHTML = '';

  var existing = document.getElementById('csvLoading-' + idx);
  if (existing) existing.remove();

  var loadDiv = document.createElement('div');
  loadDiv.className = 'csv-loading';
  loadDiv.id = 'csvLoading-' + idx;
  loadDiv.textContent = 'Reviewing with 師父';
  card.querySelector('.csv-card-header').after(loadDiv);

  fetch('{{ route("admin.words.csv-import.enrich") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSV_CSRF, 'Accept': 'application/json' },
    body: JSON.stringify({ traditional: traditional }),
  })
  .then(function(r) { return r.json(); })
  .then(function(result) {
    if (result.error) {
      loadDiv.textContent = '❌ ' + result.error;
      loadDiv.style.color = '#dc2626';
      loadDiv.style.fontStyle = 'normal';
      card.classList.add('csv-error');
    } else {
      csvRenderWord(idx, result);
    }
  })
  .catch(function(err) {
    console.error('Force review error:', err);
    loadDiv.textContent = '❌ Network error';
    loadDiv.style.color = '#dc2626';
    loadDiv.style.fontStyle = 'normal';
  });
}

// ── Update progress bar ───────────────────────────────────────────
function csvUpdateProgress() {
  var pct = Math.round((csvProcessed / csvTotal) * 100);
  document.getElementById('csvBar').style.width = pct + '%';
  document.getElementById('csvCount').textContent = csvProcessed + ' / ' + csvTotal;
  if (csvProcessed >= csvTotal) {
    document.getElementById('csvProgress').textContent = 'All words processed';
  }
}

// ── Process words sequentially (promise chain) ───────────────────
(function() {
  var needsProcessing = [];
  CSV_WORDS.forEach(function(w, i) {
    if (!w.reviewed) {
      needsProcessing.push({ idx: i, traditional: w.traditional });
    } else {
      csvProcessed++;
    }
  });

  csvUpdateProgress();

  if (needsProcessing.length === 0) return;

  function processOne(item) {
    return fetch('{{ route("admin.words.csv-import.enrich") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSV_CSRF, 'Accept': 'application/json' },
      body: JSON.stringify({ traditional: item.traditional }),
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
      if (result.error) {
        var el = document.getElementById('csvLoading-' + item.idx);
        if (el) { el.textContent = '❌ ' + result.error; el.style.color = '#dc2626'; el.style.fontStyle = 'normal'; }
        document.getElementById('csvWord-' + item.idx).classList.add('csv-error');
      } else {
        csvRenderWord(item.idx, result);
      }
      csvProcessed++;
      csvUpdateProgress();
    })
    .catch(function(err) {
      console.error('CSV enrich error for ' + item.traditional + ':', err);
      var el = document.getElementById('csvLoading-' + item.idx);
      if (el) { el.textContent = '❌ Network error'; el.style.color = '#dc2626'; el.style.fontStyle = 'normal'; }
      csvProcessed++;
      csvUpdateProgress();
    });
  }

  // Chain promises sequentially
  var chain = Promise.resolve();
  needsProcessing.forEach(function(item) {
    chain = chain.then(function() { return processOne(item); });
  });
})();
</script>

@endsection
