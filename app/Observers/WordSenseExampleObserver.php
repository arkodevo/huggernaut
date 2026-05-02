<?php

namespace App\Observers;

use App\Jobs\GenerateRowAudio;
use App\Models\WordSenseExample;

// Dispatches audio generation when an example is created or its
// chinese_text changes. The hash on AudioGenerator catches any drift
// even without this observer, but firing here gives near-immediate
// turnaround instead of waiting for the next manual sweep.
//
// is_suppressed flips and translation edits do not invalidate audio;
// only chinese_text matters for synthesis.
class WordSenseExampleObserver
{
    public function created(WordSenseExample $example): void
    {
        GenerateRowAudio::dispatch('example', $example->id);
    }

    public function updated(WordSenseExample $example): void
    {
        if ($example->wasChanged('chinese_text')) {
            GenerateRowAudio::dispatch('example', $example->id);
        }
    }
}
