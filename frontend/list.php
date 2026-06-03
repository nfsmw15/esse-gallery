<?php

declare(strict_types=1);

use EsseGallery\GalleryRepository;

$isLoggedIn = \Esse\Auth::check();
$albums     = $isLoggedIn ? GalleryRepository::allAlbums() : GalleryRepository::publicAlbums();
?>
<link rel="stylesheet" href="/plugins/esse-gallery/assets/css/gallery.css">

<section class="gal-overview">
    <div class="esse-grid-wrap">

        <?php if (empty($albums)): ?>
            <p style="text-align:center; padding:3rem 0; opacity:.5;">Die Galerie ist noch leer.</p>
        <?php else: ?>
            <div class="esse-grid" data-cols="4">
                <?php foreach ($albums as $album): ?>
                    <div class="esse-grid-item">
                        <a href="/gallery/<?= htmlspecialchars($album['slug']) ?>"
                           class="gal-album-card">
                            <div class="gal-album-thumb">
                                <?php if ($album['cover_image_id']): ?>
                                    <img src="/gallery/thumb/<?= (int) $album['cover_image_id'] ?>"
                                         alt="<?= htmlspecialchars($album['title']) ?>">
                                <?php endif; ?>
                                <span class="gal-badge gal-badge-count">
                                    <?= (int) $album['image_count'] ?>
                                </span>
                                <?php if ($isLoggedIn && !$album['is_public']): ?>
                                    <span class="gal-badge gal-badge-private">Privat</span>
                                <?php endif; ?>
                            </div>
                            <div class="gal-album-title"><?= htmlspecialchars($album['title']) ?></div>
                            <?php if ($album['description']): ?>
                                <div class="gal-album-desc"><?= htmlspecialchars($album['description']) ?></div>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
