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
 * Canonical address of a blog page.
 *
 * FrontController::init() builds the address it compares the request against from `php_self`
 * (`getPageLink('post')` and the like). A module controller is not a page of the shop, so that address
 * comes out as `?controller=post` and every friendly address gets redirected away from itself. The
 * controllers know their own address, so the comparison runs against that instead.
 */
trait M4pBlogCanonical
{
    protected function canonicalRedirection(string $canonicalUrl = '')
    {
        parent::canonicalRedirection($this->getCanonicalURL());
    }
}
