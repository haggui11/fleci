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

/**
 * Translation util methods.
 */
abstract class GlotioUtils
{
    /**
     *
     * @param int $id_shop
     * @param int $ssl
     * @param bool $relative_protocol
     * @return string
     * @see Link::getBaseLink()
     */
    public static function getBaseLink($id_shop = null, $ssl = null, $relative_protocol = false)
    {
        static $force_ssl = null;

        if ($ssl === null) {
            if ($force_ssl === null) {
                $force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
            }
            $ssl = $force_ssl;
        }

        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $id_shop !== null) {
            $shop = new Shop($id_shop);
        } else {
            $shop = Context::getContext()->shop;
        }

        $ssl_enable = Configuration::get('PS_SSL_ENABLED');
        if ($relative_protocol) {
            $base = '//'.($ssl && $ssl_enable ? $shop->domain_ssl : $shop->domain);
        } else {
            $base = (($ssl && $ssl_enable) ? 'https://'.$shop->domain_ssl : 'http://'.$shop->domain);
        }

        return $base.$shop->getBaseURI();
    }

    /**
     * Extrae una lista de claves de un array.
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function extractKeys(array $array, array $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Indica si el Prestashop es de la versión 1.7 o no.
     * @return bool
     */
    public static function isPs17()
    {
        return version_compare(_PS_VERSION_, '1.7', '>') && version_compare(_PS_VERSION_, '1.8', '<');
    }

    /**
     * Indica si el Prestashop es de la versión 1.6 o no.
     * @return bool
     */
    public static function isPs16()
    {
        return version_compare(_PS_VERSION_, '1.6', '>') && version_compare(_PS_VERSION_, '1.7', '<');
    }

    /**
     * Borrar del path pasado la ruta a Prestashop.
     * @param string $filePath
     * @return string
     */
    public static function removePsFromPath($filePath)
    {
        return str_replace(_PS_ROOT_DIR_ . '/', '', $filePath);
    }

    /**
     * Generates a valid random api key.
     * @return string
     */
    public static function getRandomApiKey()
    {
        $chars  = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $length = 32;

        $new_key_value = '';
        for ($i = 0; $i < $length; $i++) {
            $pos = mt_rand(0, Tools::strlen($chars) - 1);
            $new_key_value .= $chars[$pos];
        }

        return $new_key_value;
    }

    /**
     * Devuelve la primera ruta existente de la lista pasada.
     * @param string[] $paths
     * @return string|null
     */
    public static function getExistingPathFromList(array $paths)
    {
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Create one level parent directory of passed path if it doesn't exist yet.
     * @param string $filePath
     * @return bool
     * @throws GlotioException
     */
    public static function createParentDirOfPathIfNotExists($filePath)
    {
        $dir = pathinfo($filePath, PATHINFO_DIRNAME);
        if (!is_dir($dir)) {
            $parent_dir = pathinfo($dir, PATHINFO_DIRNAME);
            if (is_dir($parent_dir) && is_writable($parent_dir)) {
                return mkdir($dir, 0777);
            } else {
                throw new GlotioException("Cannot create directory " . GlotioUtils::removePsFromPath($dir) . ", path is not writable");
            }
        }
    }
}
