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

class GlotioUrlTranslator
{
    /** @var string */
    private $shop_domain;

    /**
     * UrlTranslator constructor.
     */
    public function __construct()
    {
        $shop_domain = GlotioUtils::getBaseLink();
        $shop_domain = preg_replace("(https?://)", "", $shop_domain);

        $this->shop_domain = $shop_domain;
    }

    /**
     * @param string $original_url
     * @param string $language
     * @return string
     */
    public function translateUrl($original_url, $language)
    {
        // Save global vars state
        $saved_GET    = $_GET;
        $saved_SERVER = $_SERVER;
        $saved_dispatcher_instance = Dispatcher::$instance;

        try {
            $translate_url = $this->__translateUrl($original_url, $language);
            // Restore global vars state
            $_GET    = $saved_GET;
            $_SERVER = $saved_SERVER;
            Dispatcher::$instance = $saved_dispatcher_instance;
            return $translate_url;
        } catch (Exception $ex) {
            // Restore global vars state
            $_GET    = $saved_GET;
            $_SERVER = $saved_SERVER;
            Dispatcher::$instance = $saved_dispatcher_instance;
        }
    }

    /**
     * @param string $original_url
     * @param string $language
     * @return string
     */
    private function __translateUrl($original_url, $language)
    {
        $slug = $this->getSlugFromUrl($original_url);

        // Push new global vars state
        $_GET = array();
        $_SERVER['REQUEST_URI']        = $slug;
        $_SERVER['HTTP_X_REWRITE_URL'] = $slug;
        Dispatcher::$instance = null;

        $controller = Dispatcher::getInstance()->getController();

        // Si al obtener el controller no se encuentra un id es un error y no podemos saber a que url nos referimos
        if (in_array($controller, array('product', 'category', 'supplier', 'manufacturer', 'cms'))) {
            $isset = false;
            foreach (array('id_product', 'id_category', 'id_supplier', 'id_manufacturer', 'id_cms', 'id_cms_category') as $var) {
                if (Tools::getValue($var)) {
                    $isset = true;
                    break;
                }
            }

            if (!$isset) {
                return null;
            }
        }

        if ($controller == 'pagenotfound') {
            return null;
        }

        $id_lang = GlotioPsLanguages::isoToIdLang($language);
        $new_url = Context::getContext()->link->getLanguageLink($id_lang);

        $has_url_domain = strpos($original_url, $this->shop_domain) !== false;
        if (!$has_url_domain) {
            $new_url = preg_replace("(https?://)", "", $new_url);
            $new_url = str_replace($this->shop_domain, '', $new_url);
        }

        $new_url = preg_replace("/(\??id_shop=\d?)/", "", $new_url);

        $new_url = $new_url . $this->getQueryStringFromUrl($original_url);

        if ($original_url[0] === '/' && $new_url[0] !== '/') {
            $new_url = '/' . $new_url;
        }

        return $new_url;
    }

    /**
     * Extrae el slug del anchor pasado
     * @param string $url
     * @return string $slug
     */
    private function getSlugFromUrl($url)
    {
        // Si la url no tiene dominio, parse_url falla con urls incompletas
        if (!preg_match('/.+\..+/', $url)) {
            $url = 'domain.com' . $url;
        }

        // Si la url no tiene especificado el protocolo parse_url falla con urls incompletas
        if (strpos($url, 'http') === false) {
            $url = 'http://' . $url;
        }

        $parts = parse_url($url);
        if ($parts) {
            $slug = '/' . ltrim($parts['path'], '/');
        } else {
            $slug = $url;
        }

        return $slug;
    }

    /**
     * Devuelve de la URL pasada la parte correspondiente al query string ?xxxx y el anchor link #xxxx
     * @param string $url
     * @return string
     */
    private function getQueryStringFromUrl($url)
    {
        $query_string = '';

        if (strpos($url, '?') !== false) {
            $query_string = Tools::substr($url, strpos($url, '?'));
        } elseif (strpos($url, '#') !== false) {
            $query_string = Tools::substr($url, strpos($url, '#'));
        }

        return $query_string;
    }
}
