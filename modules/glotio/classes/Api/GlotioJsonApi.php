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
 * Métodos para el API
 */
abstract class GlotioJsonApi
{
    /**
     * Parsea la string JSON pasada en un array asociativo.
     * @param string $json_string
     * @return array
     */
    public static function parseJsonArray($json_string)
    {
        $params = json_decode($json_string, true);

        if (is_null($params)) {
            if (function_exists('json_last_error') && JSON_ERROR_NONE !== json_last_error()) {
                self::returnJsonError('Unable to parse JSON input. ' . json_last_error());
            } else {
                self::returnJsonError('Unable to parse JSON input');
            }
        }

        if (!is_array($params)) {
            $params = array($params);
        }

        return $params;
    }

    /**
     * Devuelve el objeto pasado convertido a JSON y finaliza la ejecución.
     * @param mixed $object
     */
    public static function returnJson($object)
    {
        self::addHeaders();

        $object = self::utf8ize($object);
        $json   = json_encode($object);

        if ($json === false) {
            if (function_exists('json_last_error') && JSON_ERROR_NONE !== json_last_error()) {
                self::returnJsonError('Unable to encode JSON output. ' . json_last_error() . ' - ' . json_last_error_msg(), 500);
            } else {
                self::returnJsonError('Unable to encode JSON output.', 500);
            }
        }

        echo $json;
        exit;
    }

    /**
     * Devuelve un mensaje de error en formato JSON y finaliza la ejecución.
     * @param array|string $message
     * @param int $status_code
     */
    public static function returnJsonError($message, $status_code = 500)
    {
        self::httpResponseCode($status_code);
        self::returnJson(array('error' => true, 'message' => $message));
    }

    /**
     * Añade las cabeceras para evitar cacheo de las respuestas del API.
     */
    public static function addHeaders()
    {
        header('Content-Type: application/json; charset=UTF-8');
        header("Expires: on, 01 Jan 1970 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    /**
     * Use it for json_encode some corrupt UTF-8 chars useful for = malformed utf-8 characters possibly incorrectly encoded by json_encode.
     * @see https://stackoverflow.com/questions/46305169/php-json-encode-malformed-utf-8-characters-possibly-incorrectly-encoded
     * @param mixed $mixed
     * @return array|false|string|string[]|null
     */
    public static function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = self::utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            if (function_exists('mb_convert_encoding')) {
                if (function_exists('iconv')) {
                    $mixed = iconv(mb_detect_encoding($mixed, mb_detect_order(), true), 'UTF-8', $mixed);
                    //$mixed = mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
                } else {
                    $mixed = mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
                }
            }
        }

        return $mixed;
    }

    /**
     * Set HTTP response code
     * @param int $code
     * @return int|mixed|null
     */
    public static function httpResponseCode($code = null)
    {
        if ($code !== null) {
            switch ($code) {
                case 100:
                    $text = 'Continue';
                    break;
                case 101:
                    $text = 'Switching Protocols';
                    break;
                case 200:
                    $text = 'OK';
                    break;
                case 201:
                    $text = 'Created';
                    break;
                case 202:
                    $text = 'Accepted';
                    break;
                case 203:
                    $text = 'Non-Authoritative Information';
                    break;
                case 204:
                    $text = 'No Content';
                    break;
                case 205:
                    $text = 'Reset Content';
                    break;
                case 206:
                    $text = 'Partial Content';
                    break;
                case 300:
                    $text = 'Multiple Choices';
                    break;
                case 301:
                    $text = 'Moved Permanently';
                    break;
                case 302:
                    $text = 'Moved Temporarily';
                    break;
                case 303:
                    $text = 'See Other';
                    break;
                case 304:
                    $text = 'Not Modified';
                    break;
                case 305:
                    $text = 'Use Proxy';
                    break;
                case 400:
                    $text = 'Bad Request';
                    break;
                case 401:
                    $text = 'Unauthorized';
                    break;
                case 402:
                    $text = 'Payment Required';
                    break;
                case 403:
                    $text = 'Forbidden';
                    break;
                case 404:
                    $text = 'Not Found';
                    break;
                case 405:
                    $text = 'Method Not Allowed';
                    break;
                case 406:
                    $text = 'Not Acceptable';
                    break;
                case 407:
                    $text = 'Proxy Authentication Required';
                    break;
                case 408:
                    $text = 'Request Time-out';
                    break;
                case 409:
                    $text = 'Conflict';
                    break;
                case 410:
                    $text = 'Gone';
                    break;
                case 411:
                    $text = 'Length Required';
                    break;
                case 412:
                    $text = 'Precondition Failed';
                    break;
                case 413:
                    $text = 'Request Entity Too Large';
                    break;
                case 414:
                    $text = 'Request-URI Too Large';
                    break;
                case 415:
                    $text = 'Unsupported Media Type';
                    break;
                case 500:
                    $text = 'Internal Server Error';
                    break;
                case 501:
                    $text = 'Not Implemented';
                    break;
                case 502:
                    $text = 'Bad Gateway';
                    break;
                case 503:
                    $text = 'Service Unavailable';
                    break;
                case 504:
                    $text = 'Gateway Time-out';
                    break;
                case 505:
                    $text = 'HTTP Version not supported';
                    break;
                default:
                    $text = 'Internal Server Error';
                    break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);
            $GLOBALS['http_response_code'] = $code;
        } else {
            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }

        return $code;
    }
}
