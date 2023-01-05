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

class TTPCFUpgrade
{
    // Migrate module data from v1 to v2
    public static function migrateTo20($module)
    {
        if (!Configuration::get($module->settings_prefix.'UPDATED20FREE') && self::checkTableExists('ttproductcountdown')) {
            $product_timers =
                Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'ttproductcountdown`');
            $timers = array_merge($product_timers);

            foreach ($timers as $timer) {
                $ttpc = new TTPCF();

                // common fields
                $ttpc->from = $timer['from'];
                $ttpc->to = $timer['to'];
                // if free version convert dates to utc
                if (!self::checkTableExists('ttproductcountdown_category')) {
                    $tz = Configuration::get('PS_TIMEZONE');
                    $dt_to = new DateTime($timer['to'], new DateTimeZone($tz));
                    $dt_to->setTimezone(new DateTimeZone('UTC'));
                    $dt_from = new DateTime($timer['from'], new DateTimeZone($tz));
                    $dt_from->setTimezone(new DateTimeZone('UTC'));
                    $ttpc->from = $dt_from->format('Y-m-d H:i:s');
                    $ttpc->to = $dt_to->format('Y-m-d H:i:s');
                }
                $ttpc->active = $timer['active'];

                if (isset($timer['id_countdown']) && $timer['id_countdown']) {
                    $id_countdown = $timer['id_countdown'];
                    $id_object = $timer['id_product'];
                    if (isset($timer['id_product_attribute']) && $timer['id_product_attribute']) {
                        $id_object .= '-'.$timer['id_product_attribute'];
                    }
                    $lang_table = 'ttproductcountdown_lang';
                    $id_key = 'id_countdown';
                } else {
                    continue;
                }

                // name
                if (self::checkTableExists($lang_table)) {
                    try {
                        $name_data = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . pSQL($lang_table) . '`
                             WHERE `' . pSQL($id_key) . '` = ' . (int)$id_countdown
                        );
                        foreach ($name_data as $name) {
                            $ttpc->name[$name['id_lang']] = $name['name'];
                        }
                    } catch (Exception $e) {
                        // ignore, update from the free version
                    }
                }

                // validate and save
                $errors = $ttpc->validateAllFields();
                if (!(is_array($errors) && count($errors))) {
                    $ttpc->save();

                    // objects
                    $ttpc->setObjects(array($id_object), 'product');
                }
            }
        }

        $theme = Configuration::get($module->settings_prefix.'THEME');
        if (!file_exists(_PS_MODULE_DIR_ . $module->name . '/views/css/themes/'.$theme)) {
            Configuration::updateValue($module->settings_prefix.'THEME', '1-simple.css');
        }

        Configuration::updateValue($module->settings_prefix.'UPDATED20FREE', 1);
    }

    public static function checkTableExists($table)
    {
        $result = Db::getInstance()->executeS('SHOW TABLES LIKE "'._DB_PREFIX_.pSQL($table).'"');

        if (is_array($result) && count($result)) {
            return true;
        }

        return false;
    }
}
