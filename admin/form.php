<?php

declare(strict_types=1);

use Esse\Auth;
use EsseGallery\GalleryRepository;

$isEdit  = isset($albumId);
$album   = $isEdit ? GalleryRepository::albumById($albumId) : null;

if ($isEdit && !$album) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Album nicht gefunden.'];
    header('Location: /admin/gallery');
    exit;
}

$errors = [];
$values = [
    'title'       => $album['title']       ?? '',
    'slug'        => $album['slug']        ?? '',
    'description' => $album['description'] ?? '',
    'is_public'   => $album['is_public']   ?? 1,
    'sort_order'  => $album['sort_order']  ?? 0,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf()) {
        $errors[] = 'Ungültiges CSRF-Token.';
    } else {
        $values['title']       = trim($_POST['title'] ?? '');
        $values['slug']        = trim($_POST['slug'] ?? '');
        $values['description'] = trim($_POST['description'] ?? '');
        $values['is_public']   = isset($_POST['is_public']) ? 1 : 0;
        $values['sort_order']  = (int) ($_POST['sort_order'] ?? 0);

        if ($values['title'] === '') {
            $errors[] = 'Titel ist erforderlich.';
        }

        if ($values['slug'] === '') {
            $values['slug'] = GalleryRepository::slugify($values['title']);
        }

        if (empty($errors)) {
            if ($isEdit) {
                GalleryRepository::updateAlbum($albumId, $values);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Album gespeichert.'];
                header('Location: /admin/gallery');
            } else {
                $newId = GalleryRepository::createAlbum($values);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Album angelegt.'];
                header('Location: /admin/gallery/' . $newId . '/images');
            }
            exit;
        }
    }
}

$flash = null;
if (!empty($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$pageTitle = $isEdit ? 'Album bearbeiten' : 'Neues Album';
$activeNav = 'admin.gallery';

$topbarRight = '<a href="/admin/gallery" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Zurück
</a>';

ob_start();
?>
<?php if ($errors): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width:640px;">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Auth::csrfToken()) ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold">Titel <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control"
                       value="<?= htmlspecialchars($values['title']) ?>"
                       id="albumTitle" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Slug</label>
                <input type="text" name="slug" class="form-control font-monospace"
                       value="<?= htmlspecialchars($values['slug']) ?>"
                       id="albumSlug"
                       placeholder="wird automatisch aus dem Titel generiert">
                <div class="form-text">Wird für die URL verwendet: /gallery/<strong id="slugPreview"><?= htmlspecialchars($values['slug']) ?></strong></div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Beschreibung</label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Optional"><?= htmlspecialchars($values['description']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Sortierung</label>
                <input type="number" name="sort_order" class="form-control" style="max-width:120px;"
                       value="<?= (int) $values['sort_order'] ?>">
                <div class="form-text">Niedrigere Zahl = weiter oben.</div>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_public" id="isPublic"
                           <?= $values['is_public'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="isPublic">Öffentlich sichtbar</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> <?= $isEdit ? 'Speichern' : 'Album anlegen' ?>
                </button>
                <a href="/admin/gallery" class="btn btn-outline-secondary">Abbrechen</a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();

$extraScripts = '<script>
(function() {
    function slugify(str) {
        var map = {"ä":"ae","ö":"oe","ü":"ue","ß":"ss","Ä":"ae","Ö":"oe","Ü":"ue"};
        str = str.replace(/[äöüßÄÖÜ]/g, function(m){ return map[m] || m; });
        return str.toLowerCase().trim()
            .replace(/[^a-z0-9\s-]/g, "")
            .replace(/[\s-]+/g, "-")
            .replace(/^-+|-+$/g, "");
    }

    var titleEl   = document.getElementById("albumTitle");
    var slugEl    = document.getElementById("albumSlug");
    var previewEl = document.getElementById("slugPreview");
    var slugDirty = slugEl.value !== "";

    slugEl.addEventListener("input", function() {
        slugDirty = true;
        previewEl.textContent = slugEl.value || "(leer)";
    });

    titleEl.addEventListener("input", function() {
        if (!slugDirty) {
            var s = slugify(titleEl.value);
            slugEl.value = s;
            previewEl.textContent = s || "(leer)";
        }
    });
})();
</script>';

require ESSE_ROOT . '/admin/layout.php';
