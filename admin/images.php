<?php

declare(strict_types=1);

use Esse\Auth;
use Esse\Ui;
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

$topbarRight = Ui::button('Zurück', '/admin/gallery', [
                   'variant' => 'ghost',
                   'size'    => 'sm',
                   'icon'    => 'bi bi-arrow-left',
               ])
             . ' '
             . Ui::button('Album bearbeiten', '/admin/gallery/' . (int) $albumId . '/edit', [
                   'variant' => 'ghost',
                   'size'    => 'sm',
                   'icon'    => 'bi bi-pencil',
               ]);

ob_start();
?>
<!-- Upload-Zone -->
<div id="gal-dropzone"
     class="gal-dropzone"
     ondragover="event.preventDefault(); this.classList.add('gal-dropzone--active')"
     ondragleave="this.classList.remove('gal-dropzone--active')"
     ondrop="galHandleDrop(event)">
    <i class="bi bi-cloud-upload" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.6;"></i>
    <div style="font-weight:600;">Bilder hierher ziehen</div>
    <div style="font-size:.875rem;opacity:.6;">oder</div>
    <label class="esse-btn esse-btn--primary esse-btn--sm" style="margin-top:.5rem;cursor:pointer;">
        <i class="bi bi-folder2-open"></i> Dateien auswählen
        <input type="file" id="gal-file-input" multiple accept="image/*" style="display:none;">
    </label>
    <div style="font-size:.8rem;opacity:.5;margin-top:.35rem;">JPEG, PNG, GIF, WebP — max. 20 MB pro Bild</div>
</div>

<!-- Fortschrittsbereich -->
<div id="gal-progress-area" style="margin-bottom:1rem;"></div>

<!-- Bilder-Grid -->
<div class="esse-grid-wrap">
    <div id="gal-image-grid" class="esse-grid" data-cols="6">
        <?php foreach ($images as $img): ?>
            <div class="esse-grid-item" id="gal-img-<?= (int) $img['id'] ?>">
                <?php include __DIR__ . '/image-card.php'; ?>
            </div>
        <?php endforeach; ?>
        <?php if (empty($images)): ?>
            <div id="gal-empty-hint" style="grid-column:1/-1;">
                <?= Ui::emptyState('Noch keine Bilder', 'Bilder hochladen um zu starten.', ['icon' => 'bi bi-images']) ?>
            </div>
        <?php endif; ?>
    </div>
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
