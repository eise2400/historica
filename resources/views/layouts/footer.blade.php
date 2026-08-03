<footer class="bg-brand-dark text-brand-light mt-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm">
        <div>&copy; {{ now()->year }} Historica Deing e.V. &ndash; Geschichts- und Heimatverein für Teugn</div>
        <div class="space-x-3">
            <a href="{{ route('impressum') }}" class="hover:text-white">Impressum</a>
            <span>&middot;</span>
            <a href="{{ route('datenschutz') }}" class="hover:text-white">Datenschutz</a>
            <span>&middot;</span>
            <a href="{{ route('kontakt') }}" class="hover:text-white">Kontakt</a>
        </div>
    </div>
</footer>
