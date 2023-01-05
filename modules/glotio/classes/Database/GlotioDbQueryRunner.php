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

require_once _PS_MODULE_DIR_ . 'glotio/glotio.php';

class GlotioDbQueryRunner
{
    /** @var Db */
    private $Db;

    /**
     * DbQueryRunner constructor.
     * @param Db $database
     */
    public function __construct($database = null)
    {
        if (!$database) {
            $database = Db::getInstance();
        }

        $this->Db = $database;
    }

    /**
     * Ejecuta la consulta SELECT.
     * @param GlotioDbQueryBuilder $query
     * @return array
     * @throws PrestaShopException
     */
    public function doSelect(GlotioDbQueryBuilder $query)
    {
        $sql = $query->build();

        $result = $this->Db->executeS($sql);
        if (!is_array($result)) {
            throw new GlotioException("Query error: " . $this->Db->getMsgError() . (Glotio::GLOTIO_DEBUG ? ". Query: " . $sql : ''));
        }

        return $result;
    }

    /**
     * Ejecuta la consulta SELECT COUNT(*).
     * @param GlotioDbQueryBuilder $query
     * @return int
     * @throws PrestaShopException
     */
    public function doSelectCount(GlotioDbQueryBuilder $query)
    {
        $sql = $query->build();

        $result = $this->Db->executeS($sql);
        if (!is_array($result)) {
            throw new GlotioException("Query error: " . $this->Db->getMsgError() . (Glotio::GLOTIO_DEBUG ? ". Query: " . $sql : ''));
        }

        if (!isset($result[0]['cnt'])) {
            throw new GlotioException("COUNT query format error: SELECT should be like COUNT(*) AS cnt");
        }

        return (int)$result[0]['cnt'];
    }
}
