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

abstract class GlotioPsModules
{
    /**
     * @var string[] List of modules that should not be analyzed by Glotio in any way.
     */
    private static $modules_blacklist = array(
        'glotio',
        'orbitvushn',
        'gauthenticator',
    );

    /**
     * @var string[] List of modules that should't be analyzed to find ObjectModels.
     * Don't need to add a module here if is already present on modules_blacklist.
     */
    private static $object_models_modules_blacklist = array(
        'cpaliexpress',
        'gamification',
    );

    /**
     * Devuelve un listado de módulos presentes en el directorio de módulos.
     * Versión del mismo método del core de Prestashop que no lanza excepciones.
     * @return array Modules Directory List
     * @see Module::getModulesDirOnDisk
     */
    public static function getModulesDirOnDisk()
    {
        $module_list = array();
        $modules = scandir(_PS_MODULE_DIR_);
        foreach ($modules as $name) {
            if (is_file(_PS_MODULE_DIR_ . $name)) {
                continue;
            } elseif (is_dir(_PS_MODULE_DIR_ . $name . DIRECTORY_SEPARATOR) && Tools::file_exists_cache(_PS_MODULE_DIR_ . $name . '/' . $name . '.php')) {
                $module_list[] = $name;
            }
        }

        return $module_list;
    }

    /**
     * Get information about modules installed in the shop.
     * @return array
     */
    public static function getModulesInfo()
    {
        $module_list = array();

        $installed_modules = array_column(Module::getModulesInstalled(), 'name');

        foreach (self::getModulesDirOnDisk() as $module) {
            // Load config.xml file associated to the module
            $config_file = _PS_MODULE_DIR_.$module.'/config.xml';
            $module_file = _PS_MODULE_DIR_.$module.'/'.$module.'.php';
            if (Tools::file_exists_cache($config_file)) {
                // Load xml
                libxml_use_internal_errors(true);
                $xml_module = @simplexml_load_file($config_file);
                if (!$xml_module || libxml_get_errors()) {
                    continue;
                }
                libxml_clear_errors();

                // Push items to array
                $items = array();
                foreach ($xml_module as $k => $v) {
                    $items[$k] = (string)$v;
                }

                if (!isset($items['name']) || !isset($items['version'])) {
                    continue;
                }

                $module_list[] = array(
                    'name'    => trim($items['name']),
                    'version' => trim($items['version']),
                    'tab'     => isset($items['tab']) ? trim($items['tab']) : null,
                    'active'  => in_array($module, $installed_modules) && Module::isEnabled($module),
                );
            } else if (Tools::file_exists_cache($module_file)) {
                $module_contents = Tools::file_get_contents($module_file);
                if (preg_match('/^\s*\$this\-\>name\s*\=\s*[\'|"](.+)[\'|"]\;$/mi', $module_contents, $mod_name)
                    && preg_match('/^\s*\$this\-\>version\s*\=\s*[\'|"](.+)[\'|"]\;$/mi', $module_contents, $mod_version)
                    && preg_match('/^\s*\$this\-\>tab\s*\=\s*[\'|"](.+)[\'|"]\;$/mi', $module_contents, $mod_tab)
                ) {
                    $module_list[] = array(
                        'name'    => trim($mod_name[1]),
                        'version' => trim($mod_version[1]),
                        'tab'     => trim($mod_tab[1]),
                        'active'  => in_array($module, $installed_modules) && Module::isEnabled($module),
                    );
                }
            }
        }

        return $module_list;
    }

    /**
     * Recupera un listado de los módulos activos.
     * @return array
     */
    public static function getTranslatableModules()
    {
        static $translatableModules;

        if (is_null($translatableModules)) {
            $on_disk_modules     = self::getModulesDirOnDisk();
            $installed_modules   = array_column(Module::getModulesInstalled(), 'name');
            $translatableModules = array_intersect($on_disk_modules, $installed_modules);
            $translatableModules = array_filter($translatableModules, 'GlotioPsModules::filterEnabledModules');
            $translatableModules = array_filter($translatableModules, 'GlotioPsModules::filterModulesBlacklist');
        }

        return $translatableModules;
    }

    /**
     * Obtiene un listado de los módulos en los que deben buscarse ObjectModels en su código fuente.
     * @return array
     */
    public static function getTranslatableModulesWithObjectModels()
    {
        $translatableModules = self::getTranslatableModules();
        $translatableModules = array_filter($translatableModules, 'GlotioPsModules::filterObjectModelModulesBlacklist');

        return $translatableModules;
    }

    /**
     * Recupera un listado de los módulos que tienen carpeta de mails (en la carpeta del módulo o en la carpeta del módulo en el tema).
     * @return array
     */
    public static function getTranslatableModuleWithMails()
    {
        static $translatableMailModules;

        if (is_null($translatableMailModules)) {
            $translatableMailModules = self::getModuleWithMailsFolderInPath(_PS_MODULE_DIR_);
        }

        return $translatableMailModules;
    }

    /**
     * Recupera un listado de los módulos que tienen carpeta de mails.
     * @return array
     */
    public static function getTranslatableThemeModuleWithMails()
    {
        static $translatableMailThemeModules;

        if (is_null($translatableMailThemeModules)) {
            $translatableMailThemeModules = self::getModuleWithMailsFolderInPath(_PS_THEME_DIR_ . 'modules/');
        }

        return $translatableMailThemeModules;
    }

    /**
     * Recupera un listado de los módulos que tienen carpeta de mails en las carpetas de módulos en el tema activo.
     * @param string $search_path
     * @return array
     */
    public static function getModuleWithMailsFolderInPath($search_path)
    {
        $translatableModules = self::getTranslatableModules();

        $modules = array();

        foreach ($translatableModules as $module) {
            $dir = $search_path . $module . '/';
            if (is_dir($dir) && in_array('mails', scandir($dir))) {
                $modules[] = $module;
            }
        }

        return $modules;
    }

    /**
     * @param string $module
     * @return bool
     */
    public static function filterEnabledModules($module)
    {
        return Module::isEnabled($module);
    }

    /**
     * @param string $module
     * @return bool
     */
    public static function filterModulesBlacklist($module)
    {
        return !in_array($module, self::$modules_blacklist);
    }

    /**
     * @param string $module
     * @return bool
     */
    public static function filterObjectModelModulesBlacklist($module)
    {
        return !in_array($module, self::$object_models_modules_blacklist);
    }

    /**
     * @param Module $module
     * @return bool
     * @see Module::isUsingNewTranslationSystem
     * @see AdminTranslationsControllerCore::isUsingNewTranslationSystem
     */
    public static function isUsingNewTranslationSystem($module)
    {
        static $domains;

        if (!GlotioUtils::isPs17()) {
            return false;
        }

        $is_prestashop_module          = Tools::strtolower($module->author) === 'prestashop';
        $non_core_modules_translatable = version_compare(_PS_VERSION_, '1.7.6', '>=');
        if (($is_prestashop_module || $non_core_modules_translatable)) {
            if (is_callable(array($module, 'isUsingNewTranslationSystem'))) {
                return $module->isUsingNewTranslationSystem();
            } else {
                // Backport of isUsingNewTranslationSystem method. First 1.7.x versions don't have this method on Module class
                if (!$domains) {
                    $domains = array_keys(Context::getContext()->getTranslator()->getCatalogue()->all());
                }

                $moduleName = preg_replace('/^ps_(\w+)/', '$1', $module->name);

                foreach ($domains as $domain) {
                    if (false !== stripos($domain, $moduleName)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
