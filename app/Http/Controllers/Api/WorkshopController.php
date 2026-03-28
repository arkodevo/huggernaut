<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use App\Models\ShifuEngagement;
use App\Models\UserSavedExample;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WorkshopController extends Controller
{
    /**
     * Proxy a critique request to the Anthropic API.
     */
    public function critique(Request $request): JsonResponse
    {
        $request->validate([
            'system_prompt'  => ['required', 'string', 'max:8000'],
            'sentence'       => ['required', 'string', 'max:2000'],
            'engagement_id'  => ['nullable', 'string', 'max:36'],
            'word_label'     => ['nullable', 'string', 'max:32'],
        ]);

        $result = $this->callAnthropic(
            $request->input('system_prompt'),
            $request->input('sentence'),
        );

        if (isset($result['error'])) {
            return response()->json(['error' => 'AI request failed'], 502);
        }

        // Log usage (user_id nullable for guests)
        AiUsageLog::create([
            'user_id'       => Auth::id(),
            'word_sense_id' => $request->input('word_sense_id'),
            'request_type'  => 'feedback',
            'credits_used'  => 1,
        ]);

        $text = collect($result['content'] ?? [])
            ->map(fn ($b) => $b['text'] ?? '')
            ->join('');

        // ── Engagement tracking ──
        $engagementUuid = $request->input('engagement_id');
        $engagement = null;

        if ($engagementUuid) {
            $engagement = ShifuEngagement::where('uuid', $engagementUuid)->first();
        }

        if (! $engagement) {
            $engagement = ShifuEngagement::create([
                'user_id'        => Auth::id(),
                'word_sense_id'  => $request->input('word_sense_id'),
                'word_object_id' => $request->input('word_object_id'),
                'context'        => 'writing_conservatory',
                'word_label'     => $request->input('word_label', ''),
                'started_at'     => now(),
            ]);
        }

        $engagement->addInteraction(
            $request->input('sentence'),
            $text,
        );

        return response()->json([
            'text'          => $text,
            'engagement_id' => $engagement->uuid,
        ]);
    }

    /**
     * Proxy a theme generation request to the Anthropic API.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'system_prompt' => ['required', 'string', 'max:8000'],
            'theme'         => ['required', 'string', 'max:500'],
            'word_label'    => ['nullable', 'string', 'max:32'],
        ]);

        $result = $this->callAnthropic(
            $request->input('system_prompt'),
            'Theme/subject: ' . $request->input('theme'),
        );

        if (isset($result['error'])) {
            return response()->json(['error' => 'AI request failed'], 502);
        }

        AiUsageLog::create([
            'user_id'       => Auth::id(),
            'word_sense_id' => $request->input('word_sense_id'),
            'request_type'  => 'generation',
            'credits_used'  => 1,
        ]);

        $text = collect($result['content'] ?? [])
            ->map(fn ($b) => $b['text'] ?? '')
            ->join('');

        // ── Engagement tracking ──
        $engagement = ShifuEngagement::create([
            'user_id'        => Auth::id(),
            'word_sense_id'  => $request->input('word_sense_id'),
            'word_object_id' => $request->input('word_object_id'),
            'context'        => 'generation',
            'word_label'     => $request->input('word_label', ''),
            'started_at'     => now(),
            'completed_at'   => now(),
            'outcome'        => 'saved',
        ]);

        $engagement->addInteraction(
            'Theme/subject: ' . $request->input('theme'),
            $text,
        );

        return response()->json([
            'text'          => $text,
            'engagement_id' => $engagement->uuid,
        ]);
    }

    /**
     * Save a user-authored (optionally AI-verified) example sentence.
     */
    public function saveExample(Request $request): JsonResponse
    {
        $request->validate([
            'word_sense_id'    => ['required', 'integer', 'exists:word_senses,id'],
            'word_object_id'   => ['nullable', 'integer', 'exists:word_objects,id'],
            'chinese_text'     => ['required', 'string', 'max:2000'],
            'english_text'     => ['required', 'string', 'max:2000'],
            'ai_verified'      => ['boolean'],
            'ai_feedback'      => ['nullable', 'string', 'max:5000'],
            'original_chinese_text' => ['nullable', 'string', 'max:2000'],
            'source_type'      => ['nullable', 'string', 'in:learner,generated'],
            'assessed_level'   => ['nullable', 'string', 'in:beginner,learner,developing,advanced,fluent'],
            'assessed_mastery' => ['nullable', 'string', 'in:seed,sprout,bud,flower,fruit'],
            'mastery_guidance' => ['nullable', 'string', 'max:5000'],
            'engagement_id'    => ['nullable', 'string', 'max:36'],
        ]);

        $example = UserSavedExample::create([
            'user_id'          => Auth::id(),
            'word_sense_id'    => $request->input('word_sense_id'),
            'word_object_id'   => $request->input('word_object_id'),
            'chinese_text'     => $request->input('chinese_text'),
            'english_text'     => $request->input('english_text'),
            'original_chinese_text' => $request->input('original_chinese_text'),
            'ai_verified'      => $request->boolean('ai_verified', false),
            'ai_feedback'      => $request->input('ai_feedback'),
            'source_type'      => $request->input('source_type', 'learner'),
            'assessed_level'   => $request->input('assessed_level'),
            'assessed_mastery' => $request->input('assessed_mastery'),
            'mastery_guidance' => $request->input('mastery_guidance'),
            'is_public'        => false,
        ]);

        // ── Close engagement on save ──
        $engagementUuid = $request->input('engagement_id');
        if ($engagementUuid) {
            $engagement = ShifuEngagement::where('uuid', $engagementUuid)->first();
            if ($engagement) {
                $engagement->complete('saved');
            }
        }

        return response()->json($example, 201);
    }

    /**
     * Delete a saved example belonging to the authenticated user.
     */
    public function deleteExample(int $id): JsonResponse
    {
        $deleted = UserSavedExample::where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['deleted' => (bool) $deleted]);
    }

    /**
     * Update the authenticated user's fluency level.
     */
    public function updateFluencyLevel(Request $request): JsonResponse
    {
        $request->validate([
            'fluency_level' => ['required', 'string', 'in:beginner,learner,developing,advanced,fluent'],
        ]);

        $user = Auth::user();
        $user->fluency_level = $request->input('fluency_level');
        $user->save();

        return response()->json(['fluency_level' => $user->fluency_level]);
    }

    /**
     * Analyze a sentence/phrase: translate, assess, annotate words.
     */
    public function analyze(Request $request): JsonResponse
    {
        $request->validate([
            'text' => ['required', 'string', 'max:2000'],
        ]);

        $text = $request->input('text');

        $prompt = "You are 師父 (Shifu), the expert Chinese language tutor for the Living Lexicon 流動. "
            . "You are warm, encouraging, and intellectually precise.\n\n"
            . "A learner wants you to analyze the following Chinese text.\n\n"
            . "Your task:\n"
            . "1. Provide a natural, fluent English translation\n"
            . "2. Give brief feedback on the text (grammar, register, naturalness — 2-3 sentences)\n"
            . "3. For key words, provide brief notes (meaning in this context, any nuances)\n\n"
            . "Respond ONLY in JSON (no markdown):\n"
            . "{\n"
            . "  \"translation\": \"natural English translation\",\n"
            . "  \"feedback\": \"brief 師父 commentary on the text\",\n"
            . "  \"word_notes\": [\n"
            . "    { \"word\": \"詞\", \"pinyin\": \"cí\", \"note\": \"brief note about this word in context\" }\n"
            . "  ]\n"
            . "}";

        // Inject persona overlay
        $personaSlug = Auth::user()?->shifu_persona ?? 'dragon';
        $persona = config("shifu-personas.{$personaSlug}");
        if ($persona) {
            $prompt .= "\n\nFEEDBACK STYLE PERSONA:\n" . $persona['prompt'];
        }

        $result = $this->callAnthropic($prompt, $text);

        if (isset($result['error'])) {
            return response()->json(['error' => 'AI request failed'], 502);
        }

        $raw = collect($result['content'] ?? [])
            ->map(fn ($b) => $b['text'] ?? '')
            ->join('');

        $clean = preg_replace('/```json|```/', '', $raw);
        $parsed = json_decode(trim($clean), true);

        if (! $parsed) {
            return response()->json(['error' => 'Unable to parse analysis.'], 502);
        }

        // Log usage
        AiUsageLog::create([
            'user_id'      => Auth::id(),
            'request_type' => 'analysis',
            'credits_used' => 1,
        ]);

        // Engagement tracking
        $engagement = ShifuEngagement::create([
            'user_id'    => Auth::id(),
            'context'    => 'analysis',
            'word_label' => mb_substr($text, 0, 32),
            'started_at' => now(),
            'completed_at' => now(),
            'outcome'    => 'saved',
        ]);

        $engagement->addInteraction($text, $raw);

        $parsed['engagement_id'] = $engagement->uuid;

        return response()->json($parsed);
    }

    /**
     * Call the Anthropic Messages API.
     */
    private function callAnthropic(string $systemPrompt, string $userMessage): array
    {
        $response = Http::withHeaders([
            'x-api-key'         => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model'      => config('services.anthropic.model', 'claude-sonnet-4-20250514'),
            'max_tokens' => 1000,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        return $response->json();
    }
}
