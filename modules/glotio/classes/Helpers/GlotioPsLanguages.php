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

abstract class GlotioPsLanguages
{
    /** @var bool */
    private static $loaded = false;

    /** @var array */
    private static $languages = array();

    /** @var array */
    private static $iso_by_id_lang = array();

    /** @var array */
    private static $id_lang_by_iso = array();

    /** @var array */
    private static $locale_by_iso = array();

    /** @var array */
    private static $iso_by_locale = array();

    /**
     * Carga una sóla vez los datos de los idiomas presentes en la instalación de Prestashop.
     * @param bool $reload
     */
    public static function loadLanguages($reload = false)
    {
        if (self::$loaded && !$reload) {
            return;
        }

        self::$languages      = array();
        self::$iso_by_id_lang = array();
        self::$id_lang_by_iso = array();
        self::$locale_by_iso  = array();
        self::$iso_by_locale  = array();

        $default_id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $id_shop         = (int) Context::getContext()->shop->id;

        $query = new DbQuery();
        $query
            ->select('l.*, ls.id_shop')
            ->from('lang', 'l')
            ->leftJoin('lang_shop', 'ls', "l.id_lang = ls.id_lang AND ls.id_shop = {$id_shop}")
        ;
        $result = Db::getInstance()->executeS($query);

        foreach ($result as $language) {
            $id_lang  = (int) $language['id_lang'];
            $iso_code = $language['iso_code'];
            $locale   = isset($language['locale']) ? $language['locale'] : $iso_code;

            self::$languages[] = array(
                'id'      => $id_lang,
                'name'    => $language['name'],
                'enabled' => ((int) $language['active']) === 1 && isset($language['id_shop']),
                'iso'     => $iso_code,
                'locale'  => Tools::strtolower(isset($language['locale']) ? $language['locale'] : $language['language_code']),
                'locale_alt' => Tools::strtolower($language['language_code']),
                'default' => ($default_id_lang == $id_lang),
            );

            self::$iso_by_id_lang[$id_lang]  = $iso_code;
            self::$id_lang_by_iso[$iso_code] = $id_lang;
            self::$locale_by_iso[$iso_code]  = $locale;
            self::$iso_by_locale[$locale]    = $iso_code;
        }

        self::$loaded = true;
    }

    /**
     * Return information about languages installed in this Prestashop.
     * @return array
     */
    public static function getLanguagesData()
    {
        self::loadLanguages();

        return self::$languages;
    }

    /**
     * @param int $id_lang
     * @return string
     */
    public static function idLangToIso($id_lang)
    {
        self::loadLanguages();

        return isset(self::$iso_by_id_lang[$id_lang]) ? self::$iso_by_id_lang[$id_lang] : null;
    }

    /**
     * @param string $iso
     * @return int
     */
    public static function isoToIdLang($iso)
    {
        self::loadLanguages();

        return isset(self::$id_lang_by_iso[$iso]) ? self::$id_lang_by_iso[$iso] : null;
    }

    /**
     * @param string $iso
     * @return string
     */
    public static function isoToLocale($iso)
    {
        self::loadLanguages();

        return isset(self::$locale_by_iso[$iso]) ? self::$locale_by_iso[$iso] : null;
    }

    /**
     * @param string $locale
     * @return string
     */
    public static function localeToIso($locale)
    {
        self::loadLanguages();

        return isset(self::$iso_by_locale[$locale]) ? self::$iso_by_locale[$locale] : null;
    }

    /**
     * @return int[]
     */
    public static function getIds()
    {
        self::loadLanguages();

        return array_values(self::$id_lang_by_iso);
    }

    /**
     * @return string[]
     */
    public static function getIsos()
    {
        self::loadLanguages();

        return array_values(self::$iso_by_id_lang);
    }

    /**
     * @return string[]
     */
    public static function getLocales()
    {
        self::loadLanguages();

        return array_values(self::$locale_by_iso);
    }
}
