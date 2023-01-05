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

class GlotioObjectModelTranslationSource extends GlotioAbstractTranslationSource
{
    /** @var GlotioPsModelInfo */
    protected $psModelInfo;

    /** @var int */
    protected $num_records;

    /**
     * ObjectModelTranslationSource constructor.
     * @param GlotioPsModelInfo $psModelInfo
     */
    public function __construct(GlotioPsModelInfo $psModelInfo)
    {
        $this->type        = self::TYPE_DB;
        $this->subtype     = $psModelInfo->getObjectType();
        $this->psModelInfo = $psModelInfo;
    }

    public function getCategoriesTree()
    {
        return GlotioTranslationSourceCategoriesBuilder::create()
            ->dbCatalogOrOthers($this->subtype)
            ->build();
    }

    public function getFieldsDefinition()
    {
        $result      = array();
        $fields      = $this->psModelInfo->getDefinition('fields');
        $lang_fields = $this->psModelInfo->getLangFields();

        foreach ($lang_fields as $field_name) {
            $result[$field_name] = $this->getFieldDefinition($field_name, $fields[$field_name]);
        }


        // Count number of chars in default_lang
        $table           = $this->psModelInfo->getTable();
        $table_lang      = $this->psModelInfo->getLangTable();
        $primary_key     = $this->psModelInfo->getPrimaryKey();
        $default_id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $id_shop         = (int)Context::getContext()->shop->id;

        $query = new DbQuery();

        foreach ($lang_fields as $field_name) {
            $query->select("SUM(CHAR_LENGTH(`{$field_name}`)) as `$field_name`");
        }

        $query
            ->from($table, 'a')
            ->leftJoin($table_lang, 'l', "a.{$primary_key} = l.{$primary_key} AND l.id_lang = {$default_id_lang}");

        if ($this->psModelInfo->isShopTableAssociated()) {
            $shop_table = $this->psModelInfo->getShopTable();
            $primary    = $this->psModelInfo->getPrimaryKey();

            $query->leftJoin($shop_table, 's', "a.{$primary} = s.{$primary} AND s.id_shop = {$id_shop}");
        }

        $this->addExtraQueryFilters($query);

        $num_chars_result = Db::getInstance()->getRow($query);
        if (is_array($num_chars_result)) {
            foreach ($num_chars_result as $field_name => $num_chars) {
                if (isset($result[$field_name])) {
                    $result[$field_name]['num_chars'] = (int) $num_chars;
                }
            }
        }

        return array_values($result);
    }

    public function isValid()
    {
        return $this->modelTablesExist() && $this->getNumRecords() > 0;
    }

    /**
     * @param bool $throwException
     * @return bool
     * @throws GlotioException
     */
    protected function modelTablesExist($throwException = false)
    {
        $tables_exist = true;

        $tables = array(
            $this->psModelInfo->getTable(),
            $this->psModelInfo->getLangTable(),
        );

        if ($this->psModelInfo->isShopTableAssociated()) {
            $tables[] = $this->psModelInfo->getShopTable();
        }

        foreach ($tables as $table) {
            if (!GlotioShopInfoHelper::tableExists($table)) {
                if ($throwException) {
                    throw new GlotioException("Table {$table} doesn't exist in database");
                }

                $tables_exist = false;
                break;
            }
        }

        return $tables_exist;
    }

    /**
     * @param string $field_name
     * @param array $field_definition
     * @return array
     */
    protected function getFieldDefinition($field_name, array $field_definition)
    {
        return array(
            'field'     => $field_name,
            'type'      => GlotioPsModelInfo::getFieldTypeAsString($field_definition['type']),
            'size'      => isset($field_definition['size']) ? $field_definition['size'] : null,
            'html'      => $field_definition['type'] === ObjectModel::TYPE_HTML,
            'num_chars' => null,
        );
    }

    public function getNumRecords()
    {
        if (is_null($this->num_records)) {
            $this->modelTablesExist(true);

            $table       = $this->psModelInfo->getTable();
            $table_lang  = $this->psModelInfo->getLangTable();
            $primary_key = $this->psModelInfo->getPrimaryKey();
            $id_shop     = (int) Context::getContext()->shop->id;

            $count_query = new DbQuery();
            $count_query
                ->select("COUNT(DISTINCT(a.{$primary_key})) as cnt")
                ->from($table, 'a')
                ->leftJoin($table_lang, 'l', "a.{$primary_key} = l.{$primary_key}")
            ;

            if ($this->psModelInfo->isShopTableAssociated()) {
                $shop_table = $this->psModelInfo->getShopTable();
                $primary    = $this->psModelInfo->getPrimaryKey();

                $count_query->leftJoin($shop_table, 's', "a.{$primary} = s.{$primary} AND s.id_shop = {$id_shop}");
            }

            $this->addExtraQueryFilters($count_query);

            $this->num_records = (int) Db::getInstance()->getValue($count_query);
        }

        return $this->num_records;
    }

    public function getRecords($page, $limit)
    {
        if ($page < 1) {
            throw new GlotioException("Unsupported value of page parameter");
        }
        if ($limit < 1) {
            throw new GlotioException("Unsupported value of limit parameter");
        }

        $this->modelTablesExist(true);

        $table       = $this->psModelInfo->getTable();
        $primary_key = $this->psModelInfo->getPrimaryKey();
        $shop_table  = $this->psModelInfo->getShopTable();
        $id_shop     = (int) Context::getContext()->shop->id;

        // Obtain ids to query (paginated)
        $limitQuery = new DbQuery();
        $limitQuery
            ->select("a.{$primary_key}")
            ->from($table, 'a')
            ->orderBy("a.{$primary_key} ASC")
            ->limit($limit, ($page - 1) * $limit);

        if ($this->psModelInfo->isShopTableAssociated()) {
            $limitQuery->leftJoin($shop_table, 's', "a.{$primary_key} = s.{$primary_key} AND s.id_shop = {$id_shop}");
        }

        $this->addExtraQueryFilters($limitQuery);

        $sql = "SELECT MIN(dt.{$primary_key}) AS min_id, MAX(dt.{$primary_key}) AS max_id
                FROM ({$limitQuery->build()}) AS dt";

        $min_max_ids = Db::getInstance()->executeS($sql);
        if (!$min_max_ids) {
            // No records to request, so return empty array
            return array();
        }

        $min_max_ids = reset($min_max_ids);
        $min_id      = $min_max_ids['min_id'];
        $max_id      = $min_max_ids['max_id'];

        // Query paginated ids
        $records     = $this->getTableRows($min_id, $max_id, GlotioPsLanguages::getIds(), $id_shop);
        $fields_lang = $this->psModelInfo->getLangFields();

        // Group retrieved records (one primary_key could have multiple rows, one for each language)
        $groupData = array();
        foreach ($records as $record) {
            $language = GlotioPsLanguages::idLangToIso($record['id_lang']);
            if (!$language) {
                continue;
            }

            $primary_key_value = (int) $record[$primary_key];
            if (!$primary_key_value) {
                continue;
            }

            // Create TranslationText record only the first time that this primary_key is proccessed
            if (!isset($groupData[$primary_key_value])) {
                // Generate meta_keys
                $meta_keys = array($primary_key => $primary_key_value);
                if ($this->psModelInfo->isMultiLangShop()) {
                    $meta_keys['id_shop'] = $id_shop;
                }

                // Generate meta_filters (rest of values from
                $meta_filters = array_diff_key($record, array_flip($fields_lang), array_flip(array($primary_key, 'id_shop', 'id_lang')));
                foreach ($meta_filters as $field_name => &$meta_filter_value) {
                    $meta_filter_value = $this->psModelInfo->castFieldValue($field_name, $meta_filter_value);
                }

                $groupData[$primary_key_value] = $this->createTranslationText(array(), compact('meta_keys', 'meta_filters'));
            }

            // Add texts for this language
            $groupData[$primary_key_value]['texts'][$language] = GlotioUtils::extractKeys($record, $fields_lang);
        }

        return array_values($groupData);
    }

    public function addRecords($records)
    {
        $results     = array();
        $table       = _DB_PREFIX_ . $this->psModelInfo->getLangTable();
        $primary_key = $this->psModelInfo->getPrimaryKey();
        $columns     = $this->psModelInfo->getLangTableColumns();
        $hookRunner  = new GlotioHookRunner($this->psModelInfo);

        // Iterate over received records to insert new translations
        foreach ($records as $record) {
            foreach ($record['texts'] as $iso_lang => $translated_fields) {
                // Check if language is valid first
                $id_lang = GlotioPsLanguages::isoToIdLang($iso_lang);
                if (!$id_lang) {
                    $id        = $record['meta_keys'][$primary_key];
                    $fields    = array($iso_lang => array_keys($translated_fields));
                    $results[] = self::createTranslationUploadResultError($id, $fields, "Language {$iso_lang} not found");
                    continue;
                }

                // If no values are provided, insertion cannot be done
                if (!$translated_fields) {
                    continue;
                }

                // Info to generate result record
                $fields = array($iso_lang => array_keys($translated_fields));

                // Get current values
                $id      = $record['meta_keys'][$primary_key];
                $id_shop = isset($record['meta_keys']['id_shop']) ? $record['meta_keys']['id_shop'] : null;
                $row     = $this->getTableRow($id, $id_lang, $id_shop);

                // Row could not exists in DB, for example when adding new language rows are not added
                if ($row) {
                    $current_values = GlotioUtils::extractKeys($row, $columns);
                } else {
                    $current_values = array(
                        $primary_key => $id,
                        'id_lang'    => $id_lang,
                    );

                    if (isset($record['meta_keys']['id_shop'])) {
                        $current_values['id_shop'] = $record['meta_keys']['id_shop'];
                    }
                }

                // Fix max size of translated_fields
                foreach ($translated_fields as $field => $value) {
                    $translated_fields[$field] = $this->psModelInfo->truncateFieldValue($field, $value);
                }

                // Get column values to insert overriding current values
                $values = array_merge($current_values, $translated_fields);

                // Build INSERT query
                $update = array_keys($translated_fields);
                $query  = GlotioInsertQueryBuilder::createWithArray(compact('table', 'values', 'update'));

                // Run INSERT query & related hooks
                $hookRunner->execBeforeUpdateHook($id);

                $query_result = Db::getInstance()->execute($query->build());

                if ($query_result) {
                    $hookRunner->execAfterUpdateHook($id, compact('id_lang', 'iso_lang', 'id_shop', 'record'));

                    $result = self::createTranslationUploadResultSuccess($record['_id'], $fields);
                } else {
                    $result = self::createTranslationUploadResultError($record['_id'], $fields, Db::getInstance()->getMsgError());
                }

                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * @param int $id
     * @param int $id_lang
     * @param null $id_shop
     * @return array
     * @throws GlotioException
     * @throws PrestaShopDatabaseException
     */
    private function getTableRow($id, $id_lang, $id_shop = null)
    {
        $rows = $this->getTableRows($id, $id, array($id_lang), $id_shop);
        return is_array($rows) ? reset($rows) : array();
    }

    /**
     * @param int $min_id
     * @param int $max_id
     * @param array $id_langs
     * @param int|null $id_shop
     * @return array
     * @throws GlotioException
     * @throws PrestaShopDatabaseException
     */
    private function getTableRows($min_id, $max_id, array $id_langs, $id_shop = null)
    {
        if (!$min_id && !$max_id) {
            return array();
        }

        if (!$id_langs) {
            throw new GlotioException('id_langs cannot be empty on getTableRows');
        }

        $table       = $this->psModelInfo->getTable();
        $table_lang  = $this->psModelInfo->getLangTable();
        $primary_key = $this->psModelInfo->getPrimaryKey();
        $shop_table  = $this->psModelInfo->getShopTable();
        if (!$id_shop) {
            $id_shop = (int) Context::getContext()->shop->id;
        }

        $query = new DbQuery();
        $query
            ->select('a.*, l.*')
            ->from($table, 'a')
            ->leftJoin($table_lang, 'l', "a.{$primary_key} = l.{$primary_key}")
            ->where("a.{$primary_key} BETWEEN {$min_id} AND {$max_id}")
            ->where("l.id_lang IN(" . implode(",", $id_langs) . ")")
            ->orderBy("a.{$primary_key} ASC");

        if ($this->psModelInfo->isMultiLangShop()) {
            $query->where("l.id_shop = {$id_shop}");
        }

        if ($this->psModelInfo->isShopTableAssociated()) {
            $query
                ->select('s.*')
                ->leftJoin($shop_table, 's', "a.{$primary_key} = s.{$primary_key} AND s.id_shop = {$id_shop}");
        }

        $this->addExtraQueryFilters($query);

        $rows = Db::getInstance()->executeS($query);
        if (!$rows) {
            $rows = array();
        }

        return $rows;
    }

    /**
     * @param DbQuery $query
     */
    protected function addExtraQueryFilters(DbQuery $query)
    {
        $this->addActiveQueryFilter($query);
        $this->addDeletedQueryFilter($query);
    }

    /**
     * @param DbQuery $query
     */
    protected function addActiveQueryFilter(DbQuery $query)
    {
        if ($this->filter_active && $active_field = $this->psModelInfo->hasActiveField()) {
            if ($this->psModelInfo->isShopField($active_field)) {
                $query->where("s.active = 1");
            } else {
                $query->where("a.active = 1");
            }
        }
    }

    /**
     * @param DbQuery $query
     */
    protected function addDeletedQueryFilter(DbQuery $query)
    {
        if ($this->psModelInfo->hasField('deleted')) {
            if ($this->psModelInfo->isShopField('deleted')) {
                $query->where("s.deleted = 0");
            } else {
                $query->where("a.deleted = 0");
            }
        }
    }
}
