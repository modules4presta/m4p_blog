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
class AdminM4pBlogCategoryController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'm4p_blog_category';
        $this->className = 'M4pBlogCategory';
        $this->identifier = 'id_m4p_blog_category';
        // The lang join is written by hand, because the list has to pick the row of the current shop.
        $this->lang = false;
        $this->allow_export = false;
        $this->position_identifier = 'id_m4p_blog_category';
        $this->_defaultOrderBy = 'position';
        $this->_defaultOrderWay = 'ASC';

        parent::__construct();

        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;

        $this->_select = 'b.`name`, b.`link_rewrite`, parent.`name` AS parent_name,
            (SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'm4p_blog_post_category` pc
                WHERE pc.`id_m4p_blog_category` = a.`id_m4p_blog_category` AND pc.`id_shop` = a.`id_shop`) AS posts';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'm4p_blog_category_lang` b
                ON b.`id_m4p_blog_category` = a.`id_m4p_blog_category`
                AND b.`id_shop` = a.`id_shop`
                AND b.`id_lang` = ' . $idLang . '
            LEFT JOIN `' . _DB_PREFIX_ . 'm4p_blog_category_lang` parent
                ON parent.`id_m4p_blog_category` = a.`id_parent`
                AND parent.`id_shop` = a.`id_shop`
                AND parent.`id_lang` = ' . $idLang;
        $this->_where = ' AND a.`id_shop` = ' . $idShop;

        $this->fields_list = [
            'id_m4p_blog_category' => [
                'title' => $this->trans('ID', [], 'Admin.Global'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'name' => [
                'title' => $this->trans('Name', [], 'Admin.Global'),
                'filter_key' => 'b!name',
            ],
            'parent_name' => [
                'title' => $this->trans('Parent category', [], 'Modules.M4pblog.Admin'),
                'filter_key' => 'parent!name',
            ],
            'posts' => [
                'title' => $this->trans('Posts', [], 'Modules.M4pblog.Admin'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
                'orderby' => false,
            ],
            'position' => [
                'title' => $this->trans('Position', [], 'Admin.Global'),
                'align' => 'center',
                'class' => 'fixed-width-sm',
                'position' => 'position',
                'filter_key' => 'a!position',
            ],
            'active' => [
                'title' => $this->trans('Displayed', [], 'Admin.Global'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'class' => 'fixed-width-sm',
                'orderby' => false,
            ],
        ];

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->trans('Delete selected', [], 'Admin.Actions'),
                'confirm' => $this->trans('Delete selected items? Their posts stay, their subcategories move up.', [], 'Admin.Notifications.Warning'),
                'icon' => 'icon-trash',
            ],
        ];
    }

    public function initPageHeaderToolbar(): void
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_category'] = [
                'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                'desc' => $this->trans('Add new category', [], 'Modules.M4pblog.Admin'),
                'icon' => 'process-icon-new',
            ];
        }

        parent::initPageHeaderToolbar();
    }

    public function renderForm()
    {
        /** @var M4pBlogCategory $category */
        $category = $this->loadObject(true);
        $loaded = Validate::isLoadedObject($category);
        $idShop = (int) $this->context->shop->id;
        $excluded = $loaded ? M4pBlogCategory::getSubtreeIds((int) $category->id, $idShop) : [];

        $parents = [['id' => 0, 'name' => $this->trans('Top level', [], 'Modules.M4pblog.Admin')]];
        foreach (M4pBlogCategory::getTree($idShop, (int) $this->context->language->id) as $row) {
            $idCategory = (int) $row['id_m4p_blog_category'];
            if (in_array($idCategory, $excluded, true) || (int) $row['depth'] >= M4pBlogCategory::MAX_DEPTH) {
                continue;
            }
            $parents[] = [
                'id' => $idCategory,
                'name' => str_repeat('— ', (int) $row['depth']) . $row['name'],
            ];
        }

        $inputs = [
            [
                'type' => 'text',
                'label' => $this->trans('Name', [], 'Admin.Global'),
                'name' => 'name',
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
                'hint' => $this->trans('The last part of the address. Left empty, it is built from the name.', [], 'Modules.M4pblog.Admin'),
            ],
            [
                'type' => 'select',
                'label' => $this->trans('Parent category', [], 'Modules.M4pblog.Admin'),
                'name' => 'id_parent',
                'options' => ['query' => $parents, 'id' => 'id', 'name' => 'name'],
            ],
            [
                'type' => 'textarea',
                'label' => $this->trans('Description', [], 'Admin.Global'),
                'name' => 'description',
                'lang' => true,
                'autoload_rte' => true,
            ],
            [
                'type' => 'file',
                'label' => $this->trans('Image', [], 'Admin.Global'),
                'name' => 'image',
                'image' => $loaded && $category->image ? $this->imagePreview((string) $category->image) : null,
            ],
        ];

        if ($loaded && $category->image) {
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

        $this->fields_form = [
            'legend' => [
                'title' => $this->trans('Category', [], 'Admin.Global'),
                'icon' => 'icon-folder',
            ],
            'input' => array_merge($inputs, [
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
                ],
                [
                    'type' => 'switch',
                    'label' => $this->trans('Displayed', [], 'Admin.Global'),
                    'name' => 'active',
                    'values' => [
                        ['id' => 'active_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Admin.Global')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->trans('No', [], 'Admin.Global')],
                    ],
                ],
            ]),
            'submit' => ['title' => $this->trans('Save', [], 'Admin.Actions')],
        ];

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
                $_POST['link_rewrite_' . $idLang] = Tools::str2url((string) Tools::getValue('name_' . $idLang));
            }
        }

        return parent::postProcess();
    }

    protected function copyFromPost(&$object, $table)
    {
        parent::copyFromPost($object, $table);
        if (!$object instanceof M4pBlogCategory) {
            return;
        }

        $object->id_shop = (int) $this->context->shop->id;
        // Without the list, ObjectModel::update() writes the language rows for every shop of the context.
        $object->id_shop_list = [$object->id_shop];
        if ($object->id && in_array((int) $object->id_parent, M4pBlogCategory::getSubtreeIds((int) $object->id, (int) $object->id_shop), true)) {
            $object->id_parent = 0;
        }

        if (!empty($_FILES['image']['tmp_name'])) {
            $name = M4pBlogImage::store($_FILES['image']);
            if ($name === '') {
                $this->errors[] = $this->trans('Upload a JPG, PNG or WebP image up to 10 MB.', [], 'Modules.M4pblog.Admin');
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

    public function ajaxProcessUpdatePositions(): void
    {
        $positions = (array) Tools::getValue($this->table);
        $ids = [];
        foreach ($positions as $position) {
            $parts = explode('_', (string) $position);
            if (isset($parts[2]) && (int) $parts[2]) {
                $ids[] = (int) $parts[2];
            }
        }

        $done = $ids && M4pBlogCategory::updatePositions($ids, (int) $this->context->shop->id);
        header('Content-Type: application/json');
        exit(json_encode(['success' => $done]));
    }

    private function imagePreview(string $name): string
    {
        $sources = M4pBlogImage::sources($name);
        if ($sources === null) {
            return '';
        }

        return '<img src="' . htmlspecialchars($sources['src'], ENT_QUOTES) . '" alt="" width="200">';
    }
}
