<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\Photo;
use App\Models\PhotoPersonTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PhotoTagController extends Controller
{
    public function store(Request $request, Photo $photo): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'person_id' => ['nullable', 'exists:people,id'],
            'new_first_name' => ['nullable', 'string', 'max:100'],
            'new_last_name' => ['nullable', 'string', 'max:100'],
            'x_percent' => ['nullable', 'numeric', 'between:0,100'],
            'y_percent' => ['nullable', 'numeric', 'between:0,100'],
            'note' => ['nullable', 'string', 'max:200'],
            'status' => ['required', 'in:approved,pending,rejected'],
        ]);

        $personId = $data['person_id'] ?? null;
        if (! $personId) {
            if (blank($data['new_last_name'] ?? null)) {
                return back()->withErrors(['new_last_name' => 'Bitte eine bestehende Person auswählen oder einen Nachnamen angeben.']);
            }
            $person = Person::create([
                'first_name' => $data['new_first_name'] ?? '',
                'last_name' => $data['new_last_name'],
            ]);
            $personId = $person->id;
        }

        PhotoPersonTag::updateOrCreate(
            ['photo_id' => $photo->id, 'person_id' => $personId],
            [
                'x_percent' => $data['x_percent'] ?? null,
                'y_percent' => $data['y_percent'] ?? null,
                'note' => $data['note'] ?? null,
                'status' => $data['status'],
                'reviewed_by' => $request->user()->id,
            ]
        );

        return $this->redirectToTags($photo, $request)->with('success', 'Person wurde markiert.');
    }

    public function update(Request $request, Photo $photo, PhotoPersonTag $tag): RedirectResponse
    {
        $this->authorizeAdmin($request);
        abort_unless($tag->photo_id === $photo->id, 404);

        $data = $request->validate([
            'x_percent' => ['nullable', 'numeric', 'between:0,100'],
            'y_percent' => ['nullable', 'numeric', 'between:0,100'],
            'note' => ['nullable', 'string', 'max:200'],
            'status' => ['required', 'in:approved,pending,rejected'],
        ]);

        $tag->update([
            ...$data,
            'reviewed_by' => $request->user()->id,
        ]);

        return $this->redirectToTags($photo, $request)->with('success', 'Markierung wurde aktualisiert.');
    }

    public function destroy(Request $request, Photo $photo, PhotoPersonTag $tag): RedirectResponse
    {
        $this->authorizeAdmin($request);
        abort_unless($tag->photo_id === $photo->id, 404);

        $tag->delete();

        return $this->redirectToTags($photo, $request)->with('success', 'Markierung wurde entfernt.');
    }

    /**
     * The click-to-mark popup submits with a hidden `markmode=1` field so
     * that, after the redirect, the JS on the tags page can re-activate
     * "Markiermodus" automatically - letting the admin tag several people
     * in a row without re-enabling it after every single submit.
     */
    private function redirectToTags(Photo $photo, Request $request): RedirectResponse
    {
        return redirect()->route('filament.admin.resources.photos.tags', array_filter([
            'record' => $photo,
            'markmode' => $request->boolean('markmode') ? 1 : null,
        ]));
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->is_admin, 403);
    }
}
