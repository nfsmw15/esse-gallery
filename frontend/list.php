<?php

declare(strict_types=1);

use Esse\Ui;
use EsseGallery\GalleryRepository;

$isLoggedIn = \Esse\Auth::check();
$albums     = $isLoggedIn ? GalleryRepository::allAlbums() : GalleryRepository::publicAlbums();
?>
<link rel="stylesheet" href="/plugins/esse-gallery/assets/css/gallery.css">

<section class="gal-overview">
<?php if (empty($albums)): ?>
    <?= Ui::emptyState('Noch keine Alben', 'Die Galerie ist noch leer.', ['icon' => 'bi bi-images']) ?>
<?php else: ?>
    <?php
    $items = [];
    foreach ($albums as $album) {
        ob_start(); ?>
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
        <?php $items[] = ob_get_clean();
    }
    echo Ui::grid($items, ['cols' => 4]);
    ?>
<?php endif; ?>
</section>
