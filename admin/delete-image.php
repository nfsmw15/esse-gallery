<?php

declare(strict_types=1);

use Esse\Auth;
use EsseGallery\GalleryRepository;
use EsseGallery\GalleryImage;

header('Content-Type: application/json');

if (!Auth::verifyCsrf()) {
    echo json_encode(['error' => 'Ungültiges CSRF-Token.']);
    exit;
}

$img = GalleryRepository::imageById($imageId);
if (!$img) {
    http_response_code(404);
    echo json_encode(['error' => 'Bild nicht gefunden.']);
    exit;
}

// Cover-Referenz entfernen falls nötig
$album = GalleryRepository::albumById((int) $img['album_id']);
if ($album && (int) $album['cover_image_id'] === $imageId) {
    // Nächstes Bild als Cover setzen
    $siblings = GalleryRepository::imagesByAlbum((int) $img['album_id']);
    $next     = null;
    foreach ($siblings as $s) {
        if ((int) $s['id'] !== $imageId) {
            $next = (int) $s['id'];
            break;
        }
    }
    GalleryRepository::setCover((int) $img['album_id'], $next);
}

GalleryImage::delete($img['filename']);
GalleryRepository::deleteImage($imageId);

echo json_encode(['success' => true]);
exit;
