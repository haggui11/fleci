<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class TtCategorySlider extends Module implements WidgetInterface
{
    protected $html = '';
    protected $spacer_size = '5';
    protected $_postErrors  = array();

    public function __construct()
    {
        $this->name = 'ttcategoryslider';
        $this->tab = 'front_office_features';
        $this->author = 'TemplateTrip';
        $this->version = '1.0.0';
        $this->need_instance = 0;
        
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('TT - Category Product Slider', array(), 'Modules.ttcategoryslider.Admin');
        $this->description = $this->trans('Display category wise products.', array(), 'Modules.ttcategoryslider.Admin');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->templateFile = 'module:ttcategoryslider/views/templates/front/ttcategoryslider.tpl';
    }
    public function install()
    {
        $arrayDefault = array('3','4','5','6');
        Configuration::updateValue("TT_CATE_SLIDER_LIST", serialize($arrayDefault));
        Configuration::updateValue("TT_CATE_NBR", 8);

        return parent::install() &&
            $this->installDB() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayHome');
    }

    public function installDB()
    {
        $return = true;
        $return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ttcategoryslider` (
			`id_ttcategoryslider` int(11) NOT NULL AUTO_INCREMENT,
			`image` varchar(128) NOT NULL,
			`id_shop` int(10) NOT NULL,
			`name_category` varchar(128) NOT NULL,
			`id_category` int(10)  NOT NULL,		
			PRIMARY KEY (`id_ttcategoryslider`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;');

         return $return;
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->uninstallDB();
    }

    public function uninstallDB($drop_table = true)
    {
        Configuration::deleteByName('TT_CATE_SLIDER_LIST');
        Configuration::deleteByName('TT_CATE_NBR');

        $ret = true;
        if ($drop_table) {
            $ret &=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'ttcategoryslider`');
        }

        return $ret;
    }

    public function getContent()
    {
        $output = '';
        $errors = array();
        if (((bool)Tools::isSubmit('submitTTcategoryslider')) == true) {
            $ids = Tools::getValue('TT_CATE_SLIDER_LIST');
            $nbr = Tools::getValue('TT_CATE_NBR');
            if (empty($ids) || !is_array($ids)) {
                $ids = array(1);
            } else if (!Validate::isInt($nbr) || $nbr <= 0) {
                $errors[] = $this->trans('The number of products is invalid. Please enter a positive number.', array(), 'Modules.ttcategoryslider.Admin');
            }

            if (isset($errors) && count($errors)) {
                $output = $this->displayError(implode('<br />', $errors));
            } else {
                Configuration::updateValue('TT_CATE_NBR', (int)($nbr));
                Configuration::updateValue('TT_CATE_SLIDER_LIST', serialize($ids));
                $output .= $this->displayConfirmation($this->trans('Your settings have been updated.', array(), 'Modules.ttcategoryslider.Admin'));
            }
        }

        if (((bool)Tools::isSubmit('submitCategoryImage')) == true) {
            $category_id = Tools::getValue('category_id');
            $id_lang = (int) Context::getContext()->language->id;
            $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
            $id_shop = (int) Context::getContext()->shop->id;
            $category = new Category((int)$category_id, (int)$id_lang, (int)$id_shop);
            $name_cate = $category->name;
            if ($_FILES['imagethumb']['tmp_name']!='') {
                $upload_path = _PS_MODULE_DIR_.$this->name.'/views/img/';
                $filename = $category_id.'-'.Tools::stripslashes($this->name).'.jpg';
                if (move_uploaded_file($_FILES['imagethumb']['tmp_name'], $upload_path .$filename)) {
                    $cate_exit = $this->getimage($category_id, $id_shop);
                    if ($cate_exit ==null) {
                        $this->addcategoryicon($category_id, $name_cate, $filename, $id_shop);
                        $output .= $this->displayConfirmation($this->trans('Add Image Successfully', array(), 'Modules.ttcategoryslider.Admin'));
                    } else {
                        $this->updatecategoryicon($category_id, $name_cate, $filename);
                        $output .= $this->displayConfirmation($this->trans('Updated Successfully', array(), 'Modules.ttcategoryslider.Admin'));
                    }
                }
            }
        }

        if (Tools::isSubmit('deletettcategoryslider') && Tools::getValue('id_category')) {
            $category_id = Tools::getValue('id_category');
            $id_lang = (int) Context::getContext()->language->id;
            $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
            $id_shop = (int) Context::getContext()->shop->id;
            $category = new Category((int)$category_id, (int)$id_lang, (int)$id_shop);
            $name_cate = $category->name;
            $upload_path = _PS_MODULE_DIR_.$this->name.'/views/img/';
            
            $filename = $category_id.'-'.Tools::stripslashes($this->name).'.jpg';
            $cate_exit = $this->getimage($category_id, $id_shop);
            if ($cate_exit == null) {
                $output .= $this->displayConfirmation($this->trans('No Image Found', array(), 'Modules.ttcategoryslider.Admin'));
            } else {
                $this->deleteCategoryId(Tools::getValue('id_category'));
                unlink($upload_path.$filename);
                $output .= $this->displayConfirmation($this->trans('Deleted Successfully', array(), 'Modules.ttcategoryslider.Admin'));
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        return $output.$this->renderForm().$this->imageForm().$this->renderList();
    }

    protected function renderForm()
    {
        $values = $this->getConfigFormValues(); // Get values form database
        $var = $values['TT_CATE_SLIDER_LIST'];
        if (!is_array($var)) {
            $var = array(1);
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                'title' => $this->trans('Category List', array(), 'Modules.ttcategoryslider.Admin'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'categories',
                        'label' => $this->trans('Categories :', array(), 'Modules.ttcategoryslider.Admin'),
                        'name' => 'TT_CATE_SLIDER_LIST',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Set the categories to be showed.', array(), 'Modules.ttcategoryslider.Admin'),
                        'tree'  => array(
                            'id'                  => 'categories-tree',
                            'selected_categories' => $var,
                            'disabled_categories' => null,
                            'use_search'          => true,
                            'use_checkbox'        => true,
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Number of products to be displayed', array(), 'Modules.ttcategoryslider.Admin'),
                        'name' => 'TT_CATE_NBR',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Set the number of products that you would like to display.', array(), 'Modules.ttcategoryslider.Admin'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Modules.ttcategoryslider.Admin'),
                    'name' => 'submitTTcategoryslider',
                ),
            ),
        );

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTTcategoryslider';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    protected function getConfigFormValues()
    {
        $result =  array(
            'TT_CATE_SLIDER_LIST' =>  Tools::getValue('TT_CATE_SLIDER_LIST', Configuration::get('TT_CATE_SLIDER_LIST')),
            'TT_CATE_NBR' =>  Tools::getValue('TT_CATE_NBR', Configuration::get('TT_CATE_NBR')),
            'category_id' => Tools::getValue('category_id', Configuration::get('category_id')),
        );

        if (!is_array($result['TT_CATE_SLIDER_LIST']) && !empty($result['TT_CATE_SLIDER_LIST'])) {
            $result['TT_CATE_SLIDER_LIST'] = unserialize($result['TT_CATE_SLIDER_LIST']);
        }

        return $result;
    }

    protected function imageForm()
    {
        $id_lang = (int)Context::getContext()->language->id;
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Upload Categories Banner', array(), 'Modules.ttcategoryslider.Admin'),
                    'icon' => 'icon-upload',
                ),
                'input' => array(
                    array(
                        'type' => 'file',
                        'label' => $this->trans('Upload Category Banner:', array(), 'Modules.ttcategoryslider.Admin'),
                        'name' => 'imagethumb',
                        'id' => 'imagethumb',
                    ),
                    array(
                        'type' => 'category_list',
                        'label' => 'Select Category:',
                        'name' => 'category_id',
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Modules.ttcategoryslider.Admin'),
                    'name' => 'submitCategoryImage',
                ),
            ),
        );

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCategoryImage';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'options_image' => $this->getCategoryOptions(1, (int)$id_lang, (int)Shop::getContextShopID()),
        );
        $helper->override_folder = '/';

        return $helper->generateForm(array($fields_form));
    }

    protected function renderList()
    {
        $fields_list = array(
            'id_ttcategoryslider' => array(
                'title' => $this->trans(' ID', array(), 'Modules.ttcategoryslider.Admin'),
                'type' => 'text',
            ),
            'id_shop' => array(
                'title' => $this->trans('ID shop', array(), 'Modules.ttcategoryslider.Admin'),
                'type' => 'text',
            ),
            'image' => array(
                'title' => $this->trans('Icon Category ', array(), 'Modules.ttcategoryslider.Admin'),
                'type' => 'text',
            ),
            'id_category' => array(
                'title' => $this->trans('ID Category', array(), 'Modules.ttcategoryslider.Admin'),
                'type' => 'text',
            ),
            'name_category' => array(
                'title' => $this->trans('Name Category', array(), 'Modules.ttcategoryslider.Admin'),
                'type' => 'text',
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'id_category';
        $helper->actions = array('delete');
        $helper->show_toolbar = false;

        $helper->title = $this->trans('Category Banner List', array(), 'Modules.ttcategoryslider.Admin');
        $helper->table = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $links = $this->getcategoryicon();
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        if (is_array($links) && count($links)) {
            return $helper->generateList($links, $fields_list);
        } else {
            return false;
        }
    }

    protected function getCategoryOptions($id_category = 1, $id_lang = false, $id_shop = false, $space = true)
    {
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
        $category = new Category((int)$id_category, (int)$id_lang, (int)$id_shop);

        if (is_null($category->id)) {
            return;
        }
        if ($space) {
            $children = Category::getChildren((int)$id_category, (int)$id_lang, true, (int)$id_shop);
            $spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$category->level_depth);
        }
        $shop = (object)Shop::getShop((int)$category->getShopID());
        $this->html .= '<option value="'.(int)$category->id.'">'.(isset($spacer) ? $spacer : '').$category->name.'('.$shop->name.')</option>';

        if (isset($children) && count($children)) {
            foreach ($children as $child) {
                $this->getCategoryOptions((int)$child['id_category'], (int)$id_lang, (int)$child['id_shop']);
            }
            return $this->html;
        }
    }

    public function addcategoryicon($category_id, $name_cate, $filename, $id_shop)
    {
        $result = Db::getInstance()->execute('INSERT  INTO `'._DB_PREFIX_.'ttcategoryslider`(`id_category`,`name_category`,`image`,`id_shop`) VALUES ('.(int)$category_id.', \''.pSQL($name_cate).'\', \''.pSQL($filename).'\','.(int)$id_shop.')');
        return $result;
    }

    public function updatecategoryicon($category_id, $name_cate, $filename)
    {
        $result = Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ttcategoryslider` SET `name_category` = \''.pSQL($name_cate).'\',`image` =\''.pSQL($filename).'\' WHERE `id_category` = '.(int)$category_id);
        return $result;
    }

    public static function deleteCategoryId($category_id)
    {
        $sql = 'DELETE FROM`'._DB_PREFIX_.'ttcategoryslider` WHERE `id_category` = '.(int)$category_id;
        Db::getInstance()->execute($sql);
    }
    
    public function getimage($category_id, $id_shop)
    {
        $sql = 'SELECT id_ttcategoryslider ,image,id_category,name_category FROM '._DB_PREFIX_.'ttcategoryslider WHERE id_category = '.(int)$category_id.' and id_shop ='.(int)$id_shop.'' ;
        return Db::getInstance()->executeS($sql);
    }

    public function getcategoryicon()
    {
        $sql = 'SELECT id_ttcategoryslider ,image,id_category,name_category, id_shop FROM '._DB_PREFIX_.'ttcategoryslider';
        return Db::getInstance()->executeS($sql);
    }

    public function hookdisplayHeader($params)
    {
        $this->context->controller->registerJavascript('modules-ttcategoryslider', 'modules/'.$this->name.'/views/js/tt_categorysilder.js', array('position' => 'bottom', 'priority' => 150));
        $this->context->controller->registerStylesheet('modules-ttcategoryslider', 'modules/'.$this->name.'/views/css/ttcategoryslider.css', array('media' => 'all', 'priority' => 150));
    }

    public function renderWidget($hookName, array $configuration)
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId('ttcategoryslider'))) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }

        return $this->fetch($this->templateFile, $this->getCacheId('ttcategoryslider'));
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        $id_lang = (int)Context::getContext()->language->id;
        $id_shop = (int)Context::getContext()->shop->id;
        $config_values = $this->getConfigFormValues();

        $arrayCategory = array();
        $categoryimg = '';
        $catnb = Configuration::get('TT_CATE_NBR');
        foreach ($config_values['TT_CATE_SLIDER_LIST'] as $id) {
            if ($id == 1) {
                continue;
            }
            $category = new Category((int)$id, $id_lang, $id_shop);
            $child_cate = Category::getChildren((int)$id, $id_lang);
            $categoryids = $this->getimage($id, $id_shop);

            $result = $category->getProducts($this->context->language->id, 0, ($catnb ? $catnb : 8));

            $assembler = new ProductAssembler($this->context);
            $presenterFactory = new ProductPresenterFactory($this->context);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );

            $products_for_template = array();

            foreach ($result as $rawProduct) {
                $products_for_template[] = $presenter->present(
                    $presentationSettings,
                    $assembler->assembleProduct($rawProduct),
                    $this->context->language
                );
            }

            foreach ($categoryids as $categoryid) {
                $categoryimg = $categoryid;
            }
            $html = '';
            
            if (!empty($products_for_template)) {
                $arrayCategory[] = array(
                    'id' => $id,
                    'html'=>$html,
                    'name'=> $category->name,
                    'category'=> $category,
                    'description'=> $category->description,
                    'child_cate'=>$child_cate,
                    'cate_id' =>$categoryimg,
                    'product' => $products_for_template,
                    'hookName' => $hookName,
                    'configuration' => $configuration
                );
            }
        }

        return array(
             'ttcategorysliderinfos' => $arrayCategory,
             'image_url' => $this->context->link->getMediaLink(_MODULE_DIR_.'ttcategoryslider/views/img'),
        );
    }
}
