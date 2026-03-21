<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectionController extends Controller
{
    public function index(): JsonResponse
    {
        $collections = Auth::user()->collections()
            ->with('wordSenses:word_senses.id')
            ->get()
            ->map(fn ($c) => [
                'id'       => $c->id,
                'name'     => $c->name,
                'name_zh'  => $c->name_zh,
                'senseIds' => $c->wordSenses->pluck('id'),
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

    public function addSense(Collection $collection, int $senseId): JsonResponse
    {
        if ($collection->user_id !== Auth::id()) {
            abort(403);
        }

        $maxSort = $collection->wordSenses()->max('collection_sense.sort_order') ?? 0;

        $collection->wordSenses()->syncWithoutDetaching([
            $senseId => ['sort_order' => $maxSort + 1, 'added_at' => now()],
        ]);

        return response()->json(['ok' => true]);
    }

    public function removeSense(Collection $collection, int $senseId): JsonResponse
    {
        if ($collection->user_id !== Auth::id()) {
            abort(403);
        }

        $collection->wordSenses()->detach($senseId);

        return response()->json(['ok' => true]);
    }
}
