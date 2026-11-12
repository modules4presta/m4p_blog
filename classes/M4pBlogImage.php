<?php

/**
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Images of posts, categories and authors: WebP copies in three widths, with the dimensions in a sidecar
 * JSON so templates can set width/height and srcset. Each copy is encoded lossy and lossless and the
 * lossless one is kept while it stays small: graphics with text and flat colours (smeared by lossy WebP)
 * stay small losslessly, photos grow several times.
 */
class M4pBlogImage
{
    public const WIDTHS = [480, 960, 1600];

    private const MAX_BYTES = 10485760;
    private const LOSSLESS_MAX_BYTES = 81920;
    /** Decoding needs about 4 bytes per pixel; 40 MP stays well under the memory limit of shared hosting. */
    private const MAX_PIXELS = 40000000;
    private const TYPES = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];

    public static function directory(): string
    {
        return _PS_IMG_DIR_ . 'm4p_blog/';
    }

    public static function exists(string $name): bool
    {
        return preg_match('/^[a-f0-9]{16}$/', $name) === 1 && is_file(self::directory() . $name . '.json');
    }

    /**
     * @param array{tmp_name?: string, size?: int, error?: int} $file an entry of $_FILES
     *
     * @return string the image name, or '' when the file is not a usable image
     */
    public static function store(array $file): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || ($file['size'] ?? 0) > self::MAX_BYTES
            || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))
        ) {
            return '';
        }

        return self::importFile((string) $file['tmp_name']);
    }

    /**
     * @return string the image name, or '' when the file is not a usable image
     */
    public static function importFile(string $path): string
    {
        $info = @getimagesize($path);
        if (!$info || !isset(self::TYPES[$info[2]])
            || filesize($path) > self::MAX_BYTES
            || $info[0] * $info[1] > self::MAX_PIXELS
        ) {
            return '';
        }

        $source = @imagecreatefromstring((string) file_get_contents($path));
        if (!$source) {
            return '';
        }

        $dir = self::directory();
        if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
            imagedestroy($source);

            return '';
        }
        if (!is_file($dir . 'index.php') && is_file(_PS_IMG_DIR_ . 'index.php')) {
            copy(_PS_IMG_DIR_ . 'index.php', $dir . 'index.php');
        }

        $name = substr(sha1_file($path) ?: bin2hex(random_bytes(8)), 0, 16);

        imagepalettetotruecolor($source);
        imagealphablending($source, true);
        imagesavealpha($source, true);
        $width = imagesx($source);
        $height = imagesy($source);
        $variants = [];
        foreach (self::WIDTHS as $target) {
            $scaled = $width > $target
                ? imagescale($source, $target, (int) round($height * $target / $width), IMG_BICUBIC)
                : $source;
            file_put_contents($dir . $name . '-' . $target . '.webp', self::encode($scaled));
            $variants[$target] = [imagesx($scaled), imagesy($scaled)];
            if ($scaled !== $source) {
                imagedestroy($scaled);
            }
            // A source narrower than the next width would only be copied again.
            if ($width <= $target) {
                break;
            }
        }
        imagedestroy($source);

        file_put_contents(
            $dir . $name . '.json',
            (string) json_encode(['width' => $width, 'height' => $height, 'variants' => $variants])
        );

        return $name;
    }

    public static function delete(string $name): void
    {
        if (!self::exists($name)) {
            return;
        }
        foreach (glob(self::directory() . $name . '*') ?: [] as $file) {
            unlink($file);
        }
    }

    /**
     * @return array{src: string, srcset: string, width: int, height: int}|null
     */
    public static function sources(string $name): ?array
    {
        if (!self::exists($name)) {
            return null;
        }
        $meta = json_decode((string) file_get_contents(self::directory() . $name . '.json'), true);
        $variants = (array) ($meta['variants'] ?? []);
        if (!$variants) {
            return null;
        }

        $base = Context::getContext()->link->getMediaLink(_PS_IMG_ . 'm4p_blog/' . $name);
        $srcset = [];
        $largest = null;
        foreach ($variants as $target => $size) {
            $srcset[] = $base . '-' . $target . '.webp ' . $size[0] . 'w';
            $largest = $size;
        }

        return [
            'src' => $base . '-' . (int) array_key_first($variants) . '.webp',
            'srcset' => implode(', ', $srcset),
            'width' => (int) $largest[0],
            'height' => (int) $largest[1],
        ];
    }

    /**
     * @param resource|GdImage $image
     */
    private static function encode($image): string
    {
        ob_start();
        imagewebp($image, null, 82);
        $lossy = (string) ob_get_clean();
        if (!defined('IMG_WEBP_LOSSLESS')) {
            return $lossy;
        }
        ob_start();
        imagewebp($image, null, IMG_WEBP_LOSSLESS);
        $lossless = (string) ob_get_clean();

        return strlen($lossless) <= max(1.5 * strlen($lossy), self::LOSSLESS_MAX_BYTES) ? $lossless : $lossy;
    }
}
