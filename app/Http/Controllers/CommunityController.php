<?php

namespace App\Http\Controllers;

use App\Models\Affirmation;
use App\Models\UserSavedExample;
use Illuminate\Http\Request;
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
            $affirmations = $this->loadRecentAffirmations();
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
     * Recent affirmations across all users, newest first. Capped to 50 for
     * the skeleton pass — pagination lands once we see real usage volume.
     * Each row carries enough context to show "{learner} affirmed {sense}".
     */
    private function loadRecentAffirmations(): array
    {
        return Affirmation::with([
                'user',
                'wordSense.wordObject',
                'wordSense.pronunciation',
                'wordSense.definitions' => fn ($q) => $q
                    ->where('language_id', 1)
                    ->orderBy('sort_order')
                    ->with('posLabel'),
            ])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($a) {
                $s = $a->wordSense;
                $u = $a->user;
                if (! $s || ! $s->wordObject) {
                    return null;
                }
                $def = $s->definitions->first();

                return [
                    'author'      => $u?->chinese_name ?: ($u?->name ?: 'Anonymous'),
                    'traditional' => $s->wordObject->traditional,
                    'smartId'     => $s->wordObject->smart_id,
                    'pinyin'      => $s->pronunciation?->pronunciation_text ?? '',
                    'pos'         => $def?->posLabel?->slug ?? '',
                    'definition'  => $def?->definition_text ?? '',
                    'affirmedAt'  => $a->created_at,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
