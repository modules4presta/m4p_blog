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
 * PDF files of the "File to download" block, stored in img/m4p_blog/files/ under a random name, so a
 * module update or a theme change keeps them.
 */
class M4pBlogFile
{
    public const MAX_BYTES = 20971520;

    public static function directory(): string
    {
        return _PS_IMG_DIR_ . 'm4p_blog/files/';
    }

    public static function exists(string $name): bool
    {
        return preg_match('/^[a-f0-9]{16}\.pdf$/', $name) === 1 && is_file(self::directory() . $name);
    }

    /**
     * Stores one uploaded PDF; returns its name or '' when the file is not a PDF up to 20 MB.
     *
     * @param array{tmp_name: string, size: int|string, error: int|string} $file
     */
    public static function store(array $file): string
    {
        if ((int) $file['error'] !== UPLOAD_ERR_OK || (int) $file['size'] > self::MAX_BYTES || !is_uploaded_file($file['tmp_name'])) {
            return '';
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $head = (string) file_get_contents($file['tmp_name'], false, null, 0, 5);
        if ($mime !== 'application/pdf' || $head !== '%PDF-') {
            return '';
        }

        $dir = self::directory();
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            return '';
        }
        if (!is_file($dir . 'index.php') && is_file(_PS_IMG_DIR_ . 'index.php')) {
            copy(_PS_IMG_DIR_ . 'index.php', $dir . 'index.php');
        }
        $name = bin2hex(random_bytes(8)) . '.pdf';

        return move_uploaded_file($file['tmp_name'], $dir . $name) ? $name : '';
    }

    /**
     * Address and size of a stored file, null when it is gone.
     *
     * @return array{url: string, bytes: int}|null
     */
    public static function describe(string $name): ?array
    {
        if (!self::exists($name)) {
            return null;
        }

        return [
            'url' => Context::getContext()->link->getMediaLink(_PS_IMG_ . 'm4p_blog/files/' . $name),
            'bytes' => (int) filesize(self::directory() . $name),
        ];
    }
}
