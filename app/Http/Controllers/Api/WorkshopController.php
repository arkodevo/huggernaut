<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
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
            'system_prompt' => ['required', 'string', 'max:8000'],
            'sentence'      => ['required', 'string', 'max:2000'],
        ]);

        $result = $this->callAnthropic(
            $request->input('system_prompt'),
            $request->input('sentence'),
        );

        if (isset($result['error'])) {
            return response()->json(['error' => 'AI request failed'], 502);
        }

        // Log usage
        AiUsageLog::create([
            'user_id'      => Auth::id(),
            'word_sense_id' => $request->input('word_sense_id'),
            'request_type' => 'feedback',
            'credits_used' => 1,
        ]);

        $text = collect($result['content'] ?? [])
            ->map(fn ($b) => $b['text'] ?? '')
            ->join('');

        return response()->json(['text' => $text]);
    }

    /**
     * Proxy a theme generation request to the Anthropic API.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'system_prompt' => ['required', 'string', 'max:8000'],
            'theme'         => ['required', 'string', 'max:500'],
        ]);

        $result = $this->callAnthropic(
            $request->input('system_prompt'),
            'Theme/subject: ' . $request->input('theme'),
        );

        if (isset($result['error'])) {
            return response()->json(['error' => 'AI request failed'], 502);
        }

        AiUsageLog::create([
            'user_id'      => Auth::id(),
            'word_sense_id' => $request->input('word_sense_id'),
            'request_type' => 'generation',
            'credits_used' => 1,
        ]);

        $text = collect($result['content'] ?? [])
            ->map(fn ($b) => $b['text'] ?? '')
            ->join('');

        return response()->json(['text' => $text]);
    }

    /**
     * Save a user-authored (optionally AI-verified) example sentence.
     */
    public function saveExample(Request $request): JsonResponse
    {
        $request->validate([
            'word_sense_id' => ['required', 'integer', 'exists:word_senses,id'],
            'chinese_text'  => ['required', 'string', 'max:2000'],
            'english_text'  => ['required', 'string', 'max:2000'],
            'ai_verified'   => ['boolean'],
            'ai_feedback'   => ['nullable', 'string', 'max:5000'],
            'source_type'   => ['nullable', 'string', 'in:learner,generated'],
        ]);

        $example = UserSavedExample::create([
            'user_id'       => Auth::id(),
            'word_sense_id' => $request->input('word_sense_id'),
            'chinese_text'  => $request->input('chinese_text'),
            'english_text'  => $request->input('english_text'),
            'ai_verified'   => $request->boolean('ai_verified', false),
            'ai_feedback'   => $request->input('ai_feedback'),
            'source_type'   => $request->input('source_type', 'learner'),
            'is_public'     => false,
        ]);

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
