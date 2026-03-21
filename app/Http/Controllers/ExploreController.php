<?php

namespace App\Http\Controllers;

use App\Models\DesignationGroup;
use App\Models\WordObject;
use App\Models\WordSense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// Serves the live lexicon explorer view.
// Shapes word_sense rows into the WORDS JS array format expected by the
// client-side filter / card engine inherited from modular-lexicon-demo.html.
class ExploreController extends Controller
{
    // ── Slug mapping tables ───────────────────────────────────────────────────

    // DB channel slugs → demo JS channel values
    private const CHANNEL_MAP = [
        'fluid'            => 'fluid',           // spoken & written — passes through as 'fluid'
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
    // (dim-fluid avoids slug collision with channel 'fluid' in the DB;
    //  in the JS dimension filter it's just 'fluid')
    private const DIMENSION_MAP = [
        'dim-fluid' => 'fluid',
        'abstract'  => 'abstract',
        'concrete'  => 'concrete',
        'internal'  => 'internal',
        'external'  => 'external',
    ];

    // DB tocfl_level slugs → short form used by JS chips
    public const TOCFL_SLUG_MAP = [
        'tocfl-prep'     => 'prep',
        'tocfl-entry'    => 'entry',
        'tocfl-basic'    => 'basic',
        'tocfl-advanced' => 'advanced',
        'tocfl-high'     => 'high',
        'tocfl-fluency'  => 'fluency',
    ];

    // tocfl slug → integer level (used for the `level` field in WORDS)
    private const TOCFL_NUM_MAP = [
        'tocfl-prep'     => 1,
        'tocfl-entry'    => 2,
        'tocfl-basic'    => 3,
        'tocfl-advanced' => 4,
        'tocfl-high'     => 5,
        'tocfl-fluency'  => 6,
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
    public const POS_FULL_NAMES = [
        'V'       => 'Verb',
        'Vi'      => 'Intransitive Verb',
        'Vp'      => 'Process Verb',
        'Vpsep'   => 'Vp-sep / Separable Process Verb',
        'Vpt'     => 'Process Verb (Telic)',
        'Vs'      => 'Stative Verb',
        'Vsattr'  => 'Vs-attr / Stative Verb (Attributive)',
        'Vspred'  => 'Vs-pred / Stative Verb (Predicative)',
        'Vssep'   => 'Vs-sep / Separable Stative Verb',
        'Vst'     => 'State-Transitive Verb',
        'Vaux'    => 'Auxiliary Verb',
        'Vsep'    => 'V-sep / Separable Verb',
        'N'       => 'Noun',
        'M'       => 'Measure Word',
        'Adv'     => 'Adverb',
        'Prep'    => 'Preposition',
        'Conj'    => 'Conjunction',
        'Ptc'     => 'Particle',
        'Det'     => 'Determiner',
        'Prn'     => 'Pronoun',
        'Num'     => 'Number',
        'IE'      => 'Idiomatic Expression',
        'Ph'      => 'Phrase',
    ];

    // ── Controller ────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $wordObjects = WordObject::with([
            'senses' => fn ($q) => $q->orderBy('id')->with([
                'pronunciation',
                'channel',
                'connotation',
                'tocflLevel',
                'domains' => fn ($q) => $q->with(['labels' => fn ($q) => $q->whereIn('language_id', [1, 2])]),
                'definitions' => fn ($q) => $q->where('language_id', 1)
                                              ->orderBy('sort_order')
                                              ->with('posLabel'),
                'designations', // pivot — register + dimension
                'examples'      => fn ($q) => $q->where('is_suppressed', false)
                                               ->where('is_public', true),
                'senseRelations.relationType',
            ]),
        ])->get();

        // One JS WORDS entry per word_object — all senses aggregated into definitions[].
        $words = $wordObjects
            ->map(fn ($w) => $this->shapeWordObject($w))
            ->filter()
            ->values();

        // ── Domain groups for the cascading filter UI ─────────────────────────
        // Build: [{slug, label(EN), label_zh, domains:[{slug, label(EN), label_zh}]}]
        // Labels for both EN (language_id 1) and ZH-TW (language_id 2) are loaded
        // so the client can toggle between languages on the domain chip.
        $domainGroups = DesignationGroup::with([
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

        $channelSlug = $primary->channel?->slug ?? 'fluid';
        $channel     = self::CHANNEL_MAP[$channelSlug] ?? $channelSlug;

        $tocflSlug  = $primary->tocflLevel?->slug;
        $tocflShort = $tocflSlug ? (self::TOCFL_SLUG_MAP[$tocflSlug] ?? null) : null;
        $tocflNum   = $tocflSlug ? (self::TOCFL_NUM_MAP[$tocflSlug]  ?? null) : null;

        // ── Definitions: all senses in order ─────────────────────────────────
        // Each entry carries its own formula + usageNote for per-def display.

        $definitions = $senses->flatMap(fn ($s) => $s->definitions->map(fn ($d) => [
            'pos'       => self::POS_FULL_NAMES[$d->posLabel?->slug ?? ''] ?? ($d->posLabel?->slug ?? ''),
            'def'       => $d->definition_text,
            'formula'   => $d->formula ?? '',
            'usageNote' => $d->usage_note ?? '',
        ]))->values()->all();

        // ── Examples: one per sense, first becomes w.example, rest are extras ─

        $allExamples = $senses
            ->map(fn ($s) => $s->examples->first())
            ->filter()
            ->map(fn ($e) => ['cn' => $e->chinese_text, 'en' => $e->english_text])
            ->values()->all();

        // ── Relation proximity: union across all senses ───────────────────────

        $relProximity = $senses
            ->flatMap(fn ($s) => $s->senseRelations
                ->map(fn ($r) => self::REL_PROXIMITY[$r->relationType?->slug ?? ''] ?? null))
            ->filter()->unique()->values()->all();

        return [
            'smart_id'        => $word->smart_id,
            'traditional'     => $word->traditional,
            'simplified'      => $word->simplified ?? $word->traditional,
            'pinyin'          => $primary->pronunciation?->pronunciation_text ?? '',
            'definitions'     => $definitions,
            'relProximity'    => $relProximity,
            'family'          => new \stdClass(), // word family tree — Phase 1
            'definition'      => $definitions[0]['def'] ?? '',
            'register'        => $register,
            'connotation'     => $primary->connotation?->slug ?? 'neutral',
            'channel'         => $channel,
            'dimension'       => $dimensions,
            'intensity'       => $primary->intensity ?? 2,
            'tocfl'           => $tocflShort,
            'level'           => $tocflNum,
            'domain'          => $primary->domains->firstWhere('pivot.is_primary', true)?->slug,
            'allDomains'      => $senses->flatMap(fn ($s) => $s->domains->pluck('slug'))
                                    ->unique()->values()->all(),
            'domainPairs'     => $senses->flatMap(function ($s) {
                                    $primary = $s->domains->firstWhere('pivot.is_primary', true);
                                    if (! $primary) return [];
                                    $secondaries = $s->domains->where('pivot.is_primary', false);
                                    if ($secondaries->isEmpty()) {
                                        return [['p' => $primary->slug, 's' => null]];
                                    }
                                    return $secondaries->map(fn ($d) => [
                                        'p' => $primary->slug, 's' => $d->slug,
                                    ]);
                                })->unique(fn ($d) => $d['p'] . '~' . ($d['s'] ?? ''))
                                  ->values()->all(),
            'example'         => $allExamples[0] ?? ['cn' => '', 'en' => ''],
            'extraExamples'   => array_slice($allExamples, 1),
            // Fallbacks for search surface matching
            'usageNote'       => $definitions[0]['usageNote'] ?? '',
            'formula'         => $definitions[0]['formula'] ?? '',
            'senseIds'        => $senses->pluck('id')->values()->all(),
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
                'senseRelations'  => fn ($q) => $q->with([
                    'relationType',
                    'relatedSense' => fn ($q) => $q->with([
                        'wordObject',
                        'pronunciation',
                        'definitions' => fn ($q) => $q->where('language_id', 1)
                                                       ->orderBy('sort_order')
                                                       ->with('posLabel'),
                        'tocflLevel',
                    ]),
                ]),
                'inverseSenseRelations' => fn ($q) => $q->with([
                    'relationType',
                    'wordSense' => fn ($q) => $q->with([
                        'wordObject',
                        'pronunciation',
                        'definitions' => fn ($q) => $q->where('language_id', 1)
                                                       ->orderBy('sort_order')
                                                       ->with('posLabel'),
                        'tocflLevel',
                    ]),
                ]),
            ]),
        ])->where('smart_id', $smartId)->firstOrFail();

        $shaped = $this->shapeWordObjectDetail($word);

        // Build a word index for sentence segmentation (all word_objects)
        $wordIndex = WordObject::query()
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

        // Shape each sense independently
        $shapedSenses = $senses->map(function (WordSense $sense) {
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

            // Definitions
            $definitions = $sense->definitions->map(fn ($d) => [
                'pos'       => self::POS_FULL_NAMES[$d->posLabel?->slug ?? ''] ?? ($d->posLabel?->slug ?? ''),
                'posAbbr'   => $d->posLabel?->slug ?? '',
                'def'       => $d->definition_text,
                'formula'   => $d->formula ?? '',
                'usageNote' => $d->usage_note ?? '',
            ])->values()->all();

            // Examples
            $examples = $sense->examples->map(fn ($e) => [
                'id'     => $e->id,
                'cn'     => $e->chinese_text,
                'en'     => $e->english_text,
                'source' => $e->source,
                'theme'  => $e->theme,
            ])->values()->all();

            // Domain labels (EN + ZH) — from many-to-many pivot
            $shapeDomainDesig = fn ($d) => [
                'slug' => $d->slug,
                'en'   => $d->labels?->firstWhere('language_id', 1)?->label
                            ?? ucwords(str_replace('-', ' ', $d->slug)),
                'zh'   => $d->labels?->firstWhere('language_id', 2)?->label,
            ];

            $primaryDomain = $sense->domains->firstWhere('pivot.is_primary', true);
            $domainShaped  = $primaryDomain ? $shapeDomainDesig($primaryDomain) : null;

            $secondaryDomainsShaped = $sense->domains
                ->where('pivot.is_primary', false)
                ->map($shapeDomainDesig)
                ->values()->all();

            // Collocations
            $collocations = $sense->collocations->map(fn ($wo) => [
                'traditional' => $wo->traditional,
                'smartId'     => $wo->smart_id,
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

            $shapeRelated = function (WordSense $related, ?string $note = null) {
                return [
                    'traditional' => $related->wordObject?->traditional,
                    'smartId'     => $related->wordObject?->smart_id,
                    'pinyin'      => $related->pronunciation?->pronunciation_text ?? '',
                    'pos'         => self::POS_FULL_NAMES[$related->definitions->first()?->posLabel?->slug ?? ''] ?? '',
                    'posAbbr'     => $related->definitions->first()?->posLabel?->slug ?? '',
                    'def'         => $related->definitions->first()?->definition_text ?? '',
                    'tocfl'       => self::TOCFL_SLUG_MAP[$related->tocflLevel?->slug ?? ''] ?? null,
                    'note'        => $note,
                ];
            };

            // Forward relations (this sense → related sense)
            foreach ($sense->senseRelations as $rel) {
                $typeSlug = $rel->relationType?->slug ?? '';
                $related  = $rel->relatedSense;
                if (!$related?->wordObject) continue;
                $shaped = $shapeRelated($related, $rel->editorial_note);

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

            // Inverse relations (related sense → this sense)
            foreach ($sense->inverseSenseRelations as $rel) {
                $typeSlug = $rel->relationType?->slug ?? '';
                $source   = $rel->wordSense;
                if (!$source?->wordObject) continue;
                $shaped = $shapeRelated($source, $rel->editorial_note);

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
                'learnerTraps'    => $sense->learner_traps,
                'collocations'    => $collocations,
                'relations'       => $relations,
                'family'          => $family,
            ];
        })->values()->all();

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
                'text'      => $p->pronunciation_text,
                'system'    => $p->pronunciationSystem?->slug ?? 'pinyin',
                'isPrimary' => (bool) $p->is_primary,
            ])->values()->all(),
            'senses'          => $shapedSenses,
            'family'          => $allFamily,
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
            'savedSenseIds'  => $user->savedSenses()->pluck('word_sense_id')->all(),
            'collections'    => $user->collections()
                ->with('wordSenses:word_senses.id')
                ->get()
                ->map(fn ($c) => [
                    'id'       => $c->id,
                    'name'     => $c->name,
                    'senseIds' => $c->wordSenses->pluck('id'),
                ]),
        ];
    }
}
