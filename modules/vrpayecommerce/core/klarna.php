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

class VRpayecommercePaymentKlarna
{
    /** constant for flag */
    const KLARNAFLAG_CHECKOUT_PAGE = 0;
    const KLARNAFLAG_PRODUCT_PAGE = 1;

    /** constant for account */
    const KLARNAPCLASS_ACCOUNT = 1;

    /** constant for country */
    const COUNTRY_AT = 15;
    const COUNTRY_DK = 59;
    const COUNTRY_FI = 73;
    const COUNTRY_DE = 81;
    const COUNTRY_NL = 154;
    const COUNTRY_NO = 164;
    const COUNTRY_SE = 209;

    /** constant for lowest payment */
    const COUNTRY_SE_LOWEST_PAYMENT = 50.0;
    const COUNTRY_NO_LOWEST_PAYMENT = 95.0;
    const COUNTRY_FI_LOWEST_PAYMENT = 8.95;
    const COUNTRY_DK_LOWEST_PAYMENT = 89.0;
    const COUNTRY_DE_LOWEST_PAYMENT = 6.95;
    const COUNTRY_AT_LOWEST_PAYMENT = 6.95;
    const COUNTRY_NL_LOWEST_PAYMENT = 5.0;
    const DEFAULT_LOWEST_PAYMENT = 0.0;

    /**
     * This constant tells the irr function when to stop.
     * If the calculation error is lower than this the calculation is done.
     *
     * @var float
     */
    protected static $accuracy = 0.01;

    /**
     * klarna url
     *
     * @var string
     */
    protected static $klarnaUrl = 'payment.testdrive.klarna.com';

    /**
     * xml request for get klarna pclass
     *
     * @param array $pClassParameters
     * @return xml
     */
    protected static function getXmlRequest($pClassParameters)
    {
        $methodCall = new SimpleXMLElement('<?xml version="1.0" encoding="ISO-8859-1"?><methodCall></methodCall>');
        $methodCall->addChild('methodName', 'get_pclasses');
        $params = $methodCall->addChild('params');

        $parameters = array(
            'proto_vsn' => array('type' => 'string', 'value' => '4.1'),
            'client_vsn' => array('type' => 'string', 'value' => 'xmlrpc:vrpayvirtuell:1.1.0'),
            'eid' => array('type' => 'int', 'value' => $pClassParameters['MERCHANT_ID']),
            'currency' => array('type' => 'int', 'value' => $pClassParameters['CURRENCY']),
            'shared_secret' => array('type' => 'string', 'value' => $pClassParameters['DIGEST']),
            'country' => array('type' => 'int', 'value' => $pClassParameters['COUNTRY']),
            'language' => array('type' => 'int', 'value' => $pClassParameters['LANGUAGE'])
        );

        foreach ($parameters as $value) {
            $param = $params->addChild('param');
            $paramValue = $param->addChild('value');
            $paramValue->addChild($value['type'], $value['value']);
        }

        $xmlRequest = $methodCall->asXML();

        return $xmlRequest;
    }

    /**
     * get klarna pclass
     *
     * @param array $pClassParameters
     * @return xml|boolean
     */
    public static function getPClasses($pClassParameters)
    {
        $xmlRequest = self::getXmlRequest($pClassParameters);
        $xmlResponse = VRpayecommercePaymentCore::getGatewayResponse(
            self::$klarnaUrl,
            $pClassParameters['SERVER'],
            'POST',
            $xmlRequest,
            'xml'
        );
        if ($xmlResponse['is_valid']) {
            return simplexml_load_string($xmlResponse['response']);
        }
        return false;
    }

    /**
     * Get xml response from klarna
     * @param  string $url
     * @param  string $xmlRequest
     * @return xml|boolean
     */
    private static function getXmlResponse($url, $xmlRequest, $serverMode)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        VRpayecommercePaymentCore::setSSLVerifypeer($ch, $serverMode);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return VRpayecommercePaymentCore::getResponse($ch, false);
    }

    /**
     * This is a simplified model of how our paccengine works if
     * a client always pays their bills. It adds interest and fees
     * and checks minimum payments. It will run until the value
     * of the account reaches 0, and return an array of all the
     * individual payments. Months is the amount of months to run
     * the simulation. Important! Don't feed it too few months or
     * the whole loan won't be paid off, but the other functions
     * should handle this correctly.
     *
     * Giving it too many months has no bad effects, or negative
     * amount of months which means run forever, but it will stop
     * as soon as the account is paid in full.
     *
     * Depending if the account is a base account or not, the
     * payment has to be 1/24 of the capital amount.
     *
     * The payment has to be at least $minpay, unless the capital
     * amount + interest + fee is less than $minpay; in that case
     * that amount is paid and the function returns since the client
     * no longer owes any money.
     *
     * @param array $parameters
     * float   pval    initial loan to customer (in any currency)
     * float   rate    interest rate per year in %
     * float   fee     monthly invoice fee
     * float   minpay  minimum monthly payment allowed for this country.
     * float   payment payment the client to pay each month
     * int     months  amount of months to run (-1 => infinity)
     * boolean base    is it a base account?
     *
     * @return array  An array of monthly payments for the customer.
     */
    protected static function fulpacc($parameters)
    {
        $bal = $parameters['pval'];
        $payarray = array();
        while (($parameters['months'] != 0) && ($bal > self::$accuracy)) {
            $interest = $bal * $parameters['rate'] / (100.0 * 12);
            $newbal = $bal + $interest + $parameters['fee'];

            if ($parameters['minpay'] >= $newbal || $parameters['payment'] >= $newbal) {
                $payarray[] = $newbal;
                return $payarray;
            }

            $newpay = max($parameters['payment'], $parameters['minpay']);
            if ($parameters['base']) {
                $newpay = max($newpay, $bal/24.0 + $parameters['fee'] + $interest);
            }

            $bal = $newbal - $newpay;
            $payarray[] = $newpay;
            $parameters['months'] -= 1;
        }

        return $payarray;
    }

    /**
     * Calculates how much you have to pay each month if you want to
     * pay exactly the same amount each month. The interesting input
     * is the amount of $months.
     *
     * It does not include the fee so add that later.
     *
     * Return value: monthly payment.
     *
     * @param float $pval   principal value
     * @param int   $months months to pay of in
     * @param float $rate   interest rate in % as before
     *
     * @return float monthly payment
     */
    protected static function annuity($pval, $months, $rate)
    {
        if ($months == 0) {
            return $pval;
        }

        if ($rate == 0) {
            return $pval/$months;
        }

        $p = $rate / (100.0*12);
        return $pval * $p / (1 - pow((1+$p), -$months));
    }

    /**
     * Grabs the array of all monthly payments for specified PClass.
     *
     * @param float        $sum    The sum for the order/product.
     * @param KlarnaPClass $pclass KlarnaPClass used to calculate the APR.
     * @param int          $flags  Checkout or Product page.
     *
     * @return array An array of monthly payments.
     */
    protected static function getPayArray($sum, $pclass, $flags)
    {
        $monthsfee = 0;
        if ($flags === self::KLARNAFLAG_CHECKOUT_PAGE) {
            $monthsfee = $pclass['invoiceFee'];
        }
        $startfee = 0;
        if ($flags === self::KLARNAFLAG_CHECKOUT_PAGE) {
            $startfee = $pclass['startFee'];
        }

        //Include start fee in sum
        $sum += $startfee;
        $base = ($pclass['type'] === self::KLARNAPCLASS_ACCOUNT);
        $lowest = self::getLowestPaymentForAccount($pclass['country']);

        if ($flags == self::KLARNAFLAG_CHECKOUT_PAGE) {
            $minpay = ($pclass['type'] === self::KLARNAPCLASS_ACCOUNT) ? $lowest : 0;
        } else {
            $minpay = 0;
        }

        $payment = self::annuity(
            $sum,
            $pclass['months'],
            $pclass['interestRate']
        );

        //Add monthly fee
        $payment += $monthsfee;

        return self::fulpacc(
            array(
                'pval'      => $sum,
                'rate'      => $pclass['interestRate'],
                'fee'       => $monthsfee,
                'minpay'    => $minpay,
                'payment'   => $payment,
                'months'    => $pclass['months'],
                'base'      => $base
            )
        );
    }

    /**
     * Calculates the monthly cost for the specified pclass.
     * The result is rounded up to the correct value depending on the
     * pclass country.<br>
     *
     * @param float        $sum    The sum for the order/product.
     * @param KlarnaPClass $pclass PClass used to calculate monthly cost.
     * @param int          $flags  Checkout or product page.
     *
     * @return float  The monthly cost.
     */
    public static function calcMonthlyCost($sum, $pclass, $flags)
    {
        if (is_numeric($sum) && (!is_int($sum) || !is_float($sum))) {
            $sum = (float) $sum;
        }

        if (is_numeric($flags) && !is_int($flags)) {
            $flags = (int) $flags;
        }

        $payarr = self::getPayArray($sum, $pclass, $flags);
        $value = 0;
        if (isset($payarr[0])) {
            $value = $payarr[0];
        }

        if (self::KLARNAFLAG_CHECKOUT_PAGE == $flags) {
            return round($value, 2);
        }
        return self::pRound($value, $pclass['country']);
    }

    /**
     * Returns the lowest monthly payment for Klarna Account.
     *
     * @param int $country KlarnaCountry constant.
     *
     * @return int|float Lowest monthly payment.
     */
    protected static function getLowestPaymentForAccount($country)
    {
        switch ($country) {
            case self::COUNTRY_SE:
                return self::COUNTRY_SE_LOWEST_PAYMENT;
            case self::COUNTRY_NO:
                return self::COUNTRY_NO_LOWEST_PAYMENT;
            case self::COUNTRY_FI:
                return self::COUNTRY_FI_LOWEST_PAYMENT;
            case self::COUNTRY_DK:
                return self::COUNTRY_DK_LOWEST_PAYMENT;
            case self::COUNTRY_DE:
                return self::COUNTRY_DE_LOWEST_PAYMENT;
            case self::COUNTRY_AT:
                return self::COUNTRY_AT_LOWEST_PAYMENT;
            case self::COUNTRY_NL:
                return self::COUNTRY_NL_LOWEST_PAYMENT;
            default:
                return self::DEFAULT_LOWEST_PAYMENT;
        }
    }

    /**
     * Rounds a value depending on the specified country.
     *
     * @param int|float $value   The value to be rounded.
     * @param int       $country KlarnaCountry constant.
     *
     * @return float|int
     */
    protected static function pRound($value, $country)
    {
        switch ($country) {
            case self::COUNTRY_FI:
            case self::COUNTRY_DE:
            case self::COUNTRY_NL:
            case self::COUNTRY_AT:
                $multiply = 10; //Round to closest decimal
                break;
            default:
                $multiply = 1; //Round to closest integer
                break;
        }

        return floor(($value*$multiply)+0.5)/$multiply;
    }

    /**
     * get Klarna monthly cost.
     *
     * @return float
     */
    public static function getKlarnaCalcMonthlyCost($amount, $pClass)
    {
        $flags = self::KLARNAFLAG_CHECKOUT_PAGE;

        return self::calcMonthlyCost($amount, $pClass, $flags);
    }
}
