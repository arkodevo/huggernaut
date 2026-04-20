<?php

namespace App\Http\Controllers;

use App\Models\AiUsageLog;
use App\Models\Collection;
use App\Models\CollectionTest;
use App\Models\CollectionTestAnswer;
use App\Models\ShifuEngagement;
use App\Models\UserWordProgress;
use App\Models\WordSense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class CollectionTestController extends Controller
{
    // ── Page route ────────────────────────────────────────────────────────────

    public function show(Collection $collection): View
    {
        abort_unless(Auth::id() === $collection->user_id, 403);

        // Load all senses for all words in collection
        $wordObjectIds = $collection->wordObjects()->pluck('word_objects.id');
        $senses = WordSense::whereIn('word_object_id', $wordObjectIds)
            ->with([
                'wordObject',
                'pronunciation',
                'channel',
                'connotation',
                'tocflLevel',
                'domains' => fn ($q) => $q->with(['labels' => fn ($q) => $q->whereIn('language_id', [1, 2])]),
                'definitions' => fn ($q) => $q->where('language_id', 1)
                    ->orderBy('sort_order')
                    ->with('posLabel'),
                'designations',
                'examples' => fn ($q) => $q->where('is_suppressed', false)->where('is_public', true),
            ])
            ->orderBy('id')
            ->get()
            ->map(fn ($s) => $this->shapeSense($s))
            ->filter()
            ->values();

        // Load distractor pool: senses NOT in this collection's words
        $collectionSenseIds = WordSense::whereIn('word_object_id', $wordObjectIds)->pluck('id');
        $distractors = WordSense::whereNotIn('id', $collectionSenseIds)
            ->whereHas('definitions', fn ($q) => $q->where('language_id', 1))
            ->with([
                'wordObject',
                'pronunciation',
                'channel',
                'connotation',
                'tocflLevel',
                'domains' => fn ($q) => $q->with(['labels' => fn ($q) => $q->whereIn('language_id', [1, 2])]),
                'definitions' => fn ($q) => $q->where('language_id', 1)->orderBy('sort_order')->with('posLabel'),
                'designations',
            ])
            ->inRandomOrder()
            ->limit(30)
            ->get()
            ->map(fn ($s) => $this->shapeSense($s, false))
            ->filter()
            ->values();

        // Load learning progress for words in this collection
        $wordProgress = DB::table('user_word_progress')
            ->where('user_id', Auth::id())
            ->whereIn('word_object_id', $wordObjectIds)
            ->get()
            ->keyBy('word_object_id')
            ->map(fn ($p) => [
                'pinyin_passed'     => (bool) $p->pinyin_passed,
                'definition_passed' => (bool) $p->definition_passed,
                'usage_passed'      => (bool) $p->usage_passed,
            ]);

        return view('collection-test', [
            'collection'    => ['id' => $collection->id, 'name' => $collection->name],
            'senses'        => $senses,
            'distractors'   => $distractors,
            'wordProgress'  => $wordProgress,
            'authUser'      => (new ExploreController())->authUserPayload(),
        ]);
    }

    // ── API: create test session ──────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'collection_id'  => 'required|exists:collections,id',
            'test_mode'      => 'required|string|max:30',
            'attribute_slug' => 'nullable|string|max:30',
            'total_questions' => 'required|integer|min:1',
        ]);

        $test = CollectionTest::create([
            'user_id'         => Auth::id(),
            'collection_id'   => $data['collection_id'],
            'test_mode'       => $data['test_mode'],
            'attribute_slug'  => $data['attribute_slug'] ?? null,
            'total_questions'  => $data['total_questions'],
            'started_at'      => now(),
        ]);

        return response()->json(['testId' => $test->id]);
    }

    // ── API: save one answer ─────────────────────────────────────────────────

    public function storeAnswer(Request $request, CollectionTest $test): JsonResponse
    {
        abort_unless(Auth::id() === $test->user_id, 403);

        $data = $request->validate([
            'word_sense_id'  => 'required|exists:word_senses,id',
            'question_index' => 'required|integer|min:0',
            'correct_value'  => 'required|string',
            'chosen_value'   => 'required|string',
            'is_correct'     => 'required|boolean',
            'hints_used'     => 'nullable|array',
            'score_tier'     => 'required|string|in:clean,assisted,learning',
            'ai_feedback'    => 'nullable|string',
            'time_spent_ms'  => 'nullable|integer',
        ]);

        $answer = CollectionTestAnswer::create([
            'collection_test_id' => $test->id,
            'word_sense_id'      => $data['word_sense_id'],
            'question_index'     => $data['question_index'],
            'correct_value'      => $data['correct_value'],
            'chosen_value'       => $data['chosen_value'],
            'is_correct'         => $data['is_correct'],
            'hints_used'         => $data['hints_used'] ?? [],
            'score_tier'         => $data['score_tier'],
            'ai_feedback'        => $data['ai_feedback'] ?? null,
            'time_spent_ms'      => $data['time_spent_ms'] ?? null,
        ]);

        // Track learning progress for usage tests immediately (AI-graded)
        if ($test->test_mode === 'usage' && $data['is_correct'] && $data['score_tier'] === 'clean') {
            $woId = WordSense::where('id', $data['word_sense_id'])->value('word_object_id');
            if ($woId) {
                UserWordProgress::safeUpsert(Auth::id(), $woId, [
                    'usage_passed' => true, 'usage_passed_at' => now(),
                ]);
            }
        }

        return response()->json(['answerId' => $answer->id]);
    }

    // ── API: complete test ───────────────────────────────────────────────────

    public function complete(CollectionTest $test): JsonResponse
    {
        abort_unless(Auth::id() === $test->user_id, 403);

        $answers = $test->answers;
        $test->update([
            'clean_count'    => $answers->where('score_tier', 'clean')->count(),
            'assisted_count' => $answers->where('score_tier', 'assisted')->count(),
            'learning_count' => $answers->where('score_tier', 'learning')->count(),
            'completed_at'   => now(),
        ]);

        // ── Track learning progress ─────────────────────────────────────────
        $progressColumn = match ($test->test_mode) {
            'pinyin'     => 'pinyin_passed',
            'definition' => 'definition_passed',
            'usage'      => 'usage_passed',
            default      => null,
        };

        if ($progressColumn) {
            $timestampColumn = $progressColumn . '_at';

            // Get word_object_ids where user got a "clean" correct answer
            $passedWordObjectIds = $test->answers()
                ->where('is_correct', true)
                ->where('score_tier', 'clean')
                ->join('word_senses', 'word_senses.id', '=', 'collection_test_answers.word_sense_id')
                ->distinct()
                ->pluck('word_senses.word_object_id');

            foreach ($passedWordObjectIds as $woId) {
                UserWordProgress::safeUpsert(Auth::id(), $woId, [
                    $progressColumn => true, $timestampColumn => now(),
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    // ── API: mark word as learned (self-assessment) ───────────────────────────

    public function markLearned(int $wordObjectId): JsonResponse
    {
        UserWordProgress::safeUpsert(Auth::id(), $wordObjectId, [
            'pinyin_passed'        => true,
            'definition_passed'    => true,
            'usage_passed'         => true,
            'pinyin_passed_at'     => now(),
            'definition_passed_at' => now(),
            'usage_passed_at'      => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ── API: usage check (師父 quick evaluation) ─────────────────────────────

    public function usageCheck(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sentence'       => 'required|string|max:500',
            'word'           => 'required|string',
            'pinyin'         => 'nullable|string',
            'pos'            => 'nullable|string',
            'definition'     => 'nullable|string',
            'register'       => 'nullable|string',
            'connotation'    => 'nullable|string',
            'channel'        => 'nullable|string',
            'domain'         => 'nullable|string',
            'word_sense_id'  => 'nullable|exists:word_senses,id',
            'word_object_id' => 'nullable|exists:word_objects,id',
            'engagement_id'  => 'nullable|string|max:36',
        ]);

        $pos = $data['pos'] ?? '';
        $register = $data['register'] ?? '';
        $connotation = $data['connotation'] ?? '';
        $channel = $data['channel'] ?? '';
        $domain = $data['domain'] ?? '';

        $prompt = "You are 師父 (Shifu), the expert Chinese language tutor for the Living Lexicon 流動. You deeply care about your students and celebrate their progress. You are warm, patient, and encouraging — but always intellectually honest.\n\n"
            . "A student is writing a sentence using the word 「{$data['word']}」 as part of a vocabulary test.\n\n"
            . "Word metadata:\n"
            . "- Word: {$data['word']}\n"
            . ($data['pinyin'] ? "- Pinyin: {$data['pinyin']}\n" : '')
            . ($pos ? "- Part of Speech: {$pos}\n" : '')
            . ($data['definition'] ? "- Definition: {$data['definition']}\n" : '')
            . ($register ? "- Register: {$register}\n" : '')
            . ($connotation ? "- Connotation: {$connotation}\n" : '')
            . ($channel ? "- Channel: {$channel}\n" : '')
            . ($domain ? "- Domain: {$domain}\n" : '')
            . "\nYour task: Evaluate whether the student's sentence uses 「{$data['word']}」 correctly and naturally.\n\n"
            . "IMPORTANT LINGUISTIC RULES:\n"
            . "- Parse the ENTIRE sentence structure carefully before judging. Identify every verb, subject, and object.\n"
            . "- If the word is intransitive (Vi), it does NOT require a direct object — do not penalize correct intransitive usage.\n"
            . "- If the word is transitive (Vt), it should have an appropriate object.\n"
            . "- Consider common collocations and compound usages (e.g. 人才流失 for 流失, 文化流傳 for 流傳).\n"
            . "- A sentence can be correct even if not perfectly elegant. Only mark incorrect for genuine grammatical or usage errors.\n\n"
            . "FEEDBACK STYLE:\n"
            . "- Be warm and encouraging. Acknowledge what the student did well before noting issues.\n"
            . "- This is a TEST — NEVER provide the corrected sentence, rewritten version, or the answer. The student must discover it.\n"
            . "- If incorrect: give a gentle, specific nudge (e.g. 'Think about what connects these two clauses' or 'The verb placement feels off — where does the action belong?'). Guide, don't tell.\n"
            . "- If correct: celebrate the usage and briefly explain why it works — point out the specific grammatical or collocational success.\n"
            . "- Keep feedback to 2-3 sentences maximum.\n\n"
            . "Respond ONLY in JSON (no markdown): { \"correct\": true/false, \"explanation\": \"your warm, precise feedback in English\" }";

        // Inject persona overlay
        $personaSlug = Auth::user()?->shifu_persona ?? 'dragon';
        $persona = config("shifu-personas.{$personaSlug}");
        if ($persona) {
            $prompt .= "\n\nFEEDBACK STYLE PERSONA:\n" . $persona['prompt'];
        }

        try {
            $response = Http::withHeaders([
                'x-api-key'         => config('services.anthropic.key'),
                'anthropic-version'  => '2023-06-01',
                'content-type'       => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model'       => config('services.anthropic.model', 'claude-sonnet-4-20250514'),
                'max_tokens'  => 300,
                'temperature' => 0.2,
                'system'      => $prompt,
                'messages'    => [['role' => 'user', 'content' => $data['sentence']]],
            ]);

            $body = $response->json();
            $text = $body['content'][0]['text'] ?? '{}';

            // Log usage
            AiUsageLog::create([
                'user_id'       => Auth::id(),
                'word_sense_id' => $data['word_sense_id'] ?? null,
                'request_type'  => 'test_usage_check',
                'credits_used'  => 1,
            ]);

            $clean = preg_replace('/```json|```/', '', $text);
            $result = json_decode(trim($clean), true) ?: ['correct' => false, 'explanation' => 'Unable to parse response.'];

            // ── Engagement tracking ──
            $engagementUuid = $data['engagement_id'] ?? null;
            $engagement = $engagementUuid
                ? ShifuEngagement::where('uuid', $engagementUuid)->first()
                : null;

            if (! $engagement) {
                $engagement = ShifuEngagement::create([
                    'user_id'        => Auth::id(),
                    'word_sense_id'  => $data['word_sense_id'] ?? null,
                    'word_object_id' => $data['word_object_id'] ?? null,
                    'context'        => 'test',
                    'word_label'     => $data['word'],
                    'started_at'     => now(),
                ]);
            }

            $isCorrect = $result['correct'] ?? false;
            $engagement->addInteraction(
                $data['sentence'],
                $result['explanation'] ?? '',
                $isCorrect,
            );

            // Auto-complete on correct or final attempt (handled by frontend)
            $result['engagement_id'] = $engagement->uuid;

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['correct' => false, 'explanation' => 'Error evaluating sentence.'], 500);
        }
    }

    // ── Data shaping ─────────────────────────────────────────────────────────

    private function shapeSense(WordSense $sense, bool $includeExamples = true): ?array
    {
        $wo = $sense->wordObject;
        if (!$wo) return null;

        $defs = $sense->definitions;
        if ($defs->isEmpty()) return null;

        // Register
        $registerSlugs = ['standard', 'literary', 'formal', 'informal', 'colloquial', 'slang'];
        $registerDes = $sense->designations->first(fn ($d) => in_array($d->slug, $registerSlugs));
        $registerMap = ['standard' => 'neutral', 'literary' => 'literary', 'formal' => 'formal', 'informal' => 'informal', 'colloquial' => 'colloquial', 'slang' => 'slang'];
        $register = $registerMap[$registerDes?->slug ?? 'standard'] ?? 'neutral';

        // Dimension
        $dimSlugs = ['abstract', 'concrete', 'internal', 'external', 'dim-fluid'];
        $dimMap = ['dim-fluid' => 'fluid', 'abstract' => 'abstract', 'concrete' => 'concrete', 'internal' => 'internal', 'external' => 'external'];
        $dimensions = $sense->designations
            ->filter(fn ($d) => in_array($d->slug, $dimSlugs))
            ->map(fn ($d) => $dimMap[$d->slug] ?? $d->slug)
            ->values()->all();

        // Channel
        $channelMap = ['channel-balanced' => 'balanced', 'fluid' => 'balanced', 'spoken-only' => 'spoken-only', 'spoken-dominant' => 'spoken-dominant', 'written-dominant' => 'written-dominant', 'written-only' => 'written-only'];
        $channel = $channelMap[$sense->channel?->slug ?? 'channel-balanced'] ?? 'balanced';

        // Connotation
        $connotation = $sense->connotation?->slug ?? 'neutral';

        // Intensity
        $intensity = $sense->intensity ?? 2;

        // TOCFL
        $tocflSlugMap = ExploreController::TOCFL_SLUG_MAP;
        $tocflSlug = $sense->tocflLevel?->slug;
        $tocfl = $tocflSlug ? ($tocflSlugMap[$tocflSlug] ?? null) : null;

        // Domains
        $primaryDomain = $sense->domains->first();
        $domainLabel = $primaryDomain?->labels->firstWhere('language_id', 1)?->label ?? '';
        $domainLabelZh = $primaryDomain?->labels->firstWhere('language_id', 2)?->label ?? '';
        $secondaryDomains = $sense->domains->slice(1)->map(fn ($d) => [
            'en' => $d->labels->firstWhere('language_id', 1)?->label ?? '',
            'zh' => $d->labels->firstWhere('language_id', 2)?->label ?? '',
        ])->values()->all();

        // Definitions
        $shapedDefs = $defs->map(fn ($d) => [
            'pos'    => $d->posLabel?->slug ?? '',
            'posFull' => ExploreController::POS_FULL_NAMES[$d->posLabel?->slug ?? ''] ?? '',
            'def'    => $d->definition_text,
        ])->values()->all();

        $shaped = [
            'senseId'      => $sense->id,
            'wordObjectId' => $wo->id,
            'smartId'      => $wo->smart_id,
            'traditional'  => $wo->traditional,
            'simplified'  => $wo->simplified ?? '',
            'pinyin'      => $sense->pronunciation?->pronunciation_text ?? '',
            'definitions' => $shapedDefs,
            'domain'      => $domainLabel,
            'domainZh'    => $domainLabelZh,
            'secondaryDomains' => $secondaryDomains,
            'register'    => $register,
            'connotation' => $connotation,
            'channel'     => $channel,
            'dimensions'  => $dimensions,
            'intensity'   => $intensity,
            'tocfl'       => $tocfl,
        ];

        if ($includeExamples) {
            $exIds = $sense->examples->pluck('id')->all();
            $exTrans = ! empty($exIds)
                ? \DB::table('word_sense_example_translations')
                    ->whereIn('word_sense_example_id', $exIds)
                    ->get()
                    ->groupBy('word_sense_example_id')
                : collect();

            $shaped['examples'] = $sense->examples->map(function ($ex) use ($exTrans) {
                $trans = $exTrans->get($ex->id, collect())->pluck('translation_text', 'language_id');
                return [
                    'cn'           => $ex->chinese_text,
                    'en'           => $trans->get(1),
                    'translations' => $trans->all(),
                ];
            })->values()->all();
        }

        return $shaped;
    }
}
