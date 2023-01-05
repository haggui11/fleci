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
 * Buscar y extrae de una carpeta los modelos disponibles en su código fuente que tienen propiedad $definition.
 * @package Glotio\Translations\Modules
 */
class GlotioModuleModelsExtractor
{
    /** @var string */
    private $module_name;

    /** @var string */
    private $module_path;

    /**
     * @var GlotioPhpParser
     */
    private $phpParser;

    /**
     * Crear una instancia para leer la carpeta de un módulo.
     * @param string $module_name
     * @param GlotioPhpParser $phpParser
     * @return self
     */
    public static function createWithModuleName($module_name, $phpParser)
    {
        $path = _PS_MODULE_DIR_ . $module_name;
        return new self($module_name, $path, $phpParser);
    }

    /**
     * ModuleModelsExtractor constructor.
     * @param string $module_name
     * @param string $module_path
     * @param GlotioPhpParser $phpParser
     */
    public function __construct($module_name, $module_path, $phpParser = null)
    {
        $this->module_name = $module_name;
        $this->module_path = $module_path;
        $this->phpParser   = $phpParser;
    }

    /**
     * Extrae y devuelve los datos de los modelos encontrados.
     * @return GlotioModuleModel[]
     * @throws GlotioException
     */
    public function extractModelsData()
    {
        if (!is_dir($this->module_path)) {
            throw new GlotioException("Directory not found, [$this->module_path]");
        }

        $moduleModels = array();

        $phpFiles = $this->findPhpFilesInDirectory($this->module_path);

        foreach ($phpFiles as $phpFile) {
            $parsedClassModels = $this->phpParser->parsePhpFileToFindObjectModelClasses($phpFile);

            foreach ($parsedClassModels as $parsedClassModel) {
                if (!$parsedClassModel->isValid()) {
                    continue;
                }

                $moduleModel = $this->phpParser->getGlotioModuleModel($parsedClassModel, $this->module_name);
                if ($moduleModel) {
                    $moduleModels[$moduleModel->classname] = $moduleModel;
                }
            }
        }

        return $moduleModels;
    }

    /**
     * @param string $search_path
     * @return string[]
     */
    private function findPhpFilesInDirectory($search_path)
    {
        $finder = new GlotioFileSearch($search_path);
        $finder->addIgnore(array('cache', 'translations', 'test', 'tests', 'vendor', 'vendors', 'es.php', 'en.php', 'de.php', 'it.php', 'fr.php'));
        return $finder->find('php');
    }
}
