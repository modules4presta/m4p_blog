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
 * Products under a post, their section title and the table of former URLs.
 */
function upgrade_module_1_3_0(M4p_Blog $module): bool
{
    $db = Db::getInstance();
    $prefix = _DB_PREFIX_;
    $columns = [
        'm4p_blog_post' => ['products', "VARCHAR(255) NOT NULL DEFAULT '' AFTER `position`"],
        'm4p_blog_post_lang' => ['products_title', "VARCHAR(255) NOT NULL DEFAULT '' AFTER `link_rewrite`"],
    ];
    foreach ($columns as $table => [$column, $definition]) {
        $exists = $db->executeS("SHOW COLUMNS FROM `{$prefix}{$table}` LIKE '{$column}'");
        if (!$exists && !$db->execute("ALTER TABLE `{$prefix}{$table}` ADD `{$column}` {$definition}")) {
            return false;
        }
    }

    return $db->execute("CREATE TABLE IF NOT EXISTS `{$prefix}m4p_blog_redirect` (
        `id_shop` INT UNSIGNED NOT NULL DEFAULT 1,
        `entity` VARCHAR(16) NOT NULL,
        `id_lang` INT UNSIGNED NOT NULL DEFAULT 0,
        `old_rewrite` VARCHAR(255) NOT NULL,
        `id_entity` INT UNSIGNED NOT NULL,
        `date_add` DATETIME NOT NULL,
        PRIMARY KEY (`id_shop`, `entity`, `id_lang`, `old_rewrite`),
        KEY `entity` (`id_shop`, `entity`, `id_entity`)
    ) ENGINE=" . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;');
}
