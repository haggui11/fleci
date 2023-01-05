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
 * Representa una plantilla de email de Prestashop.
 */
class GlotioMailTemplate
{
    /** @var string Expresión regular para comprobar el path a una carpeta de mails */
    const REGEX_CHECK_MAIL_PATH = '/\/mails\/([a-z]{2})\/(.+)\.(html|txt)+/';

    /** @var string */
    public $name;

    /** @var string */
    public $type;

    /** @var string */
    public $file_path;

    /** @var string */
    public $iso_lang;

    /** @var string */
    public $content;

    /**
     * Crea una nueva instancia a partir de la ruta al fichero de plantilla.
     * @param string $file_path
     * @return self
     * @throws GlotioException
     */
    public static function createWithFilePath($file_path)
    {
        if (!preg_match(self::REGEX_CHECK_MAIL_PATH, $file_path, $matches)) {
            throw new GlotioException("Invalid path [$file_path], doesn't have an iso code like /mails/en/");
        }

        $instance            = new self();
        $instance->iso_lang  = $matches[1];
        $instance->name      = $matches[2];
        $instance->type      = $matches[3];
        $instance->file_path = $file_path;

        return $instance;
    }

    /**
     * @return string
     */
    public function getTemplateId()
    {
        return $this->name . '_' . $this->type;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        if (!$this->content && file_exists($this->file_path)) {
            $this->content = file_get_contents($this->file_path);
            if (!$this->content) {
                $this->content = null;
            }
        }

        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}
