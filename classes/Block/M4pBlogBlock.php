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
 * A block type of the post content. A type declares its fields (the editor builds the form from them),
 * cleans the posted values and names its front template; storage and ordering are shared.
 *
 * Field types: text, richtext, lines (one item per line), image, choice. Fields marked `translatable` hold
 * the text of the post language, the others the settings of the block.
 */
abstract class M4pBlogBlock
{
    /** @var Module */
    protected $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    abstract public function type(): string;

    abstract public function label(): string;

    /** Material icon name shown in the "Add block" picker. */
    abstract public function icon(): string;

    /**
     * @return array<int, array{name: string, type: string, translatable: bool, label: string, placeholder?: string, options?: array<string, string>, default?: string}>
     */
    abstract public function fields(): array;

    public function template(): string
    {
        return 'module:m4p_blog/views/templates/front/blocks/' . $this->type() . '.tpl';
    }

    /**
     * Cleans the settings or the text of one block.
     *
     * @return array<string, mixed>
     */
    public function sanitize(array $values, bool $translatable): array
    {
        $clean = [];
        foreach ($this->fields() as $field) {
            if ($field['translatable'] !== $translatable) {
                continue;
            }
            $clean[$field['name']] = $this->sanitizeField($field, $values[$field['name']] ?? null);
        }

        return $clean;
    }

    /**
     * Whether the block has nothing to show; empty blocks are not rendered.
     */
    public function isEmpty(array $settings, array $data): bool
    {
        foreach ($this->fields() as $field) {
            if ($field['type'] === 'choice') {
                continue;
            }
            $value = $field['translatable'] ? ($data[$field['name']] ?? null) : ($settings[$field['name']] ?? null);
            if ($value !== null && $value !== '' && $value !== []) {
                return false;
            }
        }

        return true;
    }

    /**
     * Data as the front template gets it.
     */
    public function view(array $settings, array $data): array
    {
        return $data;
    }

    /**
     * Field definitions for the editor, with labels translated.
     */
    public function describe(): array
    {
        return [
            'type' => $this->type(),
            'label' => $this->label(),
            'icon' => $this->icon(),
            'fields' => $this->fields(),
        ];
    }

    /**
     * @return mixed
     */
    protected function sanitizeField(array $field, $value)
    {
        switch ($field['type']) {
            case 'richtext':
                return M4pBlogHtml::clean((string) $value);
            case 'lines':
                $lines = is_array($value) ? $value : preg_split('/\R/u', (string) $value);

                return array_values(array_filter(array_map(
                    static fn ($line): string => mb_substr(trim(strip_tags((string) $line)), 0, 255),
                    $lines
                ), static fn (string $line): bool => $line !== ''));
            case 'image':
                return M4pBlogImage::exists((string) $value) ? (string) $value : '';
            case 'choice':
                $value = (string) $value;

                return array_key_exists($value, $field['options'] ?? []) ? $value : (string) ($field['default'] ?? '');
            default:
                return mb_substr(trim(strip_tags((string) $value)), 0, 255);
        }
    }

    protected function trans(string $text): string
    {
        return $this->module->getTranslator()->trans($text, [], 'Modules.M4pblog.Admin');
    }
}
