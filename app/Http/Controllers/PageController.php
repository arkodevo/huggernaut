<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function translation(): View
    {
        return view('translation');
    }

    public function idioms(): View
    {
        return view('idioms');
    }

    public function chineseNames(): View
    {
        return view('chinese-names');
    }

    public function help(): View
    {
        return view('help');
    }
}
