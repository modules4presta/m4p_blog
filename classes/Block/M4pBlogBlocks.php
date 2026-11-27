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
 * Registry of the block types; the editor's "Add block" picker lists them in this order.
 */
class M4pBlogBlocks
{
    private const TYPES = [
        M4pBlogHtmlBlock::class,
        M4pBlogFactsBlock::class,
        M4pBlogListBlock::class,
        M4pBlogStepsBlock::class,
        M4pBlogNoteBlock::class,
        M4pBlogFaqBlock::class,
        M4pBlogFileBlock::class,
    ];

    /** @var array<string, M4pBlogBlock>|null */
    private static $blocks;

    /**
     * @return array<string, M4pBlogBlock>
     */
    public static function all(Module $module): array
    {
        if (self::$blocks === null) {
            self::$blocks = [];
            foreach (self::TYPES as $class) {
                $block = new $class($module);
                self::$blocks[$block->type()] = $block;
            }
        }

        return self::$blocks;
    }

    public static function get(Module $module, string $type): ?M4pBlogBlock
    {
        return self::all($module)[$type] ?? null;
    }
}
