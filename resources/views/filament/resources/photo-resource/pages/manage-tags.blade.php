<x-filament-panels::page>
    @include('filament.partials.accent-buttons')

    @if (session('success'))
        <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7">
            <button type="button" id="mark-mode-toggle" class="btn-accent-outline text-xs px-3 py-1.5 rounded border mb-2">
                Markiermodus: Aus
            </button>
            <div id="tag-preview-wrapper" class="relative inline-block max-w-full">
                <img id="tag-preview-img" src="{{ $photo->image_url }}" alt="{{ $photo->title }}"
                     class="max-w-full h-auto rounded-md border border-gray-200 cursor-crosshair">
                <div id="tag-preview-overlay" class="absolute inset-0 pointer-events-none"></div>
            </div>
            <p class="text-sm text-gray-500 mt-2">
                Bei aktiviertem <strong>Markiermodus</strong> direkt auf eine freie Stelle im Foto klicken, um dort
                sofort eine neue Person zu markieren, oder eine bestehende Markierung anklicken, um sie zu bearbeiten
                oder zu entfernen - beides öffnet ein kleines Eingabefenster. Alternativ (z. B. für Vorschläge ohne
                Position) weiterhin über die Liste rechts: bei der gewünschten Person auf <strong>„Position
                setzen“</strong> klicken und anschließend auf die Stelle im Foto klicken, dann bei der Person auf
                <strong>„Speichern“</strong> klicken.
            </p>
        </div>

        <div class="lg:col-span-5 space-y-6">
            <div>
                <h3 class="font-semibold mb-3">Markierte Personen</h3>
                <div class="space-y-4">
                    @forelse ($photo->personTags as $tag)
                        <div class="border border-gray-200 rounded-md p-3" data-tag-row data-tag-id="{{ $tag->id }}" data-person-id="{{ $tag->person->id }}" data-person-name="{{ $tag->person->full_name }}">
                            <form method="POST" action="{{ route('admin.photos.tags.update', [$photo, $tag]) }}" class="space-y-2">
                                @csrf
                                @method('PUT')
                                <div class="flex items-center justify-between">
                                    <span class="font-medium">{{ $tag->person->full_name }}</span>
                                    <button type="button" class="set-position-btn btn-accent-outline text-xs px-2 py-1 rounded border">
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
                                    <button type="submit" class="btn-accent text-xs px-3 py-1.5 rounded">Speichern</button>
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
                        <button type="button" class="set-position-btn btn-accent-outline text-xs px-2 py-1 rounded border">
                            Position setzen
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" step="0.01" min="0" max="100" name="x_percent" placeholder="X (%)" class="rounded-md border-gray-300 text-sm">
                        <input type="number" step="0.01" min="0" max="100" name="y_percent" placeholder="Y (%)" class="rounded-md border-gray-300 text-sm">
                    </div>
                    <input type="text" name="note" placeholder="Anmerkung zur Position" class="w-full rounded-md border-gray-300 text-sm">
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="btn-accent text-sm px-4 py-2 rounded">Person markieren</button>
                </form>
            </div>
        </div>
    </div>

    <dialog id="tag-popup" class="rounded-md p-0 border border-gray-200 shadow-lg max-w-sm w-full">
        <form id="tag-popup-form" method="POST" class="space-y-3 p-4">
            @csrf
            <input type="hidden" name="_method" id="tag-popup-method" value="">
            <h3 id="tag-popup-title" class="font-semibold">Person markieren</h3>
            <div>
                <label class="block text-xs font-medium mb-1" for="tag-popup-search">Bereits erfasste Person</label>
                <input
                    type="text"
                    id="tag-popup-search"
                    list="tag-popup-options"
                    placeholder="Namen eingeben zum Suchen …"
                    class="w-full rounded-md border-gray-300 text-sm"
                    autocomplete="off"
                >
                <datalist id="tag-popup-options">
                    @foreach ($people as $person)
                        <option data-id="{{ $person->id }}" value="{{ $person->full_name }}"></option>
                    @endforeach
                </datalist>
                <input type="hidden" name="person_id" id="tag-popup-person-id">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <input type="text" name="new_first_name" id="tag-popup-first-name" placeholder="Vorname (neue Person)" class="rounded-md border-gray-300 text-sm">
                <input type="text" name="new_last_name" id="tag-popup-last-name" placeholder="Nachname (neue Person)" class="rounded-md border-gray-300 text-sm">
            </div>
            <input type="text" name="note" id="tag-popup-note" placeholder="Anmerkung zur Position" class="w-full rounded-md border-gray-300 text-sm">
            <input type="hidden" name="status" value="approved">
            <input type="hidden" name="x_percent" id="tag-popup-x">
            <input type="hidden" name="y_percent" id="tag-popup-y">
            <input type="hidden" name="markmode" value="1">
            <div class="flex items-center justify-between gap-2 pt-2">
                <button type="submit" form="tag-popup-delete-form" id="tag-popup-delete" class="text-xs text-red-600 hover:text-red-800 hidden">Löschen</button>
                <div class="flex gap-2 ml-auto">
                    <button type="button" id="tag-popup-cancel" class="btn-accent-outline text-sm px-3 py-1.5 rounded border">Abbrechen</button>
                    <button type="submit" class="btn-accent text-sm px-4 py-1.5 rounded">Speichern</button>
                </div>
            </div>
        </form>
    </dialog>
    <form id="tag-popup-delete-form" method="POST" class="hidden" onsubmit="return confirm('Markierung wirklich entfernen?');">
        @csrf
        @method('DELETE')
        <input type="hidden" name="markmode" value="1">
    </form>

    <script>
        (function () {
            var img = document.getElementById('tag-preview-img');
            var overlay = document.getElementById('tag-preview-overlay');
            var armedRow = null;
            var markModeActive = false;
            var markModeToggle = document.getElementById('mark-mode-toggle');
            var tagPopup = document.getElementById('tag-popup');
            var popupForm = document.getElementById('tag-popup-form');
            var popupMethod = document.getElementById('tag-popup-method');
            var popupTitle = document.getElementById('tag-popup-title');
            var popupSearch = document.getElementById('tag-popup-search');
            var popupOptions = document.getElementById('tag-popup-options');
            var popupPersonId = document.getElementById('tag-popup-person-id');
            var popupFirstName = document.getElementById('tag-popup-first-name');
            var popupLastName = document.getElementById('tag-popup-last-name');
            var popupNote = document.getElementById('tag-popup-note');
            var popupX = document.getElementById('tag-popup-x');
            var popupY = document.getElementById('tag-popup-y');
            var popupDelete = document.getElementById('tag-popup-delete');
            var deleteForm = document.getElementById('tag-popup-delete-form');

            function setMarkMode(active) {
                markModeActive = active;
                if (markModeToggle) {
                    markModeToggle.classList.toggle('is-armed', active);
                    markModeToggle.textContent = 'Markiermodus: ' + (active ? 'Ein' : 'Aus');
                }
            }

            if (markModeToggle) {
                markModeToggle.addEventListener('click', function () {
                    setMarkMode(!markModeActive);
                });
            }

            if (new URLSearchParams(window.location.search).get('markmode') === '1') {
                setMarkMode(true);
            }

            function openPopup(opts) {
                popupSearch.value = '';
                popupPersonId.value = '';
                popupFirstName.value = '';
                popupLastName.value = '';
                popupNote.value = opts.note || '';
                popupX.value = opts.x.toFixed(2);
                popupY.value = opts.y.toFixed(2);

                if (opts.mode === 'edit') {
                    popupTitle.textContent = 'Markierung bearbeiten';
                    popupSearch.value = opts.personName || '';
                    popupPersonId.value = opts.personId || '';
                    popupForm.action = opts.updateUrl;
                    popupMethod.value = 'PUT';
                    deleteForm.action = opts.destroyUrl;
                    popupDelete.classList.remove('hidden');
                } else {
                    popupTitle.textContent = 'Person markieren';
                    popupForm.action = opts.storeUrl;
                    popupMethod.value = '';
                    popupDelete.classList.add('hidden');
                }

                tagPopup.showModal();
                popupSearch.focus();
            }

            if (popupSearch && popupPersonId && popupOptions) {
                popupSearch.addEventListener('input', function () {
                    var match = Array.prototype.find.call(popupOptions.options, function (o) { return o.value === popupSearch.value; });
                    popupPersonId.value = match ? match.dataset.id : '';
                });
            }

            var popupCancel = document.getElementById('tag-popup-cancel');
            if (popupCancel) {
                popupCancel.addEventListener('click', function () { tagPopup.close(); });
            }

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

                    if (row.dataset.tagId) {
                        dot.style.cursor = 'pointer';
                        dot.addEventListener('click', function (evt) {
                            if (!markModeActive) return;
                            evt.stopPropagation();
                            var noteInput = row.querySelector('input[name="note"]');
                            openPopup({
                                mode: 'edit',
                                personId: row.dataset.personId,
                                personName: row.dataset.personName,
                                note: noteInput ? noteInput.value : '',
                                x: x,
                                y: y,
                                updateUrl: row.querySelector('form').action,
                                destroyUrl: row.querySelectorAll('form')[1].action,
                            });
                        });
                    }

                    overlay.appendChild(dot);
                });
            }

            document.querySelectorAll('.set-position-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.set-position-btn').forEach(function (b) {
                        b.classList.remove('is-armed');
                    });
                    btn.classList.add('is-armed');
                    armedRow = btn.closest('[data-tag-row]');
                });
            });

            if (img) {
                img.addEventListener('click', function (evt) {
                    var rect = img.getBoundingClientRect();
                    var xPct = Math.max(0, Math.min(100, ((evt.clientX - rect.left) / rect.width) * 100));
                    var yPct = Math.max(0, Math.min(100, ((evt.clientY - rect.top) / rect.height) * 100));

                    if (markModeActive) {
                        openPopup({
                            mode: 'create',
                            x: xPct,
                            y: yPct,
                            storeUrl: @js(route('admin.photos.tags.store', $photo)),
                        });
                        return;
                    }

                    if (!armedRow) {
                        alert('Bitte zuerst bei der gewünschten Person auf "Position setzen" klicken.');
                        return;
                    }
                    armedRow.querySelector('input[name="x_percent"]').value = xPct.toFixed(2);
                    armedRow.querySelector('input[name="y_percent"]').value = yPct.toFixed(2);
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
