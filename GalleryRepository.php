<?php

declare(strict_types=1);

namespace EsseGallery;

use Esse\DB;

class GalleryRepository
{
    public static function migrate(): void
    {
        $ta = DB::table('gallery_albums');
        $ti = DB::table('gallery_images');

        DB::query("CREATE TABLE IF NOT EXISTS `{$ta}` (
            `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `title`          VARCHAR(255) NOT NULL,
            `slug`           VARCHAR(255) NOT NULL,
            `description`    TEXT,
            `cover_image_id` INT UNSIGNED NULL DEFAULT NULL,
            `is_public`      TINYINT(1) NOT NULL DEFAULT 1,
            `sort_order`     INT NOT NULL DEFAULT 0,
            `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        DB::query("CREATE TABLE IF NOT EXISTS `{$ti}` (
            `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `album_id`      INT UNSIGNED NOT NULL,
            `filename`      VARCHAR(255) NOT NULL,
            `original_name` VARCHAR(255) NOT NULL DEFAULT '',
            `caption`       VARCHAR(500) NOT NULL DEFAULT '',
            `sort_order`    INT NOT NULL DEFAULT 0,
            `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_album` (`album_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public static function drop(): void
    {
        $ta = DB::table('gallery_albums');
        $ti = DB::table('gallery_images');
        DB::query("DROP TABLE IF EXISTS `{$ti}`");
        DB::query("DROP TABLE IF EXISTS `{$ta}`");
    }

    // --- Alben ---

    public static function allAlbums(): array
    {
        $ta = DB::table('gallery_albums');
        $ti = DB::table('gallery_images');
        return DB::fetchAll(
            "SELECT a.*, COUNT(i.id) AS image_count
               FROM `{$ta}` a
          LEFT JOIN `{$ti}` i ON i.album_id = a.id
           GROUP BY a.id
           ORDER BY a.sort_order ASC, a.created_at DESC"
        );
    }

    public static function publicAlbums(): array
    {
        $ta = DB::table('gallery_albums');
        $ti = DB::table('gallery_images');
        return DB::fetchAll(
            "SELECT a.*, COUNT(i.id) AS image_count
               FROM `{$ta}` a
          LEFT JOIN `{$ti}` i ON i.album_id = a.id
              WHERE a.is_public = 1
           GROUP BY a.id
           ORDER BY a.sort_order ASC, a.created_at DESC"
        );
    }

    public static function albumById(int $id): ?array
    {
        $ta = DB::table('gallery_albums');
        $row = DB::fetch("SELECT * FROM `{$ta}` WHERE id = ?", [$id]);
        return $row ?: null;
    }

    public static function albumBySlug(string $slug): ?array
    {
        $ta = DB::table('gallery_albums');
        $row = DB::fetch("SELECT * FROM `{$ta}` WHERE slug = ?", [$slug]);
        return $row ?: null;
    }

    public static function createAlbum(array $data): int
    {
        $ta = DB::table('gallery_albums');
        return DB::insert($ta, [
            'title'       => $data['title'],
            'slug'        => self::uniqueSlug($data['slug'] ?? self::slugify($data['title'])),
            'description' => $data['description'] ?? '',
            'is_public'   => (int) ($data['is_public'] ?? 1),
            'sort_order'  => (int) ($data['sort_order'] ?? 0),
        ]);
    }

    public static function updateAlbum(int $id, array $data): void
    {
        $ta = DB::table('gallery_albums');
        $current = self::albumById($id);
        if (!$current) return;

        $newSlug = $data['slug'] ?? self::slugify($data['title'] ?? $current['title']);
        if ($newSlug !== $current['slug']) {
            $newSlug = self::uniqueSlug($newSlug, $id);
        }

        DB::update($ta, [
            'title'       => $data['title'] ?? $current['title'],
            'slug'        => $newSlug,
            'description' => $data['description'] ?? $current['description'],
            'is_public'   => isset($data['is_public']) ? (int) $data['is_public'] : $current['is_public'],
            'sort_order'  => isset($data['sort_order']) ? (int) $data['sort_order'] : $current['sort_order'],
        ], ['id' => $id]);
    }

    public static function deleteAlbum(int $id): void
    {
        $ta = DB::table('gallery_albums');
        DB::delete($ta, ['id' => $id]);
    }

    public static function setCover(int $albumId, ?int $imageId): void
    {
        $ta = DB::table('gallery_albums');
        DB::update($ta, ['cover_image_id' => $imageId], ['id' => $albumId]);
    }

    // --- Bilder ---

    public static function imagesByAlbum(int $albumId): array
    {
        $ti = DB::table('gallery_images');
        return DB::fetchAll(
            "SELECT * FROM `{$ti}` WHERE album_id = ? ORDER BY sort_order ASC, created_at ASC",
            [$albumId]
        );
    }

    public static function imageById(int $id): ?array
    {
        $ti = DB::table('gallery_images');
        $row = DB::fetch("SELECT * FROM `{$ti}` WHERE id = ?", [$id]);
        return $row ?: null;
    }

    public static function addImage(int $albumId, string $filename, string $originalName): int
    {
        $ti = DB::table('gallery_images');
        $maxOrder = (int) DB::value(
            "SELECT COALESCE(MAX(sort_order), -1) FROM `{$ti}` WHERE album_id = ?",
            [$albumId]
        );
        $id = DB::insert($ti, [
            'album_id'      => $albumId,
            'filename'      => $filename,
            'original_name' => $originalName,
            'sort_order'    => $maxOrder + 1,
        ]);

        // Erstes Bild automatisch als Cover setzen
        $album = self::albumById($albumId);
        if ($album && $album['cover_image_id'] === null) {
            self::setCover($albumId, $id);
        }

        return $id;
    }

    public static function updateCaption(int $imageId, string $caption): void
    {
        $ti = DB::table('gallery_images');
        DB::update($ti, ['caption' => $caption], ['id' => $imageId]);
    }

    public static function deleteImage(int $id): void
    {
        $ti = DB::table('gallery_images');
        DB::delete($ti, ['id' => $id]);
    }

    // --- Hilfsfunktionen ---

    public static function slugify(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $map  = ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss'];
        $text = strtr($text, $map);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }

    private static function uniqueSlug(string $slug, int $excludeId = 0): string
    {
        $ta       = DB::table('gallery_albums');
        $original = $slug;
        $i        = 1;

        while (true) {
            $existing = DB::value(
                "SELECT id FROM `{$ta}` WHERE slug = ?",
                [$slug]
            );
            if (!$existing || (int) $existing === $excludeId) {
                return $slug;
            }
            $slug = $original . '-' . $i;
            $i++;
        }
    }
}
