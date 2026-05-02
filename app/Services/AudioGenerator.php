<?php

namespace App\Services;

use App\Models\WordPronunciation;
use App\Models\WordSenseExample;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

// Single source of truth for audio generation. Used by both the
// `audio:generate` artisan command (bulk / manual sweeps) and the queued
// GenerateRowAudio job (per-row dispatch from observers + importer).
//
// Freshness contract:
//   audio is fresh ⟺ has_audio[slot] === true
//                    AND audio_text_hash === sha256(current_source_text)
//
// A null/mismatched hash means "regenerate" — the same logic that triggers
// when has_audio is missing a slot. This is what makes a manual sweep
// trustworthy after content edits and what lets the observer just clear
// the hash to invalidate.
class AudioGenerator
{
    /** Voice key → edge-tts voice ID. Keys double as storage subdirectory names. */
    public const VOICES = [
        'tw-f' => 'zh-TW-HsiaoChenNeural',
        'tw-m' => 'zh-TW-YunJheNeural',
        'cn-f' => 'zh-CN-XiaoxiaoNeural',
        'cn-m' => 'zh-CN-YunxiNeural',
    ];

    /** Polite throttle between sequential edge-tts calls (ms). */
    public const SLEEP_MS = 100;

    /** Minimum file size to consider a generation successful. */
    private const MIN_FILE_BYTES = 500;

    /** Process timeout per synthesize call (seconds). */
    private const SYNTHESIZE_TIMEOUT = 30;

    /**
     * Regenerate any stale or missing voices for a pronunciation row.
     * The synthesized text is the headword (traditional), not the pinyin —
     * edge-tts handles CJK→phonetics natively.
     *
     * @param array<string,string>|null $targetVoices Subset of self::VOICES, or null for all.
     * @param bool $force Regenerate even if hash + has_audio agree.
     * @return array{generated:int,errors:int,skipped:int}
     */
    public function regeneratePronunciation(
        WordPronunciation $p,
        ?array $targetVoices = null,
        bool $force = false
    ): array {
        $targetVoices ??= self::VOICES;

        $word = $p->wordObject;
        $text = $word?->traditional ?? '';
        if ($text === '') {
            return ['generated' => 0, 'errors' => 0, 'skipped' => count($targetVoices)];
        }

        return $this->regenerateRow(
            type: 'pronunciations',
            rowId: $p->id,
            sourceText: $text,
            currentHasAudio: is_array($p->has_audio) ? $p->has_audio : [],
            currentHash: $p->audio_text_hash,
            targetVoices: $targetVoices,
            force: $force,
            persist: function (array $hasAudio, string $hash) use ($p) {
                $p->forceFill([
                    'has_audio'       => $hasAudio,
                    'audio_text_hash' => $hash,
                ])->saveQuietly();
            },
        );
    }

    /**
     * Regenerate any stale or missing voices for an example sentence row.
     *
     * @param array<string,string>|null $targetVoices Subset of self::VOICES, or null for all.
     * @param bool $force Regenerate even if hash + has_audio agree.
     * @return array{generated:int,errors:int,skipped:int}
     */
    public function regenerateExample(
        WordSenseExample $e,
        ?array $targetVoices = null,
        bool $force = false
    ): array {
        $targetVoices ??= self::VOICES;

        $text = trim((string) $e->chinese_text);
        if ($text === '') {
            return ['generated' => 0, 'errors' => 0, 'skipped' => count($targetVoices)];
        }

        return $this->regenerateRow(
            type: 'examples',
            rowId: $e->id,
            sourceText: $text,
            currentHasAudio: is_array($e->has_audio) ? $e->has_audio : [],
            currentHash: $e->audio_text_hash,
            targetVoices: $targetVoices,
            force: $force,
            persist: function (array $hasAudio, string $hash) use ($e) {
                $e->forceFill([
                    'has_audio'       => $hasAudio,
                    'audio_text_hash' => $hash,
                ])->saveQuietly();
            },
        );
    }

    /**
     * SHA-256 of the source text, normalized via trim. Stored verbatim
     * alongside the audio files; recomputed on every freshness check.
     */
    public static function hashFor(string $text): string
    {
        return hash('sha256', trim($text));
    }

    /**
     * Are the current files for this row fresh?
     * Public so freshness logic is reusable from queries / dashboards
     * without duplicating the rule.
     *
     * @param array<string,bool>|null $hasAudio
     * @param array<string,string> $expectedVoices
     */
    public static function isFresh(
        ?array $hasAudio,
        ?string $storedHash,
        string $currentText,
        array $expectedVoices
    ): bool {
        if ($storedHash !== self::hashFor($currentText)) return false;
        $hasAudio ??= [];
        foreach (array_keys($expectedVoices) as $key) {
            if (empty($hasAudio[$key])) return false;
        }
        return true;
    }

    // ── internal ────────────────────────────────────────────────────────

    /**
     * Core per-row regen loop, parameterized so pronunciation and example
     * paths share the same logic. Decides which voices need regen, calls
     * the synthesizer, and persists the updated has_audio + hash atomically
     * via the supplied closure.
     *
     * @param array<string,bool> $currentHasAudio
     * @param array<string,string> $targetVoices
     * @param \Closure(array<string,bool>, string):void $persist
     * @return array{generated:int,errors:int,skipped:int}
     */
    private function regenerateRow(
        string $type,
        int $rowId,
        string $sourceText,
        array $currentHasAudio,
        ?string $currentHash,
        array $targetVoices,
        bool $force,
        \Closure $persist,
    ): array {
        $newHash = self::hashFor($sourceText);
        $hashChanged = ($currentHash !== $newHash);

        $hasAudio = $currentHasAudio;
        $needsGeneration = [];

        foreach ($targetVoices as $key => $voice) {
            $stale = $hashChanged || empty($hasAudio[$key]);
            if ($force || $stale) {
                $needsGeneration[$key] = $voice;
            }
        }

        if (empty($needsGeneration)) {
            return ['generated' => 0, 'errors' => 0, 'skipped' => count($targetVoices)];
        }

        // When the hash changes, every existing slot is presumed stale —
        // wipe has_audio for the targeted voices so partial state never lingers.
        if ($hashChanged) {
            foreach (array_keys($needsGeneration) as $k) {
                $hasAudio[$k] = false;
            }
        }

        $generated = 0;
        $errors = 0;

        foreach ($needsGeneration as $key => $voice) {
            $relPath = "audio/{$type}/{$key}/{$rowId}.mp3";
            if ($this->synthesize($sourceText, $voice, $relPath)) {
                $hasAudio[$key] = true;
                $generated++;
            } else {
                $hasAudio[$key] = false;
                $errors++;
            }
            usleep(self::SLEEP_MS * 1000);
        }

        // Always persist the new hash — even on partial failure — so the
        // freshness check correctly identifies which slots still need work
        // on the next pass.
        $persist($hasAudio, $newHash);

        return [
            'generated' => $generated,
            'errors'    => $errors,
            'skipped'   => count($targetVoices) - count($needsGeneration),
        ];
    }

    /**
     * Synthesize text via edge-tts Python CLI, write to storage path.
     * Returns true on success, false on failure.
     */
    private function synthesize(string $text, string $voice, string $storagePath): bool
    {
        $absPath = Storage::disk('public')->path($storagePath);
        $dir = dirname($absPath);
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $process = new Process([
            'python3', '-m', 'edge_tts',
            '--voice', $voice,
            '--text', $text,
            '--write-media', $absPath,
        ]);
        $process->setTimeout(self::SYNTHESIZE_TIMEOUT);

        try {
            $process->run();
        } catch (\Throwable $e) {
            return false;
        }

        if (! $process->isSuccessful()) {
            return false;
        }

        if (! File::exists($absPath) || File::size($absPath) < self::MIN_FILE_BYTES) {
            if (File::exists($absPath)) File::delete($absPath);
            return false;
        }

        return true;
    }
}
