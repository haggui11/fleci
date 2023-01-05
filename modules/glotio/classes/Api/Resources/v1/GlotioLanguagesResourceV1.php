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

class GlotioLanguagesResourceV1 extends GlotioRestApiResource
{
    public function get(array $params)
    {
        return GlotioPsLanguages::getLanguagesData();
    }
    public function post(array $params)
    {
        return $this->put($params);
    }

    public function put(array $params)
    {
        if (!is_array($params) || !$params) {
            throw new GlotioException("Expected non empty JSON array in request body", 400);
        }

        $iso_code = isset($params['iso_code']) ? $params['iso_code'] : null;
        if (!Validate::isLanguageIsoCode($iso_code)) {
            throw new GlotioException("Invalid provided iso_code value '$iso_code'", 400);
        }

        if (in_array($iso_code, GlotioPsLanguages::getIsos())) {
            return array(
                'success' => false,
                'message' => 'This language is already installed',
            );
        }

        if (GlotioUtils::isPs17()) {
            GlotioPsKernel::getInstance();
        }

        try {
            $errors = Language::downloadAndInstallLanguagePack($iso_code, _PS_VERSION_);
            if (is_array($errors) && count($errors) > 0) {
                return $this->returnError($errors);
            }

            return array(
                'success' => true,
                'message' => 'The language was installed successfully',
            );
        } catch (Error $ex) {
            return $this->returnError($ex);
        } catch (Exception $ex) {
            return $this->returnError($ex);
        }
    }

    /**
     * @param array|Exception|Error $error
     * @return array
     */
    private function returnError($error)
    {
        return array(
            'success' => false,
            'message' => 'Error while installing language pack',
            'errors'  => is_array($error) ? $error : $error->getMessage(),
        );
    }
}
