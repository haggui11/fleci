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
 * Stores parsed information for an ObjetModel PHP source code.
 */
class GlotioParsedClassModel
{
    /**
     * @var string
     */
    public $classname;

    /**
     * @var mixed[]
     */
    public $public_properties = array();

    /**
     * @var mixed
     */
    public $definition_property;

    /**
     * @var mixed[]
     */
    public $class_constants = array();

    /**
     * @var bool
     */
    public $is_multilang_defined = false;

    /**
     * @param string $classname
     */
    public function __construct($classname)
    {
        $this->classname = $classname;
    }

    /**
     * @param mixed $public_property
     */
    public function addToFoundClassPublicProperty($public_property)
    {
        $this->public_properties[] = $public_property;
    }

    /**
     * @param mixed $definition_property
     */
    public function addToFoundClassDefinitionProperty($definition_property)
    {
        $this->definition_property = $definition_property;
    }

    /**
     * @param mixed $public_property
     */
    public function addToFoundClassClassConstant($class_constant)
    {
        $this->class_constants[] = $class_constant;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->classname && $this->definition_property && $this->is_multilang_defined;
    }
}
