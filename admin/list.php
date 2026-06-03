<?php

declare(strict_types=1);

use Esse\Auth;
use EsseGallery\GalleryRepository;

$flash = null;
if (!empty($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$pageTitle = 'Galerie';
$activeNav = 'admin.gallery';

$albums = GalleryRepository::allAlbums();

$topbarRight = '<a href="/admin/gallery/create" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Neues Album
</a>';

ob_start();
?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($albums)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-images fs-1 d-block mb-3 opacity-50"></i>
                Noch keine Alben vorhanden.
                <a href="/admin/gallery/create" class="d-block mt-2">Erstes Album anlegen</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:60px"></th>
                            <th>Titel</th>
                            <th>Slug</th>
                            <th class="text-center" style="width:90px">Bilder</th>
                            <th class="text-center" style="width:90px">Sichtbar</th>
                            <th style="width:160px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($albums as $album): ?>
                            <tr>
                                <td class="ps-3">
                                    <?php if ($album['cover_image_id']): ?>
                                        <img
                                            src="/gallery/thumb/<?= (int) $album['cover_image_id'] ?>"
                                            alt=""
                                            class="rounded"
                                            style="width:48px;height:48px;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="rounded bg-secondary d-flex align-items-center justify-content-center"
                                             style="width:48px;height:48px;">
                                            <i class="bi bi-images text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/admin/gallery/<?= (int) $album['id'] ?>/images"
                                       class="fw-semibold text-decoration-none">
                                        <?= htmlspecialchars($album['title']) ?>
                                    </a>
                                    <?php if ($album['description']): ?>
                                        <div class="text-muted small text-truncate" style="max-width:300px;">
                                            <?= htmlspecialchars($album['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code class="small"><?= htmlspecialchars($album['slug']) ?></code>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= (int) $album['image_count'] ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($album['is_public']): ?>
                                        <span class="badge bg-success">Ja</span>
                                    <?php else: ?>
                                        <span class="badge bg-dark">Nein</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="/admin/gallery/<?= (int) $album['id'] ?>/images"
                                       class="btn btn-sm btn-outline-secondary me-1"
                                       title="Bilder verwalten">
                                        <i class="bi bi-images"></i>
                                    </a>
                                    <a href="/admin/gallery/<?= (int) $album['id'] ?>/edit"
                                       class="btn btn-sm btn-outline-primary me-1"
                                       title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Löschen"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal"
                                            data-album-id="<?= (int) $album['id'] ?>"
                                            data-album-title="<?= htmlspecialchars($album['title'], ENT_QUOTES) ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Lösch-Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Album löschen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Album <strong id="deleteModalTitle"></strong> und alle darin enthaltenen Bilder unwiderruflich löschen?
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Abbrechen</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Auth::csrfToken()) ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Löschen</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

$extraScripts = '<script>
document.getElementById("deleteModal").addEventListener("show.bs.modal", function(e) {
    var btn = e.relatedTarget;
    document.getElementById("deleteModalTitle").textContent = btn.dataset.albumTitle;
    document.getElementById("deleteForm").action = "/admin/gallery/" + btn.dataset.albumId + "/delete";
});
</script>';

require ESSE_ROOT . '/admin/layout.php';
