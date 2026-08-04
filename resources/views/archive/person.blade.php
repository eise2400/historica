<x-app-layout :title="$person->full_name">
    <h1 class="text-xl font-semibold mb-1">{{ $person->full_name }}</h1>
    @if ($person->notes)
        <p class="text-gray-500 mb-4">{{ $person->notes }}</p>
    @endif

    <h2 class="font-semibold mt-6 mb-3">Fotos mit dieser Person</h2>
    @if ($photos->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach ($photos as $photo)
                <a href="{{ $photo->url }}" class="block bg-white rounded-md shadow-sm overflow-hidden hover:shadow-md transition">
                    <img src="{{ $photo->thumbnail_url }}" alt="{{ $photo->title }}" loading="lazy" class="h-40 w-full object-cover">
                    <div class="p-2 text-sm font-medium truncate">{{ $photo->title }}</div>
                </a>
            @endforeach
        </div>
    @else
        <p class="text-gray-500">Keine Fotos gefunden.</p>
    @endif
</x-app-layout>
