<x-filament-panels::page>
    @if (session('success'))
        <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7">
            <div id="tag-preview-wrapper" class="relative inline-block max-w-full">
                <img id="tag-preview-img" src="{{ $photo->image_url }}" alt="{{ $photo->title }}"
                     class="max-w-full h-auto rounded-md border border-gray-200 cursor-crosshair">
                <div id="tag-preview-overlay" class="absolute inset-0 pointer-events-none"></div>
            </div>
            <p class="text-sm text-gray-500 mt-2">
                Bei der gewünschten Person unten auf <strong>„Position setzen“</strong> klicken und anschließend auf die
                Stelle im Foto klicken, um die Position zu markieren. Danach bei der jeweiligen Person auf
                <strong>„Speichern“</strong> klicken. Die kleinen Ringe im Foto zeigen bereits gesetzte Positionen
                (Name erscheint beim Überfahren mit der Maus).
            </p>
        </div>

        <div class="lg:col-span-5 space-y-6">
            <div>
                <h3 class="font-semibold mb-3">Markierte Personen</h3>
                <div class="space-y-4">
                    @forelse ($photo->personTags as $tag)
                        <div class="border border-gray-200 rounded-md p-3" data-tag-row data-person-name="{{ $tag->person->full_name }}">
                            <form method="POST" action="{{ route('admin.photos.tags.update', [$photo, $tag]) }}" class="space-y-2">
                                @csrf
                                @method('PUT')
                                <div class="flex items-center justify-between">
                                    <span class="font-medium">{{ $tag->person->full_name }}</span>
                                    <button type="button" class="set-position-btn text-xs px-2 py-1 rounded border border-accent text-accent hover:bg-accent hover:text-white">
                                        Position setzen
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="number" step="0.01" min="0" max="100" name="x_percent" value="{{ $tag->x_percent }}" placeholder="X (%)"
                                           class="rounded-md border-gray-300 text-sm">
                                    <input type="number" step="0.01" min="0" max="100" name="y_percent" value="{{ $tag->y_percent }}" placeholder="Y (%)"
                                           class="rounded-md border-gray-300 text-sm">
                                </div>
                                <input type="text" name="note" value="{{ $tag->note }}" placeholder="Anmerkung, z. B. hintere Reihe links"
                                       class="w-full rounded-md border-gray-300 text-sm">
                                <div class="flex items-center gap-2">
                                    <select name="status" class="rounded-md border-gray-300 text-sm flex-1">
                                        @foreach (\App\Models\PhotoPersonTag::STATUSES as $value => $label)
                                            <option value="{{ $value }}" @selected($tag->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="text-xs px-3 py-1.5 rounded bg-accent text-white hover:bg-accent-dark">Speichern</button>
                                </div>
                                @if ($tag->suggestedBy)
                                    <p class="text-xs text-gray-400">Vorgeschlagen von {{ $tag->suggestedBy->name }}</p>
                                @endif
                            </form>
                            <form method="POST" action="{{ route('admin.photos.tags.destroy', [$photo, $tag]) }}" class="mt-2"
                                  onsubmit="return confirm('Markierung wirklich entfernen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:text-red-800">Entfernen</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Noch keine Personen markiert.</p>
                    @endforelse
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <h3 class="font-semibold mb-3">Neue Person markieren</h3>
                <form method="POST" action="{{ route('admin.photos.tags.store', $photo) }}" class="space-y-2" data-tag-row data-person-name="">
                    @csrf
                    <input
                        type="text"
                        id="new-person-search"
                        list="new-person-options"
                        placeholder="Bereits erfasste Person suchen …"
                        class="w-full rounded-md border-gray-300 text-sm"
                        autocomplete="off"
                    >
                    <datalist id="new-person-options">
                        @foreach ($people as $person)
                            <option data-id="{{ $person->id }}" value="{{ $person->full_name }}"></option>
                        @endforeach
                    </datalist>
                    <input type="hidden" name="person_id" id="new-person-id">
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="new_first_name" placeholder="Vorname (neue Person)" class="rounded-md border-gray-300 text-sm">
                        <input type="text" name="new_last_name" placeholder="Nachname (neue Person)" class="rounded-md border-gray-300 text-sm">
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="button" class="set-position-btn text-xs px-2 py-1 rounded border border-accent text-accent hover:bg-accent hover:text-white">
                            Position setzen
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" step="0.01" min="0" max="100" name="x_percent" placeholder="X (%)" class="rounded-md border-gray-300 text-sm">
                        <input type="number" step="0.01" min="0" max="100" name="y_percent" placeholder="Y (%)" class="rounded-md border-gray-300 text-sm">
                    </div>
                    <input type="text" name="note" placeholder="Anmerkung zur Position" class="w-full rounded-md border-gray-300 text-sm">
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="text-sm px-4 py-2 rounded bg-accent text-white hover:bg-accent-dark">Person markieren</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var img = document.getElementById('tag-preview-img');
            var overlay = document.getElementById('tag-preview-overlay');
            var armedRow = null;

            function markers() {
                document.querySelectorAll('[data-marker]').forEach(function (m) { m.remove(); });
                document.querySelectorAll('[data-tag-row]').forEach(function (row) {
                    var x = parseFloat(row.querySelector('input[name="x_percent"]').value);
                    var y = parseFloat(row.querySelector('input[name="y_percent"]').value);
                    if (isNaN(x) || isNaN(y)) return;
                    var dot = document.createElement('div');
                    dot.setAttribute('data-marker', '1');
                    dot.title = row.dataset.personName || '';
                    dot.style.position = 'absolute';
                    dot.style.left = x + '%';
                    dot.style.top = y + '%';
                    dot.style.width = '12px';
                    dot.style.height = '12px';
                    dot.style.marginLeft = '-6px';
                    dot.style.marginTop = '-6px';
                    dot.style.borderRadius = '50%';
                    dot.style.background = 'rgba(255, 255, 255, 0.35)';
                    dot.style.border = '2px solid rgba(168, 101, 43, 0.9)';
                    dot.style.pointerEvents = 'auto';
                    overlay.appendChild(dot);
                });
            }

            document.querySelectorAll('.set-position-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.set-position-btn').forEach(function (b) {
                        b.classList.remove('bg-accent', 'text-white');
                    });
                    btn.classList.add('bg-accent', 'text-white');
                    armedRow = btn.closest('[data-tag-row]');
                });
            });

            if (img) {
                img.addEventListener('click', function (evt) {
                    if (!armedRow) {
                        alert('Bitte zuerst bei der gewünschten Person auf "Position setzen" klicken.');
                        return;
                    }
                    var rect = img.getBoundingClientRect();
                    var xPct = ((evt.clientX - rect.left) / rect.width) * 100;
                    var yPct = ((evt.clientY - rect.top) / rect.height) * 100;
                    armedRow.querySelector('input[name="x_percent"]').value = Math.max(0, Math.min(100, xPct)).toFixed(2);
                    armedRow.querySelector('input[name="y_percent"]').value = Math.max(0, Math.min(100, yPct)).toFixed(2);
                    markers();
                });
            }

            var search = document.getElementById('new-person-search');
            var hiddenId = document.getElementById('new-person-id');
            var options = document.getElementById('new-person-options');
            if (search && hiddenId && options) {
                search.addEventListener('input', function () {
                    var match = Array.prototype.find.call(options.options, function (o) { return o.value === search.value; });
                    hiddenId.value = match ? match.dataset.id : '';
                    search.closest('[data-tag-row]').dataset.personName = search.value;
                });
            }

            markers();
        })();
    </script>
</x-filament-panels::page>
