<?php

namespace App\Http\Controllers;

use App\Models\UserSavedExample;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyWritingsController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $writings = UserSavedExample::where('user_id', $user->id)
            ->with([
                'wordSense' => fn ($q) => $q->with([
                    'wordObject',
                    'pronunciation',
                    'definitions' => fn ($q) => $q->where('language_id', 1)
                        ->orderBy('sort_order')
                        ->with('posLabel'),
                ]),
            ])
            ->orderByDesc('created_at')
            ->paginate(20);

        $shaped = $writings->through(function ($ex) {
            $ws = $ex->wordSense;
            $wo = $ws?->wordObject;
            $def = $ws?->definitions->first();

            return [
                'id'           => $ex->id,
                'traditional'  => $wo?->traditional ?? '',
                'smartId'      => $wo?->smart_id ?? '',
                'pinyin'       => $ws?->pronunciation?->pronunciation_text ?? '',
                'pos'          => ExploreController::POS_FULL_NAMES[$def?->posLabel?->slug ?? ''] ?? '',
                'posAbbr'      => ExploreController::POS_DISPLAY_ABBR[$def?->posLabel?->slug ?? ''] ?? ($def?->posLabel?->slug ?? ''),
                'chinese_text' => $ex->chinese_text,
                'english_text' => $ex->english_text,
                'ai_verified'  => $ex->ai_verified,
                'ai_feedback'  => $ex->ai_feedback,
                'source_type'      => $ex->source_type ?? 'learner',
                'assessed_level'   => $ex->assessed_level,
                'assessed_mastery' => $ex->assessed_mastery,
                'mastery_guidance'  => $ex->mastery_guidance,
                'is_public'         => (bool) $ex->is_public,
                'created_at'       => $ex->created_at->format('M j, Y'),
            ];
        });

        return view('my-writings', [
            'writings' => $shaped,
            'authUser' => (new ExploreController())->authUserPayload(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = UserSavedExample::where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['deleted' => (bool) $deleted]);
    }

    /**
     * Toggle a writing's visibility (public/private). Owner only.
     */
    public function toggleVisibility(Request $request, int $id): JsonResponse
    {
        $writing = UserSavedExample::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate(['is_public' => 'required|boolean']);

        $writing->update(['is_public' => $request->boolean('is_public')]);

        return response()->json([
            'ok'        => true,
            'id'        => $writing->id,
            'is_public' => (bool) $writing->is_public,
        ]);
    }
}
