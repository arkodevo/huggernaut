<?php

namespace App\Http\Controllers;

use App\Models\Affirmation;
use App\Models\UserSavedExample;
use App\Models\WordSense;
use Illuminate\Http\Request;
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

        if ($tab === 'writings') {
            $writings = $this->loadPublicWritings();
        } elseif ($tab === 'affirmations') {
            $affirmations = $this->loadMostAffirmedSenses();
        }

        return view('community', [
            'tab'          => $tab,
            'writings'     => $writings,
            'affirmations' => $affirmations,
            'authUser'     => (new ExploreController())->authUserPayload(),
        ]);
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
}
