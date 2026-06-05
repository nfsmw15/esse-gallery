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
                                        <img src="/gallery/thumb/<?= (int) $album['cover_image_id'] ?>"
                                             alt="" class="rounded"
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
                                <td><code class="small"><?= htmlspecialchars($album['slug']) ?></code></td>
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
                                    <form method="POST"
                                          action="/admin/gallery/<?= (int) $album['id'] ?>/delete"
                                          class="d-inline"
                                          onsubmit="return confirm('Album und alle Bilder unwiderruflich löschen?')">
                                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Auth::csrfToken()) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Löschen">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();

require ESSE_ROOT . '/admin/layout.php';
