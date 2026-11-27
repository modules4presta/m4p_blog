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
 * A highlighted note: information, a tip or a warning, in the message component of the theme.
 */
class M4pBlogNoteBlock extends M4pBlogBlock
{
    public const TONES = ['info' => 'info', 'tip' => 'success', 'warning' => 'warning'];

    public function type(): string
    {
        return 'note';
    }

    public function label(): string
    {
        return $this->trans('Highlight');
    }

    public function icon(): string
    {
        return 'info';
    }

    public function fields(): array
    {
        return [
            [
                'name' => 'tone',
                'type' => 'choice',
                'translatable' => false,
                'label' => $this->trans('Type'),
                'options' => ['info' => $this->trans('Information'), 'tip' => $this->trans('Tip'), 'warning' => $this->trans('Warning')],
                'default' => 'info',
            ],
            ['name' => 'text', 'type' => 'textarea', 'translatable' => true, 'label' => $this->trans('Text')],
        ];
    }

    public function view(array $settings, array $data): array
    {
        return $data + ['tone' => self::TONES[(string) ($settings['tone'] ?? 'info')] ?? 'info'];
    }
}
