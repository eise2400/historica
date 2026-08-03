<?php

namespace App\Http\Controllers\Archive;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use App\Models\Person;
use App\Models\Photo;
use App\Models\PhotoPersonTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhotoController extends Controller
{
    public function index(Request $request): View
    {
        $query = Photo::query()->where('is_published', true)->with(['category', 'location']);

        if ($category = $request->string('kategorie')->toString()) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }
        if ($location = $request->string('ort')->toString()) {
            $query->whereHas('location', fn ($q) => $q->where('slug', $location));
        }
        if ($year = $request->string('jahr')->toString()) {
            $query->where(function ($q) use ($year) {
                $q->whereYear('date_from', $year)->orWhereYear('date_to', $year);
            });
        }
        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('persons', function ($personQuery) use ($search) {
                        $personQuery->where('last_name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%");
                    });
            });
        }

        $photos = $query->latest()->paginate(24)->withQueryString();

        return view('archive.index', [
            'photos' => $photos,
            'categories' => Category::orderBy('order')->get(),
            'locations' => Location::whereHas('photos', fn ($q) => $q->where('is_published', true))->orderBy('name')->get(),
            'selectedCategory' => $category,
            'selectedLocation' => $location,
            'selectedYear' => $year,
            'query' => $search,
        ]);
    }

    public function show(Photo $photo): View
    {
        abort_unless($photo->is_published, 404);

        $tags = $photo->personTags()->approved()->with('person')->get();

        return view('archive.show', [
            'photo' => $photo->load('category', 'location'),
            'tags' => $tags,
            'people' => auth()->check() ? Person::orderBy('last_name')->orderBy('first_name')->get() : collect(),
        ]);
    }

    public function person(Person $person): View
    {
        $photos = Photo::query()
            ->where('is_published', true)
            ->whereHas('personTags', fn ($q) => $q->where('person_id', $person->id)->approved())
            ->get();

        return view('archive.person', ['person' => $person, 'photos' => $photos]);
    }

    public function suggestTag(Request $request, Photo $photo): RedirectResponse
    {
        abort_unless($photo->is_published, 404);

        $data = $request->validate([
            'person_id' => ['nullable', 'exists:people,id'],
            'new_first_name' => ['nullable', 'string', 'max:100'],
            'new_last_name' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        $personId = $data['person_id'] ?? null;
        if (! $personId) {
            if (blank($data['new_last_name'] ?? null)) {
                return back()->withErrors([
                    'new_last_name' => 'Bitte eine bestehende Person auswählen oder mindestens einen Nachnamen angeben.',
                ]);
            }
            $person = Person::create([
                'first_name' => $data['new_first_name'] ?? '',
                'last_name' => $data['new_last_name'],
            ]);
            $personId = $person->id;
        }

        PhotoPersonTag::firstOrCreate(
            ['photo_id' => $photo->id, 'person_id' => $personId],
            [
                'note' => $data['note'] ?? null,
                'status' => PhotoPersonTag::STATUS_PENDING,
                'suggested_by' => $request->user()->id,
            ]
        );

        return redirect($photo->url)->with('success', 'Vielen Dank! Ihr Vorschlag wird nach Prüfung durch den Webmaster freigeschaltet.');
    }
}
