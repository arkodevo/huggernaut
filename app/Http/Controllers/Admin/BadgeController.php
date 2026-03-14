<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function index(): View
    {
        $badges = Badge::ordered()->get();

        return view('admin.badges.index', compact('badges'));
    }

    public function create(): View
    {
        return view('admin.badges.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Badge::create($data);

        return redirect()->route('admin.badges.index')
                         ->with('success', 'Badge created.');
    }

    public function edit(Badge $badge): View
    {
        return view('admin.badges.edit', compact('badge'));
    }

    public function update(Request $request, Badge $badge): RedirectResponse
    {
        $data = $this->validated($request);

        $badge->update($data);

        return redirect()->route('admin.badges.index')
                         ->with('success', 'Badge updated.');
    }

    public function toggleActive(Badge $badge): RedirectResponse
    {
        $badge->update(['is_active' => ! $badge->is_active]);

        $label = $badge->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Badge \"{$badge->name}\" {$label}.");
    }

    // ── Shared validation ─────────────────────────────────────────────────────

    private function validated(Request $request): array
    {
        return $request->validate([
            'slug'          => ['required', 'string', 'max:60', 'regex:/^[a-z0-9\-]+$/'],
            'name'          => ['required', 'string', 'max:100'],
            'description'   => ['required', 'string'],
            'icon'          => ['required', 'string', 'max:50'],
            'trigger_type'  => ['required', 'in:points_total,action_count,streak,manual'],
            'threshold'     => ['required', 'integer', 'min:0'],
            'action_type'   => ['nullable', 'string', 'max:50'],
            'bonus_credits' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active'     => ['boolean'],
            'sort_order'    => ['required', 'integer', 'min:0', 'max:65535'],
        ]);
    }
}
