<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PreferencesController extends Controller
{
    public function show(): View
    {
        $prefs = auth()->user()->ui_preferences ?? [];

        return view('admin.preferences', [
            'currentFont'         => auth()->user()->chinese_font ?? 'biaukai',
            'verbPresentation'    => $prefs['verb_presentation'] ?? 'intricate',
            'languages'           => Language::orderBy('id')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'chinese_font'      => ['sometimes', 'in:biaukai,noto-serif,noto-sans'],
            'verb_presentation' => ['sometimes', 'in:consolidated,intricate'],
            'notes_coverage'    => ['sometimes', 'array'],
        ]);

        $user = auth()->user();

        if ($request->has('chinese_font')) {
            $user->update(['chinese_font' => $request->chinese_font]);
        }

        if ($request->has('verb_presentation')) {
            $prefs = $user->ui_preferences ?? [];
            $prefs['verb_presentation'] = $request->verb_presentation;
            $user->update(['ui_preferences' => $prefs]);
        }

        if ($request->has('notes_coverage')) {
            $enabled = $request->input('notes_coverage', []);
            Language::query()->update(['has_notes_coverage' => false]);
            if (! empty($enabled)) {
                Language::whereIn('id', $enabled)->update(['has_notes_coverage' => true]);
            }
        }

        return back()->with('success', 'Preferences saved.');
    }
}
