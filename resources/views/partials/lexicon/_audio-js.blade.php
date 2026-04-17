{{-- Shared audio playback — single source of truth.

    Called from:
      - word-detail.blade.php (hero pronunciation + example sentences)
      - Future: SRP search results, flashcards, any other pronunciation surface

    Modularity rule (feedback_modularity.md):
      One 🔊 button shape, one playback function. Icon change, visual tweak,
      voice-cycle behavior — all happens here. Callers pass data; this file
      owns presentation and behavior.

    Model:
      Each button knows:
        - type: 'pronunciations' | 'examples'  (maps to storage subdir)
        - id:   row id in the corresponding table
        - hasAudio: { "tw-f": bool, "tw-m": bool, "cn-f": bool, "cn-m": bool }
        - fallbackText: Chinese text to synthesize via Web Speech API when
          a specific voice's file is missing
      Region preference (tw/cn/both) read from localStorage at render time.
      Gender position cycles F → M → F → M per button, tracked on the DOM
      via data-gender-idx. Independent per button.
--}}
<style>
/* ── Audio button — minimal, literary, matches chip aesthetic ── */
.audio-btn {
  display: inline-flex; align-items: baseline; gap: 0.25rem;
  background: none; border: 1px solid var(--border); border-radius: 2px;
  padding: 0.15rem 0.5rem; cursor: pointer;
  font-family: 'DM Mono', monospace; font-size: 0.7rem;
  color: var(--dim); line-height: 1.2;
  transition: color 0.15s, border-color 0.15s, background 0.15s;
  vertical-align: baseline;
}
.audio-btn:hover { color: var(--accent); border-color: var(--accent); }
.audio-btn:focus-visible { outline: 1px solid var(--accent); outline-offset: 1px; }
.audio-btn.playing { color: var(--accent); border-color: var(--accent); background: rgba(98,64,200,0.06); }
.audio-btn:disabled { opacity: 0.5; cursor: wait; }

.audio-btn-icon { font-size: 0.85em; }
.audio-btn-region {
  font-size: 0.58rem; letter-spacing: 0.06em; text-transform: uppercase;
  opacity: 0.7;
}
.audio-btn-gender {
  font-size: 0.55rem; letter-spacing: 0.06em;
  opacity: 0.55;
}
.audio-btn-group {
  display: inline-flex; gap: 0.3rem; vertical-align: baseline;
}
</style>
<script>
// ── Region preference (persistent) ──
// Valid values: 'off' | 'tw' | 'cn' | 'both'. Default 'tw'.
// 'off' = learner opted out; no 🔊 buttons render anywhere.
let audioRegionPref = localStorage.getItem('audioRegionPref') || 'tw';

function audioSetRegionPref(value) {
  if (!['off', 'tw', 'cn', 'both'].includes(value)) return;
  audioRegionPref = value;
  localStorage.setItem('audioRegionPref', value);
  if (window.syncPref) syncPref('audioRegionPref', value);

  // Re-render any existing 🔊 buttons in the document so they reflect the new mode.
  // We replace on the OUTER element — either a single .audio-btn or a .audio-btn-group wrapper.
  const groups = document.querySelectorAll('.audio-btn-group');
  const groupSet = new Set(groups);
  groups.forEach(g => {
    const data = _audioDecodeContainer(g);
    if (data) g.outerHTML = audioButton(data.type, data.id, data.hasAudio, data.fallbackText, data.label);
  });
  document.querySelectorAll('.audio-btn').forEach(btn => {
    if (btn.closest('.audio-btn-group')) return;
    if (btn.parentElement && Array.from(groupSet).some(g => g.contains(btn))) return;
    const data = _audioDecodeContainer(btn);
    if (data) btn.outerHTML = audioButton(data.type, data.id, data.hasAudio, data.fallbackText, data.label);
  });
}

/**
 * Derive region preference from the two Settings checkboxes.
 * Both off is allowed — learner can disable audio entirely. 🔊 buttons
 * simply don't render.
 */
function audioSyncFromCheckboxes() {
  const tw = document.getElementById('wdChkAudioTW');
  const cn = document.getElementById('wdChkAudioCN');
  if (!tw || !cn) return;

  let value;
  if (tw.checked && cn.checked)      value = 'both';
  else if (tw.checked)               value = 'tw';
  else if (cn.checked)               value = 'cn';
  else                               value = 'off';
  audioSetRegionPref(value);
}

/** Initialize the Settings checkboxes from the saved preference. */
function audioInitCheckboxes() {
  const tw = document.getElementById('wdChkAudioTW');
  const cn = document.getElementById('wdChkAudioCN');
  if (!tw || !cn) return;
  tw.checked = (audioRegionPref === 'tw' || audioRegionPref === 'both');
  cn.checked = (audioRegionPref === 'cn' || audioRegionPref === 'both');
}

/**
 * Render the 🔊 button(s) for a row. In 'tw' or 'cn' mode, one button.
 * In 'both' mode, two buttons side by side with 臺/陸 labels.
 *
 * @param {string} type         'pronunciations' | 'examples'
 * @param {number} id           row id
 * @param {object} hasAudio     { 'tw-f': bool, 'tw-m': bool, 'cn-f': bool, 'cn-m': bool }
 * @param {string} fallbackText Chinese text for Web Speech API fallback
 * @param {string} label        Optional extra label (rare; omit usually)
 * @returns {string} HTML
 */
function audioButton(type, id, hasAudio, fallbackText, label) {
  hasAudio = hasAudio || {};
  const pref = audioRegionPref;
  if (pref === 'off') return '';
  const regions = pref === 'both' ? ['tw', 'cn'] : [pref];
  const buttons = regions.map(region => {
    const regionLabel = (pref === 'both')
      ? `<span class="audio-btn-region">${region === 'tw' ? '臺' : '陸'}</span>`
      : '';
    // Stash everything on the element itself so the click handler is pure — no
    // mapping between click event and row state.
    const hasAudioAttr = _escapeAttr(JSON.stringify(hasAudio));
    const textAttr     = _escapeAttr(fallbackText || '');
    const labelAttr    = label ? ` data-label="${_escapeAttr(label)}"` : '';
    return `<button type="button"
      class="audio-btn"
      data-audio-target="${type}"
      data-audio-id="${id}"
      data-region="${region}"
      data-gender-idx="0"
      data-has-audio='${hasAudioAttr}'
      data-fallback-text="${textAttr}"${labelAttr}
      onclick="audioPlay(event, this)"
      aria-label="Play pronunciation"
      title="Tap to hear — each tap cycles female / male">
      <span class="audio-btn-icon">🔊</span>${regionLabel}<span class="audio-btn-gender" data-gender-letter>F</span>
    </button>`;
  }).join('');
  return pref === 'both'
    ? `<span class="audio-btn-group">${buttons}</span>`
    : buttons;
}

/** Decode container data so we can re-render on preference change. */
function _audioDecodeContainer(el) {
  // Support either single button or the .audio-btn-group wrapper
  const btn = el.classList && el.classList.contains('audio-btn') ? el : el.querySelector('.audio-btn');
  if (!btn) return null;
  try {
    return {
      type: btn.getAttribute('data-audio-target'),
      id: parseInt(btn.getAttribute('data-audio-id'), 10),
      hasAudio: JSON.parse(btn.getAttribute('data-has-audio') || '{}'),
      fallbackText: btn.getAttribute('data-fallback-text') || '',
      label: btn.getAttribute('data-label') || '',
    };
  } catch (e) { return null; }
}

function _escapeAttr(s) {
  return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── Playback ──
let _audioCurrentEl = null;
let _audioCurrent   = null;
let _audioUtterance = null;

async function audioPlay(evt, btn) {
  if (evt) { evt.preventDefault(); evt.stopPropagation(); }
  if (!btn || btn.disabled) return;

  // If there's a previous playback, abort it (including if it's THIS button).
  _audioAbort();

  const type         = btn.getAttribute('data-audio-target');
  const id           = btn.getAttribute('data-audio-id');
  const region       = btn.getAttribute('data-region'); // 'tw' | 'cn'
  const genderIdx    = parseInt(btn.getAttribute('data-gender-idx') || '0', 10);
  const genders      = ['f', 'm'];
  const gender       = genders[genderIdx % 2];
  const voiceKey     = `${region}-${gender}`;
  const hasAudio     = _audioDecodeContainer(btn)?.hasAudio || {};
  const fallbackText = btn.getAttribute('data-fallback-text') || '';

  // Advance the cycle for next tap
  const nextIdx = (genderIdx + 1) % genders.length;
  btn.setAttribute('data-gender-idx', String(nextIdx));
  const letter = btn.querySelector('[data-gender-letter]');
  if (letter) letter.textContent = genders[nextIdx].toUpperCase();

  btn.classList.add('playing');
  _audioCurrentEl = btn;

  try {
    if (hasAudio[voiceKey]) {
      const path = `/storage/audio/${type}/${voiceKey}/${id}.mp3`;
      await _audioPlayFile(path);
    } else {
      // Fall through to Web Speech API
      _audioSpeak(fallbackText, region === 'cn' ? 'zh-CN' : 'zh-TW');
    }
  } catch (e) {
    console.warn('audio playback failed, falling back to TTS:', e);
    if (fallbackText) _audioSpeak(fallbackText, region === 'cn' ? 'zh-CN' : 'zh-TW');
  } finally {
    // Cleanup of the playing state happens in _audioPlayFile / _audioSpeak handlers
  }
}

function _audioPlayFile(path) {
  return new Promise((resolve, reject) => {
    const audio = new Audio(path);
    _audioCurrent = audio;
    audio.onended = () => {
      if (_audioCurrentEl) _audioCurrentEl.classList.remove('playing');
      _audioCurrentEl = null; _audioCurrent = null;
      resolve();
    };
    audio.onerror = (e) => {
      if (_audioCurrentEl) _audioCurrentEl.classList.remove('playing');
      _audioCurrentEl = null; _audioCurrent = null;
      reject(e);
    };
    audio.play().catch(reject);
  });
}

function _audioSpeak(text, lang) {
  if (!text || !window.speechSynthesis) {
    if (_audioCurrentEl) _audioCurrentEl.classList.remove('playing');
    _audioCurrentEl = null;
    return;
  }
  const u = new SpeechSynthesisUtterance(text);
  u.lang = lang || 'zh-TW';
  u.onend = () => {
    if (_audioCurrentEl) _audioCurrentEl.classList.remove('playing');
    _audioCurrentEl = null; _audioUtterance = null;
  };
  u.onerror = u.onend;
  _audioUtterance = u;
  window.speechSynthesis.cancel(); // abort any prior queue
  window.speechSynthesis.speak(u);
}

function _audioAbort() {
  if (_audioCurrent) {
    try { _audioCurrent.pause(); _audioCurrent.currentTime = 0; } catch (e) {}
    _audioCurrent = null;
  }
  if (_audioUtterance && window.speechSynthesis) {
    try { window.speechSynthesis.cancel(); } catch (e) {}
    _audioUtterance = null;
  }
  if (_audioCurrentEl) {
    _audioCurrentEl.classList.remove('playing');
    _audioCurrentEl = null;
  }
}
</script>
