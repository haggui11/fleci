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

class GlotioModuleModelCache
{
    const MODELS_CACHE_INDEX_FILENAME  = 'parsed_models.index.%s.php';
    const MODELS_CACHE_SOURCE_FILENAME = 'parsed_models.cache.%s.php';

    /** @var string */
    private $cache_directory;

    /** @var string */
    private $cache_key;

    /**
     * ModuleModelCache constructor.
     * @param string $cache_directory
     * @param string $cache_key
     */
    public function __construct($cache_directory, $cache_key)
    {
        $this->cache_directory = rtrim($cache_directory, '/');
        $this->cache_key = $cache_key;
    }

    /**
     * @return bool
     */
    public function exists()
    {
        $cache_index_file  = $this->getIndexFilePath();
        $cache_source_file = $this->getSourceFilePath();

        return file_exists($cache_index_file) && file_exists($cache_source_file);
    }

    /**
     * @return bool
     */
    public function isObsolete()
    {
        if (!$this->exists()) {
            return true;
        }

        $created_date_cache_file = date("Ymd", filemtime($this->getIndexFilePath()));

        // 1 day cache is valid
        return $created_date_cache_file < date("Ymd");
    }

    /**
     * @return GlotioModuleModel[]
     */
    public function load()
    {
        $moduleModels = array();

        if (!$this->exists()) {
            return $moduleModels;
        }

        require_once $this->getSourceFilePath();

        $parsed_models_index = include $this->getIndexFilePath();

        if (is_array($parsed_models_index)) {
            foreach ($parsed_models_index as $item) {
                $moduleModels[] = GlotioModuleModel::createWithArray($item);
            }
        }

        return $moduleModels;
    }

    /**
     * @param GlotioModuleModel[] $moduleModels
     * @throws GlotioException
     */
    public function store(array $moduleModels)
    {
        if (!$moduleModels) {
            throw new GlotioException("Can't store in cache an empty ModuleModel array");
        }

        $created_class_index       = array();
        $created_class_source_code = '';

        foreach ($moduleModels as $moduleModel) {
            $created_class_index[] = $moduleModel->getIndex();
            $created_class_source_code .= $moduleModel->source_code . "\n\n";
        }

        $cache_index_file = $this->getIndexFilePath();
        $write_index      = file_put_contents($cache_index_file, "<?php\n\nreturn " . var_export($created_class_index, true) . ";");
        if (!$write_index) {
            throw new GlotioException("Can't write to location [$cache_index_file]");
        }

        $cache_source_file = $this->getSourceFilePath();
        $write_source      = file_put_contents($cache_source_file, "<?php\n\n" . $created_class_source_code);
        if (!$write_source) {
            throw new GlotioException("Can't write to location [$cache_source_file]");
        }
    }

    /**
     * @return bool
     */
    public function reset()
    {
        $reseted = true;

        // Remove the last .php part to delete all cache files
        $cache_files = array(
            str_replace('.php', '', $this->getIndexFilePath('*')),
            str_replace('.php', '', $this->getSourceFilePath('*')),
        );

        foreach ($cache_files as $cache_file) {
            $files_to_delete = glob($cache_file);
            if (is_array($files_to_delete)) {
                foreach ($files_to_delete as $file_to_delete) {
                    if (is_file($file_to_delete) && is_writeable($file_to_delete)) {
                        $reseted = $reseted & @unlink($file_to_delete);
                    }
                }
            }
        }

        return $reseted;
    }

    /**
     * @param string $cache_key
     * @return string
     */
    private function getIndexFilePath($cache_key = null)
    {
        return $this->cache_directory . '/' . sprintf(self::MODELS_CACHE_INDEX_FILENAME, $cache_key ? $cache_key : $this->cache_key);
    }

    /**
     * @param string $cache_key
     * @return string
     */
    private function getSourceFilePath($cache_key = null)
    {
        return $this->cache_directory . '/' . sprintf(self::MODELS_CACHE_SOURCE_FILENAME, $cache_key ? $cache_key : $this->cache_key);
    }
}
