<?php

declare(strict_types=1);

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
<section class="gal-album py-4">
    <div class="container">

        <a href="/gallery" class="text-muted text-decoration-none small d-inline-block mb-4">
            <i class="bi bi-arrow-left"></i> Zurück zur Galerie
        </a>

        <?php if ($album['description']): ?>
            <p class="text-muted mb-4"><?= htmlspecialchars($album['description']) ?></p>
        <?php endif; ?>

        <?php if (empty($images)): ?>
            <p class="text-muted py-5 text-center">Dieses Album enthält noch keine Bilder.</p>
        <?php else: ?>
            <div class="row g-2" id="gal-grid">
                <?php foreach ($images as $i => $img): ?>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <a href="/gallery/img/<?= (int) $img['id'] ?>"
                           class="gal-thumb-link d-block rounded overflow-hidden"
                           style="aspect-ratio:1/1; background:#111;"
                           data-index="<?= $i ?>"
                           data-caption="<?= htmlspecialchars($img['caption'], ENT_QUOTES) ?>">
                            <img src="/gallery/thumb/<?= (int) $img['id'] ?>"
                                 alt="<?= htmlspecialchars($img['caption'] ?: $img['original_name']) ?>"
                                 class="w-100 h-100 gal-thumb-img"
                                 loading="lazy"
                                 style="object-fit:cover; display:block; transition:transform .25s;">
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- Lightbox -->
<div id="gal-lightbox" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-md-down" style="max-width:90vw;">
        <div class="modal-content bg-black border-0">
            <div class="modal-header border-0 py-2 px-3">
                <span id="gal-lb-counter" class="text-muted small"></span>
                <button type="button" id="gal-lb-close" class="btn-close btn-close-white ms-auto" aria-label="Schließen"></button>
            </div>
            <div class="modal-body p-0 text-center position-relative" style="min-height:60vh;">
                <button id="gal-lb-prev"
                        class="position-absolute start-0 top-50 translate-middle-y"
                        style="z-index:10; background:rgba(0,0,0,.5); border:none; color:#fff; font-size:2rem; line-height:1; padding:.4rem .8rem; border-radius:4px; cursor:pointer;"
                        aria-label="Vorheriges Bild">&#10094;</button>
                <img id="gal-lb-img"
                     src=""
                     alt=""
                     class="img-fluid"
                     style="max-height:80vh; object-fit:contain;">
                <button id="gal-lb-next"
                        class="position-absolute end-0 top-50 translate-middle-y"
                        style="z-index:10; background:rgba(0,0,0,.5); border:none; color:#fff; font-size:2rem; line-height:1; padding:.4rem .8rem; border-radius:4px; cursor:pointer;"
                        aria-label="Nächstes Bild">&#10095;</button>
            </div>
            <div class="modal-footer border-0 py-2 justify-content-center">
                <p id="gal-lb-title" class="text-muted small mb-0"></p>
            </div>
        </div>
    </div>
</div>

<style>
.gal-thumb-link:hover .gal-thumb-img { transform: scale(1.08); }
</style>

<script src="/plugins/esse-gallery/assets/gallery.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() { galLightboxInit(); });
</script>
