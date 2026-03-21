<script>
// ── Preference Sync: localStorage ↔ DB ─────────────────────────────────────
// Anonymous users: localStorage only (zero change).
// Logged-in users: localStorage for instant UI + debounced PATCH to DB.
// On page load (logged in): DB values override localStorage; missing DB keys
// get backfilled from localStorage (one-time migration).
(function() {
  if (!window.__AUTH) return;

  var csrfToken = document.querySelector('meta[name="csrf-token"]');
  if (!csrfToken) return;
  var token = csrfToken.content;

  // Keys we sync (flat prefs — not per-sense notes or per-sense writing)
  var SYNC_KEYS = [
    'scriptMode', 'langMode', 'iconsMode', 'pinyinMode',
    'textDir', 'workshopDefault', 'wdViewMode',
    'posMode', 'currentLevel', 'fontScale', 'customScenarios'
  ];

  // Also sync section visibility / collapse state (dynamic keys)
  var SECTION_PREFIXES = ['wdSection_', 'wdOpen_'];

  // ── Page-load: DB → localStorage (with backfill) ──
  var dbPrefs = window.__AUTH.uiPreferences || {};

  // One-time cleanup: remove activeScenario from DB if present (no longer synced)
  if (dbPrefs.activeScenario !== undefined) {
    fetch('/api/preferences', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
      body: JSON.stringify({ activeScenario: null }),
    });
    delete dbPrefs.activeScenario;
  }
  var backfill = {};
  var hasBackfill = false;

  SYNC_KEYS.forEach(function(key) {
    if (dbPrefs[key] !== undefined && dbPrefs[key] !== null) {
      localStorage.setItem(key, dbPrefs[key]);
    } else {
      var lsVal = localStorage.getItem(key);
      if (lsVal !== null) {
        backfill[key] = lsVal;
        hasBackfill = true;
      }
    }
  });

  // Backfill section keys from DB
  Object.keys(dbPrefs).forEach(function(key) {
    for (var i = 0; i < SECTION_PREFIXES.length; i++) {
      if (key.indexOf(SECTION_PREFIXES[i]) === 0) {
        localStorage.setItem(key, dbPrefs[key]);
        return;
      }
    }
  });

  // Backfill section keys from localStorage to DB
  for (var i = 0; i < localStorage.length; i++) {
    var key = localStorage.key(i);
    for (var j = 0; j < SECTION_PREFIXES.length; j++) {
      if (key.indexOf(SECTION_PREFIXES[j]) === 0 && dbPrefs[key] === undefined) {
        backfill[key] = localStorage.getItem(key);
        hasBackfill = true;
      }
    }
  }

  // Push backfill to DB once
  if (hasBackfill) {
    fetch('/api/preferences', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
      body: JSON.stringify(backfill),
    });
  }

  // ── Live sync: debounced PATCH on preference change ──
  var timer, pending = {};

  window.syncPref = function(key, value) {
    if (!window.__AUTH) return;
    pending[key] = value;
    clearTimeout(timer);
    timer = setTimeout(function() {
      fetch('/api/preferences', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
        body: JSON.stringify(pending),
      });
      pending = {};
    }, 300);
  };
})();
</script>
