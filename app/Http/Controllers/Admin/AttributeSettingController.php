<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttributeSettingController extends Controller
{
    public function index(): View
    {
        $categories = Category::with([
            'attributes' => fn ($q) => $q
                ->with(['labels' => fn ($q) => $q->where('language_id', 1)])
                ->orderBy('sort_order'),
        ])->orderBy('sort_order')->get();

        return view('admin.attribute-settings.index', compact('categories'));
    }

    public function update(Request $request, Attribute $attribute): RedirectResponse
    {
        $request->validate([
            'learner_min_band' => ['required', 'integer', 'min:0', 'max:6'],
        ]);

        $attribute->update(['learner_min_band' => $request->learner_min_band]);

        return back()->with('success', "Updated '{$attribute->slug}' visibility threshold.");
    }
}
