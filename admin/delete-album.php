<?php

declare(strict_types=1);

use Esse\Auth;
use EsseGallery\GalleryRepository;
use EsseGallery\GalleryImage;

if (!Auth::verifyCsrf()) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Ungültiges CSRF-Token.'];
    header('Location: /admin/gallery');
    exit;
}

$album = GalleryRepository::albumById($albumId);
if (!$album) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Album nicht gefunden.'];
    header('Location: /admin/gallery');
    exit;
}

$images = GalleryRepository::imagesByAlbum($albumId);
foreach ($images as $img) {
    GalleryImage::delete($img['filename']);
    GalleryRepository::deleteImage((int) $img['id']);
}

GalleryRepository::deleteAlbum($albumId);

$_SESSION['flash'] = ['type' => 'success', 'message' => 'Album gelöscht.'];
header('Location: /admin/gallery');
exit;
