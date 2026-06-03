<?php

declare(strict_types=1);

use EsseGallery\GalleryRepository;
use EsseGallery\GalleryImage;

$img = GalleryRepository::imageById($imageId);
if (!$img) {
    http_response_code(404);
    exit;
}

$album = GalleryRepository::albumById((int) $img['album_id']);
if (!$album || (!$album['is_public'] && !\Esse\Auth::check())) {
    http_response_code(403);
    exit;
}

GalleryImage::serveThumb($img['filename'], 300);
