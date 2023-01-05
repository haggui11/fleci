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

abstract class GlotioTranslationsParser
{
    /**
     * Parsea los textos del fichero pasado.
     * @param string $filepath
     * @param string $type_translation
     * @param string $module_name
     * @return array
     */
    public static function parseFile($filepath, $type_translation, $module_name = '')
    {
        $content = file_get_contents($filepath);

        // Ignorar ficheros con este texto
        if (strpos($content, 'IGNORE_THIS_FILE_FOR_TRANSLATION') !== false) {
            return array();
        }

        $type_file = pathinfo($filepath, PATHINFO_EXTENSION);

        return self::userParseFile($content, $type_translation, $type_file, $module_name);
    }

    /**
     * This method parse a file by type of translation and type file.
     * @see \AdminTranslationsController::userParseFile()
     * @param $content
     * @param $type_translation : front, back, errors, modules...
     * @param string|bool $type_file : (tpl|php)
     * @param string $module_name : name of the module
     * @return array
     */
    private static function userParseFile($content, $type_translation, $type_file, $module_name = '')
    {
        switch ($type_translation) {
            case 'front':
                // Parsing file in Front office
                $regex = '/\{l\s*s=([\'\"])'._PS_TRANS_PATTERN_.'\1(\s*sprintf=.*)?(\s*js=1)?\s*\}/U';
                break;

            case 'back':
                // Parsing file in Back office
                if ($type_file == 'php') {
                    $regex = '/this->l\((\')'._PS_TRANS_PATTERN_.'\'[\)|\,]/U';
                } elseif ($type_file == 'specific') {
                    $regex = '/Translate::getAdminTranslation\((\')'._PS_TRANS_PATTERN_.'\'(?:,.*)*\)/U';
                } else {
                    $regex = '/\{l\s*s\s*=([\'\"])'._PS_TRANS_PATTERN_.'\1(\s*sprintf=.*)?(\s*js=1)?(\s*slashes=1)?.*\}/U';
                }
                break;

            case 'errors':
                // Parsing file for all errors syntax
                $regex = '/Tools::displayError\((\')'._PS_TRANS_PATTERN_.'\'(,\s*(.+))?\)/U';
                break;

            case 'modules':
                // Parsing modules file
                if ($type_file == 'php') {
                    $regex = '/->l\((\')'._PS_TRANS_PATTERN_.'\'(, ?\'(.+)\')?(, ?(.+))?\)/U';
                } else {
                    // In tpl file look for something that should contain mod='module_name' according to the documentation
                    $regex = '/\{l\s*s=([\'\"])'._PS_TRANS_PATTERN_.'\1.*\s+mod=\''.$module_name.'\'.*\}/U';
                }
                break;

            case 'pdf':
                // Parsing PDF file
                if ($type_file == 'php') {
                    $regex = array(
                        '/HTMLTemplate.*::l\((\')'._PS_TRANS_PATTERN_.'\'[\)|\,]/U',
                        '/->l\((\')'._PS_TRANS_PATTERN_.'\'(, ?\'(.+)\')?(, ?(.+))?\)/U'
                    );
                } else {
                    $regex = '/\{l\s*s=([\'\"])'._PS_TRANS_PATTERN_.'\1(\s*sprintf=.*)?(\s*js=1)?(\s*pdf=\'true\')?\s*\}/U';
                }
                break;
        }

        if (!is_array($regex)) {
            $regex = array($regex);
        }

        $strings = array();
        foreach ($regex as $regex_row) {
            $matches = array();
            $n = preg_match_all($regex_row, $content, $matches);
            for ($i = 0; $i < $n; $i += 1) {
                $quote = $matches[1][$i];
                $string = $matches[2][$i];

                if ($quote === '"') {
                    // Escape single quotes because the core will do it when looking for the translation of this string
                    $string = str_replace('\'', '\\\'', $string);
                    // Unescape double quotes
                    $string = preg_replace('/\\\\+"/', '"', $string);
                }

                $strings[] = $string;
            }
        }

        return array_filter(array_unique($strings));
    }
}
