<?php

declare(strict_types=1);

use Esse\Auth;
use EsseGallery\GalleryRepository;

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

GalleryRepository::setCover((int) $img['album_id'], $imageId);

echo json_encode(['success' => true]);
exit;
