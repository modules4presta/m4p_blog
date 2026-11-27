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
 * Questions and answers, shown as an accordion.
 */
class M4pBlogFaqBlock extends M4pBlogBlock
{
    public function type(): string
    {
        return 'faq';
    }

    public function label(): string
    {
        return $this->trans('Questions and answers');
    }

    public function icon(): string
    {
        return 'quiz';
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'translatable' => true, 'label' => $this->trans('Title')],
            [
                'name' => 'items',
                'type' => 'pairs',
                'translatable' => true,
                'label' => $this->trans('Questions – the question in the first line, the answer below it, an empty line before the next question'),
            ],
        ];
    }

    public function isEmpty(array $settings, array $data): bool
    {
        return empty($data['items']);
    }
}
