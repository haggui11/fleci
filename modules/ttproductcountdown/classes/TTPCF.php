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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2021 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
 */

class TTPCF extends ObjectModel
{
    public $from;
    public $to;
    public $active;
    public $name;

    public $from_tz;
    public $to_tz;
    public $to_time;
    public $to_date;

    public static $definition = array(
        'table' => 'ttpcf',
        'primary' => 'id_ttpcf',
        'multilang' => true,
        'fields' => array(
            // Classic fields
            'from' => array('type' => self::TYPE_DATE, 'validate' => 'isPhpDateFormat'),
            'to' => array('type' => self::TYPE_DATE, 'validate' => 'isPhpDateFormat'),
            // Lang fields
            'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        $this->active = $this->isActive();

        $this->loadTz();
    }

    public function isActive()
    {
        if (!$this->id) {
            return true;
        }

        $context = Context::getContext();
        $is_active = Db::getInstance()->getValue(
            'SELECT `active`
             FROM `'._DB_PREFIX_.'ttpcf_shop`
             WHERE `id_ttpcf` = '.(int)$this->id.'
              AND `id_shop` = '.(int)$context->shop->id
        );

        return $is_active;
    }

    public function loadTz()
    {
        $this->from_tz = ($this->from ? date('Y-m-d\TH:i:s\Z', strtotime($this->from)) : '');
        $this->to_tz = ($this->to ? date('Y-m-d\TH:i:s\Z', strtotime($this->to)) : '');
        $this->to_time = ($this->to ? strtotime($this->to.' UTC') * 1000 : 0);
        $this->to_date = ($this->to ? date('d/m/Y', strtotime($this->to.' UTC')) : '');
    }

    public function validateAllFields()
    {
        $errors = array();

        $valid = $this->validateFields(false, true);
        if ($valid !== true) {
            $errors[] = $valid . "\n";
        }
        $valid_lang = $this->validateFieldsLang(false, true);
        if ($valid_lang !== true) {
            $errors[] = $valid_lang . "\n";
        }

        if (!$this->to) {
            $module = Module::getInstanceByName('ttproductcountdown');
            $errors[] = $module->l('The "to" field is required.');
        }

        return $errors;
    }

    public function validateField($field, $value, $id_lang = null, $skip = array(), $human_errors = true)
    {
        return parent::validateField($field, $value, $id_lang, $skip, $human_errors);
    }

    public static function displayFieldName($field, $class = __CLASS__, $htmlentities = true, Context $context = null)
    {
        return '"'.parent::displayFieldName($field, $class, $htmlentities, $context).'"';
    }

    public function save($null_values = false, $auto_date = true)
    {
        // Convert dates from TZ to normal format if necessary
        $dt_from = new DateTime($this->from, new DateTimeZone('UTC'));
        $dt_to = new DateTime($this->to, new DateTimeZone('UTC'));
        $this->from = $dt_from->format('Y-m-d H:i:s');
        $this->to = $dt_to->format('Y-m-d H:i:s');

        // Saving
        $saved = parent::save($null_values, $auto_date);

        if ($saved) {
            // save shop data
            foreach (Shop::getContextListShopID() as $id_shop) {
                Db::getInstance()->execute(
                    'INSERT INTO `'._DB_PREFIX_.'ttpcf_shop`
                     (`id_ttpcf`, `id_shop`, `active`)
                     VALUES
                     ('.(int)$this->id.', '.(int)$id_shop.', '.(int)$this->active.')
                     ON DUPLICATE KEY UPDATE
                      `active` = '.(int)$this->active
                );
            }
        }

        $module = Module::getInstanceByName('ttproductcountdown');
        $module->clearSmartyCache();

        return $saved;
    }

    public function getRelatedProducts()
    {
        $products = array();

        // By products
        $countdown_products = $this->getObjects('product');
        foreach ($countdown_products as $c_product) {
            $id = $c_product['id_object'];
            $ipa = $c_product['id_product_attribute'];
            $products[$id.'-'.$ipa] = array('id_product' => $id, 'id_product_attribute' => $ipa);
        }

        return $products;
    }

    public static function getRelatedProductsIDs($id_ttpc)
    {
        $results = array();

        // from products
        $products = self::getObjectsStatic($id_ttpc, 'product', true);
        $products = self::filterInactiveProducts($products);
        $results = array_merge($results, $products);
        $results = array_unique($results);

        return $results;
    }

    public function delete()
    {
        $result = parent::delete();

        if ($result) {
            Db::getInstance()->execute(
                'DELETE
                 FROM `' . _DB_PREFIX_ . 'ttpcf_shop`
                 WHERE `id_ttpcf` = ' . (int)$this->id
            );
        }

        return $result;
    }

    public static function findTTPC($type, $id_object, $id_product_attribute = 0, $ttpc_module = null, $skip_expired = true)
    {
        $ttpc_module = ($ttpc_module ? $ttpc_module : Module::getInstanceByName('ttproductcountdown'));
        $context = Context::getContext();

        if (is_array($id_object)) {
            $id_object_str = implode(',', array_map('intval', $id_object));
        } else {
            $id_object_str = (int)$id_object;
        }

        $id_ttpc = Db::getInstance()->getValue(
            'SELECT ttpcfo.`id_ttpcf`
             FROM `' . _DB_PREFIX_ . 'ttpcf_object` ttpcfo
             LEFT JOIN `'._DB_PREFIX_.'ttpcf` ttpcf USING (`id_ttpcf`)
             LEFT JOIN `'._DB_PREFIX_.'ttpcf_shop` ttpcfs USING (`id_ttpcf`)
             WHERE ttpcfs.`id_shop` IN (' . implode(',', array_map('intval', Shop::getContextListShopID())) . ')
              AND ttpcfo.`type` = "'.pSQL($type).'"
              AND ttpcfo.`id_object` IN (' . pSQL($id_object_str) . ')
              AND ttpcfo.`id_product_attribute` IN (0, ' . (int)$id_product_attribute.')
             ORDER BY ttpcfo.`id_product_attribute` DESC, ttpcf.`to` ASC, ttpcfo.`id_object` DESC'
        );

        if ($id_ttpc) {
            $ttpc = new TTPCF($id_ttpc, $context->language->id);

            $datetime_current = new DateTime('now', new DateTimeZone('UTC'));
            $datetime_from = new DateTime($ttpc->from, new DateTimeZone('UTC'));
            $datetime_to = new DateTime($ttpc->to, new DateTimeZone('UTC'));

            // Return false if countdown is expired or not started yet
            if ($skip_expired) {
                if ($datetime_from > $datetime_current ||
                    (($datetime_to < $datetime_current) && ($ttpc_module->hide_expired || $ttpc_module->hide_after_end))
                ) {
                    return false;
                }
            }

            if (Validate::isLoadedObject($ttpc)) {
                return $ttpc;
            }
        }

        return null;
    }

    public function setObjects($objects, $type)
    {
        // delete old values
        Db::getInstance()->delete('ttpcf_object', '`id_ttpcf` = ' . (int)$this->id.' AND `type` = "'.pSQL($type).'"');

        // insert new values
        if (is_array($objects)) {
            foreach ($objects as $id_object) {
                $id_product_attribute = 0;
                if (strpos($id_object, '-') !== false) {
                    $ids = explode('-', $id_object);
                    $id_object = $ids[0];
                    $id_product_attribute = $ids[1];
                }

                Db::getInstance()->insert('ttpcf_object', array(
                    'id_object' => (int)$id_object,
                    'id_product_attribute' => (int)$id_product_attribute,
                    'id_ttpcf' => (int)$this->id,
                    'type' => $type,
                ));
            }
        }
    }

    public function getObjects($type, $ids_only = false)
    {
        if (!$this->id) {
            return array();
        }

        $objects = self::getObjectsStatic($this->id, $type, $ids_only);

        return $objects;
    }

    public static function getObjectsStatic($id_ttpc, $type, $ids_only = false)
    {
        $objects = Db::getInstance()->executeS(
            'SELECT *
             FROM `'._DB_PREFIX_.'ttpcf_object`
             WHERE `id_ttpcf` = '.(int)$id_ttpc.'
              AND `type` = "'.pSQL($type).'"'
        );

        if ($ids_only) {
            $tmp = array();
            foreach ($objects as $object) {
                $tmp[] = $object['id_object'];
            }
            $objects = $tmp;
        }

        return $objects;
    }

    public static function getObjectsCount($id_ttpc, $type = '')
    {
        return Db::getInstance()->getValue(
            'SELECT COUNT(`id_ttpcf_object`)
             FROM `'._DB_PREFIX_.'ttpcf_object`
             WHERE `id_ttpcf` = '.(int)$id_ttpc.'
              '.($type ? ' AND `type` = "'.pSQL($type).'" ' : '')
        );
    }

    public static function checkCountdownAppliedToProduct($id_ttpc, $id_product)
    {
        // 1. If the countdown is applied directly to the product
        $as_product = Db::getInstance()->getValue(
            'SELECT `id_ttpcf`
             FROM `'._DB_PREFIX_.'ttpcf_object`
             WHERE `type` = "product"
              AND `id_ttpcf` = '.(int)$id_ttpc.'
              AND `id_object` = '.(int)$id_product
        );
        if ($as_product) {
            return true;
        }

        return false;
    }

    public static function getAll($active = true)
    {
        $timers = self::getCollection('TTPCF');

        $ttpc_list = Db::getInstance()->executeS(
            'SELECT `id_ttpcf`
             FROM `'._DB_PREFIX_.'ttpcf_shop` 
             WHERE `id_shop` IN (' . implode(',', array_map('intval', Shop::getContextListShopID())) . ')'
        );
        $timers->where('id_ttpcf', 'IN', self::sqlArrayToList($ttpc_list, 'id_ttpcf'));

        if ($active) {
            $ttpc_list = Db::getInstance()->executeS(
                'SELECT `id_ttpcf`
                 FROM `'._DB_PREFIX_.'ttpcf_shop` 
                 WHERE `active` = 1
                  AND `id_shop` IN (' . implode(',', array_map('intval', Shop::getContextListShopID())) . ')'
            );
            $timers->where('id_ttpcf', 'IN', self::sqlArrayToList($ttpc_list, 'id_ttpcf'));
        }

        return $timers;
    }

    public static function getCollection($class)
    {
        $context = Context::getContext();
        if (class_exists('PrestaShopCollection')) {
            $collection = new PrestaShopCollection($class, $context->language->id);
        } else {
            $collection = new Collection($class, $context->language->id);
        }

        return $collection;
    }

    /**
     * @param bool $active_only
     * @return array list of shop ids
     */
    public function getShops($active_only = true)
    {
        $result = array();

        $shops = Db::getInstance()->executeS(
            'SELECT `id_shop`
             FROM `'._DB_PREFIX_.'ttpcf_shop`
             WHERE `id_ttpcf` = '.(int)$this->id.
             ($active_only ? ' AND `active` = 1 ' : '')
        );

        foreach ($shops as $shop) {
            $result[] = $shop['id_shop'];
        }

        return $result;
    }

    public static function getShopsStatic($id_ttpc, $active_only = true)
    {
        $ttpc = new TTPCF($id_ttpc);

        return $ttpc->getShops($active_only);
    }

    public function checkCurrentShopDisplay()
    {
        $check = Db::getInstance()->getValue(
            'SELECT `id_ttpcf`
             FROM `'._DB_PREFIX_.'ttpcf_shop`
             WHERE `id_ttpcf` = '.(int)$this->id.'
              `id_shop` IN (' . implode(',', array_map('intval', Shop::getContextListShopID())) . ')'
        );

        return $check;
    }

    public static function sqlArrayToList($array, $column)
    {
        $result = array();

        foreach ($array as $item) {
            $result[] = $item[$column];
        }

        if (!count($result)) {
            $result = array(0);
        }

        return $result;
    }

    public static function filterInactiveProducts($ids, $id_shop = null)
    {
        if (!$ids) {
            return $ids;
        }

        $result = array();
        $context = Context::getContext();
        $id_shop = ($id_shop ? $id_shop : $context->shop->id);

        $raw_data = Db::getInstance()->executeS(
            'SELECT `id_product`
             FROM `'._DB_PREFIX_.'product_shop`
             WHERE `id_shop` = '.(int)$id_shop.'
              AND `active` = 1
              AND `id_product` IN ('.implode(',', array_map('intval', $ids)).')'
        );

        foreach ($raw_data as $product) {
            $result[] = $product['id_product'];
        }

        return $result;
    }
}
