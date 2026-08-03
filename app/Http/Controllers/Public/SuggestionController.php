<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuggestionController extends Controller
{
    public function index(Request $request): View
    {
        $suggestions = $request->user()
            ->suggestedTags()
            ->with(['photo', 'person'])
            ->latest()
            ->get();

        return view('public.suggestions', ['suggestions' => $suggestions]);
    }
}
