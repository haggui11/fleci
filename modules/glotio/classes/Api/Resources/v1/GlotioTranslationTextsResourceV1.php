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

class GlotioTranslationTextsResourceV1 extends GlotioRestApiResource
{
    /** @var string Expresión regular para comprobar los parámetros de entrada */
    const REGEX_CHECK_INPUT_VALUE = '/^[a-zA-Z0-9_\-\|\.<>{} ]+$/';

    /** @var int Límite de registros por defecto que se piden de una vez por el API */
    const DEFAULT_LIMIT = 1000;

    /** @var int Límite máximo de registros que se pueden pedir de una vez por el API */
    const MAX_LIMIT = 10000;

    public function get(array $params)
    {
        $page = filter_var(Tools::getValue('page', 1), FILTER_SANITIZE_NUMBER_INT);
        if ($page < 1) {
            throw new GlotioException("Unsupported value of page parameter");
        }

        $limit = filter_var(Tools::getValue('limit', self::DEFAULT_LIMIT), FILTER_SANITIZE_NUMBER_INT);
        if ($limit < 1 || $limit > self::MAX_LIMIT) {
            throw new GlotioException("Unsupported value of limit parameter, should be between 1 and " . self::MAX_LIMIT);
        }

        $filter_active = filter_var(Tools::getValue('filter_active'), FILTER_SANITIZE_NUMBER_INT);
        if ($filter_active < 0 || $filter_active > 1) {
            throw new GlotioException("Unsupported value of filter_active parameter, should be 0 or 1");
        }

        $type    = Tools::getValue('source_type', null);
        $subtype = Tools::getValue('source_subtype', null);
        $theme   = Tools::getValue('source_theme', null);
        $module  = Tools::getValue('source_module', null);
        $params  = compact('type', 'subtype', 'theme', 'module');

        foreach ($params as $param_name => $param_value) {
            if ($param_value && !preg_match(self::REGEX_CHECK_INPUT_VALUE, $param_value)) {
                throw new GlotioException("Unsupported input parameter [$param_name] format. Provided value: '$param_value'");
            }
        }

        try {
            $factory = new GlotioTranslationSourceFactory();
            $factory->setFilterActive($filter_active);
            $source = $factory->create($params);

            $num_records = $source->getNumRecords();
            $records     = $source->getRecords($page, $limit);

            return array(
                'pagination' => array(
                    'page'          => (int) $page,
                    'total_pages'   => ceil($num_records / $limit),
                    'limit'         => (int) $limit,
                    'has_more'      => ($page * $limit) < $num_records,
                    'count_records' => count($records),
                    'total_records' => $num_records,
                    'filter_active' => (bool) $filter_active,
                ),
                'records' => $records,
            );
        } catch (Exception $ex) {
            return array(
                'pagination' => array(
                    'page'          => (int) $page,
                    'total_pages'   => 0,
                    'limit'         => (int) $limit,
                    'has_more'      => false,
                    'count_records' => 0,
                    'total_records' => 0,
                    'filter_active' => (bool) $filter_active,
                ),
                'has_errors' => true,
                'error_message' => $ex->getMessage(),
                'records' => array(),
            );
        }
    }

    public function post(array $params)
    {
        return $this->put($params);
    }

    public function put(array $params)
    {
        // Disable hooks execution on upload on demand
        $disable_hooks = Tools::getValue('disable_hooks');
        if ($disable_hooks) {
            GlotioHookRunner::setEnabledHooks(false);
        }

        $records = $params;
        if (!is_array($records) || !$records) {
            throw new GlotioException("Expected JSON array in request body");
        }

        $translationSourceFactory = new GlotioTranslationSourceFactory();

        // Group records by TranslationSource
        $sources_and_records = array();
        foreach ($records as $record) {
            $this->checkUploadRecord($record);

            $params = $record['meta_source'];
            $key    = implode('|', $params);

            // Create source the first time that we found these params
            if (!isset($sources_and_records[$key])) {
                $sources_and_records[$key] = array(
                    'source'  => $translationSourceFactory->create($params),
                    'records' => array(),
                );
            }

            // Push records for this source
            $sources_and_records[$key]['records'][] = $record;
        }

        // Process all records of the same TranslationSource at once
        $results = array();
        foreach ($sources_and_records as $source_and_record) {
            $source  = $source_and_record['source'];
            $records = $source_and_record['records'];
            $results = array_merge($results, $source->addRecords($records));
        }

        return $results;
    }

    /**
     * @param array $record
     * @throws GlotioException
     */
    private function checkUploadRecord(array $record)
    {
        if (!isset($record['_id'])) {
            throw new GlotioException("Received record without _id");
        }

        if (!isset($record['meta_source']['type'])) {
            throw new GlotioException("Received record without meta_source.type");
        }

        foreach ($record['meta_source'] as $field => $value) {
            if ($value && !preg_match(self::REGEX_CHECK_INPUT_VALUE, $value)) {
                throw new GlotioException("Unsupported input parameter [meta_source.{$field}] format");
            }
        }

        if (!isset($record['texts']) || ! $record['texts']) {
            throw new GlotioException("Received record without texts");
        }

        foreach ($record['texts'] as $lang => $fieldsByLang) {
            if (!is_array($fieldsByLang) || !$fieldsByLang) {
                throw new GlotioException("Received record without texts array in lang [text.{$lang}]");
            }

            foreach ($fieldsByLang as $field => $value) {
                if (!preg_match(self::REGEX_CHECK_INPUT_VALUE, $field)) {
                    throw new GlotioException("Unsupported input parameter [texts.{$lang}.{$field}] field name");
                }
            }
        }
    }
}
