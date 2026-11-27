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
 * A titled list in a frame, one item per line, with bullets or check marks.
 */
class M4pBlogListBlock extends M4pBlogBlock
{
    public function type(): string
    {
        return 'list';
    }

    public function label(): string
    {
        return $this->trans('Boxed list');
    }

    public function icon(): string
    {
        return 'checklist';
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'translatable' => true, 'label' => $this->trans('Title')],
            [
                'name' => 'items',
                'type' => 'lines',
                'translatable' => true,
                'label' => $this->trans('Items – one per line'),
            ],
            [
                'name' => 'marker',
                'type' => 'choice',
                'translatable' => false,
                'label' => $this->trans('Marker'),
                'options' => ['bullet' => $this->trans('Bullet'), 'check' => $this->trans('Check mark')],
                'default' => 'bullet',
            ],
        ];
    }

    public function isEmpty(array $settings, array $data): bool
    {
        return empty($data['items']);
    }
}
