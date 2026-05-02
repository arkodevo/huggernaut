<?php

namespace App\Console\Commands;

use App\Models\WordPronunciation;
use App\Models\WordSenseExample;
use App\Services\AudioGenerator;
use Illuminate\Console\Command;

// Bulk / manual audio generation. Iterates pronunciations and example
// sentences, delegating per-row work to AudioGenerator. The same service
// powers the queued GenerateRowAudio job, so manual sweeps and live
// observer-driven dispatches behave identically.
//
// Freshness is determined by AudioGenerator::isFresh (hash + has_audio).
// A massive `--force` sweep ignores the freshness check; a normal sweep
// only does real work where files are missing or the text has drifted
// since the audio was last written.
class GenerateAudio extends Command
{
    protected $signature = 'audio:generate
        {--word= : Only generate for a single word (pass smart_id, e.g. u6d41_u52d5)}
        {--voice= : Only generate this voice key (tw-f, tw-m, cn-f, cn-m)}
        {--pronunciations-only : Skip example sentences}
        {--examples-only : Skip word pronunciations}
        {--force : Regenerate files even if has_audio + hash agree}
        {--limit= : Cap the number of rows processed per target type (useful for testing)}';

    protected $description = 'Generate neural TTS audio (TW F/M + CN F/M) via edge-tts. Hash-aware: skips rows whose audio_text_hash matches the current source text and whose has_audio covers the requested voices.';

    public function handle(AudioGenerator $generator): int
    {
        $targetVoices = $this->resolveTargetVoices();
        if (empty($targetVoices)) {
            $this->error('No valid voices selected.');
            return 1;
        }

        $this->info('Voices: ' . implode(', ', array_keys($targetVoices)));

        $force              = (bool) $this->option('force');
        $pronunciationsOnly = $this->option('pronunciations-only');
        $examplesOnly       = $this->option('examples-only');

        $stats = ['pronunciations' => 0, 'examples' => 0, 'errors' => 0];

        if (! $examplesOnly) {
            $stats['pronunciations'] = $this->generatePronunciations($generator, $targetVoices, $force, $stats);
        }
        if (! $pronunciationsOnly) {
            $stats['examples'] = $this->generateExamples($generator, $targetVoices, $force, $stats);
        }

        $this->info(sprintf(
            'Done. Pronunciations: %d · Examples: %d · Errors: %d',
            $stats['pronunciations'],
            $stats['examples'],
            $stats['errors']
        ));

        return $stats['errors'] > 0 ? 1 : 0;
    }

    /** @return array<string,string> */
    private function resolveTargetVoices(): array
    {
        $voiceOpt = $this->option('voice');
        if ($voiceOpt) {
            if (! isset(AudioGenerator::VOICES[$voiceOpt])) {
                $this->error("Unknown voice key: {$voiceOpt}. Valid: " . implode(', ', array_keys(AudioGenerator::VOICES)));
                return [];
            }
            return [$voiceOpt => AudioGenerator::VOICES[$voiceOpt]];
        }
        return AudioGenerator::VOICES;
    }

    /** @param array<string,string> $targetVoices */
    private function generatePronunciations(AudioGenerator $generator, array $targetVoices, bool $force, array &$stats): int
    {
        $this->info('--- Pronunciations ---');

        $query = WordPronunciation::query()->with('wordObject');
        if ($wordSmartId = $this->option('word')) {
            $query->whereHas('wordObject', fn ($q) => $q->where('smart_id', $wordSmartId));
        }
        if ($limit = (int) $this->option('limit')) {
            $query->limit($limit);
        }

        $rows = $query->orderBy('id')->get();
        $this->info("Scanning {$rows->count()} pronunciations");

        $count = 0;
        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        foreach ($rows as $p) {
            $result = $generator->regeneratePronunciation($p, $targetVoices, $force);
            $count          += $result['generated'];
            $stats['errors'] += $result['errors'];
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        return $count;
    }

    /** @param array<string,string> $targetVoices */
    private function generateExamples(AudioGenerator $generator, array $targetVoices, bool $force, array &$stats): int
    {
        $this->info('--- Examples ---');

        $query = WordSenseExample::query()->where('is_suppressed', false);
        if ($wordSmartId = $this->option('word')) {
            $query->whereHas('wordSense.wordObject', fn ($q) => $q->where('smart_id', $wordSmartId));
        }
        if ($limit = (int) $this->option('limit')) {
            $query->limit($limit);
        }

        $rows = $query->orderBy('id')->get();
        $this->info("Scanning {$rows->count()} examples");

        $count = 0;
        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        foreach ($rows as $e) {
            $result = $generator->regenerateExample($e, $targetVoices, $force);
            $count          += $result['generated'];
            $stats['errors'] += $result['errors'];
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        return $count;
    }
}
