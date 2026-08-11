<?php

namespace App\Http\Controllers\Archive;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use App\Models\Person;
use App\Models\Photo;
use App\Models\PhotoPersonTag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhotoController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filtersFromRequest($request);
        $query = $this->applyFilters(Photo::query()->where('is_published', true)->with(['category', 'location']), $filters);

        $photos = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(24)->withQueryString();

        return view('archive.index', [
            'photos' => $photos,
            'categories' => Category::orderBy('order')->get(),
            'locations' => Location::whereHas('photos', fn ($q) => $q->where('is_published', true))->orderBy('name')->get(),
            'selectedCategory' => $filters['kategorie'],
            'selectedLocation' => $filters['ort'],
            'selectedYear' => $filters['jahr'],
            'query' => $filters['q'],
            'filters' => array_filter($filters),
        ]);
    }

    public function show(Request $request, Photo $photo): View
    {
        abort_unless($photo->is_published, 404);

        $filters = $this->filtersFromRequest($request);
        $tags = $photo->personTags()->approved()->with('person')->get();

        return view('archive.show', [
            'photo' => $photo->load('category', 'location'),
            'tags' => $tags,
            'people' => auth()->check() ? Person::orderBy('last_name')->orderBy('first_name')->get() : collect(),
            'filters' => array_filter($filters),
            'previousPhoto' => $this->adjacentPhoto($photo, $filters, 'previous'),
            'nextPhoto' => $this->adjacentPhoto($photo, $filters, 'next'),
        ]);
    }

    /**
     * @return array{kategorie: string, ort: string, jahr: string, q: string}
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'kategorie' => $request->string('kategorie')->toString(),
            'ort' => $request->string('ort')->toString(),
            'jahr' => $request->string('jahr')->toString(),
            'q' => $request->string('q')->toString(),
        ];
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        if ($filters['kategorie']) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['kategorie']));
        }
        if ($filters['ort']) {
            $query->whereHas('location', fn ($q) => $q->where('slug', $filters['ort']));
        }
        if ($filters['jahr']) {
            $query->where(function ($q) use ($filters) {
                $q->whereYear('date_from', $filters['jahr'])->orWhereYear('date_to', $filters['jahr']);
            });
        }
        if ($filters['q']) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['q']}%")
                    ->orWhere('description', 'like', "%{$filters['q']}%")
                    ->orWhereHas('persons', function ($personQuery) use ($filters) {
                        $personQuery->where('last_name', 'like', "%{$filters['q']}%")
                            ->orWhere('first_name', 'like', "%{$filters['q']}%");
                    });
            });
        }

        return $query;
    }

    /**
     * Finds the photo before/after $photo in the same order and (optional)
     * filtering as the archive listing (orderByDesc('created_at', 'id')),
     * so "vor/zurück blättern" stays within whatever filter/search the
     * visitor arrived through.
     */
    private function adjacentPhoto(Photo $photo, array $filters, string $direction): ?Photo
    {
        $query = $this->applyFilters(Photo::query()->where('is_published', true), $filters);

        if ($direction === 'next') {
            $query->where(function ($q) use ($photo) {
                $q->where('created_at', '<', $photo->created_at)
                    ->orWhere(function ($q2) use ($photo) {
                        $q2->where('created_at', $photo->created_at)->where('id', '<', $photo->id);
                    });
            })->orderByDesc('created_at')->orderByDesc('id');
        } else {
            $query->where(function ($q) use ($photo) {
                $q->where('created_at', '>', $photo->created_at)
                    ->orWhere(function ($q2) use ($photo) {
                        $q2->where('created_at', $photo->created_at)->where('id', '>', $photo->id);
                    });
            })->orderBy('created_at')->orderBy('id');
        }

        return $query->first();
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
