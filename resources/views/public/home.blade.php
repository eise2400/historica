<x-app-layout>
    <div class="rounded-lg bg-gradient-to-br from-brand-dark to-brand text-white p-8 sm:p-12 mb-10">
        <h1 class="text-3xl font-bold mb-3">Historica Deing e.V.</h1>
        <p class="text-brand-light text-lg mb-6">
            Wir bewahren und erforschen die Geschichte von Teugn &ndash; mit einem wachsenden Fotoarchiv,
            Vereinsarbeit und Blick für Details.
        </p>
        <a href="{{ route('archive.index') }}" class="inline-block bg-accent hover:bg-accent-dark text-white px-5 py-2.5 rounded-md font-medium mr-3">Zum Fotoarchiv</a>
        <a href="{{ route('aufnahmeantrag') }}" class="inline-block border border-white/60 hover:bg-white/10 text-white px-5 py-2.5 rounded-md font-medium">Mitglied werden</a>
    </div>

    <section class="mb-10">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">Neu im Fotoarchiv</h2>
            <a href="{{ route('archive.index') }}" class="text-accent hover:text-accent-dark text-sm">Alle Fotos ansehen &rarr;</a>
        </div>

        @if ($latestPhotos->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($latestPhotos as $photo)
                    <a href="{{ $photo->url }}" class="block bg-white rounded-md shadow-sm overflow-hidden hover:shadow-md transition">
                        <img src="{{ $photo->thumbnail_url }}" alt="{{ $photo->title }}" loading="lazy" class="h-40 w-full object-cover">
                        <div class="p-2">
                            <div class="text-sm font-medium truncate">{{ $photo->title }}</div>
                            <span class="inline-block mt-1 text-xs bg-brand-light text-brand-dark px-2 py-0.5 rounded">{{ $photo->category->name }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Es sind noch keine Fotos veröffentlicht.</p>
        @endif
    </section>

    <section>
        <h2 class="text-xl font-semibold mb-3">Der Verein</h2>
        <p class="text-gray-700 mb-4 max-w-3xl">
            Historica Deing e.V. widmet sich der Erforschung und Vermittlung der Geschichte des Ortes Teugn.
            Neben Vortragsabenden und Ausstellungen betreiben wir ein digitales Fotoarchiv, in dem historische
            Aufnahmen aus den Bereichen Ortsansichten, Vereinsleben und Landwirtschaft gesammelt, zeitlich und
            räumlich eingeordnet sowie mit Namen von abgebildeten Personen versehen werden.
        </p>
        <a href="{{ route('satzung') }}" class="inline-block border border-gray-300 hover:border-accent px-4 py-2 rounded-md text-sm mr-2">Unsere Satzung</a>
        <a href="{{ route('kontakt') }}" class="inline-block border border-gray-300 hover:border-accent px-4 py-2 rounded-md text-sm">Kontakt aufnehmen</a>
    </section>
</x-app-layout>
