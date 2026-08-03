/* Click-to-tag: lets the webmaster mark where a person is standing on a
 * group photo. Workflow: click "Position setzen" next to a person row,
 * then click the spot on the photo preview. */
(function () {
    function markerIdFor(row) {
        if (!row.dataset.markerId) {
            row.dataset.markerId = "marker-" + Math.random().toString(36).slice(2);
        }
        return row.dataset.markerId;
    }

    function updateMarker(row) {
        var overlay = document.getElementById("photo-preview-overlay");
        if (!overlay) return;
        var xInput = row.querySelector('input[name$="-x_percent"]');
        var yInput = row.querySelector('input[name$="-y_percent"]');
        var id = markerIdFor(row);
        var existing = document.getElementById(id);
        var x = parseFloat(xInput.value);
        var y = parseFloat(yInput.value);
        if (isNaN(x) || isNaN(y)) {
            if (existing) existing.remove();
            return;
        }
        if (!existing) {
            existing = document.createElement("div");
            existing.id = id;
            existing.className = "tag-marker";
            overlay.appendChild(existing);
        }
        existing.style.left = x + "%";
        existing.style.top = y + "%";
    }

    function armRow(row, btn) {
        document.querySelectorAll(".set-position-btn.armed").forEach(function (b) {
            b.classList.remove("armed");
        });
        document.querySelectorAll("tr.photo-tag-row-armed").forEach(function (r) {
            r.classList.remove("photo-tag-row-armed");
        });
        btn.classList.add("armed");
        row.classList.add("photo-tag-row-armed");
        window.__historicaArmedRow = row;
    }

    function initRow(row) {
        var xInput = row.querySelector('input[name$="-x_percent"]');
        var yInput = row.querySelector('input[name$="-y_percent"]');
        if (!xInput || !yInput || row.dataset.tagInit) return;
        row.dataset.tagInit = "1";

        var btn = document.createElement("button");
        btn.type = "button";
        btn.className = "set-position-btn";
        btn.textContent = "Position setzen";
        xInput.parentNode.appendChild(btn);
        btn.addEventListener("click", function () {
            armRow(row, btn);
        });

        xInput.addEventListener("change", function () {
            updateMarker(row);
        });
        yInput.addEventListener("change", function () {
            updateMarker(row);
        });
        updateMarker(row);
    }

    function initAllRows() {
        document.querySelectorAll("tr").forEach(function (row) {
            if (row.querySelector('input[name$="-x_percent"]')) {
                initRow(row);
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        initAllRows();

        var img = document.getElementById("photo-preview-img");
        if (img) {
            img.addEventListener("click", function (evt) {
                var row = window.__historicaArmedRow;
                if (!row) {
                    alert('Bitte zuerst bei der gewünschten Person auf "Position setzen" klicken.');
                    return;
                }
                var rect = img.getBoundingClientRect();
                var xPct = ((evt.clientX - rect.left) / rect.width) * 100;
                var yPct = ((evt.clientY - rect.top) / rect.height) * 100;
                var xInput = row.querySelector('input[name$="-x_percent"]');
                var yInput = row.querySelector('input[name$="-y_percent"]');
                xInput.value = Math.max(0, Math.min(100, xPct)).toFixed(2);
                yInput.value = Math.max(0, Math.min(100, yPct)).toFixed(2);
                updateMarker(row);
            });
        }

        if (window.django && django.jQuery) {
            django.jQuery(document).on("formset:added", function () {
                initAllRows();
            });
        }
    });
})();
