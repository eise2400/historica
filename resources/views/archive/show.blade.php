<x-app-layout :title="$photo->title">
    <div class="grid lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            <div class="relative inline-block max-w-full">
                <img src="{{ $photo->image_url }}" alt="{{ $photo->title }}" class="max-w-full h-auto rounded-md">
                @foreach ($tags as $tag)
                    @if ($tag->x_percent !== null && $tag->y_percent !== null)
                        <span
                            class="absolute w-4 h-4 -ml-2 -mt-2 rounded-full bg-accent/60 hover:bg-accent border-2 border-white cursor-pointer transition"
                            style="left: {{ $tag->x_percent }}%; top: {{ $tag->y_percent }}%;"
                            title="{{ $tag->person->full_name }}{{ $tag->note ? ' – '.$tag->note : '' }}"
                        ></span>
                    @endif
                @endforeach
            </div>

            <h1 class="text-xl font-semibold mt-4">{{ $photo->title }}</h1>
            <p class="text-gray-500 mb-2">
                {{ $photo->date_display }}
                @if ($photo->location)
                    &middot; <a href="{{ route('archive.index', ['ort' => $photo->location->slug]) }}" class="hover:text-accent">{{ $photo->location->name }}</a>
                @endif
                &middot; <span class="inline-block text-xs bg-brand-light text-brand-dark px-2 py-0.5 rounded align-middle">{{ $photo->category->name }}</span>
            </p>
            @if ($photo->description)
                <p class="text-gray-700 whitespace-pre-line">{{ $photo->description }}</p>
            @endif
            @if ($photo->source)
                <p class="text-xs text-gray-500 mt-2">Quelle: {{ $photo->source }}</p>
            @endif
        </div>

        <div class="lg:col-span-4">
            <h2 class="font-semibold mb-2">Abgebildete Personen</h2>
            @if ($tags->isNotEmpty())
                <ul class="space-y-1 mb-6">
                    @foreach ($tags as $tag)
                        <li>
                            <a href="{{ route('archive.person', $tag->person) }}" class="text-accent hover:text-accent-dark">{{ $tag->person->full_name }}</a>
                            @if ($tag->note)
                                <span class="text-gray-500 text-sm"> &ndash; {{ $tag->note }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500 mb-6">Noch keine Personen benannt.</p>
            @endif

            <hr class="mb-4">
            <h2 class="font-semibold mb-2">Person benennen</h2>
            @auth
                <form method="POST" action="{{ route('archive.suggest-tag', $photo) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium mb-1" for="person_id">Bereits erfasste Person</label>
                        <select id="person_id" name="person_id" class="w-full rounded-md border-gray-300 text-sm focus:border-accent focus:ring-accent">
                            <option value="">– Bitte wählen –</option>
                            @foreach ($people as $person)
                                <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium mb-1" for="new_first_name">Vorname (neu)</label>
                            <input type="text" id="new_first_name" name="new_first_name" class="w-full rounded-md border-gray-300 text-sm focus:border-accent focus:ring-accent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1" for="new_last_name">Nachname (neu)</label>
                            <input type="text" id="new_last_name" name="new_last_name" class="w-full rounded-md border-gray-300 text-sm focus:border-accent focus:ring-accent">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" for="note">Anmerkung zur Position</label>
                        <input type="text" id="note" name="note" placeholder="z. B. hintere Reihe, 2. von links" class="w-full rounded-md border-gray-300 text-sm focus:border-accent focus:ring-accent">
                    </div>
                    <button type="submit" class="bg-accent hover:bg-accent-dark text-white px-4 py-2 rounded-md text-sm font-medium">
                        Vorschlag einreichen
                    </button>
                    <p class="text-xs text-gray-500">Ihr Vorschlag wird vor der Veröffentlichung geprüft.</p>
                </form>
            @else
                <p class="text-sm text-gray-500">
                    <a href="{{ route('login') }}?next={{ request()->path() }}" class="text-accent hover:text-accent-dark">Melden Sie sich an</a>,
                    um Personen auf diesem Foto zu benennen.
                </p>
            @endauth
        </div>
    </div>
</x-app-layout>
