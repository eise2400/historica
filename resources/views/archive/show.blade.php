<x-app-layout :title="$photo->title">
    <div class="grid lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            <div class="relative inline-block max-w-full" id="photo-wrapper">
                <img src="{{ $photo->image_url }}" alt="{{ $photo->title }}" class="max-w-full h-auto rounded-md">
                @foreach ($tags as $tag)
                    @if ($tag->x_percent !== null && $tag->y_percent !== null)
                        <button
                            type="button"
                            class="person-marker"
                            data-person-id="{{ $tag->person->id }}"
                            style="left: {{ $tag->x_percent }}%; top: {{ $tag->y_percent }}%;"
                        >
                            <span class="person-marker-label">
                                {{ $tag->person->full_name }}{{ $tag->note ? ' – '.$tag->note : '' }}
                            </span>
                        </button>
                    @endif
                @endforeach
            </div>
            @if ($tags->whereNotNull('x_percent')->isNotEmpty())
                <button type="button" id="toggle-markers-btn" class="mt-2 text-xs text-gray-500 hover:text-accent underline">
                    Markierungen auf dem Foto anzeigen
                </button>
            @endif

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
                        <li
                            class="person-list-item px-1 py-0.5"
                            @if ($tag->x_percent !== null) data-person-id="{{ $tag->person->id }}" @endif
                        >
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
                        <label class="block text-xs font-medium mb-1" for="person_search">Bereits erfasste Person</label>
                        <input
                            type="text"
                            id="person_search"
                            list="person_options"
                            placeholder="Namen eingeben zum Suchen …"
                            class="w-full rounded-md border-gray-300 text-sm focus:border-accent focus:ring-accent"
                            autocomplete="off"
                        >
                        <datalist id="person_options">
                            @foreach ($people as $person)
                                <option data-id="{{ $person->id }}" value="{{ $person->full_name }}"></option>
                            @endforeach
                        </datalist>
                        <input type="hidden" name="person_id" id="person_id">
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

@push('scripts')
<script>
    (function () {
        const wrapper = document.getElementById('photo-wrapper');
        const toggleBtn = document.getElementById('toggle-markers-btn');
        if (toggleBtn && wrapper) {
            toggleBtn.addEventListener('click', function () {
                const visible = wrapper.classList.toggle('markers-visible');
                toggleBtn.textContent = visible
                    ? 'Markierungen auf dem Foto ausblenden'
                    : 'Markierungen auf dem Foto anzeigen';
            });
        }

        function setActive(personId, active) {
            document.querySelectorAll('[data-person-id="' + personId + '"]').forEach(function (el) {
                el.classList.toggle('is-active', active);
            });
        }

        document.querySelectorAll('[data-person-id]').forEach(function (el) {
            const id = el.getAttribute('data-person-id');
            el.addEventListener('mouseenter', function () { setActive(id, true); });
            el.addEventListener('mouseleave', function () { setActive(id, false); });
            el.addEventListener('focus', function () { setActive(id, true); });
            el.addEventListener('blur', function () { setActive(id, false); });
        });

        const search = document.getElementById('person_search');
        const hiddenId = document.getElementById('person_id');
        const options = document.getElementById('person_options');
        if (search && hiddenId && options) {
            search.addEventListener('input', function () {
                const match = Array.from(options.options).find(function (o) { return o.value === search.value; });
                hiddenId.value = match ? match.dataset.id : '';
            });
        }
    })();
</script>
@endpush
