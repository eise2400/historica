<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = SitePage::where('slug', $slug)->firstOrFail();

        return view('public.page', ['page' => $page]);
    }
}
