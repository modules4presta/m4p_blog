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
 * Numbered steps under a title, one step per line.
 */
class M4pBlogStepsBlock extends M4pBlogBlock
{
    public function type(): string
    {
        return 'steps';
    }

    public function label(): string
    {
        return $this->trans('Steps');
    }

    public function icon(): string
    {
        return 'format_list_numbered';
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'translatable' => true, 'label' => $this->trans('Title')],
            [
                'name' => 'steps',
                'type' => 'lines',
                'translatable' => true,
                'label' => $this->trans('Steps – one per line'),
            ],
        ];
    }

    public function isEmpty(array $settings, array $data): bool
    {
        return empty($data['steps']);
    }
}
