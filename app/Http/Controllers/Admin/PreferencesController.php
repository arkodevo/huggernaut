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
        return view('admin.preferences', [
            'currentFont' => auth()->user()->chinese_font ?? 'biaukai',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'chinese_font' => ['required', 'in:biaukai,noto-serif,noto-sans'],
        ]);

        auth()->user()->update([
            'chinese_font' => $request->chinese_font,
        ]);

        return back()->with('success', 'Preferences saved.');
    }
}
