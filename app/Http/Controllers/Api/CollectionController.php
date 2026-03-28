<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\SearchNotFound;
use App\Models\UserSavedWord;
use App\Models\WordObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    public function index(): JsonResponse
    {
        $collections = Auth::user()->collections()
            ->with('wordObjects:word_objects.id')
            ->get()
            ->map(fn ($c) => [
                'id'            => $c->id,
                'name'          => $c->name,
                'name_zh'       => $c->name_zh,
                'wordObjectIds' => $c->wordObjects->pluck('id'),
            ]);

        return response()->json($collections);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'name_zh' => ['nullable', 'string', 'max:255'],
        ]);

        $collection = Auth::user()->collections()->create([
            'name'    => $data['name'],
            'name_zh' => $data['name_zh'] ?? null,
            'type'    => 'custom',
        ]);

        return response()->json($collection, 201);
    }

    public function update(Request $request, Collection $collection): JsonResponse
    {
        if ($collection->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'name'    => ['sometimes', 'string', 'max:255'],
            'name_zh' => ['nullable', 'string', 'max:255'],
        ]);

        $collection->update($data);

        return response()->json($collection);
    }

    public function destroy(Collection $collection): JsonResponse
    {
        if ($collection->user_id !== Auth::id()) {
            abort(403);
        }

        $collection->delete();

        return response()->json(['ok' => true]);
    }

    public function addWord(Collection $collection, int $wordObjectId): JsonResponse
    {
        if ($collection->user_id !== Auth::id()) {
            abort(403);
        }

        // Ensure the word is saved (user_saved_words) — adding to a collection implies saving
        UserSavedWord::firstOrCreate(
            ['user_id' => Auth::id(), 'word_object_id' => $wordObjectId],
            ['saved_at' => now()]
        );

        $maxSort = $collection->wordObjects()->max('collection_word.sort_order') ?? 0;

        $collection->wordObjects()->syncWithoutDetaching([
            $wordObjectId => ['sort_order' => $maxSort + 1, 'added_at' => now()],
        ]);

        return response()->json(['ok' => true]);
    }

    public function importWords(Request $request, Collection $collection): JsonResponse
    {
        if ($collection->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'words' => ['required', 'array', 'max:500'],
            'words.*' => ['required', 'string', 'max:32'],
            'mode' => ['required', 'string', 'in:append,overwrite'],
        ]);

        $words = $data['words'];
        $mode = $data['mode'];

        // If overwrite, clear existing words from collection
        if ($mode === 'overwrite') {
            $collection->wordObjects()->detach();
        }

        // Look up all word objects by traditional character
        $wordObjects = WordObject::whereIn('traditional', $words)->get()->keyBy('traditional');

        $added = 0;
        $alreadyIn = 0;
        $notFound = [];
        $maxSort = $collection->wordObjects()->max('collection_word.sort_order') ?? 0;

        // Get existing word IDs in this collection
        $existingIds = $collection->wordObjects()->pluck('word_objects.id')->all();

        foreach ($words as $word) {
            $wo = $wordObjects->get($word);
            if (! $wo) {
                $notFound[] = $word;
                // Log to Not Found system
                SearchNotFound::create([
                    'character'     => $word,
                    'source'        => 'import',
                    'user_id'       => Auth::id(),
                    'collection_id' => $collection->id,
                ]);
                continue;
            }

            if (in_array($wo->id, $existingIds) && $mode === 'append') {
                $alreadyIn++;
                continue;
            }

            // Ensure word is saved
            UserSavedWord::firstOrCreate(
                ['user_id' => Auth::id(), 'word_object_id' => $wo->id],
                ['saved_at' => now()]
            );

            // Add to collection
            $maxSort++;
            $collection->wordObjects()->syncWithoutDetaching([
                $wo->id => ['sort_order' => $maxSort, 'added_at' => now()],
            ]);
            $added++;
        }

        return response()->json([
            'added'      => $added,
            'already_in' => $alreadyIn,
            'not_found'  => $notFound,
            'total'      => $collection->wordObjects()->count(),
        ]);
    }

    public function removeWord(Collection $collection, int $wordObjectId): JsonResponse
    {
        if ($collection->user_id !== Auth::id()) {
            abort(403);
        }

        $collection->wordObjects()->detach($wordObjectId);

        // If the word is no longer in ANY of this user's collections, unsave it entirely
        $unsaved = false;
        $stillInOther = DB::table('collection_word')
            ->join('collections', 'collections.id', '=', 'collection_word.collection_id')
            ->where('collections.user_id', Auth::id())
            ->where('collection_word.word_object_id', $wordObjectId)
            ->exists();

        if (!$stillInOther) {
            UserSavedWord::where('user_id', Auth::id())
                ->where('word_object_id', $wordObjectId)
                ->delete();
            $unsaved = true;
        }

        return response()->json(['ok' => true, 'unsaved' => $unsaved]);
    }
}
