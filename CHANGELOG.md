# Changelog — esse-gallery

## [0.2.2] — 2026-06-05

### Behoben
- **Lightbox schließt nicht:** Zwei-Schichten-Fix:
  1. `display: none !important` auf `.gal-lightbox` verhindert dass Theme-CSS
     (`display: flex` ohne `!important`) die geschlossene Lightbox sichtbar hält
  2. Portal-Pattern: Lightbox wird per JS ans Ende von `<body>` verschoben,
     sodass kein Vorfahren-Element (transform, z-index, overflow) interferieren kann
- Direkte Event-Listener auf Buttons zusätzlich zur Delegation als Fallback;
  `closest()` im Delegation-Handler für robusteres Button-Targeting
- Backdrop-Klick (`.gal-lb-body`) schließt die Lightbox ebenfalls

---

## [0.2.1] — 2026-06-05

### Behoben
- **Lightbox-Buttons** (Schließen / Zurück / Weiter) reagierten in manchen Themes nicht auf Klicks —
  Event-Listener werden jetzt per Delegation auf dem Lightbox-Container registriert statt direkt
  auf einzelnen Elementen, wodurch ein `TypeError` bei fehlenden IDs vermieden wird
- **Lightbox öffnet sich nicht** im Cyber-Theme: `z-index` von 1050 auf 9999 erhöht,
  `.is-open { display: flex !important }` verhindert Theme-Überschreibungen
- **„← Zurück zur Galerie"** ist jetzt mit `text-decoration: underline`, `opacity: .8` und
  `color: inherit` in allen Themes gut lesbar
- `album.php`: `Ui::section()` entfernt — der Album-Titel erscheint bereits im Theme-Seitentitel;
  der Wrapper erzeugte in manchen Themes Stacking-Context-Probleme

---

## [0.2.0] — 2026-06-05

### Geändert
- **Ui-Migration:** Alle Bootstrap-Komponenten durch `Esse\Ui`-Methoden ersetzt — Plugin funktioniert
  jetzt mit beliebigen Themes, die keine Bootstrap-Abhängigkeit voraussetzen
- Frontend `list.php`: Album-Grid via `Ui::grid()` (cols=4), leere Galerie via `Ui::emptyState()`
- Frontend `album.php`: Foto-Grid via `Ui::section()` + `Ui::grid()` (cols=6), leere Ansicht via `Ui::emptyState()`
- Admin `list.php`: Albentabelle via `Ui::panel()` + `Ui::table()`, Badges via `Ui::badge()`,
  Aktions-Buttons via `Ui::button()` — Bootstrap-Lösch-Modal durch direkten POST-Button mit `confirm()` ersetzt
- Admin `form.php`: Fehler-Alert via `Ui::alert()`, Formular-Container via `Ui::panel()`,
  Buttons via `Ui::button()` / `esse-btn`-Klassen
- Admin `images.php`: Bilder-Grid auf `esse-grid` umgestellt, Upload-Dropzone als `.gal-dropzone`,
  leere Ansicht via `Ui::emptyState()`, Buttons via `Ui::button()`
- Admin `image-card.php`: Bootstrap-Card entfernt — eigene Klassen `.gal-image-card`, `.gal-card-thumb`,
  `.gal-image-card-body`, `.gal-image-card-footer`; Cover-Badge via `Ui::badge()`;
  Aktions-Buttons als `<button type="button">` mit `esse-btn`-Klassen
- `gallery.js`: Bootstrap-Klassen durchgehend ersetzt (`bg-secondary` → `gal-dropzone--active`,
  Bootstrap-Progress → `.gal-progress-*`, Bootstrap-Alert → `.gal-upload-error`,
  `col-*` → `esse-grid-item`, `badge bg-warning` → `esse-badge--warning`)
- `gallery.css`: Neue Abschnitte für `.gal-dropzone`, `.gal-image-card`, `.gal-caption-input`,
  `.gal-progress-*`, `.gal-upload-error`
- `Plugin.php`: Icon-Namen ohne Pack-Prefix (`images` statt `bi-images`) für
  `registerPage()`, `addAdminNav()` und `renderFile()`; `renderFile()` erhält nun den Icon-Parameter

---

## [0.1.0] — 2026-06-03

### Geändert
- Bootstrap-Grid vollständig entfernt — Frontend nutzt jetzt Theme-agnostische `esse-grid`-Klassen
- Neue Plugin-CSS-Datei `assets/css/gallery.css` mit allen eigenen Styles (`.gal-*`)
- Lightbox-Overlay komplett neu ohne Bootstrap-Modal — eigene CSS-Klasse `.gal-lightbox.is-open`
- `gallery.js` ohne Bootstrap-Abhängigkeit (`bootstrap.Modal` entfernt)
- Bootstrap-Badges ersetzt durch `.gal-badge`, `.gal-badge-count`, `.gal-badge-private`
- CSS-Route `/plugins/esse-gallery/assets/css/{file}` hinzugefügt

---

## [0.0.1] — 2026-06-03

Erste öffentliche Version.

### Hinzugefügt
- Albumverwaltung im Admin: anlegen, bearbeiten, löschen
- Bild-Upload mit Drag & Drop und Fortschrittsanzeige (AJAX, Multi-File)
- Automatische Thumbnail-Generierung via PHP GD (300×300, Square-Crop)
- EXIF-Rotationskorrektur für JPEG-Uploads
- Thumbnails werden gecacht in `storage/gallery/thumbs/`
- Bilder werden sicher über PHP-Routen ausgeliefert (kein direkter Storage-Zugriff)
- Vanilla-JS-Lightbox im Frontend (Pfeiltasten, Touch-Swipe, Escape)
- Cover-Bild pro Album (automatisch beim ersten Upload, manuell änderbar)
- Bildunterschriften (Caption) inline bearbeitbar im Admin
- Sichtbarkeit pro Album: Öffentlich (alle) oder Privat (nur eingeloggte Mitglieder)
- Sortierfeld für Alben
- Automatische Slug-Generierung aus dem Albumtitel, manuell editierbar
- Seitentitel im Theme wird dynamisch auf den Albumtitel gesetzt
- `uninstall()` löscht alle Datenbanktabellen
