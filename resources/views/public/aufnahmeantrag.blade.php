<x-app-layout title="Aufnahmeantrag">
    <h1 class="text-2xl font-semibold mb-6">Aufnahmeantrag</h1>

    @if ($page && $page->content)
        <div class="rich-content max-w-3xl mb-6">{!! $page->content !!}</div>
    @endif
    @if ($page && $page->document_path)
        <a href="{{ $page->document_url }}" download class="inline-block mb-8 border border-gray-300 hover:border-accent px-4 py-2 rounded-md text-sm">
            Aufnahmeantrag als PDF herunterladen
        </a>
    @endif

    <h2 class="text-lg font-semibold mb-4">Online beitreten</h2>
    <form method="POST" action="{{ route('aufnahmeantrag.store') }}" class="grid sm:grid-cols-2 gap-4 max-w-3xl">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1" for="first_name">Vorname</label>
            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required class="w-full rounded-md border-gray-300 focus:border-accent focus:ring-accent">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="last_name">Nachname</label>
            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required class="w-full rounded-md border-gray-300 focus:border-accent focus:ring-accent">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium mb-1" for="street">Straße, Hausnummer</label>
            <input type="text" id="street" name="street" value="{{ old('street') }}" required class="w-full rounded-md border-gray-300 focus:border-accent focus:ring-accent">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="postal_code">PLZ</label>
            <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required class="w-full rounded-md border-gray-300 focus:border-accent focus:ring-accent">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="city">Ort</label>
            <input type="text" id="city" name="city" value="{{ old('city') }}" required class="w-full rounded-md border-gray-300 focus:border-accent focus:ring-accent">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="email">E-Mail</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required class="w-full rounded-md border-gray-300 focus:border-accent focus:ring-accent">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="phone">Telefon</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="w-full rounded-md border-gray-300 focus:border-accent focus:ring-accent">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" for="birth_date">Geburtsdatum</label>
            <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}" class="w-full rounded-md border-gray-300 focus:border-accent focus:ring-accent">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium mb-1" for="message">Anmerkungen</label>
            <textarea id="message" name="message" rows="4" class="w-full rounded-md border-gray-300 focus:border-accent focus:ring-accent">{{ old('message') }}</textarea>
        </div>
        <div class="sm:col-span-2">
            <button type="submit" class="bg-accent hover:bg-accent-dark text-white px-5 py-2.5 rounded-md font-medium">
                Aufnahmeantrag absenden
            </button>
        </div>
    </form>
</x-app-layout>
