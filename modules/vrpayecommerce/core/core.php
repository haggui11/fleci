<?php
/**
* 2015 VR pay eCommerce
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
*  @author    VR pay eCommerce <info@vr-epay.info>
*  @copyright 2015 VR pay eCommerce
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of VR pay eCommerce
*/

class VRpayecommercePaymentCore
{
    protected static $checkoutUrlLive = 'https://vr-pay-ecommerce.de/v1/checkouts';
    protected static $checkoutUrlTest = 'https://test.vr-pay-ecommerce.de/v1/checkouts';

    protected static $backofficeUrlLive = 'https://vr-pay-ecommerce.de/v1/payments/';
    protected static $backofficeUrlTest = 'https://test.vr-pay-ecommerce.de/v1/payments/';

    protected static $serverToServerUrlLive = 'https://vr-pay-ecommerce.de/v1/payments';
    protected static $serverToServerUrlTest = 'https://test.vr-pay-ecommerce.de/v1/payments';

    protected static $paymentWidgetUrlLive = 'https://vr-pay-ecommerce.de/v1/paymentWidgets.js?checkoutId=';
    protected static $paymentWidgetUrlTest = 'https://test.vr-pay-ecommerce.de/v1/paymentWidgets.js?checkoutId=';

    protected static $registerUrlLive = 'https://vr-pay-ecommerce.de/v1/registrations/';
    protected static $registerUrlTest = 'https://test.vr-pay-ecommerce.de/v1/registrations/';

    protected static $queryUrlLive = 'https://ctpe.net/payment/query';
    protected static $queryUrlTest = 'https://test.ctpe.net/payment/query';

    protected static $ackReturnCodes = array (
        '000.000.000',
        '000.100.110',
        '000.100.111',
        '000.100.112',
        '000.100.200',
        '000.100.201',
        '000.100.202',
        '000.100.203',
        '000.100.204',
        '000.100.205',
        '000.100.206',
        '000.100.207',
        '000.100.208',
        '000.100.209',
        '000.100.210',
        '000.100.220',
        '000.100.221',
        '000.100.222',
        '000.100.223',
        '000.100.224',
        '000.100.225',
        '000.100.226',
        '000.100.227',
        '000.100.228',
        '000.100.229',
        '000.100.230',
        '000.100.299',
        '000.200.000',
        '000.300.000',
        '000.300.100',
        '000.300.101',
        '000.300.102',
        '000.400.000',
        '000.400.010',
        '000.400.020',
        '000.400.030',
        '000.400.040',
        '000.400.050',
        '000.400.060',
        '000.400.070',
        '000.400.080',
        '000.400.090',
        '000.400.101',
        '000.400.102',
        '000.400.103',
        '000.400.104',
        '000.400.105',
        '000.400.106',
        '000.400.107',
        '000.400.108',
        '000.400.200',
        '000.500.000',
        '000.500.100',
        '000.600.000'
    );

    protected static $nokReturnCodes = array (
        '100.100.100',
        '100.100.101',
        '100.100.200',
        '100.100.201',
        '100.100.300',
        '100.100.301',
        '100.100.303',
        '100.100.304',
        '100.100.400',
        '100.100.401',
        '100.100.402',
        '100.100.500',
        '100.100.501',
        '100.100.600',
        '100.100.601',
        '100.100.650',
        '100.100.651',
        '100.100.700',
        '100.100.701',
        '100.150.100',
        '100.150.101',
        '100.150.200',
        '100.150.201',
        '100.150.202',
        '100.150.203',
        '100.150.204',
        '100.150.205',
        '100.150.300',
        '100.200.100',
        '100.200.103',
        '100.200.104',
        '100.200.200',
        '100.210.101',
        '100.210.102',
        '100.211.101',
        '100.211.102',
        '100.211.103',
        '100.211.104',
        '100.211.105',
        '100.211.106',
        '100.212.101',
        '100.212.102',
        '100.212.103',
        '100.250.100',
        '100.250.105',
        '100.250.106',
        '100.250.107',
        '100.250.110',
        '100.250.111',
        '100.250.120',
        '100.250.121',
        '100.250.122',
        '100.250.123',
        '100.250.124',
        '100.250.125',
        '100.250.250',
        '100.300.101',
        '100.300.200',
        '100.300.300',
        '100.300.400',
        '100.300.401',
        '100.300.402',
        '100.300.501',
        '100.300.600',
        '100.300.601',
        '100.300.700',
        '100.300.701',
        '100.350.100',
        '100.350.101',
        '100.350.200',
        '100.350.201',
        '100.350.301',
        '100.350.302',
        '100.350.303',
        '100.350.310',
        '100.350.311',
        '100.350.312',
        '100.350.313',
        '100.350.314',
        '100.350.315',
        '100.350.400',
        '100.350.500',
        '100.350.600',
        '100.350.601',
        '100.350.610',
        '100.360.201',
        '100.360.300',
        '100.360.303',
        '100.360.400',
        '100.370.100',
        '100.370.101',
        '100.370.102',
        '100.370.110',
        '100.370.111',
        '100.370.121',
        '100.370.122',
        '100.370.123',
        '100.370.124',
        '100.370.125',
        '100.370.131',
        '100.370.132',
        '100.380.100',
        '100.380.101',
        '100.380.110',
        '100.380.201',
        '100.380.305',
        '100.380.306',
        '100.380.401',
        '100.380.501',
        '100.390.101',
        '100.390.102',
        '100.390.103',
        '100.390.104',
        '100.390.105',
        '100.390.106',
        '100.390.107',
        '100.390.108',
        '100.390.109',
        '100.390.110',
        '100.390.111',
        '100.390.112',
        '100.390.113',
        '100.395.101',
        '100.395.102',
        '100.395.501',
        '100.395.502',
        '100.396.101',
        '100.396.102',
        '100.396.103',
        '100.396.104',
        '100.396.106',
        '100.396.201',
        '100.397.101',
        '100.397.102',
        '100.400.000',
        '100.400.001',
        '100.400.002',
        '100.400.005',
        '100.400.007',
        '100.400.020',
        '100.400.021',
        '100.400.030',
        '100.400.039',
        '100.400.040',
        '100.400.041',
        '100.400.042',
        '100.400.043',
        '100.400.044',
        '100.400.045',
        '100.400.051',
        '100.400.060',
        '100.400.061',
        '100.400.063',
        '100.400.064',
        '100.400.065',
        '100.400.071',
        '100.400.080',
        '100.400.081',
        '100.400.083',
        '100.400.084',
        '100.400.085',
        '100.400.086',
        '100.400.087',
        '100.400.091',
        '100.400.100',
        '100.400.120',
        '100.400.121',
        '100.400.122',
        '100.400.123',
        '100.400.130',
        '100.400.139',
        '100.400.140',
        '100.400.141',
        '100.400.142',
        '100.400.143',
        '100.400.144',
        '100.400.145',
        '100.400.146',
        '100.400.147',
        '100.400.148',
        '100.400.149',
        '100.400.150',
        '100.400.151',
        '100.400.152',
        '100.400.241',
        '100.400.242',
        '100.400.243',
        '100.400.260',
        '100.400.300',
        '100.400.301',
        '100.400.302',
        '100.400.303',
        '100.400.304',
        '100.400.305',
        '100.400.306',
        '100.400.307',
        '100.400.308',
        '100.400.309',
        '100.400.310',
        '100.400.311',
        '100.400.312',
        '100.400.313',
        '100.400.314',
        '100.400.315',
        '100.400.316',
        '100.400.317',
        '100.400.318',
        '100.400.319',
        '100.400.320',
        '100.400.321',
        '100.400.322',
        '100.400.323',
        '100.400.324',
        '100.400.325',
        '100.400.326',
        '100.400.327',
        '100.400.328',
        '100.400.500',
        '100.500.101',
        '100.500.201',
        '100.500.301',
        '100.500.302',
        '100.550.300',
        '100.550.301',
        '100.550.303',
        '100.550.310',
        '100.550.311',
        '100.550.312',
        '100.550.400',
        '100.550.401',
        '100.550.601',
        '100.550.603',
        '100.550.605',
        '100.600.500',
        '100.700.100',
        '100.700.101',
        '100.700.200',
        '100.700.201',
        '100.700.300',
        '100.700.400',
        '100.700.500',
        '100.700.800',
        '100.700.801',
        '100.700.802',
        '100.700.810',
        '100.800.100',
        '100.800.101',
        '100.800.102',
        '100.800.200',
        '100.800.201',
        '100.800.202',
        '100.800.300',
        '100.800.301',
        '100.800.302',
        '100.800.400',
        '100.800.401',
        '100.800.500',
        '100.800.501',
        '100.900.100',
        '100.900.101',
        '100.900.105',
        '100.900.200',
        '100.900.300',
        '100.900.301',
        '100.900.400',
        '100.900.401',
        '100.900.450',
        '100.900.500',
        '200.100.101',
        '200.100.102',
        '200.100.103',
        '200.100.150',
        '200.100.151',
        '200.100.199',
        '200.100.201',
        '200.100.300',
        '200.100.301',
        '200.100.302',
        '200.100.401',
        '200.100.402',
        '200.100.403',
        '200.100.404',
        '200.100.501',
        '200.100.502',
        '200.100.503',
        '200.100.504',
        '200.200.106',
        '200.300.403',
        '200.300.404',
        '200.300.405',
        '200.300.406',
        '200.300.407',
        '500.100.201',
        '500.100.202',
        '500.100.203',
        '500.100.301',
        '500.100.302',
        '500.100.303',
        '500.100.304',
        '500.100.401',
        '500.100.402',
        '500.100.403',
        '500.200.101',
        '600.100.100',
        '600.200.100',
        '600.200.200',
        '600.200.201',
        '600.200.202',
        '600.200.300',
        '600.200.310',
        '600.200.400',
        '600.200.500',
        '600.200.600',
        '600.200.700',
        '600.200.800',
        '600.200.810',
        '700.100.100',
        '700.100.200',
        '700.100.300',
        '700.100.400',
        '700.100.500',
        '700.100.600',
        '700.100.700',
        '700.100.701',
        '700.100.710',
        '700.300.100',
        '700.300.200',
        '700.300.300',
        '700.300.400',
        '700.300.500',
        '700.300.600',
        '700.300.700',
        '700.400.000',
        '700.400.100',
        '700.400.101',
        '700.400.200',
        '700.400.300',
        '700.400.400',
        '700.400.402',
        '700.400.410',
        '700.400.420',
        '700.400.510',
        '700.400.520',
        '700.400.530',
        '700.400.540',
        '700.400.550',
        '700.400.560',
        '700.400.561',
        '700.400.562',
        '700.400.570',
        '700.400.700',
        '700.450.001',
        '800.100.100',
        '800.100.150',
        '800.100.151',
        '800.100.152',
        '800.100.153',
        '800.100.154',
        '800.100.155',
        '800.100.156',
        '800.100.157',
        '800.100.158',
        '800.100.159',
        '800.100.160',
        '800.100.161',
        '800.100.162',
        '800.100.163',
        '800.100.164',
        '800.100.165',
        '800.100.166',
        '800.100.167',
        '800.100.168',
        '800.100.169',
        '800.100.170',
        '800.100.171',
        '800.100.172',
        '800.100.173',
        '800.100.174',
        '800.100.175',
        '800.100.176',
        '800.100.177',
        '800.100.178',
        '800.100.179',
        '800.100.190',
        '800.100.191',
        '800.100.192',
        '800.100.195',
        '800.100.196',
        '800.100.197',
        '800.100.198',
        '800.100.402',
        '800.100.500',
        '800.100.501',
        '800.110.100',
        '800.120.100',
        '800.120.101',
        '800.120.102',
        '800.120.103',
        '800.120.200',
        '800.120.201',
        '800.120.202',
        '800.120.203',
        '800.120.300',
        '800.120.401',
        '800.121.100',
        '800.130.100',
        '800.140.100',
        '800.140.101',
        '800.140.110',
        '800.140.111',
        '800.140.112',
        '800.140.113',
        '800.150.100',
        '800.160.100',
        '800.160.110',
        '800.160.120',
        '800.160.130',
        '800.200.159',
        '800.200.160',
        '800.200.165',
        '800.200.202',
        '800.200.208',
        '800.200.220',
        '800.300.101',
        '800.300.102',
        '800.300.200',
        '800.300.301',
        '800.300.302',
        '800.300.401',
        '800.300.500',
        '800.300.501',
        '800.400.100',
        '800.400.101',
        '800.400.102',
        '800.400.103',
        '800.400.104',
        '800.400.105',
        '800.400.110',
        '800.400.150',
        '800.400.151',
        '800.400.200',
        '800.400.500',
        '800.500.100',
        '800.500.110',
        '800.600.100',
        '800.700.100',
        '800.700.101',
        '800.700.201',
        '800.700.500',
        '800.800.102',
        '800.800.202',
        '800.800.302',
        '800.800.800',
        '800.800.801',
        '800.900.100',
        '800.900.101',
        '800.900.200',
        '800.900.201',
        '800.900.300',
        '800.900.301',
        '800.900.302',
        '800.900.303',
        '800.900.401',
        '800.900.450',
        '900.100.100',
        '900.100.200',
        '900.100.201',
        '900.100.202',
        '900.100.203',
        '900.100.300',
        '900.100.400',
        '900.100.500',
        '900.100.600',
        '900.200.100',
        '900.300.600',
        '900.400.100',
        '999.999.999'
    );

    /**
     * OPP URL
     *
     */

    /**
     * Get checkout URL
     *
     * @param string $serverMode server mode
     * @return string
     */
    private static function getCheckoutUrl($serverMode)
    {
        if ($serverMode == "LIVE") {
            return self::$checkoutUrlLive;
        } else {
            return self::$checkoutUrlTest;
        }
    }

    /**
     * Get payment status URL
     *
     * @param string $serverMode server mode
     * @param string $checkoutId checkout id.
     * @return string
     */
    private static function getPaymentStatusUrl($serverMode, $checkoutId)
    {
        if ($serverMode == "LIVE") {
            return self::$checkoutUrlLive . '/' . $checkoutId. "/payment";
        } else {
            return self::$checkoutUrlTest . '/' . $checkoutId. "/payment";
        }
    }

    /**
     * Get the server to server URL based on the server mode for capture request
     *
     * @param string $server_mode server mode.
     * @param string $checkoutId checkout id.
     * @return string
     */
    private static function getPaymentServerToServerStatusUrl($serverMode, $checkoutId)
    {
        if ($serverMode == "LIVE") {
            return self::$serverToServerUrlLive . '/' . $checkoutId;
        } else {
            return self::$serverToServerUrlTest . '/' . $checkoutId;
        }
    }

    /**
     * Get payment status URL
     *
     * @param string $serverMode server mode
     * @param string $referenceId
     * @return string
     */
    private static function getBackOfficeUrl($serverMode, $referenceId)
    {
        if ($serverMode == "LIVE") {
            return self::$backofficeUrlLive.$referenceId;
        } else {
            return self::$backofficeUrlTest.$referenceId;
        }
    }

    /**
     * Get the serverToServer URL based on the server mode
     *
     * @param   string  $serverMode
     * @return  string
     */
    private static function getServerToServerUrl($serverMode)
    {
        if ($serverMode == "LIVE") {
            return self::$serverToServerUrlLive;
        } else {
            return self::$serverToServerUrlTest;
        }
    }

    /**
     * Get register payment URL
     *
     * @param string $serverMode server mode
     * @param string $referenceId
     * @return string
     */
    private static function getRegisterUrl($serverMode, $referenceId)
    {
        if ($serverMode=="LIVE") {
            return self::$registerUrlLive. $referenceId . '/payments';
        } else {
            return self::$registerUrlTest. $referenceId . '/payments';
        }
    }

    /**
     * Get deregister payment URL
     *
     * @param string $serverMode server mode
     * @param string $referenceId
     * @return string
     */
    private static function getDeRegisterUrl($serverMode, $referenceId)
    {
        if ($serverMode=="LIVE") {
            return self::$registerUrlLive. $referenceId;
        } else {
            return self::$registerUrlTest. $referenceId;
        }
    }

    /**
     * Get the URL to execute query
     *
     * @param   string  $serverMode
     * @return  string
     */
    private static function getQueryUrl($serverMode)
    {
        if ($serverMode=="LIVE") {
            return self::$queryUrlLive;
        } else {
            return self::$queryUrlTest;
        }
    }

    /**
     * OPP Curl
     *
     */

    /**
     * Sets the ssl verifypeer.
     *
     * @param      curl  $ch
     * @param      string  $serverMode  The server mode
     */
    public static function setSSLVerifypeer($ch, $serverMode)
    {
        if ($serverMode == 'TEST') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
    }

    /**
     * Get a response from the gateway
     * @param  string $url
     * @param  string $serverMode
     * @param  string $type
     * @param  string $postFields
     * @param  string $format
     * @param  string $bearerToken
     * @return array
     */
    public static function getGatewayResponse($url, $serverMode, $type = 'GET', $postFields = '', $format = 'string', $bearerToken = null)
    {
        PrestaShopLogger::addLog('Vrpayecommerce - curl request url : '.$url);
        PrestaShopLogger::addLog('Vrpayecommerce - curl request server mode : '.$serverMode);
        PrestaShopLogger::addLog('Vrpayecommerce - curl request type : '.$type);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        self::setSSLVerifypeer($ch, $serverMode);

        if(!empty($bearerToken))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $bearerToken,
            ]);
        }

        if ($type == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        }
        if ($format == 'xml') {
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array('Content-type: application/x-www-form-urlencoded;charset=UTF-8')
            );
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $responseBody = curl_exec($ch);
        $curlResponse = array();
        $curlErrorNo = curl_errno($ch);
        if ($curlErrorNo) {
            $curlResponse['response'] = self::getCurlErrorIdentifier($curlErrorNo);
            $curlResponse['is_valid'] = false;
        } else {
            $jsonResponse = Tools::jsonDecode($responseBody, true);
            if (isset($jsonResponse)) {
                $curlResponse['response'] = $jsonResponse;
            } else {
                $curlResponse['response'] = $responseBody;
            }
            $curlResponse['is_valid'] = true;
        }

        curl_close($ch);

        return $curlResponse;
    }

    /**
     * get Curl error identifier translation
     *
     * @param string $code
     * @return string
     */
    private static function getCurlErrorIdentifier($code)
    {
        $errorIdentifier = array(
            '60' => 'ERROR_MERCHANT_SSL_CERTIFICATE'
        );

        if ($code) {
            if (array_key_exists($code, $errorIdentifier)) {
                $errorMessages = $errorIdentifier[$code];
            } else {
                $errorMessages = 'ERROR_GENERAL_NORESPONSE';
            }
        } else {
            $errorMessages =  'ERROR_GENERAL_GENERAL';
        }

        PrestaShopLogger::addLog('Vrpayecommerce - curl error message : '.$errorMessages);

        return $errorMessages;
    }

    /**
     * OPP Post Parameter
     *
     */

    /**
     * get credential
     *
     * @param array $transactionData
     * @return array $parameters
     */
    private static function getCredentialParameter($transactionData)
    {
        $parameters = array();
        if(!empty($transactionData['bearer']))
        {
            $parameters['bearer']   = $transactionData['bearer'];
            $parameters['entityId'] = $transactionData['channel_id'];
        }
        else
        {
            $parameters['authentication.userId'] = $transactionData['login'];
            $parameters['authentication.password'] = $transactionData['password'];
            $parameters['authentication.entityId'] = $transactionData['channel_id'];
        }

        // test mode parameters (true)
        if (!empty($transactionData['test_mode'])) {
            $parameters['testMode'] = $transactionData['test_mode'];
        }

        return $parameters;
    }

    /**
     * set format data for number
     *
     * @param string $number
     * @return string
     */
    public static function setNumberFormat($number)
    {
        return number_format((float)str_replace(',', '.', $number), 2, '.', '');
    }

    /**
     * get detail of cart item
     *
     * @param array $cartItems
     * @return array $parameters
     */
    private static function getCartItemParameter($cartItems)
    {
        $parameters = array();
        for ($i=0; $i < count($cartItems); $i++) {
            $parameters['cart.items['.$i.'].merchantItemId'] = $cartItems[$i]['merchant_item_id'];
            $parameters['cart.items['.$i.'].discount'] = self::setNumberFormat($cartItems[$i]['discount']);
            $parameters['cart.items['.$i.'].quantity'] = $cartItems[$i]['quantity'];
            $parameters['cart.items['.$i.'].name'] = $cartItems[$i]['name'];
            $parameters['cart.items['.$i.'].price'] = self::setNumberFormat($cartItems[$i]['price']);
            $parameters['cart.items['.$i.'].tax'] = self::setNumberFormat($cartItems[$i]['tax']);
            if(isset($cartItems[$i]['totalTaxAmount']))
            {
                $parameters['cart.items['.$i.'].totalTaxAmount'] = self::setNumberFormat($cartItems[$i]['totalTaxAmount']);
            }
            if(isset($cartItems[$i]['totalAmount']))
            {
                $parameters['cart.items['.$i.'].totalAmount'] = self::setNumberFormat($cartItems[$i]['totalAmount']);
            }
        }
        return $parameters;
    }

    /**
     * get detail of checkout
     *
     * @param array $transactionData
     * @return string
     */
    public static function getCheckoutParameter($transactionData)
    {
        $parameters = array();
        $parameters = self::getCredentialParameter($transactionData);
        $parameters['merchantTransactionId'] = $transactionData['transaction_id'];
        $parameters['customer.email'] = $transactionData['customer']['email'];
        $parameters['customer.givenName'] = $transactionData['customer']['first_name'];
        $parameters['customer.surname'] = $transactionData['customer']['last_name'];
        $parameters['billing.street1'] = $transactionData['billing']['street'];
        $parameters['billing.city'] = $transactionData['billing']['city'];
        $parameters['billing.postcode'] = $transactionData['billing']['zip'];
        $parameters['billing.country'] = $transactionData['billing']['country_code'];
        $parameters['amount'] = self::setNumberFormat($transactionData['amount']);
        $parameters['currency'] = $transactionData['currency'];

        $customerDateOfBirth = strtotime($transactionData['customer']['birthdate']);

        if (isset($transactionData['customer']['sex'])) {
            $parameters['customer.sex'] = $transactionData['customer']['sex'];
        } if (isset($transactionData['customer']['birthdate']) && $customerDateOfBirth > 0) {
            $parameters['customer.birthDate'] = $transactionData['customer']['birthdate'];
        } if (isset($transactionData['customer']['phone'])) {
            $parameters['customer.phone'] = $transactionData['customer']['phone'];
        } if (!empty($transactionData['customer']['mobile'])) {
            $parameters['customer.mobile'] = $transactionData['customer']['mobile'];
        }

        // VAT-ID
        if (!empty($transactionData['customParameters']['buyerCompanyVat']))
        {
            $parameters['customParameters[buyerCompanyVat]'] = $transactionData['customParameters']['buyerCompanyVat'];
        }

        //klarna parameters
        if (!empty($transactionData['cartItems'])) {
            $parameters = array_merge($parameters, self::getCartItemParameter($transactionData['cartItems']));
        } if (!empty($transactionData['customParameters']['KLARNA_CART_ITEM1_FLAGS'])) {
            $parameters['customParameters[KLARNA_CART_ITEM1_FLAGS]'] =
            $transactionData['customParameters']['KLARNA_CART_ITEM1_FLAGS'];
        } if (!empty($transactionData['customParameters']['KLARNA_PCLASS_FLAG'])
              && trim($transactionData['customParameters']['KLARNA_PCLASS_FLAG'])!=='') {
            $parameters['customParameters[KLARNA_PCLASS_FLAG]'] =
            $transactionData['customParameters']['KLARNA_PCLASS_FLAG'];
        }

        //paydirekt parameters
        if (!empty($transactionData['customParameters']['PAYDIREKT_minimumAge'])) {
            $parameters['customParameters[PAYDIREKT_minimumAge]'] =
            $transactionData['customParameters']['PAYDIREKT_minimumAge'];
        } if (!empty($transactionData['customParameters']['PAYDIREKT_payment.isPartial'])) {
            $parameters['customParameters[PAYDIREKT_payment.isPartial]'] =
            $transactionData['customParameters']['PAYDIREKT_payment.isPartial'];
        } if (!empty($transactionData['customParameters']['PAYDIREKT_payment.shippingAmount'])) {
            $parameters['customParameters[PAYDIREKT_payment.shippingAmount]'] =
            self::setNumberFormat($transactionData['customParameters']['PAYDIREKT_payment.shippingAmount']);
        }

        // payment type for RG.DB or only RG
        if (!empty($transactionData['payment_type'])) {
            $parameters['paymentType'] = $transactionData['payment_type'];
        }

        // registration parameter (true)
        if (!empty($transactionData['payment_registration'])) {
            $parameters['createRegistration'] = $transactionData['payment_registration'];
            if (!empty($transactionData['3D'])) {
                  $parameters['customParameters[presentation.amount3D]'] =
                  self::setNumberFormat($transactionData['3D']['amount']);
                  $parameters['customParameters[presentation.currency3D]'] = $transactionData['3D']['currency'];
            }
        }

        // recurring payment parameters : initial/repeated
        if (!empty($transactionData['payment_recurring'])) {
            $parameters['recurringType'] = $transactionData['payment_recurring'];
        }

        if (!empty($transactionData['customer_ip'])) {
            $parameters['customer.ip'] = $transactionData['customer_ip'];
        }

        //easycredit parameter
        if (!empty($transactionData['paymentBrand'])) {
            $parameters['paymentBrand'] = $transactionData['paymentBrand'];
        }

        if (!empty($transactionData['shopperResultUrl'])) {
            $parameters['shopperResultUrl'] = $transactionData['shopperResultUrl'];
        }

        if (isset($transactionData['customer']['birthdate']) && $customerDateOfBirth > 0) {
            $parameters['customer.birthDate'] = $transactionData['customer']['birthdate'];
        }

        if (!empty($transactionData['customParameters']['RISK_ANZAHLPRODUKTEIMWARENKORB'])) {
            $parameters['customParameters[RISK_ANZAHLPRODUKTEIMWARENKORB]'] =
            $transactionData['customParameters']['RISK_ANZAHLPRODUKTEIMWARENKORB'];
        }

        if (isset($transactionData['customParameters']['RISK_ANZAHLBESTELLUNGEN'])) {
            $parameters['customParameters[RISK_ANZAHLBESTELLUNGEN]'] =
            $transactionData['customParameters']['RISK_ANZAHLBESTELLUNGEN'];
        }

        if (isset($transactionData['customParameters']['RISK_BESTELLUNGERFOLGTUEBERLOGIN'])) {
            $parameters['customParameters[RISK_BESTELLUNGERFOLGTUEBERLOGIN]'] =
            $transactionData['customParameters']['RISK_BESTELLUNGERFOLGTUEBERLOGIN'];
        }

        if (isset($transactionData['customParameters']['RISK_KUNDENSTATUS'])) {
            $parameters['customParameters[RISK_KUNDENSTATUS]'] =
            $transactionData['customParameters']['RISK_KUNDENSTATUS'];
        }

        if (isset($transactionData['customParameters']['RISK_KUNDESEIT'])) {
            $parameters['customParameters[RISK_KUNDESEIT]'] = $transactionData['customParameters']['RISK_KUNDESEIT'];
        }

        if (!empty($transactionData['customParameters']['RISK_NEGATIVEZAHLUNGSINFORMATION'])) {
            $parameters['customParameters[RISK_NEGATIVEZAHLUNGSINFORMATION]'] =
            $transactionData['customParameters']['RISK_NEGATIVEZAHLUNGSINFORMATION'];
        }

        if (!empty($transactionData['customParameters']['RISK_RISIKOARTIKELIMWARENKORB'])) {
            $parameters['customParameters[RISK_RISIKOARTIKELIMWARENKORB]'] =
            $transactionData['customParameters']['RISK_RISIKOARTIKELIMWARENKORB'];
        }
        if (isset($transactionData['shipping']['city'])) {
            $parameters['shipping.city'] = $transactionData['shipping']['city'];
        }
        if (isset($transactionData['shipping']['street1'])) {
            $parameters['shipping.street1'] = $transactionData['shipping']['street1'];
        }
        if (isset($transactionData['shipping']['postcode'])) {
            $parameters['shipping.postcode'] = $transactionData['shipping']['postcode'];
        }
        if (isset($transactionData['shipping']['country'])) {
            $parameters['shipping.country'] = $transactionData['shipping']['country'];
        }

        if (!empty($transactionData['registrations'])) {
            foreach ($transactionData['registrations'] as $key => $value) {
                  $parameters['registrations['.$key.'].id'] = $value;
            }
        }

        $versionTrackerFields = array(
            'PLUGIN_email',
            'PLUGIN_accountId',
            'PLUGIN_shopUrl',
            'PLUGIN_shopSystem',
            'PLUGIN_shopSystemVersion',
            'PLUGIN_version',
            'PLUGIN_outletLocation',
            'PLUGIN_mode'
        );
        foreach($versionTrackerFields as $versionTrackerField)
        {
            if(isset($transactionData['customParameters'][$versionTrackerField]))
            {
                $parameters['customParameters'][$versionTrackerField] = $transactionData['customParameters'][$versionTrackerField];
            }
        }

        if(isset($parameters['bearer']))
        {
            unset($parameters['bearer']);
        }

        return http_build_query($parameters);
    }

    /**
     * get detail which used for registration
     *
     * @param array $transactionData
     * @return string
     */
    private static function getRecurringPaymentParameter($transactionData)
    {
        $parameters = array();
        $parameters = self::getCredentialParameter($transactionData);
        $parameters['amount'] = self::setNumberFormat($transactionData['amount']);
        $parameters['currency'] = $transactionData['currency'];
        $parameters['paymentType'] = $transactionData['payment_type'];
        $parameters['merchantTransactionId'] = $transactionData['transaction_id'];
        $parameters['recurringType'] = $transactionData['payment_recurring'];

        if(isset($parameters['bearer']))
        {
            unset($parameters['bearer']);
        }

        return http_build_query($parameters);
    }

    /**
     * get detail which used for backoffice process
     *
     * @param array $transactionData
     * @return string
     */
    private static function getBackOfficeParameter($transactionData)
    {
        $parameters = array();
        $parameters = self::getCredentialParameter($transactionData);
        $parameters['paymentType'] = $transactionData['payment_type'];

        //Reversal (RV) didn't send amount & currency parameter
        if ($transactionData['payment_type'] != 'RV') {
            if(isset($transactionData['amount']))
            {
                $parameters['amount'] = self::setNumberFormat($transactionData['amount']);
            }
            if(isset($transactionData['currency']))
            {
                $parameters['currency'] = $transactionData['currency'];
            }
        }

        // some transactions may need customParameters
        // transform them from array structure to ['customParameters[' . <key> . ']'] = <value>
        if (isset($transactionData['customParameters']) && is_array($transactionData['customParameters']))
        {
            foreach ($transactionData['customParameters'] as $key => $value)
            {
                $parameters['customParameters[' . $key . ']'] = $value;
            }
        }

        if(isset($parameters['bearer']))
        {
            unset($parameters['bearer']);
        }

        return http_build_query($parameters);
    }

    /**
     * get xml which used for update status
     *
     * @param string $referenceId
     * @param array $transactionData
     * @return string $xml
     */
    private static function getXmlUpdateStatus($referenceId, $transactionData)
    {
        switch ($transactionData['test_mode']) {
            case 'INTERNAL':
                $mode = 'INTEGRATOR_TEST';
                break;
            case 'EXTERNAL':
                $mode = 'CONNECTOR_TEST';
                break;
            default:
                $mode = 'LIVE';
                break;
        }

        $xml = 'load=<?xml version="1.0" encoding="UTF-8"?><Request version="1.0">
            <Header><Security sender="'.$transactionData['channel_id'].'"/></Header>
            <Query mode="'.$mode.'" level="CHANNEL" entity="'.$transactionData['channel_id'].'" type="STANDARD">
                <User login="'.$transactionData['login'].'" pwd="'.$transactionData['password'].'"/>
                <Identification>
                    <UniqueID>'.$referenceId.'</UniqueID>
                </Identification>
                </Query>
            </Request>';

        return $xml;
    }

    /**
     * OPP Main Operation
     *
     */

    /**
     * check payment status 3 times if no response
     *
     * @param string $paymentStatusUrl
     * @param array $resultJson
     * @param string $serverMode
     * @param string $bearerToken
     * @return array $resultJson
     */
    private static function isPaymentGetResponse($paymentStatusUrl, &$resultJson, $serverMode, $bearerToken = null)
    {
        PrestaShopLogger::addLog('VRpayecommerce - start try get the response from gateway');
        for ($i=0; $i < 3; $i++) {
            $response = true;
            try {
                $resultJson = self::getGatewayResponse($paymentStatusUrl, $serverMode, 'GET', '', 'string', $bearerToken);
            } catch (Exception $e) {
                $resultJson['response'] = 'ERROR_GENERAL_NORESPONSE';
                $resultJson['is_valid'] = false;
                $response = false;
            }
            if ($response && $resultJson) {
                break;
            }
        }
        PrestaShopLogger::addLog('VRpayecommerce - get response : '. print_r($resultJson, true));
        PrestaShopLogger::addLog('VRpayecommerce - end try get the response from gateway');

        return $resultJson;
    }

    /**
     * Prepare the checkout & get checkout id (for create payment form)
     * @param  array $transactionData
     * @return array $response
     */
    public static function getCheckoutResult($transactionData)
    {
        //prepareCheckout
        PrestaShopLogger::addLog('VRpayecommerce - get checkout parameters : '. print_r($transactionData, true));

        $checkoutUrl = self::getCheckoutUrl($transactionData['server_mode']);
        PrestaShopLogger::addLog('VRpayecommerce - get API url : '. $checkoutUrl);

        $postData = self::getCheckoutParameter($transactionData);
        PrestaShopLogger::addLog('VRpayecommerce - set curl post data : '. $postData);

        $response = self::getGatewayResponse($checkoutUrl, $transactionData['server_mode'], 'POST', $postData, 'string', isset($transactionData['bearer']) ? $transactionData['bearer'] : null);
        PrestaShopLogger::addLog('VRpayecommerce - get response from gateway : '. print_r($response, true));

        return $response;
    }

    /**
     * get payment widget URL by checkout id (for Create Payment Form)
     *
     * @param string $checkoutId
     * @param string $serverMode
     * @return string $paymentWidgetUrl
     */
    public static function getPaymentWidgetUrl($serverMode, $checkoutId)
    {
        PrestaShopLogger::addLog('VRpayecommerce - start get payment widget url');

        if ($serverMode == 'LIVE') {
            $paymentWidgetUrl = self::$paymentWidgetUrlLive . $checkoutId;
        } else {
            $paymentWidgetUrl = self::$paymentWidgetUrlTest . $checkoutId;
        }
        PrestaShopLogger::addLog('VRpayecommerce - payment widget url: '.$paymentWidgetUrl);
        PrestaShopLogger::addLog('VRpayecommerce - end get payment widget url');

        return $paymentWidgetUrl;
    }

    /**
     * Get the Payment Status
     *
     * @param string $checkoutId
     * @param array $transactionData
     * @return array $response
     */
    public static function getPaymentStatus($checkoutId, $transactionData)
    {
        PrestaShopLogger::addLog('VRpayecommerce - start get payment status');
        PrestaShopLogger::addLog('VRpayecommerce - get chekout id : '. $checkoutId);
        PrestaShopLogger::addLog('VRpayecommerce - get transaction data : '. print_r($transactionData, true));

        $paymentStatusUrl = self::getPaymentStatusUrl($transactionData['server_mode'], $checkoutId);
        $credentials = self::getCredentialParameter($transactionData);
        $bearerToken = null;
        if(isset($credentials['bearer']))
        {
            $bearerToken = $credentials['bearer'];
            unset($credentials['bearer']);
        }
        $paymentStatusUrl .= '?'.http_build_query($credentials);
        PrestaShopLogger::addLog('VRpayecommerce - get API url : '. $paymentStatusUrl);

        $resultJson = '';
        $response = self::isPaymentGetResponse($paymentStatusUrl, $resultJson, $transactionData['server_mode'], $bearerToken);
        PrestaShopLogger::addLog('VRpayecommerce - get response from gateway : '. print_r($response, true));
        PrestaShopLogger::addLog('VRpayecommerce - end get payment status ');

        return $response;
    }

    /**
     * Send an Initial Payment server to server
     *
     * @param string $checkoutId
     * @param array $transactionData
     * @return array $response
     */
    public static function getPaymentServerToServerStatus($checkoutId, $transactionData)
    {
        PrestaShopLogger::addLog('VRpayecommerce - start get payment server to server status');
        PrestaShopLogger::addLog('VRpayecommerce - get chekout id : '. $checkoutId);
        PrestaShopLogger::addLog('VRpayecommerce - get transaction data : '. print_r($transactionData, true));

        $paymentStatusUrl = self::getPaymentServerToServerStatusUrl($transactionData['server_mode'], $checkoutId);
        $credentials = self::getCredentialParameter($transactionData);
        $bearerToken = null;
        if(isset($credentials['bearer']))
        {
            $bearerToken = $credentials['bearer'];
            unset($credentials['bearer']);
        }
        $paymentStatusUrl .= '?'.http_build_query($credentials);
        PrestaShopLogger::addLog('VRpayecommerce - get API url : '. $paymentStatusUrl);

        $resultJson = '';
        $response = self::isPaymentGetResponse($paymentStatusUrl, $resultJson, $transactionData['server_mode'], $bearerToken);
        PrestaShopLogger::addLog('VRpayecommerce - get response from gateway : '. print_r($response, true));
        PrestaShopLogger::addLog('VRpayecommerce - end get payment status ');

        return $response;
    }

    /**
     * Recurring Payment, Sending Repeated Payment
     *
     * @param string $referenceId
     * @param array $transactionData
     * @return array $resultJson
     */
    public static function getRecurringPaymentResult($referenceId, $transactionData)
    {
        PrestaShopLogger::addLog('VRpayecommerce - start get payment status');
        PrestaShopLogger::addLog('VRpayecommerce - get referenceId : '. $referenceId);
        PrestaShopLogger::addLog('VRpayecommerce - get transaction data : '. print_r($transactionData, true));

        $registerUrl = self::getRegisterUrl($transactionData['server_mode'], $referenceId);
        PrestaShopLogger::addLog('VRpayecommerce - get API url : '. $registerUrl);

        $postData = self::getRecurringPaymentParameter($transactionData);
        PrestaShopLogger::addLog('VRpayecommerce - get post data : '. $postData);

        $resultJson = self::getGatewayResponse($registerUrl, $transactionData['server_mode'], 'POST', $postData, 'string', isset($transactionData['bearer']) ? $transactionData['bearer'] : null);
        PrestaShopLogger::addLog('VRpayecommerce - get response from gateway : '. print_r($resultJson, true));

        return $resultJson;
    }

    /**
     * Back Office Operation : Capture, Refund, Reversal
     *
     * @param string $referenceId
     * @param array $transactionData
     * @return array $resultJson
     */
    public static function backOfficeOperation($referenceId, $transactionData)
    {
        PrestaShopLogger::addLog('VRpayecommerce - start backOfficeOperation');
        PrestaShopLogger::addLog('VRpayecommerce - get referenceId : '. $referenceId);
        PrestaShopLogger::addLog('VRpayecommerce - get transaction data : '. print_r($transactionData, true));

        $backOfficeUrl = self::getBackOfficeUrl($transactionData['server_mode'], $referenceId);
        PrestaShopLogger::addLog('VRpayecommerce - get API url : '. $backOfficeUrl);

        $postData = self::getBackOfficeParameter($transactionData);
        PrestaShopLogger::addLog('VRpayecommerce - get postData : '. $postData);

        $resultJson = self::getGatewayResponse($backOfficeUrl, $transactionData['server_mode'], 'POST', $postData, 'string', isset($transactionData['bearer']) ? $transactionData['bearer'] : null);
        PrestaShopLogger::addLog('VRpayecommerce - get response from gateway : '. print_r($resultJson, true));
        PrestaShopLogger::addLog('VRpayecommerce - end back office operation');

        return $resultJson;
    }

    /**
     * Send request to payment gateway and get response data
     *
     * @param array $transactionData
     * @return array $resultJson
     */
    public static function getServerToServerResponse($transactionData)
    {
        PrestaShopLogger::addLog('VRpayecommerce - start get server to server response');
        PrestaShopLogger::addLog('VRpayecommerce - get transaction data : '. print_r($transactionData, true));

        $backOfficeUrl = self::getServerToServerUrl($transactionData['server_mode']);
        PrestaShopLogger::addLog('VRpayecommerce - get API url : '. $backOfficeUrl);

        $postData = self::getCheckoutParameter($transactionData);
        PrestaShopLogger::addLog('VRpayecommerce - get postData : '. $postData);

        $resultJson = self::getGatewayResponse($backOfficeUrl, $transactionData['server_mode'], 'POST', $postData, 'string', isset($transactionData['bearer']) ? $transactionData['bearer'] : null);
        PrestaShopLogger::addLog('VRpayecommerce - get response from gateway : '. print_r($resultJson, true));
        PrestaShopLogger::addLog('VRpayecommerce - end get server to server response');

        return $resultJson;
    }

    /**
     * Deregister Payment
     *
     * @param string $referenceId
     * @param array $transactionData
     * @return array $resultJson
     */
    public static function deleteRegistration($referenceId, $transactionData)
    {
        PrestaShopLogger::addLog('VRpayecommerce - start deregister payment account');
        PrestaShopLogger::addLog('VRpayecommerce - get referenceId : '. $referenceId);
        PrestaShopLogger::addLog('VRpayecommerce - get transaction data : '. print_r($transactionData, true));

        $deRegisterUrl = self::getDeRegisterUrl($transactionData['server_mode'], $referenceId);
        $credentials = self::getCredentialParameter($transactionData);
        $bearerToken = null;
        if(isset($credentials['bearer']))
        {
            $bearerToken = $credentials['bearer'];
            unset($credentials['bearer']);
        }

        $deRegisterUrl .= '?'.http_build_query($bearerToken);
        PrestaShopLogger::addLog('VRpayecommerce - get API url : '. $deRegisterUrl);

        $resultJson = self::getGatewayResponse($deRegisterUrl, $transactionData['server_mode'], 'DELETE', '', 'string', $bearerToken);
        PrestaShopLogger::addLog('VRpayecommerce - get response from gateway : '. print_r($resultJson, true));
        PrestaShopLogger::addLog('VRpayecommerce - end deregister payment account');

        return $resultJson;
    }

    /**
     * Update Status Payment
     *
     * @param string $referenceId
     * @param array $transactionData
     * @return array $result
     */
    public static function updateStatus($referenceId, $transactionData)
    {
        PrestaShopLogger::addLog('VRpayecommerce - start update payment status');
        PrestaShopLogger::addLog('VRpayecommerce - get referenceId : '. $referenceId);
        PrestaShopLogger::addLog('VRpayecommerce - get transaction data : '. print_r($transactionData, true));

        $queryUrl = self::getQueryUrl($transactionData['server_mode']);
        PrestaShopLogger::addLog('VRpayecommerce - get API url : '. $queryUrl);

        $xmlRequest = self::getXmlUpdateStatus($referenceId, $transactionData);
        $result = self::getGatewayResponse($queryUrl, $transactionData['server_mode'], 'POST', $xmlRequest, 'xml');
        PrestaShopLogger::addLog('VRpayecommerce - get response from gateway  : '. print_r($result, true));
        PrestaShopLogger::addLog('VRpayecommerce - end update payment status');

        return $result;
    }

    /**
     * get result of transaction
     *
     * @param string|boolean $returnCode
     * @return string|boolean
     */
    public static function getTransactionResult($returnCode = false)
    {
        if ($returnCode) {
            if (in_array($returnCode, self::$ackReturnCodes)) {
                return "ACK";
            } elseif (in_array($returnCode, self::$nokReturnCodes)) {
                return "NOK";
            }
        }
        return false;
    }

    /**
     * get error identifier based of code
     *
     * @param string $code
     * @return string
     */
    public static function getErrorIdentifier($code)
    {
        $errorMessages = array(
            '800.150.100' => 'ERROR_CC_ACCOUNT',

            '800.100.402' => 'ERROR_CC_INVALIDDATA',
            '100.100.101' => 'ERROR_CC_INVALIDDATA',
            '800.100.151' => 'ERROR_CC_INVALIDDATA',
            '000.400.108' => 'ERROR_CC_INVALIDDATA',
            '100.100.100' => 'ERROR_CC_INVALIDDATA',
            '100.100.200' => 'ERROR_CC_INVALIDDATA',
            '100.100.201' => 'ERROR_CC_INVALIDDATA',
            '100.100.300' => 'ERROR_CC_INVALIDDATA',
            '100.100.301' => 'ERROR_CC_INVALIDDATA',
            '100.100.304' => 'ERROR_CC_INVALIDDATA',
            '100.100.400' => 'ERROR_CC_INVALIDDATA',
            '100.100.401' => 'ERROR_CC_INVALIDDATA',
            '100.100.402' => 'ERROR_CC_INVALIDDATA',
            '100.100.651' => 'ERROR_CC_INVALIDDATA',
            '100.100.700' => 'ERROR_CC_INVALIDDATA',
            '100.200.100' => 'ERROR_CC_INVALIDDATA',
            '100.200.103' => 'ERROR_CC_INVALIDDATA',
            '100.200.104' => 'ERROR_CC_INVALIDDATA',
            '100.400.000' => 'ERROR_CC_INVALIDDATA',
            '100.400.001' => 'ERROR_CC_INVALIDDATA',
            '100.400.086' => 'ERROR_CC_INVALIDDATA',
            '100.400.087' => 'ERROR_CC_INVALIDDATA',
            '100.400.002' => 'ERROR_CC_INVALIDDATA',
            '100.400.316' => 'ERROR_CC_INVALIDDATA',
            '100.400.317' => 'ERROR_CC_INVALIDDATA',
            '100.100.600' => 'ERROR_CC_INVALIDDATA',

            '800.300.401' => 'ERROR_CC_BLACKLIST',

            '800.100.171' => 'ERROR_CC_DECLINED_CARD',
            '800.100.165' => 'ERROR_CC_DECLINED_CARD',
            '800.100.159' => 'ERROR_CC_DECLINED_CARD',
            '800.100.195' => 'ERROR_CC_DECLINED_CARD',
            '000.400.101' => 'ERROR_CC_DECLINED_CARD',
            '100.100.501' => 'ERROR_CC_DECLINED_CARD',
            '100.100.701' => 'ERROR_CC_DECLINED_CARD',
            '100.400.005' => 'ERROR_CC_DECLINED_CARD',
            '100.400.020' => 'ERROR_CC_DECLINED_CARD',
            '100.400.021' => 'ERROR_CC_DECLINED_CARD',
            '100.400.030' => 'ERROR_CC_DECLINED_CARD',
            '100.400.039' => 'ERROR_CC_DECLINED_CARD',
            '100.400.081' => 'ERROR_CC_DECLINED_CARD',
            '100.400.100' => 'ERROR_CC_DECLINED_CARD',
            '100.400.123' => 'ERROR_CC_DECLINED_CARD',
            '100.400.319' => 'ERROR_CC_DECLINED_CARD',
            '800.100.154' => 'ERROR_CC_DECLINED_CARD',
            '800.100.156' => 'ERROR_CC_DECLINED_CARD',
            '800.100.158' => 'ERROR_CC_DECLINED_CARD',
            '800.100.160' => 'ERROR_CC_DECLINED_CARD',
            '800.100.161' => 'ERROR_CC_DECLINED_CARD',
            '800.100.163' => 'ERROR_CC_DECLINED_CARD',
            '800.100.164' => 'ERROR_CC_DECLINED_CARD',
            '800.100.166' => 'ERROR_CC_DECLINED_CARD',
            '800.100.167' => 'ERROR_CC_DECLINED_CARD',
            '800.100.169' => 'ERROR_CC_DECLINED_CARD',
            '800.100.170' => 'ERROR_CC_DECLINED_CARD',
            '800.100.173' => 'ERROR_CC_DECLINED_CARD',
            '800.100.174' => 'ERROR_CC_DECLINED_CARD',
            '800.100.175' => 'ERROR_CC_DECLINED_CARD',
            '800.100.176' => 'ERROR_CC_DECLINED_CARD',
            '800.100.177' => 'ERROR_CC_DECLINED_CARD',
            '800.100.190' => 'ERROR_CC_DECLINED_CARD',
            '800.100.191' => 'ERROR_CC_DECLINED_CARD',
            '800.100.196' => 'ERROR_CC_DECLINED_CARD',
            '800.100.197' => 'ERROR_CC_DECLINED_CARD',
            '800.100.168' => 'ERROR_CC_DECLINED_CARD',

            '100.100.303' => 'ERROR_CC_EXPIRED',

            '800.100.153' => 'ERROR_CC_INVALIDCVV',
            '100.100.601' => 'ERROR_CC_INVALIDCVV',
            '800.100.192' => 'ERROR_CC_INVALIDCVV',

            '800.100.157' => 'ERROR_CC_EXPIRY',

            '800.100.162' => 'ERROR_CC_LIMIT_EXCEED',

            '100.400.040' => 'ERROR_CC_3DAUTH',
            '100.400.060' => 'ERROR_CC_3DAUTH',
            '100.400.080' => 'ERROR_CC_3DAUTH',
            '100.400.120' => 'ERROR_CC_3DAUTH',
            '100.400.260' => 'ERROR_CC_3DAUTH',
            '800.900.300' => 'ERROR_CC_3DAUTH',
            '800.900.301' => 'ERROR_CC_3DAUTH',
            '800.900.302' => 'ERROR_CC_3DAUTH',
            '100.380.401' => 'ERROR_CC_3DAUTH',

            '100.390.105' => 'ERROR_CC_3DERROR',
            '000.400.103' => 'ERROR_CC_3DERROR',
            '000.400.104' => 'ERROR_CC_3DERROR',
            '100.390.106' => 'ERROR_CC_3DERROR',
            '100.390.107' => 'ERROR_CC_3DERROR',
            '100.390.108' => 'ERROR_CC_3DERROR',
            '100.390.109' => 'ERROR_CC_3DERROR',
            '100.390.111' => 'ERROR_CC_3DERROR',
            '800.400.200' => 'ERROR_CC_3DERROR',
            '100.390.112' => 'ERROR_CC_3DERROR',

            '100.100.500' => 'ERROR_CC_NOBRAND',

            '800.100.155' => 'ERROR_GENERAL_LIMIT_AMOUNT',
            '000.100.203' => 'ERROR_GENERAL_LIMIT_AMOUNT',
            '100.550.310' => 'ERROR_GENERAL_LIMIT_AMOUNT',
            '100.550.311' => 'ERROR_GENERAL_LIMIT_AMOUNT',

            '800.120.101' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',
            '800.120.100' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',
            '800.120.102' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',
            '800.120.103' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',
            '800.120.200' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',
            '800.120.201' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',
            '800.120.202' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',
            '800.120.203' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',

            '800.100.152' => 'ERROR_CC_DECLINED_AUTH',
            '000.400.106' => 'ERROR_CC_DECLINED_AUTH',
            '000.400.105' => 'ERROR_CC_DECLINED_AUTH',
            '000.400.103' => 'ERROR_CC_DECLINED_AUTH',

            '100.380.501' => 'ERROR_GENERAL_DECLINED_RISK',

            '800.400.151' => 'ERROR_CC_ADDRESS',
            '800.400.150' => 'ERROR_CC_ADDRESS',

            '100.400.300' => 'ERROR_GENERAL_CANCEL',
            '100.396.101' => 'ERROR_GENERAL_CANCEL',
            '900.300.600' => 'ERROR_GENERAL_CANCEL',

            '800.100.501' => 'ERROR_CC_RECURRING',
            '800.100.500' => 'ERROR_CC_RECURRING',

            '800.100.178' => 'ERROR_CC_REPEATED',
            '800.300.500' => 'ERROR_CC_REPEATED',
            '800.300.501' => 'ERROR_CC_REPEATED',

            '800.700.101' => 'ERROR_GENERAL_ADDRESS',
            '800.700.201' => 'ERROR_GENERAL_ADDRESS',
            '800.700.500' => 'ERROR_GENERAL_ADDRESS',
            '800.800.102' => 'ERROR_GENERAL_ADDRESS',
            '800.800.202' => 'ERROR_GENERAL_ADDRESS',
            '800.800.302' => 'ERROR_GENERAL_ADDRESS',
            '800.900.101' => 'ERROR_GENERAL_ADDRESS',
            '800.100.198' => 'ERROR_GENERAL_ADDRESS',
            '000.100.201' => 'ERROR_GENERAL_ADDRESS',

            '100.400.121' => 'ERROR_GENERAL_BLACKLIST',
            '800.100.172' => 'ERROR_GENERAL_BLACKLIST',
            '800.200.159' => 'ERROR_GENERAL_BLACKLIST',
            '800.200.160' => 'ERROR_GENERAL_BLACKLIST',
            '800.200.165' => 'ERROR_GENERAL_BLACKLIST',
            '800.200.202' => 'ERROR_GENERAL_BLACKLIST',
            '800.200.208' => 'ERROR_GENERAL_BLACKLIST',
            '800.200.220' => 'ERROR_GENERAL_BLACKLIST',
            '800.300.101' => 'ERROR_GENERAL_BLACKLIST',
            '800.300.102' => 'ERROR_GENERAL_BLACKLIST',
            '800.300.200' => 'ERROR_GENERAL_BLACKLIST',
            '800.300.301' => 'ERROR_GENERAL_BLACKLIST',
            '800.300.302' => 'ERROR_GENERAL_BLACKLIST',

            '000.100.200' => 'ERROR_GENERAL_GENERAL',
            '000.100.202' => 'ERROR_GENERAL_GENERAL',
            '000.100.206' => 'ERROR_GENERAL_GENERAL',
            '000.100.207' => 'ERROR_GENERAL_GENERAL',
            '000.100.208' => 'ERROR_GENERAL_GENERAL',
            '000.100.209' => 'ERROR_GENERAL_GENERAL',
            '000.100.210' => 'ERROR_GENERAL_GENERAL',
            '000.100.220' => 'ERROR_GENERAL_GENERAL',
            '000.100.221' => 'ERROR_GENERAL_GENERAL',
            '000.100.222' => 'ERROR_GENERAL_GENERAL',
            '000.100.223' => 'ERROR_GENERAL_GENERAL',
            '000.100.224' => 'ERROR_GENERAL_GENERAL',
            '000.100.225' => 'ERROR_GENERAL_GENERAL',
            '000.100.226' => 'ERROR_GENERAL_GENERAL',
            '000.100.227' => 'ERROR_GENERAL_GENERAL',
            '000.100.228' => 'ERROR_GENERAL_GENERAL',
            '000.100.229' => 'ERROR_GENERAL_GENERAL',
            '000.100.230' => 'ERROR_GENERAL_GENERAL',
            '000.100.299' => 'ERROR_GENERAL_GENERAL',
            '000.400.102' => 'ERROR_GENERAL_GENERAL',
            '000.400.200' => 'ERROR_GENERAL_GENERAL',
            '100.211.105' => 'ERROR_GENERAL_GENERAL',
            '100.211.106' => 'ERROR_GENERAL_GENERAL',
            '100.212.101' => 'ERROR_GENERAL_GENERAL',
            '100.212.102' => 'ERROR_GENERAL_GENERAL',
            '100.212.103' => 'ERROR_GENERAL_GENERAL',
            '100.250.100' => 'ERROR_GENERAL_GENERAL',
            '100.370.100' => 'ERROR_GENERAL_GENERAL',
            '100.380.100' => 'ERROR_GENERAL_GENERAL',
            '100.390.110' => 'ERROR_GENERAL_GENERAL',
            '100.390.113' => 'ERROR_GENERAL_GENERAL',
            '100.395.501' => 'ERROR_GENERAL_GENERAL',
            '100.396.102' => 'ERROR_GENERAL_GENERAL',
            '100.396.103' => 'ERROR_GENERAL_GENERAL',
            '100.396.104' => 'ERROR_GENERAL_GENERAL',
            '100.396.106' => 'ERROR_GENERAL_GENERAL',
            '100.396.201' => 'ERROR_GENERAL_GENERAL',
            '100.397.101' => 'ERROR_GENERAL_GENERAL',
            '100.397.102' => 'ERROR_GENERAL_GENERAL',
            '100.400.007' => 'ERROR_GENERAL_GENERAL',
            '100.400.041' => 'ERROR_GENERAL_GENERAL',
            '100.400.042' => 'ERROR_GENERAL_GENERAL',
            '100.400.043' => 'ERROR_GENERAL_GENERAL',
            '100.400.044' => 'ERROR_GENERAL_GENERAL',
            '100.400.045' => 'ERROR_GENERAL_GENERAL',
            '100.400.051' => 'ERROR_GENERAL_GENERAL',
            '100.400.061' => 'ERROR_GENERAL_GENERAL',
            '100.400.063' => 'ERROR_GENERAL_GENERAL',
            '100.400.064' => 'ERROR_GENERAL_GENERAL',
            '100.400.065' => 'ERROR_GENERAL_GENERAL',
            '100.400.071' => 'ERROR_GENERAL_GENERAL',
            '100.400.083' => 'ERROR_GENERAL_GENERAL',
            '100.400.084' => 'ERROR_GENERAL_GENERAL',
            '100.400.085' => 'ERROR_GENERAL_GENERAL',
            '100.400.091' => 'ERROR_GENERAL_GENERAL',
            '100.400.122' => 'ERROR_GENERAL_GENERAL',
            '100.400.130' => 'ERROR_GENERAL_GENERAL',
            '100.400.139' => 'ERROR_GENERAL_GENERAL',
            '100.400.140' => 'ERROR_GENERAL_GENERAL',
            '100.400.243' => 'ERROR_GENERAL_GENERAL',
            '100.400.301' => 'ERROR_GENERAL_GENERAL',
            '100.400.303' => 'ERROR_GENERAL_GENERAL',
            '100.400.304' => 'ERROR_GENERAL_GENERAL',
            '100.400.305' => 'ERROR_GENERAL_GENERAL',
            '100.400.306' => 'ERROR_GENERAL_GENERAL',
            '100.400.307' => 'ERROR_GENERAL_GENERAL',
            '100.400.308' => 'ERROR_GENERAL_GENERAL',
            '100.400.309' => 'ERROR_GENERAL_GENERAL',
            '100.400.310' => 'ERROR_GENERAL_GENERAL',
            '100.400.311' => 'ERROR_GENERAL_GENERAL',
            '100.400.312' => 'ERROR_GENERAL_GENERAL',
            '100.400.313' => 'ERROR_GENERAL_GENERAL',
            '100.400.314' => 'ERROR_GENERAL_GENERAL',
            '100.400.315' => 'ERROR_GENERAL_GENERAL',
            '100.400.318' => 'ERROR_GENERAL_GENERAL',
            '100.400.320' => 'ERROR_GENERAL_GENERAL',
            '100.400.321' => 'ERROR_GENERAL_GENERAL',
            '100.400.322' => 'ERROR_GENERAL_GENERAL',
            '100.400.323' => 'ERROR_GENERAL_GENERAL',
            '100.400.324' => 'ERROR_GENERAL_GENERAL',
            '100.400.325' => 'ERROR_GENERAL_GENERAL',
            '100.400.326' => 'ERROR_GENERAL_GENERAL',
            '100.400.327' => 'ERROR_GENERAL_GENERAL',
            '100.400.328' => 'ERROR_GENERAL_GENERAL',
            '100.400.500' => 'ERROR_GENERAL_GENERAL',
            '100.500.101' => 'ERROR_GENERAL_GENERAL',
            '100.500.201' => 'ERROR_GENERAL_GENERAL',
            '100.500.301' => 'ERROR_GENERAL_GENERAL',
            '100.500.302' => 'ERROR_GENERAL_GENERAL',
            '100.550.300' => 'ERROR_GENERAL_GENERAL',
            '100.550.301' => 'ERROR_GENERAL_GENERAL',
            '100.550.303' => 'ERROR_GENERAL_GENERAL',
            '100.550.312' => 'ERROR_GENERAL_GENERAL',
            '100.550.400' => 'ERROR_GENERAL_GENERAL',
            '100.550.401' => 'ERROR_GENERAL_GENERAL',
            '100.550.601' => 'ERROR_GENERAL_GENERAL',
            '100.550.603' => 'ERROR_GENERAL_GENERAL',
            '100.550.605' => 'ERROR_GENERAL_GENERAL',
            '100.600.500' => 'ERROR_GENERAL_GENERAL',
            '100.700.100' => 'ERROR_GENERAL_GENERAL',
            '100.700.101' => 'ERROR_GENERAL_GENERAL',
            '100.700.200' => 'ERROR_GENERAL_GENERAL',
            '100.700.201' => 'ERROR_GENERAL_GENERAL',
            '100.700.300' => 'ERROR_GENERAL_GENERAL',
            '100.700.400' => 'ERROR_GENERAL_GENERAL',
            '100.700.500' => 'ERROR_GENERAL_GENERAL',
            '100.700.800' => 'ERROR_GENERAL_GENERAL',
            '100.700.801' => 'ERROR_GENERAL_GENERAL',
            '100.700.802' => 'ERROR_GENERAL_GENERAL',
            '100.700.810' => 'ERROR_GENERAL_GENERAL',
            '100.800.100' => 'ERROR_GENERAL_GENERAL',
            '100.800.101' => 'ERROR_GENERAL_GENERAL',
            '100.800.102' => 'ERROR_GENERAL_GENERAL',
            '100.800.200' => 'ERROR_GENERAL_GENERAL',
            '100.800.201' => 'ERROR_GENERAL_GENERAL',
            '100.800.202' => 'ERROR_GENERAL_GENERAL',
            '100.800.300' => 'ERROR_GENERAL_GENERAL',
            '100.800.301' => 'ERROR_GENERAL_GENERAL',
            '100.800.302' => 'ERROR_GENERAL_GENERAL',
            '100.800.400' => 'ERROR_GENERAL_GENERAL',
            '100.800.401' => 'ERROR_GENERAL_GENERAL',
            '100.800.500' => 'ERROR_GENERAL_GENERAL',
            '100.800.501' => 'ERROR_GENERAL_GENERAL',
            '100.900.100' => 'ERROR_GENERAL_GENERAL',
            '100.900.101' => 'ERROR_GENERAL_GENERAL',
            '100.900.105' => 'ERROR_GENERAL_GENERAL',
            '100.900.200' => 'ERROR_GENERAL_GENERAL',
            '100.900.300' => 'ERROR_GENERAL_GENERAL',
            '100.900.301' => 'ERROR_GENERAL_GENERAL',
            '100.900.400' => 'ERROR_GENERAL_GENERAL',
            '100.900.401' => 'ERROR_GENERAL_GENERAL',
            '100.900.450' => 'ERROR_GENERAL_GENERAL',
            '100.900.500' => 'ERROR_GENERAL_GENERAL',
            '200.100.101' => 'ERROR_GENERAL_GENERAL',
            '200.100.102' => 'ERROR_GENERAL_GENERAL',
            '200.100.103' => 'ERROR_GENERAL_GENERAL',
            '200.100.150' => 'ERROR_GENERAL_GENERAL',
            '200.100.151' => 'ERROR_GENERAL_GENERAL',
            '200.100.199' => 'ERROR_GENERAL_GENERAL',
            '200.100.201' => 'ERROR_GENERAL_GENERAL',
            '200.100.300' => 'ERROR_GENERAL_GENERAL',
            '200.100.301' => 'ERROR_GENERAL_GENERAL',
            '200.100.302' => 'ERROR_GENERAL_GENERAL',
            '200.100.401' => 'ERROR_GENERAL_GENERAL',
            '200.100.402' => 'ERROR_GENERAL_GENERAL',
            '200.100.403' => 'ERROR_GENERAL_GENERAL',
            '200.100.404' => 'ERROR_GENERAL_GENERAL',
            '200.100.501' => 'ERROR_GENERAL_GENERAL',
            '200.100.502' => 'ERROR_GENERAL_GENERAL',
            '200.100.503' => 'ERROR_GENERAL_GENERAL',
            '200.100.504' => 'ERROR_GENERAL_GENERAL',
            '200.200.106' => 'ERROR_GENERAL_GENERAL',
            '200.300.403' => 'ERROR_GENERAL_GENERAL',
            '200.300.404' => 'ERROR_GENERAL_GENERAL',
            '200.300.405' => 'ERROR_GENERAL_GENERAL',
            '200.300.406' => 'ERROR_GENERAL_GENERAL',
            '200.300.407' => 'ERROR_GENERAL_GENERAL',
            '500.100.201' => 'ERROR_GENERAL_GENERAL',
            '500.100.202' => 'ERROR_GENERAL_GENERAL',
            '500.100.203' => 'ERROR_GENERAL_GENERAL',
            '500.100.301' => 'ERROR_GENERAL_GENERAL',
            '500.100.302' => 'ERROR_GENERAL_GENERAL',
            '500.100.303' => 'ERROR_GENERAL_GENERAL',
            '500.100.304' => 'ERROR_GENERAL_GENERAL',
            '500.100.401' => 'ERROR_GENERAL_GENERAL',
            '500.100.402' => 'ERROR_GENERAL_GENERAL',
            '500.100.403' => 'ERROR_GENERAL_GENERAL',
            '500.200.101' => 'ERROR_GENERAL_GENERAL',
            '600.100.100' => 'ERROR_GENERAL_GENERAL',
            '600.200.100' => 'ERROR_GENERAL_GENERAL',
            '600.200.200' => 'ERROR_GENERAL_GENERAL',
            '600.200.201' => 'ERROR_GENERAL_GENERAL',
            '600.200.202' => 'ERROR_GENERAL_GENERAL',
            '600.200.300' => 'ERROR_GENERAL_GENERAL',
            '600.200.310' => 'ERROR_GENERAL_GENERAL',
            '600.200.400' => 'ERROR_GENERAL_GENERAL',
            '600.200.500' => 'ERROR_GENERAL_GENERAL',
            '600.200.600' => 'ERROR_GENERAL_GENERAL',
            '600.200.700' => 'ERROR_GENERAL_GENERAL',
            '600.200.800' => 'ERROR_GENERAL_GENERAL',
            '600.200.810' => 'ERROR_GENERAL_GENERAL',
            '700.100.100' => 'ERROR_GENERAL_GENERAL',
            '700.100.200' => 'ERROR_GENERAL_GENERAL',
            '700.100.300' => 'ERROR_GENERAL_GENERAL',
            '700.100.400' => 'ERROR_GENERAL_GENERAL',
            '700.100.500' => 'ERROR_GENERAL_GENERAL',
            '700.100.600' => 'ERROR_GENERAL_GENERAL',
            '700.100.700' => 'ERROR_GENERAL_GENERAL',
            '700.100.701' => 'ERROR_GENERAL_GENERAL',
            '700.100.710' => 'ERROR_GENERAL_GENERAL',
            '700.300.500' => 'ERROR_GENERAL_GENERAL',
            '700.400.000' => 'ERROR_GENERAL_GENERAL',
            '700.400.400' => 'ERROR_GENERAL_GENERAL',
            '700.400.402' => 'ERROR_GENERAL_GENERAL',
            '700.400.410' => 'ERROR_GENERAL_GENERAL',
            '700.400.420' => 'ERROR_GENERAL_GENERAL',
            '700.400.562' => 'ERROR_GENERAL_GENERAL',
            '700.400.570' => 'ERROR_GENERAL_GENERAL',
            '700.400.700' => 'ERROR_GENERAL_GENERAL',
            '700.450.001' => 'ERROR_GENERAL_GENERAL',
            '800.100.100' => 'ERROR_GENERAL_GENERAL',
            '800.100.150' => 'ERROR_GENERAL_GENERAL',
            '800.100.179' => 'ERROR_GENERAL_GENERAL',
            '800.110.100' => 'ERROR_GENERAL_GENERAL',
            '800.120.300' => 'ERROR_GENERAL_GENERAL',
            '800.120.401' => 'ERROR_GENERAL_GENERAL',
            '800.121.100' => 'ERROR_GENERAL_GENERAL',
            '800.130.100' => 'ERROR_GENERAL_GENERAL',
            '800.140.100' => 'ERROR_GENERAL_GENERAL',
            '800.140.101' => 'ERROR_GENERAL_GENERAL',
            '800.140.110' => 'ERROR_GENERAL_GENERAL',
            '800.140.111' => 'ERROR_GENERAL_GENERAL',
            '800.140.112' => 'ERROR_GENERAL_GENERAL',
            '800.140.113' => 'ERROR_GENERAL_GENERAL',
            '800.160.100' => 'ERROR_GENERAL_GENERAL',
            '800.160.110' => 'ERROR_GENERAL_GENERAL',
            '800.160.120' => 'ERROR_GENERAL_GENERAL',
            '800.160.130' => 'ERROR_GENERAL_GENERAL',
            '800.400.100' => 'ERROR_GENERAL_GENERAL',
            '800.400.101' => 'ERROR_GENERAL_GENERAL',
            '800.400.102' => 'ERROR_GENERAL_GENERAL',
            '800.400.103' => 'ERROR_GENERAL_GENERAL',
            '800.400.104' => 'ERROR_GENERAL_GENERAL',
            '800.400.105' => 'ERROR_GENERAL_GENERAL',
            '800.400.110' => 'ERROR_GENERAL_GENERAL',
            '800.400.500' => 'ERROR_GENERAL_GENERAL',
            '800.500.100' => 'ERROR_GENERAL_GENERAL',
            '800.500.110' => 'ERROR_GENERAL_GENERAL',
            '800.600.100' => 'ERROR_GENERAL_GENERAL',
            '800.700.100' => 'ERROR_GENERAL_GENERAL',
            '800.800.800' => 'ERROR_GENERAL_GENERAL',
            '800.800.801' => 'ERROR_GENERAL_GENERAL',
            '800.900.100' => 'ERROR_GENERAL_GENERAL',
            '800.900.201' => 'ERROR_GENERAL_GENERAL',
            '800.900.303' => 'ERROR_GENERAL_GENERAL',
            '800.900.401' => 'ERROR_GENERAL_GENERAL',
            '900.100.100' => 'ERROR_GENERAL_GENERAL',
            '900.100.200' => 'ERROR_GENERAL_GENERAL',
            '900.100.201' => 'ERROR_GENERAL_GENERAL',
            '900.100.202' => 'ERROR_GENERAL_GENERAL',
            '900.100.203' => 'ERROR_GENERAL_GENERAL',
            '900.100.300' => 'ERROR_GENERAL_GENERAL',
            '900.100.400' => 'ERROR_GENERAL_GENERAL',
            '900.100.500' => 'ERROR_GENERAL_GENERAL',
            '900.100.600' => 'ERROR_GENERAL_GENERAL',
            '900.200.100' => 'ERROR_GENERAL_GENERAL',
            '900.400.100' => 'ERROR_GENERAL_GENERAL',
            '999.999.999' => 'ERROR_GENERAL_GENERAL',

            '000.400.107' => 'ERROR_GENERAL_TIMEOUT',
            '100.395.502' => 'ERROR_GENERAL_TIMEOUT',

            '100.395.101' => 'ERROR_GIRO_NOSUPPORT',
            '100.395.102' => 'ERROR_GIRO_NOSUPPORT',

            '700.400.100' => 'ERROR_CAPTURE_BACKEND',
            '700.400.101' => 'ERROR_CAPTURE_BACKEND',
            '700.400.510' => 'ERROR_CAPTURE_BACKEND',

            '800.100.500' => 'ERROR_REORDER_BACKEND',
            '800.100.501' => 'ERROR_REORDER_BACKEND',

            '700.300.300' => 'ERROR_REFUND_BACKEND',
            '700.300.400' => 'ERROR_REFUND_BACKEND',
            '700.300.600' => 'ERROR_REFUND_BACKEND',
            '700.300.700' => 'ERROR_REFUND_BACKEND',
            '700.400.200' => 'ERROR_REFUND_BACKEND',
            '700.400.300' => 'ERROR_REFUND_BACKEND',
            '700.400.520' => 'ERROR_REFUND_BACKEND',
            '700.400.530' => 'ERROR_REFUND_BACKEND',
            '700.300.100' => 'ERROR_REFUND_BACKEND',

            '700.400.560' => 'ERROR_RECEIPT_BACKEND',
            '700.400.561' => 'ERROR_RECEIPT_BACKEND',

            '800.900.200' => 'ERROR_ADDRESS_PHONE'
        );
        if ($code) {
            return array_key_exists($code, $errorMessages) ? $errorMessages[$code] : 'ERROR_UNKNOWN';
        } else {
            return 'ERROR_UNKNOWN';
        }
    }

    /**
     * get backend error identifier based of code
     *
     * @param string $code
     * @return string
     */
    public static function getErrorIdentifierBackend($code)
    {
        $errorMessages = array(
            '700.400.100' => 'ERROR_CAPTURE_BACKEND',
            '700.400.101' => 'ERROR_CAPTURE_BACKEND',
            '700.400.510' => 'ERROR_CAPTURE_BACKEND',

            '800.100.500' => 'ERROR_REORDER_BACKEND',
            '800.100.501' => 'ERROR_REORDER_BACKEND',

            '700.300.300' => 'ERROR_REFUND_BACKEND',
            '700.300.400' => 'ERROR_REFUND_BACKEND',
            '700.300.600' => 'ERROR_REFUND_BACKEND',
            '700.300.700' => 'ERROR_REFUND_BACKEND',
            '700.400.200' => 'ERROR_REFUND_BACKEND',
            '700.400.300' => 'ERROR_REFUND_BACKEND',
            '700.400.520' => 'ERROR_REFUND_BACKEND',
            '700.400.530' => 'ERROR_REFUND_BACKEND',
            '700.300.100' => 'ERROR_REFUND_BACKEND',

            '700.400.560' => 'ERROR_RECEIPT_BACKEND',
            '700.400.561' => 'ERROR_RECEIPT_BACKEND'
        );
        if ($code) {
            return array_key_exists($code, $errorMessages) ? $errorMessages[$code] : 'ERROR_GENERAL_PROCESSING';
        } else {
            return 'ERROR_GENERAL_PROCESSING';
        }
    }

    /**
     * check if code status is inreview
     *
     * @param string $code
     * @return boolean
     */
    public static function isSuccessReview($code)
    {
        $inReviews = array(
            '000.400.000',
            '000.400.010',
            '000.400.020',
            '000.400.030',
            '000.400.040',
            '000.400.050',
            '000.400.060',
            '000.400.070',
            '000.400.080',
            '000.400.090'
        );
        if (in_array($code, $inReviews)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * check if code status is inreview
     *
     * @param string $identifier
     * @param string $langIsoCode
     * @return string
     */
    public static function getTranslationByLangIsoCode($identifier, $langIsoCode)
    {
        $_MODULE = array();
        $file = _PS_MODULE_DIR_.'vrpayecommerce/translations/'.$langIsoCode.'.php';
        include($file);

        $translation = $_MODULE['<{vrpayecommerce}prestashop>vrpayecommerce_'.md5($identifier)];
        if (isset($translation)) {
            return $translation;
        }

        return $identifier;
    }
}
