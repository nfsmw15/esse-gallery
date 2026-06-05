<?php
// Partial: wird von images.php und per AJAX (upload.php Response) genutzt.
// Erwartet $img (array) und $album (array) im Scope.
use Esse\Ui;
$isCover = (int) ($album['cover_image_id'] ?? 0) === (int) $img['id'];
?>
<div class="gal-image-card" data-img-id="<?= (int) $img['id'] ?>">
    <div class="gal-card-thumb" style="aspect-ratio:1/1;overflow:hidden;position:relative;">
        <img src="/gallery/thumb/<?= (int) $img['id'] ?>"
             alt="<?= htmlspecialchars($img['original_name']) ?>"
             style="width:100%;height:100%;object-fit:cover;display:block;">
        <?php if ($isCover): ?>
            <?= Ui::badge('★ Cover', 'warning') ?>
        <?php endif; ?>
    </div>
    <div class="gal-image-card-body">
        <input type="text"
               class="gal-caption-input"
               value="<?= htmlspecialchars($img['caption']) ?>"
               placeholder="Bildunterschrift…"
               data-img-id="<?= (int) $img['id'] ?>">
    </div>
    <div class="gal-image-card-footer">
        <button type="button"
                class="esse-btn esse-btn--ghost esse-btn--sm gal-btn-cover"
                data-img-id="<?= (int) $img['id'] ?>"
                title="Als Cover setzen">
            <i class="bi bi-star"></i>
        </button>
        <a href="/gallery/img/<?= (int) $img['id'] ?>"
           target="_blank"
           class="esse-btn esse-btn--ghost esse-btn--sm"
           title="Original öffnen">
            <i class="bi bi-box-arrow-up-right"></i>
        </a>
        <button type="button"
                class="esse-btn esse-btn--danger esse-btn--sm gal-btn-delete"
                data-img-id="<?= (int) $img['id'] ?>"
                title="Löschen">
            <i class="bi bi-trash"></i>
        </button>
    </div>
</div>
