<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyWordsController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        // ── All saved senses ───────────────────────────────────────────────
        $savedSenses = $user->savedSenses()
            ->with([
                'wordSense' => fn ($q) => $q->with([
                    'wordObject',
                    'pronunciation',
                    'definitions' => fn ($q) => $q->where('language_id', 1)
                        ->orderBy('sort_order')
                        ->with('posLabel'),
                    'domains' => fn ($q) => $q->with([
                        'labels' => fn ($q) => $q->whereIn('language_id', [1, 2]),
                    ]),
                    'tocflLevel',
                ]),
            ])
            ->get()
            ->filter(fn ($s) => $s->wordSense !== null)
            ->map(fn ($s) => $this->shapeSavedSense($s))
            ->values();

        // ── Collections with their senses ──────────────────────────────────
        $collections = $user->collections()
            ->with([
                'wordSenses' => fn ($q) => $q->with([
                    'wordObject',
                    'pronunciation',
                    'definitions' => fn ($q) => $q->where('language_id', 1)
                        ->orderBy('sort_order')
                        ->with('posLabel'),
                    'domains' => fn ($q) => $q->with([
                        'labels' => fn ($q) => $q->whereIn('language_id', [1, 2]),
                    ]),
                    'tocflLevel',
                ]),
            ])
            ->get()
            ->map(fn ($c) => [
                'id'     => $c->id,
                'name'   => $c->name,
                'nameZh' => $c->name_zh,
                'senses' => $c->wordSenses->map(fn ($ws) => $this->shapeWordSense($ws))->values(),
            ]);

        return view('my-words', [
            'savedSenses' => $savedSenses,
            'collections' => $collections,
            'authUser'    => (new ExploreController())->authUserPayload(),
        ]);
    }

    private function shapeSavedSense($savedSense): array
    {
        $ws = $savedSense->wordSense;
        $shaped = $this->shapeWordSense($ws);
        $shaped['note'] = $savedSense->personal_note ?? '';
        $shaped['savedAt'] = $savedSense->saved_at?->toIso8601String();

        return $shaped;
    }

    private function shapeWordSense($ws): array
    {
        $wo = $ws->wordObject;
        $def = $ws->definitions->first();
        $primaryDomain = $ws->domains->firstWhere('pivot.is_primary', true);
        $domainLabel = null;
        $domainLabelZh = null;
        if ($primaryDomain) {
            $domainLabel = $primaryDomain->labels->firstWhere('language_id', 1)?->label;
            $domainLabelZh = $primaryDomain->labels->firstWhere('language_id', 2)?->label;
        }

        return [
            'senseId'    => $ws->id,
            'smartId'    => $wo->smart_id,
            'traditional' => $wo->traditional,
            'simplified' => $wo->simplified ?? $wo->traditional,
            'pinyin'     => $ws->pronunciation?->pronunciation_text ?? '',
            'definition' => $def?->definition_text ?? '',
            'pos'        => ExploreController::POS_FULL_NAMES[$def?->posLabel?->slug ?? ''] ?? '',
            'domain'     => $domainLabel,
            'domainZh'   => $domainLabelZh,
            'tocfl'      => ExploreController::TOCFL_SLUG_MAP[$ws->tocflLevel?->slug ?? ''] ?? null,
        ];
    }
}
