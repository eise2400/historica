<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MembershipApplication;
use App\Models\SitePage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function show(): View
    {
        return view('public.aufnahmeantrag', [
            'page' => SitePage::where('slug', 'aufnahmeantrag')->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'street' => ['required', 'string', 'max:200'],
            'postal_code' => ['required', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'birth_date' => ['nullable', 'date'],
            'message' => ['nullable', 'string'],
        ]);

        MembershipApplication::create($data);

        return redirect()->route('aufnahmeantrag')->with('success', 'Vielen Dank für Ihren Aufnahmeantrag! Wir setzen uns in Kürze mit Ihnen in Verbindung.');
    }
}
