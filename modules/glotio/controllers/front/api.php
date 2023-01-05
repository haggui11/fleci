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

class GlotioApiModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        try {
            // Some API request could take a long time to be processed, try to increase execution_time
            $max_execution_time = @ini_get('max_execution_time');
            $GLOBALS['glotio_max_execution_time_original'] = $max_execution_time;
            if ($max_execution_time && (int) $max_execution_time > 0 && (int) $max_execution_time < 180) {
                @ini_set('max_execution_time', 180);
            }

            if (extension_loaded('zlib')) {
                //ini_set('zlib.output_compression_level', 6);
                @ob_start('ob_gzhandler');
            }

            // Error handler
            if (!Glotio::GLOTIO_DEBUG) {
                ini_set('html_errors', 'off');
                set_error_handler(array($this, 'apiErrorHandler'));
                register_shutdown_function(array($this, 'apiFatalErrorShutdownHandler'));
            }

            $this->requireAuth();

            $result = $this->processApiRequest();

            GlotioJsonApi::returnJson($result);
        } catch (GlotioException $ex) {
            if (Glotio::GLOTIO_DEBUG) {
                GlotioJsonApi::returnJsonError(array('message' => $ex->getMessage(), 'trace' => $ex->getTrace()), $ex->getCode());
            } else {
                GlotioJsonApi::returnJsonError($ex->getMessage(), $ex->getCode());
            }
        } catch (Exception $ex) {
            if (Glotio::GLOTIO_DEBUG) {
                GlotioJsonApi::returnJsonError(array('message' => $ex->getMessage(), 'trace' => $ex->getTrace()), 500);
            } else {
                GlotioJsonApi::returnJsonError($ex->getMessage(), 500);
            }
        }
    }

    /**
     * Comprueba si la petición está autorizada o finaliza la ejecución.
     */
    private function requireAuth()
    {
        $token = Tools::getValue('token');
        if (!$token) {
            $token = $_SERVER['HTTP_TOKEN'];
        }

        if ($token !== Configuration::get('GLOTIO_API_KEY')) {
            GlotioJsonApi::returnJsonError('Not authorized', 401);
        }
    }

    /**
     * @throws GlotioException
     * @return array|object
     */
    private function processApiRequest()
    {
        $version   = filter_var(Tools::getValue('version'), FILTER_SANITIZE_NUMBER_INT);
        $resource  = filter_var(Tools::getValue('resource'), FILTER_SANITIZE_STRING);
        $processor = GlotioRestApiResourceFactory::create($version, $resource);
        $input     = Tools::file_get_contents('php://input');
        $params    = ($input) ? GlotioJsonApi::parseJsonArray($input) : array();

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                return $processor->get($params);
            case 'POST':
                return $processor->post($params);
            case 'PUT':
                return $processor->put($params);
            case 'DELETE':
                return $processor->delete($params);
            default:
                throw new GlotioException("Method not allowed", 405);
        }
    }

    public function apiErrorHandler($code, $message, $file, $line)
    {
        return true;
    }

    public function apiFatalErrorShutdownHandler()
    {
        $last_error = error_get_last();
        if (in_array($last_error['type'], array(E_ERROR, E_RECOVERABLE_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR))) {
            GlotioJsonApi::returnJsonError($last_error, 500);
            exit;
        }
    }
}
