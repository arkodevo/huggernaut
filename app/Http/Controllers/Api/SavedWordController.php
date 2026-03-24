<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSavedWord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedWordController extends Controller
{
    public function index(): JsonResponse
    {
        $ids = Auth::user()->savedWords()->pluck('word_object_id');

        return response()->json($ids);
    }

    public function toggle(int $wordObjectId): JsonResponse
    {
        $user = Auth::user();
        $existing = UserSavedWord::where('user_id', $user->id)
            ->where('word_object_id', $wordObjectId)
            ->first();

        if (!$existing) {
            UserSavedWord::create([
                'user_id'        => $user->id,
                'word_object_id' => $wordObjectId,
                'saved_at'       => now(),
            ]);

            $user->awardPoints('word_saved');
        }

        return response()->json(['saved' => true]);
    }

    public function destroy(int $wordObjectId): JsonResponse
    {
        $user = Auth::user();

        UserSavedWord::where('user_id', $user->id)
            ->where('word_object_id', $wordObjectId)
            ->delete();

        // Also remove from all user's collections
        $collectionIds = $user->collections()->pluck('id');
        if ($collectionIds->isNotEmpty()) {
            \DB::table('collection_word')
                ->whereIn('collection_id', $collectionIds)
                ->where('word_object_id', $wordObjectId)
                ->delete();
        }

        return response()->json(['saved' => false]);
    }

    public function updateNote(Request $request, int $wordObjectId): JsonResponse
    {
        $request->validate(['note' => ['nullable', 'string', 'max:5000']]);

        UserSavedWord::where('user_id', Auth::id())
            ->where('word_object_id', $wordObjectId)
            ->update(['personal_note' => $request->input('note', '')]);

        return response()->json(['ok' => true]);
    }
}
