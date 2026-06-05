/**
 * esse-gallery — Lightbox + Admin-Upload
 * Vanilla JS, keine externen Abhängigkeiten.
 *
 * Exportierte Einstiegspunkte:
 *   galLightboxInit()  — Frontend-Album-Seite
 *   galAdminInit()     — Admin-Bilder-Verwaltung
 */

/* =====================================================================
   LIGHTBOX (Frontend)
   ===================================================================== */
function galLightboxInit() {
    var links     = Array.from(document.querySelectorAll('.gal-thumb-link'));
    var lightbox  = document.getElementById('gal-lightbox');
    var lbImg     = document.getElementById('gal-lb-img');
    var lbTitle   = document.getElementById('gal-lb-title');
    var lbCounter = document.getElementById('gal-lb-counter');
    var lbPrev    = document.getElementById('gal-lb-prev');
    var lbNext    = document.getElementById('gal-lb-next');
    var lbClose   = document.getElementById('gal-lb-close');

    if (!lightbox || links.length === 0) return;

    var cur = 0;

    function show(i) {
        cur = Math.max(0, Math.min(i, links.length - 1));
        var link      = links[cur];
        lbImg.src     = link.href;
        lbImg.alt     = link.dataset.caption || '';
        lbTitle.textContent   = link.dataset.caption || '';
        lbCounter.textContent = (cur + 1) + ' / ' + links.length;
        lbPrev.disabled = cur === 0;
        lbNext.disabled = cur === links.length - 1;
    }

    function open(i) {
        show(i);
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function close() {
        lightbox.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    links.forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            open(parseInt(a.dataset.index, 10) || 0);
        });
    });

    lbClose.addEventListener('click', close);
    lbPrev.addEventListener('click', function () { show(cur - 1); });
    lbNext.addEventListener('click', function () { show(cur + 1); });

    // Touch-Swipe
    var touchStartX = 0;
    lightbox.addEventListener('touchstart', function (e) {
        touchStartX = e.changedTouches[0].clientX;
    }, { passive: true });
    lightbox.addEventListener('touchend', function (e) {
        var dx = e.changedTouches[0].clientX - touchStartX;
        if (Math.abs(dx) > 40) dx < 0 ? show(cur + 1) : show(cur - 1);
    }, { passive: true });

    // Tastatur
    document.addEventListener('keydown', function (e) {
        if (!lightbox.classList.contains('is-open')) return;
        if (e.key === 'ArrowLeft')  { show(cur - 1); }
        if (e.key === 'ArrowRight') { show(cur + 1); }
        if (e.key === 'Escape')     { close(); }
    });
}

/* =====================================================================
   ADMIN-UPLOAD & VERWALTUNG
   ===================================================================== */
function galAdminInit() {
    var dropzone    = document.getElementById('gal-dropzone');
    var fileInput   = document.getElementById('gal-file-input');
    var progressArea = document.getElementById('gal-progress-area');
    var imageGrid   = document.getElementById('gal-image-grid');

    if (!dropzone || !fileInput) return;

    // --- Datei-Auswahl ---
    fileInput.addEventListener('change', function () {
        galUploadFiles(Array.from(fileInput.files));
        fileInput.value = '';
    });

    // --- Drag & Drop ---
    dropzone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropzone.classList.remove('gal-dropzone--active');
        var files = Array.from(e.dataTransfer.files).filter(function (f) {
            return f.type.startsWith('image/');
        });
        if (files.length) galUploadFiles(files);
    });

    // --- Caption inline speichern ---
    imageGrid.addEventListener('blur', function (e) {
        if (!e.target.classList.contains('gal-caption-input')) return;
        var imgId   = e.target.dataset.imgId;
        var caption = e.target.value;
        galSaveCaption(imgId, caption);
    }, true);

    imageGrid.addEventListener('keydown', function (e) {
        if (!e.target.classList.contains('gal-caption-input')) return;
        if (e.key === 'Enter') { e.preventDefault(); e.target.blur(); }
    });

    // --- Cover setzen ---
    imageGrid.addEventListener('click', function (e) {
        var btn = e.target.closest('.gal-btn-cover');
        if (!btn) return;
        galSetCover(parseInt(btn.dataset.imgId, 10));
    });

    // --- Bild löschen ---
    imageGrid.addEventListener('click', function (e) {
        var btn = e.target.closest('.gal-btn-delete');
        if (!btn) return;
        if (!confirm('Bild wirklich löschen?')) return;
        galDeleteImage(parseInt(btn.dataset.imgId, 10));
    });
}

// window-level-Handler für den Drop auf der Drop-Zone (auch von Plugin.php aus aufgerufen)
function galHandleDrop(e) {
    e.preventDefault();
    document.getElementById('gal-dropzone').classList.remove('gal-dropzone--active');
    var files = Array.from(e.dataTransfer.files).filter(function (f) {
        return f.type.startsWith('image/');
    });
    if (files.length) galUploadFiles(files);
}

function galUploadFiles(files) {
    files.forEach(function (file) {
        galUploadSingle(file);
    });
}

function galUploadSingle(file) {
    var progressArea = document.getElementById('gal-progress-area');
    var imageGrid    = document.getElementById('gal-image-grid');

    // Fortschrittsbalken anlegen
    var barId  = 'gal-bar-' + Date.now() + '-' + Math.random().toString(36).slice(2);
    var barWrap = document.createElement('div');
    barWrap.className = 'gal-progress-wrap';
    barWrap.innerHTML =
        '<div class="gal-progress-info">' +
            '<span class="gal-progress-name">' + galEsc(file.name) + '</span>' +
            '<span class="gal-progress-pct" id="' + barId + '-pct">0 %</span>' +
        '</div>' +
        '<div class="gal-progress-track">' +
            '<div id="' + barId + '" class="gal-progress-fill" style="width:0%"></div>' +
        '</div>';
    progressArea.appendChild(barWrap);

    var fd = new FormData();
    fd.append('_csrf', GAL_CSRF);
    fd.append('file', file);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', GAL_UPLOAD_URL);

    xhr.upload.addEventListener('progress', function (e) {
        if (!e.lengthComputable) return;
        var pct = Math.round(e.loaded / e.total * 100);
        var bar = document.getElementById(barId);
        var pctEl = document.getElementById(barId + '-pct');
        if (bar) bar.style.width = pct + '%';
        if (pctEl) pctEl.textContent = pct + ' %';
    });

    xhr.addEventListener('load', function () {
        barWrap.remove();

        var res;
        try { res = JSON.parse(xhr.responseText); } catch (ex) { res = { error: 'Ungültige Server-Antwort.' }; }

        if (!res.success) {
            var errEl = document.createElement('div');
            errEl.className = 'gal-upload-error';
            errEl.textContent = galEsc(file.name) + ': ' + (res.error || 'Upload-Fehler');
            progressArea.appendChild(errEl);
            return;
        }

        // Leere-Hinweis entfernen
        var hint = document.getElementById('gal-empty-hint');
        if (hint) hint.remove();

        // Karte einfügen
        var col = document.createElement('div');
        col.className = 'esse-grid-item';
        col.id = 'gal-img-' + res.image_id;
        col.innerHTML = res.html;
        imageGrid.appendChild(col);
    });

    xhr.addEventListener('error', function () {
        barWrap.remove();
        var errEl = document.createElement('div');
        errEl.className = 'gal-upload-error';
        errEl.textContent = galEsc(file.name) + ': Netzwerkfehler.';
        progressArea.appendChild(errEl);
    });

    xhr.send(fd);
}

function galSaveCaption(imgId, caption) {
    var fd = new FormData();
    fd.append('_csrf', GAL_CSRF);
    fd.append('caption', caption);
    fetch('/admin/gallery/images/' + imgId + '/caption', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.success) console.warn('Caption-Fehler:', res.error);
        });
}

function galDeleteImage(imgId) {
    var fd = new FormData();
    fd.append('_csrf', GAL_CSRF);
    fetch('/admin/gallery/images/' + imgId + '/delete', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                var col = document.getElementById('gal-img-' + imgId);
                if (col) col.remove();
                // Wenn Grid leer → Hinweis anzeigen
                var grid = document.getElementById('gal-image-grid');
                if (grid && grid.querySelectorAll('.gal-image-card').length === 0) {
                    var hint = document.createElement('div');
                    hint.id = 'gal-empty-hint';
                    hint.style.cssText = 'grid-column:1/-1';
                    hint.innerHTML = '<div class="esse-empty-state">'
                        + '<div class="esse-empty-icon"><i class="bi bi-images"></i></div>'
                        + '<h3 class="esse-empty-title">Noch keine Bilder</h3>'
                        + '</div>';
                    grid.appendChild(hint);
                }
            } else {
                alert(res.error || 'Fehler beim Löschen.');
            }
        });
}

function galSetCover(imgId) {
    var fd = new FormData();
    fd.append('_csrf', GAL_CSRF);
    fetch('/admin/gallery/images/' + imgId + '/cover', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                // Alle Cover-Badges entfernen und neues setzen
                document.querySelectorAll('.gal-image-card').forEach(function (card) {
                    var badge = card.querySelector('.esse-badge--warning');
                    if (badge) badge.remove();
                });
                var thumb = document.querySelector('.gal-image-card[data-img-id="' + imgId + '"] .gal-card-thumb');
                if (thumb) {
                    var badge = document.createElement('span');
                    badge.className = 'esse-badge esse-badge--warning';
                    badge.innerHTML = '★ Cover';
                    thumb.appendChild(badge);
                }
                GAL_COVER_ID = imgId;
            } else {
                alert(res.error || 'Fehler.');
            }
        });
}

function galEsc(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
