<?php

declare(strict_types=1);

namespace EsseGallery;

use Esse\Router;

require_once __DIR__ . '/GalleryRepository.php';
require_once __DIR__ . '/GalleryImage.php';

class Plugin extends \Esse\Plugin
{
    public function boot(): void
    {
        GalleryRepository::migrate();

        $this->addAdminNav('Galerie', '/admin/gallery', 'images', 'admin.gallery');
        $this->registerPage('/gallery',        'Galerie',        'images');
        $this->registerPage('/gallery/{slug}', 'Galerie-Album',  'images');

        $base = $this->basePath();

        // --- Plugin-Assets ---
        Router::get('/plugins/esse-gallery/assets/{file}', function (string $file) {
            $path = $this->basePath('assets/' . basename($file));
            if (!file_exists($path)) { http_response_code(404); exit; }
            $mime = mime_content_type($path) ?: 'application/octet-stream';
            header("Content-Type: {$mime}");
            readfile($path);
        }, ['name' => 'gallery.assets', 'auth' => 'public']);

        Router::get('/plugins/esse-gallery/assets/css/{file}', function (string $file) {
            $path = $this->basePath('assets/css/' . basename($file));
            if (!file_exists($path)) { http_response_code(404); exit; }
            header('Content-Type: text/css');
            readfile($path);
        }, ['name' => 'gallery.assets.css', 'auth' => 'public']);

        // --- Bild-Serving (public) ---
        Router::get('/gallery/img/{id}', function (string $id) use ($base) {
            $imageId = (int) $id;
            require "{$base}/frontend/serve-image.php";
        }, ['name' => 'gallery.img', 'auth' => 'public']);

        Router::get('/gallery/thumb/{id}', function (string $id) use ($base) {
            $imageId = (int) $id;
            require "{$base}/frontend/serve-thumb.php";
        }, ['name' => 'gallery.thumb', 'auth' => 'public']);

        // --- Frontend ---
        Router::get('/gallery', function () use ($base) {
            \Esse\PageRenderer::renderFile("{$base}/frontend/list.php", 'Galerie', 'public', 'images');
        }, ['name' => 'gallery.list', 'auth' => 'public']);

        Router::get('/gallery/{slug}', function (string $slug) use ($base) {
            $album = GalleryRepository::albumBySlug($slug);
            $title = ($album && ($album['is_public'] || \Esse\Auth::check()))
                ? $album['title']
                : 'Galerie';
            \Esse\PageRenderer::renderFile("{$base}/frontend/album.php", $title, 'public', 'images');
        }, ['name' => 'gallery.album', 'auth' => 'public']);

        // --- Admin: Alben ---
        Router::get('/admin/gallery', fn () => require "{$base}/admin/list.php",
            ['name' => 'admin.gallery', 'auth' => 'admin']);

        Router::get('/admin/gallery/create', fn () => require "{$base}/admin/form.php",
            ['name' => 'admin.gallery.create', 'auth' => 'admin']);

        Router::post('/admin/gallery/create', fn () => require "{$base}/admin/form.php",
            ['name' => 'admin.gallery.create.post', 'auth' => 'admin']);

        Router::get('/admin/gallery/{id}/edit', function (string $id) use ($base) {
            $albumId = (int) $id;
            require "{$base}/admin/form.php";
        }, ['name' => 'admin.gallery.edit', 'auth' => 'admin']);

        Router::post('/admin/gallery/{id}/edit', function (string $id) use ($base) {
            $albumId = (int) $id;
            require "{$base}/admin/form.php";
        }, ['name' => 'admin.gallery.edit.post', 'auth' => 'admin']);

        Router::post('/admin/gallery/{id}/delete', function (string $id) use ($base) {
            $albumId = (int) $id;
            require "{$base}/admin/delete-album.php";
        }, ['name' => 'admin.gallery.delete', 'auth' => 'admin']);

        // --- Admin: Bilder ---
        Router::get('/admin/gallery/{id}/images', function (string $id) use ($base) {
            $albumId = (int) $id;
            require "{$base}/admin/images.php";
        }, ['name' => 'admin.gallery.images', 'auth' => 'admin']);

        Router::post('/admin/gallery/{id}/images/upload', function (string $id) use ($base) {
            $albumId = (int) $id;
            require "{$base}/admin/upload.php";
        }, ['name' => 'admin.gallery.upload', 'auth' => 'admin']);

        Router::post('/admin/gallery/images/{imgId}/caption', function (string $imgId) use ($base) {
            $imageId = (int) $imgId;
            require "{$base}/admin/update-caption.php";
        }, ['name' => 'admin.gallery.caption', 'auth' => 'admin']);

        Router::post('/admin/gallery/images/{imgId}/delete', function (string $imgId) use ($base) {
            $imageId = (int) $imgId;
            require "{$base}/admin/delete-image.php";
        }, ['name' => 'admin.gallery.image.delete', 'auth' => 'admin']);

        Router::post('/admin/gallery/images/{imgId}/cover', function (string $imgId) use ($base) {
            $imageId = (int) $imgId;
            require "{$base}/admin/set-cover.php";
        }, ['name' => 'admin.gallery.cover', 'auth' => 'admin']);
    }

    public function install(): void
    {
        // Speicher-Verzeichnisse anlegen
        $storageBase = ESSE_ROOT . '/storage/gallery';
        foreach ([$storageBase, $storageBase . '/originals', $storageBase . '/thumbs'] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    public function uninstall(): void
    {
        GalleryRepository::drop();
    }
}
