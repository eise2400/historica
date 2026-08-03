<x-app-layout title="Kontakt">
    <h1 class="text-2xl font-semibold mb-6">Kontakt</h1>

    <div class="grid md:grid-cols-12 gap-8">
        <form method="POST" action="{{ route('kontakt.store') }}" class="md:col-span-7 space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium mb-1">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-md border-gray-300 focus:border-accent focus:ring-accent">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium mb-1">E-Mail</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-md border-gray-300 focus:border-accent focus:ring-accent">
            </div>
            <div>
                <label for="subject" class="block text-sm font-medium mb-1">Betreff</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject') }}"
                       class="w-full rounded-md border-gray-300 focus:border-accent focus:ring-accent">
            </div>
            <div>
                <label for="message" class="block text-sm font-medium mb-1">Nachricht</label>
                <textarea id="message" name="message" rows="6" required
                          class="w-full rounded-md border-gray-300 focus:border-accent focus:ring-accent">{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="bg-accent hover:bg-accent-dark text-white px-5 py-2.5 rounded-md font-medium">
                Nachricht senden
            </button>
        </form>

        <div class="md:col-span-5">
            <h2 class="text-lg font-semibold mb-2">Historica Deing e.V.</h2>
            <address class="not-italic text-gray-700 leading-relaxed">
                Vereinsheim Historica Deing<br>
                Teugn<br>
                E-Mail: <a href="mailto:info@historica-deing.de" class="text-accent hover:text-accent-dark">info@historica-deing.de</a>
            </address>
        </div>
    </div>
</x-app-layout>
