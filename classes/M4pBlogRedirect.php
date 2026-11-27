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
 * Former friendly URLs of posts, categories and authors. A changed URL is remembered here, and the
 * front controller answers the old address with a 301 to the current one. An author's URL has no
 * language; its rows carry id_lang 0.
 */
class M4pBlogRedirect
{
    public const TABLE = 'm4p_blog_redirect';

    public const POST = 'post';
    public const CATEGORY = 'category';
    public const AUTHOR = 'author';

    /**
     * Remembers the former URLs of one entity: language id => URL before the save, compared with the URLs
     * after it. The current URLs leave the table, so an address taken back is served again, not redirected.
     *
     * @param array<int, string> $before
     * @param array<int, string> $after
     */
    public static function remember(string $entity, int $idEntity, int $idShop, array $before, array $after): void
    {
        $db = Db::getInstance();
        foreach ($after as $idLang => $current) {
            $current = (string) $current;
            $db->delete(self::TABLE, self::where($entity, $idShop, (int) $idLang, $current));
            $old = (string) ($before[$idLang] ?? '');
            if ($old === '' || $old === $current) {
                continue;
            }
            $db->delete(self::TABLE, self::where($entity, $idShop, (int) $idLang, $old));
            $db->insert(self::TABLE, [
                'id_shop' => $idShop,
                'entity' => pSQL($entity),
                'id_lang' => (int) $idLang,
                'old_rewrite' => pSQL($old),
                'id_entity' => $idEntity,
                'date_add' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Id of the entity that used this URL before, 0 when none did.
     */
    public static function find(string $entity, int $idShop, int $idLang, string $rewrite): int
    {
        return (int) Db::getInstance()->getValue(
            'SELECT `id_entity` FROM `' . _DB_PREFIX_ . self::TABLE . '` WHERE ' . self::where($entity, $idShop, $idLang, $rewrite)
        );
    }

    public static function forget(string $entity, int $idEntity, int $idShop): void
    {
        Db::getInstance()->delete(
            self::TABLE,
            '`entity` = "' . pSQL($entity) . '" AND `id_entity` = ' . $idEntity . ' AND `id_shop` = ' . $idShop
        );
    }

    /**
     * Sends a 301 to the current address and stops.
     */
    public static function moved(string $url): void
    {
        Tools::redirect($url, __PS_BASE_URI__, null, ['HTTP/1.1 301 Moved Permanently']);
    }

    private static function where(string $entity, int $idShop, int $idLang, string $rewrite): string
    {
        return '`id_shop` = ' . $idShop . ' AND `entity` = "' . pSQL($entity) . '" AND `id_lang` = ' . $idLang
            . ' AND `old_rewrite` = "' . pSQL($rewrite) . '"';
    }
}
