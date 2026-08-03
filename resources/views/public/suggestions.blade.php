<x-app-layout title="Meine Vorschläge">
    <h1 class="text-2xl font-semibold mb-2">Mein Profil</h1>
    <p class="text-gray-600 mb-6">Angemeldet als <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})</p>

    <h2 class="text-lg font-semibold mb-4">Meine Personen-Vorschläge</h2>
    @if ($suggestions->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-200">
                        <th class="py-2 pr-4">Foto</th>
                        <th class="py-2 pr-4">Person</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Eingereicht am</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($suggestions as $tag)
                        <tr class="border-b border-gray-100">
                            <td class="py-2 pr-4"><a href="{{ $tag->photo->url }}" class="text-accent hover:text-accent-dark">{{ $tag->photo->title }}</a></td>
                            <td class="py-2 pr-4">{{ $tag->person->full_name }}</td>
                            <td class="py-2 pr-4">{{ $tag->statusLabel() }}</td>
                            <td class="py-2 pr-4">{{ $tag->created_at->format('d.m.Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500">Sie haben noch keine Personen vorgeschlagen.</p>
    @endif
</x-app-layout>
