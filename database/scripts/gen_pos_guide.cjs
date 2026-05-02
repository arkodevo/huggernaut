/*
 * Generate 流動 Living Lexicon — POS Reference Guide (canonical .docx).
 *
 * Output: /Users/chuluoyi/Documents/華語/planning/流動_POS_Reference_Guide_vX.Y.docx
 *
 * Run:  NODE_PATH=/opt/homebrew/lib/node_modules node database/scripts/gen_pos_guide.cjs
 *
 * Companion to the Enrichment Quality Guide. Travels with project_pos_reference_v2.1.md
 * (memory companion — file path retained for stability across version bumps).
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
const YELLOW_BG = 'FFF2CC';
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
    children: [new TextRun({ text: 'POS Reference Guide v2.4', font: FONT, size: 44, bold: true, color: '1F4E79' })],
    alignment: AlignmentType.CENTER,
    spacing: { after: 600 },
  }),
  new Paragraph({
    children: [new TextRun({ text: '光流 + 惠明 + 絡一 — 2026-04-26', font: FONT, size: 26, color: GRAY })],
    alignment: AlignmentType.CENTER,
    spacing: { after: 720 },
  }),
  new Paragraph({
    children: [new TextRun({ text: 'For: 澄言 · 惠明 · 光流 · 絡一. Companion: Enrichment Quality Guide v2.5 + project_state_verb_lens.md (philosophical lens framework).', font: FONT, size: 22, italics: true, color: GRAY })],
    alignment: AlignmentType.CENTER,
    spacing: { after: 600 },
  }),
);

// Version History
content.push(h2('Version History'));
content.push(simpleTable(
  ['Version', 'Changes'],
  [
    ['v1.1', 'Initial reference. 3×3 verb grid, forced test order, 在/著 triangulation, resultative complement rule.'],
    ['v1.2', 'Trap words appendix + known reviewer tendencies.'],
    ['v1.3', 'Stative subcategories clarified (Vsattr CLOSED, Vspred predicative-only).'],
    ['v1.4', 'Vsep/Vpsep/Vssep separable categories + Vcomp resultative complement reference.'],
    ['v1.5', 'Nominalization filter, causative/inchoative alternation, formula guidelines.'],
    ['v2.0', 'Refined for v1.5 lexicon spec (action/process/state × transitive/intransitive/separable grid as authoritative). Disagreement protocol formalized.'],
    ['v2.1', 'Valency mapping extended to all verb POS. 澄言 batch-11 calibration on Vsattr/Vcomp/Vsep/Vpsep/Vssep.'],
    [
      'v2.2',
      '§0 Master-Perspective Principle added (codifies investigation-before-dispute discipline learned across 11 disputes that uniformly failed lens recheck on 2026-04-26). §7 Stative Verbs broadened — 很-test rules out gradable Vs membership but does NOT rule out Vst membership for inhabited-mode states (忍, 愛, 信, 知, 教 jiao4, 算了, 受得了). §14a Determiner broader scope added — Chinese-grammar functional definition (any noun-phrase modifier that specifies/quantifies/identifies) vs English-default pre-noun-only reading; 氣 as canonical category-determining suffix. §14b Prn vs N distinction added — master reserves Prn for personal/demonstrative pronouns; quantifier-collectives (大家/人人/各位) stay N.',
    ],
    [
      'v2.3',
      '§6 Resultative Complement Rule split into two distinct diagnostic families: §6.1 true resultative complements (V+結 → Vpt/Vp), §6.2 potential complements (V+得/不+結 → typically Vst, capacity-state). The 了 in 受得了/忍得了/等不了 is the verb 了 (liao3 — manage/handle through to completion), not the perfective 了 (le).',
    ],
    [
      { text: 'v2.4', bold: true, shade: YELLOW_BG },
      { text: 'POS slug ontology corrected: §14c CE = Complement Expression (V+得/不+結 family — 受得了, 想不到, 找得到, 看不出, 怪不得). Currently the cohort is classified under Vst per §6.2 (capacity-state semantics); CE is reserved for any future structural classification need. §14d IE = Idiomatic Expression (BROAD scope) — covers all idiomatic content including 成語 (chengyu / four-character classical idioms), 慣用語 (colloquial fixed phrases), set expressions, proverbs. No separate CY/Chengyu slug — chengyu sits in IE as a sub-type. The previous DB labeling (CE = Chengyu) was a slug-meaning error caught 2026-04-26; it had encouraged Chengyan to use CE for "Complement Expression" (slug invention by abbreviation false-friend) and was structurally wrong because chengyu is one form of idiom, properly under IE. Lessons ledger entry #18 names the slug-meaning verification discipline.', shade: YELLOW_BG },
    ],
  ],
  [1560, 7800]
));

content.push(pageBreak());

// 0. Master-Perspective Principle (NEW v2.2)
content.push(h1('0. Master-Perspective Principle (NEW v2.2 — read first)'));
content.push(runs({ text: 'When master classifies in a way that diverges from English-trained intuition, the first response is investigation, not dispute.', bold: true }));
content.push(para('Ask in order:'));
content.push(numItem('What kind of reality is the language treating this word as expressing? State / process / action / condition / substrate / field / relation / force?', 'master_persp'));
content.push(numItem('What does the master family-consistency say? Does the POS appear with consistent label across the word\'s compound family? Family-uniform classifications are strong signals master is honoring an ontological category.', 'master_persp'));
content.push(numItem('Is our editorial position rooted in actual Chinese-grammar reasoning, or in English-linguistics-trained intuition translated to a mechanical test (the 很-test, the 在-test, the resultative-complement rule applied without ontological context)?', 'master_persp'));

content.push(runs('The structural diagnostic tests in this guide are ', { text: 'calibrated for clear cases', bold: true }, '. Applied to philosophically saturated words, fixed expressions, or substrate concepts (氣, 心, 性, 道, 忍, 愛, 信, 知), they may give false negatives. ', { text: 'When tests conflict with master family-consistency or framework reasoning, family wins.', bold: true }));

content.push(runs({ text: 'Dispute only when investigation finds no framework reason ', bold: true }, 'AND we have a Chinese-grammar-rooted alternative analysis. Default is trust.'));

content.push(h2('Failure mode this prevents'));
content.push(runs({ text: 'Stacked English-trained reads. ', bold: true }, 'When 光流 + 惠明 + 澄言 converge on the same POS dispute, we may be stacking three intuitions rooted in the same lens bias — sanrenxing without cross-framework safety. The check is the framework investigation, not another reviewer.'));
content.push(runs({ text: 'Lesson surfaced 2026-04-26: ', bold: true }, '11 of 11 active disputes from L3 batches 01-02 failed lens recheck (忍/氣 Det/教/嚇/請假/扔/躺/提高/受得了/算了/起 N/人人). The bias was complete and consistent across all three reviewers. The fix is structural: investigate first, dispute only with evidence.'));

content.push(runs('See ', { text: 'project_state_verb_lens.md ', italics: true, bold: true }, 'for the full philosophical-lens framework — three keys (state-as-inhabited / substrate concepts / relational causality) all the same principle.'));

content.push(pageBreak());

// 1. Verb System
content.push(h1('1. Verb System — Full 3x3 Grid'));
content.push(simpleTable(
  ['', 'Transitive', 'Intransitive', 'Separable'],
  [
    [{ text: 'ACTION', bold: true, shade: GREY_BG }, 'V — 買, 吃, 編輯', 'Vi — 哭, 坐, 飛行', 'Vsep — 辦案→辦了案'],
    [{ text: 'PROCESS', bold: true, shade: GREY_BG }, 'Vpt — 完成, 打破, 明白', 'Vp — 死, 崩潰, 退休', 'Vpsep — 結婚→結了婚'],
    [{ text: 'STATE', bold: true, shade: GREY_BG }, 'Vst — 喜歡, 知道', 'Vs — 好, 貴, 悲觀', 'Vssep — 擔心→擔了心'],
  ],
  [1500, 2500, 2500, 2500]
));

content.push(h2('Special Categories'));
content.push(bullet('Vsattr — State verb, attributive only. CLOSED TOCFL category.'));
content.push(bullet('Vspred — State verb, predicative only. Includes 嚇 (state-flash of being-startled).'));
content.push(bullet('Vaux — Modal auxiliary (能, 可以, 會).'));
content.push(bullet('Vcomp — Verbal complement (得, 不得, 到, 住, 掉…).'));

content.push(h2('Critical Warnings'));
content.push(bullet('V is a valid sense-level tag — the default transitive action verb, NOT a grouping label.'));
content.push(bullet('Vpt = Process Verb, TRANSITIVE. The "t" means TRANSITIVE, not telic.'));
content.push(bullet('Vp = Process Verb, INTRANSITIVE. Subject undergoes the change.'));
content.push(bullet('Vi is NOT "anything without an object." Vi = intransitive ACTION only. Intransitive change-of-state = Vp.'));
content.push(bullet('Object omission is NOT intransitive. A verb is transitive if it CAN take a direct object.'));

content.push(h2('Valency by POS'));
content.push(simpleTable(
  ['Valency', 'POS types', 'Reasoning'],
  [
    ['0', { text: 'Vi, Vp, Vs, Vspred, Vsattr, Vcomp, Vssep', bold: true }, 'Intransitive. Vsattr no argument structure. Vcomp = complement morpheme. Vssep = stative-separable.'],
    ['1', { text: 'V, Vpt, Vst, Vaux, Vsep, Vpsep', bold: true }, 'Transitive. Vsep / Vpsep count separable O. Vaux takes following VP as complement.'],
    ['2', { text: 'ditransitive (rare)', bold: true }, 'e.g. 給 — give (someone) (something).'],
    ['null', { text: 'non-verbs', bold: true }, 'N, M, Adv, Prep, Conj, Ptc, Det, Prn, Num, IE, Ph, CE, Intj, Aux.'],
  ],
  [900, 3600, 4500]
));

// 2. Red Flags
content.push(h1('2. Red Flags — Common Drift Errors'));
content.push(bullet('Do not classify by English gloss.'));
content.push(bullet('Do not default transitive verbs to V — ask: does morphology encode a result? If yes → Vpt.'));
content.push(bullet('Do not treat +在 as proof of Action (durative process verbs also accept 在).'));
content.push(bullet('Do not treat object omission as intransitive.'));
content.push(bullet('Do not override morphology with intuition.'));
content.push(bullet('Do not run tests on extended/poetic/coerced readings — test core lexical sense only.'));
content.push(bullet('Do not silently improvise when rubric and instinct diverge — rubric wins.'));
content.push(runs({ text: 'Do not dispute master\'s POS without first investigating the framework reason. ', bold: true }, '(See §0.)'));

// 3. Forced Test Order
content.push(h1('3. Forced Test Order — Run Every Step in Sequence'));
content.push(numItem('Separable? Can you insert 了/過/modifier between V and O? YES → Vsep/Vpsep/Vssep', 'force_order'));
content.push(numItem('Resultative complement morphology? 成/到/出/上/開/掉/etc. YES → Vpt (takes object) or Vp (intransitive). STOP.', 'force_order'));
content.push(numItem('著-test? V+著+[object/target/complement]. Natural → continue. Marginal → inconclusive. Incompatible → strong evidence for Process.', 'force_order'));
content.push(numItem('在-test? YES → non-stative → Step 5. NO → Stative → Step 6.', 'force_order'));
content.push(numItem('Threshold crossing? YES → Process (Vpt/Vp). NO → Action (V/Vi).', 'force_order'));
content.push(numItem('Stative subcategories: Takes object → Vst. 很-test passes, no object → Vs (gradable). 很-test fails — see §7 (broader Vs/Vst includes inhabited-mode states).', 'force_order'));

// 4. Threshold-Crossing Rule
content.push(h1('4. Threshold-Crossing Rule'));
content.push(para('If the verb means entering, reaching, exceeding, achieving, producing, losing, releasing, or bringing something into a new state → test Process (Vpt/Vp) FIRST.'));
content.push(para('Examples: 加入→Vpt, 通過→Vpt, 超過→Vpt, 失去→Vpt, 造成→Vpt, 產生→Vpt/Vp, 達成→Vpt'));

// 5. 在/著 Triangulation
content.push(h1('5. 在/著 Triangulation Matrix'));
content.push(simpleTable(
  ['Category', '+在', '+著', 'Confirmed by'],
  [
    ['Action (V/Vi)', 'Yes', 'Yes*', 'Both pass'],
    ['State (Vs/Vst)', 'No', 'Yes**', '在 fails, 著 accepted'],
    ['Process (Vp/Vpt)', 'Limited', 'No', '著 rejected'],
  ],
  [2200, 1500, 1500, 3800]
));
content.push(h3('Three-Way 著 Scale'));
content.push(bullet('Natural → not punctual Process. +在=Action, -在=State.'));
content.push(bullet('Marginal/literary → inconclusive. Default to morphology.'));
content.push(bullet('Incompatible → strong evidence for Process → Vp/Vpt.'));

// 6. Resultative Complement
content.push(h1('6. Resultative vs Potential Complements (REFINED v2.3)'));
content.push(runs({ text: 'Two structurally distinct complement families that the §6 rule must NOT conflate. The previous version of this rule misfired on potential complements like 受得了, producing the CE-sibling dispute that failed lens recheck on 2026-04-26.', bold: true }));

content.push(h2('6.1 True Resultative Complements (V + 結)'));
content.push(runs('If a resultative complement morpheme is present directly after the verb (no 得/不 between) → ', { text: 'Vpt (transitive) or Vp (intransitive)', bold: true }, '. The structure describes V completing into a result-state.'));
content.push(para('Common complements: 到, 住, 掉, 見, 滿, 出, 成, 合, 回, 化, 斷, 取/得, 失/掉, 止, 先, 後, 染, 立, 選, 上'));
content.push(para('Examples: 寫完, 打破, 看見, 做好, 找到 — each describes a result-event (writing→finished, hitting→broken, looking→seeing, making→good, looking-for→found).'));
content.push(h3('V → Vpt Promotion Pattern'));
content.push(para('Base action verb (V) + complement → Vpt. These are SEPARATE lexicon entries.'));
content.push(bullet('閉(V) → 閉上(Vpt), 關(V) → 關上(Vpt), 打(V) → 打敗(Vpt)'));

content.push(h2('6.2 Potential Complements (V + 得/不 + 結) — Vst territory, NOT Vpt'));
content.push(runs({ text: 'Diagnostic: presence of 得 or 不 between the verb and the complement morpheme.', bold: true }, ' This is a DIFFERENT structure expressing modal capacity — the held state of being-able / not-being-able to reach the complement. It is NOT a resultative complement, and should not be treated as one.'));

content.push(h3('Structure'));
content.push(simpleTable(
  ['Pattern', 'Type', 'Semantics', 'POS'],
  [
    ['V + 結', 'Resultative (寫完, 打破, 看見)', 'V completed INTO a result-state', { text: 'Vpt / Vp', bold: true }],
    ['V + 得 + 結', 'Potential affirmative (寫得完, 看得見, 受得了)', 'Held STATE of being-able to reach 結', { text: 'Vst (capacity-state)', bold: true }],
    ['V + 不 + 結', 'Potential negative (寫不完, 看不見, 受不了)', 'Held STATE of not-being-able to reach 結', { text: 'Vst (capacity-state)', bold: true }],
  ],
  [2200, 3200, 3200, 1400]
));

content.push(h3('The 了 in 受得了 / 等不了 / 忍得了'));
content.push(runs('The 了 in these phrases is ', { text: 'the verb 了 (liao3 — to manage / handle through to completion)', bold: true }, ', NOT the perfective particle 了 (le). It functions as a complement target meaning "to manage to bring V through to completion."'));
content.push(bullet('受得了 = bear (V) + (potential) + manage-through (了) = held STATE of being-able-to-bear-it-through'));
content.push(bullet('受不了 = bear + (negative potential) + manage-through = held STATE of not-being-able-to-bear-it-through'));
content.push(bullet('忍得了 = endure + (potential) + manage-through = held STATE of being-able-to-endure-it'));
content.push(bullet('等不了 = wait + (negative potential) + manage-through = held STATE of not-being-able-to-wait-it-out'));
content.push(runs('These name capacity-states one inhabits (Key 1 of the philosophical lens — see ', { text: 'project_state_verb_lens.md', italics: true }, '), cohort with 忍, 愛, 信, 知 as inhabited modes of being. The Vst classification is master honoring the capacity-state semantics that the potential-complement form serves.'));

content.push(h3('Why this matters'));
content.push(para('Applying the §6.1 resultative-complement rule mechanically to a potential complement misfires: the morphological surface looks complement-shaped, but the structure is modal-capacity, not result-attainment. The 受得了 → CE editorial sibling (2026-04-26) was rooted in this misanalysis.'));

content.push(h3('Modal encapsulation (NEW v2.5 — semantic framing for usage_note)'));
content.push(runs({ text: 'V+得/不+結 forms encapsulate modal "能/不能" semantics. ', bold: true }, 'The 得/不 does the modal work English uses "can/cannot" for:'));
content.push(bullet('受得了 = 能受 + (了 completion) → "(I) am able to bear (it through)"'));
content.push(bullet('受不了 = 不能受 + (了) → "(I) am not able to bear (it through)"'));
content.push(bullet('等不了 = 不能等 + (了) → "(I) am not able to wait (it through)"'));
content.push(bullet('找得到 = 能找 + (到 arrival) → "(I) am able to find (it)"'));
content.push(runs('This is ', { text: 'quasi-Vaux at the semantic level', bold: true }, ' — but classification stays Vst because: (a) takes a direct object/situation, not a VP (受得了 你的脾氣, not 受得了 + V), (b) lexicalized as a single word, not a productive modal+verb construction. ', { text: 'Embed the modal-encapsulation reading in usage_note', bold: true }, ' so learners see what the form is doing semantically.'));

content.push(h3('CE exclusion for the 了-cohort (PERMANENT, NEW v2.5)'));
content.push(runs({ text: 'V+得了 / V+不了 forms (受得了, 受不了, 等得了, 等不了, 忍得了, 忍不了) are NOT CE — not now, not as future structural reserve. ', bold: true }, 'The 了(liao3) in these forms has grammaticalized into a modal-completion marker (closer to "manage" + aspect than to a content-bearing complement morpheme). The whole sub-cohort stays Vst categorically. CE remains available for V+得/不+結 forms with content-bearing complement morphemes (到/見/出/完 etc.) if a future structural-only need arises — but the 了-cohort is excluded from that reserve.'));

content.push(h3('Quick check'));
content.push(bullet('See 得 or 不 between V and the complement morpheme? → potential complement → Vst territory.'));
content.push(bullet('See V directly bonded to the complement morpheme? → resultative → Vpt/Vp territory.'));
content.push(bullet('Is the negation 不+V or V+不+結? The split test: 受不了 (V+不+結, potential) vs 不受 (不+V, simple negation) confirms the structure.'));

content.push(pageBreak());

// 7. Stative Verbs — BROADENED v2.2
content.push(h1('7. Stative Verbs (BROADENED v2.2)'));
content.push(runs({ text: 'Two scopes of stativity in Chinese — broader than English-default suggests.', bold: true }));
content.push(para('The 很-test diagnoses gradable adjectival states (好/貴/高/快/慢) — qualities measured along a continuous scale. 很-passing is sufficient evidence for Vs membership.'));
content.push(runs({ text: 'But the 很-test does NOT define the boundary of Vst. ', bold: true }, 'Vst includes a second class of inhabited modes that don\'t grade because they\'re binary states one inhabits, not continuous qualities one measures.'));

content.push(h3('Two Vs/Vst sub-classes'));
content.push(simpleTable(
  ['Sub-class', 'Examples', 'Diagnostic'],
  [
    ['Gradable adjectival states (English-default Vs)', '好, 貴, 高, 快, 慢, 漂亮', '很-test passes'],
    [{ text: 'Psychological/cognitive dispositions (inhabited mode)', bold: true }, '忍, 愛, 信, 知, 懂, 怕, 喜歡, 想, 覺得', '很-test typically FAILS; framework test: "is this a sustained mode of being one inhabits?"'],
    [{ text: 'Doctrinal/imparted-state verbs', bold: true }, '教 jiao4 (doctrinal teaching, distinct from 教 jiao1 action), 修 (cultivate)', 'Same framework test — inhabited-mode-of-being'],
    [{ text: 'States of capacity', bold: true }, '受得了, 受不了, 忍不住, 忍得住', 'Inhabited capacity-states; very-X often unnatural but Vst membership intact'],
    [{ text: 'Affective fixed expressions / mood states', bold: true }, '算了, 沒關係', 'Inhabited affective states; Vs not because gradable but because state-of-being'],
  ],
  [2400, 2800, 3800]
));

content.push(h3('Diagnostic Rules'));
content.push(bullet('Vs (adjectival): 在-test fails AND no object AND 很-test passes → gradable adjectival Vs. Examples: 是, 姓, 好, 貴, 漂亮.'));
content.push(bullet('Vs (broader, inhabited mode): 在-test fails AND no object AND the word names a state inhabited rather than performed, even when 很-test fails. Examples: 算了, 沒關係.'));
content.push(bullet('Vst: 在-test fails AND takes object/complement. Examples: 喜歡, 知道, 覺得, 怕, 信, 忍, 受得了, 教 jiao4 (doctrinal).'));
content.push(bullet('不/沒 test: 沒+V = unreached result → Vpt. 不+V = ongoing state → Vst.'));
content.push(bullet('Vsattr: FAILS 很-test, attributive only, CLOSED CATEGORY. Full TOCFL inventory: 本來, 必須, 彩色, 長途, 大型, 電動...'));
content.push(bullet('Vspred: Predicative only (夠, 多, 嚇 — state-flash of being-startled).'));

content.push(h3('Critical Note (lesson 2026-04-26)'));
content.push(runs('The English-default reading of "stative verb" is gradable-quality (the adjectival Vs class). ', { text: 'Chinese Vs/Vst is broader, encompassing inhabited modes of being that English would naturally categorize as actions (endure, love, believe, know). ', bold: true }, 'When an English gloss reads actional but the master classifies Vst, ', { text: 'the master is likely honoring the inhabited-mode reading.', bold: true }, ' Apply §0 master-perspective principle before disputing.'));
content.push(runs('See ', { text: 'project_state_verb_lens.md ', italics: true, bold: true }, 'Key 1 for the philosophical framing.'));

// 8. Separable Verbs
content.push(h1('8. Separable Verbs (離合詞)'));
content.push(bullet('Vsep — Action, V-O splits: 辦案→辦了案'));
content.push(bullet('Vpsep — Process, V-O splits: 結婚→結了婚 (CLOSED, 13 words)'));
content.push(bullet('Vssep — State, V-O splits: 擔心→擔了心 (CLOSED, 7 words)'));
content.push(bullet('Fused V-O compounds that do NOT split → NOT Vsep'));
content.push(runs({ text: 'Caveat (lesson 2026-04-26): ', bold: true }, 'separability evidence (e.g. 請了三天假) is not by itself sufficient to over-rule master\'s V classification on a fused verbal expression. Master may treat 請X compounds as V-sep when X is a concrete entity (請客) and as V when X is an abstract state being requested (請假, 請教, 請求). Investigate the 請-family pattern before disputing.'));

// 9. Causative/Inchoative
content.push(h1('9. Causative/Inchoative Alternation'));
content.push(para('Same character can have both Vpt (causative) and Vp (inchoative) senses. Create separate DB entries.'));
content.push(bullet('降低成本(Vpt) vs 溫度降低了(Vp)'));

// 10. Nominalization
content.push(h1('10. Nominalization Filter'));
content.push(para('Do NOT create separate N sense for natural nominalization. Only add N when noun use changes the FRAME.'));
content.push(bullet('Frame shift YES → add N: 按摩(service), 報復(retaliation suffered), 編輯(person)'));
content.push(bullet('Frame shift NO → no N: 安撫, 拖延'));

// 11. Disagreement Protocol
content.push(h1('11. Disagreement Protocol'));
content.push(numItem('Apply §0 master-perspective check first — investigate framework before asserting disagreement.', 'disagree'));
content.push(numItem('Record rubric result.', 'disagree'));
content.push(numItem('Record hesitation reason.', 'disagree'));
content.push(numItem('Rubric wins by default. TOCFL master overrides rubric when family-consistent or framework-reasoned.', 'disagree'));
content.push(numItem('Flag for review via the disputed-POS workflow only when investigation finds no framework reason.', 'disagree'));

// 12. Known Tendencies
content.push(h1('12. Known Tendencies'));
content.push(h3('Huiming'));
content.push(bullet('Vsattr overuse → check TOCFL closed list'));
content.push(bullet('Vsep over-application on fused V-O → separation test in modern speech'));
content.push(bullet('N under-assigned for primary noun senses'));
content.push(bullet('Adv secondary POS sometimes missed'));
content.push(bullet('Prn under-used for reciprocal pronouns'));
content.push(h3('Louie'));
content.push(bullet('Vst over-applied to active verbs → 在-test mandatory'));
content.push(bullet('Vi used as default intransitive → Vi = intransitive ACTION only'));
content.push(bullet('N assigned to nominalization-only uses → apply nominalization filter'));
content.push(h3('Guangliu'));
content.push(bullet('Vsattr over-applied to gradable Vs → if 很+V natural → Vs only'));
content.push(bullet('Vpsep over-called → TOCFL Vpsep = 13 words'));
content.push(bullet('Vpt confused with V → apply resultative complement rule first'));
content.push(bullet('Vi treated as any intransitive → Vi = intransitive ACTION only'));
content.push(bullet('N over-generated → nominalization filter'));
content.push(bullet('Vst missed entirely → 在-test fails + takes object = Vst'));
content.push(bullet('Vspred under-used → predicative-only state verbs'));
content.push(runs({ text: 'NEW (2026-04-26): English-trained POS dispute pattern. ', bold: true }, 'Disputes master classifications based on English-shaped intuition (action-default reading of Vs/Vst, pre-noun-only reading of Det, gradable-only reading of stative) without investigating master\'s framework reason. Caught when 11 of 11 active L3 disputes failed lens recheck. Apply §0 before disputing.'));

content.push(h2('TOCFL as Tiebreaker'));
content.push(runs({ text: 'When all three disagree, TOCFL classification governs. ', bold: true }, 'Furthermore: when all three reviewers AGREE on a dispute, ask whether the agreement reflects shared lens bias rather than confirmed correctness. The cross-framework check is investigation, not consensus.'));

// 13. Trap Words
content.push(h1('13. Known Trap Words'));
content.push(simpleTable(
  ['Word', 'Trap', 'Correct POS', 'Rule'],
  [
    ['抵達', 'Looks intransitive', 'V', 'Location as direct object'],
    ['離開', 'Looks intransitive', 'V', 'Same: 離開這裡'],
    ['明白', 'Feels like Vst', 'Vpt', '白=resultative; 沒明白=result unreached'],
    ['知道/喜歡/覺得', 'Feels like process', 'Vst', '在-test fails; already IN the state'],
    ['通過', 'Simple action', 'Vpt', '過=resultative complement'],
    ['加入', 'Simple action', 'Vpt', '入=resultative complement'],
    ['完成', 'Might feel Vi', 'Vpt', '成=resultative; takes direct object'],
    ['閉/開', 'Tempting Vpt', 'V', 'No complement in base verb'],
    ['安裝', 'Goal-directed→Vpt?', 'V', 'Durative activity; 在安裝 freely'],
    ['必須', 'Classifying→Vsattr?', 'Vaux', 'Modal auxiliary'],
    ['夠', 'Gradable→Vs?', 'Vspred', 'Predicative bias'],
    ['忍', 'Reads as action ("endure")', 'Vs', 'Inhabited-mode state per §7 broadened scope'],
    ['教 jiao4', 'Reads as action', 'Vst', 'Doctrinal-teaching as inhabited mode (distinct from jiao1 V)'],
    ['受得了', 'V+得+Vcomp pattern', 'Vst', 'Inhabited capacity-state per §7 broadened scope'],
    ['算了', 'Looks idiomatic', 'Vs', 'Inhabited affective state per §7 broadened scope'],
    ['嚇', 'Reads as action ("scare")', 'Vspred', 'State-flash of being-startled'],
  ],
  [1500, 2300, 1500, 3700]
));

// 14. Non-Verb POS
content.push(h1('14. Non-Verb POS Reference'));
content.push(para('N, Prn, Num, M, Adv, Prep, Conj, Det, Ptc, Intj, IE, Ph, CE, Vcomp'));

// 14a. Determiner — broader scope (NEW v2.2)
content.push(h1('14a. Determiner (Det) — broader than English-default (NEW v2.2)'));
content.push(runs({ text: 'Chinese-grammar definition: ', bold: true }, 'a determiner is a word that modifies a noun or noun phrase to specify, quantify, or identify it. The role is functional, not positional.'));
content.push(simpleTable(
  ['Sub-role', 'Position', 'Examples'],
  [
    ['Pre-nominal specifiers (English-default Det)', 'before the noun', '這, 那, 每, 各, 哪 — locate or identify'],
    ['Pre-nominal quantifier-restrictors', 'before the noun', '全, 整 — quantify scope'],
    [{ text: 'Post-nominal category-determining suffixes (Chinese-specific Det role)', bold: true }, 'after the morpheme being categorized', { text: '氣 in 氧氣/氮氣/蒸氣/廢氣/香氣/怒氣 — closes the compound and assigns it to a category-class', bold: true }],
  ],
  [2800, 2200, 4500]
));
content.push(para('The broader scope embraces "modifier that narrows or fixes the noun\'s reference," regardless of position. English-trained intuition reads Det as pre-noun-only because English determiners (a/an/the/this/that/each) are positionally fixed; Chinese is not bound to that constraint.'));

content.push(h3('Canonical case (2026-04-26): 氣 in compounds'));
content.push(runs('Master classifies 氣 qi4 at L5 as Det because in compound formation 氣 functions as a ', { text: 'category-determining suffix', bold: true }, ' — 氧氣 = oxygen-as-gas-category, 怒氣 = anger-as-mood-category, 香氣 = aroma-as-atmospheric-category. The 氣-suffix closes the compound and tells you what kind of thing the whole word names.'));
content.push(para('We initially disputed this Det classification because it didn\'t fit the English pre-noun specifier role. The dispute was the failure mode: English-shaped intuition mis-read TOCFL\'s broader Det scope.'));

content.push(h3('Audit principle'));
content.push(runs({ text: 'When master classifies a word as Det that doesn\'t fit English-determiner intuition, ask whether the word modifies, specifies, quantifies, or identifies another lexical element — from any position. ', bold: true }, 'If yes, master\'s Det is right; the dispute is rooted in narrow-scope reading of the label, not actual classification error.'));
content.push(runs('See ', { text: 'project_state_verb_lens.md ', italics: true, bold: true }, 'Key 2 (substrate concepts that resist boxing) for the philosophical framing.'));

// 14b. Prn vs N (NEW v2.2)
content.push(h1('14b. Prn vs N for Quantifier-Collectives (NEW v2.2)'));
content.push(runs({ text: 'Master reserves Prn for true personal/demonstrative pronouns. ', bold: true }, 'Quantifier-collectives (大家, 人人, 各位, 誰, 什麼) stay as N in master\'s framework — they name collective/quantifier referents but don\'t function as pure pronouns the way 我/你/他/這/那/哪 do.'));

content.push(h3('DB convention vs master'));
content.push(simpleTable(
  ['Word', 'Master POS', 'DB convention', 'Lens reading'],
  [
    ['我, 你, 他, 我們, 你們, 他們', 'N (coarse)', 'Prn (refined)', 'Personal pronouns — DB upgrade is correct'],
    ['這, 那, 哪 (demonstratives)', 'Det', 'Det / Prn (context-dependent)', 'Demonstrative function'],
    [{ text: '大家, 人人, 各位, 誰, 什麼', bold: true }, 'N', 'N (do NOT upgrade)', 'Quantifier-collectives — master\'s N is intentional ontology, not coarse classification'],
  ],
  [2700, 1700, 2300, 2800]
));

content.push(runs({ text: 'Audit lesson (2026-04-26): ', bold: true }, 'Our DB\'s Prn-over-N upgrade was correct for personal pronouns (我/你/他). Extending it to quantifier-collectives (人人 N → Prn) was English-bias inertia — English groups all "everyone/who/what" type words as pronouns, but master deliberately distinguishes.'));

content.push(pageBreak());

// 14c. CE — Complement Expression (NEW v2.4)
content.push(h1('14c. CE — Complement Expression (NEW v2.4)'));
content.push(runs({ text: 'CE = Complement Expression. ', bold: true }, 'The structural class for V+得/不+結 forms — verb + (potential particle) + complement morpheme. Names the family of expressions whose full meaning emerges from the V+complement structure.'));

content.push(h2('Membership criteria'));
content.push(bullet('Internal V+得/不+結 morphology (potential complement structure)'));
content.push(bullet('Productive negation pattern: 受得了 ↔ 受不了, 看得見 ↔ 看不見'));
content.push(bullet('Capacity / result-modal semantics (the held state of being-able / not-being-able to reach the complement)'));
content.push(bullet('Examples: 受得了, 受不了, 想不到, 找得到, 看不出, 怪不得, 顧不得'));

content.push(h2('Current working classification'));
content.push(runs({ text: 'The 受得了 cohort currently sits at ', bold: true }, { text: 'Vst per §6.2', bold: true, color: 'C00000' }, ' (capacity-state semantics). ', { text: 'CE is reserved as the structural classification but not actively used', bold: true }, ' — Vst captures the working semantics for these forms without producing duplicate content. If a future need arises to classify a V+得/不+結 form by its structural pattern rather than its capacity-state semantics, CE is available. Until then, default to Vst per §6.2.'));

content.push(h2('What CE is NOT'));
content.push(bullet('NOT chengyu / classical 4-character idioms — those sit in IE (see §14d)'));
content.push(bullet('NOT general idiomatic expressions like 算了 / 沒關係 — those sit in IE'));
content.push(bullet('NOT verbal complement morphemes like 完, 到, 見 standing alone — those are Vcomp'));
content.push(bullet('NOT resultative compounds like 看見, 寫完 (V+結 directly bonded) — those are Vpt per §6.1'));

content.push(h2('History note (2026-04-26)'));
content.push(runs('Before v2.4 the slug CE was DB-labeled "Chengyu" — a slug-meaning error. The "CE" abbreviation naturally reads as "Complement Expression" to English-trained reviewers; this false-friend invited Chengyan to use CE for V+得+了 structures (which is the right structural reading of CE-as-Complement-Expression, but conflicted with the DB\'s Chengyu labeling). Resolution: re-labeled CE to mean Complement Expression (its natural English reading); folded chengyu into IE as a sub-type. ', { text: 'Slug-meaning verification is now lessons ledger entry #18.', bold: true }));

content.push(pageBreak());

// 14d. IE — Idiomatic Expression (NEW v2.4)
content.push(h1('14d. IE — Idiomatic Expression — broad scope (NEW v2.4)'));
content.push(runs({ text: 'IE = Idiomatic Expression, broadly. ', bold: true }, 'Covers all idiomatic content in Chinese — from classical 成語 (chengyu / four-character idioms) to colloquial 慣用語 (set expressions / fixed phrases) to 諺語 (proverbs) to set discourse phrases. The umbrella term in Chinese linguistics for this scope is 熟語 (shu2yu3).'));

content.push(h2('Sub-types under IE'));
content.push(simpleTable(
  ['Sub-type', 'Description', 'Examples'],
  [
    [{ text: '成語 (Chengyu)', bold: true }, 'Classical four-character idioms drawn from literature, history, folklore. Fixed lexical form, often with literal + figurative readings.', '一石二鳥, 守株待兔, 畫蛇添足, 班門弄斧, 對牛彈琴'],
    [{ text: '慣用語', bold: true }, 'Colloquial fixed phrases, often three characters, modern register, pragmatic discourse use.', '走後門, 開夜車, 吃豆腐, 拍馬屁'],
    [{ text: 'Set discourse phrases', bold: true }, 'Standalone pragmatic moves — closing matters, expressing politeness, etc.', '算了, 沒關係, 不客氣, 加油, 對不起'],
    [{ text: '諺語 (Proverbs)', bold: true }, 'Folk wisdom expressions, often longer or with parallel structure.', '入鄉隨俗, 滴水穿石, 一分耕耘一分收穫'],
  ],
  [2200, 4400, 3000]
));

content.push(h2('Why no separate Chengyu slug'));
content.push(runs({ text: 'Decision (2026-04-26 by 絡一): ', bold: true }, 'IE is sufficient as the umbrella POS class. Adding a separate CY (Chengyu) slug would proliferate POS classes for marginal differentiation and risk confusing enrichers. The chengyu/non-chengyu distinction can be captured (when needed) in usage_note rather than at the POS level. IE is one POS, broad in scope by design.'));

content.push(h2('What IE is NOT'));
content.push(bullet('NOT V+得/不+結 structures (those are CE per §14c, currently working as Vst)'));
content.push(bullet('NOT general nouns or noun phrases that happen to be idiomatic in translation — those stay N'));
content.push(bullet('NOT verb compounds with idiomatic meaning — those stay in their verb POS class'));

content.push(h2('Audit principle'));
content.push(runs({ text: 'When marking IE: ', bold: true }, 'verify the form is genuinely a fixed multi-word expression that doesn\'t productively decompose under modern usage. If the form productively negates (受得了 → 受不了), it\'s probably CE-territory (currently Vst per §6.2). If the form has internal verb morphology that operates compositionally, it\'s probably a verb compound under one of the V-family POS classes.'));

content.push(pageBreak());

// 15. Definition Rules
content.push(h1('15. Definition Rules'));
content.push(bullet('No POS information in definitions'));
content.push(bullet('No capitalization of first word'));
content.push(bullet('No separable notes like "(VO separable: X+Y)"'));
content.push(bullet('Definitions specific to THIS sense and THIS POS only'));
content.push(bullet('Both EN and ZH-TW definitions for every sense'));
content.push(bullet('Every example sentence MUST contain the word being defined'));

// 16. Alignment & Source
content.push(h1('16. Alignment & Source'));
content.push(bullet('Source: tocfl | editorial'));
content.push(bullet('Alignment: full (🤓) | partial (🤨) | disputed (😵\u200d💫)'));
content.push(bullet('Disputation: mark TOCFL sense disputed, create editorial sense as partial, both visible. INVESTIGATE THE FRAMEWORK FIRST per §0 before invoking the disputed-POS workflow.'));

// 17. Vcomp
content.push(h1('17. Resultative Complements (Vcomp)'));
content.push(para('56 characters function as resultative complements. Each has a Vcomp sense alongside regular POS senses.'));
content.push(bullet('Verbal: 到, 見, 住, 開, 掉, 成, 出, 上, 完, 入, 回, 合, 滿, 會, 懂, 走, 給, 在, 著'));
content.push(bullet('State: 好, 對, 錯, 飽, 壞, 死, 熟, 乾, 醉, 透, 準, 碎, 亮, 暗, 濕'));
content.push(bullet('Outcome: 破, 斷, 光, 清, 化, 取, 得, 敗, 失, 去, 先, 後, 染, 立, 選, 起'));

// 18. Formula Guidelines
content.push(h1('18. Formula Guidelines'));
content.push(bullet('Standard slot labels: [Verb], [Noun], [Modifier], [Clause], [Person], [Place], [Number]'));
content.push(bullet('Do NOT use: [Adj], [Stative Word], POS slugs as labels'));
content.push(bullet('Word MUST appear in its own formula'));
content.push(bullet('Use Chinese in formulas (slot labels in [] are only English in formula_en; Chinese in formula_zh)'));
content.push(bullet('Vsep formulas MUST show both joined and split forms'));

// Build doc
const numberingConfigs = [
  {
    reference: 'bullets',
    levels: [
      { level: 0, format: LevelFormat.BULLET, text: '\u2022', alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 360, hanging: 240 } } } },
      { level: 1, format: LevelFormat.BULLET, text: '\u25E6', alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 720, hanging: 240 } } } },
    ],
  },
];
for (const id of _registeredLists) {
  numberingConfigs.push({
    reference: 'numbered_' + id,
    levels: [
      { level: 0, format: LevelFormat.DECIMAL, text: '%1.', alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 360, hanging: 240 } } } },
    ],
  });
}

const doc = new Document({
  creator: '光流 (Claude)',
  title: '流動 Living Lexicon — POS Reference Guide v2.4',
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
  numbering: { config: numberingConfigs },
  sections: [
    {
      properties: { page: { margin: { top: 1080, bottom: 1080, left: 1080, right: 1080 } } },
      children: content,
    },
  ],
});

(async () => {
  const buffer = await Packer.toBuffer(doc);
  const outPath = '/Users/chuluoyi/Documents/華語/planning/流動_POS_Reference_Guide_v2.4.docx';
  fs.writeFileSync(outPath, buffer);
  console.log('Wrote ' + outPath + ' (' + buffer.length + ' bytes)');
})();
