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
require_once __DIR__ . '/classes/Block/M4pBlogBlock.php';
require_once __DIR__ . '/classes/Block/M4pBlogHtmlBlock.php';
require_once __DIR__ . '/classes/Block/M4pBlogBlocks.php';
require_once __DIR__ . '/classes/M4pBlogContent.php';
require_once __DIR__ . '/classes/M4pBlogPost.php';
require_once __DIR__ . '/classes/M4pBlogCategory.php';
require_once __DIR__ . '/classes/M4pBlogAuthor.php';

class M4p_Blog extends Module
{
    public const CONFIG_ACTIVE = 'M4P_BLOG_ACTIVE';
    public const CONFIG_PER_PAGE = 'M4P_BLOG_PER_PAGE';

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
        $this->version = '1.0.1';
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
            && $this->registerHook(['moduleRoutes', 'header', 'actionAdminControllerSetMedia'])
            && $this->installTabs()
            && Configuration::updateValue(self::CONFIG_ACTIVE, 1)
            && Configuration::updateValue(self::CONFIG_PER_PAGE, 9);
    }

    public function uninstall(): bool
    {
        Configuration::deleteByName(self::CONFIG_ACTIVE);
        Configuration::deleteByName(self::CONFIG_PER_PAGE);

        return $this->uninstallTabs() && $this->runSql('uninstall') && parent::uninstall();
    }

    public function getContent(): string
    {
        $output = '';
        if (Tools::isSubmit('submit' . $this->name)) {
            $perPage = (int) Tools::getValue(self::CONFIG_PER_PAGE);
            if ($perPage < 1 || $perPage > 100) {
                $output .= $this->displayError(
                    $this->trans('Posts per page: a number between 1 and 100.', [], 'Modules.M4pblog.Admin')
                );
            } else {
                Configuration::updateValue(self::CONFIG_ACTIVE, (bool) Tools::getValue(self::CONFIG_ACTIVE));
                Configuration::updateValue(self::CONFIG_PER_PAGE, $perPage);
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

    public function hookHeader(): void
    {
        if (in_array($this->context->controller->php_self ?? '', self::CONTROLLERS, true)) {
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
        foreach (M4pBlogCategory::getTree($idShop, $idLang, true) as $row) {
            $categories[] = [
                'id' => (int) $row['id_m4p_blog_category'],
                'name' => (string) $row['name'],
                'depth' => (int) $row['depth'],
                'url' => $this->categoryLink((string) $row['link_rewrite'], $idLang),
                'total' => $counts[(int) $row['id_m4p_blog_category']] ?? 0,
            ];
        }

        return $categories;
    }

    /**
     * @return array{pages: int, current: int, previous: ?string, next: ?string}
     */
    public function pagination(int $total, int $current, string $baseUrl): array
    {
        $pages = (int) ceil($total / $this->postsPerPage());
        $separator = strpos($baseUrl, '?') === false ? '?' : '&';

        return [
            'pages' => $pages,
            'current' => $current,
            'previous' => $current > 1 ? $baseUrl . ($current > 2 ? $separator . 'page=' . ($current - 1) : '') : null,
            'next' => $current < $pages ? $baseUrl . $separator . 'page=' . ($current + 1) : null,
        ];
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

    private function renderSettingsForm(): string
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submit' . $this->name;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->fields_value = [
            self::CONFIG_ACTIVE => (int) Configuration::get(self::CONFIG_ACTIVE),
            self::CONFIG_PER_PAGE => $this->postsPerPage(),
        ];

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
