<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'chinese_font'      => ['sometimes', 'in:biaukai,noto-serif,noto-sans'],
            'verb_presentation' => ['sometimes', 'in:consolidated,intricate'],
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

        return back()->with('success', 'Preferences saved.');
    }
}
