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

$album = GalleryRepository::albumById($albumId);
if (!$album) {
    http_response_code(404);
    echo json_encode(['error' => 'Album nicht gefunden.']);
    exit;
}

if (empty($_FILES['file'])) {
    echo json_encode(['error' => 'Keine Datei empfangen.']);
    exit;
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $msg = match ($file['error']) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Datei zu groß.',
        UPLOAD_ERR_PARTIAL                         => 'Upload unvollständig.',
        default                                    => 'Upload-Fehler.',
    };
    echo json_encode(['error' => $msg]);
    exit;
}

if ($file['size'] > 20 * 1024 * 1024) {
    echo json_encode(['error' => 'Datei größer als 20 MB.']);
    exit;
}

$filename = GalleryImage::store($file);
if (!$filename) {
    echo json_encode(['error' => 'Ungültiger Dateityp. Erlaubt: JPEG, PNG, GIF, WebP.']);
    exit;
}

$originalName = htmlspecialchars_decode(
    basename($file['name']),
    ENT_QUOTES
);

$imageId = GalleryRepository::addImage($albumId, $filename, $originalName);
$img     = GalleryRepository::imageById($imageId);

// HTML-Partial für die Karte direkt zurückgeben
ob_start();
include __DIR__ . '/image-card.php';
$cardHtml = ob_get_clean();

echo json_encode([
    'success'  => true,
    'image_id' => $imageId,
    'html'     => $cardHtml,
]);
exit;
