<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Help — 流動 Living Lexicon</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@300;400;600;700&family=DM+Mono:ital,wght@0,300;0,400;1,300&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
@include('partials.lexicon._foundations')
<style>

/* ── Layout ── */
.help-wrap {
  max-width: 860px;
  margin: 0 auto;
  padding: 2.5rem 1.4rem 5rem;
  position: relative; z-index: 1;
}

/* ── Page header ── */
.help-eyebrow {
  font-family: 'DM Mono', monospace;
  font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase;
  color: var(--accent); opacity: 0.7;
  margin-bottom: 0.6rem;
}
.help-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: 2.2rem; font-weight: 300;
  color: var(--ink);
  margin-bottom: 0.5rem;
  line-height: 1.15;
}
.help-intro {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.05rem; color: var(--dim);
  line-height: 1.7; max-width: 580px;
  margin-bottom: 3rem;
}

/* ── Section headings ── */
.help-section {
  margin-bottom: 2.8rem;
}
.help-section-label {
  font-family: 'DM Mono', monospace;
  font-size: 0.6rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--accent); opacity: 0.6;
  margin-bottom: 0.35rem;
}
.help-section-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.35rem; font-weight: 400;
  color: var(--ink);
  margin-bottom: 0.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--border);
}
.help-section-desc {
  font-family: 'Cormorant Garamond', serif;
  font-size: 0.95rem; color: var(--dim);
  line-height: 1.6;
  margin-bottom: 1rem;
}

/* ── POS rows ── */
.pos-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 0.5rem;
}
.pos-table thead th {
  font-family: 'DM Mono', monospace;
  font-size: 0.58rem; letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--dim); opacity: 0.6;
  font-weight: 400;
  text-align: left;
  padding: 0.3rem 0.8rem 0.5rem;
  border-bottom: 1px solid var(--border);
}
.pos-table tbody tr {
  border-bottom: 1px solid rgba(0,0,0,0.05);
  transition: background 0.12s;
}
.pos-table tbody tr:hover {
  background: var(--surface);
}
.pos-table td {
  padding: 0.75rem 0.8rem;
  vertical-align: top;
}

/* Chip column */
.pos-table td.td-chip {
  width: 3.2rem;
  padding-right: 0.4rem;
}
.pos-chip {
  display: inline-block;
  font-family: 'DM Mono', monospace;
  font-size: 0.68rem; font-weight: 400;
  letter-spacing: 0.03em;
  color: var(--accent);
  background: var(--tag-bg);
  border-radius: 2px;
  padding: 0.2rem 0.45rem;
  white-space: nowrap;
}

/* Full name column */
.td-name {
  width: 13rem;
}
.pos-fullname {
  font-family: 'DM Mono', monospace;
  font-size: 0.72rem;
  color: var(--text);
  line-height: 1.4;
}

/* Description column */
.td-desc {
  /* flexible */
}
.pos-desc {
  font-family: 'Cormorant Garamond', serif;
  font-size: 0.97rem;
  color: var(--dim);
  line-height: 1.55;
  margin-bottom: 0.4rem;
}

/* Example block */
.pos-example {
  display: flex; align-items: baseline; gap: 0.5rem;
  flex-wrap: wrap;
}
.pos-ex-zh {
  font-family: 'Noto Serif TC', serif;
  font-size: 1.05rem;
  color: var(--ink);
  font-weight: 400;
}
.pos-ex-py {
  font-family: 'DM Mono', monospace;
  font-size: 0.67rem;
  color: var(--dim);
  font-style: italic;
}
.pos-ex-en {
  font-family: 'Cormorant Garamond', serif;
  font-size: 0.9rem;
  color: var(--dim);
}
.pos-ex-sep {
  color: var(--border);
  font-size: 0.8rem;
}

/* ── Group divider (the 2x2 axis note) ── */
.axis-note {
  display: flex; align-items: center; gap: 0.8rem;
  margin: 2rem 0 1.5rem;
  font-family: 'DM Mono', monospace;
  font-size: 0.62rem; letter-spacing: 0.1em; text-transform: uppercase;
  color: var(--dim); opacity: 0.5;
}
.axis-note::before, .axis-note::after {
  content: ''; flex: 1;
  height: 1px; background: var(--border);
}

/* ── Framework box ── */
.framework-box {
  background: var(--surface);
  border: 1px solid var(--border);
  border-left: 3px solid var(--accent);
  border-radius: 2px;
  padding: 1.2rem 1.4rem;
  margin-bottom: 2rem;
}
.framework-box-title {
  font-family: 'DM Mono', monospace;
  font-size: 0.65rem; letter-spacing: 0.15em; text-transform: uppercase;
  color: var(--accent); margin-bottom: 0.8rem;
}
.framework-grid {
  display: grid;
  grid-template-columns: auto 1fr 1fr 1fr;
  gap: 1px;
  background: var(--border);
  border: 1px solid var(--border);
  border-radius: 2px;
  overflow: hidden;
  font-family: 'DM Mono', monospace;
  font-size: 0.7rem;
}
.framework-grid div {
  background: var(--bg);
  padding: 0.5rem 0.8rem;
  line-height: 1.5;
}
.framework-grid .fg-header {
  background: rgba(98,64,200,0.06);
  color: var(--dim);
  font-size: 0.6rem;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}
.framework-grid .fg-axis {
  background: rgba(98,64,200,0.04);
  color: var(--accent);
  font-weight: 400;
}
.framework-grid .fg-cell {
  color: var(--text);
}
.framework-grid .fg-cell span {
  color: var(--accent);
  font-weight: 500;
}

/* ── Mobile ── */
@media (max-width: 600px) {
  .td-name { width: auto; }
  .pos-fullname { font-size: 0.65rem; }
  .framework-grid { font-size: 0.62rem; }
}

</style>
</head>
<body>
@include('partials.lexicon._site-header')

<div class="help-wrap">

  <div class="help-eyebrow">Help</div>
  <h1 class="help-title">Understanding 流動</h1>
  <p class="help-intro">
    流動 uses a precise part-of-speech system drawn from TOCFL, Taiwan's official Chinese proficiency framework.
    Each word in the lexicon carries one or more POS tags that describe exactly how it behaves in a sentence —
    not just broadly as a "verb" or "noun," but specifically what kind.
    This page explains each tag.
  </p>

  {{-- ══════════════════════════════════════════════════════════════ --}}
  {{-- VERB FRAMEWORK --}}
  {{-- ══════════════════════════════════════════════════════════════ --}}

  <div class="help-section">
    <div class="help-section-label">Verbs</div>
    <h2 class="help-section-title">The Verb System</h2>
    <p class="help-section-desc">
      Chinese verbs are organized along two axes: what <em>kind</em> of event they describe,
      and whether they take a direct object. Together these produce six core verb types,
      plus a few special categories.
    </p>

    <div class="framework-box">
      <div class="framework-box-title">Verb type × grammatical behavior</div>
      <div class="framework-grid">
        <div class="fg-header"></div>
        <div class="fg-header">Transitive (takes object)</div>
        <div class="fg-header">Intransitive (no object)</div>
        <div class="fg-header">Separable (V-O splits)</div>
        <div class="fg-axis">Action</div>
        <div class="fg-cell"><span>V</span> &nbsp;·&nbsp; 買書, 吃飯</div>
        <div class="fg-cell"><span>Vi</span> &nbsp;·&nbsp; 哭, 坐, 飛行</div>
        <div class="fg-cell"><span>V-sep</span> &nbsp;·&nbsp; 辦案, 頒獎</div>
        <div class="fg-axis">Process</div>
        <div class="fg-cell"><span>Vpt</span> &nbsp;·&nbsp; 完成, 打破</div>
        <div class="fg-cell"><span>Vp</span> &nbsp;·&nbsp; 死, 崩潰, 退休</div>
        <div class="fg-cell"><span>V-sep</span> &nbsp;·&nbsp; 生氣, 結婚</div>
        <div class="fg-axis">State</div>
        <div class="fg-cell"><span>Vst</span> &nbsp;·&nbsp; 喜歡, 知道</div>
        <div class="fg-cell"><span>Vs</span> &nbsp;·&nbsp; 好, 貴, 悲觀</div>
        <div class="fg-cell"><span>V-sep</span> &nbsp;·&nbsp; 擔心, 傷心</div>
      </div>
    </div>

    {{-- Action Verbs --}}
    <div class="axis-note">Action Verbs — no inherent state change</div>
    <table class="pos-table">
      <thead>
        <tr>
          <th>Tag</th>
          <th>Full name</th>
          <th>What it means · Example</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="td-chip"><span class="pos-chip">V</span></td>
          <td class="td-name"><div class="pos-fullname">Action Verb<br>transitive</div></td>
          <td class="td-desc">
            <div class="pos-desc">Subject does something to a direct object. The most common verb type — buying, eating, editing, supervising.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">買書</span>
              <span class="pos-ex-py">mǎi shū</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">to buy a book</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">監督工作</span>
              <span class="pos-ex-py">jiāndū gōngzuò</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">to supervise the work</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vi</span></td>
          <td class="td-name"><div class="pos-fullname">Action Verb<br>intransitive</div></td>
          <td class="td-desc">
            <div class="pos-desc">Subject performs an activity or behavior with no direct object — and no state change. The subject simply acts.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">辯論</span>
              <span class="pos-ex-py">biànlùn</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">to debate</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">飛行</span>
              <span class="pos-ex-py">fēixíng</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">to fly</span>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    {{-- State Verbs --}}
    <div class="axis-note">State Verbs — describe a quality, condition, or relationship</div>
    <table class="pos-table">
      <thead>
        <tr>
          <th>Tag</th>
          <th>Full name</th>
          <th>What it means · Example</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vs</span></td>
          <td class="td-name"><div class="pos-fullname">State Verb<br>intransitive</div></td>
          <td class="td-desc">
            <div class="pos-desc">Describes a quality or condition. No object. Works as a predicate on its own: <em>很貴</em>, <em>很悲觀</em>. What English speakers would call adjectives.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">昂貴</span>
              <span class="pos-ex-py">ángguì</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">expensive</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">悲觀</span>
              <span class="pos-ex-py">bēiguān</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">pessimistic</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vst</span></td>
          <td class="td-name"><div class="pos-fullname">State Verb<br>transitive</div></td>
          <td class="td-desc">
            <div class="pos-desc">Expresses a state, attitude, identity, or feeling directed toward an object or clause — like, know, resemble, feel. Not a dynamic action; you are simply in this relationship.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">喜歡音樂</span>
              <span class="pos-ex-py">xǐhuān yīnyuè</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">to like music</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">知道答案</span>
              <span class="pos-ex-py">zhīdào dá'àn</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">to know the answer</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vs-attr</span></td>
          <td class="td-name"><div class="pos-fullname">State Verb<br>attributive only</div></td>
          <td class="td-desc">
            <div class="pos-desc">Used <em>only</em> before a noun — it cannot stand alone as a predicate. A small, fixed category of words that function more like classifying labels than true adjectives.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">額外的費用</span>
              <span class="pos-ex-py">éwài de fèiyòng</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">extra charges (not: *費用很額外)</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">國營企業</span>
              <span class="pos-ex-py">guóyíng qǐyè</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">state-owned enterprise</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vs-pred</span></td>
          <td class="td-name"><div class="pos-fullname">State Verb<br>predicative only</div></td>
          <td class="td-desc">
            <div class="pos-desc">Used primarily as a predicate — it does not freely modify nouns as an attributive. The complement to Vs-attr.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">水夠了</span>
              <span class="pos-ex-py">shuǐ gòu le</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">the water is enough</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">人很多</span>
              <span class="pos-ex-py">rén hěn duō</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">there are a lot of people</span>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    {{-- Process Verbs --}}
    <div class="axis-note">Process Verbs — someone or something ends up in a different state</div>
    <table class="pos-table">
      <thead>
        <tr>
          <th>Tag</th>
          <th>Full name</th>
          <th>What it means · Example</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vp</span></td>
          <td class="td-name"><div class="pos-fullname">Process Verb<br>intransitive</div></td>
          <td class="td-desc">
            <div class="pos-desc">The subject undergoes a change of state. No direct object — it simply <em>happens</em> to the subject. Before and after are qualitatively different.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">崩潰</span>
              <span class="pos-ex-py">bēngkuì</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">to collapse (intact → broken)</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">退休</span>
              <span class="pos-ex-py">tuìxiū</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">to retire (working → retired)</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vpt</span></td>
          <td class="td-name"><div class="pos-fullname">Process Verb<br>transitive</div></td>
          <td class="td-desc">
            <div class="pos-desc">A process verb that takes a direct object — the object receives the process or comes into being as its result.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">完成任務</span>
              <span class="pos-ex-py">wánchéng rènwù</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">to complete a task (task → done)</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">打破記錄</span>
              <span class="pos-ex-py">dǎpò jìlù</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">to break a record (record → broken)</span>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    {{-- Special Verb Categories --}}
    <div class="axis-note">Special Verb Categories</div>
    <table class="pos-table">
      <thead>
        <tr>
          <th>Tag</th>
          <th>Full name</th>
          <th>What it means · Example</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="td-chip"><span class="pos-chip">V-sep</span></td>
          <td class="td-name"><div class="pos-fullname">Separable Verb<br>(V-O compound)</div></td>
          <td class="td-desc">
            <div class="pos-desc">A verb-object compound that can be split — you can insert an aspect marker or modifier between the two parts. Common in everyday speech.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">畢了業</span>
              <span class="pos-ex-py">bì le yè</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">graduated (from 畢業 → split with 了)</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">辦了兩個案</span>
              <span class="pos-ex-py">bàn le liǎng ge àn</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">handled two cases (from 辦案)</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vaux</span></td>
          <td class="td-name"><div class="pos-fullname">Auxiliary Verb<br>(modal)</div></td>
          <td class="td-desc">
            <div class="pos-desc">Modal verbs that express ability, permission, possibility, or desire. They precede the main verb and cannot take a direct object on their own.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">能說中文</span>
              <span class="pos-ex-py">néng shuō Zhōngwén</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">can speak Chinese</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">可以進來</span>
              <span class="pos-ex-py">kěyǐ jìnlái</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">may come in</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vcomp</span></td>
          <td class="td-name"><div class="pos-fullname">Verbal Complement</div></td>
          <td class="td-desc">
            <div class="pos-desc">A particle-like element that follows a verb to indicate possibility or permission: 得 (can, is possible) and 不得 (cannot, is not permitted).</div>
            <div class="pos-example">
              <span class="pos-ex-zh">去得</span>
              <span class="pos-ex-py">qù de</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">can go / it is possible to go</span>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  {{-- ══════════════════════════════════════════════════════════════ --}}
  {{-- NON-VERB POS --}}
  {{-- ══════════════════════════════════════════════════════════════ --}}

  <div class="help-section">
    <div class="help-section-label">Other Parts of Speech</div>
    <h2 class="help-section-title">Nouns, Adverbs &amp; More</h2>
    <p class="help-section-desc">
      Non-verb POS tags follow familiar grammatical categories, with a few distinctions
      that are specific to Chinese — particularly the measure word and the particle.
    </p>

    <table class="pos-table">
      <thead>
        <tr>
          <th>Tag</th>
          <th>Full name</th>
          <th>What it means · Example</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="td-chip"><span class="pos-chip">N</span></td>
          <td class="td-name"><div class="pos-fullname">Noun</div></td>
          <td class="td-desc">
            <div class="pos-desc">A person, place, thing, concept, or idea. The core referential category.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">勇氣</span>
              <span class="pos-ex-py">yǒngqì</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">courage</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">環境</span>
              <span class="pos-ex-py">huánjìng</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">environment</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">M</span></td>
          <td class="td-name"><div class="pos-fullname">Measure Word<br>(classifier)</div></td>
          <td class="td-desc">
            <div class="pos-desc">Chinese requires a measure word between a number (or 這/那) and a noun. Different nouns use different measure words — one of the most distinctive features of Chinese grammar.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">一本書</span>
              <span class="pos-ex-py">yī běn shū</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">one book (本 for bound volumes)</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">三條魚</span>
              <span class="pos-ex-py">sān tiáo yú</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">three fish (條 for long, flexible things)</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Adv</span></td>
          <td class="td-name"><div class="pos-fullname">Adverb</div></td>
          <td class="td-desc">
            <div class="pos-desc">Modifies a verb, adjective, or another adverb. Typically placed before the element it modifies.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">都</span>
              <span class="pos-ex-py">dōu</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">all, both</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">大概</span>
              <span class="pos-ex-py">dàgài</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">probably, roughly</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Prep</span></td>
          <td class="td-name"><div class="pos-fullname">Preposition</div></td>
          <td class="td-desc">
            <div class="pos-desc">Introduces a prepositional phrase that modifies a verb. Chinese prepositions (介詞) always precede the verb, not follow it as in English.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">從台北出發</span>
              <span class="pos-ex-py">cóng Táiběi chūfā</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">to set off from Taipei</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">對你說</span>
              <span class="pos-ex-py">duì nǐ shuō</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">to say to you</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Conj</span></td>
          <td class="td-name"><div class="pos-fullname">Conjunction</div></td>
          <td class="td-desc">
            <div class="pos-desc">Connects words, phrases, or clauses.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">跟</span>
              <span class="pos-ex-py">gēn</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">and (connecting nouns)</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">可是</span>
              <span class="pos-ex-py">kěshì</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">but, however</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Ptc</span></td>
          <td class="td-name"><div class="pos-fullname">Particle</div></td>
          <td class="td-desc">
            <div class="pos-desc">Grammatical particles that add aspect, modality, or sentence-final meaning. They carry no independent lexical meaning but are grammatically essential.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">嗎</span>
              <span class="pos-ex-py">ma</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">question particle</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">了</span>
              <span class="pos-ex-py">le</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">completive verbal particle</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Det</span></td>
          <td class="td-name"><div class="pos-fullname">Determiner</div></td>
          <td class="td-desc">
            <div class="pos-desc">Points to or specifies a noun — this, that, which. Chinese does not use articles (a, the), so determiners carry more of that referential work.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">這</span>
              <span class="pos-ex-py">zhè</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">this</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">那</span>
              <span class="pos-ex-py">nà</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">that</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Prn</span></td>
          <td class="td-name"><div class="pos-fullname">Pronoun</div></td>
          <td class="td-desc">
            <div class="pos-desc">Stands in for a noun — personal pronouns, demonstratives used pronominally, and question-word pronouns.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">我</span>
              <span class="pos-ex-py">wǒ</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">I, me</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">誰</span>
              <span class="pos-ex-py">shéi</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">who</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Num</span></td>
          <td class="td-name"><div class="pos-fullname">Number</div></td>
          <td class="td-desc">
            <div class="pos-desc">Cardinal and ordinal numbers. In Chinese, numbers always appear with a measure word before a noun.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">三</span>
              <span class="pos-ex-py">sān</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">three</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">第一</span>
              <span class="pos-ex-py">dì yī</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">first</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Aux</span></td>
          <td class="td-name"><div class="pos-fullname">Auxiliary<br>(structural particle)</div></td>
          <td class="td-desc">
            <div class="pos-desc">Structural particles like 的, 地, 得 that link modifiers to the elements they modify. Different from Ptc (sentence-final particles) and Vaux (modal verbs).</div>
            <div class="pos-example">
              <span class="pos-ex-zh">漂亮的花</span>
              <span class="pos-ex-py">piàoliang de huā</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">beautiful flower (的 links adj → noun)</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Intj</span></td>
          <td class="td-name"><div class="pos-fullname">Interjection</div></td>
          <td class="td-desc">
            <div class="pos-desc">A spontaneous exclamation or response word that stands independently. Expresses surprise, acknowledgment, pain, or other immediate reactions.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">哇</span>
              <span class="pos-ex-py">wa</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">wow</span>
            </div>
            <div class="pos-example">
              <span class="pos-ex-zh">唉</span>
              <span class="pos-ex-py">āi</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">alas, sigh</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">IE</span></td>
          <td class="td-name"><div class="pos-fullname">Idiomatic Expression<br>(成語)</div></td>
          <td class="td-desc">
            <div class="pos-desc">A fixed four-character expression (成語, chéngyǔ) with a meaning that cannot be derived from its individual characters. Drawn from classical literature and deeply embedded in written and formal Chinese.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">一石二鳥</span>
              <span class="pos-ex-py">yī shí èr niǎo</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">kill two birds with one stone</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Ph</span></td>
          <td class="td-name"><div class="pos-fullname">Phrase</div></td>
          <td class="td-desc">
            <div class="pos-desc">A fixed multi-word expression that functions as a unit — neither a single word nor a full sentence, but a conventional chunk of language with its own meaning.</div>
            <div class="pos-example">
              <span class="pos-ex-zh">不好意思</span>
              <span class="pos-ex-py">bù hǎo yìsi</span>
              <span class="pos-ex-sep">·</span>
              <span class="pos-ex-en">excuse me, I'm embarrassed</span>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  {{-- ══════════════════════════════════════════════════════════════ --}}
  {{-- QUICK REFERENCE --}}
  {{-- ══════════════════════════════════════════════════════════════ --}}

  <div class="help-section">
    <div class="help-section-label">Quick Reference</div>
    <h2 class="help-section-title">All Tags at a Glance</h2>
    <p class="help-section-desc">Every POS tag used in the lexicon, in one place.</p>

    <table class="pos-table">
      <thead>
        <tr>
          <th>Tag</th>
          <th>Full name</th>
          <th>Tag</th>
          <th>Full name</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="td-chip"><span class="pos-chip">V</span></td>
          <td><div class="pos-fullname">Action Verb, transitive</div></td>
          <td class="td-chip"><span class="pos-chip">N</span></td>
          <td><div class="pos-fullname">Noun</div></td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vi</span></td>
          <td><div class="pos-fullname">Action Verb, intransitive</div></td>
          <td class="td-chip"><span class="pos-chip">M</span></td>
          <td><div class="pos-fullname">Measure Word</div></td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vp</span></td>
          <td><div class="pos-fullname">Process Verb, intransitive</div></td>
          <td class="td-chip"><span class="pos-chip">Adv</span></td>
          <td><div class="pos-fullname">Adverb</div></td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vpt</span></td>
          <td><div class="pos-fullname">Process Verb, transitive</div></td>
          <td class="td-chip"><span class="pos-chip">Prep</span></td>
          <td><div class="pos-fullname">Preposition</div></td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vs</span></td>
          <td><div class="pos-fullname">State Verb, intransitive</div></td>
          <td class="td-chip"><span class="pos-chip">Conj</span></td>
          <td><div class="pos-fullname">Conjunction</div></td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vst</span></td>
          <td><div class="pos-fullname">State Verb, transitive</div></td>
          <td class="td-chip"><span class="pos-chip">Ptc</span></td>
          <td><div class="pos-fullname">Particle</div></td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vs-attr</span></td>
          <td><div class="pos-fullname">State Verb, attributive</div></td>
          <td class="td-chip"><span class="pos-chip">Det</span></td>
          <td><div class="pos-fullname">Determiner</div></td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vs-pred</span></td>
          <td><div class="pos-fullname">State Verb, predicative</div></td>
          <td class="td-chip"><span class="pos-chip">Prn</span></td>
          <td><div class="pos-fullname">Pronoun</div></td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">V-sep</span></td>
          <td><div class="pos-fullname">Separable Verb (V-O)</div></td>
          <td class="td-chip"><span class="pos-chip">Num</span></td>
          <td><div class="pos-fullname">Number</div></td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vaux</span></td>
          <td><div class="pos-fullname">Auxiliary Verb (modal)</div></td>
          <td class="td-chip"><span class="pos-chip">Aux</span></td>
          <td><div class="pos-fullname">Auxiliary (structural particle)</div></td>
        </tr>
        <tr>
          <td class="td-chip"><span class="pos-chip">Vcomp</span></td>
          <td><div class="pos-fullname">Verbal Complement</div></td>
          <td class="td-chip"><span class="pos-chip">Intj</span></td>
          <td><div class="pos-fullname">Interjection</div></td>
        </tr>
        <tr>
          <td class="td-chip"></td>
          <td></td>
          <td class="td-chip"><span class="pos-chip">IE</span></td>
          <td><div class="pos-fullname">Idiomatic Expression</div></td>
        </tr>
        <tr>
          <td class="td-chip"></td>
          <td></td>
          <td class="td-chip"><span class="pos-chip">Ph</span></td>
          <td><div class="pos-fullname">Phrase</div></td>
        </tr>
      </tbody>
    </table>
  </div>

</div>{{-- /help-wrap --}}

@include('partials.lexicon._site-footer')
</body>
</html>
