<?php

declare(strict_types=1);

use EsseGallery\GalleryRepository;

$isLoggedIn = \Esse\Auth::check();
$albums     = $isLoggedIn ? GalleryRepository::allAlbums() : GalleryRepository::publicAlbums();

// Das Theme rendert diesen Inhalt — kein volles HTML nötig, nur Content.
// Das CMS wickelt den Output in das aktive Theme ein.
?>
<section class="gal-overview py-4">
    <div class="container">

        <?php if (empty($albums)): ?>
            <p class="text-muted text-center py-5">Die Galerie ist noch leer.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($albums as $album): ?>
                    <div class="col-6 col-sm-4 col-md-3">
                        <a href="/gallery/<?= htmlspecialchars($album['slug']) ?>"
                           class="gal-album-card text-decoration-none d-block">
                            <div class="gal-album-thumb rounded overflow-hidden position-relative mb-2"
                                 style="aspect-ratio:1/1; background:#1a1a1a;">
                                <?php if ($album['cover_image_id']): ?>
                                    <img src="/gallery/thumb/<?= (int) $album['cover_image_id'] ?>"
                                         alt="<?= htmlspecialchars($album['title']) ?>"
                                         class="w-100 h-100"
                                         style="object-fit:cover; display:block; transition:transform .3s;">
                                <?php else: ?>
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                        <i class="bi bi-images fs-1 opacity-25"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="badge bg-dark bg-opacity-75 position-absolute bottom-0 end-0 m-2">
                                    <?= (int) $album['image_count'] ?> <i class="bi bi-image"></i>
                                </span>
                                <?php if ($isLoggedIn && !$album['is_public']): ?>
                                    <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-1">
                                        <i class="bi bi-eye-slash"></i> Privat
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="fw-semibold text-truncate"><?= htmlspecialchars($album['title']) ?></div>
                            <?php if ($album['description']): ?>
                                <div class="small text-muted text-truncate">
                                    <?= htmlspecialchars($album['description']) ?>
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<style>
.gal-album-card:hover .gal-album-thumb img { transform: scale(1.05); }
</style>
