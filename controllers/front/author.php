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
 * Posts of one author, with the author box above them.
 *
 * @property M4p_Blog $module
 */
class M4p_BlogAuthorModuleFrontController extends ModuleFrontController
{
    use M4pBlogCanonical;

    /** @var string */
    public $php_self = 'author';

    /** @var array<string, mixed> */
    private $author = [];

    /** @var int */
    private $page = 1;

    public function init(): void
    {
        $rewrite = (string) Tools::getValue('rewrite');
        $author = $this->module->isBlogActive()
            ? M4pBlogAuthor::getByRewrite($rewrite, (int) $this->context->shop->id)
            : null;
        if ($author === null) {
            Tools::redirect('index.php?controller=404');
        }
        $this->author = $author;
        $this->page = max(1, (int) Tools::getValue('page', 1));

        parent::init();
    }

    public function initContent(): void
    {
        parent::initContent();

        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;
        $idAuthor = (int) $this->author['id_m4p_blog_author'];
        $perPage = $this->module->postsPerPage();
        $total = M4pBlogPost::countPosts($idShop, $idLang, ['author' => $idAuthor]);

        $posts = [];
        foreach (M4pBlogPost::getPosts($idShop, $idLang, [
            'author' => $idAuthor,
            'limit' => $perPage,
            'offset' => ($this->page - 1) * $perPage,
        ]) as $row) {
            $posts[] = $this->module->presentPost($row, $idLang);
        }

        $this->context->smarty->assign([
            'm4pBlogPosts' => $posts,
            'm4pBlogCategories' => $this->module->presentCategories($idLang),
            'm4pBlogPagination' => $this->module->pagination($total, $this->page, $this->authorUrl()),
            'm4pBlogAuthor' => $this->module->presentAuthor($this->author),
            'm4pBlogTitle' => (string) $this->author['display_name'],
        ]);

        $this->setTemplate('module:m4p_blog/views/templates/front/author.tpl');
    }

    public function getTemplateVarPage(): array
    {
        $page = parent::getTemplateVarPage();
        $page['meta']['title'] = (string) $this->author['display_name'];
        $page['meta']['description'] = mb_substr(
            M4pBlogHtml::toText((string) $this->author['bio']),
            0,
            300
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
        $breadcrumb['links'][] = [
            'title' => (string) $this->author['display_name'],
            'url' => $this->authorUrl(),
        ];

        return $breadcrumb;
    }

    public function getCanonicalURL(): string
    {
        $url = $this->authorUrl();

        return $this->page > 1 ? $url . (strpos($url, '?') === false ? '?' : '&') . 'page=' . $this->page : $url;
    }

    private function authorUrl(): string
    {
        return $this->module->authorLink((string) $this->author['link_rewrite']);
    }
}
