<x-filament-panels::page>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Mehrere Fotos auf einmal hochladen. Kategorie, Ort und Datierung gelten dabei für den
        ganzen Stapel – Titel, Beschreibung und Personen-Markierungen werden anschließend pro
        Foto einzeln ergänzt. Bis zu 300 Dateien pro Durchgang; der Upload läuft automatisch in
        kleineren Gruppen ab, damit er auch bei vielen Fotos zuverlässig durchläuft.
    </p>

    <form id="bulk-upload-form" class="space-y-4 max-w-2xl">
        <div>
            <label for="category_id" class="block text-sm font-medium mb-1">Kategorie für alle Fotos</label>
            <select id="category_id" name="category_id" required class="w-full rounded-md border-gray-300 text-sm">
                <option value="">– bitte wählen –</option>
                @foreach ($this->getCategories() as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="location_id" class="block text-sm font-medium mb-1">Ort für alle Fotos (optional)</label>
            <select id="location_id" name="location_id" class="w-full rounded-md border-gray-300 text-sm">
                <option value="">– kein Ort –</option>
                @foreach ($this->getLocations() as $location)
                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="date_text" class="block text-sm font-medium mb-1">Datierung für alle Fotos (optional)</label>
            <input type="text" id="date_text" name="date_text" placeholder="z. B. 'um 1965'"
                   class="w-full rounded-md border-gray-300 text-sm">
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" id="is_published" name="is_published" class="rounded border-gray-300">
            <label for="is_published" class="text-sm">Sofort veröffentlichen (sonst als Entwurf angelegt)</label>
        </div>

        <div>
            <label for="images" class="block text-sm font-medium mb-1">Fotos</label>
            <input type="file" id="images" name="images" multiple accept="image/*" required
                   class="w-full text-sm">
            <p class="text-xs text-gray-500 mt-1">Bis zu 300 Bilddateien, je maximal 25 MB.</p>
        </div>

        <div id="bulk-upload-status" class="text-sm text-gray-600 dark:text-gray-400" role="status"></div>
        <div id="bulk-upload-error" class="text-sm text-red-600" role="alert"></div>

        <button type="submit" id="bulk-upload-submit"
                class="text-sm px-4 py-2 rounded bg-accent text-white hover:bg-accent-dark disabled:opacity-50">
            Fotos anlegen
        </button>
    </form>

    <script>
        (function () {
            var BATCH_SIZE = 15;
            var MAX_FILES = 300;
            var csrfToken = @js(csrf_token());
            var storeUrl = @js(route('admin.photos.bulk-upload.store'));
            var indexUrlBase = @js(\App\Filament\Resources\PhotoResource::getUrl('index'));

            var form = document.getElementById('bulk-upload-form');
            var submitBtn = document.getElementById('bulk-upload-submit');
            var statusEl = document.getElementById('bulk-upload-status');
            var errorEl = document.getElementById('bulk-upload-error');

            form.addEventListener('submit', function (evt) {
                evt.preventDefault();
                errorEl.textContent = '';

                var filesInput = document.getElementById('images');
                var files = Array.prototype.slice.call(filesInput.files);

                if (files.length === 0) {
                    errorEl.textContent = 'Bitte mindestens eine Datei auswählen.';
                    return;
                }
                if (files.length > MAX_FILES) {
                    errorEl.textContent = 'Bitte höchstens ' + MAX_FILES + ' Dateien auf einmal auswählen (ausgewählt: ' + files.length + ').';
                    return;
                }

                var categoryId = document.getElementById('category_id').value;
                if (!categoryId) {
                    errorEl.textContent = 'Bitte eine Kategorie auswählen.';
                    return;
                }
                var locationId = document.getElementById('location_id').value;
                var dateText = document.getElementById('date_text').value;
                var isPublished = document.getElementById('is_published').checked;

                var batches = [];
                for (var i = 0; i < files.length; i += BATCH_SIZE) {
                    batches.push(files.slice(i, i + BATCH_SIZE));
                }

                submitBtn.disabled = true;
                var created = 0;

                function uploadBatch(index) {
                    if (index >= batches.length) {
                        statusEl.textContent = created + ' Foto(s) erfolgreich hochgeladen. Sie werden weitergeleitet …';
                        var url = new URL(indexUrlBase, window.location.origin);
                        url.searchParams.set('tableFilters[is_published][value]', isPublished ? '1' : '0');
                        window.location.href = url.toString();
                        return;
                    }

                    statusEl.textContent = 'Lade Gruppe ' + (index + 1) + ' von ' + batches.length + ' hoch … (' + created + ' von ' + files.length + ' Fotos bisher übertragen)';

                    var body = new FormData();
                    body.append('category_id', categoryId);
                    if (locationId) {
                        body.append('location_id', locationId);
                    }
                    if (dateText) {
                        body.append('date_text', dateText);
                    }
                    body.append('is_published', isPublished ? '1' : '0');
                    batches[index].forEach(function (file) {
                        body.append('images[]', file);
                    });

                    fetch(storeUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: body,
                    }).then(function (response) {
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }
                        return response.json();
                    }).then(function (data) {
                        created += data.created || 0;
                        uploadBatch(index + 1);
                    }).catch(function (err) {
                        submitBtn.disabled = false;
                        errorEl.textContent = 'Fehler beim Hochladen von Gruppe ' + (index + 1) + ' (' + err.message + '). '
                            + created + ' Foto(s) wurden bereits angelegt und bleiben erhalten. Bitte die verbleibenden Dateien erneut hochladen.';
                        statusEl.textContent = '';
                    });
                }

                uploadBatch(0);
            });
        })();
    </script>
</x-filament-panels::page>
