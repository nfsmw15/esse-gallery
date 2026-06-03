# Changelog — esse-gallery

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
