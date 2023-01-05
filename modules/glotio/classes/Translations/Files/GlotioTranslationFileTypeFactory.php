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

class GlotioTranslationFileTypeFactory
{
    /** @var array Definiciones de los distintos ficheros de traducciones disponibles. */
    public static $translations_informations;

    /**
     * Recupera la información del tipo de fichero de traducciones a partir de la ruta al mismo.
     * @param string $translationFilePath
     * @return GlotioTranslationFileType
     * @throws GlotioException
     */
    public static function createForFilePath($translationFilePath)
    {
        foreach (self::$translations_informations as $translations_information) {
            // Identificar el tipo de fichero por la ruta al mismo
            if (strpos($translationFilePath, $translations_information['dir']) === false) {
                continue;
            }

            // Algunos ficheros de traducción tiene un fichero con nombre específico y código iso en la ruta
            if ($translations_information['file']) {
                if (pathinfo($translationFilePath, PATHINFO_BASENAME) == $translations_information['file']) {
                    return new GlotioTranslationFileType($translations_information);
                }
            } else {
                // Los módulos y traducciones del tema tienen el código iso en el nombre del fichero
                $filename = pathinfo($translationFilePath, PATHINFO_FILENAME);
                if (!Language::getIdByIso($filename)) {
                    throw new GlotioException('The translation filename does not have a valid ISO code');
                }

                return new GlotioTranslationFileType($translations_information);
            }
        }

        throw new GlotioException("The translation file " . GlotioUtils::removePsFromPath($translationFilePath) . " is not supported");
    }
}

GlotioTranslationFileTypeFactory::$translations_informations = array(
    array(
        'type' => GlotioTranslationFileType::TYPE_BACKEND,
        'var'  => '_LANGADM',
        'dir'  => _PS_TRANSLATIONS_DIR_, // translations/iso/admin.php
        'file' => 'admin.php'
    ),
    array(
        'type' => GlotioTranslationFileType::TYPE_ERRORS,
        'var'  => '_ERRORS',
        'dir'  => _PS_TRANSLATIONS_DIR_, // translations/iso/errors.php
        'file' => 'errors.php'
    ),
    array(
        'type' => GlotioTranslationFileType::TYPE_FIELDS,
        'var'  => '_FIELDS',
        'dir'  => _PS_TRANSLATIONS_DIR_, // translations/iso/fields.php
        'file' => 'fields.php'
    ),
    array(
        'type' => GlotioTranslationFileType::TYPE_PDF,
        'var'  => '_LANGPDF',
        'dir'  => _PS_TRANSLATIONS_DIR_, // translations/iso/pdf.php
        'file' => 'pdf.php'
    ),
    array(
        'type' => GlotioTranslationFileType::TYPE_TABS,
        'var'  => '_TABS',
        'dir'  => _PS_TRANSLATIONS_DIR_, // translations/iso/tabs.php
        'file' => 'tabs.php'
    ),
    array(
        'type' => GlotioTranslationFileType::TYPE_MAILS,
        'var'  => '_LANGMAIL',
        'dir'  => _PS_MAIL_DIR_, // mails/iso/lang.php
        'file' => 'lang.php'
    ),
    array(
        'type' => GlotioTranslationFileType::TYPE_MODULES,
        'var'  => '_MODULE', //original '_MODULES'
        'dir'  => _PS_MODULE_DIR_, // module/xxxx/translations/iso.php
        'file' => null,
    ),
    array(
        'type' => GlotioTranslationFileType::TYPE_THEME,
        'var'  => '_LANG',
        'dir'  => _PS_THEME_DIR_ . 'lang/', // theme/xxxx/lang/iso.php
        'file' => null,
    ),
    array(
        'type' => GlotioTranslationFileType::TYPE_THEME_MODULES,
        'var'  => '_MODULE', //original '_MODULES'
        'dir'  => _PS_THEME_DIR_ . 'modules/', // theme/xxxx/modules/xxx/translations/iso.php
        'file' => null,
    )
);
