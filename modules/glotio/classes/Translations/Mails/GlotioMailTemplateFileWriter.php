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

abstract class GlotioMailTemplateFileWriter
{
    /**
     * Saves mail template y provided path. If needed it creates missing directories in filePath.
     * @param string $filePath
     * @param string $content
     * @throws GlotioException
     */
    public static function saveMailTemplate($filePath, $content)
    {
        if (!preg_match(GlotioMailTemplate::REGEX_CHECK_MAIL_PATH, $filePath, $matches)) {
            throw new GlotioException("Invalid path [$filePath], doesn't have an iso code like /mails/en/");
        }

        GlotioUtils::createParentDirOfPathIfNotExists($filePath);

        $dir = pathinfo($filePath, PATHINFO_DIRNAME);
        if ((file_exists($filePath) && !is_writable($filePath)) || !is_dir($dir)) {
            throw new GlotioException("Mail template " . GlotioUtils::removePsFromPath($filePath) . " is not writable");
        }

        $write_bytes = (int) file_put_contents($filePath, $content);
        if (!$write_bytes) {
            throw new GlotioException("Error while writing mail template in " . GlotioUtils::removePsFromPath($filePath));
        }
    }
}
