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

require_once(dirname(__FILE__).'/core.php');

/**
 * Class VersionTracker
 * @deprecated since 2020-06-22 in version 2.1.1
 */
class VersionTracker
{
    private static $versionTrackerUrl = 'http://api.dbserver.payreto.eu/v1/tracker';

    /**
     * version tracker URL
     *
     * @return string
     */
    private static function getVersionTrackerUrl()
    {
        return self::$versionTrackerUrl;
    }

    /**
     * get data to be sent version tracker
     *
     * @param array $versionData
     * @return array $data
     */
    private static function getVersionTrackerParameter($versionData)
    {
        $data = 'transaction_mode=' .$versionData['transaction_mode'].
                '&ip_address=' .$versionData['ip_address'].
                '&shop_version=' .$versionData['shop_version'].
                '&plugin_version=' .$versionData['plugin_version'].
                '&client=' .$versionData['client'].
                '&hash=' .md5($versionData['shop_version'].$versionData['plugin_version'].$versionData['client']);

        if (isset($versionData['shop_system'])) {
            $data .= '&shop_system=' .$versionData['shop_system'];
        }
        if (isset($versionData['email'])) {
            $data .= '&email=' .$versionData['email'];
        }
        if (isset($versionData['merchant_id'])) {
            $data .= '&merchant_id=' .$versionData['merchant_id'];
        }
        if (isset($versionData['shop_url'])) {
            $data .= '&shop_url=' .$versionData['shop_url'];
        }
        if (isset($versionData['merchant_location'])) {
            $data .= '&merchant_location=' .$versionData['merchant_location'];
        }
        return $data;
    }

    /**
     * send data to version tracker
     *
     * @param array $versionData
     * @return array $response
     */
    public static function sendVersionTracker($versionData)
    {
        PrestaShopLogger::addLog('VRpayecommerce - start send to version tracker');
        PrestaShopLogger::addLog('VRpayecommerce - get versionData : '. print_r($versionData, true));

        $postData = self::getVersionTrackerParameter($versionData);
        PrestaShopLogger::addLog('VRpayecommerce - get postData : '. $postData);

        $url = self::getVersionTrackerUrl();
        PrestaShopLogger::addLog('VRpayecommerce - get API url : '. $url);

        $response = VRpayecommercePaymentCore::getGatewayResponse(
            $url,
            $versionData['transaction_mode'],
            'POST',
            $postData
        )['response'];
        PrestaShopLogger::addLog('VRpayecommerce - get response from gateway : '. print_r($response, true));

        return $response;
    }
}
