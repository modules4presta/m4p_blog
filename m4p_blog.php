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

require_once __DIR__ . '/classes/M4pBlogCanonical.php';
require_once __DIR__ . '/classes/M4pBlogHtml.php';
require_once __DIR__ . '/classes/M4pBlogImage.php';
require_once __DIR__ . '/classes/M4pBlogFile.php';
require_once __DIR__ . '/classes/M4pBlogRedirect.php';
require_once __DIR__ . '/classes/Block/M4pBlogBlock.php';
require_once __DIR__ . '/classes/Block/M4pBlogHtmlBlock.php';
require_once __DIR__ . '/classes/Block/M4pBlogFactsBlock.php';
require_once __DIR__ . '/classes/Block/M4pBlogListBlock.php';
require_once __DIR__ . '/classes/Block/M4pBlogStepsBlock.php';
require_once __DIR__ . '/classes/Block/M4pBlogNoteBlock.php';
require_once __DIR__ . '/classes/Block/M4pBlogFaqBlock.php';
require_once __DIR__ . '/classes/Block/M4pBlogFileBlock.php';
require_once __DIR__ . '/classes/Block/M4pBlogBlocks.php';
require_once __DIR__ . '/classes/M4pBlogContent.php';
require_once __DIR__ . '/classes/M4pBlogPost.php';
require_once __DIR__ . '/classes/M4pBlogCategory.php';
require_once __DIR__ . '/classes/M4pBlogAuthor.php';

class M4p_Blog extends Module
{
    public const CONFIG_ACTIVE = 'M4P_BLOG_ACTIVE';
    public const CONFIG_PER_PAGE = 'M4P_BLOG_PER_PAGE';
    public const CONFIG_HOME_COUNT = 'M4P_BLOG_HOME_COUNT';
    public const CONFIG_HOME_CATEGORY = 'M4P_BLOG_HOME_CATEGORY';
    public const CONFIG_INTRO = 'M4P_BLOG_INTRO';
    /** Translatable texts of the home page block, stored per language. */
    public const CONFIG_HOME_TEXTS = ['M4P_BLOG_HOME_TITLE', 'M4P_BLOG_HOME_INTRO_TITLE', 'M4P_BLOG_HOME_INTRO'];

    /**
     * Segments of the friendly URLs. Module routes are the same in every language of the shop
     * (Dispatcher::loadRoutes), so they are constants, not translated strings.
     */
    public const ROUTE_BASE = 'blog';
    public const ROUTE_CATEGORY = 'category';
    public const ROUTE_AUTHOR = 'author';

    public const HOOK_JSONLD = 'displayM4pBlogJsonLd';
    public const HOOK_POST_AFTER_CONTENT = 'displayM4pBlogPostAfterContent';

    public const TAB_PARENT = 'AdminM4pBlog';
    public const TAB_POST = 'AdminM4pBlogPost';
    public const TAB_CATEGORY = 'AdminM4pBlogCategory';
    public const TAB_AUTHOR = 'AdminM4pBlogAuthor';

    private const CONTROLLERS = ['list', 'category', 'author', 'post'];

    public function __construct()
    {
        $this->name = 'm4p_blog';
        $this->tab = 'content_management';
        $this->version = '1.3.0';
        $this->author = 'Modules4Presta';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->trans('Blog', [], 'Modules.M4pblog.Admin');
        $this->description = $this->trans(
            'Shop blog with posts built from content blocks, categories and authors.',
            [],
            'Modules.M4pblog.Admin'
        );
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function install(): bool
    {
        return parent::install()
            && $this->runSql('install')
            && $this->installHooks()
            && $this->registerHook(['moduleRoutes', 'header', 'actionAdminControllerSetMedia', 'displayHome'])
            && $this->installTabs()
            && Configuration::updateValue(self::CONFIG_ACTIVE, 1)
            && Configuration::updateValue(self::CONFIG_PER_PAGE, 9)
            && Configuration::updateValue(self::CONFIG_HOME_COUNT, 2);
    }

    public function uninstall(): bool
    {
        Configuration::deleteByName(self::CONFIG_ACTIVE);
        Configuration::deleteByName(self::CONFIG_PER_PAGE);
        Configuration::deleteByName(self::CONFIG_HOME_COUNT);
        Configuration::deleteByName(self::CONFIG_HOME_CATEGORY);
        foreach (self::CONFIG_HOME_TEXTS as $key) {
            Configuration::deleteByName($key);
        }

        return $this->uninstallTabs() && $this->runSql('uninstall') && parent::uninstall();
    }

    public function getContent(): string
    {
        $output = '';
        if (Tools::isSubmit('submit' . $this->name)) {
            $perPage = (int) Tools::getValue(self::CONFIG_PER_PAGE);
            $homeCount = (int) Tools::getValue(self::CONFIG_HOME_COUNT);
            if ($perPage < 1 || $perPage > 100) {
                $output .= $this->displayError(
                    $this->trans('Posts per page: a number between 1 and 100.', [], 'Modules.M4pblog.Admin')
                );
            } elseif ($homeCount < 0 || $homeCount > 12) {
                $output .= $this->displayError(
                    $this->trans('Posts on the home page: a number between 0 and 12.', [], 'Modules.M4pblog.Admin')
                );
            } else {
                Configuration::updateValue(self::CONFIG_ACTIVE, (bool) Tools::getValue(self::CONFIG_ACTIVE));
                Configuration::updateValue(self::CONFIG_PER_PAGE, $perPage);
                Configuration::updateValue(self::CONFIG_HOME_COUNT, $homeCount);
                Configuration::updateValue(self::CONFIG_HOME_CATEGORY, (int) Tools::getValue(self::CONFIG_HOME_CATEGORY));
                foreach (self::textKeys() as $key) {
                    $values = [];
                    foreach (Language::getIDs(false) as $idLang) {
                        $values[$idLang] = trim((string) Tools::getValue($key . '_' . $idLang));
                    }
                    Configuration::updateValue($key, $values);
                }
                $output .= $this->displayConfirmation(
                    $this->trans('Settings updated', [], 'Modules.M4pblog.Admin')
                );
            }
        }

        return $output . $this->renderSettingsForm();
    }

    public function postsPerPage(): int
    {
        return max(1, (int) Configuration::get(self::CONFIG_PER_PAGE));
    }

    public function isBlogActive(): bool
    {
        return (bool) Configuration::get(self::CONFIG_ACTIVE);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function hookModuleRoutes(): array
    {
        $rewrite = ['regexp' => '[_a-zA-Z0-9\pL\pS-]+', 'param' => 'rewrite'];
        $params = ['fc' => 'module', 'module' => $this->name];

        return [
            'module-' . $this->name . '-list' => [
                'controller' => 'list',
                'rule' => self::ROUTE_BASE,
                'keywords' => [],
                'params' => $params,
            ],
            'module-' . $this->name . '-category' => [
                'controller' => 'category',
                'rule' => self::ROUTE_BASE . '/' . self::ROUTE_CATEGORY . '/{rewrite}',
                'keywords' => ['rewrite' => $rewrite],
                'params' => $params,
            ],
            'module-' . $this->name . '-author' => [
                'controller' => 'author',
                'rule' => self::ROUTE_BASE . '/' . self::ROUTE_AUTHOR . '/{rewrite}',
                'keywords' => ['rewrite' => $rewrite],
                'params' => $params,
            ],
            'module-' . $this->name . '-post' => [
                'controller' => 'post',
                'rule' => self::ROUTE_BASE . '/{rewrite}',
                'keywords' => ['rewrite' => $rewrite],
                'params' => $params,
            ],
        ];
    }

    public function hookDisplayHome(): string
    {
        $count = (int) Configuration::get(self::CONFIG_HOME_COUNT);
        if (!$this->isBlogActive() || $count < 1) {
            return '';
        }
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;
        $filters = ['limit' => $count];
        $idCategory = (int) Configuration::get(self::CONFIG_HOME_CATEGORY);
        if ($idCategory > 0) {
            $filters['category'] = $idCategory;
        }
        $posts = array_map(
            fn (array $row): array => $this->presentPost($row, $idLang),
            M4pBlogPost::getPosts($idShop, $idLang, $filters)
        );
        if ($posts === []) {
            return '';
        }

        $this->context->smarty->assign([
            'm4pBlogHome' => [
                'title' => (string) Configuration::get('M4P_BLOG_HOME_TITLE', $idLang),
                'intro_title' => (string) Configuration::get('M4P_BLOG_HOME_INTRO_TITLE', $idLang),
                'intro' => (string) Configuration::get('M4P_BLOG_HOME_INTRO', $idLang),
                'all_url' => $this->context->link->getModuleLink($this->name, 'list'),
                'posts' => $posts,
            ],
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/hook/home.tpl');
    }

    public function hookHeader(): void
    {
        $page = $this->context->controller->php_self ?? '';
        if (in_array($page, self::CONTROLLERS, true) || ($page === 'index' && (int) Configuration::get(self::CONFIG_HOME_COUNT) > 0)) {
            $this->context->controller->registerStylesheet(
                'm4p-blog',
                'modules/' . $this->name . '/views/css/front.css',
                ['media' => 'all', 'priority' => 100]
            );
        }
    }

    public function hookActionAdminControllerSetMedia(): void
    {
        if (Tools::getValue('controller') !== self::TAB_POST) {
            return;
        }
        foreach (['css/back.css', 'js/back.js'] as $file) {
            $url = $this->_path . 'views/' . $file . '?v=' . (int) filemtime(__DIR__ . '/views/' . $file);
            strpos($file, 'css') === 0
                ? $this->context->controller->addCSS($url)
                : $this->context->controller->addJS($url);
        }
    }

    public function blogLink(?int $idLang = null): string
    {
        return $this->context->link->getModuleLink($this->name, 'list', [], null, $idLang);
    }

    public function postLink(string $rewrite, ?int $idLang = null): string
    {
        return $this->context->link->getModuleLink($this->name, 'post', ['rewrite' => $rewrite], null, $idLang);
    }

    public function categoryLink(string $rewrite, ?int $idLang = null): string
    {
        return $this->context->link->getModuleLink($this->name, 'category', ['rewrite' => $rewrite], null, $idLang);
    }

    public function authorLink(string $rewrite, ?int $idLang = null): string
    {
        return $this->context->link->getModuleLink($this->name, 'author', ['rewrite' => $rewrite], null, $idLang);
    }

    /**
     * The content blocks of a post as markup, in the order they were saved.
     */
    public function renderContent(?string $json): string
    {
        $out = '';
        foreach (M4pBlogContent::views($this, $json) as $view) {
            $this->context->smarty->assign([
                'm4pBlogSettings' => $view['settings'],
                'm4pBlogData' => $view['data'],
            ]);
            $out .= $this->fetch($view['block']->template());
        }

        return $out;
    }

    /**
     * Products chosen for a post, presented like a product listing, in the chosen order; products that
     * are disabled or hidden from the catalogue are left out.
     *
     * @param int[] $ids
     *
     * @return array<int, mixed>
     */
    public function presentProducts(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $visible = array_map('intval', array_column(Db::getInstance()->executeS(
            'SELECT `id_product` FROM `' . _DB_PREFIX_ . 'product_shop`
             WHERE `id_shop` = ' . (int) $this->context->shop->id . ' AND `active` = 1
                AND `visibility` IN ("both", "catalog") AND `id_product` IN (' . implode(',', array_map('intval', $ids)) . ')'
        ) ?: [], 'id_product'));
        $raw = array_map(
            static fn (int $id): array => ['id_product' => $id],
            array_values(array_intersect($ids, $visible))
        );
        if ($raw === []) {
            return [];
        }

        $link = $this->context->link;
        $assembler = new ProductAssembler($this->context);
        $factory = new ProductPresenterFactory($this->context);
        $settings = $factory->getPresentationSettings();
        $presenter = new PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter(
            new PrestaShop\PrestaShop\Adapter\Image\ImageRetriever($link),
            $link,
            new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter(),
            new PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever(),
            $this->context->getTranslator()
        );
        $assembled = method_exists($assembler, 'assembleProducts')
            ? $assembler->assembleProducts($raw)
            : array_map([$assembler, 'assembleProduct'], $raw);

        $products = [];
        foreach ($assembled as $product) {
            $products[] = $presenter->present($settings, $product, $this->context->language);
        }

        return $products;
    }

    /**
     * Up to three latest posts of the main category of a post, without the post itself.
     *
     * @param array<string, mixed> $post
     *
     * @return array<int, array<string, mixed>>
     */
    public function relatedPosts(array $post, int $idLang, int $limit = 3): array
    {
        $idCategory = (int) ($post['id_category_default'] ?? 0);
        if ($idCategory < 1) {
            return [];
        }

        return array_map(
            fn (array $row): array => $this->presentPost($row, $idLang),
            M4pBlogPost::getPosts((int) $this->context->shop->id, $idLang, [
                'category' => $idCategory,
                'exclude' => (int) $post['id_m4p_blog_post'],
                'limit' => $limit,
            ])
        );
    }

    /**
     * A post row as the listing and the post page templates read it.
     *
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    public function presentPost(array $row, int $idLang): array
    {
        $idShop = (int) $this->context->shop->id;
        $author = $row['id_author'] ? M4pBlogAuthor::getById((int) $row['id_author'], $idShop) : null;
        $category = $row['id_category_default']
            ? M4pBlogCategory::getById((int) $row['id_category_default'], $idShop, $idLang)
            : null;
        $excerpt = trim(M4pBlogHtml::toText((string) ($row['excerpt'] ?? '')));
        if ($excerpt === '') {
            $excerpt = self::shorten(M4pBlogContent::toText($row['content'] ?? null), 200);
        }

        return [
            'id' => (int) $row['id_m4p_blog_post'],
            'title' => (string) $row['title'],
            'excerpt' => $excerpt,
            'url' => $this->postLink((string) $row['link_rewrite'], $idLang),
            'image' => $row['image'] ? M4pBlogImage::sources((string) $row['image']) : null,
            'date_published' => (string) $row['date_published'],
            'date' => Tools::displayDate((string) $row['date_published']),
            'reading_time' => (int) $row['reading_time'],
            'author' => $author ? $this->presentAuthor($author) : null,
            'category' => $category ? [
                'name' => (string) $category['name'],
                'url' => $this->categoryLink((string) $category['link_rewrite'], $idLang),
            ] : null,
        ];
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    public function presentAuthor(array $row): array
    {
        return [
            'id' => (int) $row['id_m4p_blog_author'],
            'display_name' => (string) $row['display_name'],
            'initials' => self::initials((string) $row['display_name']),
            'role' => (string) $row['role'],
            'bio' => (string) $row['bio'],
            'image' => $row['image'] ? M4pBlogImage::sources((string) $row['image']) : null,
            'url' => $this->authorLink((string) $row['link_rewrite']),
            'links' => M4pBlogAuthor::decodeLinks($row['links'] ?? null),
        ];
    }

    /**
     * Categories of the blog menu, with the number of published posts in each.
     *
     * @return array<int, array<string, mixed>>
     */
    public function presentCategories(int $idLang): array
    {
        $idShop = (int) $this->context->shop->id;
        $counts = M4pBlogCategory::getPostCounts($idShop, $idLang);
        $categories = [];
        $tree = M4pBlogCategory::getTree($idShop, $idLang, true);
        foreach ($tree as $i => $row) {
            $categories[] = [
                'id' => (int) $row['id_m4p_blog_category'],
                'name' => (string) $row['name'],
                'depth' => (int) $row['depth'],
                'url' => $this->categoryLink((string) $row['link_rewrite'], $idLang),
                'total' => $counts[(int) $row['id_m4p_blog_category']] ?? 0,
                'has_children' => isset($tree[$i + 1]) && (int) $tree[$i + 1]['depth'] > (int) $row['depth'],
            ];
        }

        return $categories;
    }

    /**
     * Numbered pagination: the first and last page, the current one with its neighbours, gaps as spacers.
     *
     * @return array{pages: int, current: int, previous: ?string, next: ?string, links: list<array{page: ?int, url: ?string, current: bool}>}
     */
    public function pagination(int $total, int $current, string $baseUrl): array
    {
        $pages = (int) ceil($total / $this->postsPerPage());
        $pageUrl = static function (int $page) use ($baseUrl): string {
            $separator = strpos($baseUrl, '?') === false ? '?' : '&';

            return $page > 1 ? $baseUrl . $separator . 'page=' . $page : $baseUrl;
        };

        $links = [];
        $previousPage = 0;
        for ($page = 1; $page <= $pages; ++$page) {
            if ($page !== 1 && $page !== $pages && abs($page - $current) > 1) {
                continue;
            }
            if ($page - $previousPage > 1) {
                $links[] = ['page' => null, 'url' => null, 'current' => false];
            }
            $links[] = ['page' => $page, 'url' => $pageUrl($page), 'current' => $page === $current];
            $previousPage = $page;
        }

        return [
            'pages' => $pages,
            'current' => $current,
            'previous' => $current > 1 ? $pageUrl($current - 1) : null,
            'next' => $current < $pages ? $pageUrl($current + 1) : null,
            'links' => $links,
        ];
    }

    /**
     * First letters of the first two words of a name, for an avatar without a photo.
     */
    private static function initials(string $name): string
    {
        $initials = '';
        foreach (array_slice(preg_split('/\s+/u', trim($name)) ?: [], 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }

        return $initials;
    }

    private static function shorten(string $text, int $length): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        $cut = mb_substr($text, 0, $length);
        $space = mb_strrpos($cut, ' ');

        return ($space !== false ? mb_substr($cut, 0, $space) : $cut) . '…';
    }

    /**
     * Configuration keys stored per language.
     *
     * @return list<string>
     */
    private static function textKeys(): array
    {
        return array_merge([self::CONFIG_INTRO], self::CONFIG_HOME_TEXTS);
    }

    private function renderSettingsForm(): string
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submit' . $this->name;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->languages = $this->context->controller->getLanguages();
        $helper->fields_value = [
            self::CONFIG_ACTIVE => (int) Configuration::get(self::CONFIG_ACTIVE),
            self::CONFIG_PER_PAGE => $this->postsPerPage(),
            self::CONFIG_HOME_COUNT => (int) Configuration::get(self::CONFIG_HOME_COUNT),
            self::CONFIG_HOME_CATEGORY => (int) Configuration::get(self::CONFIG_HOME_CATEGORY),
        ];
        foreach (self::textKeys() as $key) {
            foreach (Language::getIDs(false) as $idLang) {
                $helper->fields_value[$key][$idLang] = (string) Configuration::get($key, $idLang);
            }
        }
        $categories = [['id' => 0, 'name' => $this->trans('All posts', [], 'Modules.M4pblog.Admin')]];
        foreach (M4pBlogCategory::getTree((int) $this->context->shop->id, (int) Configuration::get('PS_LANG_DEFAULT')) as $category) {
            $categories[] = ['id' => (int) $category['id_m4p_blog_category'], 'name' => str_repeat('– ', max(0, (int) $category['depth'])) . $category['name']];
        }

        return $helper->generateForm([[
            'form' => [
                'legend' => [
                    'title' => $this->trans('Settings', [], 'Modules.M4pblog.Admin'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Blog visible', [], 'Modules.M4pblog.Admin'),
                        'name' => self::CONFIG_ACTIVE,
                        'desc' => $this->trans('Off: the blog pages answer 404 and the posts stay in the back office.', [], 'Modules.M4pblog.Admin'),
                        'values' => [
                            ['id' => 'active_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Admin.Global')],
                            ['id' => 'active_off', 'value' => 0, 'label' => $this->trans('No', [], 'Admin.Global')],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Posts per page', [], 'Modules.M4pblog.Admin'),
                        'name' => self::CONFIG_PER_PAGE,
                        'class' => 'fixed-width-sm',
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'lang' => true,
                        'label' => $this->trans('Blog page introduction', [], 'Modules.M4pblog.Admin'),
                        'name' => self::CONFIG_INTRO,
                        'desc' => $this->trans('Under the title of the blog page. Empty: no introduction.', [], 'Modules.M4pblog.Admin'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Posts on the home page', [], 'Modules.M4pblog.Admin'),
                        'name' => self::CONFIG_HOME_COUNT,
                        'class' => 'fixed-width-sm',
                        'desc' => $this->trans('0 hides the block on the home page.', [], 'Modules.M4pblog.Admin'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->trans('Home page posts from', [], 'Modules.M4pblog.Admin'),
                        'name' => self::CONFIG_HOME_CATEGORY,
                        'options' => ['query' => $categories, 'id' => 'id', 'name' => 'name'],
                    ],
                    [
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Home page block title', [], 'Modules.M4pblog.Admin'),
                        'name' => 'M4P_BLOG_HOME_TITLE',
                    ],
                    [
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Introduction title', [], 'Modules.M4pblog.Admin'),
                        'name' => 'M4P_BLOG_HOME_INTRO_TITLE',
                        'desc' => $this->trans('A panel beside the posts with the title, the text and a link to all posts. Empty title: no panel.', [], 'Modules.M4pblog.Admin'),
                    ],
                    [
                        'type' => 'textarea',
                        'lang' => true,
                        'label' => $this->trans('Introduction text', [], 'Modules.M4pblog.Admin'),
                        'name' => 'M4P_BLOG_HOME_INTRO',
                    ],
                ],
                'submit' => ['title' => $this->trans('Save', [], 'Admin.Actions')],
            ],
        ]]);
    }

    private function runSql(string $step): bool
    {
        $file = __DIR__ . '/sql/' . $step . '.php';
        if (!file_exists($file)) {
            return true;
        }
        foreach ((array) (include $file) as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    private function installHooks(): bool
    {
        $hooks = [
            self::HOOK_JSONLD => [
                'Blog structured data',
                'Structured data of a blog page; m4p_blog renders none itself (m4p_blog).',
            ],
            self::HOOK_POST_AFTER_CONTENT => [
                'After the blog post content',
                'Below the content of a post, above the author box (m4p_blog).',
            ],
        ];

        foreach ($hooks as $name => [$title, $description]) {
            if (Hook::getIdByName($name)) {
                continue;
            }
            $hook = new Hook();
            $hook->name = $name;
            $hook->title = $title;
            $hook->description = $description;
            if (!$hook->add()) {
                return false;
            }
        }

        return true;
    }

    private function installTabs(): bool
    {
        $tabs = [
            self::TAB_PARENT => [$this->trans('Blog', [], 'Modules.M4pblog.Admin'), 0, 'rss_feed'],
            self::TAB_POST => [$this->trans('Posts', [], 'Modules.M4pblog.Admin'), self::TAB_PARENT, 'description'],
            self::TAB_CATEGORY => [$this->trans('Categories', [], 'Admin.Global'), self::TAB_PARENT, 'folder'],
            self::TAB_AUTHOR => [$this->trans('Authors', [], 'Modules.M4pblog.Admin'), self::TAB_PARENT, 'person'],
        ];

        foreach ($tabs as $className => [$name, $parent, $icon]) {
            if (Tab::getIdFromClassName($className)) {
                continue;
            }

            $tab = new Tab();
            $tab->class_name = $className;
            $tab->module = $this->name;
            $tab->id_parent = $parent === 0 ? 0 : (int) Tab::getIdFromClassName($parent);
            $tab->active = 1;
            $tab->icon = $icon;
            foreach (Language::getLanguages(false) as $language) {
                $tab->name[(int) $language['id_lang']] = $name;
            }

            if (!$tab->add()) {
                return false;
            }
        }

        return true;
    }

    private function uninstallTabs(): bool
    {
        foreach ([self::TAB_POST, self::TAB_CATEGORY, self::TAB_AUTHOR, self::TAB_PARENT] as $className) {
            $idTab = (int) Tab::getIdFromClassName($className);
            if ($idTab) {
                (new Tab($idTab))->delete();
            }
        }

        return true;
    }
}
