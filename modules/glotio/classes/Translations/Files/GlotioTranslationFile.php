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

class GlotioTranslationFile
{
    /** @var string */
    private $filePath;

    /** @var GlotioTranslationFileType */
    private $fileType;

    /** @var GlotioTranslationTextInfoCollection */
    private $translations;

    /** @var bool */
    private $added_translations = false;

    /**
     * @param string $filePath
     * @throws GlotioException
     */
    public function __construct($filePath)
    {
        // Valida el path de traducciones
        if (!$filePath) {
            throw new GlotioException("Required parameter filePath");
        }

        $route = pathinfo($filePath, PATHINFO_DIRNAME);
        if (strpos($route, '..') !== false) {
            throw new GlotioException("Paths cannot contain '..' symbols");
        }

        // Recuperar traducciones actuales
        $this->filePath = $filePath;
        $this->fileType = GlotioTranslationFileTypeFactory::createForFilePath($filePath);

        $translations       = GlotioTranslationFileReader::getTranslations($this->fileType->getVar(), $filePath);
        $this->translations = GlotioTranslationTextInfoCollection::createWithTranslatedTexts($translations);
    }

    /**
     * @return GlotioTranslationFileType
     */
    public function getFileType()
    {
        return $this->fileType;
    }

    /**
     * @return GlotioTranslationTextInfoCollection
     */
    public function getTranslationsCollection()
    {
        return $this->translations;
    }

    /**
     * Añade traducciones al fichero cargado.
     * @param array $new_translations Key: translation hashed key, Value: translation text
     * @return self
     */
    public function addTranslations(array $new_translations)
    {
        foreach ($new_translations as $translation_key => $translation_value) {
            $translation_key   = pSQL($translation_key, true);
            $translation_value = pSQL($translation_value, true);

            $this->translations->addTranslatedText($translation_key, $translation_value);

            $this->added_translations = true;
        }

        return $this;
    }

    /**
     * Guarda las traducciones en el fichero cargado.
     * @return self
     * @throws GlotioException
     */
    public function saveTranslationsToFile()
    {
        if (!$this->added_translations) {
            return $this;
        }

        GlotioTranslationFileWriter::saveTranslations($this->fileType->getVar(), $this->filePath, $this->translations->toSimpleTranslationsArray());

        return $this;
    }
}
