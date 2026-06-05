<?php

declare(strict_types=1);

use Esse\Ui;
use EsseGallery\GalleryRepository;

$slug  = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$album = GalleryRepository::albumBySlug($slug);

if (!$album) {
    \Esse\Router::abort(404);
    return;
}

if (!$album['is_public'] && !\Esse\Auth::check()) {
    \Esse\Router::abort(404);
    return;
}

$images = GalleryRepository::imagesByAlbum((int) $album['id']);
?>
<link rel="stylesheet" href="/plugins/esse-gallery/assets/css/gallery.css">

<a href="/gallery" class="gal-back-link">&#8592; Zurück zur Galerie</a>

<?php if ($album['description']): ?>
    <p style="opacity:.65; margin-bottom:1.5rem;"><?= htmlspecialchars($album['description']) ?></p>
<?php endif; ?>

<?php if (empty($images)): ?>
    <?= Ui::emptyState('Noch keine Bilder', 'Dieses Album enthält noch keine Bilder.', ['icon' => 'bi bi-images']) ?>
<?php else: ?>
    <?php
    $items = [];
    foreach ($images as $i => $img) {
        ob_start(); ?>
        <a href="/gallery/img/<?= (int) $img['id'] ?>"
           class="gal-thumb-link"
           data-index="<?= $i ?>"
           data-caption="<?= htmlspecialchars($img['caption'], ENT_QUOTES) ?>">
            <img src="/gallery/thumb/<?= (int) $img['id'] ?>"
                 alt="<?= htmlspecialchars($img['caption'] ?: $img['original_name']) ?>"
                 class="gal-thumb-img"
                 loading="lazy">
        </a>
        <?php $items[] = ob_get_clean();
    }
    echo Ui::section($album['title'], Ui::grid($items, ['cols' => 6]), []);
    ?>
<?php endif; ?>

<!-- Lightbox -->
<div id="gal-lightbox" class="gal-lightbox" role="dialog" aria-modal="true">
    <div class="gal-lb-header">
        <span id="gal-lb-counter" class="gal-lb-counter"></span>
        <button type="button" id="gal-lb-close" class="gal-lb-close" aria-label="Schließen">&times;</button>
    </div>
    <div class="gal-lb-body">
        <button id="gal-lb-prev" class="gal-lb-prev" aria-label="Vorheriges Bild">&#10094;</button>
        <img id="gal-lb-img" src="" alt="">
        <button id="gal-lb-next" class="gal-lb-next" aria-label="Nächstes Bild">&#10095;</button>
    </div>
    <div class="gal-lb-footer">
        <p id="gal-lb-title" class="gal-lb-title"></p>
    </div>
</div>

<script src="/plugins/esse-gallery/assets/gallery.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() { galLightboxInit(); });
</script>
