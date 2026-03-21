<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSavedSense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedSenseController extends Controller
{
    public function index(): JsonResponse
    {
        $ids = Auth::user()->savedSenses()->pluck('word_sense_id');

        return response()->json($ids);
    }

    public function toggle(int $senseId): JsonResponse
    {
        $user = Auth::user();
        $existing = UserSavedSense::where('user_id', $user->id)
            ->where('word_sense_id', $senseId)
            ->first();

        if ($existing) {
            UserSavedSense::where('user_id', $user->id)
                ->where('word_sense_id', $senseId)
                ->delete();

            return response()->json(['saved' => false]);
        }

        UserSavedSense::create([
            'user_id'       => $user->id,
            'word_sense_id' => $senseId,
            'saved_at'      => now(),
        ]);

        $user->awardPoints('word_saved');

        return response()->json(['saved' => true, 'points' => 5]);
    }

    public function updateNote(Request $request, int $senseId): JsonResponse
    {
        $request->validate(['note' => ['nullable', 'string', 'max:5000']]);

        UserSavedSense::where('user_id', Auth::id())
            ->where('word_sense_id', $senseId)
            ->update(['personal_note' => $request->input('note', '')]);

        return response()->json(['ok' => true]);
    }
}
