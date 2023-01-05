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

abstract class GlotioRestApiResourceFactory
{
    /** @var array Mapa de clases a cargar dependiendo de la versión y el recurso solicitados al factory.
     * Formato: [version][resource] = classpath
     */
    private static $classMap = array(
        '1' => array(
            'languages'           => 'GlotioLanguagesResourceV1',
            'query'               => 'GlotioQueryResourceV1',
            'status'              => 'GlotioStatusResourceV1',
            'translation-sources' => 'GlotioTranslationSourcesResourceV1',
            'translation-texts'   => 'GlotioTranslationTextsResourceV1',
            'translation-urls'    => 'GlotioTranslationUrlsResourceV1',
        )
    );

    /**
     * @param int $version
     * @param string $resource
     * @return GlotioRestApiResourceInterface
     * @throws GlotioException
     */
    public static function create($version, $resource)
    {
        if (!isset($version)) {
            throw new GlotioException("Version not defined");
        }
        if (!isset($resource)) {
            throw new GlotioException("Resource not defined");
        }

        if (!isset(self::$classMap[$version][$resource])) {
            throw new GlotioException("Resource path v{$version}/{$resource} not found", 404);
        }

        $resource_classname = self::$classMap[$version][$resource];

        $resourceInstance = new $resource_classname();
        if (!$resourceInstance instanceof GlotioRestApiResourceInterface) {
            throw new GlotioException("$resource_classname doesn't implements RestApiResourceInterface");
        }

        return $resourceInstance;
    }
}
