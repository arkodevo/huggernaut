/*
 * Generate 流動 Living Lexicon — Enrichment Quality Guide (canonical .docx).
 *
 * Output: /Users/chuluoyi/Documents/華語/planning/流動_Enrichment_Quality_Guide_vX.Y.docx
 *
 * Run:  NODE_PATH=/opt/homebrew/lib/node_modules node database/scripts/gen_enrichment_guide.js
 *
 * (Global `docx` package required — `npm install -g docx` if missing.)
 *
 * Version-bump protocol: bump the title, changelog, output filename, and
 * memory companion (project_enrichment_guide_v2.md) in lockstep. Prior
 * version .docx files are preserved — 絡一 curates version history.
 *
 * Moved from /tmp/gen_guide_v2.js on 2026-04-21 into the permanent
 * database/scripts/ home.
 */

const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  Header, Footer, AlignmentType, LevelFormat, BorderStyle, WidthType,
  ShadingType, HeadingLevel, PageBreak, PageNumber,
} = require('docx');
const fs = require('fs');

const FONT = 'Arial';
const BLACK = '000000';
const GRAY = '555555';
const GREY_BG = 'F2F2F2';
const BLUE_BG = 'D5E8F0';
const BORDER = { style: BorderStyle.SINGLE, size: 4, color: 'CCCCCC' };
const borders = { top: BORDER, bottom: BORDER, left: BORDER, right: BORDER };

function para(text, opts = {}) {
  return new Paragraph({
    children: [new TextRun({ text, font: FONT, size: opts.size || 22, bold: opts.bold, italics: opts.italics, color: opts.color || BLACK })],
    alignment: opts.align || AlignmentType.LEFT,
    spacing: opts.spacing || { after: 120 },
  });
}

function h1(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_1,
    children: [new TextRun({ text, font: FONT, size: 32, bold: true, color: BLACK })],
    spacing: { before: 360, after: 180 },
  });
}

function h2(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_2,
    children: [new TextRun({ text, font: FONT, size: 28, bold: true, color: BLACK })],
    spacing: { before: 280, after: 140 },
  });
}

function h3(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_3,
    children: [new TextRun({ text, font: FONT, size: 24, bold: true, color: BLACK })],
    spacing: { before: 220, after: 120 },
  });
}

function bullet(text, opts = {}) {
  const children = opts.runs
    ? opts.runs.map(r => new TextRun({ text: r.text, font: FONT, size: 22, bold: r.bold, italics: r.italics, color: r.color || BLACK }))
    : [new TextRun({ text, font: FONT, size: 22 })];
  return new Paragraph({
    numbering: { reference: 'bullets', level: 0 },
    children,
    spacing: { after: 60 },
  });
}

function subBullet(text) {
  return new Paragraph({
    numbering: { reference: 'bullets', level: 1 },
    children: [new TextRun({ text, font: FONT, size: 22 })],
    spacing: { after: 60 },
  });
}

// numItem uses a per-list counter so each call-site can request an independent numbered list.
// Each `listId` maps to its own numbering reference so counters don't accumulate across sections.
const _registeredLists = new Set();
function numItem(text, listId = 'default') {
  _registeredLists.add(listId);
  return new Paragraph({
    numbering: { reference: 'numbered_' + listId, level: 0 },
    children: [new TextRun({ text, font: FONT, size: 22 })],
    spacing: { after: 60 },
  });
}

function runs(...parts) {
  return new Paragraph({
    children: parts.map(p =>
      typeof p === 'string'
        ? new TextRun({ text: p, font: FONT, size: 22 })
        : new TextRun({ text: p.text, font: FONT, size: 22, bold: p.bold, italics: p.italics, color: p.color || BLACK })
    ),
    spacing: { after: 120 },
  });
}

function quoteBlock(text) {
  return new Paragraph({
    children: [new TextRun({ text, font: FONT, size: 22, italics: true, color: GRAY })],
    indent: { left: 480 },
    spacing: { after: 120, before: 60 },
  });
}

function codeBlock(lines) {
  return lines.map(line => new Paragraph({
    children: [new TextRun({ text: line || ' ', font: 'Courier New', size: 20 })],
    shading: { fill: GREY_BG, type: ShadingType.CLEAR, color: 'auto' },
    indent: { left: 240 },
    spacing: { after: 0 },
  }));
}

function cell(text, opts = {}) {
  return new TableCell({
    borders,
    width: { size: opts.width, type: WidthType.DXA },
    shading: opts.shade ? { fill: opts.shade, type: ShadingType.CLEAR, color: 'auto' } : undefined,
    margins: { top: 80, bottom: 80, left: 140, right: 140 },
    children: [new Paragraph({
      children: [new TextRun({ text, font: FONT, size: 22, bold: opts.bold, color: opts.color || BLACK })],
      alignment: opts.align || AlignmentType.LEFT,
    })],
  });
}

function simpleTable(headers, dataRows, widths) {
  const rows = [
    new TableRow({
      tableHeader: true,
      children: headers.map((h, i) => cell(h, { width: widths[i], bold: true, shade: BLUE_BG })),
    }),
    ...dataRows.map(row => new TableRow({
      children: row.map((c, i) => cell(typeof c === 'string' ? c : c.text, {
        width: widths[i],
        bold: typeof c === 'object' && c.bold,
        shade: typeof c === 'object' ? c.shade : undefined,
        color: typeof c === 'object' ? c.color : undefined,
      })),
    })),
  ];
  return new Table({
    width: { size: widths.reduce((a, b) => a + b, 0), type: WidthType.DXA },
    columnWidths: widths,
    rows,
  });
}

function pageBreak() {
  return new Paragraph({ children: [new PageBreak()] });
}

const content = [];

// Title
content.push(
  new Paragraph({
    children: [new TextRun({ text: '流動 Living Lexicon', font: FONT, size: 56, bold: true })],
    alignment: AlignmentType.CENTER,
    spacing: { before: 2400, after: 240 },
  }),
  new Paragraph({
    children: [new TextRun({ text: 'Enrichment Quality Guide v2.3', font: FONT, size: 44, bold: true, color: '1F4E79' })],
    alignment: AlignmentType.CENTER,
    spacing: { after: 600 },
  }),
  new Paragraph({
    children: [new TextRun({ text: '光流 + 惠明 + 絡一 — 2026-04-19', font: FONT, size: 26, color: GRAY })],
    alignment: AlignmentType.CENTER,
    spacing: { after: 720 },
  }),
  new Paragraph({
    children: [new TextRun({ text: 'For: 澄言 (enrichment) · 惠明 (editorial audit) · 光流 (structural audit + tooling) · 絡一 (editorial verdict)', font: FONT, size: 22, italics: true, color: GRAY })],
    alignment: AlignmentType.CENTER,
    spacing: { after: 600 },
  }),
);

// Version History
content.push(h2('Version History'));
content.push(simpleTable(
  ['Version', 'Changes'],
  [
    ['v1.0', 'Initial release.'],
    ['v1.1', 'Decision ladder, triage order, uncertainty workflow, collocation warning, second-half scan.'],
    ['v1.2', 'Clarified 2+ relation tension (1 clean + flag > 2 junk). Added Vcomp checklist item.'],
    ['v1.3', 'Companion to POS Reference Guide v2.1.'],
    ['v1.4', 'Bilingual notes required (formula_en/_zh, usage_note_en/_zh, learner_traps_en/_zh); frozen 54-domain set.'],
    [
      'v2.0',
      'Comprehensive revision: Intensity two-stage decision (0 = Not Applicable, null is blocker), contrast-or-trap written-dimension test, root-form/bound-morpheme example hygiene, six sense-split triggers, category/sequence/hypernym trap, reviewer-process rules, storage-value distinctions, valence-shift pattern, mandatory --dry-run gate, 13-entry lessons ledger.',
    ],
    [
      'v2.1',
      '惠明 alignment audit fixes — template-vs-guide parity. Valency mapping extended to Vs/Vspred/Vaux. DO-NOT-INFER language on TOCFL/HSK. dim-fluid ≠ semantic_mode warning. Collocations minimum (2) + REJECT/ACCEPT examples. Relations decision ladder + contrast-test + category-trap + flag-over-fake notes at field-entry. Variant inflation rule. Structure guidance for 3+ char words. Disputed-POS two-sense workflow ported from template.',
    ],
    [
      'v2.2',
      'Intensity categorical rule (binding): Stage 1 answered by POS category, not per-word judgment. ONLY Vs / Vst / degree-Adv / abstract-emotional-N / IE get graded 1-5; all action verbs, Vaux/Vcomp, Vsattr, concrete nouns, function words, non-degree adverbs default to intensity: 0. Predictability beats per-word accuracy.',
    ],
    [
      { text: 'v2.3', bold: true, shade: 'FFF2CC' },
      { text: 'semantic_mode RETIRED — column + FK dropped on word_senses; attribute + 5 designations removed. 4,574 of 4,634 tagged senses (98.6%) silently defaulted to literal-only; the field was dead. The literal/figurative axis is now captured by (a) proper sense splitting when uses diverge, and (b) dimension tagging (concrete vs abstract/internal). Dimension expanded with explicit MULTI-SELECT guidance + per-slug table + 6 worked examples (迷戀 → [internal, abstract]; 跑 → [external, concrete]; 吧 → [grammatical, pragmatic]). Validator R4a retired. "Enrich with 師父" admin save path fixed: enrich-existing decision now does full content-replace (definitions, examples, notes, relations, collocations, pivots) via shared writeSenseContent helper, not just attribute scalars + notes.', shade: 'FFF2CC' },
    ],
  ],
  [1560, 7800]
));

content.push(pageBreak());

// 0. Core philosophy
content.push(h1('0. Core Philosophy'));
content.push(para('Quality wins over completeness. Flag wins over fake.', { bold: true, size: 26 }));
content.push(para('Every field in the schema is an editorial decision, not a field to fill. If you cannot make a confident decision, flag it — don\'t invent, don\'t stretch, don\'t settle for a weak second edge to hit a count. A sense with one clean relation and a principled _flags note is worth more than three relations where two are weak.'));
content.push(para('The pipeline runs visibly: 澄言 enriches → 光流 runs the structural validator + reviews batch-level metrics → 惠明 runs the editorial audit → 絡一 adjudicates. Reviewer disagreements are signal, not noise. If you aren\'t sure, surface the uncertainty.'));
content.push(runs({ text: 'Your output feeds learners. ', bold: true }, 'A learner filtering for "mild-intensity positive emotions" expects the data to be right. 5,599 senses sat at default intensity=1 because no layer forced the decision — 我們不要再做一次.'));

// 1. Before you start
content.push(h1('1. Before You Start'));
content.push(h2('1.1 Pre-submission gate (mandatory)'));
content.push(para('Before submitting any batch, run:'));
content.push(...codeBlock(['php artisan words:import /path/to/your_file.jsonl --dry-run']));
content.push(para('This catches every mechanical failure — invalid slugs, missing required fields, out-of-range values. If the dry-run fails, fix and re-run. Do not submit output that the validator rejects.'));

content.push(h2('1.2 Three storage-value principles'));
content.push(numItem('Slug values must be copied exactly from the frozen list. No pluralization changes, no synonyms, no variants. The slug IS the slug.', 'storage_principles'));
content.push(numItem('Integer values must be in their documented range. Intensity 0-5, valency 0/1/2/null by POS, etc.', 'storage_principles'));
content.push(numItem('Required fields must be present. Bilingual notes, examples ≥2, collocations ≥2, etc.', 'storage_principles'));
content.push(para('If you want to express a concept the frozen set doesn\'t have, flag it with _flags — don\'t invent.'));

content.push(h2('1.3 One-question pre-submission test'));
content.push(quoteBlock('For every sense, ask: "Is every value I wrote an explicit decision I would defend, or did I default past it?" If there\'s any field you can\'t defend, either fix it or flag it.'));

// 2. Structural fields
content.push(h1('2. Structural Fields'));
content.push(h2('2.1 POS classification'));
content.push(para('Use the POS Reference Guide v2.1 as authoritative. The verb grid:'));
content.push(simpleTable(
  ['', 'Transitive', 'Intransitive', 'Separable'],
  [
    [{ text: 'Action', bold: true, shade: GREY_BG }, 'V', 'Vi', 'Vsep'],
    [{ text: 'Process', bold: true, shade: GREY_BG }, 'Vpt', 'Vp', 'Vpsep'],
    [{ text: 'State', bold: true, shade: GREY_BG }, 'Vst', 'Vs', 'Vssep'],
  ],
  [1800, 2520, 2520, 2520]
));
content.push(para('Plus: Vsattr (attributive only, CLOSED), Vspred (predicative only), Vaux (modal), Vcomp (complement).'));
content.push(para('Non-verb: N, M, Adv, Prep, Conj, Ptc, Det, Prn, Num, IE, Ph, CE, Intj, Aux.'));
content.push(h3('Valency by POS'));
content.push(para('Every verb POS has a valency assignment. Matches the full grid:'));
content.push(simpleTable(
  ['Valency', 'POS types', 'Reasoning'],
  [
    ['0', { text: 'Vi, Vp, Vs, Vspred, Vsattr, Vcomp, Vssep', bold: true }, 'Intransitive. Vsattr has no argument structure (attributive only). Vcomp is a complement morpheme (no independent object). Vssep is stative-separable (擔心 — stative, pseudo-O is separable 心).'],
    ['1', { text: 'V, Vpt, Vst, Vaux, Vsep, Vpsep', bold: true }, 'Transitive. Vsep / Vpsep count the separable O as valency (結婚 → 結了婚). Vaux takes the following VP as its complement.'],
    ['2', { text: 'ditransitive (rare)', bold: true }, 'e.g. 給 — give (someone) (something).'],
    ['null', { text: 'non-verbs', bold: true }, 'N, M, Adv, Prep, Conj, Ptc, Det, Prn, Num, IE, Ph, CE, Intj, Aux.'],
  ],
  [900, 3600, 4500]
));
content.push(runs({ text: 'Source for Vsattr/Vcomp/Vsep/Vpsep/Vssep: ', bold: true }, '澄言\'s batch-11 calibration, confirmed in v2.1 audit.'));

content.push(h3('Known POS failure patterns (avoid)'));
content.push(bullet('Vsattr overuse — Vsattr is CLOSED; if the word isn\'t on the list, it\'s not Vsattr'));
content.push(bullet('Creating Adv senses for Vs-used-adverbially-with-地 (寂然地 is Vs, not Adv)'));
content.push(bullet('Mis-tagging 放下 as Vsep — it\'s Vpt (下 is a resultative complement, not the O in V-O)'));
content.push(bullet('Separable verbs 唱歌, 走路, 打電話 are Vsep, NOT Vp or Vi'));

content.push(h2('2.2 Structure (word-level)'));
content.push(runs('Must be one of: ', { text: 'single | left-right | top-bottom | enclosing', bold: true }, '. For 3+ char words, use the dominant structure or left-right as default.'));

content.push(h2('2.3 Alignment (word and sense level)'));
content.push(runs('Must be one of: ', { text: 'full | partial | disputed', bold: true }, '.'));
content.push(bullet('full = matches TOCFL classification exactly'));
content.push(bullet('partial = editorial addition we made'));
content.push(bullet('disputed = we disagree with TOCFL\'s classification'));
content.push(h3('Disputed-POS workflow (two-sense pattern)'));
content.push(runs('When you disagree with TOCFL\'s POS for a sense, use the ', { text: 'two-sense workflow', bold: true }, ':'));
content.push(numItem('Mark the original TOCFL sense as alignment: "disputed" (keep the original POS; preserve the official record).', 'disputed_workflow'));
content.push(numItem('Create a new editorial sense with your corrected POS, marked alignment: "partial" and source: "editorial".', 'disputed_workflow'));
content.push(numItem('Both senses stay visible for learners — the disputed one shows the official classification, the partial one shows your editorial correction.', 'disputed_workflow'));
content.push(para('This preserves transparency: learners can see both the official TOCFL call and the editorial judgment.'));

content.push(h2('2.4 Source (sense level)'));
content.push(runs('Must be one of: ', { text: 'tocfl | editorial', bold: true }, '.'));
content.push(bullet('tocfl = from the official TOCFL wordlist'));
content.push(bullet('editorial = added by our team (including disputed-POS corrections)'));

content.push(h2('2.5 TOCFL / HSK levels'));
content.push(runs({ text: 'Do not override or invent TOCFL or HSK values. ', bold: true, color: 'C00000' }, 'Pass through what the source data provides. Skeleton batches carry these values pre-populated from official wordlists — keep them untouched. If a skeleton sense arrives with no TOCFL/HSK value, leave as null. Never derive a level from your own judgment of difficulty, frequency, or semantic similarity.'));

content.push(pageBreak());

// 3. Taxonomy
content.push(h1('3. Taxonomy Fields (Slug Discipline)'));
content.push(runs({ text: 'Universal rule: ', bold: true }, 'never invent a slug. Every slug value must come from the DB-authoritative frozen set. If you want to express a concept the set doesn\'t have, add a _flags note — don\'t invent a slug.'));
content.push(para('The validator (FrozenSets.php) pulls these sets live from the DB. The dry-run will reject any slug not in the set.'));

content.push(h2('3.1 Domains — frozen 54'));
content.push(para('Assign 1-4 domains per sense, ordered by relevance (position 1 = most relevant). Minimum 1. Don\'t stretch to fill — 1-2 well-chosen beats 4 vague.'));
content.push(...codeBlock([
  'animals · appearance · art · body · business · cognition · communication ·',
  'culture · daily-living · education · emotion · energy · environment ·',
  'family · food · health · history · housing · identity · language · law ·',
  'leisure · life · materials · media · medicine · money · movement · music ·',
  'nature · number-quantity · objects · perception · personal · personality ·',
  'philosophy · place · politics · properties · religion · safety · science ·',
  'social-relations · society · sound · space · sports · technology · time ·',
  'transportation · travel · values · weather · work',
]));

content.push(h3('Common slug-invention traps'));
content.push(simpleTable(
  ['Invented slug', 'Correct DB slug'],
  [
    ['material (singular)', 'materials (plural)'],
    ['sport (singular)', 'sports (plural)'],
    ['relationships', 'social-relations'],
    ['labor', 'work'],
    ['aesthetic', 'appearance'],
    ['daily-life', 'daily-living'],
    ['action', 'movement'],
    ['senses', 'perception'],
  ],
  [3600, 3600]
));

content.push(h2('3.2 Channel — frozen 5'));
content.push(simpleTable(
  ['Slug', 'Meaning'],
  [
    ['channel-balanced', 'Default — used in both speech and writing'],
    ['spoken-dominant', 'Primarily spoken, rare in writing'],
    ['written-dominant', 'Primarily written, rare in speech'],
    ['spoken-only', 'Effectively only spoken'],
    ['written-only', 'Effectively only written'],
  ],
  [2800, 6200]
));
content.push(runs({ text: 'Trap: ', bold: true, color: 'C00000' }, 'written-preferred / spoken-preferred do NOT exist. The suffix is -dominant.'));

content.push(h2('3.3 Connotation — frozen 6'));
content.push(bullet('positive (strongly positive)'));
content.push(bullet('positive-dominant (leans positive)'));
content.push(bullet('neutral (default for most words)'));
content.push(bullet('context-dependent (valence varies by context)'));
content.push(bullet('negative-dominant (leans negative)'));
content.push(bullet('negative (strongly negative)'));
content.push(runs({ text: 'Note: ', bold: true }, 'extreme intensity often co-varies with valence shift (see §4). Grade intensity and connotation independently.'));

content.push(h2('3.4 Register — frozen 6'));
content.push(para('literary · formal · standard (default) · informal · colloquial · slang'));
content.push(para('Multi-value array — a word can span multiple registers (e.g., ["standard", "informal"]).'));

content.push(h2('3.5 Dimension — settled 10 (MULTI-SELECT — use as many as genuinely apply)'));
content.push(runs(
  { text: 'Dimension is an array. ', bold: true },
  'Emit as ',
  { text: '"dimension": ["internal", "abstract"]', font: 'Courier New' },
  '. This is orthogonal to domain: ',
  { text: 'domain', italics: true },
  ' says "field of use"; ',
  { text: 'dimension', italics: true },
  ' says "what kind of thing is this concept." Most senses want 1–2 dimensions; complex concepts may want 3. Not every slot needs filling — 0 is valid for function words with no meaningful referent.'
));

content.push(simpleTable(
  ['Slug', 'Meaning', 'Examples'],
  [
    ['concrete', 'physical objects, tangible things', '桌子, 水, 書'],
    ['abstract', 'ideas, qualities, states with no physical form', '自由, 理論, 意義'],
    ['internal', 'psychological, emotional, mental states', '迷戀, 擔心, 相信, 快樂'],
    ['external', 'actions/states affecting the outer world', '跑, 建造, 吃'],
    ['spatial', 'position, direction, geometry', '上, 旁邊, 遠'],
    ['temporal', 'time, duration, sequence', '昨天, 漸漸, 早'],
    ['aspectual', 'grammatical aspect: ongoing, completed, habitual', '著, 了, 過'],
    ['grammatical', 'structural/function words with no lexical content', '的, 嗎'],
    ['pragmatic', 'speech acts, interjections, discourse particles', '唉, 喂, 哇, 吧'],
    ['dim-fluid', 'genuinely straddles multiple dimensions (rare)', '—'],
  ],
  [1800, 4400, 3200]
));

content.push(para('Worked examples:', { bold: true, spacing: { before: 160, after: 80 } }));
content.push(bullet('迷戀 (infatuated) → ["internal", "abstract"] — inner state + non-physical'));
content.push(bullet('桌子 (table) → ["concrete"] — physical object'));
content.push(bullet('跑 (to run) → ["external", "concrete"] — outer action on body'));
content.push(bullet('自由 (freedom) → ["abstract", "internal"] — idea + felt experience'));
content.push(bullet('昨天 (yesterday) → ["temporal"] — pure time reference'));
content.push(bullet('吧 (particle) → ["grammatical", "pragmatic"] — both structural + speech-act'));

content.push(h2('3.6 semantic_mode — RETIRED (2026-04-20)'));
content.push(runs(
  { text: 'Former 5-slot spectrum ', bold: true },
  '(literal-only / literal-dominant / balanced / metaphorical-dominant / metaphorical-only) ',
  { text: 'no longer exists. ', bold: true, color: 'C00000' },
  '4,574 of 4,634 tagged senses (98.6%) silently defaulted to literal-only; the field was dead on arrival. ',
  'The literal/figurative axis is now captured by two mechanisms already in the schema: (a) splitting senses when literal and metaphorical uses diverge (sense-split trigger #4 in §2), and (b) dimension tagging — a sense used metaphorically against an abstract target lands ',
  { text: 'abstract', font: 'Courier New' },
  ' + ',
  { text: 'internal', font: 'Courier New' },
  ', not ',
  { text: 'concrete', font: 'Courier New' },
  '. If you see ',
  { text: 'semantic_mode', font: 'Courier New' },
  ' in legacy template or ledger text, ignore it.'
));

content.push(h2('3.7 Sensitivity — frozen 5'));
content.push(para('general (default — safe for all learners) · mature · profanity · sexual · taboo'));
content.push(para('Most senses are general. Non-general values need explicit editorial justification.'));

content.push(pageBreak());

// 4. Intensity
content.push(h1('4. Intensity (Two-Stage Decision — NEW in v2.0)'));
content.push(runs({ text: 'This section replaces the v1.4 "Intensity 1-5 scale" guidance. ', italics: true }, 'Intensity requires a two-stage editorial decision; the old framing produced 5,599 silent defaults.'));

content.push(h2('4.1 What intensity measures'));
content.push(runs({ text: 'The strength of the quality the word denotes ', bold: true }, '— how much of the thing is present in what the word describes. Not how loudly the speaker is expressing. The internal semantic content.'));
content.push(bullet('喜歡 denotes mild positive inclination; 愛 denotes a committed emotional bond; 痴迷 denotes extreme fixation.'));
content.push(bullet('暗 denotes moderate lack of light; 漆黑 denotes extreme lack of light.'));
content.push(bullet('很 intensifies by a moderate amount; 極其 intensifies by an extreme amount.'));
content.push(para('The flower icons 🌸→🌺 map to how fully the quality has bloomed into its strength.'));

content.push(h2('4.2 Storage values — three distinct states'));
content.push(simpleTable(
  ['Value', 'Meaning'],
  [
    [{ text: '0', bold: true }, 'Not Applicable — explicit editorial decision that the word has no strength gradient'],
    [{ text: '1-5', bold: true }, 'Graded intensity on the strength scale'],
    [{ text: 'null', bold: true, color: 'C00000' }, 'Not yet enriched/assessed — pending, unfinished'],
  ],
  [1200, 7800]
));
content.push(runs({ text: 'Policy: every submitted sense MUST have 0 or 1-5. NEVER null. ', bold: true, color: 'C00000' }, 'null is a validator blocker — submission will fail import. null means "unfinished," and a finished enrichment is never null.'));

content.push(h2('4.3 The two-stage decision (v2.2 categorical rule — binding)'));
content.push(h3('STAGE 1 — Answered by POS category, not per-word judgment'));
content.push(runs('Predictability beats per-word accuracy. A learner who sees intensity reliably on stative verbs and never on action verbs learns one rule and trusts the chip. A learner who sees intensity sometimes on action verbs (報仇 graded but 安慰 not, or vice versa) has to learn a per-word rule and stops trusting the chip.'));
content.push(runs({ text: 'ONLY these POS categories get graded 1-5:', bold: true, color: '0F5132' }));
content.push(simpleTable(
  ['POS category', 'Examples', 'Why'],
  [
    [{ text: 'Vs', bold: true }, '暗, 好, 完美, 暴力 (Vs), 悲痛, 不安', 'qualities predicated of subject'],
    [{ text: 'Vst', bold: true }, '喜歡, 愛, 熱愛, 痴迷, 討厭, 重視', 'emotional/cognitive states directed at object'],
    [{ text: 'Degree adverbs (Adv subset)', bold: true }, '有點, 比較, 很, 非常, 極其', 'meaning IS intensity by nature'],
    [{ text: 'Abstract emotional/evaluative N', bold: true }, '熱情, 恐懼, 狂熱, 激情', 'denote graded inner states'],
    [{ text: 'IE (idioms with built-in weight)', bold: true }, '千辛萬苦, 感激涕零', 'idioms with semantic weight'],
  ],
  [2400, 3300, 3660]
));
content.push(runs({ text: 'EVERYTHING ELSE → intensity: 0 (Not Applicable). ', bold: true, color: 'C00000' }, 'Including:'));
content.push(bullet('All action verbs: V, Vi, Vpt, Vsep, Vp, Vpsep, Vssep'));
content.push(bullet('Modal/auxiliary verbs: Vaux, Vcomp'));
content.push(bullet('Attributive-only stative: Vsattr'));
content.push(bullet('All concrete nouns (桌子, 書, 學生, 玻璃, 病房, 報社)'));
content.push(bullet('All function words: Ptc, Conj, Prep, Det, Prn, Num, M, Aux'));
content.push(bullet('Non-degree adverbs: 已經, 正在, 暗中, 按時 (temporal/aspectual/grammatical, not degree)'));
content.push(runs({ text: 'NO → intensity: 0 + _flags note ', italics: true }, '(naming the category: "action verb" / "concrete noun" / "function word" / "non-degree adverb"). STOP.'));
content.push(runs({ text: 'YES → continue to Stage 2.', italics: true }));
content.push(h3('STAGE 2 — Grade 1-5 (only if Stage 1 = YES)'));

content.push(h2('4.4 Canonical family — positive attachment (Vst)'));
content.push(para('Valence stays positive across all 5 levels. Read top to bottom; feel the gradient:'));
content.push(simpleTable(
  ['Level', 'Word', 'Meaning'],
  [
    ['1', { text: '心動 / 有好感', bold: true }, 'first stirring, pre-like — "there\'s something here"'],
    ['2', { text: '喜歡', bold: true }, 'like — baseline positive affection, clearly present'],
    ['3', { text: '愛好', bold: true }, 'established fondness — sustained preference'],
    ['4', { text: '愛', bold: true }, 'love — committed emotional bond'],
    ['5', { text: '熱愛', bold: true }, 'passionate love — pronounced, enthusiastic'],
  ],
  [900, 2100, 6000]
));

content.push(h2('4.5 Secondary canonical family — pure intensifiers (Adv)'));
content.push(para('Neutral valence. These words ARE intensity by nature:'));
content.push(simpleTable(
  ['Level', 'Word', 'English'],
  [
    ['1', { text: '有點', bold: true }, 'a bit'],
    ['2', { text: '比較', bold: true }, 'comparatively'],
    ['3', { text: '很', bold: true }, 'very'],
    ['4', { text: '非常', bold: true }, 'extremely'],
    ['5', { text: '極其', bold: true }, 'utterly'],
  ],
  [900, 2100, 6000]
));

content.push(h2('4.6 Cross-POS calibration'));
content.push(para('Level 3 anchors across POS — all feel "present, clear, not extreme":'));
content.push(simpleTable(
  ['POS', 'Level 3 anchor'],
  [
    ['Vst', { text: '愛好', bold: true }],
    ['Adv', { text: '很', bold: true }],
    ['Vs', { text: '好', bold: true }],
    ['N', { text: '熱情', bold: true }],
    ['V', { text: '喊', bold: true }],
  ],
  [2000, 7000]
));
content.push(para('If your level 3 on one POS doesn\'t feel equivalent to these, recalibrate.'));

content.push(h2('4.7 Valence-shift pattern (important observation)'));
content.push(runs('Chinese vocabulary often exhibits ', { text: 'valence shift at extreme intensity', bold: true }, '. Words at level 5 on a strength axis often carry non-positive connotation. ', { text: 'Both fields are graded — independently:', bold: true }));
content.push(bullet('', { runs: [
  { text: '痴迷 (Vst)', bold: true }, { text: ' → ' }, { text: 'intensity 5', bold: true }, { text: ' (extreme attachment) AND connotation ' }, { text: 'negative-dominant', italics: true }, { text: ' (obsessive, pathological). Used naturally for 痴迷於賭博. Learner sees 🌺 5 + 🌧️ negative — reads as "extreme + concerning."' }
]}));
content.push(bullet('', { runs: [
  { text: '狂熱 (Vs)', bold: true }, { text: ' → ' }, { text: 'intensity 5', bold: true }, { text: ' AND connotation ' }, { text: 'context-dependent', italics: true }, { text: ' (fanatical — sometimes positive zeal, often critical).' }
]}));
content.push(bullet('', { runs: [
  { text: '熱愛 (Vst)', bold: true }, { text: ' → ' }, { text: 'intensity 5', bold: true }, { text: ' AND connotation ' }, { text: 'positive', italics: true }, { text: '. The pure-positive level 5.' }
]}));
content.push(runs({ text: 'Both 熱愛 and 痴迷 sit at intensity 5 on different valences. ', bold: true }, 'The canonical Like-Love family uses 熱愛 as the positive anchor; 痴迷 is graded the same intensity but tagged with its real connotation. Learners filter intensity 4-5 and find both — the connotation chip tells them which kind of 5.'));

content.push(h2('4.8 Worked examples — categorical rule applied'));
content.push(simpleTable(
  ['Sense', 'POS', 'Stage 1 answer', 'Intensity', 'Reasoning'],
  [
    [{ text: '痴迷', bold: true }, 'Vst', 'YES (stative)', { text: '5', bold: true }, 'Extreme fixation. Connotation = negative-dominant (independent field). Both grade independently — learner sees 🌺 5 + 🌧️ negative.'],
    [{ text: '熱愛', bold: true }, 'Vst', 'YES (stative)', { text: '5', bold: true }, 'Pronounced passion. Connotation = positive. The pure-positive 5.'],
    [{ text: '暴力', bold: true }, 'Vs', 'YES (stative)', { text: '4', bold: true }, 'Strong evaluative quality. Connotation = negative-dominant.'],
    [{ text: '暴力', bold: true }, 'N', 'NO (concrete N)', { text: '0', bold: true }, 'The N sense denotes a category of acts, not a graded quality.'],
    [{ text: '報仇', bold: true }, 'Vsep', 'NO (action verb)', { text: '0', bold: true }, 'Action denotes a category-distinct act. Acts feel weighty, but the verb itself doesn\'t denote a strength gradient.'],
    [{ text: '安慰', bold: true }, 'Vpt', 'NO (action verb)', { text: '0', bold: true }, 'Action category. Particular instances vary in intensity by context, but the word doesn\'t.'],
    [{ text: '煎/炸/蒸/燉', bold: true }, 'Vpt', 'NO (action verbs)', { text: '0', bold: true }, 'Cooking methods are category-distinct (heat source, technique), not strength variations.'],
    [{ text: '很', bold: true }, 'Adv (degree)', 'YES (degree adverb)', { text: '3', bold: true }, 'Pure intensifier — meaning IS intensity.'],
    [{ text: '已經', bold: true }, 'Adv (temporal)', 'NO (non-degree)', { text: '0', bold: true }, 'Temporal adverb, not degree.'],
    [{ text: '熱情', bold: true }, 'N (abstract emotional)', 'YES', { text: '3', bold: true }, 'Graded inner state.'],
    [{ text: '桌子', bold: true }, 'N (concrete)', 'NO', { text: '0', bold: true }, 'Concrete object, no gradient.'],
  ],
  [1100, 1200, 1700, 900, 4460]
));

content.push(h2('4.10 Intensity checklist'));
content.push(runs({ text: 'Stage 1 (always run): ', bold: true }, 'Does this sense denote a quality with a strength gradient?'));
content.push(subBullet('NO → intensity: 0 + _flags note, STOP.'));
content.push(runs({ text: 'Stage 2 (only if Stage 1 = YES):', bold: true }));
content.push(subBullet('At L4 calibration, is it first-stirring (1) / baseline-present (2) / moderate (3) / pronounced (4) / extreme (5)?'));
content.push(subBullet('Would my chosen level match equivalent-force words in other POS (愛好/很/好/熱情/喊 at 3)?'));
content.push(subBullet('If extreme (5): is this word also valence-shifted? (Grade intensity and connotation independently.)'));
content.push(subBullet('If 1, did I really consider the scale or did I default?'));

content.push(pageBreak());

// 5. Content Quality
content.push(h1('5. Content Quality'));

content.push(h2('5.1 Definitions'));
content.push(bullet('EN + ZH-TW required for every sense.'));
content.push(bullet('ZH must be pure Chinese — no English words.'));
content.push(bullet('No POS information in definitions.'));
content.push(bullet('Do not capitalize first word of EN definitions.'));

content.push(h3('5.1a Definition depth at L4+ (critical)'));
content.push(runs('At TOCFL Level 4 and higher, definitions must ', { text: 'EXPLAIN', bold: true }, ', not just gloss-stack. A learner at this level needs context the English gloss alone can\'t give: target, mechanism, duration, register, boundary condition, or usage frame.'));
content.push(runs({ text: 'Gloss-stacking (REJECT):', bold: true, color: 'C00000' }));
content.push(bullet('愛好 N: "愛好；喜歡做的事；興趣" ← three near-synonyms with semicolons'));
content.push(bullet('案子 N: "案子；案件；事情" ← same pattern'));
content.push(bullet('安慰: "安慰；讓人心裡比較好受" ← starts with the headword, thin'));
content.push(runs({ text: 'Proper L4+ definitions (ACCEPT):', bold: true, color: '0F5132' }));
content.push(bullet('愛好 N: "長期喜歡並經常從事的活動或消遣，帶有個人選擇和持續投入的意味"'));
content.push(bullet('安慰 Vpt: "用言語、行動或陪伴讓處於難過、擔心或痛苦中的人心裡好受一些"'));
content.push(bullet('保障 Vpt: "透過法律、制度或行動來確保權利、安全或福利不受侵害"'));

content.push(h3('5.1b Self-check for each ZH definition'));
content.push(numItem('Does it start with the headword character? → rewrite', 'def_selfcheck'));
content.push(numItem('Is it three or more semicolon-separated near-synonyms? → rewrite', 'def_selfcheck'));
content.push(numItem('Under 15 characters with no verb/predicate structure? → flag for review', 'def_selfcheck'));
content.push(numItem('Could a learner who knew the English gloss write this ZH? → if yes, rewrite', 'def_selfcheck'));

content.push(h2('5.2 Examples'));
content.push(bullet('Minimum 2 per sense. The target word MUST appear in each example.'));
content.push(bullet('Vsep: split forms OK (辦了案, 結了婚).'));
content.push(bullet('Vcomp: show complement attached to a verb (學會, 壞掉了, 回不去).'));
content.push(bullet('EN translation required for every example.'));
content.push(runs({ text: 'NEVER write meta-commentary', bold: true, color: 'C00000' }, ' — banned patterns:'));
content.push(subBullet('"很多詞都和X有關"'));
content.push(subBullet('"這個詞..."'));
content.push(subBullet('"作動詞時..."'));
content.push(subBullet('"這個字有..."'));
content.push(para('Examples demonstrate usage, not describe the word.'));

content.push(h3('5.2a Root-form / bound-morpheme example hygiene'));
content.push(para('For characters that live primarily in compounds (bound morphemes like 癌, 案, 保, 寶, 報, 棒), examples must reflect real usage, not engineered standalone uses.'));
content.push(bullet('If predominantly bound, at least one example should show the character in compound position (肺癌, 本案, 球棒, 警棒, 電報).'));
content.push(bullet('A standalone example must be natural modern Chinese, not engineered to showcase the headword.'));
content.push(bullet('If 3 proposed examples all feel stilted, the word is bound — use compound-position examples.'));
content.push(bullet('Pair with a _flags note: "bound morpheme — examples use compound-position form to reflect natural usage."'));
content.push(runs({ text: 'Examples of the right move:', bold: true }));
content.push(bullet('癌: "醫生說他得的是肺癌。" (肺癌 compound — natural)'));
content.push(bullet('案: "本案目前還在調查中。" (本案 compound — natural)'));
content.push(bullet('棒 N: "他買了一支新的棒球棒。" (棒球棒 compound — replaced "一根棒" which was engineered)'));

content.push(h2('5.3 Formulas (bilingual)'));
content.push(bullet('Provide formula_en AND formula_zh for every sense.'));
content.push(bullet('Structural, not narrative. V + [Object] or 把 [Noun] 當作 [Noun], not a sentence.'));
content.push(bullet('The Chinese word itself stays in Chinese in BOTH versions.'));
content.push(bullet('formula_en: slot labels in [] use English — 把 [Noun] 當作 [Noun]'));
content.push(bullet('formula_zh: slot labels in [] use Chinese — 把 [名詞] 當作 [名詞]'));
content.push(bullet('Grammar and word order are identical; only the [] labels change.'));
content.push(bullet('Target word MUST appear in its own formula.'));
content.push(bullet('Vsep: show both joined and split forms.'));
content.push(runs({ text: 'If you find yourself listing 4 object types in a slot label, that content belongs in usage_note_zh', italics: true }, ' — the formula should stay structural.'));

content.push(h2('5.4 Usage notes (bilingual)'));
content.push(bullet('Provide usage_note_en AND usage_note_zh for every sense.'));
content.push(bullet('EN version: natural English for English-speaking learners.'));
content.push(bullet('ZH version: natural Chinese for immersion-mode learners.'));
content.push(bullet('Write each independently — do NOT translate word-for-word.'));

content.push(h2('5.5 Learner traps (bilingual)'));
content.push(bullet('Provide learner_traps_en AND learner_traps_zh for every sense.'));
content.push(bullet('A trap hidden in the language the learner is LEARNING is useless.'));
content.push(bullet('Write each independently — natural, not translated.'));

content.push(h2('5.6 Collocations'));
content.push(bullet('Minimum 2 per sense — natural phrases.'));
content.push(bullet('Do NOT confuse collocations with formula fragments or chopped substrings.'));
content.push(bullet('Single-word or 一個+headword patterns are too thin to count.'));

content.push(pageBreak());

// 6. Relations
content.push(h1('6. Relations — The Hardest Editorial Layer'));

content.push(h2('6.1 Decision ladder (apply for EVERY relation)'));
content.push(numItem('Clear semantic opposite on a shared dimension? → antonym', 'decision_ladder'));
content.push(numItem('Same field but not substitutable? → contrast', 'decision_ladder'));
content.push(numItem('Nearly interchangeable across contexts? → synonym_close', 'decision_ladder'));
content.push(numItem('Substitutable in some contexts with different nuance? → synonym_related', 'decision_ladder'));
content.push(numItem('None of the above? → DO NOT FORCE. Flag instead.', 'decision_ladder'));

content.push(h2('6.2 Contrast-or-trap test (CRITICAL)'));
content.push(runs({ text: 'Before classifying any pair as contrast, write the SHARED DIMENSION in ≤5 words. ', bold: true }, 'If you cannot, it is not a contrast — it belongs in learner_traps.'));

content.push(h3('Clean contrasts (pass the test)'));
content.push(simpleTable(
  ['Pair', 'Shared dimension'],
  [
    ['暗 ↔ 亮', 'brightness level ✓'],
    ['安慰 ↔ 責備', 'response to another\'s state ✓'],
    ['保存 ↔ 保留', 'mode of preservation ✓'],
    ['報仇 ↔ 報答', 'reciprocation type ✓'],
    ['包裹 ↔ 信件', 'postal item type ✓'],
    ['半路 ↔ 全程', 'journey coverage ✓'],
  ],
  [3000, 6000]
));

content.push(h3('Failed the test (rejected in past batches)'));
content.push(simpleTable(
  ['Pair', 'Why it fails'],
  [
    ['罷工 ↔ 抗議', 'strike is a TYPE OF protest, not polarity'],
    ['癌症 ↔ 腫瘤', 'cancer is-a tumor-related condition'],
    ['安慰 ↔ 鼓勵', 'both supportive, different target conditions'],
    ['愛人 ↔ 情人', 'regional/role distinction, not polarity'],
    ['保障 ↔ 威脅', 'rhetorical polarity, not lexical'],
    ['扮演 ↔ 導演', 'same performance domain, not contrasting acts'],
    ['半數 ↔ 多數', '"statistical quantity" is schematic, not lexical'],
  ],
  [3000, 6000]
));
content.push(runs({ text: 'If the pair fails the test, put the distinction in learner_traps_zh / learner_traps_en ', bold: true }, 'where it teaches the nuance. DO NOT stuff traps into the relation layer.'));

content.push(h2('6.3 Flag-over-fake (§9 coverage rule)'));
content.push(runs({ text: '1 clean relation + _flags note > 2 relations where one is forced.', bold: true }, ' Never pad the relation layer with weak edges to hit a count.'));
content.push(para('A bound-root form (癌, 案, 保, 寶, 報) with one clean compound partner + a flag is correct. Inventing a second synonym to reach 2 is wrong.'));
content.push(para('When you §9-flag, the flag should name the reason:'));
content.push(bullet('"bound morpheme root — thin standalone neighborhood"'));
content.push(bullet('"classifier with very narrow usage"'));
content.push(bullet('"sentence-final particle — limited relational field"'));
content.push(bullet('"idiomatic expression — no true lexical neighbors"'));

content.push(h2('6.4 Category / sequence / hypernym — NOT synonymy'));
content.push(runs('When no true lexical relation exists, downgrading to a ', { text: 'category relation', bold: true }, ' is NOT synonymy. These fail the substitution test:'));
content.push(bullet('', { runs: [
  { text: 'Sequence neighbors: ', bold: true }, { text: '甲/乙/丙/丁 are ordering members of 天干, not synonyms. 丙 doesn\'t mean the same thing as 甲.' }
]}));
content.push(bullet('', { runs: [
  { text: 'Hypernyms: ', bold: true }, { text: '菠菜 is a kind of 青菜. Category inclusion, not meaning-equivalence.' }
]}));
content.push(bullet('', { runs: [
  { text: 'Institution-vs-activity: ', bold: true }, { text: '補習 (activity) ≠ 補習班 (institution). Different category levels.' }
]}));
content.push(bullet('', { runs: [
  { text: 'Bound roots: ', bold: true }, { text: '軍 (bound root) is too broad to be synonym_related of 部隊.' }
]}));
content.push(runs({ text: 'Apply the substitution test: ', bold: true }, 'can X replace Y in 3 natural sentences without meaning loss? If no, it\'s not synonym_related. When only category candidates exist, keep the §9 flag.'));

content.push(h2('6.5 Reciprocity (for in-batch pairs)'));
content.push(runs('If you list Y as a relation of X, and Y is another word in the same batch, ', { text: 'Y must list X with the same relation type ', bold: true }, 'on its own sense.'));
content.push(bullet('X says Y is synonym_related → Y must have X as synonym_related'));
content.push(bullet('X says Y is antonym → Y must have X as antonym'));
content.push(bullet('X says Y is contrast → Y must have X as contrast'));
content.push(bullet('X says Y is synonym_close → Y must have X as synonym_close'));
content.push(para('Asymmetric pairings are rejected at audit. Cross-batch asymmetries (Y not in current batch) are flagged, not forced.'));

content.push(h2('6.6 Balance — targets, not hard gates'));
content.push(simpleTable(
  ['Type', 'Target', 'Watch for'],
  [
    ['synonym_close', '2-8%', 'Overuse = not distinguishing close from related'],
    ['synonym_related', '35-50%', '>50% = catch-all drift'],
    ['antonym', '5-15%', '<5% = missing obvious pairs'],
    ['contrast', '30-45%', '<25% = underused'],
  ],
  [2400, 1400, 5200]
));
content.push(runs({ text: 'Hygiene wins over balance. ', bold: true }, 'If pruning weak relations pushes the batch off-band, that\'s structural drift to document, not cause to restore the weak relations.'));

content.push(h2('6.7 Middle-ground demotion trap'));
content.push(runs('When a reviewer flags a weak contrast, ', { text: 'remove or restore cleanly ', bold: true }, '— demoting to synonym_related can make the network blurrier, not cleaner.'));
content.push(para('Example: 哎呀/哎喲 ↔ 唉 was demoted from contrast to synonym_related, which made the family feel substitutable when it is not. The right move is either (a) articulate a sharper shared dimension and keep as contrast, or (b) remove the pair entirely.'));

content.push(h2('6.8 Reviewer-prescribed slug maps — verify against DB first'));
content.push(runs({ text: 'A reviewer\'s authority does not override the DB\'s authority. ', bold: true }, 'When a reviewer\'s feedback prescribes a slug remapping, cross-check the prescribed target against FrozenSets::domains() (or equivalent) before applying across a batch.'));
content.push(para('This error pattern was the cause of 148 slug failures across L4 batches 02-09 in early April 2026 — the reviewer prescribed a map that didn\'t match the DB, and it propagated unchecked.'));

content.push(pageBreak());

// 7. Sense architecture
content.push(h1('7. Sense Architecture'));
content.push(h2('7.1 Six sense-split triggers'));
content.push(runs('Create a ', { text: 'separate sense ', bold: true }, 'whenever ANY of these is true:'));
content.push(numItem('Different POS — 愛好 is both N (hobby) and Vst (to love/be keen on). Two senses. 保障 is both Vpt and N. Two senses.', 'split_triggers'));
content.push(numItem('Different pinyin/reading — 行 (xíng) = to walk/OK, 行 (háng) = row/profession. Two senses.', 'split_triggers'));
content.push(numItem('Different domain of use — 熬 (cooking: simmer) and 熬 (enduring: persist through hardship) — different domains (food vs emotion), different objects, different register. Two senses.', 'split_triggers'));
content.push(numItem('Literal ↔ metaphorical split with distinct usage patterns — if both appear in real text with different collocations, split them.', 'split_triggers'));
content.push(numItem('The usage_note or learner_traps starts saying "also used as..." — that phrase is a self-signal that you compressed two senses into one. Split them.', 'split_triggers'));
content.push(numItem('Distinct syntactic behavior — 拜拜 can be Vi (farewell "bye-bye") or Vsep (to worship at a temple, 拜拜神明). Two senses.', 'split_triggers'));
content.push(runs({ text: 'If in doubt, split. ', bold: true }, 'A sense can always be merged later; a compressed entry often ships to learners before anyone notices the conflation.'));

content.push(h2('7.2 Variant pairs'));
content.push(para('Orthographic variants (e.g., 佈告 / 布告, 佈告欄 / 布告欄) are legitimate as separate entries linked with reciprocal synonym_related. But each variant entry must stand on its own — differentiated definitions, independent examples, and its own relations. Don\'t let the variant cross-links pad the graph; that\'s variant-inflation, which 惠明 flags at audit.'));

// 8. Lessons ledger
content.push(h1('8. Lessons Ledger — Named Anti-Patterns'));
content.push(runs('Every pattern below has been caught in 2+ batches. ', { text: 'Check your output against each before submitting. ', bold: true }, 'The full versioned list lives in LessonsLedger.php and flows into the 師父 prompt automatically.'));
content.push(simpleTable(
  ['#', 'Category', 'Pattern'],
  [
    ['1', 'Slug invention', 'Domain slug pluralization / variant (material vs materials)'],
    ['2', 'Slug invention', 'Slug remapping to synonyms not in frozen set (relationships vs social-relations)'],
    ['3', 'Slug invention', 'Channel suffix invention (written-preferred doesn\'t exist; written-dominant does)'],
    ['4', 'Relation typing', 'Type-of relationship typed as contrast (罷工 ↔ 抗議)'],
    ['5', 'Relation typing', 'Middle-ground demotion that blurs (contrast → related)'],
    ['6', 'Definition quality', 'ZH definition as semicolon-separated synonym list'],
    ['7', 'Example quality', 'Stilted standalone examples for bound morphemes'],
    ['8', 'Example quality', 'Meta-commentary in examples ("很多詞都和X有關")'],
    ['9', 'Language isolation', 'English text bleeding into ZH note fields'],
    ['10', 'Sense architecture', 'Two senses compressed into one ("also used as...")'],
    ['11', 'Reviewer process', 'Reviewer-prescribed slug maps applied without DB verification'],
    ['12', 'Relation typing', 'Category / sequence / hypernym forced into synonym_related (甲/乙/丁 for 丙)'],
    ['13', 'Definition quality', 'Intensity defaulted to 1; Not Applicable not used; null left on enriched senses'],
  ],
  [600, 2200, 6200]
));

content.push(pageBreak());

// 9. Pre-submission checklist
content.push(h1('9. Pre-Submission Checklist'));
content.push(para('Before submitting any batch, verify every sense:'));

content.push(h2('Mechanical (automated via --dry-run)'));
content.push(bullet('☐ All slug values copied exactly from frozen sets (domain, channel, connotation, register, dimension, sensitivity, POS)'));
content.push(bullet('☐ dimension is emitted as an ARRAY (even when one value) — multi-select'));
content.push(bullet('☐ structure, alignment, source, tocfl, hsk values in their valid sets'));
content.push(bullet('☐ Intensity is 0-5 (never null — null is a validator blocker)'));
content.push(bullet('☐ Valency is correct integer for verb POS, null for non-verbs'));
content.push(bullet('☐ Relations are string arrays with valid keys'));
content.push(bullet('☐ 2+ examples per sense, target word appears in each'));
content.push(bullet('☐ 2+ collocations per sense'));
content.push(bullet('☐ All 6 bilingual note fields filled'));
content.push(bullet('☐ EN + ZH-TW definitions both present, ZH is pure Chinese'));

content.push(h2('Editorial (manual)'));
content.push(bullet('☐ Intensity — every sense has an explicit 0 or 1-5 decision (not a default-1)'));
content.push(bullet('☐ Domains — 1-4 assigned, position 1 is the most relevant'));
content.push(bullet('☐ Relations — every edge passes the decision ladder; no padding'));
content.push(bullet('☐ Every contrast — shared dimension articulable in ≤5 words'));
content.push(bullet('☐ L4+ definitions — explain, not gloss-stack'));
content.push(bullet('☐ Examples — natural usage, no meta-commentary'));
content.push(bullet('☐ Bound morphemes — at least one compound-position example'));
content.push(bullet('☐ Multi-POS words — split into separate senses'));
content.push(bullet('☐ In-batch relations — reciprocal (both sides point back)'));
content.push(bullet('☐ _flags notes added wherever a decision needs reviewer attention'));

content.push(h2('Post-automated-check'));
content.push(bullet('☐ php artisan words:import <file> --dry-run returns Validation passed ✓'));

// 10. Where to verify
content.push(h1('10. Where to Verify'));
content.push(simpleTable(
  ['Resource', 'Location'],
  [
    ['Frozen slug sets', 'App\\Services\\Enrichment\\FrozenSets.php — live from DB'],
    ['Validator (23 rules)', 'App\\Services\\Enrichment\\Validators\\StructuralValidator.php'],
    ['Lessons ledger (13 entries)', 'App\\Services\\Enrichment\\LessonsLedger.php'],
    ['師父 enrichment prompt', 'App\\Services\\ShifuWordEnricher.php'],
    ['Import template (v2.3)', 'database/templates/word-import-template.json'],
    ['POS Reference v2.1', 'project_pos_reference_v2.1.md (memory)'],
    ['Intensity Specification', 'project_intensity_specification.md (memory)'],
    ['This guide', 'project_enrichment_guide_v2.md (memory)'],
  ],
  [3500, 5500]
));
content.push(runs({ text: 'Run the dry-run before submission. Always.', bold: true, color: '1F4E79' }));

// Build doc
const doc = new Document({
  creator: '光流 (Claude)',
  title: '流動 Living Lexicon — Enrichment Quality Guide v2.3',
  styles: {
    default: { document: { run: { font: FONT, size: 22 } } },
    paragraphStyles: [
      { id: 'Heading1', name: 'Heading 1', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 32, bold: true, font: FONT, color: BLACK },
        paragraph: { spacing: { before: 360, after: 180 }, outlineLevel: 0 } },
      { id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 28, bold: true, font: FONT, color: BLACK },
        paragraph: { spacing: { before: 280, after: 140 }, outlineLevel: 1 } },
      { id: 'Heading3', name: 'Heading 3', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 24, bold: true, font: FONT, color: BLACK },
        paragraph: { spacing: { before: 220, after: 120 }, outlineLevel: 2 } },
    ]
  },
  numbering: {
    config: [
      { reference: 'bullets',
        levels: [
          { level: 0, format: LevelFormat.BULLET, text: '•', alignment: AlignmentType.LEFT,
            style: { paragraph: { indent: { left: 720, hanging: 360 } } } },
          { level: 1, format: LevelFormat.BULLET, text: '◦', alignment: AlignmentType.LEFT,
            style: { paragraph: { indent: { left: 1080, hanging: 360 } } } },
        ] },
      // One independent numbered-list reference per list-id so counters don't accumulate across sections.
      ...[..._registeredLists].map(listId => ({
        reference: 'numbered_' + listId,
        levels: [
          { level: 0, format: LevelFormat.DECIMAL, text: '%1.', alignment: AlignmentType.LEFT,
            style: { paragraph: { indent: { left: 720, hanging: 360 } } } },
        ]
      })),
    ]
  },
  sections: [{
    properties: {
      page: {
        size: { width: 12240, height: 15840 },
        margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 },
      },
    },
    headers: {
      default: new Header({ children: [new Paragraph({
        children: [new TextRun({ text: '流動 Living Lexicon — Enrichment Quality Guide v2.3', font: FONT, size: 18, color: GRAY })],
        alignment: AlignmentType.RIGHT,
      })] })
    },
    footers: {
      default: new Footer({ children: [new Paragraph({
        children: [
          new TextRun({ text: 'Page ', font: FONT, size: 18, color: GRAY }),
          new TextRun({ children: [PageNumber.CURRENT], font: FONT, size: 18, color: GRAY }),
        ],
        alignment: AlignmentType.CENTER,
      })] })
    },
    children: content,
  }]
});

Packer.toBuffer(doc).then(buffer => {
  const outPath = '/Users/chuluoyi/Documents/華語/planning/流動_Enrichment_Quality_Guide_v2.3.docx';
  fs.writeFileSync(outPath, buffer);
  console.log('Wrote ' + outPath + ' (' + buffer.length + ' bytes)');
});
