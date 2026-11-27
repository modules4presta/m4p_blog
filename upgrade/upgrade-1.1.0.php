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
 * The home page block: two latest posts by default.
 */
function upgrade_module_1_1_0(M4p_Blog $module): bool
{
    return $module->registerHook('displayHome')
        && Configuration::updateValue(M4p_Blog::CONFIG_HOME_COUNT, 2);
}
