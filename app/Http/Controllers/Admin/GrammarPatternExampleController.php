<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrammarPattern;
use App\Models\GrammarPatternExample;
use App\Models\GrammarPatternExampleTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GrammarPatternExampleController extends Controller
{
    public function store(Request $request, GrammarPattern $pattern): RedirectResponse
    {
        $data = $request->validate([
            'chinese_text'       => ['required', 'string'],
            'pinyin_text'        => ['nullable', 'string'],
            'source'             => ['nullable', 'in:default,ai_generated,shifu,community'],
            'translations'       => ['nullable', 'array'],
            'translations.*'     => ['nullable', 'string'],
        ]);

        $example = $pattern->examples()->create([
            'chinese_text' => $data['chinese_text'],
            'pinyin_text'  => $data['pinyin_text'] ?? null,
            'source'       => $data['source'] ?? 'default',
            'sort_order'   => $pattern->examples()->count(),
        ]);

        $this->syncTranslations($example, $data['translations'] ?? []);

        return back()->with('success', 'Example added.');
    }

    public function update(Request $request, GrammarPatternExample $example): RedirectResponse
    {
        $data = $request->validate([
            'chinese_text'       => ['required', 'string'],
            'pinyin_text'        => ['nullable', 'string'],
            'source'             => ['nullable', 'in:default,ai_generated,shifu,community'],
            'is_suppressed'      => ['nullable'],
            'translations'       => ['nullable', 'array'],
            'translations.*'     => ['nullable', 'string'],
        ]);

        $example->update([
            'chinese_text'  => $data['chinese_text'],
            'pinyin_text'   => $data['pinyin_text'] ?? null,
            'source'        => $data['source'] ?? 'default',
            'is_suppressed' => $request->has('is_suppressed'),
        ]);

        $this->syncTranslations($example, $data['translations'] ?? []);

        return back()->with('success', 'Example updated.');
    }

    public function destroy(GrammarPatternExample $example): RedirectResponse
    {
        $example->delete();

        return back()->with('success', 'Example deleted.');
    }

    private function syncTranslations(GrammarPatternExample $example, array $translations): void
    {
        foreach ($translations as $langId => $text) {
            if (! empty($text)) {
                GrammarPatternExampleTranslation::updateOrCreate(
                    ['grammar_pattern_example_id' => $example->id, 'language_id' => $langId],
                    ['translation_text' => $text]
                );
            } else {
                GrammarPatternExampleTranslation::where('grammar_pattern_example_id', $example->id)
                    ->where('language_id', $langId)
                    ->delete();
            }
        }
    }
}
