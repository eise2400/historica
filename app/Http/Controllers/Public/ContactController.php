<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('public.kontakt');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email'],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string'],
        ]);

        $contactMessage = ContactMessage::create($data);

        if (config('mail.contact_recipient')) {
            Mail::raw(
                "Von: {$contactMessage->name} <{$contactMessage->email}>\n\n{$contactMessage->message}",
                function ($mail) use ($contactMessage) {
                    $mail->to(config('mail.contact_recipient'))
                        ->subject('Kontaktanfrage: '.($contactMessage->subject ?: 'Ohne Betreff'));
                }
            );
        }

        return redirect()->route('kontakt')->with('success', 'Vielen Dank für Ihre Nachricht! Wir melden uns so bald wie möglich bei Ihnen.');
    }
}
