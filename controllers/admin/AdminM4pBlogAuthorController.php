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
class AdminM4pBlogAuthorController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'm4p_blog_author';
        $this->className = 'M4pBlogAuthor';
        $this->identifier = 'id_m4p_blog_author';
        $this->allow_export = false;
        $this->_defaultOrderBy = 'position';
        $this->_defaultOrderWay = 'ASC';

        parent::__construct();

        $this->_select = '(SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'm4p_blog_post` p
            WHERE p.`id_author` = a.`id_m4p_blog_author` AND p.`id_shop` = a.`id_shop`) AS posts';
        $this->_where = ' AND a.`id_shop` = ' . (int) $this->context->shop->id;

        $this->fields_list = [
            'id_m4p_blog_author' => [
                'title' => $this->trans('ID', [], 'Admin.Global'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'display_name' => [
                'title' => $this->trans('Display name', [], 'Modules.M4pblog.Admin'),
            ],
            'role' => [
                'title' => $this->trans('Role', [], 'Modules.M4pblog.Admin'),
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
                'confirm' => $this->trans('Delete selected items? Their posts stay without an author.', [], 'Admin.Notifications.Warning'),
                'icon' => 'icon-trash',
            ],
        ];
    }

    public function initPageHeaderToolbar(): void
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_author'] = [
                'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                'desc' => $this->trans('Add new author', [], 'Modules.M4pblog.Admin'),
                'icon' => 'process-icon-new',
            ];
        }

        parent::initPageHeaderToolbar();
    }

    public function renderForm()
    {
        /** @var M4pBlogAuthor $author */
        $author = $this->loadObject(true);
        $loaded = Validate::isLoadedObject($author);

        $employees = [['id' => 0, 'name' => $this->trans('Not linked', [], 'Modules.M4pblog.Admin')]];
        foreach (Employee::getEmployees() as $employee) {
            $employees[] = [
                'id' => (int) $employee['id_employee'],
                'name' => $employee['firstname'] . ' ' . $employee['lastname'],
            ];
        }

        $inputs = [
            [
                'type' => 'text',
                'label' => $this->trans('Display name', [], 'Modules.M4pblog.Admin'),
                'name' => 'display_name',
                'required' => true,
                'maxlength' => 255,
            ],
            [
                'type' => 'text',
                'label' => $this->trans('Friendly URL', [], 'Admin.Global'),
                'name' => 'link_rewrite',
                'maxlength' => 255,
                'hint' => $this->trans('The last part of the address. Left empty, it is built from the display name.', [], 'Modules.M4pblog.Admin'),
            ],
            [
                'type' => 'text',
                'label' => $this->trans('Role', [], 'Modules.M4pblog.Admin'),
                'name' => 'role',
                'maxlength' => 255,
                'hint' => $this->trans('For example: product specialist.', [], 'Modules.M4pblog.Admin'),
            ],
            [
                'type' => 'textarea',
                'label' => $this->trans('Bio', [], 'Modules.M4pblog.Admin'),
                'name' => 'bio',
                'rows' => 5,
                'autoload_rte' => true,
            ],
            [
                'type' => 'file',
                'label' => $this->trans('Photo', [], 'Modules.M4pblog.Admin'),
                'name' => 'image',
                'image' => $loaded && $author->image ? $this->imagePreview((string) $author->image) : null,
            ],
        ];

        if ($loaded && $author->image) {
            $inputs[] = [
                'type' => 'switch',
                'label' => $this->trans('Remove the photo', [], 'Modules.M4pblog.Admin'),
                'name' => 'image_remove',
                'values' => [
                    ['id' => 'image_remove_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Admin.Global')],
                    ['id' => 'image_remove_off', 'value' => 0, 'label' => $this->trans('No', [], 'Admin.Global')],
                ],
            ];
        }

        $this->fields_form = [
            'legend' => [
                'title' => $this->trans('Author', [], 'Modules.M4pblog.Admin'),
                'icon' => 'icon-user',
            ],
            'input' => array_merge($inputs, [
                [
                    'type' => 'textarea',
                    'label' => $this->trans('Links', [], 'Modules.M4pblog.Admin'),
                    'name' => 'link_lines',
                    'rows' => 4,
                    'hint' => $this->trans('One per line: label | address.', [], 'Modules.M4pblog.Admin'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->trans('Employee', [], 'Admin.Global'),
                    'name' => 'id_employee',
                    'options' => ['query' => $employees, 'id' => 'id', 'name' => 'name'],
                    'desc' => $this->trans('Optional link to a back office account; it changes nothing on the front office.', [], 'Modules.M4pblog.Admin'),
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

        $this->fields_value['link_lines'] = $loaded ? $author->getLinkLines() : '';

        return parent::renderForm();
    }

    /**
     * The friendly URL may be left empty in the form; the required check of the core runs on the posted
     * values, so the fallback has to be written into them before it.
     */
    public function postProcess()
    {
        if (trim((string) Tools::getValue('link_rewrite')) === '') {
            $_POST['link_rewrite'] = Tools::str2url((string) Tools::getValue('display_name'));
        }

        return parent::postProcess();
    }

    protected function copyFromPost(&$object, $table)
    {
        parent::copyFromPost($object, $table);
        if (!$object instanceof M4pBlogAuthor) {
            return;
        }

        $object->id_shop = (int) $this->context->shop->id;
        $object->setLinkLines((string) Tools::getValue('link_lines'));

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

    private function imagePreview(string $name): string
    {
        $sources = M4pBlogImage::sources($name);
        if ($sources === null) {
            return '';
        }

        return '<img src="' . htmlspecialchars($sources['src'], ENT_QUOTES) . '" alt="" width="160">';
    }
}
