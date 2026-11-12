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

class M4pBlogPost extends ObjectModel
{
    public const TABLE = 'm4p_blog_post';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PUBLISHED = 'published';

    /** ObjectModel declares `id_shop` protected and EntityMapper assigns it from outside. */
    public $id_shop;

    /** @var string */
    public $status = self::STATUS_DRAFT;

    /** @var string */
    public $date_published;

    /** @var int */
    public $id_author = 0;

    /** @var int */
    public $id_category_default = 0;

    /** @var int */
    public $reading_time = 0;

    /** @var string */
    public $image = '';

    /** @var int */
    public $position = 0;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    /** @var string|array<int, string> */
    public $title;

    /** @var string|array<int, string> */
    public $excerpt;

    /** @var string|array<int, string> */
    public $content;

    /** @var string|array<int, string> */
    public $meta_title;

    /** @var string|array<int, string> */
    public $meta_description;

    /** @var string|array<int, string> */
    public $link_rewrite;

    public static $definition = [
        'table' => self::TABLE,
        'primary' => 'id_m4p_blog_post',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'status' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 16],
            'date_published' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
            'id_author' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_category_default' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'reading_time' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'image' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'title' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'excerpt' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 1000],
            'content' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'],
            'meta_title' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255],
            'meta_description' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 512],
            'link_rewrite' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => true, 'size' => 255],
        ],
    ];

    /**
     * @return array<string, string> status => translated label
     */
    public static function statuses(Module $module): array
    {
        $translator = $module->getTranslator();

        return [
            self::STATUS_DRAFT => $translator->trans('Draft', [], 'Modules.M4pblog.Admin'),
            self::STATUS_SCHEDULED => $translator->trans('Scheduled', [], 'Modules.M4pblog.Admin'),
            self::STATUS_PUBLISHED => $translator->trans('Published', [], 'Modules.M4pblog.Admin'),
        ];
    }

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

    public function delete()
    {
        $id = (int) $this->id;
        $db = Db::getInstance();
        $db->delete('m4p_blog_post_category', 'id_m4p_blog_post = ' . $id);
        $db->delete('m4p_blog_post_tag', 'id_m4p_blog_post = ' . $id);
        $db->delete('m4p_blog_comment', 'id_m4p_blog_post = ' . $id);
        if ($this->image) {
            M4pBlogImage::delete((string) $this->image);
        }

        return parent::delete();
    }

    /**
     * @return int[]
     */
    public function getCategories(): array
    {
        $rows = Db::getInstance()->executeS(
            'SELECT `id_m4p_blog_category` FROM `' . _DB_PREFIX_ . 'm4p_blog_post_category`
             WHERE `id_m4p_blog_post` = ' . (int) $this->id . ' AND `id_shop` = ' . (int) $this->id_shop
        ) ?: [];

        return array_map(static fn (array $row): int => (int) $row['id_m4p_blog_category'], $rows);
    }

    /**
     * @param int[] $idCategories
     */
    public function setCategories(array $idCategories): void
    {
        $db = Db::getInstance();
        $db->delete('m4p_blog_post_category', 'id_m4p_blog_post = ' . (int) $this->id);
        if ($this->id_category_default) {
            $idCategories[] = (int) $this->id_category_default;
        }
        foreach (array_unique(array_filter(array_map('intval', $idCategories))) as $idCategory) {
            $db->insert('m4p_blog_post_category', [
                'id_m4p_blog_post' => (int) $this->id,
                'id_m4p_blog_category' => $idCategory,
                'id_shop' => (int) $this->id_shop,
            ]);
        }
    }

    /**
     * SQL condition for a post a visitor may see: published, or scheduled with the date already passed.
     */
    public static function visibleCondition(string $alias = 'p'): string
    {
        return '(' . $alias . '.`status` = "' . self::STATUS_PUBLISHED . '"
            OR (' . $alias . '.`status` = "' . self::STATUS_SCHEDULED . '" AND ' . $alias . '.`date_published` <= NOW()))';
    }

    /**
     * Posts for a listing page.
     *
     * @param array{category?: int, author?: int, limit?: int, offset?: int} $filters
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getPosts(int $idShop, int $idLang, array $filters = []): array
    {
        $sql = 'SELECT p.*, pl.`title`, pl.`excerpt`, pl.`content`, pl.`link_rewrite`, pl.`meta_description`
            FROM `' . _DB_PREFIX_ . self::TABLE . '` p
            INNER JOIN `' . _DB_PREFIX_ . 'm4p_blog_post_lang` pl
                ON pl.`id_m4p_blog_post` = p.`id_m4p_blog_post`
                AND pl.`id_shop` = p.`id_shop`
                AND pl.`id_lang` = ' . $idLang
            . self::joinFilters($filters) . '
            WHERE p.`id_shop` = ' . $idShop . ' AND ' . self::visibleCondition() . '
                AND pl.`title` <> "" ' . self::whereFilters($filters) . '
            ORDER BY p.`date_published` DESC, p.`position` ASC, p.`id_m4p_blog_post` DESC';

        if (isset($filters['limit'])) {
            $sql .= ' LIMIT ' . max(0, (int) ($filters['offset'] ?? 0)) . ', ' . max(1, (int) $filters['limit']);
        }

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * @param array{category?: int, author?: int} $filters
     */
    public static function countPosts(int $idShop, int $idLang, array $filters = []): int
    {
        return (int) Db::getInstance()->getValue(
            'SELECT COUNT(DISTINCT p.`id_m4p_blog_post`)
            FROM `' . _DB_PREFIX_ . self::TABLE . '` p
            INNER JOIN `' . _DB_PREFIX_ . 'm4p_blog_post_lang` pl
                ON pl.`id_m4p_blog_post` = p.`id_m4p_blog_post`
                AND pl.`id_shop` = p.`id_shop`
                AND pl.`id_lang` = ' . $idLang
            . self::joinFilters($filters) . '
            WHERE p.`id_shop` = ' . $idShop . ' AND ' . self::visibleCondition() . '
                AND pl.`title` <> "" ' . self::whereFilters($filters)
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getByRewrite(string $rewrite, int $idShop, int $idLang): ?array
    {
        $row = Db::getInstance()->getRow(
            'SELECT p.*, pl.*
            FROM `' . _DB_PREFIX_ . self::TABLE . '` p
            INNER JOIN `' . _DB_PREFIX_ . 'm4p_blog_post_lang` pl
                ON pl.`id_m4p_blog_post` = p.`id_m4p_blog_post`
                AND pl.`id_shop` = p.`id_shop`
                AND pl.`id_lang` = ' . $idLang . '
            WHERE p.`id_shop` = ' . $idShop . '
                AND pl.`link_rewrite` = "' . pSQL($rewrite) . '"
                AND ' . self::visibleCondition()
        );

        return $row ?: null;
    }

    /**
     * Rewrite of a post in a language, whatever its status; the back office needs it for the preview link.
     */
    public static function getRewrite(int $idPost, int $idShop, int $idLang): string
    {
        return (string) Db::getInstance()->getValue(
            'SELECT `link_rewrite` FROM `' . _DB_PREFIX_ . 'm4p_blog_post_lang`
             WHERE `id_m4p_blog_post` = ' . $idPost . ' AND `id_shop` = ' . $idShop . ' AND `id_lang` = ' . $idLang
        );
    }

    private static function joinFilters(array $filters): string
    {
        if (empty($filters['category'])) {
            return '';
        }

        return ' INNER JOIN `' . _DB_PREFIX_ . 'm4p_blog_post_category` pc
            ON pc.`id_m4p_blog_post` = p.`id_m4p_blog_post`
            AND pc.`id_shop` = p.`id_shop`
            AND pc.`id_m4p_blog_category` = ' . (int) $filters['category'];
    }

    private static function whereFilters(array $filters): string
    {
        return empty($filters['author']) ? '' : ' AND p.`id_author` = ' . (int) $filters['author'];
    }
}
