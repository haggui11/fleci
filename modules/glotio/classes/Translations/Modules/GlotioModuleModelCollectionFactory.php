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

abstract class GlotioModuleModelCollectionFactory
{
    /**
     * Creates a new instance with cache data, renewing cache first if needed.
     * @param null $cache_directory_path
     * @return GlotioModuleModelCollection
     * @throws GlotioException
     */
    public static function create($cache_directory_path = null)
    {
        if (!$cache_directory_path) {
            $cache_directory_path = _PS_MODULE_DIR_ . 'glotio/cache/';
        }

        $cache_key = md5(implode('|', GlotioPsModules::getTranslatableModules()));
        $moduleModelCache = new GlotioModuleModelCache($cache_directory_path, $cache_key);

        if ($moduleModelCache->isObsolete()) {
            $moduleModelCache->reset();

            $moduleModels = self::extractModuleModels();

            if ($moduleModels) {
                $moduleModelCache->store($moduleModels);
            }
        }

        $moduleModels = $moduleModelCache->load();

        $moduleModelCollection = new GlotioModuleModelCollection();

        foreach ($moduleModels as $moduleModel) {
            if (Module::isEnabled($moduleModel->module)) {
                $moduleModelCollection->add($moduleModel);
            }
        }

        return $moduleModelCollection;
    }

    /**
     * @return GlotioModuleModel[]
     */
    private static function extractModuleModels()
    {
        $return = array();

        $phpParser = GlotioPhpParserFactory::create();

        foreach (GlotioPsModules::getTranslatableModulesWithObjectModels() as $module) {
            $moduleModelsExtractor = GlotioModuleModelsExtractor::createWithModuleName($module, $phpParser);
            $return = array_merge($return, $moduleModelsExtractor->extractModelsData());
        }

        return array_values($return);
    }
}
