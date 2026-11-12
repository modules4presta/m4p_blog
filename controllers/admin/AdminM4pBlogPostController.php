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
 * @property M4p_Blog $module
 */
class AdminM4pBlogPostController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'm4p_blog_post';
        $this->className = 'M4pBlogPost';
        $this->identifier = 'id_m4p_blog_post';
        // The lang join is written by hand, because the list has to pick the row of the current shop.
        $this->lang = false;
        $this->allow_export = false;
        $this->_defaultOrderBy = 'date_published';
        $this->_defaultOrderWay = 'DESC';

        parent::__construct();

        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;

        $this->_select = 'b.`title`, b.`link_rewrite`, au.`display_name` AS author, cl.`name` AS category';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'm4p_blog_post_lang` b
                ON b.`id_m4p_blog_post` = a.`id_m4p_blog_post`
                AND b.`id_shop` = a.`id_shop`
                AND b.`id_lang` = ' . $idLang . '
            LEFT JOIN `' . _DB_PREFIX_ . 'm4p_blog_author` au
                ON au.`id_m4p_blog_author` = a.`id_author` AND au.`id_shop` = a.`id_shop`
            LEFT JOIN `' . _DB_PREFIX_ . 'm4p_blog_category_lang` cl
                ON cl.`id_m4p_blog_category` = a.`id_category_default`
                AND cl.`id_shop` = a.`id_shop`
                AND cl.`id_lang` = ' . $idLang;
        $this->_where = ' AND a.`id_shop` = ' . $idShop;

        $this->fields_list = [
            'id_m4p_blog_post' => [
                'title' => $this->trans('ID', [], 'Admin.Global'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'title' => [
                'title' => $this->trans('Title', [], 'Admin.Global'),
                'filter_key' => 'b!title',
            ],
            'status' => [
                'title' => $this->trans('Status', [], 'Admin.Global'),
                'type' => 'select',
                'list' => M4pBlogPost::statuses($this->module),
                'filter_key' => 'a!status',
                'callback' => 'displayStatus',
                'align' => 'center',
                'class' => 'fixed-width-md',
            ],
            'author' => [
                'title' => $this->trans('Author', [], 'Modules.M4pblog.Admin'),
                'filter_key' => 'au!display_name',
            ],
            'category' => [
                'title' => $this->trans('Category', [], 'Admin.Global'),
                'filter_key' => 'cl!name',
            ],
            'date_published' => [
                'title' => $this->trans('Publication date', [], 'Modules.M4pblog.Admin'),
                'type' => 'datetime',
                'align' => 'center',
                'class' => 'fixed-width-lg',
            ],
        ];

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = [
            'publish' => [
                'text' => $this->trans('Publish selected', [], 'Modules.M4pblog.Admin'),
                'icon' => 'icon-check',
            ],
            'unpublish' => [
                'text' => $this->trans('Hide selected', [], 'Modules.M4pblog.Admin'),
                'icon' => 'icon-eye-close',
            ],
            'delete' => [
                'text' => $this->trans('Delete selected', [], 'Admin.Actions'),
                'confirm' => $this->trans('Delete selected items?', [], 'Admin.Notifications.Warning'),
                'icon' => 'icon-trash',
            ],
        ];
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryUI('ui.datepicker');
        $this->addJS(_PS_JS_DIR_ . 'jquery/plugins/timepicker/jquery-ui-timepicker-addon.js');
        $this->addCSS(_PS_JS_DIR_ . 'jquery/plugins/timepicker/jquery-ui-timepicker-addon.css');
    }

    public function initPageHeaderToolbar(): void
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_post'] = [
                'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                'desc' => $this->trans('Add new post', [], 'Modules.M4pblog.Admin'),
                'icon' => 'process-icon-new',
            ];
        }

        parent::initPageHeaderToolbar();
    }

    public function displayStatus(?string $status): string
    {
        return M4pBlogPost::statuses($this->module)[(string) $status] ?? (string) $status;
    }

    public function renderForm()
    {
        /** @var M4pBlogPost $post */
        $post = $this->loadObject(true);
        $idShop = (int) $this->context->shop->id;
        $loaded = Validate::isLoadedObject($post);

        $statuses = [];
        foreach (M4pBlogPost::statuses($this->module) as $value => $label) {
            $statuses[] = ['id' => $value, 'name' => $label];
        }

        $authors = [['id' => 0, 'name' => $this->trans('No author', [], 'Modules.M4pblog.Admin')]];
        foreach (M4pBlogAuthor::getAll($idShop) as $author) {
            $authors[] = ['id' => (int) $author['id_m4p_blog_author'], 'name' => (string) $author['display_name']];
        }

        $categories = [['id' => 0, 'name' => $this->trans('No category', [], 'Modules.M4pblog.Admin')]];
        $checkboxes = [];
        foreach (M4pBlogCategory::getTree($idShop, (int) $this->context->language->id) as $category) {
            $name = str_repeat('— ', (int) $category['depth']) . $category['name'];
            $categories[] = ['id' => (int) $category['id_m4p_blog_category'], 'name' => $name];
            $checkboxes[] = [
                'id_m4p_blog_category' => (int) $category['id_m4p_blog_category'],
                'name' => $name,
                'val' => 1,
            ];
        }

        $inputs = [
            [
                'type' => 'text',
                'label' => $this->trans('Title', [], 'Admin.Global'),
                'name' => 'title',
                'lang' => true,
                'required' => true,
                'maxlength' => 255,
            ],
            [
                'type' => 'text',
                'label' => $this->trans('Friendly URL', [], 'Admin.Global'),
                'name' => 'link_rewrite',
                'lang' => true,
                'maxlength' => 255,
                'hint' => $this->trans('The last part of the address. Left empty, it is built from the title.', [], 'Modules.M4pblog.Admin'),
            ],
            [
                'type' => 'select',
                'label' => $this->trans('Status', [], 'Admin.Global'),
                'name' => 'status',
                'options' => ['query' => $statuses, 'id' => 'id', 'name' => 'name'],
                'desc' => $this->trans('Scheduled: the post appears once the publication date has passed.', [], 'Modules.M4pblog.Admin'),
            ],
            [
                'type' => 'text',
                'label' => $this->trans('Publication date', [], 'Modules.M4pblog.Admin'),
                'name' => 'date_published',
                'class' => 'm4p-blog-datetime fixed-width-lg',
                'hint' => $this->trans('Format YYYY-MM-DD HH:MM:SS.', [], 'Modules.M4pblog.Admin'),
            ],
            [
                'type' => 'select',
                'label' => $this->trans('Author', [], 'Modules.M4pblog.Admin'),
                'name' => 'id_author',
                'options' => ['query' => $authors, 'id' => 'id', 'name' => 'name'],
            ],
            [
                'type' => 'select',
                'label' => $this->trans('Main category', [], 'Modules.M4pblog.Admin'),
                'name' => 'id_category_default',
                'options' => ['query' => $categories, 'id' => 'id', 'name' => 'name'],
                'desc' => $this->trans('Shown in the breadcrumb and on the post card.', [], 'Modules.M4pblog.Admin'),
            ],
        ];

        if ($checkboxes) {
            $inputs[] = [
                'type' => 'checkbox',
                'label' => $this->trans('Other categories', [], 'Modules.M4pblog.Admin'),
                'name' => 'blog_category',
                'values' => ['query' => $checkboxes, 'id' => 'id_m4p_blog_category', 'name' => 'name'],
            ];
        }

        $inputs[] = [
            'type' => 'file',
            'label' => $this->trans('Cover image', [], 'Modules.M4pblog.Admin'),
            'name' => 'image',
            'image' => $loaded && $post->image ? $this->imagePreview((string) $post->image) : null,
            'hint' => $this->trans('JPG, PNG or WebP, up to 10 MB. The module stores WebP copies in three widths.', [], 'Modules.M4pblog.Admin'),
        ];

        if ($loaded && $post->image) {
            $inputs[] = [
                'type' => 'switch',
                'label' => $this->trans('Remove the image', [], 'Modules.M4pblog.Admin'),
                'name' => 'image_remove',
                'values' => [
                    ['id' => 'image_remove_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Admin.Global')],
                    ['id' => 'image_remove_off', 'value' => 0, 'label' => $this->trans('No', [], 'Admin.Global')],
                ],
            ];
        }

        $inputs[] = [
            'type' => 'html',
            'label' => $this->trans('Content', [], 'Modules.M4pblog.Admin'),
            'name' => 'm4p_blog_content',
            'html_content' => $this->renderContentEditor($loaded ? $post : null),
        ];

        $inputs = array_merge($inputs, [
            [
                'type' => 'textarea',
                'label' => $this->trans('Excerpt', [], 'Modules.M4pblog.Admin'),
                'name' => 'excerpt',
                'lang' => true,
                'rows' => 4,
                'hint' => $this->trans('Shown on the post card. Left empty, the beginning of the content is used.', [], 'Modules.M4pblog.Admin'),
            ],
            [
                'type' => 'text',
                'label' => $this->trans('Reading time (minutes)', [], 'Modules.M4pblog.Admin'),
                'name' => 'reading_time',
                'class' => 'fixed-width-sm',
                'hint' => $this->trans('Left at 0, it is counted from the content.', [], 'Modules.M4pblog.Admin'),
            ],
            [
                'type' => 'text',
                'label' => $this->trans('Meta title', [], 'Admin.Global'),
                'name' => 'meta_title',
                'lang' => true,
                'maxlength' => 255,
            ],
            [
                'type' => 'textarea',
                'label' => $this->trans('Meta description', [], 'Admin.Global'),
                'name' => 'meta_description',
                'lang' => true,
                'rows' => 3,
                'maxlength' => 512,
            ],
            [
                'type' => 'text',
                'label' => $this->trans('Position', [], 'Admin.Global'),
                'name' => 'position',
                'class' => 'fixed-width-sm',
                'hint' => $this->trans('Orders posts published at the same moment.', [], 'Modules.M4pblog.Admin'),
            ],
        ]);

        $this->fields_form = [
            'legend' => [
                'title' => $this->trans('Post', [], 'Modules.M4pblog.Admin'),
                'icon' => 'icon-pencil',
            ],
            'input' => $inputs,
            'submit' => ['title' => $this->trans('Save', [], 'Admin.Actions')],
        ];

        $this->fields_value['date_published'] = $loaded && $post->date_published
            ? $post->date_published
            : date('Y-m-d H:i:s');
        foreach ($loaded ? $post->getCategories() : [] as $idCategory) {
            $this->fields_value['blog_category_' . $idCategory] = true;
        }

        return parent::renderForm();
    }

    /**
     * The friendly URL may be left empty in the form; the required check of the core runs on the posted
     * values, so the fallback has to be written into them before it.
     */
    public function postProcess()
    {
        foreach (Language::getIDs(false) as $idLang) {
            $idLang = (int) $idLang;
            if (trim((string) Tools::getValue('link_rewrite_' . $idLang)) === '') {
                $_POST['link_rewrite_' . $idLang] = Tools::str2url((string) Tools::getValue('title_' . $idLang));
            }
        }

        return parent::postProcess();
    }

    protected function copyFromPost(&$object, $table)
    {
        parent::copyFromPost($object, $table);
        if (!$object instanceof M4pBlogPost) {
            return;
        }

        $object->id_shop = (int) $this->context->shop->id;
        // Without the list, ObjectModel::update() writes the language rows for every shop of the context.
        $object->id_shop_list = [$object->id_shop];

        $content = [];
        foreach (Language::getIDs(false) as $idLang) {
            $idLang = (int) $idLang;
            $posted = json_decode((string) Tools::getValue('content_blocks_' . $idLang), true);
            $content[$idLang] = M4pBlogContent::encode(
                M4pBlogContent::sanitize($this->module, is_array($posted) ? $posted : [])
            );
        }
        $object->content = $content;

        if (!(int) $object->reading_time) {
            $object->reading_time = M4pBlogContent::readingTime(
                $content[(int) Configuration::get('PS_LANG_DEFAULT')] ?? reset($content)
            );
        }

        if (!empty($_FILES['image']['tmp_name'])) {
            $name = M4pBlogImage::store($_FILES['image']);
            if ($name === '') {
                $this->errors[] = $this->trans(
                    'Upload a JPG, PNG or WebP image up to 10 MB.',
                    [],
                    'Modules.M4pblog.Admin'
                );
            } else {
                if ($object->image) {
                    M4pBlogImage::delete((string) $object->image);
                }
                $object->image = $name;
            }
        } elseif (Tools::getValue('image_remove') && $object->image) {
            M4pBlogImage::delete((string) $object->image);
            $object->image = '';
        }
    }

    protected function afterAdd($object)
    {
        $this->saveCategories($object);

        return true;
    }

    protected function afterUpdate($object)
    {
        $this->saveCategories($object);

        return true;
    }

    public function processBulkPublish(): void
    {
        $this->setStatus(M4pBlogPost::STATUS_PUBLISHED);
    }

    public function processBulkUnpublish(): void
    {
        $this->setStatus(M4pBlogPost::STATUS_DRAFT);
    }

    private function setStatus(string $status): void
    {
        $ids = array_filter(array_map('intval', (array) $this->boxes));
        if (!$ids) {
            $this->errors[] = $this->trans('Select at least one post.', [], 'Modules.M4pblog.Admin');

            return;
        }

        Db::getInstance()->update(
            M4pBlogPost::TABLE,
            ['status' => pSQL($status), 'date_upd' => date('Y-m-d H:i:s')],
            'id_m4p_blog_post IN (' . implode(',', $ids) . ') AND id_shop = ' . (int) $this->context->shop->id
        );
        $this->confirmations[] = $this->trans('Status updated.', [], 'Modules.M4pblog.Admin');
    }

    private function saveCategories(ObjectModel $object): void
    {
        if (!$object instanceof M4pBlogPost) {
            return;
        }

        $selected = [];
        foreach (M4pBlogCategory::getTree((int) $this->context->shop->id, (int) $this->context->language->id) as $row) {
            $idCategory = (int) $row['id_m4p_blog_category'];
            if (Tools::getValue('blog_category_' . $idCategory)) {
                $selected[] = $idCategory;
            }
        }
        $object->setCategories($selected);
    }

    private function imagePreview(string $name): string
    {
        $sources = M4pBlogImage::sources($name);
        if ($sources === null) {
            return '';
        }

        return '<img src="' . htmlspecialchars($sources['src'], ENT_QUOTES) . '" alt="" width="200">';
    }

    /**
     * The content editor: one pane per language, each holding the blocks of that language and a hidden
     * field with their JSON. views/js/back.js keeps the panes and the JSON in step.
     */
    private function renderContentEditor(?M4pBlogPost $post): string
    {
        $languages = [];
        foreach (Language::getLanguages(false) as $language) {
            $idLang = (int) $language['id_lang'];
            $blocks = $post !== null ? M4pBlogContent::decode(((array) $post->content)[$idLang] ?? null) : [];
            $languages[] = [
                'id' => $idLang,
                'name' => (string) $language['name'],
                'blocks' => $blocks,
                'json' => M4pBlogContent::encode($blocks),
            ];
        }

        $types = [];
        foreach (M4pBlogBlocks::all($this->module) as $block) {
            $types[] = $block->describe();
        }

        $this->context->smarty->assign([
            'm4pBlogEditor' => [
                'languages' => $languages,
                'types' => $types,
                'multilang' => count($languages) > 1,
            ],
            'm4pBlogEditorTexts' => [
                'add' => $this->trans('Add block', [], 'Modules.M4pblog.Admin'),
                'remove' => $this->trans('Remove', [], 'Admin.Actions'),
                'removeConfirm' => $this->trans('Remove this block?', [], 'Modules.M4pblog.Admin'),
                'up' => $this->trans('Move up', [], 'Modules.M4pblog.Admin'),
                'down' => $this->trans('Move down', [], 'Modules.M4pblog.Admin'),
                'hidden' => $this->trans('Hidden', [], 'Modules.M4pblog.Admin'),
                'hide' => $this->trans('Hide on the post page', [], 'Modules.M4pblog.Admin'),
                'empty' => $this->trans('No blocks yet. Add the first one.', [], 'Modules.M4pblog.Admin'),
                'noH1' => $this->trans('Headings in the content start at H2 – the post title is the H1 of the page.', [], 'Modules.M4pblog.Admin'),
            ],
        ]);

        return $this->createTemplate('content-editor.tpl')->fetch();
    }
}
