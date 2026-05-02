<?php

namespace App\Observers;

use App\Jobs\GenerateRowAudio;
use App\Models\WordPronunciation;

// Dispatches audio generation when a pronunciation row is born — typically
// during a batch import or an admin-created word. The synthesized text is
// the parent word_object's traditional headword (not pronunciation_text),
// so updates to pronunciation_text alone do NOT need to invalidate audio
// and are intentionally not handled here.
//
// WordObject.traditional renames are rare and out of scope for the daily
// pipeline; recover those via `audio:generate --word=<smart_id>`.
class WordPronunciationObserver
{
    public function created(WordPronunciation $pronunciation): void
    {
        GenerateRowAudio::dispatch('pronunciation', $pronunciation->id);
    }
}
