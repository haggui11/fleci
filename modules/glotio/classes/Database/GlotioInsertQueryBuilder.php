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
 * Ayuda a generar una sentencia INSERT SQL.
 */
class GlotioInsertQueryBuilder
{
    /** @var string Nombre de tabla */
    private $table;

    /** @var array Nombres de las columnas donde se insertan los valores */
    private $columns = array();

    /** @var array Array de arrays con los valores a insertar */
    private $values = array();

    /** @var array Nombres de los campos que se actualizan en una sentencia ON DUPLICATE KEY UPDATE */
    private $updatefields = array();

    /** @var bool Indica si se ignoran los errores o no */
    private $ignore = false;

    /**
     * Crea una instancia con los parámetros pasados.
     * @param array $params
     * @return GlotioInsertQueryBuilder
     * @throws GlotioException Si no es posible crear la instancia con los parámetros pasados
     */
    public static function createWithArray(array $params)
    {
        if (!isset($params['table'], $params['values']) || !$params['table'] || !$params['values']) {
            throw new GlotioException('Not found or empty parameters: table|values');
        }

        if (!is_array($params['values'])) {
            throw new GlotioException('Values should be an array');
        }

        $insertQuery = new GlotioInsertQueryBuilder();
        $insertQuery->setTable($params['table']);

        $firstValue = reset($params['values']);
        if (is_array($firstValue)) {
            $columns = array_keys($firstValue);
            $insertQuery->setColumns($columns);
            $insertQuery->setValues($params['values']);
        } else {
            $columns = array_keys($params['values']);
            $insertQuery->setColumns($columns);
            $insertQuery->setValues(array($params['values']));
        }

        if (isset($params['update']) && $params['update']) {
            $insertQuery->setUpdatefields($params['update']);
        }

        return $insertQuery;
    }

    /**
     * @param string $table
     * @return self
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param array $columns
     * @return self
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @param array $values
     * @return self
     */
    public function setValues(array $values)
    {
        $this->values = $values;
        return $this;
    }

    /**
     * @param array $updatefields
     * @return self
     */
    public function setUpdatefields(array $updatefields)
    {
        $this->updatefields = $updatefields;
        return $this;
    }

    /**
     * @param bool $ignore
     * @return self
     */
    public function setIgnore($ignore)
    {
        $this->ignore = $ignore;
        return $this;
    }

    /**
     * Devuelve el texto SQL de la sentencia INSERT.
     * @return string
     * @throws GlotioException
     */
    public function build()
    {
        if (!$this->table || !$this->columns || !$this->values) {
            throw new GlotioException('No se ha configurado los valores necesarioas para generación del INSERT SQL');
        } else if ($this->ignore && $this->updatefields) {
            throw new GlotioException('No se puede utilizar la opción IGNORE y la de ON DUPLICATE KEYS UPDATE a la vez');
        }

        $insert_type = $this->ignore ? 'INSERT IGNORE' : 'INSERT';

        $num_columns  = count($this->columns);
        $column_names = "(`" . implode("`,`", $this->columns) . "`)";

        $column_values = array();
        foreach ($this->values as $values) {
            if (count($values) != $num_columns) {
                throw new GlotioException("La cantidad de valores en una de las filas no coincide con la cantidad de columnas al generar INSERT múltiple");
            }
            $values = array_map(array($this, 'escapeValue'), $values);
            $column_values[] = "(" . implode(",", $values) . ")";
        }
        $column_values = implode(",", $column_values);

        $onDuplicateKeysUpdate = '';
        if ($this->updatefields) {
            $updateFieldsPresent = array_intersect($this->updatefields, $this->columns);

            if ($updateFieldsPresent) {
                $onDuplicateKeysUpdate = array();

                foreach ($updateFieldsPresent as $updateField) {
                    $onDuplicateKeysUpdate[] = "`$updateField`=VALUES(`$updateField`)";
                }

                $onDuplicateKeysUpdate = " ON DUPLICATE KEY UPDATE " . implode(",", $onDuplicateKeysUpdate);
            }
        }

        return "{$insert_type} INTO `{$this->table}` {$column_names} VALUES {$column_values}{$onDuplicateKeysUpdate};";
    }

    /**
     * Método auxiliar para normalizar valores en los datos introducidos en la base de datos.
     * @param string $value
     * @return string
     */
    private function escapeValue($value)
    {
        if (is_null($value)) {
            return 'NULL';
        } else {
            return "'". pSQL($value, true) . "'";
        }
    }
}
