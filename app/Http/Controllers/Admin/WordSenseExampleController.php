<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WordSense;
use App\Models\WordSenseExample;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WordSenseExampleController extends Controller
{
    public function store(Request $request, WordSense $sense): RedirectResponse
    {
        $data = $request->validate([
            'chinese_text'  => ['required', 'string'],
            'english_text'  => ['nullable', 'string'],
            'source'        => ['required', 'in:default,student,ai_generated,community'],
            'is_suppressed' => ['boolean'],
            'theme'         => ['nullable', 'string', 'max:64'],
        ]);

        $data['word_sense_id'] = $sense->id;
        $data['is_suppressed'] = $request->boolean('is_suppressed');

        WordSenseExample::create($data);

        return back()->with('success', 'Example added.');
    }

    public function update(Request $request, WordSenseExample $example): RedirectResponse
    {
        $data = $request->validate([
            'chinese_text'  => ['required', 'string'],
            'english_text'  => ['nullable', 'string'],
            'is_suppressed' => ['boolean'],
            'theme'         => ['nullable', 'string', 'max:64'],
        ]);

        $data['is_suppressed'] = $request->boolean('is_suppressed');

        $example->update($data);

        return back()->with('success', 'Example updated.');
    }

    public function destroy(WordSenseExample $example): RedirectResponse
    {
        $example->delete();

        return back()->with('success', 'Example removed.');
    }
}
