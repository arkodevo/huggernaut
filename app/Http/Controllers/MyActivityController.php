<?php

namespace App\Http\Controllers;

use App\Models\Affirmation;
use App\Models\Disputation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyActivityController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Stamp last-seen so future notification badges know where the learner
        // was up to. Safe to update on every visit — cheap.
        $user->update(['last_seen_activity_at' => now()]);

        $tab = $request->query('tab', 'writings');
        if (! in_array($tab, ['writings', 'disputations', 'affirmations'], true)) {
            $tab = 'writings';
        }

        $disputations = [];
        if ($tab === 'disputations') {
            $disputations = Disputation::where('user_id', $user->id)
                ->with([
                    'wordSense.wordObject',
                    'wordSense.pronunciation',
                    'wordSense.definitions' => fn ($q) => $q
                        ->where('language_id', 1)
                        ->orderBy('sort_order')
                        ->with('posLabel'),
                ])
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($d) {
                    $ws  = $d->wordSense;
                    $wo  = $ws?->wordObject;
                    $def = $ws?->definitions->first();
                    if (! $ws || ! $wo) {
                        return null;
                    }
                    return [
                        'id'          => $d->id,
                        'traditional' => $wo->traditional,
                        'smartId'     => $wo->smart_id,
                        'pinyin'      => $ws->pronunciation?->pronunciation_text ?? '',
                        'pos'         => $def?->posLabel?->slug ?? '',
                        'definition'  => $def?->definition_text ?? '',
                        'fields'      => $d->fields_disputed ?? [],
                        'fieldCount'  => count($d->fields_disputed ?? []),
                        'rationale'   => $d->rationale,
                        'isAnonymous' => $d->is_anonymous,
                        'status'      => $d->status,
                        'verdict'     => $d->verdict,
                        'canDelete'   => $d->status === Disputation::STATUS_PENDING,
                        'createdAt'   => $d->created_at,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        }

        $affirmations = [];
        if ($tab === 'affirmations') {
            $affirmations = Affirmation::where('user_id', $user->id)
                ->with([
                    'wordSense.wordObject',
                    'wordSense.pronunciation',
                    'wordSense.definitions' => fn ($q) => $q
                        ->where('language_id', 1)
                        ->orderBy('sort_order')
                        ->with('posLabel'),
                ])
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($a) {
                    $s = $a->wordSense;
                    if (! $s || ! $s->wordObject) {
                        return null;
                    }
                    $def = $s->definitions->first();
                    return [
                        'senseId'     => $s->id,
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

        return view('my-activity', [
            'tab'          => $tab,
            'authUser'     => (new ExploreController())->authUserPayload(),
            'affirmations' => $affirmations,
            'disputations' => $disputations,
        ]);
    }
}
