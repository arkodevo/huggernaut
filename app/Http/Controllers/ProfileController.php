<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        return view('profile', ['user' => Auth::user()]);
    }

    public function updatePllName(Request $request): JsonResponse
    {
        $request->validate(['pll_name' => 'required|string|max:255']);

        $user = Auth::user();
        $user->update(['pll_name' => $request->input('pll_name')]);

        return response()->json(['ok' => true, 'pll_name' => $user->pll_name]);
    }

    public function updateChineseName(Request $request): JsonResponse
    {
        $request->validate([
            'chinese_name'        => 'required|string|max:32',
            'chinese_name_pinyin' => 'nullable|string|max:64',
            'chinese_name_meaning' => 'nullable|string|max:2000',
        ]);

        $user = Auth::user();
        $user->update([
            'chinese_name'         => $request->input('chinese_name'),
            'chinese_name_pinyin'  => $request->input('chinese_name_pinyin'),
            'chinese_name_meaning' => $request->input('chinese_name_meaning'),
        ]);

        return response()->json([
            'ok'            => true,
            'chinese_name'  => $user->chinese_name,
            'pinyin'        => $user->chinese_name_pinyin,
            'meaning'       => $user->chinese_name_meaning,
        ]);
    }
}
