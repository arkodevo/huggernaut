<?php

namespace App\Http\Controllers;

use App\Models\Affirmation;
use App\Models\Disputation;
use App\Models\UserSavedExample;
use App\Models\WordObject;
use App\Models\WordSense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CommunityController extends Controller
{
    /**
     * Public cross-learner feed of community contributions.
     * Three tabs — writings, disputations, affirmations — each browsing
     * what other learners have shared, not the current user's own activity
     * (that lives at /my-activity).
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'writings');
        if (! in_array($tab, ['writings', 'disputations', 'affirmations'], true)) {
            $tab = 'writings';
        }

        $writings     = null;
        $affirmations = [];
        $disputations = null;

        if ($tab === 'writings') {
            $writings = $this->loadPublicWritings();
        } elseif ($tab === 'affirmations') {
            $affirmations = $this->loadMostAffirmedSenses();
        } elseif ($tab === 'disputations') {
            $disputations = $this->loadCommunityDisputations();
        }

        return view('community', [
            'tab'          => $tab,
            'writings'     => $writings,
            'affirmations' => $affirmations,
            'disputations' => $disputations,
            'authUser'     => (new ExploreController())->authUserPayload(),
        ]);
    }

    /**
     * Paginated cross-learner feed of disputations. Each row respects the
     * per-row is_anonymous snapshot when rendering the author — flipping
     * profile settings after the fact does NOT retroactively unmask past
     * disputes. Shape carries enough context for the community view to
     * show sense, field-count, rationale excerpt, and status badge.
     */
    private function loadCommunityDisputations()
    {
        $paginated = Disputation::with([
                'user',
                'wordSense' => fn ($q) => $q->with([
                    'wordObject',
                    'pronunciation',
                    'definitions' => fn ($q) => $q
                        ->where('language_id', 1)
                        ->orderBy('sort_order')
                        ->with('posLabel'),
                ]),
            ])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return $paginated->through(function ($d) {
            $ws  = $d->wordSense;
            $wo  = $ws?->wordObject;
            $def = $ws?->definitions->first();

            return [
                'id'            => $d->id,
                'author'        => $d->displayAuthor(),
                'isAnonymous'   => $d->is_anonymous,
                'traditional'   => $wo?->traditional ?? '',
                'smartId'       => $wo?->smart_id ?? '',
                'pinyin'        => $ws?->pronunciation?->pronunciation_text ?? '',
                'posAbbr'       => ExploreController::POS_DISPLAY_ABBR[$def?->posLabel?->slug ?? '']
                                   ?? ($def?->posLabel?->slug ?? ''),
                'definition'    => $def?->definition_text ?? '',
                'fieldCount'    => count($d->fields_disputed ?? []),
                'rationale'     => $d->rationale,
                'status'        => $d->status,
                'verdict'       => $d->verdict,
                'created_at'    => $d->created_at->diffForHumans(),
            ];
        });
    }

    /**
     * Public writings across all learners. Paginated, newest first.
     * Shape mirrors MyWritingsController::index but adds author fields
     * and omits the visibility chip (every row is public by definition).
     */
    private function loadPublicWritings()
    {
        $paginated = UserSavedExample::where('is_public', true)
            ->with([
                'user',
                'wordSense' => fn ($q) => $q->with([
                    'wordObject',
                    'pronunciation',
                    'definitions' => fn ($q) => $q
                        ->where('language_id', 1)
                        ->orderBy('sort_order')
                        ->with('posLabel'),
                ]),
            ])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return $paginated->through(function ($ex) {
            $ws  = $ex->wordSense;
            $wo  = $ws?->wordObject;
            $def = $ws?->definitions->first();
            $u   = $ex->user;

            return [
                'id'               => $ex->id,
                'author'           => $u?->chinese_name ?: ($u?->name ?: 'Anonymous'),
                'authorId'         => $u?->id,
                'traditional'      => $wo?->traditional ?? '',
                'smartId'          => $wo?->smart_id ?? '',
                'pinyin'           => $ws?->pronunciation?->pronunciation_text ?? '',
                'posAbbr'          => ExploreController::POS_DISPLAY_ABBR[$def?->posLabel?->slug ?? '']
                                      ?? ($def?->posLabel?->slug ?? ''),
                'chinese_text'     => $ex->chinese_text,
                'english_text'     => $ex->english_text,
                'ai_verified'      => (bool) $ex->ai_verified,
                'ai_feedback'      => $ex->ai_feedback,
                'source_type'      => $ex->source_type ?? 'learner',
                'assessed_level'   => $ex->assessed_level,
                'assessed_mastery' => $ex->assessed_mastery,
                'mastery_guidance' => $ex->mastery_guidance,
                'created_at'       => $ex->created_at->format('M j, Y'),
            ];
        });
    }

    /**
     * Leaderboard of the senses the community trusts most: top N senses by
     * affirmation count, with each sense's full display context. This is
     * the aggregate framing of affirmations — the raw votes are silent
     * scalars on the LWP, surfaced here as the community's collective
     * editorial confidence. Senses with zero affirmations never appear.
     */
    private function loadMostAffirmedSenses(): array
    {
        // Pull the ranked sense ids + counts in a single grouped query.
        $ranked = DB::table('affirmations')
            ->select('word_sense_id', DB::raw('COUNT(*) as affirm_count'))
            ->groupBy('word_sense_id')
            ->orderByDesc('affirm_count')
            ->orderBy('word_sense_id') // stable tiebreaker
            ->limit(50)
            ->get();

        if ($ranked->isEmpty()) {
            return [];
        }

        // Eager-load the senses in one query, keyed by id for O(1) lookup
        // as we walk the ranked list in count order.
        $senseIds = $ranked->pluck('word_sense_id')->all();
        $senses = WordSense::with([
                'wordObject',
                'pronunciation',
                'definitions' => fn ($q) => $q
                    ->where('language_id', 1)
                    ->orderBy('sort_order')
                    ->with('posLabel'),
            ])
            ->whereIn('id', $senseIds)
            ->get()
            ->keyBy('id');

        $rows = [];
        $rank = 0;
        foreach ($ranked as $r) {
            $s = $senses->get($r->word_sense_id);
            if (! $s || ! $s->wordObject) {
                continue;
            }
            $def = $s->definitions->first();
            $rank++;

            $rows[] = [
                'rank'        => $rank,
                'count'       => (int) $r->affirm_count,
                'senseId'     => $s->id,
                'traditional' => $s->wordObject->traditional,
                'smartId'     => $s->wordObject->smart_id,
                'pinyin'      => $s->pronunciation?->pronunciation_text ?? '',
                'pos'         => $def?->posLabel?->slug ?? '',
                'definition'  => $def?->definition_text ?? '',
            ];
        }

        return $rows;
    }

    /**
     * Per-word JSON payload for the LWP Community section. Dropdown-driven:
     * the `view` query parameter selects which data block to return. The trust
     * strip is always included so the header context persists across views.
     *
     * Views: writings | my-writings | disputes | affirmed-senses | trust
     *
     * Route is public — unauth visitors see all data except `my-writings`
     * (gated server-side) and action buttons (gated client-side).
     */
    public function wordPanel(Request $request, int $wordObjectId): JsonResponse
    {
        $word = WordObject::find($wordObjectId);
        if (! $word) {
            return response()->json(['error' => 'word_not_found'], 404);
        }

        $view   = $request->query('view', 'writings');
        $offset = max(0, (int) $request->query('offset', 0));
        $limit  = min(10, max(1, (int) $request->query('limit', 3)));

        if (! in_array($view, ['writings', 'my-writings', 'disputes', 'affirmed-senses', 'trust'], true)) {
            $view = 'writings';
        }

        $senseIds = WordSense::where('word_object_id', $wordObjectId)->pluck('id')->all();
        $userId   = Auth::id();

        $data = [];
        if ($view === 'writings') {
            $data = $this->wordPublicWritings($wordObjectId, $userId, $offset, $limit);
        } elseif ($view === 'my-writings') {
            $data = ['items' => $userId ? $this->wordMyWritings($wordObjectId, $userId) : []];
        } elseif ($view === 'disputes') {
            $data = $this->wordDisputes($senseIds);
        } elseif ($view === 'affirmed-senses') {
            $data = ['items' => $this->wordAffirmedSenses($senseIds, $userId)];
        } elseif ($view === 'trust') {
            // 'trust' view returns no separate data beyond the always-included trust strip
            $data = [];
        }

        return response()->json([
            'word' => [
                'id'          => $word->id,
                'traditional' => $word->traditional,
                'smartId'     => $word->smart_id,
            ],
            'view'  => $view,
            'data'  => $data,
            'trust' => $this->wordTrust($wordObjectId, $senseIds, $userId),
        ]);
    }

    /** Public writings for a word, excluding the current user's own rows. */
    private function wordPublicWritings(int $wordObjectId, ?int $userId, int $offset, int $limit): array
    {
        $query = UserSavedExample::where('is_public', true)
            ->whereHas('wordSense', fn ($q) => $q->where('word_object_id', $wordObjectId));
        if ($userId) {
            $query->where('user_id', '!=', $userId);
        }
        $total = (clone $query)->count();

        $items = $query
            ->with([
                'user',
                'wordSense' => fn ($q) => $q->with([
                    'pronunciation',
                    'wordObject',
                    'definitions' => fn ($q) => $q
                        ->where('language_id', 1)
                        ->orderBy('sort_order')
                        ->with('posLabel'),
                ]),
            ])
            ->orderByDesc('created_at')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(fn ($ex) => $this->shapeWritingCard($ex, false))
            ->values()
            ->all();

        return [
            'items'   => $items,
            'offset'  => $offset,
            'limit'   => $limit,
            'hasMore' => ($offset + count($items)) < $total,
            'total'   => $total,
        ];
    }

    /** Current user's writings for a word — public + private, capped at 5. */
    private function wordMyWritings(int $wordObjectId, int $userId): array
    {
        return UserSavedExample::where('user_id', $userId)
            ->whereHas('wordSense', fn ($q) => $q->where('word_object_id', $wordObjectId))
            ->with([
                'user',
                'wordSense' => fn ($q) => $q->with([
                    'pronunciation',
                    'wordObject',
                    'definitions' => fn ($q) => $q
                        ->where('language_id', 1)
                        ->orderBy('sort_order')
                        ->with('posLabel'),
                ]),
            ])
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(fn ($ex) => $this->shapeWritingCard($ex, true))
            ->values()
            ->all();
    }

    /**
     * Active disputes across this word's senses. Only pending + under_review;
     * resolved disputes are archival and live on /community instead.
     */
    private function wordDisputes(array $senseIds): array
    {
        if (empty($senseIds)) return ['items' => [], 'total' => 0];

        $rows = Disputation::whereIn('word_sense_id', $senseIds)
            ->whereIn('status', ['pending', 'under_review'])
            ->with([
                'user',
                'wordSense' => fn ($q) => $q->with([
                    'pronunciation',
                    'definitions' => fn ($q) => $q
                        ->where('language_id', 1)
                        ->orderBy('sort_order')
                        ->with('posLabel'),
                ]),
            ])
            ->orderByDesc('created_at')
            ->get();

        $items = $rows->map(function ($d) {
            $ws     = $d->wordSense;
            $def    = $ws?->definitions->first();
            $u      = $d->user;
            $fields = is_array($d->fields_disputed) ? $d->fields_disputed : [];
            $rat    = (string) ($d->rationale ?? '');
            $ratShort = mb_strlen($rat) > 200 ? mb_substr($rat, 0, 200) . '…' : $rat;

            return [
                'id'               => $d->id,
                'senseId'          => $ws?->id,
                'senseDefinition'  => $def?->definition_text ?? '',
                'sensePos'         => $def?->posLabel?->slug ?? '',
                'sensePosAbbr'     => ExploreController::POS_DISPLAY_ABBR[$def?->posLabel?->slug ?? '']
                                      ?? ($def?->posLabel?->slug ?? ''),
                'sensePinyin'      => $ws?->pronunciation?->pronunciation_text ?? '',
                'disputer'         => $d->is_anonymous ? 'Anonymous' : ($u?->chinese_name ?: ($u?->name ?: 'Anonymous')),
                'isAnonymous'      => (bool) $d->is_anonymous,
                'fieldsDisputed'   => $fields,
                'fieldCount'       => count($fields),
                'rationale'        => $ratShort,
                'status'           => $d->status,
                'createdAt'        => $d->created_at?->diffForHumans() ?? '',
            ];
        })->values()->all();

        return ['items' => $items, 'total' => count($items)];
    }

    /**
     * This word's senses ranked by affirmation count. Zero-affirm senses
     * are omitted. Includes `affirmedByMe` when authed so the learner can
     * see which senses they've already backed.
     */
    private function wordAffirmedSenses(array $senseIds, ?int $userId): array
    {
        if (empty($senseIds)) return [];

        $counts = DB::table('affirmations')
            ->whereIn('word_sense_id', $senseIds)
            ->select('word_sense_id', DB::raw('COUNT(*) as c'))
            ->groupBy('word_sense_id')
            ->orderByDesc('c')
            ->pluck('c', 'word_sense_id');

        if ($counts->isEmpty()) return [];

        $mine = $userId
            ? DB::table('affirmations')
                ->where('user_id', $userId)
                ->whereIn('word_sense_id', $counts->keys()->all())
                ->pluck('word_sense_id')->flip()
            : collect();

        $senses = WordSense::with([
                'pronunciation',
                'definitions' => fn ($q) => $q
                    ->where('language_id', 1)
                    ->orderBy('sort_order')
                    ->with('posLabel'),
            ])
            ->whereIn('id', $counts->keys()->all())
            ->get()
            ->keyBy('id');

        $rows = [];
        foreach ($counts as $senseId => $count) {
            $s = $senses->get($senseId);
            if (! $s) continue;
            $def = $s->definitions->first();
            $rows[] = [
                'senseId'      => $s->id,
                'definition'   => $def?->definition_text ?? '',
                'pos'          => $def?->posLabel?->slug ?? '',
                'posAbbr'      => ExploreController::POS_DISPLAY_ABBR[$def?->posLabel?->slug ?? '']
                                  ?? ($def?->posLabel?->slug ?? ''),
                'pinyin'       => $s->pronunciation?->pronunciation_text ?? '',
                'affirmCount'  => (int) $count,
                'affirmedByMe' => $mine->has($senseId),
            ];
        }

        return $rows;
    }

    /** Aggregate trust signals for the word — always in the response. */
    private function wordTrust(int $wordObjectId, array $senseIds, ?int $userId): array
    {
        $affirmTotal  = $senseIds ? Affirmation::whereIn('word_sense_id', $senseIds)->count() : 0;
        $disputeTotal = $senseIds
            ? Disputation::whereIn('word_sense_id', $senseIds)
                ->whereIn('status', ['pending', 'under_review'])
                ->count()
            : 0;

        $publicWritingCount = UserSavedExample::where('is_public', true)
            ->whereHas('wordSense', fn ($q) => $q->where('word_object_id', $wordObjectId))
            ->count();

        $myWritingCount = $userId
            ? UserSavedExample::where('user_id', $userId)
                ->whereHas('wordSense', fn ($q) => $q->where('word_object_id', $wordObjectId))
                ->count()
            : 0;

        return [
            'affirmTotal'        => $affirmTotal,
            'disputeTotal'       => $disputeTotal,
            'senseCount'         => count($senseIds),
            'publicWritingCount' => $publicWritingCount,
            'myWritingCount'     => $myWritingCount,
        ];
    }

    /**
     * Normalized writing-card shape — matches the JS `renderWritingCard` input
     * exactly, so no field-mapping layer is needed on the client. Any field
     * rename here MUST propagate to `_writing-card-js.blade.php`.
     */
    private function shapeWritingCard(UserSavedExample $ex, bool $isMine): array
    {
        $ws  = $ex->wordSense;
        $wo  = $ws?->wordObject;
        $def = $ws?->definitions->first();
        $u   = $ex->user;

        // Source label — matches Workshop's own labeling so card visuals stay consistent.
        $sourceType = $ex->source_type ?? 'learner';
        if ($sourceType === 'generated') {
            $source = '師父 generated';
        } elseif ($ex->ai_verified) {
            $source = '師父 verified';
        } else {
            $source = '⚠ Unverified draft';
        }

        $posSlug = $def?->posLabel?->slug ?? '';
        $posAbbr = ExploreController::POS_DISPLAY_ABBR[$posSlug] ?? $posSlug;

        return [
            'id'               => $ex->id,
            'cn'               => $ex->chinese_text,
            'en'               => $ex->english_text,
            'author'           => $u?->chinese_name ?: ($u?->name ?: 'Anonymous'),
            'authorId'         => $u?->id,
            'isMine'           => $isMine,
            'isPublic'         => (bool) $ex->is_public,
            'source'           => $source,
            'sourceType'       => $sourceType,
            'aiVerified'       => (bool) $ex->ai_verified,
            'assessedLevel'    => $ex->assessed_level,
            'assessedMastery'  => $ex->assessed_mastery,
            'masteryGuidance'  => $ex->mastery_guidance,
            'feedback'         => $ex->ai_feedback,
            'originalCn'       => $ex->original_chinese_text ?? null,
            'grammarPatterns'  => [], // pivot not loaded here; Workshop deck fills from localStorage
            'pos'              => $posSlug,
            'posAbbr'          => $posAbbr,
            'pinyin'           => $ws?->pronunciation?->pronunciation_text ?? '',
            'target'           => [
                'traditional' => $wo?->traditional ?? '',
                'simplified'  => $wo?->simplified ?? '',
            ],
            'date'             => $ex->created_at?->format('M j, Y') ?? '',
            'createdAtHuman'   => $ex->created_at?->diffForHumans() ?? '',
        ];
    }
}
