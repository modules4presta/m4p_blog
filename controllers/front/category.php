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
 * Posts of one blog category.
 *
 * @property M4p_Blog $module
 */
class M4p_BlogCategoryModuleFrontController extends ModuleFrontController
{
    use M4pBlogCanonical;

    /** @var string */
    public $php_self = 'category';

    /** @var array<string, mixed> */
    private $category = [];

    /** @var int */
    private $page = 1;

    public function init(): void
    {
        $rewrite = (string) Tools::getValue('rewrite');
        $category = $this->module->isBlogActive()
            ? M4pBlogCategory::getByRewrite($rewrite, (int) $this->context->shop->id, (int) $this->context->language->id)
            : null;
        if ($category === null) {
            Tools::redirect('index.php?controller=404');
        }
        $this->category = $category;
        $this->page = max(1, (int) Tools::getValue('page', 1));

        parent::init();
    }

    public function initContent(): void
    {
        parent::initContent();

        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;
        $idCategory = (int) $this->category['id_m4p_blog_category'];
        $perPage = $this->module->postsPerPage();
        $total = M4pBlogPost::countPosts($idShop, $idLang, ['category' => $idCategory]);

        $posts = [];
        foreach (M4pBlogPost::getPosts($idShop, $idLang, [
            'category' => $idCategory,
            'limit' => $perPage,
            'offset' => ($this->page - 1) * $perPage,
        ]) as $row) {
            $posts[] = $this->module->presentPost($row, $idLang);
        }

        $this->context->smarty->assign([
            'm4pBlogPosts' => $posts,
            'm4pBlogCategories' => $this->module->presentCategories($idLang),
            'm4pBlogPagination' => $this->module->pagination($total, $this->page, $this->categoryUrl()),
            'm4pBlogTitle' => (string) $this->category['name'],
            'm4pBlogDescription' => (string) $this->category['description'],
            'm4pBlogImage' => $this->category['image']
                ? M4pBlogImage::sources((string) $this->category['image'])
                : null,
        ]);

        $this->setTemplate('module:m4p_blog/views/templates/front/category.tpl');
    }

    public function getTemplateVarPage(): array
    {
        $page = parent::getTemplateVarPage();
        $page['meta']['title'] = (string) ($this->category['meta_title'] ?: $this->category['name']);
        $page['meta']['description'] = (string) $this->category['meta_description'];
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
        $breadcrumb['links'][] = [
            'title' => (string) $this->category['name'],
            'url' => $this->categoryUrl(),
        ];

        return $breadcrumb;
    }

    public function getCanonicalURL(): string
    {
        $url = $this->categoryUrl();

        return $this->page > 1 ? $url . (strpos($url, '?') === false ? '?' : '&') . 'page=' . $this->page : $url;
    }

    private function categoryUrl(): string
    {
        return $this->module->categoryLink((string) $this->category['link_rewrite']);
    }
}
