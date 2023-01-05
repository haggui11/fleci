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

class GlotioQueryResourceV1 extends GlotioRestApiResource
{
    public function put(array $params)
    {
        return $this->post($params);
    }

    public function post(array $params)
    {
        $params      = $this->getSelectQueryParams($params);
        $query       = GlotioDbQueryBuilder::createWithArray($params);
        $queryRunner = new GlotioDbQueryRunner();

        $num_records = $queryRunner->doSelectCount($query->toCountQuery());
        $records     = $queryRunner->doSelect($query);

        return array(
            'pagination' => array(
                'count_records' => count($records),
                'total_records' => $num_records,
            ),
            'records'       => $records,
        );
    }

    /**
     * @param array $params
     * @return array
     */
    private function getSelectQueryParams(array $params)
    {
        $defaults = array(
            'select'   => '*',
            'from'     => '',
            'where'    => array(),
            'order'    => '',
            'offset'   => 0,
            'limit'    => 1000,
            'prefix'   => _DB_PREFIX_,
        );

        return array_merge($defaults, $params);
    }
}
