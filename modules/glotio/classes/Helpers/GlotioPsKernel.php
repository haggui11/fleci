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

abstract class GlotioPsKernel
{
    /** @var AppKernel */
    private static $instance;

    /**
     * @return AppKernel
     * @throws GlotioException
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            if (file_exists(_PS_ROOT_DIR_.'/app/AppKernel.php')) {
                require_once _PS_ROOT_DIR_.'/app/AppKernel.php';
            }

            if (!class_exists('AppKernel') || GlotioUtils::isPs16()) {
                throw new GlotioException("Can't load Prestashop Symfony Kernel, AppKernel class not found. Is this Prestashop 1.7+?");
            }

            self::$instance = new AppKernel(_PS_MODE_DEV_? 'dev' : 'prod', _PS_MODE_DEV_);
            self::$instance->boot();

            /**
             * Set global $kernel variable. When Glotio API is invoked from front Prestashop Context, this container isn't loaded.
             * We need it to use Translation services and setup languages.
             * @see SymfonyContainer::getInstance
             */
            if (!isset($GLOBALS['kernel']) || !$GLOBALS['kernel']) {
                $GLOBALS['kernel'] = self::$instance;
            }
        }

        return self::$instance;
    }
}
