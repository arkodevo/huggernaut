<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PronunciationSystem;
use App\Models\WordObject;
use App\Models\WordPronunciation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WordPronunciationController extends Controller
{
    public function store(Request $request, WordObject $word): RedirectResponse
    {
        $data = $request->validate([
            'pronunciation_system_id' => ['required', 'exists:pronunciation_systems,id'],
            'pronunciation_text'      => ['required', 'string', 'max:64'],
            'is_primary'              => ['boolean'],
            'dialect_region'          => ['nullable', 'string', 'max:64'],
        ]);

        $data['word_object_id'] = $word->id;
        $data['is_primary']     = $request->boolean('is_primary');

        // If marking as primary, demote all others for this word + system
        if ($data['is_primary']) {
            WordPronunciation::where('word_object_id', $word->id)
                ->where('pronunciation_system_id', $data['pronunciation_system_id'])
                ->update(['is_primary' => false]);
        }

        WordPronunciation::create($data);

        return back()->with('success', 'Pronunciation added.');
    }

    public function destroy(WordObject $word, WordPronunciation $pronunciation): RedirectResponse
    {
        abort_unless($pronunciation->word_object_id === $word->id, 404);

        if ($word->senses()->where('pronunciation_id', $pronunciation->id)->exists()) {
            return back()->with('error', 'Cannot delete: one or more senses use this pronunciation.');
        }

        $pronunciation->delete();

        return back()->with('success', 'Pronunciation removed.');
    }
}
