<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\WordSense;
use App\Models\WordSenseExample;
use App\Models\WordSenseExampleTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WordSenseExampleController extends Controller
{
    public function store(Request $request, WordSense $sense): RedirectResponse
    {
        $data = $request->validate([
            'chinese_text'      => ['required', 'string'],
            'source'            => ['required', 'in:default,student,ai_generated,community'],
            'is_suppressed'     => ['boolean'],
            'theme'             => ['nullable', 'string', 'max:64'],
            'translations'      => ['nullable', 'array'],
            'translations.*'    => ['nullable', 'string'],
        ]);

        $data['word_sense_id'] = $sense->id;
        $data['is_suppressed'] = $request->boolean('is_suppressed');

        // Keep english_text in sync with EN translation for legacy compatibility
        $enId = Language::where('code', 'en')->value('id');
        $translations = $data['translations'] ?? [];
        $data['english_text'] = trim($translations[$enId] ?? '') ?: null;
        unset($data['translations']);

        $example = WordSenseExample::create($data);

        // Write translations
        $this->syncTranslations($example, $translations);

        return back()->with('success', 'Example added.');
    }

    public function update(Request $request, WordSenseExample $example): RedirectResponse
    {
        $data = $request->validate([
            'chinese_text'      => ['required', 'string'],
            'source'            => ['sometimes', 'in:default,student,ai_generated,community'],
            'is_suppressed'     => ['boolean'],
            'theme'             => ['nullable', 'string', 'max:64'],
            'translations'      => ['nullable', 'array'],
            'translations.*'    => ['nullable', 'string'],
        ]);

        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $data['is_suppressed'] = $request->boolean('is_suppressed');

        // Keep english_text in sync with EN translation for legacy compatibility
        $enId = Language::where('code', 'en')->value('id');
        $data['english_text'] = trim($translations[$enId] ?? '') ?: null;

        $example->update($data);

        // Write translations
        $this->syncTranslations($example, $translations);

        return back()->with('success', 'Example updated.');
    }

    public function destroy(WordSenseExample $example): RedirectResponse
    {
        $example->delete(); // translations cascade via FK

        return back()->with('success', 'Example removed.');
    }

    /**
     * Sync per-language translations for an example.
     */
    private function syncTranslations(WordSenseExample $example, array $translations): void
    {
        $now = now();

        foreach ($translations as $langId => $text) {
            $text = trim($text ?? '');

            if ($text !== '') {
                DB::table('word_sense_example_translations')->updateOrInsert(
                    ['word_sense_example_id' => $example->id, 'language_id' => $langId],
                    ['translation_text' => $text, 'updated_at' => $now, 'created_at' => $now]
                );
            } else {
                // Blank — remove the row
                DB::table('word_sense_example_translations')
                    ->where('word_sense_example_id', $example->id)
                    ->where('language_id', $langId)
                    ->delete();
            }
        }
    }
}
