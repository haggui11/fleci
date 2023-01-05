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

class GlotioTranslationUrlsResourceV1 extends GlotioRestApiResource
{
    public function get(array $params)
    {
        list($urls, $langs) = $this->validateParameters();

        $urlTranslator = new GlotioUrlTranslator();

        $translatedUrls = array();

        foreach ($urls as $url) {
            foreach ($langs as $lang) {
                $translatedUrls[] = array(
                    'language' => $lang,
                    'from_url' => $url,
                    'to_url'   => $urlTranslator->translateUrl($url, $lang),
                );
            }
        }

        return $translatedUrls;
    }

    /**
     * @return array
     * @throws GlotioException
     */
    private function validateParameters()
    {
        $urls = Tools::getValue('urls', array());
        if (!$urls) {
            throw new GlotioException("Query parameter [urls] must exists");
        }

        $urls = is_array($urls) ? $urls : array($urls);
        foreach ($urls as &$url) {
            $valid_url = filter_var($url, FILTER_SANITIZE_URL);
            if (!$valid_url) {
                throw new GlotioException("Invalid URL [$url] found in query parameter [urls]");
            }

            $url = $valid_url;
        }


        $langs = Tools::getValue('langs', array());
        if (!$langs) {
            throw new GlotioException("Query parameter [langs] must exists");
        }

        $langs = is_array($langs) ? $langs : array($langs);
        foreach ($langs as &$lang) {
            $valid_lang = filter_var($lang, FILTER_SANITIZE_STRING);
            if (!$valid_lang) {
                throw new GlotioException("Invalid language [$lang] found in query parameter [langs]");
            }

            $lang = $valid_lang;
        }


        return array($urls, $langs);
    }
}
