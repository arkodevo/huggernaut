<?php

namespace App\Http\Controllers;

use App\Models\DesignationGroup;
use App\Models\NoteType;
use App\Models\SearchLog;
use App\Models\LexiconGap;
use App\Models\SearchNotFound;
use App\Models\WordObject;
use App\Models\WordSense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

// Serves the live lexicon explorer view.
// Shapes word_sense rows into the WORDS JS array format expected by the
// client-side filter / card engine inherited from modular-lexicon-demo.html.
class ExploreController extends Controller
{
    // ── Slug mapping tables ───────────────────────────────────────────────────

    // DB channel slugs → demo JS channel values
    private const CHANNEL_MAP = [
        'channel-balanced' => 'balanced',
        'fluid'            => 'balanced',         // legacy fallback
        'spoken-only'      => 'spoken-only',
        'spoken-dominant'  => 'spoken-dominant',
        'written-dominant' => 'written-dominant',
        'written-only'     => 'written-only',
    ];

    // DB register slugs → demo JS register values (demo uses 'neutral' for standard)
    private const REGISTER_MAP = [
        'standard'   => 'neutral',
        'literary'   => 'literary',
        'formal'     => 'formal',
        'informal'   => 'informal',
        'colloquial' => 'colloquial',
        'slang'      => 'slang',
    ];

    // DB dimension slugs → demo JS dimension values
    // (dim-fluid is the DB slug for the 'fluid' semantic dimension concept)
    private const DIMENSION_MAP = [
        'dim-fluid' => 'fluid',
        'abstract'  => 'abstract',
        'concrete'  => 'concrete',
        'internal'  => 'internal',
        'external'  => 'external',
    ];

    // DB tocfl_level slugs → short form used by JS chips
    public const TOCFL_SLUG_MAP = [
        'tocfl-novice1'  => 'novice1',
        'tocfl-novice2'  => 'novice2',
        'tocfl-entry'    => 'entry',
        'tocfl-basic'    => 'basic',
        'tocfl-advanced' => 'advanced',
        'tocfl-high'     => 'high',
        'tocfl-fluency'  => 'fluency',
    ];

    // tocfl slug → integer level (used for the `level` field in WORDS)
    private const TOCFL_NUM_MAP = [
        'tocfl-novice1'  => 1,
        'tocfl-novice2'  => 2,
        'tocfl-entry'    => 3,
        'tocfl-basic'    => 4,
        'tocfl-advanced' => 5,
        'tocfl-high'     => 6,
        'tocfl-fluency'  => 7,
    ];

    // Designation slugs that belong to the register attribute
    private const REGISTER_SLUGS = ['standard', 'literary', 'formal', 'informal', 'colloquial', 'slang'];

    // Designation slugs that belong to the dimension attribute
    private const DIMENSION_SLUGS = ['abstract', 'concrete', 'internal', 'external', 'dim-fluid'];

    // sense_relation_type slug → refine proximity bucket
    private const REL_PROXIMITY = [
        'synonym_close'    => 'immediate',
        'synonym_related'  => 'close',
        'antonym'          => 'distant',
        'contrast'         => 'distant',
        'register_variant' => 'distant',
        // derivative · family_member · compound → word family section, not proximity refine
    ];

    // DB POS slugs → full English names matching the demo's POS_ABBR keys
    // (the card toggle system maps full name ↔ abbreviation; slugs alone break it)
    // DB slug → display abbreviation (Va-t / Vp-i / Vs-sep scheme)
    public const POS_DISPLAY_ABBR = [
        'V'       => 'Va-t',
        'Vi'      => 'Va-i',
        'Vsep'    => 'Va-sep',
        'Vpt'     => 'Vp-t',
        'Vp'      => 'Vp-i',
        'Vpsep'   => 'Vp-sep',
        'Vst'     => 'Vs-t',
        'Vs'      => 'Vs-i',
        'Vssep'   => 'Vs-sep',
        'Vsattr'  => 'Vs-attr',
        'Vspred'  => 'Vs-pred',
        'Vaux'    => 'Vaux',
        'Vcomp'   => 'Vcomp',
        'N'       => 'N',
        'M'       => 'M',
        'Adv'     => 'Adv',
        'Prep'    => 'Prep',
        'Conj'    => 'Conj',
        'Ptc'     => 'Ptc',
        'Aux'     => 'Aux',
        'Intj'    => 'Intj',
        'Det'     => 'Det',
        'Prn'     => 'Prn',
        'Num'     => 'Num',
        'IE'      => 'IE',
        'Ph'      => 'Ph',
        'CE'      => 'CE',
    ];

    public const POS_FULL_NAMES = [
        'V'       => 'Verb',
        'Vi'      => 'Intransitive Verb',
        'Vp'      => 'Process Verb (Intransitive)',
        'Vpsep'   => 'Vp-sep / Separable Process Verb',
        'Vpt'     => 'Process Verb (Transitive)',
        'Vs'      => 'Stative Verb',
        'Vsattr'  => 'Vs-attr / Stative Verb (Attributive)',
        'Vspred'  => 'Vs-pred / Stative Verb (Predicative)',
        'Vssep'   => 'Vs-sep / Separable Stative Verb',
        'Vst'     => 'State-Transitive Verb',
        'Vaux'    => 'Auxiliary Verb',
        'Vsep'    => 'V-sep / Separable Verb',
        'Vcomp'   => 'Verbal Complement',
        'N'       => 'Noun',
        'M'       => 'Measure Word',
        'Adv'     => 'Adverb',
        'Prep'    => 'Preposition',
        'Conj'    => 'Conjunction',
        'Ptc'     => 'Particle',
        'Aux'     => 'Auxiliary',
        'Intj'    => 'Interjection',
        'Det'     => 'Determiner',
        'Prn'     => 'Pronoun',
        'Num'     => 'Number',
        'IE'      => 'Idiomatic Expression',
        'Ph'      => 'Phrase',
    ];

    // Language ID → short code mapping
    private const LANG_CODE = [1 => 'en', 2 => 'zh'];

    // ── Notes helpers ────────────────────────────────────────────────────────

    /**
     * Batch-load notes from the normalized note_types schema.
     * Returns a nested collection: senseId → langCode → [ {slug, label, content}, ... ]
     *
     * @param  array       $senseIds
     * @param  array|null  $slugFilter  Optional slugs to include (e.g. ['formula', 'usage-note'])
     */
    private function loadNotes(array $senseIds, ?array $slugFilter = null): \Illuminate\Support\Collection
    {
        if (empty($senseIds)) {
            return collect();
        }

        $query = \DB::table('word_sense_notes')
            ->join('note_types', 'word_sense_notes.note_type_id', '=', 'note_types.id')
            ->join('note_type_labels', function ($join) {
                $join->on('note_types.id', '=', 'note_type_labels.note_type_id')
                     ->on('word_sense_notes.language_id', '=', 'note_type_labels.language_id');
            })
            ->whereIn('word_sense_notes.word_sense_id', $senseIds)
            ->select(
                'word_sense_notes.word_sense_id',
                'word_sense_notes.language_id',
                'note_types.slug',
                'note_types.sort_order',
                'note_type_labels.label',
                'word_sense_notes.content',
            );

        if ($slugFilter) {
            $query->whereIn('note_types.slug', $slugFilter);
        }

        return $query->orderBy('note_types.sort_order')
            ->get()
            ->groupBy('word_sense_id')
            ->map(function ($rows) {
                return $rows->groupBy(fn ($r) => self::LANG_CODE[$r->language_id] ?? 'en')
                    ->map(fn ($langRows) => $langRows->map(fn ($r) => [
                        'slug'    => $r->slug,
                        'label'   => $r->label,
                        'content' => $r->content,
                    ])->values()->all());
            });
    }

    /**
     * Get notes for a single sense, keyed by language code.
     * Returns: ['en' => [...], 'zh' => [...]]
     */
    private function loadSenseNotes(int $senseId, ?array $slugFilter = null): array
    {
        $all = $this->loadNotes([$senseId], $slugFilter);
        $senseNotes = $all->get($senseId, collect());
        $result = [];
        foreach (self::LANG_CODE as $code) {
            $langNotes = $senseNotes->get($code);
            if ($langNotes && !empty($langNotes)) {
                $result[$code] = $langNotes;
            }
        }
        return $result;
    }

    // ── Controller ────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        // Slim search index — lightweight payload for client-side search/filter.
        // Full word detail loads via /lexicon/{smartId} on click.
        // Use: php artisan cache:forget lexicon_words_slim   to bust after import.
        $words = cache()->remember('lexicon_words_slim', now()->addHours(24), function () {
            $wordObjects = WordObject::with([
                'senses' => fn ($q) => $q->orderBy('id')->with([
                    'pronunciation',
                    'channel',
                    'connotation',
                    'tocflLevel',
                    'domains',
                    'definitions' => fn ($q) => $q->where('language_id', 1)
                                                  ->orderBy('sort_order')
                                                  ->with('posLabel'),
                    'designations', // pivot — register + dimension
                    'senseRelations.relationType',
                ]),
            ])->published()->get();

            return $wordObjects
                ->map(fn ($w) => $this->shapeWordObjectSlim($w))
                ->filter()
                ->values()
                ->all();
        });

        // Cache domain groups too — rarely changes.
        $domainGroups = cache()->remember('lexicon_domain_groups', now()->addHours(24), function () {
            return DesignationGroup::with([
                'designations' => fn ($q) => $q->orderBy('sort_order')
                                               ->with(['labels' => fn ($q) => $q->whereIn('language_id', [1, 2])]),
                'labels'       => fn ($q) => $q->whereIn('language_id', [1, 2]),
            ])->orderBy('sort_order')->get()
              ->map(fn ($group) => [
                  'slug'     => $group->slug,
                  'label'    => $group->labels->firstWhere('language_id', 1)?->label
                                    ?? ucwords(str_replace('-', ' ', $group->slug)),
                  'label_zh' => $group->labels->firstWhere('language_id', 2)?->label,
                  'domains'  => $group->designations->map(fn ($d) => [
                      'slug'     => $d->slug,
                      'label'    => $d->labels->firstWhere('language_id', 1)?->label
                                        ?? ucwords(str_replace('-', ' ', $d->slug)),
                      'label_zh' => $d->labels->firstWhere('language_id', 2)?->label,
                  ])->values()->all(),
              ])->values()->all();
        });

        return view('lexicon-live', [
            'words'         => $words,
            'initialSearch' => $request->input('q', ''),
            'domainGroups'  => $domainGroups,
            'authUser'      => $this->authUserPayload(),
        ]);
    }

    // ── Shape one word_object (all senses) into a single WORDS array entry ──────
    // Meta attributes (register, channel, connotation, dimension, intensity, tocfl)
    // come from the first/primary sense. Definitions, examples, and relation
    // proximity buckets are aggregated across all senses.

    private function shapeWordObject(WordObject $word): ?array
    {
        $senses = $word->senses;

        if ($senses->isEmpty()) {
            return null;
        }

        $primary = $senses->first();

        // ── Meta from primary sense ───────────────────────────────────────────

        $registerDes = $primary->designations
            ->first(fn ($d) => in_array($d->slug, self::REGISTER_SLUGS));
        $dimensionDes = $primary->designations
            ->filter(fn ($d) => in_array($d->slug, self::DIMENSION_SLUGS));

        $register   = self::REGISTER_MAP[$registerDes?->slug ?? 'standard'] ?? 'neutral';
        $dimensions = $dimensionDes
            ->map(fn ($d) => self::DIMENSION_MAP[$d->slug] ?? $d->slug)
            ->values()->all();

        $channelSlug = $primary->channel?->slug ?? 'channel-balanced';
        $channel     = self::CHANNEL_MAP[$channelSlug] ?? $channelSlug;

        $tocflSlug  = $primary->tocflLevel?->slug;
        $tocflShort = $tocflSlug ? (self::TOCFL_SLUG_MAP[$tocflSlug] ?? null) : null;
        $tocflNum   = $tocflSlug ? (self::TOCFL_NUM_MAP[$tocflSlug]  ?? null) : null;

        // ── Definitions: all senses in order ─────────────────────────────────

        // Pre-load notes (formula + usage-note only for search index) via normalized schema
        $senseIds = $senses->pluck('id')->all();
        $allNotes = $this->loadNotes($senseIds, ['formula', 'usage-note']);

        $shapeDef = fn ($d) => [
            'pos' => self::POS_FULL_NAMES[$d->posLabel?->slug ?? ''] ?? ($d->posLabel?->slug ?? ''),
            'def' => $d->definition_text,
        ];

        // Flat definitions grouped by language for search card rendering
        $allDefs = $senses->flatMap(fn ($s) => $s->definitions);
        $definitions = [
            'en' => $allDefs->where('language_id', 1)->map($shapeDef)->values()->all(),
            'zh' => $allDefs->where('language_id', 2)->map($shapeDef)->values()->all(),
        ];

        // ── Sense groups: definitions grouped by sense with per-sense attributes ──
        // Used by the SRP card to render attribute chips under each sense block.

        $senseGroups = $senses->map(function ($s) use ($allNotes) {
            $registerDes = $s->designations
                ->first(fn ($d) => in_array($d->slug, self::REGISTER_SLUGS));
            $dimensionDes = $s->designations
                ->filter(fn ($d) => in_array($d->slug, self::DIMENSION_SLUGS));

            $senseNotes = $allNotes->get($s->id, collect());
            $notes = [];
            foreach (self::LANG_CODE as $code) {
                $langNotes = $senseNotes->get($code);
                if ($langNotes && !empty($langNotes)) {
                    $notes[$code] = $langNotes;
                }
            }

            $defsByLang = $s->definitions->groupBy('language_id');
            $shapeD = fn ($d) => [
                'pos' => self::POS_FULL_NAMES[$d->posLabel?->slug ?? ''] ?? ($d->posLabel?->slug ?? ''),
                'def' => $d->definition_text,
            ];

            return [
                'definitions' => [
                    'en' => ($defsByLang->get(1) ?? collect())->map($shapeD)->values()->all(),
                    'zh' => ($defsByLang->get(2) ?? collect())->map($shapeD)->values()->all(),
                ],
                'notes' => $notes,
                'register'    => self::REGISTER_MAP[$registerDes?->slug ?? 'standard'] ?? 'neutral',
                'connotation' => $s->connotation?->slug ?? 'neutral',
                'channel'     => self::CHANNEL_MAP[$s->channel?->slug ?? 'channel-balanced'] ?? ($s->channel?->slug ?? 'channel-balanced'),
                'dimension'   => $dimensionDes->map(fn ($d) => self::DIMENSION_MAP[$d->slug] ?? $d->slug)->values()->all(),
                'intensity'   => $s->intensity ?? 2,
                'tocfl'       => $s->tocflLevel?->slug ? (self::TOCFL_SLUG_MAP[$s->tocflLevel->slug] ?? null) : null,
            ];
        })->values()->all();

        // ── Examples: one per sense, first becomes w.example, rest are extras ─

        // Pre-load example translations in one batch query
        $exampleIds = $senses->flatMap(fn ($s) => $s->examples->pluck('id'))->all();
        $exTranslations = ! empty($exampleIds)
            ? \DB::table('word_sense_example_translations')
                ->whereIn('word_sense_example_id', $exampleIds)
                ->get()
                ->groupBy('word_sense_example_id')
            : collect();

        $allExamples = $senses
            ->map(fn ($s) => $s->examples->first())
            ->filter()
            ->map(function ($e) use ($exTranslations) {
                $trans = $exTranslations->get($e->id, collect())->pluck('translation_text', 'language_id');
                return [
                    'cn'           => $e->chinese_text,
                    'en'           => $trans->get(1) ?? $e->english_text,
                    'translations' => $trans->all(),
                ];
            })
            ->values()->all();

        // ── Relation proximity: union across all senses ───────────────────────

        $relProximity = $senses
            ->flatMap(fn ($s) => $s->senseRelations
                ->map(fn ($r) => self::REL_PROXIMITY[$r->relationType?->slug ?? ''] ?? null))
            ->filter()->unique()->values()->all();

        return [
            'wordObjectId'    => $word->id,
            'smart_id'        => $word->smart_id,
            'traditional'     => $word->traditional,
            'simplified'      => $word->simplified ?? $word->traditional,
            'pinyin'          => $primary->pronunciation?->pronunciation_text ?? '',
            'definitions'     => $definitions,
            'senseGroups'     => $senseGroups,
            'relProximity'    => $relProximity,
            'family'          => new \stdClass(), // word family tree — Phase 1
            'definition'      => [
                'en' => $definitions['en'][0]['def'] ?? '',
                'zh' => $definitions['zh'][0]['def'] ?? '',
            ],
            'register'        => $register,
            'connotation'     => $primary->connotation?->slug ?? 'neutral',
            'channel'         => $channel,
            'dimension'       => $dimensions,
            'intensity'       => $primary->intensity ?? 2,
            'tocfl'           => $tocflShort,
            'level'           => $tocflNum,
            'domain'          => $primary->domains->first()?->slug,
            'allDomains'      => $senses->flatMap(fn ($s) => $s->domains->pluck('slug'))
                                    ->unique()->values()->all(),
            'domainPairs'     => $senses->flatMap(function ($s) {
                                    $first = $s->domains->first();
                                    if (! $first) return [];
                                    $others = $s->domains->slice(1);
                                    if ($others->isEmpty()) {
                                        return [['p' => $first->slug, 's' => null]];
                                    }
                                    return $others->map(fn ($d) => [
                                        'p' => $first->slug, 's' => $d->slug,
                                    ]);
                                })->unique(fn ($d) => $d['p'] . '~' . ($d['s'] ?? ''))
                                  ->values()->all(),
            'example'         => $allExamples[0] ?? ['cn' => '', 'en' => ''],
            'extraExamples'   => array_slice($allExamples, 1),
            // Bilingual notes for search surface matching (primary sense only)
            'notes'           => (function () use ($allNotes, $senses) {
                $primaryNotes = $allNotes->get($senses->first()?->id, collect());
                $result = [];
                foreach (self::LANG_CODE as $code) {
                    $langNotes = $primaryNotes->get($code);
                    if ($langNotes && !empty($langNotes)) {
                        $result[$code] = $langNotes;
                    }
                }
                return $result ?: new \stdClass();
            })(),
            'senseIds'        => $senses->pluck('id')->values()->all(),
            'alignment'       => $word->alignment,
        ];
    }

    // ── Slim shape: lightweight version for search index ─────────────────────
    // Contains only what's needed for: search matching, attribute filtering,
    // and slim result card rendering. Full detail loads on click via show().

    private function shapeWordObjectSlim(WordObject $word): ?array
    {
        $senses = $word->senses;

        if ($senses->isEmpty()) {
            return null;
        }

        $primary = $senses->first();

        // ── Meta from primary sense ───────────────────────────────────────────

        $registerDes = $primary->designations
            ->first(fn ($d) => in_array($d->slug, self::REGISTER_SLUGS));
        $dimensionDes = $primary->designations
            ->filter(fn ($d) => in_array($d->slug, self::DIMENSION_SLUGS));

        $register   = self::REGISTER_MAP[$registerDes?->slug ?? 'standard'] ?? 'neutral';
        $dimensions = $dimensionDes
            ->map(fn ($d) => self::DIMENSION_MAP[$d->slug] ?? $d->slug)
            ->values()->all();

        $channelSlug = $primary->channel?->slug ?? 'channel-balanced';
        $channel     = self::CHANNEL_MAP[$channelSlug] ?? $channelSlug;

        $tocflSlug  = $primary->tocflLevel?->slug;
        $tocflShort = $tocflSlug ? (self::TOCFL_SLUG_MAP[$tocflSlug] ?? null) : null;
        $tocflNum   = $tocflSlug ? (self::TOCFL_NUM_MAP[$tocflSlug]  ?? null) : null;

        // ── Definitions: grouped by language ─────────────────────────────

        $allDefs = $senses->flatMap(fn ($s) => $s->definitions);
        $shapeDef = fn ($d) => [
            'pos' => self::POS_FULL_NAMES[$d->posLabel?->slug ?? ''] ?? ($d->posLabel?->slug ?? ''),
            'def' => $d->definition_text,
        ];
        $definitions = [
            'en' => $allDefs->where('language_id', 1)->map($shapeDef)->values()->all(),
            'zh' => $allDefs->where('language_id', 2)->map($shapeDef)->values()->all(),
        ];

        // ── Pinyin: also build toneless version for search ──────────────────

        $pinyin = $primary->pronunciation?->pronunciation_text ?? '';
        $pinyinToneless = preg_replace('/[0-9]/', '', $pinyin);

        // ── Domains: flat slug list for filter matching ─────────────────────

        $allDomains = $senses->flatMap(fn ($s) => $s->domains->pluck('slug'))
            ->unique()->values()->all();

        // ── Relation proximity: union across all senses ───────────────────

        $relProximity = $senses
            ->flatMap(fn ($s) => $s->senseRelations
                ->map(fn ($r) => self::REL_PROXIMITY[$r->relationType?->slug ?? ''] ?? null))
            ->filter()->unique()->values()->all();

        return [
            'wordObjectId'    => $word->id,
            'smart_id'        => $word->smart_id,
            'traditional'     => $word->traditional,
            'simplified'      => $word->simplified ?? $word->traditional,
            'pinyin'          => $pinyin,
            'pinyinToneless'  => $pinyinToneless,
            'definitions'     => $definitions,
            'register'        => $register,
            'connotation'     => $primary->connotation?->slug ?? 'neutral',
            'channel'         => $channel,
            'dimension'       => $dimensions,
            'intensity'       => $primary->intensity ?? 2,
            'tocfl'           => $tocflShort,
            'level'           => $tocflNum,
            'allDomains'      => $allDomains,
            'relProximity'    => $relProximity,
            'alignment'       => $word->alignment,
        ];
    }

    // ── Individual word page ─────────────────────────────────────────────────

    public function show(string $smartId): View
    {
        $word = WordObject::with([
            'radical',
            'pronunciations.pronunciationSystem',
            'senses' => fn ($q) => $q->orderBy('id')->with([
                'pronunciation.pronunciationSystem',
                'channel',
                'connotation',
                'tocflLevel',
                'hskLevel',
                'domains'         => fn ($q) => $q->with(['labels' => fn ($q) => $q->whereIn('language_id', [1, 2])]),
                'definitions'     => fn ($q) => $q->where('language_id', 1)
                                                   ->orderBy('sort_order')
                                                   ->with('posLabel'),
                'designations',
                'examples'        => fn ($q) => $q->where('is_suppressed', false)
                                                  ->where('is_public', true)
                                                  ->orderBy('id'),
                'collocations',
                'senseRelations' => fn ($q) => $q->with('relationType'),
            ]),
        ])->where('smart_id', $smartId)->firstOrFail();

        $shaped = $this->shapeWordObjectDetail($word);

        // Build a word index for sentence segmentation (all word_objects).
        // Cached for 2 hours — invalidate via Cache::forget('word_index_slim') after imports.
        $wordIndex = Cache::remember('word_index_slim', 7200, function () {
            return WordObject::query()
                ->with(['senses' => fn ($q) => $q->orderBy('id')->limit(1)->with([
                    'pronunciation',
                    'definitions' => fn ($q) => $q->where('language_id', 1)->orderBy('sort_order')->limit(1)->with('posLabel'),
                    'tocflLevel',
                ])])
                ->get()
                ->mapWithKeys(function ($wo) {
                    $s = $wo->senses->first();
                    if (!$s) return [];
                    return [$wo->traditional => [
                        'smartId' => $wo->smart_id,
                        'trad'    => $wo->traditional,
                        'simp'    => $wo->simplified ?? $wo->traditional,
                        'pinyin'  => $s->pronunciation?->pronunciation_text ?? '',
                        'def'     => $s->definitions->first()?->definition_text ?? '',
                        'pos'     => self::POS_FULL_NAMES[$s->definitions->first()?->posLabel?->slug ?? ''] ?? '',
                        'tocfl'   => self::TOCFL_SLUG_MAP[$s->tocflLevel?->slug ?? ''] ?? null,
                    ]];
                })->filter()->all();
        });

        return view('word-detail', [
            'word'      => $shaped,
            'smartId'   => $smartId,
            'wordIndex' => $wordIndex,
            'authUser'  => $this->authUserPayload(),
        ]);
    }

    // ── Shape one word_object with full sense-by-sense detail ────────────────

    private function shapeWordObjectDetail(WordObject $word): array
    {
        $senses = $word->senses;

        // Break smart_id into individual characters with their own smart_ids
        $characters = [];
        $chars = mb_str_split($word->traditional);
        foreach ($chars as $char) {
            $cp = dechex(mb_ord($char));
            $characters[] = [
                'char'    => $char,
                'smartId' => 'u' . $cp,
            ];
        }

        // Pre-load all notes for all senses in one query (no N+1)
        $allSenseIds = $senses->pluck('id')->all();
        $allNotes = $this->loadNotes($allSenseIds);

        // Shape each sense independently
        $shapedSenses = $senses->map(function (WordSense $sense) use ($allNotes) {
            // Multi-select: register
            $registerDes = $sense->designations
                ->first(fn ($d) => in_array($d->slug, self::REGISTER_SLUGS));
            $register = self::REGISTER_MAP[$registerDes?->slug ?? 'standard'] ?? 'neutral';

            // Multi-select: dimensions
            $dimensionDes = $sense->designations
                ->filter(fn ($d) => in_array($d->slug, self::DIMENSION_SLUGS));
            $dimensions = $dimensionDes
                ->map(fn ($d) => self::DIMENSION_MAP[$d->slug] ?? $d->slug)
                ->values()->all();

            // Single-select spectrum
            $channelSlug = $sense->channel?->slug ?? 'fluid';
            $channel     = self::CHANNEL_MAP[$channelSlug] ?? $channelSlug;
            $tocflSlug   = $sense->tocflLevel?->slug;
            $tocflShort  = $tocflSlug ? (self::TOCFL_SLUG_MAP[$tocflSlug] ?? null) : null;
            $hskSlug     = $sense->hskLevel?->slug;

            // Bilingual notes from normalized word_sense_notes + note_types
            $senseNotesRaw = $allNotes->get($sense->id, collect());
            $senseNotes = [];
            foreach (self::LANG_CODE as $code) {
                $langNotes = $senseNotesRaw->get($code);
                if ($langNotes && !empty($langNotes)) {
                    $senseNotes[$code] = $langNotes;
                }
            }

            // Definitions grouped by language (1=EN, 2=ZH-TW)
            $defsByLang = $sense->definitions->groupBy('language_id');
            $shapeDef = fn ($d) => [
                'pos'     => self::POS_FULL_NAMES[$d->posLabel?->slug ?? ''] ?? ($d->posLabel?->slug ?? ''),
                'posAbbr' => $d->posLabel?->slug ?? '',
                'def'     => $d->definition_text,
            ];

            $definitions = [
                'en' => ($defsByLang->get(1) ?? collect())->map($shapeDef)->values()->all(),
                'zh' => ($defsByLang->get(2) ?? collect())->map($shapeDef)->values()->all(),
            ];

            // Examples — with per-language translations from word_sense_example_translations
            $examples = $sense->examples->map(function ($e) {
                $translations = \DB::table('word_sense_example_translations')
                    ->where('word_sense_example_id', $e->id)
                    ->pluck('translation_text', 'language_id');

                return [
                    'id'           => $e->id,
                    'cn'           => $e->chinese_text,
                    'en'           => $translations->get(1) ?? $e->english_text,  // legacy fallback
                    'translations' => $translations->all(),
                    'source'       => $e->source,
                    'theme'        => $e->theme,
                    'isSuppressed' => $e->is_suppressed,
                    'hasAudio'     => is_array($e->has_audio) ? $e->has_audio : (json_decode($e->has_audio ?? '{}', true) ?: (object) []),
                ];
            })->values()->all();

            // Domain labels (EN + ZH) — from many-to-many pivot
            $shapeDomainDesig = fn ($d) => [
                'slug' => $d->slug,
                'en'   => $d->labels?->firstWhere('language_id', 1)?->label
                            ?? ucwords(str_replace('-', ' ', $d->slug)),
                'zh'   => $d->labels?->firstWhere('language_id', 2)?->label,
            ];

            $domainShaped = $sense->domains->first() ? $shapeDomainDesig($sense->domains->first()) : null;

            $secondaryDomainsShaped = $sense->domains
                ->slice(1)
                ->map($shapeDomainDesig)
                ->values()->all();

            // Collocations
            // Collocations: text-based, with render-time linking
            $collTexts = $sense->collocations->pluck('collocation_text')->filter()->values()->all();
            $collWordMap = [];
            if (!empty($collTexts)) {
                $collWordMap = WordObject::whereIn('traditional', $collTexts)
                    ->pluck('smart_id', 'traditional')->all();
            }
            $collocations = collect($collTexts)->map(fn ($text) => [
                'text'     => $text,
                'smartId'  => $collWordMap[$text] ?? null,
                'exists'   => isset($collWordMap[$text]),
            ])->values()->all();

            // Relations: merge forward + inverse, group by type
            $relations = [
                'synonymClose'    => [],
                'synonymRelated'  => [],
                'antonym'         => [],
                'contrast'        => [],
                'registerVariant' => [],
            ];
            $family = [
                'derivatives'   => [],
                'familyMembers' => [],
                'compounds'     => [],
            ];

            // Build a lookup of word_objects for linking relation targets
            $allRelatedTexts = $sense->senseRelations->pluck('related_word_text')->unique()->values();
            $relatedWords = $allRelatedTexts->isNotEmpty()
                ? WordObject::whereIn('traditional', $allRelatedTexts)
                    ->with(['senses' => fn ($q) => $q->with([
                        'pronunciation',
                        'definitions' => fn ($q) => $q->where('language_id', 1)->orderBy('sort_order')->with('posLabel'),
                        'tocflLevel',
                    ])])
                    ->get()
                    ->keyBy('traditional')
                : collect();

            $shapeRelated = function (string $text, ?string $note = null) use ($relatedWords) {
                $word = $relatedWords->get($text);
                $sense = $word?->senses->first();
                return [
                    'traditional' => $text,
                    'smartId'     => $word?->smart_id,
                    'pinyin'      => $sense?->pronunciation?->pronunciation_text ?? '',
                    'pos'         => $sense ? (self::POS_FULL_NAMES[$sense->definitions->first()?->posLabel?->slug ?? ''] ?? '') : '',
                    'posAbbr'     => $sense?->definitions->first()?->posLabel?->slug ?? '',
                    'def'         => $sense?->definitions->first()?->definition_text ?? '',
                    'tocfl'       => self::TOCFL_SLUG_MAP[$sense?->tocflLevel?->slug ?? ''] ?? null,
                    'note'        => $note,
                    'exists'      => (bool) $word,
                ];
            };

            // Forward relations (this sense → related word text)
            foreach ($sense->senseRelations as $rel) {
                $typeSlug = $rel->relationType?->slug ?? '';
                $shaped = $shapeRelated($rel->related_word_text, $rel->editorial_note);

                match ($typeSlug) {
                    'synonym_close'    => $relations['synonymClose'][]    = $shaped,
                    'synonym_related'  => $relations['synonymRelated'][]  = $shaped,
                    'antonym'          => $relations['antonym'][]         = $shaped,
                    'contrast'         => $relations['contrast'][]        = $shaped,
                    'register_variant' => $relations['registerVariant'][] = $shaped,
                    'derivative'       => $family['derivatives'][]        = $shaped,
                    'family_member'    => $family['familyMembers'][]      = $shaped,
                    'compound'         => $family['compounds'][]          = $shaped,
                    default            => null,
                };
            }

            return [
                'id'              => $sense->id,
                'pinyin'          => $sense->pronunciation?->pronunciation_text ?? '',
                'definitions'     => $definitions,
                'examples'        => $examples,
                'register'        => $register,
                'connotation'     => $sense->connotation?->slug ?? 'neutral',
                'channel'         => $channel,
                'dimensions'      => $dimensions,
                'intensity'       => $sense->intensity ?? 2,
                'tocfl'           => $tocflShort,
                'hsk'             => $hskSlug,
                'domain'           => $domainShaped,
                'secondaryDomains' => $secondaryDomainsShaped,
                'notes'           => $senseNotes,
                'collocations'    => $collocations,
                'relations'       => $relations,
                'family'          => $family,
                'alignment'       => $sense->alignment,
                'source'          => $sense->source,
            ];
        })->values()->all();

        // ── Community signals (Phase B) ───────────────────────────────────────
        // Inject per-sense affirm + dispute counts + whether the current user
        // has already cast each signal. Aggregated in a handful of grouped
        // queries, never per-sense, so the LWP stays cheap regardless of how
        // heavily a sense has been engaged with.
        $senseIds = array_column($shapedSenses, 'id');
        $affirmCounts = \DB::table('affirmations')
            ->whereIn('word_sense_id', $senseIds)
            ->select('word_sense_id', \DB::raw('COUNT(*) as c'))
            ->groupBy('word_sense_id')
            ->pluck('c', 'word_sense_id');
        // Dispute counts include pending + under_review only. Resolved disputes
        // are archival — they shouldn't pad the LWP "live dispute" count.
        $disputeCounts = \DB::table('disputations')
            ->whereIn('word_sense_id', $senseIds)
            ->whereIn('status', ['pending', 'under_review'])
            ->select('word_sense_id', \DB::raw('COUNT(*) as c'))
            ->groupBy('word_sense_id')
            ->pluck('c', 'word_sense_id');
        $isAuthed     = \Illuminate\Support\Facades\Auth::check();
        $affirmedByMe = $isAuthed
            ? \DB::table('affirmations')
                ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                ->whereIn('word_sense_id', $senseIds)
                ->pluck('word_sense_id')
                ->flip()
            : collect();
        $disputedByMe = $isAuthed
            ? \DB::table('disputations')
                ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                ->whereIn('word_sense_id', $senseIds)
                ->whereIn('status', ['pending', 'under_review'])
                ->pluck('word_sense_id')
                ->flip()
            : collect();
        foreach ($shapedSenses as &$s) {
            $s['affirmCount']  = (int) ($affirmCounts[$s['id']] ?? 0);
            $s['affirmedByMe'] = $affirmedByMe->has($s['id']);
            $s['disputeCount'] = (int) ($disputeCounts[$s['id']] ?? 0);
            $s['disputedByMe'] = $disputedByMe->has($s['id']);
        }
        unset($s);

        // Aggregate family tree across all senses, deduplicated by smartId
        $allFamily = ['derivatives' => [], 'familyMembers' => [], 'compounds' => []];
        foreach ($shapedSenses as $s) {
            foreach (['derivatives', 'familyMembers', 'compounds'] as $group) {
                foreach ($s['family'][$group] as $item) {
                    $allFamily[$group][$item['smartId']] = $item;
                }
            }
        }
        $allFamily = array_map(fn ($items) => array_values($items), $allFamily);

        return [
            'wordObjectId'    => $word->id,
            'traditional'     => $word->traditional,
            'simplified'      => $word->simplified ?? $word->traditional,
            'smartId'         => $word->smart_id,
            'characters'      => $characters,
            'radical'         => $word->radical ? [
                'character'   => $word->radical->character,
                'meaning'     => $word->radical->meaning_en,
                'meaningZh'   => $word->radical->meaning_zh,
                'strokeCount' => $word->radical->stroke_count,
            ] : null,
            'strokesTrad'     => $word->strokes_trad,
            'strokesSimp'     => $word->strokes_simp,
            'structure'       => $word->structure,
            'pronunciations'  => $word->pronunciations->map(fn ($p) => [
                'id'        => $p->id,
                'text'      => $p->pronunciation_text,
                'system'    => $p->pronunciationSystem?->slug ?? 'pinyin',
                'isPrimary' => (bool) $p->is_primary,
                'hasAudio'  => is_array($p->has_audio) ? $p->has_audio : (json_decode($p->has_audio ?? '{}', true) ?: (object) []),
            ])->values()->all(),
            'senses'          => $shapedSenses,
            'family'          => $allFamily,
            'alignment'       => $word->alignment,
            'subtlexRank'     => $word->subtlex_rank,
            'subtlexPpm'      => $word->subtlex_ppm ? (float) $word->subtlex_ppm : null,
            'subtlexCd'       => $word->subtlex_cd  ? (float) $word->subtlex_cd  : null,
        ];
    }

    // ── Related words: beginning with / containing a character ───────────────

    public function relatedWords(string $character): JsonResponse
    {
        // Only accept single characters (safety)
        if (mb_strlen($character) !== 1) {
            return response()->json(['beginning' => [], 'containing' => []]);
        }

        $shapeWord = function (WordObject $wo) {
            $s = $wo->senses->first();
            if (!$s) return null;
            return [
                'traditional' => $wo->traditional,
                'smartId'     => $wo->smart_id,
                'pinyin'      => $s->pronunciation?->pronunciation_text ?? '',
                'pos'         => self::POS_FULL_NAMES[$s->definitions->first()?->posLabel?->slug ?? ''] ?? '',
                'posAbbr'     => $s->definitions->first()?->posLabel?->slug ?? '',
                'def'         => $s->definitions->first()?->definition_text ?? '',
                'tocfl'       => self::TOCFL_SLUG_MAP[$s->tocflLevel?->slug ?? ''] ?? null,
            ];
        };

        $baseQuery = fn () => WordObject::with([
            'senses' => fn ($q) => $q->orderBy('id')->limit(1)->with([
                'pronunciation',
                'definitions' => fn ($q) => $q->where('language_id', 1)->orderBy('sort_order')->limit(1)->with('posLabel'),
                'tocflLevel',
            ]),
        ]);

        // Words beginning with the character (multi-char only, excluding exact match)
        $beginning = $baseQuery()
            ->where('traditional', 'like', $character . '%')
            ->where('traditional', '!=', $character)
            ->limit(20)
            ->get()
            ->map($shapeWord)
            ->filter()
            ->values()
            ->all();

        // Words containing the character (not at the start)
        $containing = $baseQuery()
            ->where('traditional', 'like', '%' . $character . '%')
            ->where('traditional', 'not like', $character . '%')
            ->where('traditional', '!=', $character)
            ->limit(20)
            ->get()
            ->map($shapeWord)
            ->filter()
            ->values()
            ->all();

        return response()->json([
            'beginning'  => $beginning,
            'containing' => $containing,
        ]);
    }

    // ── Search logging (called from frontend JS) ──────────────────────────────

    public function logSearch(Request $request): JsonResponse
    {
        $request->validate([
            'query'         => 'required|string|max:255',
            'results_count' => 'required|integer|min:0',
            'search_type'   => 'nullable|string|in:word,sentence',
            'known_count'   => 'nullable|integer|min:0',
            'unknown_count' => 'nullable|integer|min:0',
            'not_found'     => 'nullable|array',
            'not_found.*'   => 'string|max:16',
            'filters'       => 'nullable|array',
        ]);

        $log = SearchLog::create([
            'user_id'       => Auth::id(),
            'session_id'    => Session::getId(),
            'user_role'     => Auth::user()?->role,
            'search_type'   => $request->input('search_type', 'word'),
            'query'         => $request->input('query'),
            'results_count' => $request->input('results_count'),
            'known_count'   => $request->input('known_count', 0),
            'unknown_count' => $request->input('unknown_count', 0),
            'filters'       => $request->input('filters'),
        ]);

        // Bulk-insert not-found characters
        $notFound = $request->input('not_found', []);
        if (! empty($notFound)) {
            $rows = array_map(fn (string $char) => [
                'search_log_id' => $log->id,
                'character'     => $char,
                'created_at'    => now(),
            ], array_unique($notFound));

            SearchNotFound::insert($rows);

            // Auto-populate lexicon_gaps for new characters
            $uniqueChars = array_unique($notFound);
            $existing = LexiconGap::whereIn('character', $uniqueChars)->pluck('character')->all();
            $newChars = array_diff($uniqueChars, $existing);
            if (! empty($newChars)) {
                $gapRows = array_map(fn (string $c) => [
                    'character'  => $c,
                    'status'     => 'pending',
                    'created_at' => now(),
                ], array_values($newChars));
                LexiconGap::insert($gapRows);
            }
        }

        return response()->json(['ok' => true]);
    }

    // ── Auth payload for client-side __AUTH injection ──────────────────────────

    public function authUserPayload(): ?array
    {
        if (! Auth::check()) {
            return null;
        }

        $user = Auth::user();

        return [
            'id'             => $user->id,
            'name'           => $user->name,
            'uiPreferences'  => $user->ui_preferences ?? [],
            'savedWordIds'   => $user->savedWords()->pluck('word_object_id')->all(),
            'fluencyLevel'   => $user->fluency_level,
            'shifuPersona'   => $user->shifu_persona ?? 'dragon',
            'defaultWritingsPublic'    => (bool) ($user->default_writings_public ?? true),
            'defaultDisputesAnonymous' => (bool) ($user->default_disputes_anonymous ?? false),
            'savedExamples'  => $user->savedExamples()
                ->select('id', 'word_sense_id', 'chinese_text', 'english_text', 'original_chinese_text', 'ai_verified', 'ai_feedback', 'source_type', 'assessed_level', 'assessed_mastery', 'mastery_guidance', 'is_public', 'created_at')
                ->with(['grammarPatterns:id,slug'])
                ->get()
                ->map(function ($ex) {
                    $arr = $ex->toArray();
                    $arr['grammar_patterns'] = $ex->grammarPatterns->map(fn ($gp) => [
                        'slug'   => $gp->slug,
                        'status' => $gp->pivot->status,
                        'note'   => $gp->pivot->note,
                    ])->values()->all();
                    return $arr;
                }),
            'collections'    => $user->collections()
                ->with('wordObjects:word_objects.id')
                ->get()
                ->map(fn ($c) => [
                    'id'            => $c->id,
                    'name'          => $c->name,
                    'wordObjectIds' => $c->wordObjects->pluck('id'),
                ]),
        ];
    }
}
