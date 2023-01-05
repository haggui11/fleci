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

class GlotioTranslationFileWriter
{
    /**
     * Guarda las traducciones en el fichero indicado.
     * @param string $globalVar
     * @param string $filePath
     * @param array $translations
     * @throws GlotioException
     */
    public static function saveTranslations($globalVar, $filePath, array $translations)
    {
        GlotioUtils::createParentDirOfPathIfNotExists($filePath);

        $dir = pathinfo($filePath, PATHINFO_DIRNAME);
        if ((file_exists($filePath) && !is_writable($filePath)) || !is_dir($dir)) {
            throw new GlotioException("Translation file " . GlotioUtils::removePsFromPath($filePath) . " is not writable");
        }

        // Secure global var
        $globalVar = preg_replace("/[^a-zA-Z0-9_]/", "", $globalVar);

        $translation_file_content = "<?php\n\nglobal \${$globalVar};\n\${$globalVar} = array();\n";

        $translations = array_filter($translations);
        ksort($translations);

        foreach ($translations as $translation_key => $translation_value) {
            $translation_key   = str_replace(array("\r\n", "\r", "\n"), ' ', $translation_key);
            $translation_key   = addslashes($translation_key);

            $translation_value = str_replace(array("\r\n", "\r", "\n"), ' ', $translation_value);
            $translation_value = addslashes($translation_value);

            $translation_file_content .= '$'.$globalVar.'[\'' . $translation_key . '\'] = \'' . $translation_value . '\';' . "\n";
        }

        $translation_file_content .= "\n?>";

        $write_bytes = (int) file_put_contents($filePath, $translation_file_content);
        if (!$write_bytes) {
            throw new GlotioException("Error while writing translations to file in " . GlotioUtils::removePsFromPath($filePath));
        }
    }
}
