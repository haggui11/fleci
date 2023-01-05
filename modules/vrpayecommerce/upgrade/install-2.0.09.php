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

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_0_09()
{
    // update klarna paylater. value base on klarna invoice value
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNAPAYLATER_ACTIVE',
        Configuration::get('VRPAYECOMMERCE_KLARNAINV_ACTIVE')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNAPAYLATER_SERVER',
        Configuration::get('VRPAYECOMMERCE_KLARNAINV_SERVER')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNAPAYLATER_CHANNEL',
        Configuration::get('VRPAYECOMMERCE_KLARNAINV_CHANNEL')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNAPAYLATER_MERCHANT_ID',
        Configuration::get('VRPAYECOMMERCE_KLARNAINV_MERCHANT_ID')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNAPAYLATER_SORT',
        Configuration::get('VRPAYECOMMERCE_KLARNAINV_SORT')
    );
    
    // update klarna slice it. value base on klarna installment value
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_ACTIVE',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_ACTIVE')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_SERVER',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_SERVER')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_CHANNEL',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_CHANNEL')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_MERCHANT_ID',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_MERCHANT_ID')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_CURRENCY',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_CURRENCY')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_COUNTRY',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_COUNTRY')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_LANGUAGE',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_LANGUAGE')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_SHARED_SECRET',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_SHARED_SECRET')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_ID',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_PCLASS_ID')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_DESCRIPTION',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_PCLASS_DESCRIPTION')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_MONTHS',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_PCLASS_MONTHS')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_START_FEE',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_PCLASS_START_FEE')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_INVOICE_FEE',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_PCLASS_INVOICE_FEE')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_INTEREST_RATE',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_PCLASS_INTEREST_RATE')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_MIN_PURCHASE',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_PCLASS_MIN_PURCHASE')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_COUNTRY',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_PCLASS_COUNTRY')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_TYPE',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_PCLASS_TYPE')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_EXPIRY_DATE',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_PCLASS_EXPIRY_DATE')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNASLICEIT_SORT',
        Configuration::get('VRPAYECOMMERCE_KLARNAINS_SORT')
    );

    // update klarna online bank transfer. value base on sofort value
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNAOBT_ACTIVE',
        Configuration::get('VRPAYECOMMERCE_SUE_ACTIVE')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNAOBT_SERVER',
        Configuration::get('VRPAYECOMMERCE_SUE_SERVER')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNAOBT_CHANNEL',
        Configuration::get('VRPAYECOMMERCE_SUE_CHANNEL')
    );
    Configuration::updateValue(
        'VRPAYECOMMERCE_KLARNAOBT_SORT',
        Configuration::get('VRPAYECOMMERCE_SUE_SORT')
    );

    // delete configuration
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINV_ACTIVE');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINV_SERVER');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINV_CHANNEL');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINV_MERCHANT_ID');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINV_SORT');

    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_ACTIVE');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_SERVER');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_CHANNEL');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_MERCHANT_ID');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_CURRENCY');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_COUNTRY');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_LANGUAGE');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_SHARED_SECRET');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_PCLASS_ID');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_PCLASS_DESCRIPTION');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_PCLASS_MONTHS');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_PCLASS_START_FEE');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_PCLASS_INVOICE_FEE');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_PCLASS_INTEREST_RATE');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_PCLASS_MIN_PURCHASE');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_PCLASS_COUNTRY');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_PCLASS_TYPE');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_PCLASS_EXPIRY_DATE');
    Configuration::deleteByName('VRPAYECOMMERCE_KLARNAINS_SORT');

    Configuration::deleteByName('VRPAYECOMMERCE_SUE_ACTIVE');
    Configuration::deleteByName('VRPAYECOMMERCE_SUE_SERVER');
    Configuration::deleteByName('VRPAYECOMMERCE_SUE_CHANNEL');
    Configuration::deleteByName('VRPAYECOMMERCE_SUE_SORT');

    return true;
}
