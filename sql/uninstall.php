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

$prefix = _DB_PREFIX_;

return [
    "DROP TABLE IF EXISTS `{$prefix}m4p_blog_post_tag`;",
    "DROP TABLE IF EXISTS `{$prefix}m4p_blog_tag`;",
    "DROP TABLE IF EXISTS `{$prefix}m4p_blog_comment`;",
    "DROP TABLE IF EXISTS `{$prefix}m4p_blog_post_category`;",
    "DROP TABLE IF EXISTS `{$prefix}m4p_blog_post_lang`;",
    "DROP TABLE IF EXISTS `{$prefix}m4p_blog_post`;",
    "DROP TABLE IF EXISTS `{$prefix}m4p_blog_category_lang`;",
    "DROP TABLE IF EXISTS `{$prefix}m4p_blog_category`;",
    "DROP TABLE IF EXISTS `{$prefix}m4p_blog_author`;",
];
