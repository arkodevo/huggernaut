{{-- Shared sentence segmentation engine --}}
{{-- Depends on: WORD_INDEX (built by each page before include) --}}
<style>
.seg-known {
  cursor: pointer;
  border-bottom: 1px dashed transparent;
  transition: border-color 0.15s, color 0.15s;
  position: relative;
}
.seg-known:hover { border-color: var(--accent); color: var(--accent); }
.seg-known.highlight { color: var(--gold); font-weight: 600; }
</style>
<script>
// Greedy longest-match tokenizer against known vocabulary
function segmentSentence(text) {
  const segments = [];
  let i = 0;
  while (i < text.length) {
    let matched = false;
    for (let len = Math.min(4, text.length - i); len >= 1; len--) {
      const slice = text.substring(i, i + len);
      if (WORD_INDEX[slice]) {
        segments.push({ text: slice, data: WORD_INDEX[slice], known: true });
        i += len;
        matched = true;
        break;
      }
    }
    if (!matched) {
      segments.push({ text: text[i], known: false });
      i++;
    }
  }
  return segments;
}

// Returns HTML with .seg-known spans, optional headword highlight
function segmentedHTML(text, headword) {
  const segs = segmentSentence(text);
  const variants = headword ? [headword.traditional, headword.simplified].filter(Boolean) : [];
  return segs.map(s => {
    if (s.known) {
      const d = s.data;
      const isHead = variants.includes(s.text);
      return `<span class="seg-known${isHead ? ' highlight' : ''}" data-smart-id="${d.smartId}" data-trad="${d.trad || s.text}" data-pinyin="${d.pinyin || ''}" data-pos="${d.pos || ''}" data-def="${(d.def || '').replace(/"/g, '&quot;')}">${s.text}</span>`;
    }
    return s.text;
  }).join('');
}
</script>
