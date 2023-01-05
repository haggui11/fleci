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

class GlotioModuleModelCollection
{
    /** @var GlotioModuleModel[] */
    private $moduleModels = array();

    /**
     * Aañde un elemento a la colección.
     * @param GlotioModuleModel $moduleModel
     */
    public function add(GlotioModuleModel $moduleModel)
    {
        $key = $this->buildKey($moduleModel->module, $moduleModel->table);
        $this->moduleModels[$key] = $moduleModel;
    }

    /**
     * Añade un array de elementos a la colección.
     * @param GlotioModuleModel[] $moduleModels
     */
    public function addAll(array $moduleModels)
    {
        foreach ($moduleModels as $moduleModel) {
            $this->add($moduleModel);
        }
    }

    /**
     * Recupera un ModuleModel a partir de la clave formada por el módulo y la tabla del mismo.
     * @param string $module
     * @param string $table
     * @return GlotioModuleModel|null
     */
    public function getByModuleAndTable($module, $table)
    {
        $return_value = null;

        $key = $this->buildKey($module, $table);
        if (isset($this->moduleModels[$key])) {
            $return_value = $this->moduleModels[$key];
        }

        return $return_value;
    }

    /**
     * @return GlotioModuleModel[]
     */
    public function getModuleModelsArray()
    {
        return $this->moduleModels;
    }

    /**
     * Construye el subtipo de TranslationSource con los datos pasados.
     * @param string $module
     * @param string $table
     * @return string
     */
    private function buildKey($module, $table)
    {
        return $module . '|' . strtolower($table);
    }
}
