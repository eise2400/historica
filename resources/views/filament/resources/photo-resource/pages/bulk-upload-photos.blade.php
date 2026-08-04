<x-filament-panels::page>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Mehrere Fotos auf einmal hochladen. Kategorie, Ort und Datierung gelten dabei für den
        ganzen Stapel – Titel, Beschreibung und Personen-Markierungen werden anschließend pro
        Foto einzeln ergänzt. Bei sehr vielen Dateien empfiehlt es sich, in Gruppen von
        50–100 Fotos hochzuladen.
    </p>

    <x-filament-panels::form wire:submit="upload">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page>
