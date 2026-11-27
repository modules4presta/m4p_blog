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
 * A PDF to download, with its name and size.
 */
class M4pBlogFileBlock extends M4pBlogBlock
{
    public function type(): string
    {
        return 'file';
    }

    public function label(): string
    {
        return $this->trans('File to download');
    }

    public function icon(): string
    {
        return 'download';
    }

    public function fields(): array
    {
        return [
            ['name' => 'file', 'type' => 'file', 'translatable' => false, 'label' => $this->trans('File (PDF, up to 20 MB)')],
            ['name' => 'name', 'type' => 'text', 'translatable' => true, 'label' => $this->trans('Name shown on the page')],
        ];
    }

    public function isEmpty(array $settings, array $data): bool
    {
        return M4pBlogFile::describe((string) ($settings['file'] ?? '')) === null;
    }

    public function view(array $settings, array $data): array
    {
        $file = M4pBlogFile::describe((string) ($settings['file'] ?? ''));
        $bytes = $file['bytes'] ?? 0;
        $locale = Context::getContext()->getCurrentLocale();
        $size = $bytes >= 1048576
            ? $locale->formatNumber(round($bytes / 1048576, 1)) . ' MB'
            : $locale->formatNumber(max(1, (int) round($bytes / 1024))) . ' KB';

        return [
            'name' => (string) ($data['name'] ?? '') ?: $this->trans('File to download'),
            'url' => $file['url'] ?? '',
            'size' => $size,
        ];
    }
}
