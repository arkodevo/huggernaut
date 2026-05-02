<?php

namespace App\Jobs;

use App\Models\WordPronunciation;
use App\Models\WordSenseExample;
use App\Services\AudioGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// Per-row audio (re)generation. Dispatched by:
//   - The Eloquent observer when a pronunciation_text or chinese_text
//     edit invalidates the audio_text_hash.
//   - The Eloquent observer on row creation (importer + admin add paths).
//   - The forthcoming bulk sweep, which fans out one job per row.
//
// Routes to the `audio` queue so a backlog never blocks the default queue
// (师父 writing assessments, future ImportProgress pings, etc.).
//
// AudioGenerator is the single source of truth for freshness — this job
// is just the queued caller.
class GenerateRowAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queueName = 'audio';

    /** Up to 3 attempts per row — edge-tts hiccups should not be terminal. */
    public int $tries = 3;

    /** Hard cap on each attempt (4 voices × ~1s each + headroom). */
    public int $timeout = 120;

    /**
     * @param 'pronunciation'|'example' $rowType
     */
    public function __construct(
        public string $rowType,
        public int $rowId,
        public bool $force = false,
    ) {
        $this->onQueue($this->queueName);
    }

    public function handle(AudioGenerator $generator): void
    {
        if ($this->rowType === 'pronunciation') {
            $row = WordPronunciation::with('wordObject')->find($this->rowId);
            if ($row) $generator->regeneratePronunciation($row, null, $this->force);
            return;
        }

        if ($this->rowType === 'example') {
            $row = WordSenseExample::find($this->rowId);
            if ($row) $generator->regenerateExample($row, null, $this->force);
            return;
        }
    }

    /**
     * Identity tag for queue inspection — lets a future dashboard group
     * jobs by target row without parsing the payload.
     */
    public function tags(): array
    {
        return ["audio:{$this->rowType}:{$this->rowId}"];
    }
}
