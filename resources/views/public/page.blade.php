<x-app-layout :title="$page->title">
    <h1 class="text-2xl font-semibold mb-6">{{ $page->title }}</h1>
    <div class="rich-content max-w-3xl">
        {!! $page->content !!}
    </div>
    @if ($page->document_path)
        <a href="{{ $page->document_url }}" download class="inline-block mt-6 bg-accent hover:bg-accent-dark text-white px-5 py-2.5 rounded-md font-medium">
            {{ $page->title }} als PDF herunterladen
        </a>
    @endif
</x-app-layout>
