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

class GlotioPs16ThemeTranslationSource extends GlotioAbstractTranslationSource
{
    /** @var string */
    protected $theme_path;

    /** @var bool */
    protected $loaded = false;

    /** @var string[] */
    protected $texts = array();

    /** @var string[] */
    protected $originals = array();

    /**
     * ThemeTranslationSource constructor.
     * @param string $theme_name
     */
    public function __construct($theme_name)
    {
        $this->type       = self::TYPE_THEME;
        $this->theme      = $theme_name;
        $this->theme_path = _PS_ALL_THEMES_DIR_ . $theme_name;
    }

    public function getCategoriesTree()
    {
        return GlotioTranslationSourceCategoriesBuilder::create()
            ->theme($this->theme)
            ->templates()
            ->build();
    }

    public function getSubType()
    {
        return $this->theme;
    }

    public function getFieldsDefinition()
    {
        $this->loadTexts();

        return array(
            array(
                'field'     => '*',
                'type'      => 'string',
                'size'      => null,
                'html'      => true,
                'num_chars' => self::getNumCharsDefaultLangFromTextsArray($this->texts, $this->originals),
            )
        );
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return is_dir($this->theme_path);
    }

    public function getNumRecords()
    {
        return 1;
    }

    public function getRecords($page, $limit)
    {
        $this->loadTexts();

        $meta_original = $this->originals;

        return array(
            $this->createTranslationText($this->texts, compact('meta_original'))
        );
    }

    protected function loadTexts()
    {
        if ($this->loaded) {
            return;
        }

        if (!$this->isValid()) {
            throw new GlotioException("Theme {$this->theme} doesn't exists");
        }

        /** @var GlotioTranslationTextInfoCollection[] $textInfoCollections */
        $textInfoCollections = array();

        // Cargar todas las traducciones del tema en todos los idiomas
        foreach (GlotioPsLanguages::getIsos() as $iso) {
            // Obtener traducciones del tema
            $translationFile = $this->getThemeTranslationFile($iso);

            $textInfoCollections[$iso] = $translationFile->getTranslationsCollection();
        }

        // Generar salida del API
        foreach ($textInfoCollections as $iso => $textInfoCollection) {
            foreach ($textInfoCollection->getTranslations() as $translationTextInfo) {
                $key = $translationTextInfo->key;

                $this->texts[$iso][$key] = $translationTextInfo->translation;
                $this->originals[$key]   = $translationTextInfo->original;
            }
        }

        $this->loaded = true;
    }

    /**
     * Devuelve un objeto TranslationFile con toda la información de traducción del tema activo actual en el idioma indicado.
     * @param string $iso
     * @return GlotioTranslationFile
     * @throws GlotioException
     */
    protected function getThemeTranslationFile($iso)
    {
        // Obtener traducciones del tema
        $translationFilePath = _PS_ALL_THEMES_DIR_ . $this->theme . '/lang/' . $iso . '.php';
        $translationFile     = new GlotioTranslationFile($translationFilePath);

        // Parsear ficheros tpl del tema
        foreach ($this->getThemeParseableFiles() as $tplFile) {
            // Añadir textos a la colección
            $prefix_key = substr(basename($tplFile), 0, -4);

            // Parsear textos del fichero tpl
            $matches = GlotioTranslationsParser::parseFile($tplFile, 'front');
            foreach ($matches as $text) {
                $text_key = $prefix_key . '_' . md5($text);
                $translationFile->getTranslationsCollection()->addOriginalText($text_key, $text);
            }
        }

        return $translationFile;
    }

    /**
     * Devuelve una lista de los ficheros del tema en los que se deben parsear traducciones.
     * @return array
     */
    protected function getThemeParseableFiles()
    {
        // Buscar ficheros tpl del tema
        $tplFilesSearch = new GlotioFileSearch(_PS_ALL_THEMES_DIR_ . $this->theme);
        $tplFilesSearch->addIgnore(array('cache', 'modules', 'pdf', 'mails', 'translations'));
        $tplFiles = $tplFilesSearch->find('tpl');

        // Buscar también ficheros tpl en el directorio /themes/ (sin recursividad)
        $tplFilesSearch = new GlotioFileSearch(_PS_ALL_THEMES_DIR_);
        $tplFilesSearch->setRecursive(false);
        $tplFiles = array_merge($tplFiles, $tplFilesSearch->find('tpl'));

        return $tplFiles;
    }

    public function addRecords($records)
    {
        $results = array();

        /** @var TranslationFile[] $translationFilesByIso */
        $translationFilesByIso = array();

        // Add translations found in each record
        foreach ($records as $record) {
            foreach ($record['texts'] as $iso_lang => $translated_fields) {
                // Load TranslationFile if not loaded yet
                if (!isset($translationFilesByIso[$iso_lang])) {
                    $translationFilesByIso[$iso_lang] = $this->getThemeTranslationFile($iso_lang);
                }

                $translationFilesByIso[$iso_lang]->addTranslations($translated_fields);

                // Info to generate result record
                $fields = array($iso_lang => array_keys($translated_fields));

                try {
                    $translationFilesByIso[$iso_lang]->saveTranslationsToFile();
                    $result = self::createTranslationUploadResultSuccess($record['_id'], $fields);
                } catch (GlotioException $ex) {
                    $result = self::createTranslationUploadResultError($record['_id'], $fields, $ex->getMessage());
                }

                $results[] = $result;
            }
        }

        return $results;
    }
}
