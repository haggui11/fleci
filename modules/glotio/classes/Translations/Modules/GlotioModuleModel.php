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

class GlotioModuleModel
{
    const NORMALIZE_VALUES_REGEX = '/[^a-zA-Z0-9_-]/i';

    /** @var string */
    public $module;

    /** @var string */
    public $classname;

    /** @var string */
    public $table;

    /** @var string|null */
    public $source_code;

    /**
     * @param array $properties
     * @return GlotioModuleModel
     */
    public static function createWithArray(array $properties)
    {
        $instance = new self();

        foreach ($properties as $name => $value) {
            if (property_exists($instance, $name)) {
                if ($name !== 'source_code') {
                    $value = preg_replace(self::NORMALIZE_VALUES_REGEX, '', $value);
                }
                $instance->{$name} = $value;
            }
        }

        return $instance;
    }

    /**
     * @return array
     */
    public function getIndex()
    {
        return array(
            'module'    => $this->module,
            'classname' => $this->classname,
            'table'     => $this->table,
        );
    }
}
