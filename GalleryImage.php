<?php

declare(strict_types=1);

namespace EsseGallery;

class GalleryImage
{
    private static string $storageBase = '';

    private static function base(): string
    {
        if (self::$storageBase === '') {
            self::$storageBase = rtrim(ESSE_ROOT, '/') . '/storage/gallery';
        }
        return self::$storageBase;
    }

    public static function originalsDir(): string
    {
        $dir = self::base() . '/originals';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        return $dir;
    }

    public static function thumbsDir(): string
    {
        $dir = self::base() . '/thumbs';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        return $dir;
    }

    public static function originalPath(string $filename): string
    {
        return self::originalsDir() . '/' . $filename;
    }

    public static function thumbPath(string $filename): string
    {
        return self::thumbsDir() . '/thumb_' . $filename;
    }

    /**
     * Speichert den Upload und gibt den generierten Dateinamen zurück.
     * Gibt null zurück wenn der MIME-Typ nicht erlaubt ist.
     */
    public static function store(array $file): ?string
    {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mime    = mime_content_type($file['tmp_name']);

        if (!in_array($mime, $allowed, true)) {
            return null;
        }

        $ext      = self::extFromMime($mime);
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest     = self::originalsDir() . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return null;
        }

        return $filename;
    }

    /**
     * Sendet das Originalbild als HTTP-Response.
     */
    public static function serve(string $filename): void
    {
        $path = self::originalPath($filename);
        if (!file_exists($path)) {
            http_response_code(404);
            exit;
        }
        self::sendFile($path);
    }

    /**
     * Sendet das Thumbnail als HTTP-Response (erzeugt es bei Bedarf).
     */
    public static function serveThumb(string $filename, int $size = 300): void
    {
        $thumbPath = self::thumbPath($filename);

        if (!file_exists($thumbPath)) {
            $originalPath = self::originalPath($filename);
            if (!file_exists($originalPath)) {
                http_response_code(404);
                exit;
            }
            if (!self::createThumb($originalPath, $thumbPath, $size)) {
                http_response_code(500);
                exit;
            }
        }

        self::sendFile($thumbPath);
    }

    /**
     * Löscht Original und Thumbnail.
     */
    public static function delete(string $filename): void
    {
        $original = self::originalPath($filename);
        $thumb    = self::thumbPath($filename);

        if (file_exists($original)) unlink($original);
        if (file_exists($thumb)) unlink($thumb);
    }

    // --- Private Helpers ---

    private static function extFromMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            default      => 'jpg',
        };
    }

    private static function sendFile(string $path): void
    {
        $mime = mime_content_type($path) ?: 'application/octet-stream';
        $mtime = filemtime($path);

        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=86400');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');

        $ifModified = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
        if ($ifModified && strtotime($ifModified) >= $mtime) {
            http_response_code(304);
            exit;
        }

        readfile($path);
        exit;
    }

    private static function createThumb(string $src, string $dest, int $size): bool
    {
        if (!extension_loaded('gd')) return false;

        $info = @getimagesize($src);
        if (!$info) return false;

        [$origW, $origH, $type] = $info;

        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => @imagecreatefrompng($src),
            IMAGETYPE_GIF  => @imagecreatefromgif($src),
            IMAGETYPE_WEBP => @imagecreatefromwebp($src),
            default        => false,
        };

        if (!$image) return false;

        // EXIF-Rotation für JPEG korrigieren
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($src);
            $orientation = $exif['Orientation'] ?? 1;
            $image = self::applyOrientation($image, $orientation);
            $origW = imagesx($image);
            $origH = imagesy($image);
        }

        // Square-Crop: kleinere Seite bestimmt das Quadrat
        $cropSize = min($origW, $origH);
        $cropX    = (int) (($origW - $cropSize) / 2);
        $cropY    = (int) (($origH - $cropSize) / 2);

        $thumb = imagecreatetruecolor($size, $size);

        // Transparenz für PNG/WebP erhalten
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
            imagefilledrectangle($thumb, 0, 0, $size, $size, $transparent);
        }

        imagecopyresampled($thumb, $image, 0, 0, $cropX, $cropY, $size, $size, $cropSize, $cropSize);

        $ok = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($thumb, $dest, 85),
            IMAGETYPE_PNG  => imagepng($thumb, $dest, 6),
            IMAGETYPE_GIF  => imagegif($thumb, $dest),
            IMAGETYPE_WEBP => imagewebp($thumb, $dest, 85),
            default        => false,
        };

        imagedestroy($image);
        imagedestroy($thumb);

        return (bool) $ok;
    }

    private static function applyOrientation(\GdImage $image, int $orientation): \GdImage
    {
        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }
}
