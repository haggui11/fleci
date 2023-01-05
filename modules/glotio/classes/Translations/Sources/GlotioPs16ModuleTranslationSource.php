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

class GlotioPs16ModuleTranslationSource extends GlotioAbstractTranslationSource
{
    /** @var string */
    protected $module_path;

    /** @var bool */
    protected $loaded = false;

    /** @var string[] */
    protected $texts = array();

    /** @var string[] */
    protected $originals = array();

    /**
     * ModuleTranslationSource constructor.
     * @param string $module_name
     */
    public function __construct($module_name)
    {
        $this->type        = self::TYPE_MODULE;
        $this->module      = $module_name;
        $this->module_path = _PS_MODULE_DIR_ . $module_name;
    }

    public function getCategoriesTree()
    {
        return GlotioTranslationSourceCategoriesBuilder::create()
            ->module($this->module)
            ->templates()
            ->build();
    }

    public function getSubType()
    {
        return $this->module;
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
        return is_dir($this->module_path);
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

    /**
     * @throws GlotioException
     */
    protected function loadTexts()
    {
        if ($this->loaded) {
            return;
        }

        if (!$this->isValid()) {
            throw new GlotioException("Module {$this->module} doesn't exists");
        }

        /** @var TranslationTextInfoCollection[] $textInfoCollections */
        $textInfoCollections = array();

        // Cargar todas las traducciones del tema en todos los idiomas
        foreach (GlotioPsLanguages::getIsos() as $iso) {
            $moduleTranslationFile     = $this->getModuleTranslationFile($iso, $this->module, $this->module_path);
            $textInfoCollections[$iso] = $moduleTranslationFile->getTranslationsCollection();
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
     * Devuelve un objeto TranslationFile con la información de traducción de un módulo.
     * @param string $lang_iso
     * @param string $module_name
     * @param string $modulePath
     * @return GlotioTranslationFile
     * @throws GlotioException
     */
    private function getModuleTranslationFile($lang_iso, $module_name, $modulePath)
    {
        // Se buscan las traducciones actuales en una de estas rutas (en orden de preferencia de mayor a menor)
        $translationFilePaths = array(
            "{$modulePath}/translations/{$lang_iso}.php",
            "{$modulePath}/{$lang_iso}.php",
        );
        $translationFilePath = GlotioUtils::getExistingPathFromList($translationFilePaths);

        // Si no existe, se indica como destino de las traducciones la primera ruta
        if (!$translationFilePath) {
            $translationFilePath = reset($translationFilePaths);
        }

        // Cargar traducciones ya hechas del módulo
        $translationFile = new GlotioTranslationFile($translationFilePath);

        // Parsear ficheros tpl del tema
        foreach ($this->getModuleTranslatableFiles($modulePath) as $translatableSourceFile) {
            // Añadir textos a la colección
            $template_name = pathinfo($translatableSourceFile, PATHINFO_FILENAME);

            // Parsear textos del fichero fuente
            $matches = GlotioTranslationsParser::parseFile($translatableSourceFile, 'modules', $module_name);
            foreach ($matches as $text) {
                $md5_key = md5($text);

                if ($translationFile->getFileType()->getType() === GlotioTranslationFileType::TYPE_THEME_MODULES) {
                    $translation_key = '<{'.Tools::strtolower($module_name).'}'.strtolower(_THEME_NAME_).'>'.Tools::strtolower($template_name).'_'.$md5_key;
                } else {
                    $translation_key = '<{'.Tools::strtolower($module_name).'}prestashop>'.Tools::strtolower($template_name).'_'.$md5_key;
                }

                $translationFile->getTranslationsCollection()->addOriginalText($translation_key, $text);
            }
        }

        return $translationFile;
    }

    /**
     * Devuelve una lista de los ficheros de módulos en los que se deben parsear traducciones.
     * @param string $modulePath Ruta al módulo (en la ruta habitual de los módulos o en la del tema)
     * @return array
     */
    private function getModuleTranslatableFiles($modulePath)
    {
        $tplFilesSearch = new GlotioFileSearch($modulePath);
        $tplFilesSearch->addIgnore(array('translations', 'img', 'js', 'mails', 'override', 'vendor', 'test', 'tests'));
        $tplFilesSearch->setRecursive(true);
        $translatableTplFiles = $tplFilesSearch->find('tpl');

        $tplFilesSearch = new GlotioFileSearch($modulePath);
        $tplFilesSearch->addIgnore(array('translations', 'en.php', 'es.php', 'de.php', 'fr.php', 'it.php', 'gb.php', 'img', 'js', 'mails', 'override', 'vendor', 'test', 'tests'));
        $tplFilesSearch->setRecursive(true);
        $translatablePhpModuleFiles = $tplFilesSearch->find('php');

        return array_merge($translatableTplFiles, $translatablePhpModuleFiles);
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
                    $translationFilesByIso[$iso_lang] = $this->getModuleTranslationFile($iso_lang, $this->module, $this->module_path);
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
