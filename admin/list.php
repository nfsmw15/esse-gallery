<?php

declare(strict_types=1);

use Esse\Ui;
use EsseGallery\GalleryRepository;

$flash = null;
if (!empty($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$pageTitle = 'Galerie';
$activeNav = 'admin.gallery';

$albums = GalleryRepository::allAlbums();

$topbarRight = Ui::button('Neues Album', '/admin/gallery/create', [
    'icon' => 'bi bi-plus-lg',
    'size' => 'sm',
]);

ob_start();

if (empty($albums)) {
    echo Ui::emptyState(
        'Noch keine Alben vorhanden.',
        '',
        [
            'icon'   => 'bi bi-images',
            'action' => Ui::button('Erstes Album anlegen', '/admin/gallery/create'),
        ]
    );
} else {
    $headers = ['', 'Titel', 'Slug', 'Bilder', 'Sichtbar', ''];
    $rows    = [];

    foreach ($albums as $album) {
        if ($album['cover_image_id']) {
            $thumb = '<img src="/gallery/thumb/' . (int) $album['cover_image_id'] . '" alt=""'
                   . ' style="width:48px;height:48px;object-fit:cover;border-radius:4px;">';
        } else {
            $thumb = '<div style="width:48px;height:48px;background:#555;border-radius:4px;'
                   . 'display:flex;align-items:center;justify-content:center;">'
                   . '<i class="bi bi-images"></i></div>';
        }

        $titleHtml = '<a href="/admin/gallery/' . (int) $album['id'] . '/images"'
                   . ' style="font-weight:600;text-decoration:none;">'
                   . htmlspecialchars($album['title']) . '</a>';
        if ($album['description']) {
            $titleHtml .= '<div style="font-size:.85em;opacity:.6;overflow:hidden;'
                        . 'text-overflow:ellipsis;white-space:nowrap;max-width:280px;">'
                        . htmlspecialchars($album['description']) . '</div>';
        }

        $actions = Ui::button('', '/admin/gallery/' . (int) $album['id'] . '/images', [
                       'variant' => 'ghost',
                       'size'    => 'sm',
                       'icon'    => 'bi bi-images',
                       'attr'    => ['title' => 'Bilder verwalten'],
                   ])
                 . Ui::button('', '/admin/gallery/' . (int) $album['id'] . '/edit', [
                       'variant' => 'ghost',
                       'size'    => 'sm',
                       'icon'    => 'bi bi-pencil',
                       'attr'    => ['title' => 'Bearbeiten'],
                   ])
                 . Ui::button('', '/admin/gallery/' . (int) $album['id'] . '/delete', [
                       'variant' => 'danger',
                       'size'    => 'sm',
                       'method'  => 'post',
                       'icon'    => 'bi bi-trash',
                       'attr'    => [
                           'title'   => 'Löschen',
                           'onclick' => 'return confirm("Album und alle Bilder unwiderruflich löschen?")',
                       ],
                   ]);

        $rows[] = [
            $thumb,
            $titleHtml,
            '<code>' . htmlspecialchars($album['slug']) . '</code>',
            Ui::badge((string) (int) $album['image_count']),
            $album['is_public'] ? Ui::badge('Ja', 'success') : Ui::badge('Nein'),
            $actions,
        ];
    }

    echo Ui::panel('', Ui::table($headers, $rows));
}

$content = ob_get_clean();

require ESSE_ROOT . '/admin/layout.php';
