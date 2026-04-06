<?php

namespace App\Http\Controllers;

use App\Models\AiUsageLog;
use App\Models\CollectionTest;
use App\Models\ShifuDailyMessage;
use App\Models\UserSavedExample;
use App\Models\UserSavedWord;
use App\Models\UserWordProgress;
use App\Models\WordObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class LearnerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user  = Auth::user();
        $prefs = $user->ui_preferences ?? [];

        // Resolve user's "learned" criteria
        $learnedRequires = $prefs['learned_requires'] ?? ['pinyin', 'definition', 'usage'];

        // Configurable widget counts
        $learnedCount   = (int) ($prefs['dashboard_learned_count']   ?? 10);
        $attentionCount = (int) ($prefs['dashboard_attention_count'] ?? 10);
        $kungfuCount    = (int) ($prefs['dashboard_kungfu_count']    ?? 10);

        // ── Widget 1: Recent Learned ──────────────────────────────────
        $recentLearned = $this->recentLearned($user->id, $learnedCount, $learnedRequires);

        // ── Widget 2: Needs Attention ─────────────────────────────────
        $needsAttention = $this->needsAttention($user->id, $attentionCount, $learnedRequires);

        // ── Widget 3: Days Since Last Active ──────────────────────────
        $daysSince = $user->last_active_at
            ? (int) $user->last_active_at->diffInDays(now())
            : null;

        // ── Widget 4: Recent Tests ────────────────────────────────────
        $recentTests = CollectionTest::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->with('collection:id,name,name_zh')
            ->orderByDesc('completed_at')
            ->limit(5)
            ->get();

        // ── Widget 5: Today's Shifu Message ───────────────────────────
        $todayMessage = ShifuDailyMessage::where('user_id', $user->id)
            ->where('message_date', now()->toDateString())
            ->first();

        // ── Widget 6: 需功夫 Struggling Words ─────────────────────────
        $kungfuWords = $this->needsKungfu($user->id, $kungfuCount);

        // Persona info for emoji display
        $persona = config('shifu-personas.' . ($user->shifu_persona ?? 'dragon'));

        // Current counts for the selector UI
        $widgetCounts = [
            'learned'   => $learnedCount,
            'attention' => $attentionCount,
            'kungfu'    => $kungfuCount,
        ];

        return view('dashboard', compact(
            'user', 'recentLearned', 'needsAttention',
            'daysSince', 'recentTests', 'todayMessage',
            'kungfuWords', 'persona', 'widgetCounts'
        ));
    }

    // ── AJAX: Generate daily message ──────────────────────────────────

    public function generateDailyMessage(Request $request): JsonResponse
    {
        $user  = Auth::user();
        $today = now()->toDateString();
        $prefs = $user->ui_preferences ?? [];
        $learnedRequires = $prefs['learned_requires'] ?? ['pinyin', 'definition', 'usage'];

        // Idempotency: return existing message
        $existing = ShifuDailyMessage::where('user_id', $user->id)
            ->where('message_date', $today)
            ->first();

        if ($existing) {
            $persona = config('shifu-personas.' . $existing->persona_slug);
            return response()->json([
                'message_text' => $existing->message_text,
                'persona_slug' => $existing->persona_slug,
                'emoji'        => $persona['emoji'] ?? '🐉',
            ]);
        }

        // Build context
        $recentLearned  = $this->recentLearned($user->id, 5, $learnedRequires);
        $needsAttention = $this->needsAttention($user->id, 5, $learnedRequires);
        $kungfuWords    = $this->needsKungfu($user->id, 5);
        $daysSince      = $user->last_active_at
            ? (int) $user->last_active_at->diffInDays(now())
            : null;

        $recentTests = CollectionTest::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->with('collection:id,name,name_zh')
            ->orderByDesc('completed_at')
            ->limit(3)
            ->get();

        // Recent writings
        $recentWritings = UserSavedExample::where('user_id', $user->id)
            ->with('wordObject:id,traditional')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        $totalWritings = UserSavedExample::where('user_id', $user->id)->count();

        $context = [
            'learned_count'   => count($recentLearned),
            'learned_words'   => collect($recentLearned)->pluck('traditional')->take(5)->toArray(),
            'attention_count' => count($needsAttention),
            'kungfu_count'    => count($kungfuWords),
            'kungfu_words'    => collect($kungfuWords)->pluck('traditional')->take(5)->toArray(),
            'days_since'      => $daysSince,
            'recent_tests'    => $recentTests->map(fn ($t) => [
                'collection' => $t->collection?->name ?? 'Unknown',
                'mode'       => $t->test_mode,
                'clean'      => $t->clean_count,
                'assisted'   => $t->assisted_count,
                'learning'   => $t->learning_count,
                'total'      => $t->total_questions,
            ])->toArray(),
            'recent_writings' => $recentWritings->map(fn ($w) => [
                'word'           => $w->wordObject?->traditional ?? '?',
                'chinese_text'   => $w->chinese_text,
                'assessed_level' => $w->assessed_level,
                'assessed_mastery' => $w->assessed_mastery,
            ])->toArray(),
            'total_writings'  => $totalWritings,
        ];

        // Persona
        $personaSlug = $user->shifu_persona ?? 'dragon';
        $persona     = config('shifu-personas.' . $personaSlug);

        // Build prompts
        $systemPrompt = "You are 師父 (Shifu), a warm and wise Chinese language tutor on the 流動 Living Lexicon platform.\n\n"
            . ($persona['prompt'] ?? '')
            . "\n\nYou are writing a brief daily welcome message for your student. Keep it to 2-3 sentences maximum. "
            . "Be natural and encouraging. You may include one Chinese word or phrase naturally if it fits. "
            . "Do not use markdown formatting. Do not use bullet points or lists. Write a warm, personal greeting.";

        $learnedStr = count($context['learned_words'])
            ? implode('、', $context['learned_words'])
            : 'none recently';

        $kungfuStr = count($context['kungfu_words'])
            ? implode('、', $context['kungfu_words'])
            : 'none';

        $testStr = '';
        foreach ($context['recent_tests'] as $t) {
            $testStr .= "- {$t['collection']} ({$t['mode']}): {$t['clean']} clean / {$t['assisted']} assisted / {$t['learning']} learning out of {$t['total']}\n";
        }
        if (! $testStr) {
            $testStr = "No tests taken recently.\n";
        }

        $writingsStr = '';
        foreach ($context['recent_writings'] as $w) {
            $level = $w['assessed_level'] ? " (assessed: {$w['assessed_level']})" : '';
            $writingsStr .= "- {$w['word']}: \"{$w['chinese_text']}\"{$level}\n";
        }
        if (! $writingsStr) {
            $writingsStr = "No writings yet.\n";
        }

        $daysSinceStr = match (true) {
            $daysSince === null => 'This appears to be their first visit.',
            $daysSince === 0    => 'They were active today already.',
            $daysSince === 1    => 'They were here yesterday.',
            default             => "It has been {$daysSince} days since their last visit.",
        };

        $userMessage = "Today's date: " . now()->format('F j, Y') . "\n"
            . "Student name: " . ($user->chinese_name ?? $user->name) . "\n"
            . "Fluency level: " . ($user->fluency_level ?? 'not yet set') . "\n\n"
            . "Recent progress:\n"
            . "- Fully learned words recently: {$learnedStr}\n"
            . "- Words saved but still need practice: {$context['attention_count']}\n"
            . "- Words they're struggling with (需功夫): {$kungfuStr}\n"
            . "- {$daysSinceStr}\n"
            . "- Recent test scores:\n{$testStr}"
            . "- Recent writings ({$totalWritings} total):\n{$writingsStr}\n"
            . "Write a brief daily welcome message for this student.";

        // Call Anthropic
        try {
            $response = Http::withHeaders([
                'x-api-key'         => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model'      => config('services.anthropic.model', 'claude-sonnet-4-20250514'),
                'max_tokens' => 300,
                'system'     => $systemPrompt,
                'messages'   => [
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ]);

            $result = $response->json();
            $messageText = $result['content'][0]['text'] ?? '';

            if (! $messageText) {
                return response()->json(['error' => 'Empty response from Shifu.'], 502);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not reach 師父 right now.'], 502);
        }

        // Save (handle race condition with unique constraint)
        try {
            $message = ShifuDailyMessage::create([
                'user_id'          => $user->id,
                'message_date'     => $today,
                'persona_slug'     => $personaSlug,
                'message_text'     => $messageText,
                'context_snapshot' => $context,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            $message = ShifuDailyMessage::where('user_id', $user->id)
                ->where('message_date', $today)
                ->first();

            return response()->json([
                'message_text' => $message->message_text ?? $messageText,
                'persona_slug' => $message->persona_slug ?? $personaSlug,
                'emoji'        => $persona['emoji'] ?? '🐉',
            ]);
        }

        // Log AI usage
        AiUsageLog::create([
            'user_id'      => $user->id,
            'request_type' => 'daily_message',
            'credits_used' => 1,
        ]);

        return response()->json([
            'message_text' => $messageText,
            'persona_slug' => $personaSlug,
            'emoji'        => $persona['emoji'] ?? '🐉',
        ]);
    }

    // ── AJAX: Feedback on daily message ───────────────────────────────

    public function feedbackDailyMessage(Request $request): JsonResponse
    {
        $request->validate(['feedback' => 'required|in:up,down']);

        $message = ShifuDailyMessage::where('user_id', Auth::id())
            ->where('message_date', now()->toDateString())
            ->first();

        if (! $message) {
            return response()->json(['error' => 'No message found for today.'], 404);
        }

        $message->update([
            'feedback'    => $request->feedback,
            'feedback_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ── Private helpers ──────────────────────────────────────────────

    /**
     * Get the required test flags based on the user's learned_requires preference.
     */
    private function learnedConditions(array $requires): array
    {
        $map = [
            'pinyin'     => 'pinyin_passed',
            'definition' => 'definition_passed',
            'usage'      => 'usage_passed',
        ];

        return array_values(array_filter(
            array_map(fn ($r) => $map[$r] ?? null, $requires)
        ));
    }

    private function recentLearned(int $userId, int $limit, array $requires = ['pinyin', 'definition', 'usage']): array
    {
        $conditions = $this->learnedConditions($requires);
        if (empty($conditions)) return [];

        $query = UserWordProgress::where('user_id', $userId);
        foreach ($conditions as $col) {
            $query->where($col, true);
        }

        // Order by the latest passed timestamp among required tests
        $timestampCols = array_map(fn ($c) => str_replace('_passed', '_passed_at', $c), $conditions);
        if (count($timestampCols) === 1) {
            $query->orderByDesc($timestampCols[0]);
        } else {
            $query->orderByRaw('GREATEST(' . implode(', ', $timestampCols) . ') DESC');
        }

        $progress = $query->limit($limit)->get();
        if ($progress->isEmpty()) return [];

        $wordIds = $progress->pluck('word_object_id')->toArray();
        $words = WordObject::whereIn('id', $wordIds)
            ->with(['pronunciations' => fn ($q) => $q->where('is_primary', true)])
            ->get()->keyBy('id');

        return $progress->map(function ($p) use ($words, $timestampCols) {
            $word = $words->get($p->word_object_id);
            if (! $word) return null;

            $pron = $word->pronunciations->first();
            $learnedAt = collect($timestampCols)->map(fn ($c) => $p->{$c})->max();

            return [
                'traditional' => $word->traditional,
                'smart_id'    => $word->smart_id,
                'pinyin'      => $pron?->pronunciation_text ?? '',
                'learned_at'  => $learnedAt,
            ];
        })->filter()->values()->toArray();
    }

    private function needsAttention(int $userId, int $limit, array $requires = ['pinyin', 'definition', 'usage']): array
    {
        $conditions = $this->learnedConditions($requires);
        if (empty($conditions)) return [];

        // Saved words where NOT all required flags are true
        $notFullyLearned = DB::table('user_saved_words as sw')
            ->leftJoin('user_word_progress as wp', function ($join) use ($userId) {
                $join->on('sw.word_object_id', '=', 'wp.word_object_id')
                     ->where('wp.user_id', '=', $userId);
            })
            ->where('sw.user_id', $userId)
            ->where(function ($q) use ($conditions) {
                $q->whereNull('wp.user_id');
                foreach ($conditions as $col) {
                    $q->orWhere("wp.{$col}", false);
                }
            })
            ->select(
                'sw.word_object_id',
                'sw.saved_at',
                DB::raw('COALESCE(wp.pinyin_passed, false) as pinyin_passed'),
                DB::raw('COALESCE(wp.definition_passed, false) as def_passed'),
                DB::raw('COALESCE(wp.usage_passed, false) as usage_passed'),
                DB::raw('(CASE WHEN wp.pinyin_passed THEN 1 ELSE 0 END + CASE WHEN wp.definition_passed THEN 1 ELSE 0 END + CASE WHEN wp.usage_passed THEN 1 ELSE 0 END) as passed_count')
            )
            ->orderByDesc('passed_count')
            ->orderByDesc('sw.saved_at')
            ->limit($limit)
            ->get();

        if ($notFullyLearned->isEmpty()) return [];

        $wordIds = $notFullyLearned->pluck('word_object_id')->toArray();
        $words = WordObject::whereIn('id', $wordIds)
            ->with(['pronunciations' => fn ($q) => $q->where('is_primary', true)])
            ->get()->keyBy('id');

        return $notFullyLearned->map(function ($row) use ($words) {
            $word = $words->get($row->word_object_id);
            if (! $word) return null;

            $pron = $word->pronunciations->first();

            return [
                'traditional'   => $word->traditional,
                'smart_id'      => $word->smart_id,
                'pinyin'        => $pron?->pronunciation_text ?? '',
                'pinyin_passed' => (bool) $row->pinyin_passed,
                'def_passed'    => (bool) $row->def_passed,
                'usage_passed'  => (bool) $row->usage_passed,
                'passed_count'  => (int) $row->passed_count,
            ];
        })->filter()->values()->toArray();
    }

    /**
     * 需功夫 — words the learner has gotten wrong 2+ times in tests.
     */
    private function needsKungfu(int $userId, int $limit): array
    {
        $struggling = DB::table('collection_test_answers as a')
            ->join('collection_tests as t', 'a.collection_test_id', '=', 't.id')
            ->join('word_senses as ws', 'a.word_sense_id', '=', 'ws.id')
            ->where('t.user_id', $userId)
            ->where('a.score_tier', 'learning')
            ->groupBy('ws.word_object_id')
            ->havingRaw('COUNT(*) >= 2')
            ->select('ws.word_object_id', DB::raw('COUNT(*) as wrong_count'))
            ->orderByDesc('wrong_count')
            ->limit($limit)
            ->get();

        if ($struggling->isEmpty()) return [];

        $wordIds = $struggling->pluck('word_object_id')->toArray();
        $wrongCounts = $struggling->pluck('wrong_count', 'word_object_id')->toArray();

        $words = WordObject::whereIn('id', $wordIds)
            ->with(['pronunciations' => fn ($q) => $q->where('is_primary', true)])
            ->get()->keyBy('id');

        return $struggling->map(function ($row) use ($words, $wrongCounts) {
            $word = $words->get($row->word_object_id);
            if (! $word) return null;

            $pron = $word->pronunciations->first();

            return [
                'traditional'  => $word->traditional,
                'smart_id'     => $word->smart_id,
                'pinyin'       => $pron?->pronunciation_text ?? '',
                'wrong_count'  => (int) $row->wrong_count,
            ];
        })->filter()->values()->toArray();
    }
}
