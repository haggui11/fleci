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

abstract class GlotioAbstractTranslationSource implements GlotioTranslationSourceInterface
{
    /** @var string */
    const KEY_SEPARATOR = '|';

    /** @var string */
    protected $type;

    /** @var string */
    protected $subtype;

    /** @var string */
    protected $theme;

    /** @var string */
    protected $module;

    /** @var bool */
    protected $filter_active = false;

    public function setFilterActive($filter_active)
    {
        $this->filter_active = $filter_active;
    }

    public function getId()
    {
        $source = $this->getSource();
        return self::buildKey($source);
    }

    public function getDefinition()
    {
        try {
            $fields = $this->getFieldsDefinition();

            $num_chars = 0;
            foreach ($fields as $field) {
                if (isset($field['num_chars'])) {
                    $num_chars += $field['num_chars'];
                }
            }

            $definition = array(
                '_id'                  => $this->getId(),
                'meta_source'          => $this->getSource(),
                'meta_categories_tree' => $this->getCategoriesTree(),
                'meta_class'           => get_class($this),
                'fields'               => $fields,
                'num_records'          => $this->getNumRecords(),
                'num_chars'            => $num_chars,
            );
        } catch (Error $ex) {
            $definition = $this->getDefinitionError($ex->getMessage() . $ex->getTraceAsString());
        } catch (Exception $ex) {
            $definition = $this->getDefinitionError($ex->getMessage() . $ex->getTraceAsString());
        }

        return $definition;
    }

    /**
     * @param string $message
     * @return array
     */
    public function getDefinitionError($message)
    {
        return array(
            '_id'                  => $this->getId(),
            'meta_source'          => $this->getSource(),
            'meta_categories_tree' => $this->getCategoriesTree(),
            'meta_class'           => get_class($this),
            'fields'               => array(),
            'num_records'          => 0,
            'num_chars'            => 0,
            'has_errors'           => true,
            'error_message'        => $message,
        );
    }

    public function getSource()
    {
        $source = array();

        $fields = array('type', 'subtype', 'theme', 'module');
        foreach ($fields as $field) {
            if ($this->{$field}) {
                $source[$field] = $this->{$field};
            }
        }

        return $source;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSubType()
    {
        return $this->subtype;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getMetaKeys()
    {
        return array();
    }

    public function getMetaFilters()
    {
        return array();
    }

    /**
     * @param array $texts
     * @param array $extra_info
     * @return array
     */
    public function createTranslationText(array $texts = array(), array $extra_info = array())
    {
        $record = array(
            '_id'                   => null,
            'translation_source_id' => null,
            'meta_source'           => $this->getSource(),
            'meta_categories_tree'  => $this->getCategoriesTree(),
            'meta_keys'             => $this->getMetaKeys(),
            'texts'                 => $texts,
            'meta_filters'          => $this->getMetaFilters(),
        );
        $record = array_merge($record, $extra_info);

        $record['_id']                   = self::buildKey($record['meta_source'], $record['meta_keys']);
        $record['translation_source_id'] = self::buildKey($record['meta_source']);

        return $record;
    }

    /**
     * @param string $_id
     * @param array $fields
     * @return array
     */
    public static function createTranslationUploadResultSuccess($_id, array $fields)
    {
        return array(
            'succes' => true,
            '_id'    => $_id,
            'fields' => $fields,
        );
    }

    /**
     * @param string $_id
     * @param array $fields
     * @param string $error
     * @return array
     */
    public static function createTranslationUploadResultError($_id, array $fields, $error)
    {
        return array(
            'error'   => true,
            'message' => $error,
            '_id'     => $_id,
            'fields'  => $fields,
        );
    }

    /**
     * @param array $key_parts
     * @param array $additional_parts
     * @return string
     */
    public static function buildKey(array $key_parts, array $additional_parts = array())
    {
        return implode(self::KEY_SEPARATOR, array_merge($key_parts, $additional_parts));
    }

    /**
     * @param array $texts Format [iso][text_key] = text_value
     * @param array $originals Format [text_key] = text_value
     * @return int
     */
    public static function getNumCharsDefaultLangFromTextsArray(array $texts, array $originals = array())
    {
        $num_chars        = 0;
        $default_id_lang  = (int) Configuration::get('PS_LANG_DEFAULT');
        $default_iso_lang = GlotioPsLanguages::idLangToIso($default_id_lang);

        if (isset($texts[$default_iso_lang])) {
            foreach ($texts[$default_iso_lang] as $key => $text) {
                $strlen = Tools::strlen($text);
                if (!$strlen && isset($originals[$key])) {
                    $strlen += Tools::strlen($originals[$key]);
                }

                $num_chars += $strlen;
            }
        }

        return $num_chars;
    }
}
