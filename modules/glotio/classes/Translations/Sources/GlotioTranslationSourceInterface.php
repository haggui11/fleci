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

interface GlotioTranslationSourceInterface
{
    const TYPE_DB                = 'database';
    const TYPE_DB_MODULE         = 'database_module';
    const TYPE_MODULE            = 'module';
    const TYPE_THEME             = 'theme';
    const TYPE_THEME_MODULE      = 'theme_module';
    const TYPE_MAIL              = 'mail';
    const TYPE_MODULE_MAIL       = 'module_mail';
    const TYPE_THEME_MAIL        = 'theme_mail';
    const TYPE_THEME_MODULE_MAIL = 'theme_module_mail';

    /**
     * @param bool $filter_active
     */
    public function setFilterActive($filter_active);

    /**
     * @return string
     */
    public function getId();

    /**
     * @return array
     */
    public function getDefinition();

    /**
     * @return array
     */
    public function getSource();

    /**
     * @return string[]
     */
    public function getCategoriesTree();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getSubType();

    /**
     * @return string
     */
    public function getTheme();

    /**
     * @return string
     */
    public function getModule();

    /**
     * @return array
     */
    public function getMetaKeys();

    /**
     * @return array
     */
    public function getMetaFilters();

    /**
     * @return array
     */
    public function getFieldsDefinition();

    /**
     * @return bool
     */
    public function isValid();

    /**
     * @return int
     */
    public function getNumRecords();

    /**
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getRecords($page, $limit);

    /**
     * @param array $records
     * @return array
     */
    public function addRecords($records);
}
