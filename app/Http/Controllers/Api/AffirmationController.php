<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affirmation;
use App\Models\WordSense;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AffirmationController extends Controller
{
    /**
     * Toggle an affirmation on a word sense for the current learner.
     * Returns the updated count and whether the learner currently affirms it.
     *
     * Uses a transaction + unique index on (user_id, word_sense_id) so that
     * a double-click cannot produce two rows or a 500 from the duplicate-key.
     */
    public function toggle(int $senseId): JsonResponse
    {
        // Ensure the sense exists (404 otherwise).
        WordSense::findOrFail($senseId);

        $userId = Auth::id();

        $affirmed = DB::transaction(function () use ($userId, $senseId) {
            $existing = Affirmation::where('user_id', $userId)
                ->where('word_sense_id', $senseId)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->delete();
                return false;
            }

            Affirmation::create([
                'user_id'       => $userId,
                'word_sense_id' => $senseId,
            ]);
            return true;
        });

        $count = Affirmation::where('word_sense_id', $senseId)->count();

        return response()->json([
            'affirmed' => $affirmed,
            'count'    => $count,
        ]);
    }
}
