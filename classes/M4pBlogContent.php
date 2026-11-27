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
 * The content of a post: an ordered list of typed blocks, stored as JSON in `m4p_blog_post_lang.content`.
 *
 * One block is `{"type": …, "active": true, "settings": {…}, "data": {…}}` – the shape m4p_productcontent
 * uses, so block types and templates carry over. `data` holds the fields a block marks translatable and
 * `settings` the rest; both sit inside the language row, because the content itself is per language.
 */
class M4pBlogContent
{
    private const WORDS_PER_MINUTE = 200;

    /**
     * @return array<int, array{type: string, active: bool, settings: array, data: array}>
     */
    public static function decode(?string $json): array
    {
        $decoded = json_decode((string) $json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $blocks = [];
        foreach ($decoded as $block) {
            if (!is_array($block) || !isset($block['type'])) {
                continue;
            }
            $blocks[] = [
                'type' => (string) $block['type'],
                'active' => !isset($block['active']) || (bool) $block['active'],
                'settings' => (array) ($block['settings'] ?? []),
                'data' => (array) ($block['data'] ?? []),
            ];
        }

        return $blocks;
    }

    /**
     * Cleans a posted list of blocks; unknown types are dropped.
     *
     * @return array<int, array{type: string, active: bool, settings: array, data: array}>
     */
    public static function sanitize(Module $module, array $posted): array
    {
        $blocks = [];
        foreach ($posted as $block) {
            if (!is_array($block)) {
                continue;
            }
            $type = M4pBlogBlocks::get($module, (string) ($block['type'] ?? ''));
            if ($type === null) {
                continue;
            }
            $settings = $type->sanitize((array) ($block['settings'] ?? []), false);
            $data = $type->sanitize((array) ($block['data'] ?? []), true);
            if ($type->isEmpty($settings, $data)) {
                continue;
            }
            $blocks[] = [
                'type' => $type->type(),
                'active' => !empty($block['active']),
                'settings' => $settings,
                'data' => $data,
            ];
        }

        return $blocks;
    }

    public static function encode(array $blocks): string
    {
        return $blocks ? (string) json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
    }

    /**
     * Blocks ready to render: active, non-empty, with their type and the data the template reads.
     *
     * @return array<int, array{block: M4pBlogBlock, settings: array, data: array}>
     */
    public static function views(Module $module, ?string $json): array
    {
        $views = [];
        foreach (self::decode($json) as $block) {
            $type = M4pBlogBlocks::get($module, $block['type']);
            if ($type === null || !$block['active'] || $type->isEmpty($block['settings'], $block['data'])) {
                continue;
            }
            $views[] = [
                'block' => $type,
                'settings' => $block['settings'],
                'data' => $type->view($block['settings'], $block['data']),
            ];
        }

        return $views;
    }

    /**
     * Readable text of every block, for excerpts and the reading time.
     */
    public static function toText(?string $json): string
    {
        $parts = [];
        foreach (self::decode($json) as $block) {
            array_walk_recursive($block['data'], static function ($value) use (&$parts): void {
                $parts[] = M4pBlogHtml::toText((string) $value);
            });
        }

        return trim(implode(' ', array_filter($parts)));
    }

    /**
     * Reading time in minutes, at least one for a post that has any content.
     */
    public static function readingTime(?string $json): int
    {
        $words = preg_match_all('/[\pL\pN][\pL\pN\'-]*/u', self::toText($json));

        return $words > 0 ? max(1, (int) ceil($words / self::WORDS_PER_MINUTE)) : 0;
    }
}
