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

class M4pBlogHtmlBlock extends M4pBlogBlock
{
    public function type(): string
    {
        return 'html';
    }

    public function label(): string
    {
        return $this->trans('Text');
    }

    public function icon(): string
    {
        return 'notes';
    }

    public function fields(): array
    {
        return [
            [
                'name' => 'body',
                'type' => 'richtext',
                'translatable' => true,
                'label' => $this->trans('Content'),
                'placeholder' => $this->trans('Space for the content'),
            ],
        ];
    }
}
