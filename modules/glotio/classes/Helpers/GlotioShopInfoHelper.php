<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2021 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

abstract class GlotioShopInfoHelper
{
    /**
     * Return a unique ID for this Prestashop installation.
     * @return string
     */
    public static function getUniqueInstallationId()
    {
        return sha1('glotio'._COOKIE_KEY_);
    }

    /**
     * Return generic information about this shop and module version.
     * @return array
     */
    public static function getProjectData()
    {
        return array(
            'platform_id'      => self::getUniqueInstallationId(),
            'name'             => Configuration::get('PS_SHOP_NAME'),
            'description'      => self::getShopDescription(),
            'verticals'        => self::getVerticals(),
            'url'              => GlotioUtils::getBaseLink(),
            'platform'         => GlotioConstants::getPrestashopVersionConstant(),
            'platform_version' => _PS_VERSION_,
            'module_version'   => Module::getInstanceByName('glotio')->version,
            'endpoint'         => self::getEndpoint(),
            'meta'             => self::getProjectMetaData(),
        );
    }

    /**
     * Return endpoint URI to access to module API. Could have GET parameters if link rewrite is not enabled.
     * @return string
     */
    public static function getEndpoint()
    {
        $is_dispatcher_overrided = file_exists(_PS_OVERRIDE_DIR_.'classes/Dispatcher.php');

        if (Configuration::get('PS_REWRITING_SETTINGS') && !$is_dispatcher_overrided) {
            return GlotioUtils::getBaseLink() . 'glotio-api/';
        } else {
            return GlotioUtils::getBaseLink() . 'index.php?fc=module&module=glotio&controller=api&id_shop='. (int) Context::getContext()->shop->id;
        }
    }

    /**
     * Return metadata about this shop.
     * @return array
     */
    public static function getProjectMetaData()
    {
        $data = array(
            'id_shop'            => (int) Context::getContext()->shop->id,
            'id_default_lang'    => (int) Configuration::get('PS_LANG_DEFAULT'),
            'default_lang'       => Language::getIsoById((int) Configuration::get('PS_LANG_DEFAULT')),
            'theme'              => _THEME_NAME_,
            'multishop'          => Shop::isFeatureActive(),
            'debug'              => _PS_MODE_DEV_,
            'canonical_redirect' => (int) Configuration::get('PS_CANONICAL_REDIRECT'),
            'link_rewrite'       => ((int) Configuration::get('PS_REWRITING_SETTINGS')) === 1,
            'maintenance'        => ((int) Configuration::get('PS_SHOP_ENABLE')) === 0,
            'store_latitude'     => Configuration::get('PS_STORES_CENTER_LAT'),
            'store_longitude'    => Configuration::get('PS_STORES_CENTER_LONG'),
            'store_country'      => Country::getIsoById((int) Configuration::get('PS_SHOP_COUNTRY_ID')),
            'currencies'         => self::getCurrencies(),
            'modules'            => GlotioPsModules::getModulesInfo(),
            'product_image_types'=> self::getProductImageTypes(),
            'tables'             => self::getTables(),
        );

        if (Shop::isFeatureActive()) {
            $data['default_shop_url'] = GlotioUtils::getBaseLink($data['id_shop']);
        }

        return $data;
    }

    /**
     * @return array
     */
    private static function getProductImageTypes()
    {
        $image_types = ImageType::getImagesTypes('products');
        if (!$image_types) {
            return array();
        }

        $return = array();
        foreach ($image_types as $image_type) {
            $return[] = array(
                'name'   => $image_type['name'],
                'widht'  => $image_type['width'],
                'height' => $image_type['height'],
            );
        }

        return $return;
    }

    /**
     * @return string[]
     */
    public static function getTables()
    {
        static $tables;

        if (is_null($tables)) {
            $tables = array();

            try {
                $results = Db::getInstance()->executeS('SHOW TABLES');
                foreach ($results as $result) {
                    $key = array_keys($result);
                    $tables[] = $result[$key[0]];
                }
            } catch (Exception $ex) {
                // Ignore error
            }
        }

        return $tables;
    }

    /**
     * @return array
     */
    public static function getCurrencies()
    {
        $id_default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');

        $sql = (new DbQuery())
            ->select('c.id_currency, c.iso_code, cs.conversion_rate')
            ->from('currency', 'c')
            ->innerJoin('currency_shop', 'cs', 'c.`id_currency` = cs.`id_currency`')
            ->where('cs.`id_shop` = ' . (int) Context::getContext()->shop->id)
            ->where('c.`deleted` = 0')
            ->where('c.`active` = 1');
        $rows = Db::getInstance()->executeS($sql);
        if (!$rows) {
            $rows = array();
        }

        foreach ($rows as &$row) {
            $row['default'] = $row['id_currency'] == $id_default_currency;
        }

        return $rows;
    }

    /**
     * @param string $table
     * @return bool
     */
    public static function tableExists($table)
    {
        return in_array(_DB_PREFIX_ . $table, self::getTables());
    }

    /**
     * Return information about PHP configuration like timezone or memory limit.
     * @return array
     */
    public static function getPhpConfigData()
    {
        return array(
            'timestamp'          => date('c'),
            'timezone'           => Configuration::get('PS_TIMEZONE'),
            'php_version'        => PHP_VERSION,
            //'php_extensions' => get_loaded_extensions(),
            'memory_limit'       => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'max_execution_time_original' => isset($GLOBALS['glotio_max_execution_time_original']) ? $GLOBALS['glotio_max_execution_time_original'] : null,
            'post_max_size'      => ini_get('post_max_size'),
        );
    }

    /**
     * @return string|null
     */
    public static function getShopDescription()
    {
        $description = null;

        $id_lang_meta = Language::getIdByIso('en');
        if (!$id_lang_meta) {
            $id_lang_meta = (int) Configuration::get('PS_LANG_DEFAULT');
        }

        $meta = Meta::getMetaByPage('index', $id_lang_meta);
        if ($meta) {
            $description = $meta['title'];
            if (!$description) {
                $description = $meta['description'];
            }
        }

        return $description;
    }

    /**
     * @return string
     */
    public static function getVerticals()
    {
        /** @see \AdminPreferencesControllerCore */
        $activities = array(
            1  => 'Lingerie, Adult',
            2  => 'Animals',
            3  => 'Art, Culture',
            4  => 'Babies',
            5  => 'Beauty',
            6  => 'Cars',
            7  => 'Computers',
            8  => 'Downloads',
            9  => 'Fashion, Accessories',
            10 => 'Flowers, Gifts, Crafts',
            11 => 'Food, Beverage',
            12 => 'HiFi, Photo, Video',
            13 => 'Home, Garden',
            14 => 'Home Appliances',
            15 => 'Jewelry',
            16 => 'Mobile, Telecom',
            17 => 'Services',
            18 => 'Shoes, Accessories',
            19 => 'Sport, Entertainment',
            20 => 'Travel'
        );

        $vertical = '';

        $activity_id = (int) Configuration::get('PS_SHOP_ACTIVITY');

        if (isset($activities[$activity_id])) {
            $vertical = $activities[$activity_id];
            $vertical = str_replace(', ', ',', $vertical);
        }

        return $vertical;
    }
}
