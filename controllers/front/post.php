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
 * One post. Its title is the only H1 of the page; headings inside the content start at H2.
 *
 * @property M4p_Blog $module
 */
class M4p_BlogPostModuleFrontController extends ModuleFrontController
{
    use M4pBlogCanonical;

    /** @var string */
    public $php_self = 'post';

    /** @var array<string, mixed> */
    private $post = [];

    public function init(): void
    {
        $rewrite = (string) Tools::getValue('rewrite');
        $post = $this->module->isBlogActive()
            ? M4pBlogPost::getByRewrite($rewrite, (int) $this->context->shop->id, (int) $this->context->language->id)
            : null;
        if ($post === null || (string) $post['title'] === '') {
            Tools::redirect('index.php?controller=404');
        }
        $this->post = $post;

        parent::init();
    }

    public function initContent(): void
    {
        parent::initContent();

        $idLang = (int) $this->context->language->id;
        $presented = $this->module->presentPost($this->post, $idLang);

        $this->context->smarty->assign([
            'm4pBlogPost' => $presented,
            'm4pBlogContent' => $this->module->renderContent($this->post['content'] ?? null),
            'm4pBlogCategories' => $this->module->presentCategories($idLang),
        ]);

        $this->setTemplate('module:m4p_blog/views/templates/front/post.tpl');
    }

    public function getTemplateVarPage(): array
    {
        $page = parent::getTemplateVarPage();
        $page['meta']['title'] = (string) ($this->post['meta_title'] ?: $this->post['title']);
        $page['meta']['description'] = (string) ($this->post['meta_description']
            ?: mb_substr(M4pBlogHtml::toText((string) $this->post['excerpt']), 0, 300));
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

        $category = $this->post['id_category_default']
            ? M4pBlogCategory::getById(
                (int) $this->post['id_category_default'],
                (int) $this->context->shop->id,
                (int) $this->context->language->id
            )
            : null;
        if ($category !== null) {
            $breadcrumb['links'][] = [
                'title' => (string) $category['name'],
                'url' => $this->module->categoryLink((string) $category['link_rewrite']),
            ];
        }

        $breadcrumb['links'][] = [
            'title' => (string) $this->post['title'],
            'url' => $this->getCanonicalURL(),
        ];

        return $breadcrumb;
    }

    public function getCanonicalURL(): string
    {
        return $this->module->postLink((string) $this->post['link_rewrite']);
    }
}
