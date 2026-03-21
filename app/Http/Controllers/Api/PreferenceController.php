<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PreferenceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Auth::user()->ui_preferences ?? []);
    }

    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();
        $current = $user->ui_preferences ?? [];
        $merged = array_merge($current, $request->all());

        $user->update(['ui_preferences' => $merged]);

        return response()->json($merged);
    }
}
