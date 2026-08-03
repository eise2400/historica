<x-app-layout title="Fotoarchiv">
    <h1 class="text-2xl font-semibold mb-6">Fotoarchiv</h1>

    <form method="GET" action="{{ route('archive.index') }}" class="bg-brand-light rounded-lg p-4 mb-6 grid gap-3 sm:grid-cols-5 items-end">
        <div class="sm:col-span-2">
            <label class="block text-xs font-medium mb-1" for="q">Suche</label>
            <input type="text" id="q" name="q" value="{{ $query }}" placeholder="Titel, Beschreibung, Person"
                   class="w-full rounded-md border-gray-300 text-sm focus:border-accent focus:ring-accent">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1" for="kategorie">Kategorie</label>
            <select id="kategorie" name="kategorie" class="w-full rounded-md border-gray-300 text-sm focus:border-accent focus:ring-accent">
                <option value="">Alle Kategorien</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected($category->slug === $selectedCategory)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium mb-1" for="ort">Ort</label>
            <select id="ort" name="ort" class="w-full rounded-md border-gray-300 text-sm focus:border-accent focus:ring-accent">
                <option value="">Alle Orte</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->slug }}" @selected($location->slug === $selectedLocation)>{{ $location->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium mb-1" for="jahr">Jahr</label>
            <input type="number" id="jahr" name="jahr" value="{{ $selectedYear }}" placeholder="z. B. 1965"
                   class="w-full rounded-md border-gray-300 text-sm focus:border-accent focus:ring-accent">
        </div>
        <div class="sm:col-span-5">
            <button type="submit" class="bg-accent hover:bg-accent-dark text-white px-4 py-2 rounded-md text-sm font-medium">Filtern</button>
        </div>
    </form>

    @if ($photos->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($photos as $photo)
                <a href="{{ $photo->url }}" class="block bg-white rounded-md shadow-sm overflow-hidden hover:shadow-md transition">
                    <img src="{{ $photo->image_url }}" alt="{{ $photo->title }}" loading="lazy" class="h-44 w-full object-cover">
                    <div class="p-2">
                        <div class="text-sm font-medium truncate">{{ $photo->title }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $photo->date_display }}
                            @if ($photo->location) &middot; {{ $photo->location->name }} @endif
                        </div>
                        <span class="inline-block mt-1 text-xs bg-brand-light text-brand-dark px-2 py-0.5 rounded">{{ $photo->category->name }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $photos->links() }}
        </div>
    @else
        <p class="text-gray-500">Keine Fotos gefunden.</p>
    @endif
</x-app-layout>
