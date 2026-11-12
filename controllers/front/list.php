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
 * The blog itself: every published post, newest first.
 *
 * @property M4p_Blog $module
 */
class M4p_BlogListModuleFrontController extends ModuleFrontController
{
    use M4pBlogCanonical;

    /** @var string */
    public $php_self = 'list';

    /** @var int */
    private $page = 1;

    public function init(): void
    {
        if (!$this->module->isBlogActive()) {
            Tools::redirect('index.php?controller=404');
        }
        $this->page = max(1, (int) Tools::getValue('page', 1));

        parent::init();
    }

    public function initContent(): void
    {
        parent::initContent();

        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;
        $perPage = $this->module->postsPerPage();
        $total = M4pBlogPost::countPosts($idShop, $idLang);

        $posts = [];
        foreach (M4pBlogPost::getPosts($idShop, $idLang, [
            'limit' => $perPage,
            'offset' => ($this->page - 1) * $perPage,
        ]) as $row) {
            $posts[] = $this->module->presentPost($row, $idLang);
        }

        $this->context->smarty->assign([
            'm4pBlogPosts' => $posts,
            'm4pBlogCategories' => $this->module->presentCategories($idLang),
            'm4pBlogPagination' => $this->module->pagination($total, $this->page, $this->module->blogLink()),
            'm4pBlogTitle' => $this->trans('Blog', [], 'Modules.M4pblog.Shop'),
        ]);

        $this->setTemplate('module:m4p_blog/views/templates/front/list.tpl');
    }

    public function getTemplateVarPage(): array
    {
        $page = parent::getTemplateVarPage();
        $page['meta']['title'] = $this->trans('Blog', [], 'Modules.M4pblog.Shop')
            . ' - ' . Configuration::get('PS_SHOP_NAME');
        $page['meta']['description'] = $this->trans(
            'Posts, guides and news of the shop.',
            [],
            'Modules.M4pblog.Shop'
        );
        $page['body_classes']['m4p-blog'] = true;

        return $page;
    }

    public function getBreadcrumbLinks(): array
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => $this->trans('Blog', [], 'Modules.M4pblog.Shop'),
            'url' => $this->module->blogLink(),
        ];

        return $breadcrumb;
    }

    public function getCanonicalURL(): string
    {
        $url = $this->module->blogLink();

        return $this->page > 1 ? $url . (strpos($url, '?') === false ? '?' : '&') . 'page=' . $this->page : $url;
    }
}
