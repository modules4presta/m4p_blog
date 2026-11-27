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

class M4pBlogCategory extends ObjectModel
{
    public const TABLE = 'm4p_blog_category';

    /** How deep the tree may go; a category cannot be moved under one of its own descendants. */
    public const MAX_DEPTH = 3;

    /** ObjectModel declares `id_shop` protected and EntityMapper assigns it from outside. */
    public $id_shop;

    /** @var int */
    public $id_parent = 0;

    /** @var bool */
    public $active = true;

    /** @var int */
    public $position = 0;

    /** @var string */
    public $image = '';

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    /** @var string|array<int, string> */
    public $name;

    /** @var string|array<int, string> */
    public $description;

    /** @var string|array<int, string> */
    public $meta_title;

    /** @var string|array<int, string> */
    public $meta_description;

    /** @var string|array<int, string> */
    public $link_rewrite;

    public static $definition = [
        'table' => self::TABLE,
        'primary' => 'id_m4p_blog_category',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_parent' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'image' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'name' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'description' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'],
            'meta_title' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255],
            'meta_description' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 512],
            'link_rewrite' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => true, 'size' => 255],
        ],
    ];

    public function add($autoDate = true, $nullValues = false)
    {
        $this->id_shop = (int) ($this->id_shop ?: Context::getContext()->shop->id);
        if (!$this->position) {
            $this->position = 1 + (int) Db::getInstance()->getValue(
                'SELECT MAX(`position`) FROM `' . _DB_PREFIX_ . self::TABLE . '` WHERE `id_shop` = ' . (int) $this->id_shop
            );
        }

        return parent::add($autoDate, $nullValues);
    }

    public function update($nullValues = false)
    {
        $before = [];
        foreach (Db::getInstance()->executeS(
            'SELECT `id_lang`, `link_rewrite` FROM `' . _DB_PREFIX_ . 'm4p_blog_category_lang`'
            . ' WHERE `id_m4p_blog_category` = ' . (int) $this->id . ' AND `id_shop` = ' . (int) $this->id_shop
        ) ?: [] as $row) {
            $before[(int) $row['id_lang']] = (string) $row['link_rewrite'];
        }
        if (!parent::update($nullValues)) {
            return false;
        }
        M4pBlogRedirect::remember(M4pBlogRedirect::CATEGORY, (int) $this->id, (int) $this->id_shop, $before, (array) $this->link_rewrite);

        return true;
    }

    /**
     * Children move up to the parent of the deleted category, so no subtree is orphaned.
     */
    public function delete()
    {
        $db = Db::getInstance();
        M4pBlogRedirect::forget(M4pBlogRedirect::CATEGORY, (int) $this->id, (int) $this->id_shop);
        $db->update(
            self::TABLE,
            ['id_parent' => (int) $this->id_parent],
            'id_parent = ' . (int) $this->id . ' AND id_shop = ' . (int) $this->id_shop
        );
        $db->delete('m4p_blog_post_category', 'id_m4p_blog_category = ' . (int) $this->id);
        $db->update(
            M4pBlogPost::TABLE,
            ['id_category_default' => 0],
            'id_category_default = ' . (int) $this->id . ' AND id_shop = ' . (int) $this->id_shop
        );
        if ($this->image) {
            M4pBlogImage::delete((string) $this->image);
        }

        return parent::delete();
    }

    /**
     * Every category of a shop, ordered as a tree, each row with its `depth`.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getTree(int $idShop, int $idLang, bool $activeOnly = false): array
    {
        $rows = Db::getInstance()->executeS(
            'SELECT c.*, cl.`name`, cl.`link_rewrite`
            FROM `' . _DB_PREFIX_ . self::TABLE . '` c
            INNER JOIN `' . _DB_PREFIX_ . 'm4p_blog_category_lang` cl
                ON cl.`id_m4p_blog_category` = c.`id_m4p_blog_category`
                AND cl.`id_shop` = c.`id_shop`
                AND cl.`id_lang` = ' . $idLang . '
            WHERE c.`id_shop` = ' . $idShop . ($activeOnly ? ' AND c.`active` = 1' : '') . '
            ORDER BY c.`position` ASC, cl.`name` ASC'
        ) ?: [];

        $children = [];
        foreach ($rows as $row) {
            $children[(int) $row['id_parent']][] = $row;
        }

        return self::flatten($children, 0, 0);
    }

    /**
     * Counts of published posts per category of a shop.
     *
     * @return array<int, int>
     */
    public static function getPostCounts(int $idShop, int $idLang): array
    {
        $rows = Db::getInstance()->executeS(
            'SELECT pc.`id_m4p_blog_category`, COUNT(DISTINCT p.`id_m4p_blog_post`) AS total
            FROM `' . _DB_PREFIX_ . 'm4p_blog_post_category` pc
            INNER JOIN `' . _DB_PREFIX_ . M4pBlogPost::TABLE . '` p
                ON p.`id_m4p_blog_post` = pc.`id_m4p_blog_post` AND p.`id_shop` = pc.`id_shop`
            INNER JOIN `' . _DB_PREFIX_ . 'm4p_blog_post_lang` pl
                ON pl.`id_m4p_blog_post` = p.`id_m4p_blog_post`
                AND pl.`id_shop` = p.`id_shop`
                AND pl.`id_lang` = ' . $idLang . '
            WHERE pc.`id_shop` = ' . $idShop . ' AND ' . M4pBlogPost::visibleCondition() . ' AND pl.`title` <> ""
            GROUP BY pc.`id_m4p_blog_category`'
        ) ?: [];

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['id_m4p_blog_category']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getByRewrite(string $rewrite, int $idShop, int $idLang): ?array
    {
        $row = Db::getInstance()->getRow(
            'SELECT c.*, cl.*
            FROM `' . _DB_PREFIX_ . self::TABLE . '` c
            INNER JOIN `' . _DB_PREFIX_ . 'm4p_blog_category_lang` cl
                ON cl.`id_m4p_blog_category` = c.`id_m4p_blog_category`
                AND cl.`id_shop` = c.`id_shop`
                AND cl.`id_lang` = ' . $idLang . '
            WHERE c.`id_shop` = ' . $idShop . ' AND c.`active` = 1
                AND cl.`link_rewrite` = "' . pSQL($rewrite) . '"'
        );

        return $row ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getById(int $idCategory, int $idShop, int $idLang): ?array
    {
        $row = Db::getInstance()->getRow(
            'SELECT c.*, cl.`name`, cl.`link_rewrite`
            FROM `' . _DB_PREFIX_ . self::TABLE . '` c
            INNER JOIN `' . _DB_PREFIX_ . 'm4p_blog_category_lang` cl
                ON cl.`id_m4p_blog_category` = c.`id_m4p_blog_category`
                AND cl.`id_shop` = c.`id_shop`
                AND cl.`id_lang` = ' . $idLang . '
            WHERE c.`id_m4p_blog_category` = ' . $idCategory . ' AND c.`id_shop` = ' . $idShop
        );

        return $row ?: null;
    }

    /**
     * Ids of a category and everything below it, so a category cannot become its own parent.
     *
     * @return int[]
     */
    public static function getSubtreeIds(int $idCategory, int $idShop): array
    {
        $ids = [$idCategory];
        $level = [$idCategory];
        for ($depth = 0; $depth < self::MAX_DEPTH && $level; ++$depth) {
            $rows = Db::getInstance()->executeS(
                'SELECT `id_m4p_blog_category` FROM `' . _DB_PREFIX_ . self::TABLE . '`
                 WHERE `id_shop` = ' . $idShop . ' AND `id_parent` IN (' . implode(',', array_map('intval', $level)) . ')'
            ) ?: [];
            $level = array_map(static fn (array $row): int => (int) $row['id_m4p_blog_category'], $rows);
            $ids = array_merge($ids, $level);
        }

        return $ids;
    }

    /**
     * Positions are shop-wide, not per branch: the back office list reorders every category at once and
     * the tree only needs them to order siblings against each other.
     */
    public static function updatePositions(array $idCategories, int $idShop): bool
    {
        $done = true;
        foreach (array_values($idCategories) as $position => $idCategory) {
            $done = $done && Db::getInstance()->update(
                self::TABLE,
                ['position' => (int) $position],
                'id_m4p_blog_category = ' . (int) $idCategory . ' AND id_shop = ' . $idShop
            );
        }

        return $done;
    }

    /**
     * @param array<int, array<int, array<string, mixed>>> $children
     *
     * @return array<int, array<string, mixed>>
     */
    private static function flatten(array $children, int $idParent, int $depth): array
    {
        if ($depth > self::MAX_DEPTH) {
            return [];
        }

        $flat = [];
        foreach ($children[$idParent] ?? [] as $row) {
            $row['depth'] = $depth;
            $flat[] = $row;
            $flat = array_merge($flat, self::flatten($children, (int) $row['id_m4p_blog_category'], $depth + 1));
        }

        return $flat;
    }
}
