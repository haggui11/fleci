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
 * Ayuda a buscar ficheros con una extensión determinada en un directorio.
 * @package Glotio\Helpers
 */
class GlotioFileSearch
{
    /** @var string */
    private $dir;

    /** @var string */
    private $file_ext;

    /** @var array */
    private $ignored = array(
        '.',
        '..',
        '.svn',
        '.git',
        '.htaccess',
        'index.php',
    );

    /** @var bool */
    private $recursive = true;

    /** @var array */
    private $found_files;

    /**
     * @param string $dir
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Añade un nombre de directorio/fichero para ignorarlo.
     * @param array|string $ignored_names
     * return self
     */
    public function addIgnore($ignored_names)
    {
        $ignored_names = is_array($ignored_names) ? $ignored_names : array($ignored_names);

        foreach ($ignored_names as $ignored_name) {
            $this->ignored[] = $ignored_name;
        }

        return $this;
    }

    /**
     * @param bool $recursive
     * @return GlotioFileSearch
     */
    public function setRecursive($recursive)
    {
        $this->recursive = $recursive;
        return $this;
    }

    /**
     * Devuelve una lista de rutas  de ficheros con la extensión indicada.
     * Los archivos/directorios ignorados no se devuelven.
     * @param string $file_ext
     * @return array
     */
    public function find($file_ext)
    {
        $this->found_files = array();

        $this->file_ext = '.' . ltrim($file_ext, '.');
        $this->_find($this->dir);

        return $this->found_files;
    }

    /**
     * Recursively list files in directory $dir
     * @param string $dir
     */
    private function _find($dir)
    {
        $dir = rtrim($dir, '/').DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $file) {
            if (!in_array($file, $this->ignored)) {
                if (preg_match('#'.preg_quote($this->file_ext, '#').'$#i', $file)) {
                    $this->found_files[] = $dir.$file;
                } elseif (is_dir($dir.$file) && $this->recursive) {
                    $this->_find($dir.$file);
                }
            }
        }
    }
}
