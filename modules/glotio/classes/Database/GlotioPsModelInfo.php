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

class GlotioPsModelInfo
{
    /** @var string */
    private $class;

    /** @var string */
    private $object_type;

    /** @var array */
    private $definition;

    /**
     * Crea una nueva instancia a partir de la instancia de un ObjetModel.
     * @param ObjectModel $object
     * @return GlotioPsModelInfo
     */
    public static function createWithObjectModelInstance(ObjectModel $object)
    {
        $class = get_class($object);
        return new self($class);
    }

    /**
     * Crea una nueva instancia a partir del nombre de una clase hija de ObjectModel.
     * @param string $classname
     * @return GlotioPsModelInfo
     */
    public static function createWithObjectModelClassName($classname)
    {
        if (!$classname) {
            throw new RuntimeException("classname cannot be null");
        }

        return new self($classname);
    }

    /**
     * PsModelInfo constructor.
     * @param string $class
     */
    private function __construct($class)
    {
        if (!class_exists($class)) {
            throw new GlotioException("Class not found $class");
        }

        $this->class       = $class;
        $this->object_type = Tools::toUnderscoreCase($this->class);
        $this->definition  = ObjectModel::getDefinition($this->class);
    }

    /**
     * @param string $key
     * @return array|string|null
     */
    public function getDefinition($key = null)
    {
        if ($key) {
            $value = isset($this->definition[$key]) ? $this->definition[$key] : null;
        } else {
            $value = $this->definition;
        }

        return $value;
    }

    /**
     * @return string|null
     */
    public function hasActiveField()
    {
        $return = null;

        foreach (array('active') as $field_name) {
            if ($this->hasField($field_name)) {
                $return = $field_name;
                break;
            }
        }

        return $return;
    }

    /**
     * @param string $field_name
     * @return bool
     */
    public function hasField($field_name)
    {
        return $this->getField($field_name) ? true : false;
    }

    /**
     * @param string $field_name
     * @return bool
     */
    public function isShopField($field_name)
    {
        $field = $this->getField($field_name);
        return ($field && isset($field['shop'])) ? $field['shop'] : false;
    }

    /**
     * @param string $field_name
     * @return array|null
     */
    public function getField($field_name)
    {
        return isset($this->definition['fields'][$field_name]) ? $this->definition['fields'][$field_name] : null;
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->definition['primary'];
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->definition['table'];
    }

    /**
     * @return string
     */
    public function getLangTable()
    {
        return $this->getTable() . '_lang';
    }

    /**
    * @return string
    */
    public function getShopTable()
    {
        return $this->getTable() . '_shop';
    }

    /**
     * @return array
     */
    public function getLangFields()
    {
        $fields = array();

        foreach ($this->definition['fields'] as $field_name => $field_info) {
            if (isset($field_info['lang']) && $field_info['lang']) {
                $fields[] = $field_name;
            }
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function getLangTableColumns()
    {
        $columns = array(
            $this->getPrimaryKey(),
            'id_lang'
        );

        if ($this->isMultiLangShop()) {
            $columns[] = 'id_shop';
        }

        return array_merge($columns, $this->getLangFields());
    }

    /**
     * @return bool
     */
    public function isMultiLangShop()
    {
        return isset($this->definition['multilang_shop']) && $this->definition['multilang_shop'];
    }

    /**
     * The second condition exists because DatabaseModules aren't registered in Shop class table associations by Glotio's model parser.
     * @return bool
     */
    public function isShopTableAssociated()
    {
        return Shop::isTableAssociated($this->getTable()) || in_array(_DB_PREFIX_ . $this->getShopTable(), GlotioShopInfoHelper::getTables());
    }

    /**
     * @return string
     */
    public function getClass()
    {
        $reflection = new ReflectionClass($this->class);
        if ($reflection->hasProperty('originalClassname')) {
            return $reflection->getStaticPropertyValue('originalClassname');
        }

        return $this->class;
    }

    /**
     * @param string|int $primary_key
     * @return ObjectModel
     * @throws GlotioException
     */
    public function getInstance($primary_key)
    {
        if (!class_exists($this->class)) {
            throw new GlotioException("Tried to create not existent class instance {$this->class}");
        }

        return new $this->class((int) $primary_key);
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->object_type;
    }

    /**
     * @param string $field_name
     * @param mixed $field_value
     * @return mixed
     */
    public function castFieldValue($field_name, $field_value)
    {
        $fields = $this->getDefinition('fields');
        if (!isset($fields[$field_name]['type'])) {
            return $field_value;
        }

        $type = self::getFieldTypeAsString($fields[$field_name]['type']);

        switch ($type) {
            case 'int':
                return (int) $field_value;
            case 'float':
                return (float) $field_value;
            case 'bool':
                return (int) $field_value === 1;
            default:
                return (string) $field_value;
        }
    }

    /**
     * @param string $field
     * @param string $value
     * @return string
     */
    public function truncateFieldValue($field, $value)
    {
        if (!$value) {
            return $value;
        }

        $fields = $this->getDefinition('fields');
        if (!isset($fields[$field]['size'])) {
            return $value;
        }

        $size = $fields[$field]['size'];

        if (Tools::strlen($value) > $size) {
            $value = Tools::substr($value, 0, $size);
        }

        return $value;
    }

    /**
     * @param int $field_type
     * @return string
     */
    public static function getFieldTypeAsString($field_type)
    {
        switch ($field_type) {
            case ObjectModel::TYPE_INT:
                return 'int';
            case ObjectModel::TYPE_FLOAT:
                return 'float';
            case ObjectModel::TYPE_DATE:
                return 'date';
            case ObjectModel::TYPE_BOOL:
                return 'bool';
            case ObjectModel::TYPE_NOTHING:
                return null;
            // Not exist in firs 1.6 versions
            //case ObjectModel::TYPE_SQL:
            case 8:
                return 'sql';
            case ObjectModel::TYPE_STRING:
            case ObjectModel::TYPE_HTML:
            default:
                return 'string';
        }
    }
}
