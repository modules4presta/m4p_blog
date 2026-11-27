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
 * A row of short facts, "Label: value" per line (servings, time, season, dimensions).
 */
class M4pBlogFactsBlock extends M4pBlogBlock
{
    public function type(): string
    {
        return 'facts';
    }

    public function label(): string
    {
        return $this->trans('Parameters');
    }

    public function icon(): string
    {
        return 'view_column';
    }

    public function fields(): array
    {
        return [
            [
                'name' => 'rows',
                'type' => 'lines',
                'translatable' => true,
                'label' => $this->trans('Rows – one per line, label and value after a colon'),
                'placeholder' => $this->trans('Label: value'),
            ],
        ];
    }

    public function view(array $settings, array $data): array
    {
        $rows = [];
        foreach ((array) ($data['rows'] ?? []) as $line) {
            $parts = explode(':', (string) $line, 2);
            $rows[] = isset($parts[1])
                ? ['label' => trim($parts[0]), 'value' => trim($parts[1])]
                : ['label' => '', 'value' => trim($parts[0])];
        }

        return ['rows' => $rows];
    }
}
