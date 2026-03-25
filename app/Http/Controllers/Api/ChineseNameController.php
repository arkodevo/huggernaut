<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use App\Models\ShifuEngagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ChineseNameController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'pll_name' => 'required|string|max:255',
            'guidance' => 'required|string|max:500',
        ]);

        $pllName  = $request->input('pll_name');
        $guidance = $request->input('guidance');

        $prompt = "You are 師父 (Shifu), the expert Chinese language and culture tutor for the Living Lexicon 流動. "
            . "You are warm, knowledgeable, and deeply respectful of Chinese naming traditions.\n\n"
            . "A learner wants a Chinese name. Their name in their primary language is: \"{$pllName}\"\n"
            . "They describe the feeling/character they want: \"{$guidance}\"\n\n"
            . "Your task:\n"
            . "1. Generate 3-4 Chinese name options\n"
            . "2. Try to phonetically echo the sounds of their PLL name where natural — don't force it if it sounds awkward\n"
            . "3. Choose characters whose meanings align with their guidance\n"
            . "4. For each option, explain: why these characters, what they mean individually and together, and how they connect to the learner's guidance\n"
            . "5. Note the pinyin for each name\n\n"
            . "IMPORTANT:\n"
            . "- Names should be 2-3 characters (surname + given name)\n"
            . "- Choose real, culturally appropriate Chinese surnames\n"
            . "- The given name should feel natural to a native speaker\n"
            . "- Be warm and personal in your explanations\n\n"
            . "Respond ONLY in JSON (no markdown):\n"
            . "{ \"names\": [\n"
            . "  { \"chinese\": \"楚洛一\", \"pinyin\": \"Chǔ Luòyī\", \"meaning\": \"explanation...\" }\n"
            . "] }";

        try {
            $response = Http::withHeaders([
                'x-api-key'         => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model'       => config('services.anthropic.model', 'claude-sonnet-4-20250514'),
                'max_tokens'  => 1500,
                'temperature' => 0.7,
                'system'      => $prompt,
                'messages'    => [['role' => 'user', 'content' => "My name is {$pllName}. {$guidance}"]],
            ]);

            $body = $response->json();
            $text = $body['content'][0]['text'] ?? '{}';
            $clean = preg_replace('/```json|```/', '', $text);
            $result = json_decode(trim($clean), true);

            if (! $result || ! isset($result['names'])) {
                return response()->json(['error' => 'Unable to parse name suggestions.'], 502);
            }

            // Log usage
            AiUsageLog::create([
                'user_id'      => Auth::id(),
                'request_type' => 'chinese_name',
                'credits_used' => 1,
            ]);

            // Engagement tracking
            $engagement = ShifuEngagement::create([
                'user_id'    => Auth::id(),
                'context'    => 'chinese_names',
                'word_label' => mb_substr($pllName, 0, 32),
                'started_at' => now(),
            ]);

            $engagement->addInteraction(
                "Name: {$pllName}. Guidance: {$guidance}",
                $text,
            );

            return response()->json([
                'names'         => $result['names'],
                'engagement_id' => $engagement->uuid,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate names.'], 502);
        }
    }

    public function choose(Request $request): JsonResponse
    {
        $request->validate([
            'chinese_name'  => 'required|string|max:32',
            'pinyin'        => 'nullable|string|max:64',
            'meaning'       => 'nullable|string|max:2000',
            'engagement_id' => 'nullable|string|max:36',
        ]);

        $user = Auth::user();
        $user->update([
            'chinese_name'         => $request->input('chinese_name'),
            'chinese_name_pinyin'  => $request->input('pinyin'),
            'chinese_name_meaning' => $request->input('meaning'),
        ]);

        // Also save PLL name if not set
        if (! $user->pll_name && $request->filled('pll_name')) {
            $user->update(['pll_name' => $request->input('pll_name')]);
        }

        // Complete engagement
        $engagementUuid = $request->input('engagement_id');
        if ($engagementUuid) {
            $engagement = ShifuEngagement::where('uuid', $engagementUuid)->first();
            if ($engagement) {
                $engagement->complete('saved');
            }
        }

        return response()->json([
            'ok'           => true,
            'chinese_name' => $user->chinese_name,
            'pinyin'       => $user->chinese_name_pinyin,
            'meaning'      => $user->chinese_name_meaning,
        ]);
    }
}
