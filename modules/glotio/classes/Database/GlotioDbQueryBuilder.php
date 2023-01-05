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
 * Modifica el comportamiento de la clase DbQuery para poder generar consultas cambiando el prefijo de las tablas.
 * También modifica la salida SQL para hacer cambios:
 * - "_DB_" : se reemplaza por la constante _DB_NAME_.
 * - _PREFIX_ : se reemplaza por la constante _DB_PREFIX_.
 */
class GlotioDbQueryBuilder extends DbQuery
{
    /** @var string Prefijo de las tablas. */
    protected $prefix = _DB_PREFIX_;

    /**
     * Crea una instancia con los parámetros pasados en una array.
     * @param array $params
     * @return GlotioDbQueryBuilder
     * @throws GlotioException Si no es posible crear la instancia con los parámetros pasados
     */
    public static function createWithArray(array $params)
    {
        if (!isset($params['select'], $params['from']) || !$params['select'] || !$params['from']) {
            throw new GlotioException('Empty parameters: select|from');
        }

        $query = new GlotioDbQueryBuilder();

        if (isset($params['prefix'])) {
            $query->setPrefix($params['prefix']);
        }

        $params['select'] = is_array($params['select']) ? $params['select'] : array($params['select']);
        $query->select($params['select']);

        $query->from($params['from']);

        if (isset($params['join'])) {
            if (!is_array($params['join'])) {
                throw new GlotioException('Join clause should be an array');
            }

            $params['join'] = (is_array(reset($params['join']))) ? $params['join'] : array($params['join']);
            array_map(array($query, 'join'), $params['join']);
        }

        if (isset($params['where']) && $params['where']) {
            $params['where'] = is_array($params['where']) ? $params['where'] : array($params['where']);
            $query->where($params['where']);
        }

        if (isset($params['order']) && $params['order']) {
            $params['order'] = is_array($params['order']) ? $params['order'] : array($params['order']);
            array_map(array($query, 'orderBy'), $params['order']);
        }

        if (isset($params['group']) && $params['group']) {
            $query->groupBy($params['group']);
        }

        if (isset($params['limit']) && $params['limit']) {
            $query->limit($params['limit'], isset($params['offset']) ? $params['offset'] : 0);
        }

        return $query;
    }

    /**
     * Cambiar el prefijo de las tablas.
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Adds fields to SELECT clause
     *
     * @param string[] $concat_fields List of fields as SQL select string or as an array of fields.
     * @return DbQuery
     */
    public function select($concat_fields)
    {
        if (!is_array($concat_fields)) {
            throw new GlotioException('Error calling select(). $fields should be an array');
        }

        foreach ($concat_fields as $field) {
            $alias = null;

            // Escape field names on demand
            if (strpos($field, '(') !== false) {
                list($field, $alias) = $this->parseFunctionCallWithAlias($field);

                // Not all SQL functions are supported, we need to escape fields
                if (preg_match('/(COUNT)+\((.+)\)/mi', $field, $match)) {
                    if (trim($match[2]) !== '*') {
                        $field = "DISTINCT(" . $this->escapeTableField($match[2]) . ")";
                    }
                } elseif (preg_match('/(DISTINCT)+\((.+)\)/mi', $field, $match)) {
                    $field = "DISTINCT(" . $this->escapeTableField($match[2]) . ")";
                } elseif (preg_match('/(GROUP_CONCAT)+\((.+)\)/mi', $field, $match)) {
                    $parts  = explode(' ORDER BY ', $match[2]);
                    if ($parts) {
                        // Concated fields
                        $concat_fields = explode(',', $parts[0]);
                        foreach ($concat_fields as $i => $concat_field) {
                            $concat_field = trim($concat_field);

                            $first_char = Tools::substr($concat_field, 0, 1);
                            if (!in_array($first_char, array('"', "'"))) {
                                $concat_field = $this->escapeTableField($concat_field);
                            }

                            $concat_fields[$i] = $concat_field;
                        }

                        // Order by
                        $order_by = null;
                        if (count($parts) > 1) {
                            if (preg_match('/(.+)\s((ASC|DESC))?/mi', $parts[1], $match)) {
                                $order_by = ' ORDER BY ' . $this->escapeTableField($match[1]) . ' ' . $match[2];
                            }
                        }

                        $field = "GROUP_CONCAT(" . implode(', ', $concat_fields) . ($order_by ? $order_by : '') . ")";
                    }
                } else {
                    throw new GlotioException("SQL function not supported in {$field}");
                }
            } elseif ($field !== '*') {
                // Separate field from rename with AS
                list ($field, $alias) = $this->parseTableOrFieldWithAlias($field);
                $field = $this->escapeTableField($field);
            }

            $field = $field.($alias ? " AS `$alias`" : "");

            parent::select("$field");
        }

        return $this;
    }

    /**
     * Sets table for FROM clause
     *
     * @param string      $table Table name
     * @param string|null $alias Table alias
     *
     * @return DbQuery
     */
    public function from($table, $alias = null)
    {
        if (!empty($table)) {
            if (empty($this->query['from'])) {
                $this->query['from'] = array();
            }

            if ($alias) {
                $this->query['from'][] = '`' . bqSQL($this->prefix . $table) . '`' . ($alias ? ' AS `' . pSQL($alias) . '`' : '');
            } else {
                $tables = explode(',', $table);
                foreach ($tables as $table) {
                    list($table, $alias) = $this->parseTableOrFieldWithAlias($table);

                    $this->query['from'][] = '`' . bqSQL($this->prefix . $table) . '`' . ($alias ? ' AS `' . pSQL($alias) . '`' : '');
                }
            }
        }

        return $this;
    }

    /**
     * Permite hacer join pasando directamente la string de join como en la clase padre o pasando un array con tres parámetros.
     * @param string|array $join String o Array con formato array(type => str, table => str, on => str)
     * @return DbQuery
     * @throws GlotioException
     */
    public function join($join)
    {
        if (!is_array($join)) {
            throw new GlotioException('Error calling join(). $join field param should be an array');
        }

        if (!isset($join['type'], $join['table'], $join['on'])) {
            throw new GlotioException('Missing params on join clause: type|table|on');
        }

        list($table, $alias) = $this->parseTableOrFieldWithAlias($join['table']);

        if (strstr($table, '_PREFIX_') === false) {
            $table = $this->prefix . $table;
        }

        $join_sql = Tools::strtoupper(bqSQL($join['type'])) . " JOIN `".bqSQL($table)."`".($alias ? ' AS `'.pSQL($alias).'`' : '');

        if ($join['on']) {
            $restriction = $this->processConditionsArray($join['on']);
            $join_sql   .= " ON ({$restriction})";
        }

        $join = $join_sql;

        return parent::join($join);
    }

    public function leftJoin($table, $alias = null, $on = null)
    {
        throw new RuntimeException('Method not implemented');
    }

    public function innerJoin($table, $alias = null, $on = null)
    {
        throw new RuntimeException('Method not implemented');
    }

    public function leftOuterJoin($table, $alias = null, $on = null)
    {
        throw new RuntimeException('Method not implemented');
    }

    public function naturalJoin($table, $alias = null)
    {
        throw new RuntimeException('Method not implemented');
    }

    public function rightJoin($table, $alias = null, $on = null)
    {
        throw new RuntimeException('Method not implemented');
    }

    /**
     * Adds a restriction in WHERE clause (each restriction will be separated by AND statement)
     *
     * @param string|array $restriction
     *
     * @return DbQuery
     */
    public function where($restriction)
    {
        if (!is_array($restriction)) {
            throw new GlotioException('Error calling where(). $restriction field param should be an array');
        }

        if (!empty($restriction)) {
            $this->query['where'][] = $this->processConditionsArray($restriction);
        }

        return $this;
    }

    /**
     * Adds an ORDER BY restriction
     *
     * @param string $fields List of fields to sort. E.g. $this->order('myField, b.mySecondField DESC')
     *
     * @return DbQuery
     */
    public function orderBy($fields)
    {
        if (!empty($fields)) {
            $fields = array_map('trim', explode(',', $fields));

            $orderSql = array();
            foreach ($fields as $field) {
                if (preg_match('/(\S+)\s(asc|desc)*/mi', $field, $match)) {
                    $field = $match[1];
                    $dir   = $match[2];
                    $orderSql[] = '`' . implode('`.`', explode('.', bqSQL($field))) . '` ' . bqSQL($dir);
                } else {
                    $orderSql[] = '`' . implode('`.`', explode('.', bqSQL($field))) . '`';
                }
            }

            $orderSql = implode(', ', $orderSql);

            $this->query['order'][] = $orderSql;
        }

        return $this;
    }

    /**
     * Adds a GROUP BY restriction
     *
     * @param string $fields List of fields to group. E.g. $this->group('myField1, myField2')
     *
     * @return DbQuery
     */
    public function groupBy($fields)
    {
        if (!empty($fields)) {
            $fields = array_map('trim', explode(',', $fields));

            $groupSql = array();
            foreach ($fields as $field) {
                $groupSql[] = '`' . implode('`.`', explode('.', bqSQL($field))) . '`';
            }

            $groupSql = implode(', ', $groupSql);

            $this->query['group'][] = $groupSql;
        }

        return $this;
    }

    /**
     * Transforma la consulta actual en una consulta para contar el número de filas en la tabla seleccionada.
     * @return GlotioDbQueryBuilder
     * @throws GlotioException
     */
    public function toCountQuery()
    {
        $queryCopy = clone $this;
        $queryCopy->query['select'] = array();
        $queryCopy->query['join'] = array();
        $queryCopy->query['group']  = array();
        $queryCopy->query['having'] = array();
        $queryCopy->query['order']  = array();
        $queryCopy->limit(0, 0);

        $queryCopy->select(array('COUNT(*) AS cnt'));

        return $queryCopy;
    }

    /**
     * Modifica la salida SQL para reemplazar ciertos strings.
     * @return string
     * @throws PrestaShopException
     */
    public function build()
    {
        $sql = parent::build();

        $sql = str_replace('_PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('_DB_', _DB_NAME_, $sql);

        return $sql;
    }

    /**
     * @param string $fnCallSql
     * @return array
     */
    protected function parseFunctionCallWithAlias($fnCallSql)
    {
        $alias = null;

        if (preg_match('/([a-z_]+\(.+\))( as .+)?/mi', $fnCallSql, $match)) {
            $fnCallSql = $match[1];

            // Parse alias if found
            if (isset($match[2]) && $match[2]) {
                $alias = 'footable ' . trim($match[2]);
                list($table, $alias) = $this->parseTableOrFieldWithAlias($alias);
            }
        }

        return array($fnCallSql, $alias);
    }

    /**
     * @param string $table
     * @return array
     */
    protected function parseTableOrFieldWithAlias($table)
    {
        $alias = null;

        if (preg_match('/(\S+)( as )+(\S+)/mi', $table, $match)) {
            $table = $match[1];
            $alias = $match[3];
        }

        return array($table, $alias);
    }

    /**
     * @param string $tableField
     * @return string
     */
    protected function escapeTableField($tableField)
    {
        $field = bqSQL($tableField);
        $field = str_replace('.', '`.`', $field);
        $field = "`{$field}`";

        return $field;
    }

    /**
     * @param array $conditions
     * @return string
     * @throws GlotioException
     */
    protected function processConditionsArray(array $conditions)
    {
        if (!is_array(reset($conditions))) {
            $conditions = array($conditions);
        }

        $conditions = array_map(array($this, 'processConditionArray'), $conditions);

        return implode(' AND ', $conditions);
    }

    /**
     * @param array $condition
     * @return string
     * @throws GlotioException
     */
    protected function processConditionArray(array $condition)
    {
        if (!isset($condition['field'], $condition['operator']) || (!isset($condition['field_2']) && !isset($condition['value']))) {
            throw new GlotioException('Missing params in condition array: field|operator|field_2|value.');
        }

        $field    = $this->escapeTableField($condition['field']);
        $operator = bqSQL($condition['operator']);

        if (isset($condition['field_2'])) {
            $field_2 = $this->escapeTableField($condition['field_2']);
            return "{$field} {$operator} {$field_2}";
        } elseif (isset($condition['value'])) {
            if (in_array(Tools::strtoupper($operator), array('IN', 'NOT IN'))) {
                if (!is_array($condition['value'])) {
                    throw new GlotioException('Value should be an array for IN operator');
                }

                $values = array();
                foreach ($condition['value'] as $value) {
                    $values[] = is_numeric($value) ? (int)$value : "'" . bqSQL($value) . "'";
                }

                return "{$field} {$operator}(". implode(',', $values) . ")";
            } else {
                $value = pSQL($condition['value'], isset($condition['html']) && $condition['html']);
                return "{$field} {$operator} '{$value}'";
            }
        } else {
            throw new GlotioException('Missing params in condition array: field_2|value');
        }
    }
}
