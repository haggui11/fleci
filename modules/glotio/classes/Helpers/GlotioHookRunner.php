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

class GlotioHookRunner
{
    /**
     * @var bool
     */
    private static $enabledHooks = true;

    /**
     * @var GlotioPsModelInfo
     */
    private $psModelInfo;

    /**
     * GlotioHooks constructor.
     * @param GlotioPsModelInfo $psModelInfo
     */
    public function __construct(GlotioPsModelInfo $psModelInfo)
    {
        $this->psModelInfo = $psModelInfo;
    }

    /**
     * @param string $primary_key
     * @throws GlotioException
     */
    public function execBeforeUpdateHook($primary_key)
    {
        if (!self::$enabledHooks) {
            return;
        }

        $objectModelInstance = $this->psModelInfo->getInstance($primary_key);
        if (!Validate::isLoadedObject($objectModelInstance)) {
            return;
        }

        self::execHook('actionObjectUpdateBefore', array('object' => $objectModelInstance));
        self::execHook('actionObject'.$this->psModelInfo->getClass().'UpdateBefore', array('object' => $this));

        $objectModelInstance->clearCache();
    }

    /**
     * @param string $primary_key
     * @param array $upload_data
     * @throws GlotioException
     */
    public function execAfterUpdateHook($primary_key, array $upload_data)
    {
        if (!self::$enabledHooks) {
            return;
        }

        $objectModelInstance = $this->psModelInfo->getInstance($primary_key);
        if (!Validate::isLoadedObject($objectModelInstance)) {
            return;
        }

        self::execHook('actionObjectUpdateAfter', array('object' => $objectModelInstance));
        self::execHook('actionObject'.$this->psModelInfo->getClass().'UpdateAfter', array('object' => $objectModelInstance));

        switch ($this->psModelInfo->getTable()) {
            case 'attribute_group':
                self::execHook('actionAttributeGroupSave', array('id_attribute_group' => (int)$primary_key));
                break;
            case 'attribute':
                self::execHook('actionAttributeSave', array('id_attribute' => (int)$primary_key));
                break;
            case 'category':
                self::execHook('actionCategoryUpdate', array('id_category' => (int)$primary_key, 'category' => $objectModelInstance));
                break;
            case 'feature':
                self::execHook('actionFeatureSave', array('id_feature' => (int)$primary_key));
                break;
            case 'feature_value':
                self::execHook('actionFeatureValueSave', array('id_feature_value' => (int)$primary_key));
                break;
            case 'product':
                self::execHook('actionProductSave', array('id_product' => (int)$primary_key, 'product' => $objectModelInstance));
                self::execHook('actionProductUpdate', array('id_product' => (int)$primary_key, 'product' => $objectModelInstance));
                break;
        }

        self::execHook('actionGlotioUpdateAfter', array('object' => $objectModelInstance, 'upload_data' => $upload_data), true);
    }

    /**
     * @param $name
     * @param array $args
     * @param bool $force
     * @throws GlotioException
     */
    public static function execHook($name, array $args = array(), $force = false)
    {
        if (!self::$enabledHooks && !$force) {
            return;
        }

        try {
            Hook::exec($name, $args);
        } catch (Exception $ex) {
            // Ignore exceptions on hooks
            //throw new GlotioException($ex);
        }
    }

    /**
     * @param bool $enabledHooks
     */
    public static function setEnabledHooks($enabledHooks)
    {
        self::$enabledHooks = $enabledHooks;
    }
}
