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

/*
 * Every table carries `id_shop` in its primary key: a post, a category and an author belong to one shop of
 * the installation and are not shared. The tags and comments tables are created here but have no back
 * office or front office yet.
 */

$engine = _MYSQL_ENGINE_;
$prefix = _DB_PREFIX_;

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}m4p_blog_post` (
        `id_m4p_blog_post` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_shop` INT UNSIGNED NOT NULL DEFAULT 1,
        `status` VARCHAR(16) NOT NULL DEFAULT 'draft',
        `date_published` DATETIME NOT NULL,
        `id_author` INT UNSIGNED NOT NULL DEFAULT 0,
        `id_category_default` INT UNSIGNED NOT NULL DEFAULT 0,
        `reading_time` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        `image` VARCHAR(128) NOT NULL DEFAULT '',
        `position` INT UNSIGNED NOT NULL DEFAULT 0,
        `products` VARCHAR(255) NOT NULL DEFAULT '',
        `date_add` DATETIME NOT NULL,
        `date_upd` DATETIME NOT NULL,
        PRIMARY KEY (`id_m4p_blog_post`, `id_shop`),
        KEY `listing` (`id_shop`, `status`, `date_published`),
        KEY `author` (`id_shop`, `id_author`),
        KEY `category_default` (`id_shop`, `id_category_default`)
    ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `{$prefix}m4p_blog_post_lang` (
        `id_m4p_blog_post` INT UNSIGNED NOT NULL,
        `id_shop` INT UNSIGNED NOT NULL DEFAULT 1,
        `id_lang` INT UNSIGNED NOT NULL,
        `title` VARCHAR(255) NOT NULL DEFAULT '',
        `excerpt` TEXT,
        `content` MEDIUMTEXT,
        `meta_title` VARCHAR(255) NOT NULL DEFAULT '',
        `meta_description` VARCHAR(512) NOT NULL DEFAULT '',
        `link_rewrite` VARCHAR(255) NOT NULL DEFAULT '',
        `products_title` VARCHAR(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`id_m4p_blog_post`, `id_shop`, `id_lang`),
        KEY `rewrite` (`id_shop`, `id_lang`, `link_rewrite`)
    ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `{$prefix}m4p_blog_category` (
        `id_m4p_blog_category` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_shop` INT UNSIGNED NOT NULL DEFAULT 1,
        `id_parent` INT UNSIGNED NOT NULL DEFAULT 0,
        `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
        `position` INT UNSIGNED NOT NULL DEFAULT 0,
        `image` VARCHAR(128) NOT NULL DEFAULT '',
        `date_add` DATETIME NOT NULL,
        `date_upd` DATETIME NOT NULL,
        PRIMARY KEY (`id_m4p_blog_category`, `id_shop`),
        KEY `tree` (`id_shop`, `id_parent`, `position`)
    ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `{$prefix}m4p_blog_category_lang` (
        `id_m4p_blog_category` INT UNSIGNED NOT NULL,
        `id_shop` INT UNSIGNED NOT NULL DEFAULT 1,
        `id_lang` INT UNSIGNED NOT NULL,
        `name` VARCHAR(255) NOT NULL DEFAULT '',
        `description` TEXT,
        `meta_title` VARCHAR(255) NOT NULL DEFAULT '',
        `meta_description` VARCHAR(512) NOT NULL DEFAULT '',
        `link_rewrite` VARCHAR(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`id_m4p_blog_category`, `id_shop`, `id_lang`),
        KEY `rewrite` (`id_shop`, `id_lang`, `link_rewrite`)
    ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `{$prefix}m4p_blog_post_category` (
        `id_m4p_blog_post` INT UNSIGNED NOT NULL,
        `id_m4p_blog_category` INT UNSIGNED NOT NULL,
        `id_shop` INT UNSIGNED NOT NULL DEFAULT 1,
        PRIMARY KEY (`id_m4p_blog_post`, `id_m4p_blog_category`, `id_shop`),
        KEY `category` (`id_shop`, `id_m4p_blog_category`)
    ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `{$prefix}m4p_blog_author` (
        `id_m4p_blog_author` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_shop` INT UNSIGNED NOT NULL DEFAULT 1,
        `id_employee` INT UNSIGNED NOT NULL DEFAULT 0,
        `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
        `position` INT UNSIGNED NOT NULL DEFAULT 0,
        `display_name` VARCHAR(255) NOT NULL DEFAULT '',
        `role` VARCHAR(255) NOT NULL DEFAULT '',
        `bio` TEXT,
        `image` VARCHAR(128) NOT NULL DEFAULT '',
        `link_rewrite` VARCHAR(255) NOT NULL DEFAULT '',
        `links` TEXT,
        `date_add` DATETIME NOT NULL,
        `date_upd` DATETIME NOT NULL,
        PRIMARY KEY (`id_m4p_blog_author`, `id_shop`),
        KEY `rewrite` (`id_shop`, `link_rewrite`),
        KEY `employee` (`id_shop`, `id_employee`)
    ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `{$prefix}m4p_blog_redirect` (
        `id_shop` INT UNSIGNED NOT NULL DEFAULT 1,
        `entity` VARCHAR(16) NOT NULL,
        `id_lang` INT UNSIGNED NOT NULL DEFAULT 0,
        `old_rewrite` VARCHAR(255) NOT NULL,
        `id_entity` INT UNSIGNED NOT NULL,
        `date_add` DATETIME NOT NULL,
        PRIMARY KEY (`id_shop`, `entity`, `id_lang`, `old_rewrite`),
        KEY `entity` (`id_shop`, `entity`, `id_entity`)
    ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `{$prefix}m4p_blog_tag` (
        `id_m4p_blog_tag` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_shop` INT UNSIGNED NOT NULL DEFAULT 1,
        `id_lang` INT UNSIGNED NOT NULL DEFAULT 0,
        `name` VARCHAR(128) NOT NULL DEFAULT '',
        `link_rewrite` VARCHAR(128) NOT NULL DEFAULT '',
        PRIMARY KEY (`id_m4p_blog_tag`, `id_shop`),
        KEY `rewrite` (`id_shop`, `id_lang`, `link_rewrite`)
    ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `{$prefix}m4p_blog_post_tag` (
        `id_m4p_blog_post` INT UNSIGNED NOT NULL,
        `id_m4p_blog_tag` INT UNSIGNED NOT NULL,
        `id_shop` INT UNSIGNED NOT NULL DEFAULT 1,
        PRIMARY KEY (`id_m4p_blog_post`, `id_m4p_blog_tag`, `id_shop`),
        KEY `tag` (`id_shop`, `id_m4p_blog_tag`)
    ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `{$prefix}m4p_blog_comment` (
        `id_m4p_blog_comment` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_shop` INT UNSIGNED NOT NULL DEFAULT 1,
        `id_m4p_blog_post` INT UNSIGNED NOT NULL DEFAULT 0,
        `id_parent` INT UNSIGNED NOT NULL DEFAULT 0,
        `id_customer` INT UNSIGNED NOT NULL DEFAULT 0,
        `status` VARCHAR(16) NOT NULL DEFAULT 'pending',
        `author_name` VARCHAR(255) NOT NULL DEFAULT '',
        `author_email` VARCHAR(255) NOT NULL DEFAULT '',
        `content` TEXT,
        `ip` VARCHAR(46) NOT NULL DEFAULT '',
        `date_add` DATETIME NOT NULL,
        `date_upd` DATETIME NOT NULL,
        PRIMARY KEY (`id_m4p_blog_comment`, `id_shop`),
        KEY `post` (`id_shop`, `id_m4p_blog_post`, `status`),
        KEY `moderation` (`id_shop`, `status`, `date_add`)
    ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4;",
];
