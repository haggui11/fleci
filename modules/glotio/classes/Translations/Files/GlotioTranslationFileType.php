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

class GlotioTranslationFileType
{
    const TYPE_BACKEND = 'back';
    const TYPE_ERRORS  = 'errors';
    const TYPE_FIELDS  = 'fields';
    const TYPE_PDF     = 'pdf';
    const TYPE_TABS    = 'tabs';
    const TYPE_MAILS   = 'mails';
    const TYPE_MODULES = 'modules';
    const TYPE_THEME   = 'theme';
    const TYPE_THEME_MODULES = 'theme_modules';

    /** @var string Tipo de fichero de traducción, constantes TYPE_XXX de este fichero. */
    private $type;

    /** @var string Nombre de la variable que contiene el array de traducciones en el fichero. */
    private $var;

    /** @var string Ruta al directorio de prestashop donde se encuentran las traducciones. */
    private $dir;

    /** @var string Nombre del fichero con las traducciones. */
    private $file;

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getVar()
    {
        return $this->var;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->file;
    }
}
