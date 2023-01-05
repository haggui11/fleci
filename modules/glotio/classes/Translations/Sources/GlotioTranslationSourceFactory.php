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

class GlotioTranslationSourceFactory
{
    const PS_VERSION_1_6 = 'ps_16';
    const PS_VERSION_1_7 = 'ps_17';

    /** @var string */
    private $ps_version;

    /** @var GlotioModuleModelCollection */
    private $moduleModelCollection;

    /** @var bool */
    private $filter_active = false;

    /**
     * TranslationSourceFactory constructor.
     * @param string $ps_version
     */
    public function __construct($ps_version = null)
    {
        if (!$ps_version) {
            $ps_version = GlotioUtils::isPs16() ? self::PS_VERSION_1_6 : self::PS_VERSION_1_7;
        }

        $this->ps_version = $ps_version;
    }

    /**
     * @param bool $filter_active
     */
    public function setFilterActive($filter_active)
    {
        $this->filter_active = $filter_active;
    }

    /**
     * @return GlotioModuleModelCollection
     */
    public function getModuleModelCollection()
    {
        if (!$this->moduleModelCollection) {
            $this->moduleModelCollection = GlotioModuleModelCollectionFactory::create();
        }

        return $this->moduleModelCollection;
    }

    /**
     * @param GlotioModuleModelCollection $moduleModelCollection
     */
    public function setModuleModelCollection(GlotioModuleModelCollection $moduleModelCollection)
    {
        $this->moduleModelCollection = $moduleModelCollection;
    }

    /**
     * @param array $params Format: [type => str, subtype => str, theme => str, module => str]
     * @return GlotioTranslationSourceInterface
     * @throws GlotioException
     */
    public function create(array $params)
    {
        $defaults = array(
            'type'    => null,
            'subtype' => null,
            'theme'   => null,
            'module'  => null,
        );
        $params = array_merge($defaults, $params);

        switch ($params['type']) {
            case GlotioTranslationSourceInterface::TYPE_DB:
                $translationSource = $this->createDatabaseTypeSource($params);
                break;
            case GlotioTranslationSourceInterface::TYPE_DB_MODULE:
                $translationSource = $this->createDatabaseModuleSource($params);
                break;
            case GlotioTranslationSourceInterface::TYPE_THEME:
                $translationSource = $this->createThemeTypeSource($params);
                break;
            case GlotioTranslationSourceInterface::TYPE_MODULE:
                $translationSource = $this->createModuleTypeSource($params);
                break;
            case GlotioTranslationSourceInterface::TYPE_THEME_MODULE:
                $translationSource = $this->createThemeModuleTypeSource($params);
                break;
            case GlotioTranslationSourceInterface::TYPE_MAIL:
            case GlotioTranslationSourceInterface::TYPE_MODULE_MAIL:
            case GlotioTranslationSourceInterface::TYPE_THEME_MAIL:
            case GlotioTranslationSourceInterface::TYPE_THEME_MODULE_MAIL:
                $translationSource = $this->createMailTypeSource($params);
                break;
            default:
                throw new GlotioException("Unknow TranslationSource type [{$params['type']}]");
        }

        $translationSource->setFilterActive($this->filter_active);

        return $translationSource;
    }

    /**
     * @param array $params
     * @return GlotioTranslationSourceInterface
     */
    private function createDatabaseTypeSource(array $params)
    {
        $table = $params['subtype'];
        if (!$table) {
            throw new GlotioException("Cannot create source type [database], subtype should have a value");
        }

        $model_classname = null;
        if ($table === 'cms') {
            $model_classname = 'CMS';
        } elseif ($table === 'cms_category') {
            $model_classname = 'CMSCategory';
        } else {
            $model_classname = Tools::toCamelCase($table, true);
        }

        $psModelInfo = GlotioPsModelInfo::createWithObjectModelClassName($model_classname);

        return new GlotioObjectModelTranslationSource($psModelInfo);
    }

    /**
     * @param array $params
     * @return GlotioTranslationSourceInterface
     */
    private function createDatabaseModuleSource(array $params)
    {
        $module_name  = $params['module'];
        $module_table = $params['subtype'];

        if (!$module_name) {
            throw new GlotioException("Cannot create source type [database_module], module should have a value");
        } elseif (!$module_table) {
            throw new GlotioException("Cannot create source type [database_module], subtype should have a value");
        }

        $moduleModel = $this->getModuleModelCollection()->getByModuleAndTable($module_name, $module_table);
        if (!$moduleModel) {
            throw new GlotioException("database_module not valid, cannot found [{$module_name},{$module_table}]");
        }

        $psModelInfo = GlotioPsModelInfo::createWithObjectModelClassName($moduleModel->classname);

        return new GlotioDatabaseModuleTranslationSource($module_name, $psModelInfo);
    }

    /**
     * @param array $params
     * @return GlotioTranslationSourceInterface
     */
    private function createThemeTypeSource(array $params)
    {
        $theme = $params['theme'];
        if (!$theme) {
            throw new GlotioException("Cannot create source type [theme], theme should have a value");
        }

        if ($this->ps_version === self::PS_VERSION_1_6) {
            $instance = new GlotioPs16ThemeTranslationSource($theme);
        } else {
            $instance = new GlotioPs17ThemeTranslationSource($theme);
        }

        return $instance;
    }

    /**
     * @param array $params
     * @return GlotioTranslationSourceInterface
     */
    private function createModuleTypeSource(array $params)
    {
        $module_name = $params['module'];
        if (!$module_name) {
            throw new GlotioException("Cannot create source type [module], module should have a value");
        }

        $module = Module::getInstanceByName($module_name);
        if (!$module) {
            throw new GlotioException("Module {$module_name} not found");
        }

        if (GlotioPsModules::isUsingNewTranslationSystem($module)) {
            return new GlotioPs17ModuleTranslationSource($module_name);
        } else {
            return new GlotioPs16ModuleTranslationSource($module_name);
        }
    }

    /**
     * @param array $params
     * @return GlotioTranslationSourceInterface
     */
    private function createThemeModuleTypeSource(array $params)
    {
        $theme_name  = $params['theme'];
        $module_name = $params['module'];

        if (!$theme_name) {
            throw new GlotioException("Cannot create source type [theme_module], theme should have a value");
        } elseif (!$module_name) {
            throw new GlotioException("Cannot create source type [theme_module], module should have a value");
        }

        if ($this->ps_version === self::PS_VERSION_1_7) {
            return new GlotioPs17ThemeModuleTranslationSource($theme_name, $module_name);
        } else {
            return new GlotioPs16ThemeModuleTranslationSource($theme_name, $module_name);
        }
    }

    /**
     * @param array params
     * @return GlotioTranslationSourceInterface
     */
    private function createMailTypeSource(array $params)
    {
        $type    = $params['type'];
        $subtype = $params['subtype'];
        $theme   = $params['theme'];
        $module  = $params['module'];

        switch ($type) {
            case GlotioTranslationSourceInterface::TYPE_MAIL:
                $search_path = _PS_MAIL_DIR_;
                $subtype     = 'core';
                break;
            case GlotioTranslationSourceInterface::TYPE_THEME_MAIL:
                if (!$theme) {
                    throw new GlotioException("Cannot create source type [$type], theme should have a value");
                }
                $search_path = _PS_ALL_THEMES_DIR_ . $theme . '/mails/';
                break;
            case GlotioTranslationSourceInterface::TYPE_MODULE_MAIL:
                if (!$module) {
                    throw new GlotioException("Cannot create source type [$type], module should have a value");
                }
                $search_path = _PS_MODULE_DIR_ . $module . '/mails/';
                break;
            case GlotioTranslationSourceInterface::TYPE_THEME_MODULE_MAIL:
                if (!$theme) {
                    throw new GlotioException("Cannot create source type [$type], theme should have a value");
                }
                if (!$module) {
                    throw new GlotioException("Cannot create source type [$type], module should have a value");
                }
                $search_path = _PS_ALL_THEMES_DIR_ . $theme . '/modules/' . $module . '/mails/';
                break;
            default:
                throw new GlotioException("Unsupported type mail source [$type]");
        }

        $translationSource = new GlotioMailTemplateTranslationSource($search_path, $theme, $module);
        $translationSource->setType($type);
        $translationSource->setSubtype($subtype);

        return $translationSource;
    }
}
