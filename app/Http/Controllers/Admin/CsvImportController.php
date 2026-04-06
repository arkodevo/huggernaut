<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use App\Models\Designation;
use App\Models\Language;
use App\Models\PosLabel;
use App\Models\WordObject;
use App\Models\WordPronunciation;
use App\Models\WordSense;
use App\Models\WordSenseDefinition;
use App\Models\WordSenseExample;
use App\Models\SenseRelationType;
use App\Services\ShifuWordEnricher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CsvImportController extends Controller
{
    private const PINYIN_SYSTEM_ID = 1;
    private const MAX_PER_BATCH = 9;

    // ── Upload form ──────────────────────────────────────────────────

    public function showUpload(): View
    {
        return view('admin.words.csv-upload');
    }

    // ── Parse CSV → redirect to review page ──────────────────────────

    public function process(Request $request): View|RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'max:200', 'mimes:csv,txt'],
        ]);

        $file = $request->file('csv_file');
        $lines = array_filter(
            array_map('trim', file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))
        );

        // Remove BOM
        if (! empty($lines[0])) {
            $lines[0] = preg_replace('/^\xEF\xBB\xBF/', '', $lines[0]);
        }

        // Remove header row
        if (! empty($lines[0]) && preg_match('/^[a-zA-Z]/', $lines[0])) {
            array_shift($lines);
        }

        $words = array_values(array_unique($lines));

        if (empty($words)) {
            return back()->with('error', 'CSV file is empty.');
        }

        // Validate CJK
        $invalid = [];
        foreach ($words as $w) {
            if (! preg_match('/^[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}\x{20000}-\x{2a6df}\x{2a700}-\x{2b73f}]+$/u', $w)) {
                $invalid[] = $w;
            }
        }
        if ($invalid) {
            return back()->with('error', 'Invalid characters: ' . implode(', ', $invalid));
        }

        // Split into current batch + queue
        if (count($words) > self::MAX_PER_BATCH) {
            $currentBatch = array_slice($words, 0, self::MAX_PER_BATCH);
            session(['csv_import_queue' => array_slice($words, self::MAX_PER_BATCH)]);
        } else {
            $currentBatch = $words;
            session()->forget('csv_import_queue');
        }

        // Check existing words and their review status
        $existing = WordObject::whereIn('traditional', $currentBatch)
            ->select('traditional', 'shifu_reviewed_at', 'status', 'smart_id')
            ->get()
            ->keyBy('traditional');

        // Build word list with status
        $wordList = [];
        foreach ($currentBatch as $trad) {
            $ex = $existing->get($trad);
            $wordList[] = [
                'traditional'    => $trad,
                'exists'         => (bool) $ex,
                'reviewed'       => $ex?->shifu_reviewed_at ? true : false,
                'reviewed_at'    => $ex?->shifu_reviewed_at,
                'current_status' => $ex?->status,
            ];
        }

        $queue = session('csv_import_queue', []);
        $hasMore = ! empty($queue);
        $remainingCount = count($queue);

        return view('admin.words.csv-review', compact('wordList', 'hasMore', 'remainingCount'));
    }

    // ── AJAX: Enrich a single word ───────────────────────────────────

    public function enrichWord(Request $request): JsonResponse
    {
        $request->validate(['traditional' => 'required|string|max:16']);
        $traditional = $request->traditional;

        $enricher = new ShifuWordEnricher();
        $enriched = $enricher->enrich($traditional);

        if (isset($enriched['error'])) {
            return response()->json(['error' => $enriched['error']], 502);
        }

        // Check for existing word and find gaps
        $existingWord = WordObject::where('traditional', $traditional)
            ->with([
                'senses' => fn ($q) => $q->orderBy('id')->with([
                    'pronunciation',
                    'definitions' => fn ($q) => $q->orderBy('sort_order')->with(['posLabel', 'language']),
                    'channel', 'connotation', 'semanticMode', 'sensitivity',
                    'tocflLevel', 'hskLevel', 'designations.attribute', 'domains',
                    'examples' => fn ($q) => $q->orderBy('id'),
                ]),
            ])
            ->first();

        $gaps = $existingWord ? $this->findGaps($existingWord, $enriched) : [];

        // Log AI usage
        AiUsageLog::create([
            'user_id'      => Auth::id(),
            'request_type' => $existingWord ? 'csv_import_audit' : 'csv_import_enrichment',
            'credits_used' => 1,
        ]);

        // Build list of existing sense keys (pinyin|POS) for frontend matching
        $existingKeys = [];
        if ($existingWord) {
            foreach ($existingWord->senses as $sense) {
                $pron = $sense->pronunciation?->pronunciation_text ?? '';
                $pos = $sense->definitions->where('language_id', 1)->first()?->posLabel?->slug
                    ?? $sense->posLabels->first()?->slug ?? '';
                $existingKeys[] = $pron . '|' . $pos;
            }
        }

        return response()->json([
            'data'         => $enriched,
            'gaps'         => $gaps,
            'existing'     => (bool) $existingWord,
            'existingKeys' => $existingKeys,
        ]);
    }

    // ── AJAX: Save a single word ─────────────────────────────────────

    public function saveWord(Request $request): JsonResponse
    {
        $request->validate([
            'data'            => 'required|array',
            'sense_decisions' => 'required|array',
        ]);

        $entry = $request->data;
        $senseDecisions = $request->sense_decisions;
        $existingKeys = $request->existing_keys ?? [];

        // Guard: if all senses rejected, do nothing
        $allRejected = collect($senseDecisions)->every(fn ($d) => $d === 'reject');
        if ($allRejected) {
            return response()->json(['ok' => true, 'created' => 0, 'enriched' => 0, 'rejected' => count($senseDecisions)]);
        }

        $langEn       = Language::where('code', 'en')->value('id');
        $langZh       = Language::where('code', 'zh-TW')->value('id');
        $designations = Designation::all()->keyBy('slug')->map->id->all();
        $posLabels    = PosLabel::all()->keyBy('slug')->map->id->all();
        $relationTypes = SenseRelationType::all()->keyBy('slug')->map->id->all();

        DB::beginTransaction();

        try {
            $w = $entry['word'];
            $word = WordObject::where('smart_id', $w['smart_id'])->first();
            $isNew = ! $word;

            if ($isNew) {
                // Determine status from first non-rejected sense
                $firstAction = collect($senseDecisions)->first(fn ($d) => $d !== 'reject') ?? 'draft';
                $status = $firstAction === 'publish' ? 'published' : 'draft';

                $word = WordObject::create([
                    'smart_id'          => $w['smart_id'],
                    'traditional'       => $w['traditional'],
                    'simplified'        => $w['simplified'] ?? $w['traditional'],
                    'structure'         => $w['structure'] ?? 'single',
                    'status'            => $status,
                    'shifu_reviewed_at' => now(),
                ]);
            } else {
                // Determine highest status from sense decisions
                $hasPublish = in_array('publish', $senseDecisions) || in_array('enrich', $senseDecisions);
                $wordStatus = $hasPublish ? 'published' : $word->status;

                $word->update([
                    'simplified'        => $w['simplified'] ?? $w['traditional'],
                    'structure'         => $w['structure'] ?? $word->structure,
                    'status'            => $wordStatus,
                    'shifu_reviewed_at' => now(),
                ]);
            }

            // Load existing senses for matching
            $existingSenses = $isNew ? collect() : $word->senses()->with(['pronunciation', 'posLabels'])->get();

            $created = $enriched = $rejected = 0;

            foreach ($entry['senses'] as $si => $s) {
                $decision = $senseDecisions[$si] ?? 'reject';

                if ($decision === 'reject') {
                    $rejected++;
                    continue;
                }

                $senseKey = ($s['pinyin'] ?? '') . '|' . ($s['pos'] ?? '');
                $isExistingSense = in_array($senseKey, $existingKeys);
                $status = $decision === 'publish' ? 'published' : 'draft';

                if ($isExistingSense && $decision === 'enrich') {
                    // ENRICH: update enrichment content, preserve source + TOCFL/HSK
                    $matchedSense = $existingSenses->first(function ($es) use ($s) {
                        return $es->pronunciation?->pronunciation_text === ($s['pinyin'] ?? '')
                            && ($es->posLabels->first()?->slug ?? '') === ($s['pos'] ?? '');
                    });

                    if ($matchedSense) {
                        $matchedSense->update([
                            'channel_id'       => isset($s['channel']) && $s['channel'] ? ($designations[$s['channel']] ?? $matchedSense->channel_id) : $matchedSense->channel_id,
                            'connotation_id'   => isset($s['connotation']) && $s['connotation'] ? ($designations[$s['connotation']] ?? $matchedSense->connotation_id) : $matchedSense->connotation_id,
                            'semantic_mode_id' => isset($s['semantic_mode']) && $s['semantic_mode'] ? ($designations[$s['semantic_mode']] ?? $matchedSense->semantic_mode_id) : $matchedSense->semantic_mode_id,
                            'sensitivity_id'   => isset($s['sensitivity']) && $s['sensitivity'] ? ($designations[$s['sensitivity']] ?? $matchedSense->sensitivity_id) : $matchedSense->sensitivity_id,
                            'intensity'        => $s['intensity'] ?? $matchedSense->intensity,
                            'valency'          => $s['valency'] ?? $matchedSense->valency,
                            'formula'          => $s['formula'] ?? $matchedSense->formula,
                            'usage_note'       => $s['usage_note'] ?? $matchedSense->usage_note,
                            'learner_traps'    => $s['learner_traps'] ?? $matchedSense->learner_traps,
                            'enriched_by'      => 'shifu',
                            'enriched_at'      => now(),
                            // source, tocfl_level_id, hsk_level_id — NOT touched
                        ]);
                        $enriched++;
                    }
                } else {
                    // ADD NEW SENSE (new word or new sense on existing word)
                    $s['source'] = 'editorial';
                    $s['alignment'] = 'partial';
                    $s['enriched_by'] = 'shifu';
                    $s['tocfl'] = null;
                    $s['hsk'] = null;
                    $this->importSense($word, $s, $si, $status, $langEn, $langZh, $designations, $posLabels, $relationTypes);
                    $created++;
                }
            }

            DB::commit();

            // Bust all lexicon caches
            cache()->forget('lexicon_words');
            cache()->forget('lexicon_words_slim');
            cache()->forget('lexicon_domain_groups');
            cache()->forget('word_index_slim');
            cache()->flush(); // Nuclear option — clear everything

            return response()->json([
                'ok'       => true,
                'created'  => $created,
                'enriched' => $enriched,
                'rejected' => $rejected,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Save failed: ' . $e->getMessage()], 500);
        }
    }

    // ── Process next batch (from queue) ──────────────────────────────

    public function processNext(): View|RedirectResponse
    {
        $queue = session('csv_import_queue', []);

        if (empty($queue)) {
            return redirect()->route('admin.words.index')
                ->with('success', 'All queued words have been processed.');
        }

        $currentBatch = array_slice($queue, 0, self::MAX_PER_BATCH);
        $remaining = array_slice($queue, self::MAX_PER_BATCH);
        session(['csv_import_queue' => $remaining]);

        $existing = WordObject::whereIn('traditional', $currentBatch)
            ->select('traditional', 'shifu_reviewed_at', 'status', 'smart_id')
            ->get()
            ->keyBy('traditional');

        $wordList = [];
        foreach ($currentBatch as $trad) {
            $ex = $existing->get($trad);
            $wordList[] = [
                'traditional'    => $trad,
                'exists'         => (bool) $ex,
                'reviewed'       => $ex?->shifu_reviewed_at ? true : false,
                'reviewed_at'    => $ex?->shifu_reviewed_at,
                'current_status' => $ex?->status,
            ];
        }

        $hasMore = ! empty($remaining);
        $remainingCount = count($remaining);

        return view('admin.words.csv-review', compact('wordList', 'hasMore', 'remainingCount'));
    }

    // ── Private: find gaps in existing word ──────────────────────────

    private function findGaps(WordObject $existing, array $enriched): array
    {
        $gaps = [];

        $existingSenseCount = $existing->senses->count();
        $enrichedSenseCount = count($enriched['senses'] ?? []);

        if ($enrichedSenseCount > $existingSenseCount) {
            $gaps[] = "師父 found {$enrichedSenseCount} senses but DB only has {$existingSenseCount}. Missing senses?";
        }

        foreach ($existing->senses as $sense) {
            $enDef = $sense->definitions->where('language_id', 1)->first();
            $zhDef = $sense->definitions->where('language_id', 2)->first();
            $label = $sense->pronunciation?->pronunciation_text ?? $existing->traditional;

            if (! $zhDef?->definition_text) $gaps[] = "{$label}: missing ZH-TW definition.";
            if (! $sense->formula) $gaps[] = "{$label}: missing formula.";
            if (! $sense->usage_note) $gaps[] = "{$label}: missing usage note.";
            if (! $sense->learner_traps) $gaps[] = "{$label}: missing learner traps.";
            if ($sense->examples->count() < 2) $gaps[] = "{$label}: only {$sense->examples->count()} example(s).";
            if (! $sense->channel_id) $gaps[] = "{$label}: missing channel.";
            if (! $sense->connotation_id) $gaps[] = "{$label}: missing connotation.";
        }

        return $gaps;
    }

    // ── Private: import a single sense ───────────────────────────────

    private function importSense(
        WordObject $word, array $s, int $sortOrder, string $status,
        int $langEn, int $langZh, array $designations, array $posLabels,
        array $relationTypes = []
    ): void {
        $pronunciation = WordPronunciation::firstOrCreate(
            [
                'word_object_id'          => $word->id,
                'pronunciation_system_id' => self::PINYIN_SYSTEM_ID,
                'pronunciation_text'      => $s['pinyin'] ?? '',
            ],
            ['is_primary' => $sortOrder === 0]
        );

        $channelId      = isset($s['channel'])       && $s['channel']       ? ($designations[$s['channel']]       ?? null) : null;
        $connotationId  = isset($s['connotation'])   && $s['connotation']   ? ($designations[$s['connotation']]   ?? null) : null;
        $semanticModeId = isset($s['semantic_mode']) && $s['semantic_mode'] ? ($designations[$s['semantic_mode']] ?? null) : null;
        $sensitivityId  = isset($s['sensitivity'])   && $s['sensitivity']   ? ($designations[$s['sensitivity']]   ?? null) : null;
        $tocflId        = isset($s['tocfl'])          && $s['tocfl']         ? ($designations[$s['tocfl']]         ?? null) : null;
        $hskId          = isset($s['hsk'])            && $s['hsk']           ? ($designations[$s['hsk']]           ?? null) : null;

        $sense = WordSense::create([
            'word_object_id'   => $word->id,
            'pronunciation_id' => $pronunciation->id,
            'channel_id'       => $channelId,
            'connotation_id'   => $connotationId,
            'semantic_mode_id' => $semanticModeId,
            'sensitivity_id'   => $sensitivityId,
            'intensity'        => $s['intensity'] ?? null,
            'valency'          => $s['valency'] ?? null,
            'formula'          => $s['formula'] ?? null,
            'usage_note'       => $s['usage_note'] ?? null,
            'learner_traps'    => $s['learner_traps'] ?? null,
            'tocfl_level_id'   => $tocflId,
            'hsk_level_id'     => $hskId,
            'status'           => $status,
            'source'           => $s['source'] ?? 'editorial',
            'alignment'        => $s['alignment'] ?? 'partial',
            'enriched_by'      => $s['enriched_by'] ?? null,
            'enriched_at'      => isset($s['enriched_by']) ? now() : null,
        ]);

        // Domains (ordered, max 4)
        $domainSync = [];
        foreach (array_slice($s['domains'] ?? [], 0, 4) as $idx => $slug) {
            $id = $designations[$slug] ?? null;
            if ($id) $domainSync[$id] = ['sort_order' => $idx];
        }
        if ($domainSync) $sense->domains()->sync($domainSync);

        // Designations (register + dimension)
        $designationIds = [];
        foreach ($s['register'] ?? [] as $reg) {
            $id = $designations[$reg] ?? null;
            if ($id) $designationIds[] = $id;
        }
        foreach ($s['dimension'] ?? [] as $dim) {
            $id = $designations[$dim] ?? null;
            if ($id) $designationIds[] = $id;
        }
        if ($designationIds) $sense->designations()->attach(array_unique($designationIds));

        // POS
        $posId = $posLabels[$s['pos']] ?? null;

        // Definitions
        $defEn = WordSenseDefinition::create([
            'word_sense_id'   => $sense->id,
            'language_id'     => $langEn,
            'pos_id'          => $posId,
            'definition_text' => $s['definitions']['en'] ?? '',
            'formula'         => $s['formula'] ?? null,
            'usage_note'      => $s['usage_note'] ?? null,
            'sort_order'      => 0,
        ]);

        if (! empty($s['definitions']['zh-TW'])) {
            WordSenseDefinition::create([
                'word_sense_id'   => $sense->id,
                'language_id'     => $langZh,
                'pos_id'          => $posId,
                'definition_text' => $s['definitions']['zh-TW'],
                'sort_order'      => 0,
            ]);
        }

        if ($posId) {
            $sense->posLabels()->attach($posId, ['is_primary' => true]);
        }

        foreach ($s['examples'] ?? [] as $ex) {
            WordSenseExample::create([
                'word_sense_id' => $sense->id,
                'definition_id' => $defEn->id,
                'chinese_text'  => $ex['chinese'] ?? '',
                'english_text'  => $ex['english'] ?? null,
                'source'        => 'default',
                'is_public'     => true,
                'is_suppressed' => false,
            ]);
        }

        // Relations — look up target words by traditional character
        $relMap = [
            'synonym_close'   => $s['relations']['synonym_close']   ?? [],
            'synonym_related' => $s['relations']['synonym_related'] ?? [],
            'antonym'         => $s['relations']['antonym']         ?? [],
            'contrast'        => $s['relations']['contrast']        ?? [],
        ];

        foreach ($relMap as $relSlug => $targets) {
            $typeId = $relationTypes[$relSlug] ?? null;
            if (! $typeId || empty($targets)) continue;

            foreach ($targets as $targetTrad) {
                $targetTrad = trim($targetTrad);
                if (empty($targetTrad)) continue;

                DB::table('word_sense_relations')->insertOrIgnore([
                    'word_sense_id'     => $sense->id,
                    'related_word_text' => $targetTrad,
                    'relation_type_id'  => $typeId,
                    'editorial_note'    => null,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }

        // Collocations — store as text, linking happens at render time
        foreach ($s['collocations'] ?? [] as $collText) {
            $collText = trim($collText);
            if (empty($collText)) continue;

            DB::table('word_sense_collocations')->insertOrIgnore([
                'word_sense_id'   => $sense->id,
                'collocation_text' => $collText,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}
