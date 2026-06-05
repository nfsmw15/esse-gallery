<?php
// Partial: wird von images.php und per AJAX (upload.php Response) genutzt.
// Erwartet $img (array) und $album (array) im Scope.
$isCover = (int) ($album['cover_image_id'] ?? 0) === (int) $img['id'];
?>
<div class="card border-0 bg-dark h-100 gal-card" data-img-id="<?= (int) $img['id'] ?>">
    <div class="position-relative" style="aspect-ratio:1/1; overflow:hidden;">
        <img src="/gallery/thumb/<?= (int) $img['id'] ?>"
             alt="<?= htmlspecialchars($img['original_name']) ?>"
             class="w-100 h-100 rounded-top"
             style="object-fit:cover; display:block;">
        <?php if ($isCover): ?>
            <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-1">
                <i class="bi bi-star-fill"></i> Cover
            </span>
        <?php endif; ?>
    </div>
    <div class="card-body p-2">
        <input type="text"
               class="form-control form-control-sm bg-transparent border-secondary text-light gal-caption-input"
               value="<?= htmlspecialchars($img['caption']) ?>"
               placeholder="Bildunterschrift…"
               data-img-id="<?= (int) $img['id'] ?>">
    </div>
    <div class="card-footer border-secondary p-1 d-flex gap-1">
        <button type="button"
                class="btn btn-sm btn-outline-warning flex-fill gal-btn-cover"
                data-img-id="<?= (int) $img['id'] ?>"
                title="Als Cover setzen">
            <i class="bi bi-star"></i>
        </button>
        <a href="/gallery/img/<?= (int) $img['id'] ?>"
           target="_blank"
           class="btn btn-sm btn-outline-secondary flex-fill"
           title="Original öffnen">
            <i class="bi bi-box-arrow-up-right"></i>
        </a>
        <button type="button"
                class="btn btn-sm btn-outline-danger flex-fill gal-btn-delete"
                data-img-id="<?= (int) $img['id'] ?>"
                title="Löschen">
            <i class="bi bi-trash"></i>
        </button>
    </div>
</div>
