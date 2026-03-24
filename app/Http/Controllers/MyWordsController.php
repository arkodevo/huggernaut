<?php

namespace App\Http\Controllers;

use App\Models\WordObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyWordsController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        // ── All saved words (word_objects) with their senses ─────────────
        $savedWordIds = $user->savedWords()->pluck('word_object_id');

        $savedWords = WordObject::whereIn('id', $savedWordIds)
            ->with([
                'senses' => fn ($q) => $q->orderBy('id')->with([
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
            ->map(fn ($wo) => $this->shapeSavedWord($wo, $user))
            ->filter()
            ->values();

        // ── Collections with their words ─────────────────────────────────
        $collections = $user->collections()
            ->with([
                'wordObjects' => fn ($q) => $q->with([
                    'senses' => fn ($q) => $q->orderBy('id')->with([
                        'pronunciation',
                        'definitions' => fn ($q) => $q->where('language_id', 1)
                            ->orderBy('sort_order')
                            ->with('posLabel'),
                        'domains' => fn ($q) => $q->with([
                            'labels' => fn ($q) => $q->whereIn('language_id', [1, 2]),
                        ]),
                        'tocflLevel',
                    ]),
                ]),
            ])
            ->get()
            ->map(fn ($c) => [
                'id'     => $c->id,
                'name'   => $c->name,
                'nameZh' => $c->name_zh,
                'words'  => $c->wordObjects->map(fn ($wo) => $this->shapeWord($wo))->filter()->values(),
            ]);

        return view('my-words', [
            'savedWords'  => $savedWords,
            'collections' => $collections,
            'authUser'    => (new ExploreController())->authUserPayload(),
        ]);
    }

    private function shapeSavedWord(WordObject $wo, $user): ?array
    {
        $shaped = $this->shapeWord($wo);
        if (!$shaped) return null;

        $savedRecord = $user->savedWords()
            ->where('word_object_id', $wo->id)
            ->first();

        $shaped['note'] = $savedRecord?->personal_note ?? '';
        $shaped['savedAt'] = $savedRecord?->saved_at?->toIso8601String();

        return $shaped;
    }

    private function shapeWord(WordObject $wo): ?array
    {
        $senses = $wo->senses;
        if ($senses->isEmpty()) return null;

        $primary = $senses->first();
        $def = $primary->definitions->first();
        $primaryDomain = $primary->domains->firstWhere('pivot.is_primary', true);

        $domainLabel = null;
        $domainLabelZh = null;
        if ($primaryDomain) {
            $domainLabel = $primaryDomain->labels->firstWhere('language_id', 1)?->label;
            $domainLabelZh = $primaryDomain->labels->firstWhere('language_id', 2)?->label;
        }

        return [
            'wordObjectId' => $wo->id,
            'smartId'       => $wo->smart_id,
            'traditional'   => $wo->traditional,
            'simplified'    => $wo->simplified ?? $wo->traditional,
            'pinyin'        => $primary->pronunciation?->pronunciation_text ?? '',
            'definition'    => $def?->definition_text ?? '',
            'pos'           => ExploreController::POS_FULL_NAMES[$def?->posLabel?->slug ?? ''] ?? '',
            'domain'        => $domainLabel,
            'domainZh'      => $domainLabelZh,
            'tocfl'         => ExploreController::TOCFL_SLUG_MAP[$primary->tocflLevel?->slug ?? ''] ?? null,
            'senseCount'    => $senses->count(),
        ];
    }
}
