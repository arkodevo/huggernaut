<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WordObject;
use App\Models\WordSense;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'words_total'     => WordObject::count(),
            'words_published' => WordObject::where('status', 'published')->count(),
            'words_draft'     => WordObject::where('status', 'draft')->count(),
            'words_review'    => WordObject::where('status', 'review')->count(),
            'senses_total'    => WordSense::count(),
            'senses_published'=> WordSense::where('status', 'published')->count(),
        ];

        $recent = WordObject::with('senses')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent'));
    }
}
