<?php

namespace App\Http\Controllers;

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

        return view('my-activity', [
            'tab'      => $tab,
            'authUser' => (new ExploreController())->authUserPayload(),
        ]);
    }
}
