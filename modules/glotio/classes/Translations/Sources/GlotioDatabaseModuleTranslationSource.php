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

class GlotioDatabaseModuleTranslationSource extends GlotioObjectModelTranslationSource
{
    /**
     * DatabaseModuleTranslationSource constructor.
     * @param string $module_name
     * @param GlotioPsModelInfo $psModelInfo
     */
    public function __construct($module_name, GlotioPsModelInfo $psModelInfo)
    {
        parent::__construct($psModelInfo);

        $this->type    = self::TYPE_DB_MODULE;
        $this->subtype = $this->psModelInfo->getTable();
        $this->module  = $module_name;
    }

    public function getCategoriesTree()
    {
        return GlotioTranslationSourceCategoriesBuilder::create()
            ->module($this->module)
            ->db($this->subtype)
            ->build();
    }

    /**
     * @param DbQuery $query
     */
    protected function addExtraQueryFilters(DbQuery $query)
    {
        parent::addExtraQueryFilters($query);

        switch ($this->psModelInfo->getTable()) {
            case 'pm_advancedsearch_seo':
                $id_shop = (int)Context::getContext()->shop->id;
                $query->leftJoin('pm_advancedsearch_shop', 's', "a.id_search = s.id_search");
                $query->where("s.id_shop = {$id_shop}");
                $query->where("a.deleted = 0");
                break;
            case 'pm_advancedsearch':
                $id_shop = (int)Context::getContext()->shop->id;
                $query->leftJoin('pm_advancedsearch_shop', 's', "a.id_search = s.id_search");
                $query->where("s.id_shop = {$id_shop}");
                break;
        }
    }

    /**
     * @param DbQuery $query
     */
    protected function addActiveQueryFilter(DbQuery $query)
    {
        $disabled_module_tables_filter = array('wbstaticblock', 'pos_staticfooter', 'pos_staticblock');

        if (in_array($this->psModelInfo->getTable(), $disabled_module_tables_filter)) {
            return;
        }

        parent::addActiveQueryFilter($query);
    }
}
