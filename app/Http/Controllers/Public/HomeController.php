<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $latestPhotos = Photo::query()
            ->where('is_published', true)
            ->with('category')
            ->latest()
            ->limit(8)
            ->get();

        return view('public.home', ['latestPhotos' => $latestPhotos]);
    }
}
