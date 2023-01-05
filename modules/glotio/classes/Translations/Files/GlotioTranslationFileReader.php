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

class GlotioTranslationFileReader
{
    /**
     * Devuelve el array con las traducciones del fichero de traducciones pasado.
     * @param string $globalVar
     * @param string $filePath
     * @return array Formato: clave translation_key, valor translated_text
     */
    public static function getTranslations($globalVar, $filePath)
    {
        $translations = array();

        if (file_exists($filePath)) {
            // Evitar manipular la variable global
            $saveGlobalVarValue = isset($$globalVar) ? $$globalVar : array();
            $$globalVar         = array();

            @include($filePath);
            if (isset($$globalVar) && is_array($$globalVar)) {
                foreach ($$globalVar as $translation_key => $translated_text) {
                    $translated_text = stripslashes($translated_text);

                    $translations[$translation_key] = $translated_text;
                }
            }

            $$globalVar = $saveGlobalVarValue;
        }

        return $translations;
    }
}
