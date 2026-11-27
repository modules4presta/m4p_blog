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
 * An author of posts. Not multilingual: the display name, the role and the bio are the person's own words
 * and the same in every language of the shop.
 */
class M4pBlogAuthor extends ObjectModel
{
    public const TABLE = 'm4p_blog_author';

    /** ObjectModel declares `id_shop` protected and EntityMapper assigns it from outside. */
    public $id_shop;

    /** @var int */
    public $id_employee = 0;

    /** @var bool */
    public $active = true;

    /** @var int */
    public $position = 0;

    /** @var string */
    public $display_name;

    /** @var string */
    public $role = '';

    /** @var string */
    public $bio = '';

    /** @var string */
    public $image = '';

    /** @var string */
    public $link_rewrite;

    /** @var string JSON list of {label, url} */
    public $links = '';

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    public static $definition = [
        'table' => self::TABLE,
        'primary' => 'id_m4p_blog_author',
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_employee' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'display_name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'role' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255],
            'bio' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'image' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128],
            'link_rewrite' => ['type' => self::TYPE_STRING, 'validate' => 'isLinkRewrite', 'required' => true, 'size' => 255],
            'links' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
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
        $before = (string) Db::getInstance()->getValue(
            'SELECT `link_rewrite` FROM `' . _DB_PREFIX_ . self::TABLE . '`'
            . ' WHERE `id_m4p_blog_author` = ' . (int) $this->id . ' AND `id_shop` = ' . (int) $this->id_shop
        );
        if (!parent::update($nullValues)) {
            return false;
        }
        M4pBlogRedirect::remember(M4pBlogRedirect::AUTHOR, (int) $this->id, (int) $this->id_shop, [0 => $before], [0 => (string) $this->link_rewrite]);

        return true;
    }

    public function delete()
    {
        M4pBlogRedirect::forget(M4pBlogRedirect::AUTHOR, (int) $this->id, (int) $this->id_shop);
        Db::getInstance()->update(
            M4pBlogPost::TABLE,
            ['id_author' => 0],
            'id_author = ' . (int) $this->id . ' AND id_shop = ' . (int) $this->id_shop
        );
        if ($this->image) {
            M4pBlogImage::delete((string) $this->image);
        }

        return parent::delete();
    }

    /**
     * Links as "Label | https://url" lines, the form the editor shows them in.
     */
    public function getLinkLines(): string
    {
        $lines = [];
        foreach (self::decodeLinks($this->links) as $link) {
            $lines[] = $link['label'] . ' | ' . $link['url'];
        }

        return implode("\n", $lines);
    }

    public function setLinkLines(string $lines): void
    {
        $links = [];
        foreach (preg_split('/\R/u', $lines) ?: [] as $line) {
            $parts = array_map('trim', explode('|', (string) $line, 2));
            $url = $parts[1] ?? '';
            if ($url === '' || !preg_match('#^(https?://|mailto:|/(?!/))#i', $url)) {
                continue;
            }
            $links[] = [
                'label' => mb_substr(strip_tags($parts[0]), 0, 64),
                'url' => mb_substr($url, 0, 255),
            ];
        }

        $this->links = $links ? (string) json_encode($links, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public static function decodeLinks(?string $json): array
    {
        $decoded = json_decode((string) $json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $links = [];
        foreach ($decoded as $link) {
            if (is_array($link) && !empty($link['url'])) {
                $links[] = [
                    'label' => (string) ($link['label'] ?? $link['url']),
                    'url' => (string) $link['url'],
                ];
            }
        }

        return $links;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(int $idShop, bool $activeOnly = false): array
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE . '`
             WHERE `id_shop` = ' . $idShop . ($activeOnly ? ' AND `active` = 1' : '') . '
             ORDER BY `position` ASC, `display_name` ASC'
        ) ?: [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getByRewrite(string $rewrite, int $idShop): ?array
    {
        $row = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE . '`
             WHERE `id_shop` = ' . $idShop . ' AND `active` = 1
                AND `link_rewrite` = "' . pSQL($rewrite) . '"'
        );

        return $row ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getById(int $idAuthor, int $idShop): ?array
    {
        $row = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE . '`
             WHERE `id_m4p_blog_author` = ' . $idAuthor . ' AND `id_shop` = ' . $idShop
        );

        return $row ?: null;
    }
}
