<?php

declare(strict_types=1);

use Esse\Auth;
use EsseGallery\GalleryRepository;

$album = GalleryRepository::albumById($albumId);

if (!$album) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Album nicht gefunden.'];
    header('Location: /admin/gallery');
    exit;
}

$flash = null;
if (!empty($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$images    = GalleryRepository::imagesByAlbum($albumId);
$pageTitle = htmlspecialchars($album['title']) . ' — Bilder';
$activeNav = 'admin.gallery';

$topbarRight = '<a href="/admin/gallery" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Zurück
</a>
<a href="/admin/gallery/' . (int) $albumId . '/edit" class="btn btn-outline-primary btn-sm ms-2">
    <i class="bi bi-pencil"></i> Album bearbeiten
</a>';

ob_start();
?>
<!-- Upload-Zone -->
<div id="gal-dropzone"
     class="border border-dashed rounded-3 p-5 text-center mb-4 position-relative"
     style="border-color:#444 !important; cursor:pointer; transition:background .2s;"
     ondragover="event.preventDefault(); this.classList.add('bg-secondary')"
     ondragleave="this.classList.remove('bg-secondary')"
     ondrop="galHandleDrop(event)">
    <i class="bi bi-cloud-upload fs-1 text-muted d-block mb-2"></i>
    <div class="fw-semibold">Bilder hierher ziehen</div>
    <div class="text-muted small">oder</div>
    <label class="btn btn-sm btn-primary mt-2">
        <i class="bi bi-folder2-open"></i> Dateien auswählen
        <input type="file" id="gal-file-input" multiple accept="image/*" class="d-none">
    </label>
    <div class="form-text mt-1">JPEG, PNG, GIF, WebP — max. 20 MB pro Bild</div>
</div>

<!-- Fortschrittsbereich -->
<div id="gal-progress-area" class="mb-4"></div>

<!-- Bilder-Grid -->
<div id="gal-image-grid" class="row g-3">
    <?php foreach ($images as $img): ?>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2" id="gal-img-<?= (int) $img['id'] ?>">
            <?php include __DIR__ . '/image-card.php'; ?>
        </div>
    <?php endforeach; ?>
    <?php if (empty($images)): ?>
        <div class="col-12 text-center text-muted py-4" id="gal-empty-hint">
            <i class="bi bi-images opacity-50"></i> Noch keine Bilder — Bilder hochladen um zu starten.
        </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();

$extraScripts = '<script src="/plugins/esse-gallery/assets/gallery.js"></script>
<script>
var GAL_ALBUM_ID   = ' . (int) $albumId . ';
var GAL_CSRF       = ' . json_encode(Auth::csrfToken()) . ';
var GAL_COVER_ID   = ' . (int) ($album['cover_image_id'] ?? 0) . ';
var GAL_UPLOAD_URL = "/admin/gallery/' . (int) $albumId . '/images/upload";
</script>
<script>
document.addEventListener("DOMContentLoaded", function() { galAdminInit(); });
</script>';

require ESSE_ROOT . '/admin/layout.php';
