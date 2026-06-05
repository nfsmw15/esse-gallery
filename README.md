# esse-gallery

Foto-Galerie-Plugin für das [ESSE CMS](https://github.com/nfsmw15/esse-cms).

## Features

- **Theme-agnostisch** — Ausgabe ausschließlich über `Esse\Ui`-Methoden; kein Bootstrap im Plugin-Output
- **Albumverwaltung** — Alben anlegen, bearbeiten, löschen
- **Drag & Drop Upload** — Mehrere Bilder gleichzeitig hochladen mit Fortschrittsanzeige
- **Automatische Thumbnails** — PHP GD, 300×300, Square-Crop, EXIF-Rotationskorrektur
- **Vanilla JS Lightbox** — Pfeiltasten, Touch-Swipe, Escape, kein externes Framework nötig
- **Cover-Bild** — Automatisch beim ersten Upload, manuell änderbar
- **Bildunterschriften** — Inline bearbeitbar im Admin
- **Sichtbarkeit** — Öffentlich für alle / Privat nur für eingeloggte Mitglieder
- **Slugs** — Auto-generiert aus Titel, manuell editierbar
- **Sortierung** — Alben per Sortierfeld sortierbar

## Anforderungen

- PHP 8.1+
- PHP GD Extension (`ext-gd`)
- ESSE CMS >= 0.2.0 (wegen `Esse\Ui`-Klasse)

## Installation

1. Plugin-Verzeichnis als `esse-gallery` in `plugins/` ablegen  
   **oder** als ZIP über Admin → Plugins hochladen
2. Plugin in Admin → Plugins aktivieren
3. Speicherverzeichnisse werden automatisch angelegt:
   - `storage/gallery/originals/`
   - `storage/gallery/thumbs/`

## Routen

| Route | Beschreibung |
|-------|-------------|
| `/gallery` | Öffentliche Galerie-Übersicht |
| `/gallery/{slug}` | Einzelalbum mit Lightbox |
| `/gallery/img/{id}` | Originalbild ausliefern |
| `/gallery/thumb/{id}` | Thumbnail ausliefern (300×300) |
| `/admin/gallery` | Admin: Alben-Liste |
| `/admin/gallery/create` | Admin: Album anlegen |
| `/admin/gallery/{id}/edit` | Admin: Album bearbeiten |
| `/admin/gallery/{id}/images` | Admin: Bilder verwalten & hochladen |

## Sichtbarkeit

| Album-Einstellung | Nicht eingeloggt | Eingeloggt (Mitglied+) |
|---|---|---|
| Öffentlich | ✅ sichtbar | ✅ sichtbar |
| Privat | ❌ nicht sichtbar | ✅ sichtbar |

## Datenbankstruktur

Das Plugin legt zwei Tabellen an (Prefix gemäß CMS-Konfiguration):

- `esse_gallery_albums` — Alben (Titel, Slug, Beschreibung, Cover, Sichtbarkeit)
- `esse_gallery_images` — Bilder (Album-Zuordnung, Dateiname, Caption, Reihenfolge)

Bilder werden in `storage/gallery/` abgelegt und ausschließlich über PHP-Routen ausgeliefert — kein direkter Dateizugriff möglich.

## Lizenz

AGPL-3.0-or-later — siehe [LICENSE](LICENSE)

Copyright (C) 2026 Andreas P. — [nfsmw15.de](https://nfsmw15.de)
