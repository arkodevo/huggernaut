# Writing Workshop Modularisation & Example Filters

## Goal
Extract the Writing Workshop into shared Blade partials (CSS + JS) so both the **lexicon card** and the **IWP** use the same code. Redesign the example display to use multi-select filter chips instead of exclusive tabs.

---

## Step 1: Create shared Workshop Blade partials

Following the existing pattern (`_example-sentence-js.blade.php`, `_example-sentence-css.blade.php`), create:

- **`resources/views/partials/lexicon/_workshop-css.blade.php`** — All workshop-related CSS (shifu writing area, AI tabs, AI responses, saved deck, filter chips, delete confirmation modal)
- **`resources/views/partials/lexicon/_workshop-js.blade.php`** — All workshop JS functions:
  - `renderWorkshopPanel(wordKey, wordData, options)` — main renderer (replaces both `renderCard`'s inline workshop HTML and IWP's `renderWorkshop()`)
  - `renderSavedDeck(wordKey)` — saved writings display
  - `renderExampleFilters(wordKey)` — filter chip row
  - `switchTab(wordKey, tab, btn)` — critique/theme tab switching
  - `runCritique(wordKey)` — AI critique flow
  - `runTheme(wordKey)` — AI theme generation flow
  - `saveAIResult(btn)` — save flow (DB + localStorage guest stash)
  - `removeSaved(wordKey, idx)` — delete with confirmation
  - `toggleZaoju(key)` — expand/collapse
  - `refreshDeckWrap(wordKey)` — re-render deck after save/delete

The shared JS will accept a `context` object so it works in both views:
```js
// Card view passes: { traditional, definitions, example, extraExamples, senseIds, ... }
// IWP passes: { traditional: WORD.traditional, definitions: sense.definitions, examples: sense.examples, senseId: sense.id, ... }
```

## Step 2: Redesign example display with filter chips

**Remove** the current 4-tab system (Default Examples, My Writing, Community, 師父 Feedback).

**Replace with:**
- Default/editorial examples **always visible** at the top
- Below them, a row of multi-select filter chips:
  - `師父 Writings` — shows saved 師父-generated writings
  - `My Writings` — shows learner's own verified writings
  - `Community` — shows community contributions (stub for now)
- Chips are toggleable (on/off), multiple can be active simultaneously
- Active state: filled purple chip. Inactive: outlined
- Filtered writings appear below defaults, grouped by source type

**On the card view:** Chips sit between default examples and the "Write with 師父" input area.

**On the IWP:** Same chip row, same behaviour. Default examples render in the existing `wd-examples` section; filter chips + filtered writings appear directly below.

## Step 3: Update lexicon-live.blade.php

- Remove the inline workshop HTML from `renderCard()`
- Remove inline workshop JS functions (switchTab, runCritique, runTheme, saveAIResult, renderSavedDeck, removeSaved, etc.)
- Replace with `@include('partials.lexicon._workshop-css')` and `@include('partials.lexicon._workshop-js')`
- Call `renderWorkshopPanel(wordKey, wordData)` from `renderCard()`

## Step 4: Update word-detail.blade.php (IWP)

- Remove the current stub `renderWorkshop()` function and `wdSwitchWorkshopTab()`, `wdSaveWriting()`, `wdLoadWriting()`
- Add `@include('partials.lexicon._workshop-css')` and `@include('partials.lexicon._workshop-js')`
- Call `renderWorkshopPanel(senseId, senseData)` from the sense renderer
- Full 師父 functionality: critique, theme generation, save flow, delete with confirmation

## Step 5: Fix remaining issues from previous session

- **Save button text**: "Save Writing & 師父 Feedback" (already requested)
- **Guest flow**: Ensure localStorage stash → login redirect → auto-save-on-return works reliably
- **Generated vs verified chip**: Fix `source_type` persistence so generated stays generated after reload
- **Vertical text**: Chinese in saved writings right-justified, horizontal scroll on overflow
- **Scroll to new writing**: After save, scroll to the new writing (not top of list)

## Files touched

| File | Action |
|------|--------|
| `resources/views/partials/lexicon/_workshop-css.blade.php` | **NEW** — extracted CSS |
| `resources/views/partials/lexicon/_workshop-js.blade.php` | **NEW** — extracted JS |
| `resources/views/lexicon-live.blade.php` | Remove inline workshop code, use shared partials |
| `resources/views/word-detail.blade.php` | Remove stubs, use shared partials |

## Approach notes

- The shared JS will use a **data-attribute pattern** (`data-word-key`, `data-source`) for DOM scoping, avoiding ID collisions between card view (multiple cards) and IWP (single word, multiple senses)
- Filter chip state stored in `localStorage` per user preference (persists across page loads)
- The partial accepts view-specific config via a JS global (e.g. `window.__WORKSHOP_CONFIG = { view: 'card' | 'iwp' }`) to handle minor layout differences
