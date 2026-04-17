<?php

namespace App\Console\Commands;

use App\Models\WordObject;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class GenerateAudio extends Command
{
    protected $signature = 'audio:generate
        {--word= : Only generate for a single word (pass smart_id, e.g. u6d41_u52d5)}
        {--voice= : Only generate this voice key (tw-f, tw-m, cn-f, cn-m)}
        {--pronunciations-only : Skip example sentences}
        {--examples-only : Skip word pronunciations}
        {--force : Regenerate files even if has_audio shows them already generated}
        {--limit= : Cap the number of rows processed per target type (useful for testing)}';

    protected $description = 'Generate neural TTS audio files (TW F/M + CN F/M) via edge-tts for word pronunciations and example sentences';

    /** Voice key → edge-tts voice ID. Keys double as storage subdirectory names. */
    private const VOICES = [
        'tw-f' => 'zh-TW-HsiaoChenNeural',
        'tw-m' => 'zh-TW-YunJheNeural',
        'cn-f' => 'zh-CN-XiaoxiaoNeural',
        'cn-m' => 'zh-CN-YunxiNeural',
    ];

    private const SLEEP_MS = 100; // polite throttle between edge-tts calls

    public function handle(): int
    {
        $targetVoices = $this->resolveTargetVoices();
        if (empty($targetVoices)) {
            $this->error('No valid voices selected.');
            return 1;
        }

        $this->info('Voices: ' . implode(', ', array_keys($targetVoices)));

        $pronunciationsOnly = $this->option('pronunciations-only');
        $examplesOnly       = $this->option('examples-only');

        $stats = ['pronunciations' => 0, 'examples' => 0, 'errors' => 0];

        if (! $examplesOnly) {
            $stats['pronunciations'] = $this->generatePronunciations($targetVoices, $stats);
        }
        if (! $pronunciationsOnly) {
            $stats['examples'] = $this->generateExamples($targetVoices, $stats);
        }

        $this->info(sprintf(
            'Done. Pronunciations: %d · Examples: %d · Errors: %d',
            $stats['pronunciations'],
            $stats['examples'],
            $stats['errors']
        ));

        return $stats['errors'] > 0 ? 1 : 0;
    }

    private function resolveTargetVoices(): array
    {
        $voiceOpt = $this->option('voice');
        if ($voiceOpt) {
            if (! isset(self::VOICES[$voiceOpt])) {
                $this->error("Unknown voice key: {$voiceOpt}. Valid: " . implode(', ', array_keys(self::VOICES)));
                return [];
            }
            return [$voiceOpt => self::VOICES[$voiceOpt]];
        }
        return self::VOICES;
    }

    private function generatePronunciations(array $targetVoices, array &$stats): int
    {
        $this->info('--- Pronunciations ---');

        $query = DB::table('word_pronunciations')
            ->select('word_pronunciations.id', 'word_pronunciations.pronunciation_text', 'word_pronunciations.has_audio', 'word_objects.traditional')
            ->join('word_objects', 'word_objects.id', '=', 'word_pronunciations.word_object_id');

        if ($wordSmartId = $this->option('word')) {
            $query->where('word_objects.smart_id', $wordSmartId);
        }
        if ($limit = (int) $this->option('limit')) {
            $query->limit($limit);
        }

        $rows = $query->orderBy('word_pronunciations.id')->get();
        $this->info("Scanning {$rows->count()} pronunciations");

        $count = 0;
        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        foreach ($rows as $row) {
            $hasAudio = $this->decodeHasAudio($row->has_audio);
            $needsGeneration = [];

            foreach ($targetVoices as $key => $voice) {
                if ($this->option('force') || empty($hasAudio[$key])) {
                    $needsGeneration[$key] = $voice;
                }
            }

            if (empty($needsGeneration)) {
                $bar->advance();
                continue;
            }

            // The headword IS the text we synthesize — not the pinyin.
            // Edge TTS uses its own CJK→phonetics; pinyin numbers would read as literal numbers.
            $text = $row->traditional;
            if (! $text) {
                $bar->advance();
                continue;
            }

            foreach ($needsGeneration as $key => $voice) {
                $outputPath = "audio/pronunciations/{$key}/{$row->id}.mp3";
                if ($this->synthesize($text, $voice, $outputPath)) {
                    $hasAudio[$key] = true;
                    $count++;
                } else {
                    $stats['errors']++;
                }
                usleep(self::SLEEP_MS * 1000);
            }

            DB::table('word_pronunciations')
                ->where('id', $row->id)
                ->update(['has_audio' => json_encode($hasAudio)]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        return $count;
    }

    private function generateExamples(array $targetVoices, array &$stats): int
    {
        $this->info('--- Examples ---');

        $query = DB::table('word_sense_examples')
            ->select('word_sense_examples.id', 'word_sense_examples.chinese_text', 'word_sense_examples.has_audio')
            ->where('word_sense_examples.is_suppressed', false);

        if ($wordSmartId = $this->option('word')) {
            $query->join('word_senses', 'word_senses.id', '=', 'word_sense_examples.word_sense_id')
                  ->join('word_objects', 'word_objects.id', '=', 'word_senses.word_object_id')
                  ->where('word_objects.smart_id', $wordSmartId);
        }
        if ($limit = (int) $this->option('limit')) {
            $query->limit($limit);
        }

        $rows = $query->orderBy('word_sense_examples.id')->get();
        $this->info("Scanning {$rows->count()} examples");

        $count = 0;
        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        foreach ($rows as $row) {
            $hasAudio = $this->decodeHasAudio($row->has_audio);
            $needsGeneration = [];

            foreach ($targetVoices as $key => $voice) {
                if ($this->option('force') || empty($hasAudio[$key])) {
                    $needsGeneration[$key] = $voice;
                }
            }

            if (empty($needsGeneration)) {
                $bar->advance();
                continue;
            }

            $text = trim($row->chinese_text);
            if (! $text) {
                $bar->advance();
                continue;
            }

            foreach ($needsGeneration as $key => $voice) {
                $outputPath = "audio/examples/{$key}/{$row->id}.mp3";
                if ($this->synthesize($text, $voice, $outputPath)) {
                    $hasAudio[$key] = true;
                    $count++;
                } else {
                    $stats['errors']++;
                }
                usleep(self::SLEEP_MS * 1000);
            }

            DB::table('word_sense_examples')
                ->where('id', $row->id)
                ->update(['has_audio' => json_encode($hasAudio)]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        return $count;
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
        $process->setTimeout(30);

        try {
            $process->run();
        } catch (\Throwable $e) {
            $this->error("\nedge-tts exception ({$voice}, {$text}): {$e->getMessage()}");
            return false;
        }

        if (! $process->isSuccessful()) {
            $this->error("\nedge-tts failed ({$voice}, {$text}): " . $process->getErrorOutput());
            return false;
        }

        if (! File::exists($absPath) || File::size($absPath) < 500) {
            $this->error("\nedge-tts produced empty/small file ({$voice}, {$text}): {$storagePath}");
            if (File::exists($absPath)) File::delete($absPath);
            return false;
        }

        return true;
    }

    private function decodeHasAudio($raw): array
    {
        if (is_array($raw)) return $raw;
        if (! $raw) return [];
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
