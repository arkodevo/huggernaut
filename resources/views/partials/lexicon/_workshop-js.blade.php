{{-- Shared Writing Conservatory JS — used by lexicon card + IWP --}}
@php
  $wsGrammarPatterns = \App\Models\GrammarPattern::shifuReferenceList();
@endphp
<script>
// ── GRAMMAR PATTERN REFERENCE (for 師父 detection) ───────────────────────────
const WS_GRAMMAR_PATTERNS = @json($wsGrammarPatterns);

// ── FLUENCY LEVELS & MASTERY PHASES ──────────────────────────────────────────
const WS_LEVELS = [
  { slug: 'beginner',   en: 'Beginner',   zh: '初學', icon: '🌱' },
  { slug: 'learner',    en: 'Learner',    zh: '學習', icon: '🌿' },
  { slug: 'developing', en: 'Developing', zh: '發展', icon: '🍃' },
  { slug: 'advanced',   en: 'Advanced',   zh: '進階', icon: '🌳' },
  { slug: 'fluent',     en: 'Fluent',     zh: '流利', icon: '🀄' },
];
const WS_MASTERY = [
  { slug: 'seed',   en: 'Seed',   zh: '播', icon: '🌱' },
  { slug: 'sprout', en: 'Sprout', zh: '萌', icon: '🌿' },
  { slug: 'bud',    en: 'Bud',    zh: '苞', icon: '🌸' },
  { slug: 'flower', en: 'Flower', zh: '綻', icon: '🌼' },
  { slug: 'fruit',  en: 'Fruit',  zh: '熟', icon: '🍎' },
];
function wsLevelLabel(slug) { const l = WS_LEVELS.find(x => x.slug === slug); return l ? l : null; }
function wsMasteryLabel(slug) { const m = WS_MASTERY.find(x => x.slug === slug); return m ? m : null; }

// Get current fluency level (from profile or workshop override)
function wsGetFluencyLevel(wordKey) {
  // Check per-session override first
  const override = sessionStorage.getItem('ws_fluency_override_' + wordKey);
  if (override) return override;
  const globalOverride = sessionStorage.getItem('ws_fluency_override');
  if (globalOverride) return globalOverride;
  // Fall back to profile setting
  return (window.__AUTH && window.__AUTH.fluencyLevel) || 'developing';
}

// Default share-to-community toggle value: profile setting if signed in, else true (encourage sharing)
function wsDefaultPublic() {
  if (window.__AUTH && typeof window.__AUTH.defaultWritingsPublic === 'boolean') {
    return window.__AUTH.defaultWritingsPublic;
  }
  return true;
}

function wsSetFluencyLevel(wordKey, level) {
  sessionStorage.setItem('ws_fluency_override_' + wordKey, level);
  // Also persist to profile via API
  if (window.__AUTH) {
    window.__AUTH.fluencyLevel = level;
    fetch('/api/user/fluency-level', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': wsCsrf(), 'Accept': 'application/json' },
      body: JSON.stringify({ fluency_level: level }),
    });
  }
}

// ── TRY AGAIN ────────────────────────────────────────────────────────────────
function wsTryAgain(wordKey, mode) {
  const resultEl = document.getElementById(`ws-${mode}-result-${wordKey}`);
  if (resultEl) resultEl.innerHTML = '';
  if (mode === 'critique') {
    const input = document.getElementById(`ws-critique-input-${wordKey}`);
    if (input) { input.value = ''; input.focus(); }
  } else {
    const input = document.getElementById(`ws-theme-input-${wordKey}`);
    if (input) { input.value = ''; input.focus(); }
  }
}

// ── WORKSHOP SAVED DECK (in-memory) ──────────────────────────────────────────
const wsSavedDeck = {}; // { wordKey: [{id, cn, en, feedback, source, date}] }

function wsGetSaved(key) { return wsSavedDeck[key] || []; }

function wsSaveToWord(key, item) {
  if (!wsSavedDeck[key]) wsSavedDeck[key] = [];
  if (wsSavedDeck[key].some(s => s.cn === item.cn)) return false;
  wsSavedDeck[key].push(item);
  return true;
}

function wsRemoveFromWord(key, idx) {
  if (!wsSavedDeck[key]) return;
  const item = wsSavedDeck[key][idx];
  wsSavedDeck[key].splice(idx, 1);
  if (item && item.id) {
    fetch('/api/workshop/saved-example/' + item.id, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': wsCsrf(), 'Accept': 'application/json' },
    });
  }
}

function wsCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

// ── API HELPER ───────────────────────────────────────────────────────────────
// Returns { text, engagement_id } from critique/generate endpoints.
async function wsCallAPI(endpoint, body) {
  const response = await fetch(endpoint, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': wsCsrf(),
      'Accept': 'application/json',
    },
    body: JSON.stringify(body),
  });
  if (response.status === 401) throw new Error('auth_required');
  if (!response.ok) throw new Error('api_error');
  const data = await response.json();
  if (data.error) throw new Error('api_error');
  return data;
}

// Per-word engagement tracking: { wordKey: uuid }
const wsEngagements = {};

// ── GUEST AUTH PROMPT ────────────────────────────────────────────────────────
function wsShowAuthPrompt(wordKey, context, resultElId) {
  const pending = { wordKey, context };
  if (context === 'critique') {
    const input = document.getElementById(`ws-critique-input-${wordKey}`);
    const posSelect = document.getElementById(`ws-critique-pos-${wordKey}`);
    pending.sentence = input ? input.value : '';
    pending.pos = posSelect ? posSelect.value : '';
  } else if (context === 'theme') {
    const input = document.getElementById(`ws-theme-input-${wordKey}`);
    pending.theme = input ? input.value : '';
  }
  localStorage.setItem('ww_pending', JSON.stringify(pending));

  const resultEl = document.getElementById(resultElId);
  if (resultEl) {
    resultEl.innerHTML = `
      <div class="ws-ai-response" style="text-align:center">
        <div class="ws-ai-response-text" style="color:var(--accent);font-weight:500">
          ${langMode === 'zh' ? '登入或註冊帳戶以使用寫作院' : 'Log in or create an account to use the Writing Conservatory'}
        </div>
        <div style="display:flex;gap:0.6rem;justify-content:center;margin-top:0.5rem">
          <a href="/login" class="ws-ai-submit-btn" style="text-decoration:none;display:inline-block;width:auto">Log In</a>
          <a href="/register" class="ws-ai-submit-btn" style="text-decoration:none;display:inline-block;width:auto;background:transparent">Register</a>
        </div>
      </div>`;
  }
}

// ── RESTORE PENDING WORKSHOP DATA AFTER LOGIN ────────────────────────────────
function wsRestorePending() {
  const raw = localStorage.getItem('ww_pending');
  if (!raw || !window.__AUTH) return;
  try {
    const pending = JSON.parse(raw);
    localStorage.removeItem('ww_pending');

    if (pending.context === 'save_result') {
      fetch('/api/workshop/save-example', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': wsCsrf(),
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          word_sense_id: pending.senseId ? parseInt(pending.senseId) : null,
          word_object_id: pending.wordObjectId ? parseInt(pending.wordObjectId) : null,
          chinese_text: pending.cn,
          english_text: pending.en,
          original_chinese_text: pending.originalCn || null,
          ai_verified: pending.aiVerified,
          ai_feedback: pending.feedback || null,
          source_type: pending.isGenerated ? 'generated' : 'learner',
          assessed_level: pending.assessedLevel || null,
          assessed_mastery: pending.assessedMastery || null,
          mastery_guidance: pending.masteryGuidance || null,
          grammar_patterns: pending.grammarPatterns || [],
          is_public: typeof pending.isPublic === 'boolean' ? pending.isPublic : wsDefaultPublic(),
        }),
      }).then(r => r.json()).then(saved => {
        const wordKey = pending.wordKey;
        const source = pending.isGenerated ? '師父 generated' : pending.aiVerified ? '師父 verified' : 'My writing';
        const today = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        wsSaveToWord(wordKey, { id: saved.id, cn: pending.cn, en: pending.en, feedback: pending.feedback, source, date: today, originalCn: pending.originalCn || '', grammarPatterns: pending.grammarPatterns || [] });
        wsRefreshDeck(wordKey);
        setTimeout(() => {
          wsExpandPanel(wordKey);
          const deckEl = document.getElementById(`ws-deck-${wordKey}`);
          if (deckEl) {
            const lastCard = deckEl.querySelector('.ex-sent:last-child');
            (lastCard || deckEl).scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
        }, 300);
      }).catch(() => {});
      return;
    }

    setTimeout(() => {
      const wordKey = pending.wordKey;
      wsExpandPanel(wordKey);

      if (pending.context === 'critique') {
        const input = document.getElementById(`ws-critique-input-${wordKey}`);
        const posSelect = document.getElementById(`ws-critique-pos-${wordKey}`);
        if (input && pending.sentence) input.value = pending.sentence;
        if (posSelect && pending.pos) posSelect.value = pending.pos;
        const tab = document.querySelector(`[data-ws-key="${wordKey}"] .ws-ai-tab`);
        if (tab) wsSwitchAITab(wordKey, 'critique', tab);
      } else if (pending.context === 'theme') {
        const input = document.getElementById(`ws-theme-input-${wordKey}`);
        if (input && pending.theme) input.value = pending.theme;
        const tabs = document.querySelectorAll(`[data-ws-key="${wordKey}"] .ws-ai-tab`);
        if (tabs[1]) wsSwitchAITab(wordKey, 'theme', tabs[1]);
      }

      const panel = document.querySelector(`[data-ws-key="${wordKey}"]`);
      if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 300);
  } catch(e) { localStorage.removeItem('ww_pending'); }
}

// ── PERSONA OVERLAY ──────────────────────────────────────────────────────────
const WS_PERSONAS = @json(config('shifu-personas'));
function wsGetPersonaOverlay() {
  const slug = window.__AUTH?.shifuPersona || 'dragon';
  const p = WS_PERSONAS[slug];
  return p ? '\n\nFEEDBACK STYLE PERSONA:\n' + p.prompt + '\n' : '';
}

// ── SYSTEM PROMPTS ───────────────────────────────────────────────────────────
function wsGetCritiquePrompt(word, intendedPOS, fluencyLevel) {
  const posLine = intendedPOS
    ? `- Intended POS: ${posDisplay(intendedPOS)} (${POS_ABBR[intendedPOS] || intendedPOS}) — the learner intends to use "${word.traditional}" as this part of speech. Evaluate whether their sentence correctly uses it in this role.`
    : '- Intended POS: not specified';
  const levelInfo = WS_LEVELS.find(l => l.slug === fluencyLevel);
  const levelLabel = levelInfo ? `${levelInfo.en} (${levelInfo.zh})` : fluencyLevel;
  // Resolve bilingual notes for context
  const notes = word.notes || (word.senses && word.senses[0]?.notes) || { en: {}, zh: {} };
  const usageNote = notes.en?.usageNote || notes.zh?.usageNote || '';
  const learnerTraps = notes.en?.learnerTraps || '';
  const formula = word.formula || notes.en?.formula || notes.zh?.formula || '';
  const definition = (typeof word.definition === 'object')
    ? (word.definition.en || word.definition.zh || '')
    : (word.definition || '');

  return `You are 師父 (Shifu), the expert Chinese language tutor for the Living Lexicon 流動, a precision Chinese vocabulary app focused on fluency, nuance, and expressive accuracy.

The user has written a sentence using the word "${word.traditional}" (${word.simplified || ''}, ${word.pinyin || ''}) — meaning: ${definition}.

Learner's self-assessed fluency level: ${levelLabel}. Calibrate your feedback to this level — a Beginner needs more foundational guidance, while an Advanced learner needs nuance-level critique.

Word metadata:
- Register: ${word.register || 'n/a'}
- Connotation: ${word.connotation || 'n/a'}
- Channel: ${word.channel || 'n/a'}
- HSK Level: ${word.level || 'n/a'}
- Syntactic formula: ${formula || 'n/a'}
${posLine}
${usageNote ? `- Usage note: ${usageNote}` : ''}
${learnerTraps ? `- Common learner traps: ${learnerTraps}` : ''}

Your task: Evaluate the user's sentence with warmth and precision.${intendedPOS ? ` Pay special attention to whether "${word.traditional}" is used correctly as a ${posDisplay(intendedPOS)}.` : ''}

TARGET WORD IS SACRED: The whole purpose of this exercise is for the learner to practice "${word.traditional}". Treat it as a fixed requirement in every part of your response:
- The "corrected_cn" MUST contain the exact word "${word.traditional}" — never substitute, truncate, or swap it for a compound, derivative, synonym, or single-character root (e.g. do NOT suggest 流 instead of 流動, 開 instead of 開始, 走 instead of 走路).
- In "feedback" and "register_note", do NOT suggest replacing "${word.traditional}" with any other word when the word IS workable. If another word would also work naturally, that is irrelevant — the learner chose this word on purpose.
- You MAY comment on how well "${word.traditional}" fits the context, and you MAY suggest restructuring the rest of the sentence to showcase it better.

VERDICT OF "unworkable" — THE HARD REJECT (extremely rare): This verdict is reserved for one specific case only: when the SCENARIO the learner has chosen is fundamentally incompatible with "${word.traditional}", such that NO grammatically correct, natural sentence using "${word.traditional}" could describe that scenario. Examples of genuinely unworkable: using a literary/classical word in a casual conversational scenario; using a negative-connotation word to describe something joyful; using a word whose semantic scope simply doesn't cover the activity being described (e.g. using 流動 for something that isn't moving fluidly at all).

CRITICAL: If the scenario WOULD work for "${word.traditional}" but the learner made execution errors (wrong word choice elsewhere, grammar mistakes, awkward structure, used a different word by accident instead of "${word.traditional}"), that is NEVER "unworkable" — it is "needs_work" or "minor_issues". The test is: "Could I, 師父, write a natural sentence using '${word.traditional}' that describes the same scenario the learner is trying to convey?" If yes → it is workable. Fix their sentence and return a "correct"/"minor_issues"/"needs_work" verdict with "${word.traditional}" properly used in corrected_cn. Only if the answer is genuinely no, because the scenario itself cannot host the word, do you return "unworkable".

Before choosing "unworkable", ask yourself: "Is it the scenario that is wrong for the word, or is it the learner's execution?" Execution problems are never unworkable.

VERDICT OF "missing_target" — THE LEARNER DRIFTED: If the learner's sentence does NOT contain "${word.traditional}" at all (they forgot it, substituted another word, or wrote an entirely different sentence), set "verdict" to "missing_target". This is a gentle but firm wake-up — the learner has lost focus and needs to come back to the exercise. The response is a TEACHING MOMENT, not a correction you hand over:
- "corrected_cn" MUST be the learner's ORIGINAL sentence unchanged. Do NOT supply a rewritten sentence. The whole point is that the learner must rewrite it themselves.
- "feedback" should warmly but firmly name the miss ("Where is ${word.traditional}? You've drifted from the practice — come back"), then POINT TO the structural slot where "${word.traditional}" belongs in the scenario they imagined, WITHOUT writing the finished sentence. E.g. "Your scenario calls for ${word.traditional} in the verb slot between 水從廚房 and 到客廳 — can you place it there?" Name any grammar they'd need (directional complement, aspect marker, etc.) but do not assemble the sentence for them.
- Do NOT populate "mastery_guidance" — they haven't demonstrated mastery yet.
- Still populate "assessed_level" and "assessed_mastery" based on the craft visible in what they DID write (grammar, structure, vocabulary outside the target).
- Still populate "grammar_patterns_identified" and "grammar_patterns_suggested" — give them credit for patterns they used correctly.
- Leave "redirect_suggestion" fields as empty strings.

TEACH, DON'T FEED — THE CORRECTION GRADIENT: How much of the fix you hand over depends on how close the learner got:
- "correct" — the sentence works. "corrected_cn" = the learner's sentence (or a minimal polish). Save is offered.
- "minor_issues" — 1 or 2 small fixes (a classifier, a particle, a word-order nudge). Show the polished sentence in "corrected_cn" so the learner sees the nuance. Save is offered. This is the "you were almost there, here's the last mile" case.
- "needs_work" — 3+ errors, OR any rule-level error (wrong valency, wrong register, wrong POS usage, collocation violation, misused grammar pattern). DO NOT hand over a polished sentence. "corrected_cn" MUST be the learner's ORIGINAL sentence unchanged. In "feedback", name every error explicitly with the rule behind it ("流動 is intransitive — it cannot take a direct object like 水"; "打開 doesn't collocate with 水 in this sense"; "the directional complement needs 到, not 在"). The learner must rewrite using your diagnosis. Save is NOT offered — they try again.
- "missing_target" — handled above. No rewrite, point to the slot, they rewrite.
- "unworkable" — handled below. Scenario itself is wrong; redirect to a different scenario.

The principle: seeing the polished sentence is a reward for getting close. If the learner is far from it, showing them the answer robs them of the learning. Teach them to fish.

VERDICT OF "unworkable" — THE HARD REJECT (extremely rare): handled above in the unworkable section. When verdict is "unworkable":
- "corrected_cn" should be the learner's ORIGINAL sentence (do not try to fix it — we are telling them to start over)
- "feedback" must clearly explain WHY "${word.traditional}" cannot work in this scenario (the specific register/connotation/semantic mismatch)
- "redirect_suggestion" MUST be populated with: a different scenario (in English) where "${word.traditional}" would genuinely shine, plus a one-sentence example sentence in Traditional Chinese demonstrating it
- Do NOT populate "mastery_guidance" — the learner isn't demonstrating mastery, they're being redirected
- Still populate "assessed_level" and "assessed_mastery" based on the craft of the sentence they wrote (grammar, structure) — their writing skill is visible even when the word choice misfires

FEEDBACK TONE — WARM BUT STRICT: You are the kind of teacher students remember for life: genuinely warm, but uncompromising on craft. Think of an old master who smiles as they correct you — the affection is real, and so is the standard. Never soften the truth to spare feelings. Never flatter. A developing-level writer should feel cared for, not told they are "remarkable." Praise only what genuinely deserves praise, and when you do, be specific about WHY. If a sentence is clumsy, say so — kindly, but clearly. If a grammar rule is broken, name the rule. If a register is wrong, explain the mismatch. Errors must be called out explicitly, not hinted at. The learner should walk away knowing exactly what they did well, exactly what was wrong, and exactly why it was wrong. Warmth without honesty is flattery; strictness without warmth is cruelty. You are both. Honest warmth + precise rigor = real teaching.

GRAMMAR PATTERN DETECTION: The Living Lexicon tracks a growing library of Chinese grammar patterns. Below is the current reference list (slug · Chinese label · markers). When you review the learner's sentence:
1. If the sentence uses any pattern from the list, report it in "grammar_patterns_identified" with the slug and a one-sentence usage note ("correct" / "almost" / "misused").
2. If the sentence uses a notable grammar construction that is NOT in the list (e.g. 還是, 一邊...一邊, 不但...而且, 越...越, 的時候, 雖然...但是, V一下, V過, 了, 的, 要...了, resultative complements, directional complements, etc.), suggest it in "grammar_patterns_suggested" so an editor can add it. Only suggest patterns that are genuinely present in the sentence — do not speculate.

Known patterns (do not duplicate these as suggestions):
${WS_GRAMMAR_PATTERNS.map(p => `- ${p.slug} · ${p.zh_label} (${p.en_label}) · markers: ${(p.markers||[]).join('、') || '—'}`).join('\n')}

MASTERY ASSESSMENT: Also assess the writing on two axes:
1. Level: beginner | learner | developing | advanced | fluent — the actual demonstrated level of the writing (may differ from the learner's self-assessed level)
2. Mastery: seed | sprout | bud | flower | fruit — how fully the writing demonstrates command within that level
   - seed: bare minimum structure, minimal modifiers
   - sprout: adding modifiers, basic sentence variety
   - bud: linking clauses, showing awareness of register/style
   - flower: expressing nuance, creative word choice, natural flow
   - fruit: level mastery — ready to progress to the next level

Respond ONLY in this exact JSON format (no markdown, no extra text):
{
  "verdict": "correct" | "minor_issues" | "needs_work" | "missing_target" | "unworkable",
  "corrected_cn": "For 'correct' or 'minor_issues': the polished Traditional Chinese sentence (must contain ${word.traditional}). For 'needs_work', 'missing_target', or 'unworkable': the learner's ORIGINAL sentence unchanged — do NOT hand over a fix.",
  "corrected_en": "English translation of the corrected sentence",
  "highlight_word": "${word.traditional}",
  "feedback": "Structured feedback in English. Format: (1) One sentence on what was done well. (2) If corrections were made, list each change with a brief WHY (grammar rule, valency, collocation, register). Do not lump corrections together — name each one.${intendedPOS ? ` (3) Comment on whether the word is correctly used as a ${posDisplay(intendedPOS)}.` : ''} Keep total length to 3-5 sentences. Be honest and specific.",
  "register_note": "One sentence: does this sentence match the word's register (${word.register || 'n/a'})? If not, explain gently.",
  "assessed_level": "beginner | learner | developing | advanced | fluent",
  "assessed_mastery": "seed | sprout | bud | flower | fruit",
  "mastery_guidance": "1-2 sentences: what specifically the learner should practice to reach the next mastery phase. Give one concrete example — a pattern to try, a structure to add, a technique to experiment with. Leave empty string when verdict is 'unworkable'.",
  "redirect_suggestion": {
    "scenario": "When verdict is 'unworkable', describe in English a different scenario where ${word.traditional} would genuinely shine (2-3 sentences). Empty string otherwise.",
    "example_cn": "When verdict is 'unworkable', one natural Traditional Chinese sentence showing ${word.traditional} in that scenario. Empty string otherwise.",
    "example_en": "English translation of example_cn. Empty string otherwise."
  },
  "grammar_patterns_identified": [
    { "slug": "pattern-slug-from-list", "status": "correct | almost | misused", "note": "one-sentence observation" }
  ],
  "grammar_patterns_suggested": [
    { "pattern_text": "rough Chinese template or name (e.g. 越...越, 一邊...一邊)", "chinese_example": "the exact phrase from the learner sentence that shows this pattern", "note": "one-sentence description of what this pattern expresses" }
  ]
}

Both grammar_patterns_identified and grammar_patterns_suggested MUST be arrays (use empty array [] if nothing applies). Never omit these fields.` + wsGetPersonaOverlay();
}

function wsGetThemePrompt(word, fluencyLevel) {
  const levelInfo = WS_LEVELS.find(l => l.slug === fluencyLevel);
  const levelLabel = levelInfo ? `${levelInfo.en} (${levelInfo.zh})` : fluencyLevel;
  return `You are 師父 (Shifu), the expert Chinese language tutor for the Living Lexicon 流動, a precision Chinese vocabulary app.

Generate a vivid, natural sentence using the word "${word.traditional}" (${word.simplified || ''}) based on the user's requested theme/subject.

Target learner level: ${levelLabel}. Generate a sentence appropriate for this level — a Beginner sentence should use simpler structures, while an Advanced sentence can use literary constructions.

Word metadata:
- Register: ${word.register || 'n/a'} — STRICTLY match this register in your sentence
- Connotation: ${word.connotation || 'n/a'}
- Channel: ${word.channel || 'n/a'}
- HSK Level: ${word.level || 'n/a'}
- Syntactic formula: ${word.formula || 'n/a'}

Rules:
- Use Traditional Chinese characters throughout
- The sentence MUST use the EXACT word "${word.traditional}" — do NOT substitute compounds, derivatives, or synonyms (e.g. if the word is 流, use 流, not 流淌 or 流動)
- The sentence must feel natural and engaging, not textbook-dry
- Match the register precisely (${word.register || 'n/a'})
- Make the headword prominent and natural in context
- The sentence should connect emotionally to the user's theme

MASTERY ASSESSMENT: Also assess the generated sentence:
1. Level: beginner | learner | developing | advanced | fluent
2. Mastery: seed | sprout | bud | flower | fruit (see critique prompt for definitions)

Respond ONLY in this exact JSON format (no markdown, no extra text):
{
  "cn": "The Traditional Chinese sentence",
  "en": "Natural English translation",
  "note": "One sentence explaining why this sentence fits the theme and demonstrates the word's nuance well",
  "assessed_level": "beginner | learner | developing | advanced | fluent",
  "assessed_mastery": "seed | sprout | bud | flower | fruit"
}` + wsGetPersonaOverlay();
}

// ── RENDER SAVED DECK ────────────────────────────────────────────────────────
function wsRenderSavedDeck(wordKey, wordData) {
  const items = wsGetSaved(wordKey);
  if (!items.length) return '';
  const wsDefs = wordData ? (Array.isArray(wordData.definitions) ? wordData.definitions : getAllDefs(wordData.definitions)) : [];
  const primaryPOS = wsDefs[0]?.pos || '';
  const posChip = primaryPOS ? `<span class="ex-sent-pos">${POS_ABBR[primaryPOS] || primaryPOS}</span>` : '';
  const vertical = textDir === 'vertical';

  return `
    <div class="ws-saved-deck-section" id="ws-deck-${wordKey}">
      <span class="ws-saved-deck-label">${langMode === 'zh' ? '我的寫作' : langMode === 'both' ? 'My Writings 我的寫作' : 'My Writings'} (${items.length})</span>
      <div class="ex-sentences">
      ${items.map((item, i) => {
        const sourceType = item.source === '師父 generated' ? 'shifu' : (item.source === '師父 verified' ? 'mine' : 'mine');
        return `
        <div class="ex-sent${vertical ? ' vertical' : ''} saved-writing" data-source-type="${sourceType}">
          <div class="ws-saved-writing-chips">
            ${posChip}
            ${item.source === '師父 generated' ? '<span class="ws-shifu-chip">🙏 師父 generated</span>' : item.source === '師父 verified' ? '<span class="ws-shifu-chip">👏 師父 verified</span>' : ''}
          </div>
          ${item.assessedLevel || item.assessedMastery ? `<div class="ws-saved-writing-chips ws-assess-row">
            ${item.assessedLevel ? (() => { const l = wsLevelLabel(item.assessedLevel); return l ? '<span class="ws-level-chip">' + l.icon + ' ' + (langMode === 'zh' ? l.zh : l.en) + '</span>' : ''; })() : ''}
            ${item.assessedMastery ? (() => { const m = wsMasteryLabel(item.assessedMastery); return m ? '<span class="ws-mastery-chip">' + m.icon + ' ' + (langMode === 'zh' ? m.zh : m.en) + '</span>' : ''; })() : ''}
          </div>` : ''}
          ${(Array.isArray(item.grammarPatterns) && item.grammarPatterns.length) ? `<div class="ws-grammar-patterns ws-saved-grammar"><span class="ws-grammar-label">${langMode === 'zh' ? '句型' : 'Grammar'}:</span> ${item.grammarPatterns.map(gp => {
            const ref = (typeof WS_GRAMMAR_PATTERNS !== 'undefined') ? WS_GRAMMAR_PATTERNS.find(p => p.slug === gp.slug) : null;
            if (!ref) return '';
            const icon = gp.status === 'correct' ? '✓' : gp.status === 'almost' ? '△' : '✗';
            const label = langMode === 'zh' ? ref.zh_label : ref.en_label;
            const noteAttr = gp.note ? ' title="' + String(gp.note).replace(/"/g,'&quot;') + '"' : '';
            return '<span class="ws-grammar-chip ws-grammar-' + (gp.status || 'correct') + '"' + noteAttr + '>' + icon + ' ' + label + '</span>';
          }).filter(Boolean).join(' ')}</div>` : ''}
          <div class="ex-sent-body">
            <div class="ex-sent-cn">${segmentedHTML(item.cn, {traditional: wordKey, simplified: wordData?.simplified || ''})}</div>
            <div class="ex-sent-en">${item.en}</div>
            ${item.feedback || item.originalCn ? `<details class="ws-saved-feedback"><summary>師父 feedback</summary><div class="ws-saved-feedback-text">${item.originalCn ? '<div class="ws-original-submission"><div class="ws-original-label">' + (langMode === 'zh' ? '你的原稿' : 'Your original') + ':</div><div class="ws-original-text">' + item.originalCn + '</div></div>' : ''}${item.feedback || ''}${item.masteryGuidance ? '<div class="ws-mastery-guidance">' + item.masteryGuidance + '</div>' : ''}</div></details>` : ''}
            <div class="ws-saved-meta">
              <span class="ws-saved-date">${item.date || ''}</span>
              <button class="remove-btn" onclick="wsConfirmDelete(this, '${wordKey}', ${i})">✕ ${langMode === 'zh' ? '刪除' : 'delete'}</button>
            </div>
          </div>
        </div>
      `}).join('')}
      </div>
    </div>`;
}

// ── DELETE CONFIRMATION ──────────────────────────────────────────────────────
function wsConfirmDelete(btn, wordKey, idx) {
  const card = btn.closest('.ex-sent');
  if (!card || card.querySelector('.ws-delete-confirm')) return;
  const bar = document.createElement('div');
  bar.className = 'ws-delete-confirm';
  bar.innerHTML = `
    <span class="ws-delete-confirm-msg">${langMode === 'zh' ? '確定要刪除？' : 'Delete this writing?'}</span>
    <button class="ws-delete-confirm-yes" onclick="wsRemoveSaved('${wordKey}', ${idx})">
      ${langMode === 'zh' ? '刪除' : 'Delete'}
    </button>
    <button class="ws-delete-confirm-no" onclick="this.closest('.ws-delete-confirm').remove()">
      ${langMode === 'zh' ? '取消' : 'Cancel'}
    </button>`;
  card.appendChild(bar);
}

function wsRemoveSaved(wordKey, idx) {
  wsRemoveFromWord(wordKey, idx);
  const deckEl = document.getElementById(`ws-deck-${wordKey}`);
  const items = wsGetSaved(wordKey);
  if (deckEl) {
    if (!items.length) {
      deckEl.remove();
    } else {
      deckEl.outerHTML = wsRenderSavedDeck(wordKey, wsGetWordData(wordKey));
    }
  }
}

function wsRefreshDeck(wordKey) {
  const wrap = document.getElementById(`ws-deck-wrap-${wordKey}`);
  if (wrap) wrap.innerHTML = wsRenderSavedDeck(wordKey, wsGetWordData(wordKey));
}

// ── TOGGLE / EXPAND WORKSHOP ─────────────────────────────────────────────────
const wsOpen = {};

function wsTogglePanel(key) {
  if (wsOpen[key] === undefined) wsOpen[key] = (typeof workshopDefault !== 'undefined' && workshopDefault === 'expanded');
  wsOpen[key] = !wsOpen[key];
  const body = document.getElementById(`ws-body-${key}`);
  const btn = body?.closest('.ws-panel')?.querySelector('.ws-toggle');
  if (body) {
    body.style.display = wsOpen[key] ? 'flex' : 'none';
    if (btn) btn.textContent = wsOpen[key] ? '▼' : '▲';
  }
}

function wsExpandPanel(key) {
  wsOpen[key] = true;
  const body = document.getElementById(`ws-body-${key}`);
  const btn = body?.closest('.ws-panel')?.querySelector('.ws-toggle');
  if (body) { body.style.display = 'flex'; }
  if (btn) btn.textContent = '▼';
}

// ── AI TAB SWITCHING ─────────────────────────────────────────────────────────
function wsSwitchAITab(wordKey, tab, btn) {
  const critiqueEl = document.getElementById(`ws-tab-critique-${wordKey}`);
  const themeEl = document.getElementById(`ws-tab-theme-${wordKey}`);
  const tabs = btn.closest('.ws-ai-tabs').querySelectorAll('.ws-ai-tab');
  tabs.forEach(t => t.classList.remove('active'));
  btn.classList.add('active');

  let targetEl;
  if (tab === 'critique') {
    if (critiqueEl) critiqueEl.style.display = 'flex';
    if (themeEl) themeEl.style.display = 'none';
    targetEl = critiqueEl;
  } else {
    if (critiqueEl) critiqueEl.style.display = 'none';
    if (themeEl) themeEl.style.display = 'flex';
    targetEl = themeEl;
  }

  // Scroll the input area into view so the learner sees it
  if (targetEl) {
    setTimeout(() => {
      targetEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      // Focus the textarea so the learner can start typing immediately
      const textarea = targetEl.querySelector('textarea');
      if (textarea) textarea.focus({ preventScroll: true });
    }, 50);
  }
}

// ── EXAMPLE FILTER CHIPS ─────────────────────────────────────────────────────
function wsToggleFilter(wordKey, filterName, btn) {
  btn.classList.toggle('active');
  const panel = document.querySelector(`[data-ws-key="${wordKey}"]`);
  if (!panel) return;

  // Gather all active filters
  const activeFilters = [];
  panel.querySelectorAll('.ws-filter-chip.active').forEach(chip => {
    const onclick = chip.getAttribute('onclick') || '';
    const match = onclick.match(/wsToggleFilter\('[^']+','(\w+)'/);
    if (match) activeFilters.push(match[1]);
  });

  // Show/hide saved writings based on active filters
  const deck = panel.querySelector('.ws-saved-deck-section');
  if (!deck) return;
  deck.querySelectorAll('.saved-writing[data-source-type]').forEach(card => {
    const type = card.getAttribute('data-source-type');
    if (activeFilters.length === 0) {
      card.style.display = ''; // no filters = show all
    } else {
      card.style.display = activeFilters.includes(type) ? '' : 'none';
    }
  });

  // Toggle community placeholder
  const communityDiv = panel.querySelector('.ws-filtered-community');
  if (communityDiv) {
    communityDiv.style.display = activeFilters.includes('community') ? 'block' : 'none';
  }
}

// ── TARGET WORD DETECTION ────────────────────────────────────────────────────
// Checks whether the learner's sentence contains the target word. Direct
// substring match wins. If that fails, we try a tolerant splitter-aware match
// that allows common Chinese aspect markers, potential-form particles, and
// measure / directional morphemes BETWEEN the characters of a multi-char
// target word. This prevents false "missing_target" flags on legitimate forms:
//   看破 → 看不破, 看得破
//   看懂 → 看不懂, 看得懂
//   吃飯 → 吃了飯, 吃過飯, 吃一頓飯
//   睡著 → 睡不著, 睡得著
//   打電話 → 打過電話 (splitters between 打 and 電, then 電話 intact)
// Splitter set is deliberately narrow — we don't want to accept arbitrary
// substrings that merely happen to contain the target characters.
function wsTargetWordUsed(sentence, traditional) {
  if (!traditional) return true;
  if (!sentence) return false;
  if (sentence.includes(traditional)) return true;
  const chars = Array.from(traditional);
  if (chars.length < 2) return false;
  // Common Chinese splitters inside verb-complement / separable-verb compounds
  const splitters = '不得了過著一兩個次下來去到起完住';
  const escape = c => c.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  // Allow 1–3 splitter characters between each consecutive pair of target chars.
  // We require at least 1 char of gap (0 would just be the direct match we
  // already tested above).
  const pattern = chars.map(escape).join(`[${splitters}]{1,3}`);
  try {
    return new RegExp(pattern).test(sentence);
  } catch (e) {
    return false;
  }
}

// ── RUN CRITIQUE ─────────────────────────────────────────────────────────────
async function wsRunCritique(wordKey) {
  const wordData = wsGetWordData(wordKey);
  if (!wordData) return;
  const input = document.getElementById(`ws-critique-input-${wordKey}`);
  const posSelect = document.getElementById(`ws-critique-pos-${wordKey}`);
  const resultEl = document.getElementById(`ws-critique-result-${wordKey}`);
  const btn = input.closest('.ws-ai-workspace').querySelector('.ws-ai-submit-btn');
  const sentence = input.value.trim();
  const intendedPOS = posSelect ? posSelect.value : '';
  if (!sentence) { resultEl.innerHTML = `<div class="ws-ai-response"><div class="ws-ai-response-text" style="color:var(--rose)">Please write something first.</div></div>`; return; }

  btn.disabled = true; btn.textContent = '分析中…';
  resultEl.innerHTML = `<div class="ws-ai-response"><div class="ws-ai-response-text" style="color:var(--dim);font-style:italic">師父 is reading your writing…</div></div>`;

  try {
    const senseId = wordData.senseIds ? wordData.senseIds[0] : (wordData.senseId || null);
    const wordObjectId = wordData.wordObjectId || null;
    const fluency = wsGetFluencyLevel(wordKey);
    const apiResp = await wsCallAPI('/api/workshop/critique', {
      system_prompt: wsGetCritiquePrompt(wordData, intendedPOS, fluency),
      sentence: sentence,
      word_sense_id: senseId,
      word_object_id: wordObjectId,
      word_label: wordData.traditional || wordKey,
      engagement_id: wsEngagements[wordKey] || null,
    });
    if (apiResp.engagement_id) wsEngagements[wordKey] = apiResp.engagement_id;
    const raw = apiResp.text || '';
    const clean = raw.replace(/```json|```/g, '').trim();
    const data = JSON.parse(clean);

    // Deterministic override: if the learner's sentence doesn't contain the
    // target word at all, force verdict to "missing_target" no matter what
    // 師父 returned. Tolerant of Chinese verb-complement / separable-verb
    // splitters so we don't falsely flag legitimate forms like:
    //   看不破 (potential-negative of 看破)
    //   看得懂 (potential-positive of 看懂)
    //   吃了飯 / 吃過飯 (aspect markers inside 吃飯)
    //   睡不著 (potential-negative of 睡著)
    //   打過電話 (aspect inside 打電話)
    // A direct substring match takes precedence; only if that fails do we
    // try the tolerant splitter-aware match.
    if (!wsTargetWordUsed(sentence, wordData.traditional)) {
      data.verdict = 'missing_target';
      // Ensure we don't accidentally show a rewrite 師父 may have supplied
      data.corrected_cn = sentence;
    }

    const isUnworkable    = data.verdict === 'unworkable';
    const isMissingTarget = data.verdict === 'missing_target';
    const isNeedsWork     = data.verdict === 'needs_work';
    // Hide corrected sentence + Save whenever the learner hasn't earned to see it
    const hideCorrection  = isUnworkable || isMissingTarget || isNeedsWork;
    const hideSave        = hideCorrection;

    const verdictColor = data.verdict === 'correct' ? 'var(--jade)'
      : data.verdict === 'minor_issues' ? 'var(--gold)'
      : 'var(--rose)';
    const verdictLabel = data.verdict === 'correct' ? '✓ Correct'
      : data.verdict === 'minor_issues' ? '△ Minor issues'
      : isMissingTarget ? (langMode === 'zh' ? `✗ 你沒有使用「${wordData.traditional}」— 請重新寫` : `✗ You didn't use ${wordData.traditional} — come back and try again`)
      : isUnworkable ? (langMode === 'zh' ? '✗ 此情境不合適 — 請換一個方向' : '✗ Unworkable here — try a different scenario')
      : (langMode === 'zh' ? '✗ 請再試一次' : '✗ Needs work — rewrite using the notes below');
    const senseIdAttr = senseId ? `data-sense-id="${senseId}"` : '';
    const wordObjIdAttr = wordObjectId ? `data-word-object-id="${wordObjectId}"` : '';
    const vertical = textDir === 'vertical';

    // Build mastery chips for display
    const lvl = wsLevelLabel(data.assessed_level);
    const mst = wsMasteryLabel(data.assessed_mastery);
    const assessChips = (lvl ? `<span class="ws-level-chip">${lvl.icon} ${langMode === 'zh' ? lvl.zh : lvl.en}</span>` : '')
      + (mst ? `<span class="ws-mastery-chip">${mst.icon} ${langMode === 'zh' ? mst.zh : mst.en}</span>` : '');

    // ── Grammar pattern chips ──
    const identified = Array.isArray(data.grammar_patterns_identified) ? data.grammar_patterns_identified : [];
    const grammarBlock = identified.length
      ? `<div class="ws-grammar-patterns"><span class="ws-grammar-label">${langMode === 'zh' ? '句型' : 'Grammar patterns'}:</span> ${identified.map(gp => {
          const ref = (typeof WS_GRAMMAR_PATTERNS !== 'undefined') ? WS_GRAMMAR_PATTERNS.find(p => p.slug === gp.slug) : null;
          if (!ref) return '';
          const icon = gp.status === 'correct' ? '✓' : gp.status === 'almost' ? '△' : '✗';
          const label = langMode === 'zh' ? ref.zh_label : ref.en_label;
          const noteAttr = gp.note ? ` title="${String(gp.note).replace(/"/g,'&quot;')}"` : '';
          return `<span class="ws-grammar-chip ws-grammar-${gp.status || 'correct'}"${noteAttr}>${icon} ${label}</span>`;
        }).filter(Boolean).join(' ')}</div>`
      : '';

    // ── Redirect suggestion card (only when unworkable) ──
    const redirect = (isUnworkable && data.redirect_suggestion && typeof data.redirect_suggestion === 'object') ? data.redirect_suggestion : null;
    const redirectBlock = redirect && (redirect.scenario || redirect.example_cn)
      ? `<div class="ws-redirect-card" style="margin-top:0.6rem;padding:0.75rem 0.9rem;border:1px dashed var(--rose);border-radius:0.5rem;background:rgba(190,60,80,0.04)">
          <div class="ws-redirect-label" style="font-size:0.78rem;font-weight:600;color:var(--rose);margin-bottom:0.3rem">${langMode === 'zh' ? '試試另一個情境' : 'Try a different scenario'}</div>
          ${redirect.scenario ? `<div style="font-size:0.88rem;color:var(--ink);margin-bottom:0.4rem">${redirect.scenario}</div>` : ''}
          ${redirect.example_cn ? `<div class="resp-cn${vertical ? ' ws-vertical' : ''}" style="font-size:0.95rem">${segmentedHTML(redirect.example_cn, wordData)}</div>` : ''}
          ${redirect.example_en ? `<div class="resp-en" style="font-size:0.82rem;color:var(--dim);font-style:italic">${redirect.example_en}</div>` : ''}
        </div>`
      : '';

    // Main sentence block: for missing_target / needs_work / unworkable we show
    // the learner's ORIGINAL sentence (not a rewrite), for correct / minor_issues
    // we show 師父's polished version. Either way, display corrected_cn which the
    // upstream logic has already normalised.
    const showCorrectedEn = !hideCorrection && data.corrected_en;

    // "Save Anyway" — unverified, private-only path for needs_work / missing_target.
    // The learner can bank their attempt as a personal draft, but it cannot be
    // shared to community and won't carry 師父's verification flag. This gives
    // them agency without letting them bypass the teaching signal.
    const allowSaveAnyway = (isNeedsWork || isMissingTarget);

    resultEl.innerHTML = `
      <div class="ws-ai-response">
        <div class="ws-ai-response-label" style="color:${verdictColor}">${verdictLabel}</div>
        ${assessChips ? `<div class="ws-assess-chips">${assessChips}</div>` : ''}
        ${grammarBlock}
        <div class="ws-ai-response-text">
          <span class="resp-cn${vertical ? ' ws-vertical' : ''}">${segmentedHTML(data.corrected_cn, wordData)}</span>
          ${showCorrectedEn ? `<span class="resp-en">${data.corrected_en}</span>` : ''}
          <span class="resp-note">${data.feedback}</span>
          ${data.register_note ? `<span class="resp-note" style="margin-top:0.2rem">${data.register_note}</span>` : ''}
          ${!isUnworkable && !isMissingTarget && data.mastery_guidance ? `<span class="resp-note ws-mastery-guidance-note" style="margin-top:0.3rem"><strong>${langMode === 'zh' ? '成長建議' : 'Growth tip'}:</strong> ${data.mastery_guidance}</span>` : ''}
        </div>
        ${redirectBlock}
        <div class="ws-ai-response-actions">
          ${hideSave ? '' : `<label class="ws-share-community" title="${langMode === 'zh' ? '分享至社群，讓其他學習者看見' : 'Share with community so other learners can see'}">
            <input type="checkbox" class="ws-share-checkbox" data-word-key="${wordKey}" ${wsDefaultPublic() ? 'checked' : ''}>
            <span>${langMode === 'zh' ? '分享至社群' : 'Share with community'}</span>
          </label>
          <button class="ex-sent-save" ${senseIdAttr} ${wordObjIdAttr} data-word-key="${wordKey}" data-cn="${data.corrected_cn.replace(/"/g,'&quot;')}" data-en="${(data.corrected_en || '').replace(/"/g,'&quot;')}" data-feedback="${(data.feedback + (data.register_note ? ' ' + data.register_note : '')).replace(/"/g,'&quot;')}" data-ai="1" data-original-cn="${sentence.replace(/"/g,'&quot;')}" data-assessed-level="${data.assessed_level || ''}" data-assessed-mastery="${data.assessed_mastery || ''}" data-mastery-guidance="${(data.mastery_guidance || '').replace(/"/g,'&quot;')}" data-grammar-patterns="${JSON.stringify(identified).replace(/"/g,'&quot;')}" onclick="wsSaveAIResult(this)">＋ Save Writing & Feedback</button>`}
          ${hideSave ? `<button class="ws-try-again-btn ws-try-again-primary" onclick="wsTryAgain('${wordKey}','critique')">↻ ${langMode === 'zh' ? '再試一次' : 'Try Again'}</button>` : `<button class="ws-try-again-btn" onclick="wsTryAgain('${wordKey}','critique')">↻ ${langMode === 'zh' ? '再試一次' : 'Try Again'}</button>`}
          ${allowSaveAnyway ? `<button class="ws-save-anyway-btn" ${senseIdAttr} ${wordObjIdAttr} data-word-key="${wordKey}" data-cn="${sentence.replace(/"/g,'&quot;')}" data-en="" data-feedback="${(data.feedback + (data.register_note ? ' ' + data.register_note : '')).replace(/"/g,'&quot;')}" data-ai="0" data-original-cn="${sentence.replace(/"/g,'&quot;')}" data-assessed-level="${data.assessed_level || ''}" data-assessed-mastery="${data.assessed_mastery || ''}" data-is-public="0" data-unverified="1" data-verdict="${data.verdict}" data-grammar-patterns="${JSON.stringify(identified).replace(/"/g,'&quot;')}" title="${langMode === 'zh' ? '儲存為私人草稿；不會分享，也不包含師父的驗證 — 會標記為不合格' : 'Save as a private draft — not verified by 師父, not shared to community, and flagged with the verdict'}" onclick="wsSaveAIResult(this)">${langMode === 'zh' ? '仍要儲存為草稿' : 'Save anyway as draft'}</button>` : ''}
        </div>
      </div>`;
    setTimeout(() => resultEl.scrollIntoView({ behavior: 'smooth', block: 'center' }), 100);
  } catch(e) {
    if (e.message === 'auth_required') {
      wsShowAuthPrompt(wordKey, 'critique', `ws-critique-result-${wordKey}`);
      btn.disabled = false; btn.textContent = 'Analyse 分析 →';
      return;
    }
    resultEl.innerHTML = `<div class="ws-ai-response"><div class="ws-ai-response-text" style="color:var(--rose)">Something went wrong. Please try again.</div></div>`;
  }
  btn.disabled = false; btn.textContent = 'Analyse 分析 →';
}

// ── RUN THEME ────────────────────────────────────────────────────────────────
async function wsRunTheme(wordKey) {
  const wordData = wsGetWordData(wordKey);
  if (!wordData) return;
  const input = document.getElementById(`ws-theme-input-${wordKey}`);
  const resultEl = document.getElementById(`ws-theme-result-${wordKey}`);
  const btn = input.closest('.ws-ai-workspace').querySelector('.ws-ai-submit-btn');
  const theme = input.value.trim();
  if (!theme) { resultEl.innerHTML = `<div class="ws-ai-response"><div class="ws-ai-response-text" style="color:var(--rose)">Please enter a theme or subject.</div></div>`; return; }

  btn.disabled = true; btn.textContent = '生成中…';
  resultEl.innerHTML = `<div class="ws-ai-response"><div class="ws-ai-response-text" style="color:var(--dim);font-style:italic">師父 is crafting your writing…</div></div>`;

  try {
    const senseId = wordData.senseIds ? wordData.senseIds[0] : (wordData.senseId || null);
    const wordObjectId = wordData.wordObjectId || null;
    const fluency = wsGetFluencyLevel(wordKey);
    const apiResp = await wsCallAPI('/api/workshop/generate', {
      system_prompt: wsGetThemePrompt(wordData, fluency),
      theme: theme,
      word_sense_id: senseId,
      word_object_id: wordObjectId,
      word_label: wordData.traditional || wordKey,
    });
    const raw = apiResp.text || '';
    const clean = raw.replace(/```json|```/g, '').trim();
    const data = JSON.parse(clean);
    const senseIdAttr = senseId ? `data-sense-id="${senseId}"` : '';
    const wordObjIdAttr = wordObjectId ? `data-word-object-id="${wordObjectId}"` : '';
    const vertical = textDir === 'vertical';

    // Build mastery chips
    const lvl = wsLevelLabel(data.assessed_level);
    const mst = wsMasteryLabel(data.assessed_mastery);
    const assessChips = (lvl ? `<span class="ws-level-chip">${lvl.icon} ${langMode === 'zh' ? lvl.zh : lvl.en}</span>` : '')
      + (mst ? `<span class="ws-mastery-chip">${mst.icon} ${langMode === 'zh' ? mst.zh : mst.en}</span>` : '');

    resultEl.innerHTML = `
      <div class="ws-ai-response">
        <div class="ws-ai-response-label">✦ 師父 · Theme: ${theme}</div>
        ${assessChips ? `<div class="ws-assess-chips">${assessChips}</div>` : ''}
        <div class="ws-ai-response-text">
          <span class="resp-cn${vertical ? ' ws-vertical' : ''}">${segmentedHTML(data.cn, wordData)}</span>
          <span class="resp-en">${data.en}</span>
          <span class="resp-note">${data.note}</span>
        </div>
        <div class="ws-ai-response-actions">
          <label class="ws-share-community" title="${langMode === 'zh' ? '分享至社群，讓其他學習者看見' : 'Share with community so other learners can see'}">
            <input type="checkbox" class="ws-share-checkbox" data-word-key="${wordKey}" ${wsDefaultPublic() ? 'checked' : ''}>
            <span>${langMode === 'zh' ? '分享至社群' : 'Share with community'}</span>
          </label>
          <button class="ex-sent-save" ${senseIdAttr} ${wordObjIdAttr} data-word-key="${wordKey}" data-cn="${data.cn.replace(/"/g,'&quot;')}" data-en="${data.en.replace(/"/g,'&quot;')}" data-feedback="${(data.note || '').replace(/"/g,'&quot;')}" data-ai="1" data-generated="1" data-assessed-level="${data.assessed_level || ''}" data-assessed-mastery="${data.assessed_mastery || ''}" onclick="wsSaveAIResult(this)">＋ Save Writing & Feedback</button>
          <button class="ws-try-again-btn" onclick="wsTryAgain('${wordKey}','theme')">↻ ${langMode === 'zh' ? '再試一次' : 'Try Again'}</button>
        </div>
      </div>`;
    setTimeout(() => resultEl.scrollIntoView({ behavior: 'smooth', block: 'center' }), 100);
  } catch(e) {
    if (e.message === 'auth_required') {
      wsShowAuthPrompt(wordKey, 'theme', `ws-theme-result-${wordKey}`);
      btn.disabled = false; btn.textContent = 'Generate 生成 →';
      return;
    }
    resultEl.innerHTML = `<div class="ws-ai-response"><div class="ws-ai-response-text" style="color:var(--rose)">Something went wrong. Please try again.</div></div>`;
  }
  btn.disabled = false; btn.textContent = 'Generate 生成 →';
}

// ── SAVE AI RESULT ───────────────────────────────────────────────────────────
async function wsSaveAIResult(btn) {
  const wordKey = btn.dataset.wordKey;
  const cn = btn.dataset.cn;
  const en = btn.dataset.en;
  const feedback = btn.dataset.feedback || '';
  const aiVerified = btn.dataset.ai === '1';
  const isGenerated = btn.dataset.generated === '1';
  const isUnverified = btn.dataset.unverified === '1';
  const verdict = btn.dataset.verdict || '';
  const senseId = btn.dataset.senseId;
  const wordObjectId = btn.dataset.wordObjectId || null;
  const assessedLevel = btn.dataset.assessedLevel || '';
  const assessedMastery = btn.dataset.assessedMastery || '';
  const masteryGuidance = btn.dataset.masteryGuidance || '';
  const originalCn = btn.dataset.originalCn || '';
  let grammarPatterns = [];
  try {
    grammarPatterns = JSON.parse(btn.dataset.grammarPatterns || '[]') || [];
  } catch(e) { grammarPatterns = []; }
  // Keep only patterns whose slug is known to the client catalogue; drop any
  // leftovers 師父 might have emitted that don't exist in WS_GRAMMAR_PATTERNS.
  grammarPatterns = (Array.isArray(grammarPatterns) ? grammarPatterns : [])
    .filter(gp => gp && gp.slug && (typeof WS_GRAMMAR_PATTERNS === 'undefined' || WS_GRAMMAR_PATTERNS.some(p => p.slug === gp.slug)));

  // Read the inline "Share with community" checkbox from the same action row.
  // Save Anyway buttons explicitly force data-is-public="0" — unverified private
  // drafts must never be shared, regardless of the user's default.
  let isPublic = wsDefaultPublic();
  if (btn.dataset.isPublic === '0') {
    isPublic = false;
  } else {
    const actionsRow = btn.closest('.ws-ai-response-actions');
    if (actionsRow) {
      const shareBox = actionsRow.querySelector('.ws-share-checkbox');
      if (shareBox) isPublic = shareBox.checked;
    }
  }

  if (!window.__AUTH) {
    const pending = {
      wordKey, context: 'save_result',
      cn, en, feedback, aiVerified, isGenerated, senseId, wordObjectId,
      assessedLevel, assessedMastery, masteryGuidance, originalCn,
      grammarPatterns, isPublic,
    };
    localStorage.setItem('ww_pending', JSON.stringify(pending));
    wsShowAuthPrompt(wordKey, isGenerated ? 'theme' : 'critique', isGenerated ? `ws-theme-result-${wordKey}` : `ws-critique-result-${wordKey}`);
    return;
  }

  btn.disabled = true; btn.textContent = '…';

  try {
    const response = await fetch('/api/workshop/save-example', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': wsCsrf(),
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        word_sense_id: senseId ? parseInt(senseId) : null,
        word_object_id: wordObjectId ? parseInt(wordObjectId) : null,
        chinese_text: cn,
        english_text: en,
        original_chinese_text: originalCn || null,
        ai_verified: aiVerified,
        ai_feedback: feedback || null,
        source_type: isGenerated ? 'generated' : 'learner',
        assessed_level: assessedLevel || null,
        assessed_mastery: assessedMastery || null,
        mastery_guidance: masteryGuidance || null,
        engagement_id: wsEngagements[wordKey] || null,
        grammar_patterns: grammarPatterns,
        is_public: !!isPublic,
      }),
    });

    if (!response.ok) throw new Error('save_failed');
    const saved = await response.json();

    // Source label reflects the verification state. Unverified "Save Anyway"
    // drafts from needs_work / missing_target carry the verdict forward so the
    // learner sees the flag every time they revisit the saved entry.
    const verdictLabelMap = {
      needs_work:     langMode === 'zh' ? '需要修改' : 'Needs work',
      missing_target: langMode === 'zh' ? '未使用目標詞' : 'Target word missing',
      unworkable:     langMode === 'zh' ? '情境不合適' : 'Unworkable scenario',
    };
    let source;
    if (isUnverified) {
      const flag = verdictLabelMap[verdict] || (langMode === 'zh' ? '未驗證' : 'Unverified');
      source = langMode === 'zh' ? `⚠ 未驗證草稿 · ${flag}` : `⚠ Unverified draft · ${flag}`;
    } else if (isGenerated) {
      source = '師父 generated';
    } else if (aiVerified) {
      source = '師父 verified';
    } else {
      source = 'My writing';
    }
    const today = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    wsSaveToWord(wordKey, { id: saved.id, cn, en, feedback, source, date: today, assessedLevel, assessedMastery, masteryGuidance, originalCn, grammarPatterns });
    wsRefreshDeck(wordKey);

    // Close the AI response panel
    const responseEl = btn.closest('.ws-ai-response');
    if (responseEl) responseEl.remove();

    // Scroll to the newest writing
    const deckEl = document.getElementById(`ws-deck-${wordKey}`);
    if (deckEl) {
      const lastCard = deckEl.querySelector('.ex-sent:last-child');
      setTimeout(() => (lastCard || deckEl).scrollIntoView({ behavior: 'smooth', block: 'center' }), 100);
    }
  } catch(e) {
    btn.textContent = '✗ Error';
    setTimeout(() => { btn.textContent = '＋ Save Writing & Feedback'; btn.disabled = false; }, 2000);
  }
}

// ── RENDER WORKSHOP HTML ─────────────────────────────────────────────────────
// opts: { isIWP: bool, allPOS: string[], defaultExamplesHTML: string }
function wsRenderPanel(wordKey, wordData, opts) {
  opts = opts || {};
  const isIWP = opts.isIWP || false;
  const allPOS = opts.allPOS || [];
  const defaultExHTML = opts.defaultExamplesHTML || '';
  const panelClass = isIWP ? 'ws-panel ws-panel--iwp' : 'ws-panel';
  const wsDefault = (typeof workshopDefault !== 'undefined') ? workshopDefault : 'collapsed';
  const expanded = wsDefault === 'expanded';

  const titleText = langMode === 'zh' ? '寫作院' : langMode === 'both' ? 'Writing Conservatory 寫作院' : 'Writing Conservatory';

  // Filter chips
  const filterChips = `<div class="ws-filters">
    <button class="ws-filter-chip" onclick="wsToggleFilter('${wordKey}','shifu',this)">${langMode === 'zh' ? '師父寫作' : '師父 Writings'}</button>
    <button class="ws-filter-chip" onclick="wsToggleFilter('${wordKey}','mine',this)">${langMode === 'zh' ? '我的寫作' : 'My Writings'}</button>
    <button class="ws-filter-chip" onclick="wsToggleFilter('${wordKey}','community',this)">${langMode === 'zh' ? '社群' : 'Community'}</button>
  </div>`;

  // POS select options
  const posOptions = allPOS.length === 1
    ? allPOS.map(p => `<option value="${p}" selected>${POS_ABBR[p] || p} — ${langMode === 'zh' ? (POS_ZH[p] || posDisplay(p)) : posDisplay(p)}</option>`).join('')
    : `<option value="">${langMode === 'zh' ? '選擇詞性…' : langMode === 'both' ? 'Select POS… 選擇詞性…' : 'Select POS…'}</option>` +
      allPOS.map(p => `<option value="${p}">${POS_ABBR[p] || p} — ${langMode === 'zh' ? (POS_ZH[p] || posDisplay(p)) : posDisplay(p)}</option>`).join('');

  const vertical = textDir === 'vertical';

  return `
  <div class="${panelClass}" data-ws-key="${wordKey}">
    <div class="ws-header" onclick="wsTogglePanel('${wordKey}')" style="cursor:pointer">
      <div class="ws-title">${titleText}</div>
      <span class="ws-toggle">${expanded ? '▼' : '▲'}</span>
    </div>

    <div id="ws-body-${wordKey}" style="display:${expanded ? 'flex' : 'none'}; flex-direction:column; gap:0.75rem;">

      <!-- Default examples (always visible) -->
      <div class="ex-sentences">${defaultExHTML}</div>

      <!-- Filter chips -->
      ${filterChips}

      <!-- Community placeholder (shown when Community filter active) -->
      <div class="ws-filtered-community" style="display:none">
        <div style="font-size:0.81rem;color:var(--dim);padding:0.5rem 0">${langMode === 'zh' ? '社群內容即將推出' : 'Community contributions coming soon'}</div>
      </div>

      <!-- Saved deck (filtered by chips) -->
      <div id="ws-deck-wrap-${wordKey}">${wsRenderSavedDeck(wordKey, wordData)}</div>

      <!-- 師父 WRITING AREA -->
      <div class="ws-saved-deck-label" style="color:var(--accent);margin-top:0.75rem">Write with 師父</div>
      <div class="ws-shifu-area">
        <div class="ws-ai-tabs">
          <button class="ws-ai-tab active" onclick="wsSwitchAITab('${wordKey}', 'critique', this)">✍️ Write &amp; Get 師父 Feedback</button>
          <button class="ws-ai-tab" onclick="wsSwitchAITab('${wordKey}', 'theme', this)">🎯 Ask 師父 to Write</button>
        </div>

        <!-- CRITIQUE TAB -->
        <div id="ws-tab-critique-${wordKey}" class="ws-ai-workspace">
          <div class="ws-ai-instruction">Explore using <strong style="color:var(--accent)">${wordData.traditional}</strong> by writing your own contribution with 師父. 師父 will check grammar, register, and naturalness, assign a level and mastery assessment — then you can save the verified writing and 師父 feedback.</div>
          <div class="ws-ai-input-row">
            <select class="ws-ai-pos-select" id="ws-critique-pos-${wordKey}">
              ${posOptions}
            </select>
            <textarea class="ws-ai-textarea${vertical ? ' vertical-mode' : ''}" id="ws-critique-input-${wordKey}" placeholder="${vertical ? '在這裡寫…' : '在這裡寫… Write here…'}" rows="2"></textarea>
            <select class="ws-fluency-select" id="ws-fluency-${wordKey}" onchange="wsSetFluencyLevel('${wordKey}',this.value)">
              <option value=""${!wsGetFluencyLevel(wordKey) ? ' selected' : ''}>${langMode === 'zh' ? '師父反饋的程度？' : 'At what level would you like 師父 to offer feedback?'}</option>
              ${WS_LEVELS.map(l => `<option value="${l.slug}"${l.slug === wsGetFluencyLevel(wordKey) ? ' selected' : ''}>${l.icon} ${langMode === 'zh' ? l.zh : l.en}</option>`).join('')}
            </select>
            <button class="ws-ai-submit-btn" onclick="wsRunCritique('${wordKey}')">Analyse 分析 →</button>
          </div>
          <div id="ws-critique-result-${wordKey}"></div>
        </div>

        <!-- THEME TAB -->
        <div id="ws-tab-theme-${wordKey}" class="ws-ai-workspace" style="display:none">
          <div class="ws-ai-instruction">Ask 師父 to write a sentence using <strong style="color:var(--accent)">${wordData.traditional}</strong> around any theme you love. 師父 will target your selected level and include a mastery assessment.</div>
          <div class="ws-ai-input-row">
            <input type="text" class="ws-ai-theme-input" id="ws-theme-input-${wordKey}" placeholder="e.g. soccer, cooking, my grandmother, space travel…">
            <select class="ws-fluency-select" id="ws-fluency-theme-${wordKey}" onchange="wsSetFluencyLevel('${wordKey}',this.value)">
              <option value=""${!wsGetFluencyLevel(wordKey) ? ' selected' : ''}>${langMode === 'zh' ? '師父反饋的程度？' : 'At what level would you like 師父 to offer feedback?'}</option>
              ${WS_LEVELS.map(l => `<option value="${l.slug}"${l.slug === wsGetFluencyLevel(wordKey) ? ' selected' : ''}>${l.icon} ${langMode === 'zh' ? l.zh : l.en}</option>`).join('')}
            </select>
            <button class="ws-ai-submit-btn" onclick="wsRunTheme('${wordKey}')">Generate 生成 →</button>
          </div>
          <div id="ws-theme-result-${wordKey}"></div>
        </div>
      </div><!-- /.ws-shifu-area -->

    </div>
  </div>`;
}

// ── WORD DATA LOOKUP (view-specific, set by consuming view) ──────────────────
// Each view must define wsGetWordData(wordKey) before using workshop functions.
// Card view: looks up WORDS array
// IWP: returns WORD + sense data
if (typeof wsGetWordData === 'undefined') {
  window.wsGetWordData = function(wordKey) { return null; };
}

// ── HYDRATE SAVED DECK FROM DB ───────────────────────────────────────────────
function wsHydrateSavedDeck() {
  if (!window.__AUTH || !window.__AUTH.savedExamples) return;
  window.__AUTH.savedExamples.forEach(ex => {
    // View must provide wsResolveWordKey(ex) to map sense_id → wordKey
    const wordKey = typeof wsResolveWordKey === 'function' ? wsResolveWordKey(ex) : null;
    if (wordKey) {
      const d = ex.created_at ? new Date(ex.created_at) : null;
      const dateStr = d ? d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
      const src = ex.source_type === 'generated' ? '師父 generated' : ex.ai_verified ? '師父 verified' : 'My writing';
      wsSaveToWord(wordKey, {
        id: ex.id,
        cn: ex.chinese_text,
        en: ex.english_text,
        feedback: ex.ai_feedback || '',
        source: src,
        date: dateStr,
        assessedLevel: ex.assessed_level || '',
        assessedMastery: ex.assessed_mastery || '',
        masteryGuidance: ex.mastery_guidance || '',
        originalCn: ex.original_chinese_text || '',
        grammarPatterns: Array.isArray(ex.grammar_patterns) ? ex.grammar_patterns : [],
      });
    }
  });
}
</script>
