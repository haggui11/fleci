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

if (!class_exists('VrpayecommerceCustomMailAlert')) {
    require_once(dirname(__FILE__).'/VrpayecommerceCustomMailAlert.php');
}
require_once(dirname(__FILE__).'/core/core.php');
require_once(dirname(__FILE__).'/core/klarna.php');

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Vrpayecommerce extends PaymentModule
{
    protected $html = '';
    public $payment_methods = array (
        'CC',
        'CCSAVED',
        'DD',
        'DDSAVED',
        'GIROPAY',
        // 'KLARNAPAYLATER',
        // 'KLARNASLICEIT',
        'PAYPAL',
        'PAYPALSAVED',
        'KLARNAOBT',
        'PAYDIREKT',
        'EASYCREDIT',
        'ENTERPAY'
    );

    public $db_pa_payment_methods = array (
        'CC',
        'CCSAVED',
        'DC',
        'DDSAVED',
        'DD',
        'PAYDIREKT',
        'PAYPAL',
        'PAYPALSAVED'
    );

    protected $klarnasliceitConfig = array(
        'MERCHANT_ID',
        'CURRENCY',
        'COUNTRY',
        'LANGUAGE',
        'SHARED_SECRET',
        'PCLASS',
        'PCLASS_ID',
        'PCLASS_DESCRIPTION',
        'PCLASS_MONTHS',
        'PCLASS_START_FEE',
        'PCLASS_INVOICE_FEE',
        'PCLASS_INTEREST_RATE',
        'PCLASS_MIN_PURCHASE',
        'PCLASS_COUNTRY',
        'PCLASS_TYPE',
        'PCLASS_EXPIRY_DATE'
    );

    public function __construct()
    {
        $this->name = 'vrpayecommerce';
        $this->tab = 'payments_gateways';
        $this->version = '2.3.6';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->author = 'VR pay eCommerce';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = 'VR pay eCommerce';
        $this->description = 'Accepts payments by VR pay eCommerce';
        if ($this->l('BACKEND_TT_DELETE_DETAILS') == "BACKEND_TT_DELETE_DETAILS") {
            $this->confirmUninstall =  "Are you sure you want to delete your details ?";
        } else {
            $this->confirmUninstall = $this->l('BACKEND_TT_DELETE_DETAILS');
        }
    }

    public function install()
    {
        $this->warning = null;
        if (is_null($this->warning) && !function_exists('curl_init')) {
            $this->warning = $this->l('ERROR_MESSAGE_CURL_REQUIRED');
            if ($this->l('ERROR_MESSAGE_CURL_REQUIRED') == "ERROR_MESSAGE_CURL_REQUIRED") {
                $this->warning = "cURL is required to use this module. Please install the php extention cURL." ;
            }
        }
        if (is_null($this->warning)
            && !(parent::install()
            && $this->registerHook('header')
            && $this->registerHook('displayAdminOrder')
            && $this->registerHook('displayAdminAfterHeader')
            && $this->registerHook('customerAccount')
            && $this->registerHook('paymentReturn')
            && $this->registerHook('updateOrderStatus')
            && $this->registerHook('displayInvoice')
            && $this->registerHook('paymentOptions')
            && $this->registerHook('displayTop'))) {
            if ($this->l('ERROR_MESSAGE_INSTALL_MODULE') == "ERROR_MESSAGE_INSTALL_MODULE") {
                $this->warning = "There was an Error installing the module.";
            } else {
                $this->warning = $this->l('ERROR_MESSAGE_INSTALL_MODULE');
            }
        }

        if (is_null($this->warning) && (!$this->createOrderRefTables() || !$this->createPaymentRecurringTables())) {
            if ($this->l('ERROR_MESSAGE_CREATE_TABLE') == "ERROR_MESSAGE_CREATE_TABLE") {
                $this->warning = "There was an Error creating a custom table.";
            } else {
                $this->warning = $this->l('ERROR_MESSAGE_CREATE_TABLE');
            }
        }

        if (is_null($this->warning) && !$this->addVrpayecommerceOrderStatus()) {
            if ($this->l('ERROR_MESSAGE_CREATE_ORDER_STATUS') == "ERROR_MESSAGE_CREATE_ORDER_STATUS") {
                $this->warning = "There was an Error creating a custom order status.";
            } else {
                $this->warning = $this->l('ERROR_MESSAGE_CREATE_ORDER_STATUS');
            }
        }

        //default configuration for vrpayecommerce at first install
        $this->setDefaultValues();

        return is_null($this->warning);
    }

    public function setDefaultValues()
    {
        // default general setting.
        Configuration::updateValue('VRPAYECOMMERCE_GENERAL_BEARER', '');
        Configuration::updateValue('VRPAYECOMMERCE_GENERAL_LOGIN', '');
        Configuration::updateValue('VRPAYECOMMERCE_GENERAL_PASSWORD', '');
        Configuration::updateValue('VRPAYECOMMERCE_GENERAL_RECURRING', '1');
        Configuration::updateValue('VRPAYECOMMERCE_GENERAL_MERCHANTEMAIL', '');
        Configuration::updateValue('VRPAYECOMMERCE_GENERAL_MERCHANTNO', '');
        Configuration::updateValue('VRPAYECOMMERCE_GENERAL_SHOPURL', '');
        Configuration::updateValue('VRPAYECOMMERCE_GENERAL_VERSION_TRACKER', '1');
        Configuration::updateValue('VRPAYECOMMERCE_GENERAL_MERCHANTLOCATION', '');
        Configuration::updateValue('VRPAYECOMMERCE_POPUP', true);

        //default payment seting
        foreach ($this->payment_methods as $key => $paymentCode) {
            Configuration::updateValue('VRPAYECOMMERCE_'.$paymentCode.'_ACTIVE', '1');
            Configuration::updateValue('VRPAYECOMMERCE_'.$paymentCode.'_SERVER', 'TEST');
            Configuration::updateValue('VRPAYECOMMERCE_'.$paymentCode.'_CHANNEL', '');
            Configuration::updateValue('VRPAYECOMMERCE_'.$paymentCode.'_SORT', $key);
        }

        //default setting payment type
        Configuration::updateValue('VRPAYECOMMERCE_CC_MODE', 'DB');
        Configuration::updateValue('VRPAYECOMMERCE_CCSAVED_MODE', 'DB');
        Configuration::updateValue('VRPAYECOMMERCE_PAYPAL_MODE', 'DB');
        Configuration::updateValue('VRPAYECOMMERCE_PAYPALSAVED_MODE', 'DB');
        Configuration::updateValue('VRPAYECOMMERCE_DC_MODE', 'DB');
        Configuration::updateValue('VRPAYECOMMERCE_DD_MODE', 'DB');
        Configuration::updateValue('VRPAYECOMMERCE_DDSAVED_MODE', 'DB');
        Configuration::updateValue('VRPAYECOMMERCE_PAYDIREKT_MODE', 'DB');

        //default setting payment is partial
        Configuration::updateValue('VRPAYECOMMERCE_PAYDIREKT_PAYMENT_IS_PARTIAL', '0');

        //default minimum age
        Configuration::updateValue('VRPAYECOMMERCE_PAYDIREKT_MINIMUM_AGE', '');

        //default setting card types
        Configuration::updateValue('VRPAYECOMMERCE_CC_CARDS', '');
        Configuration::updateValue('VRPAYECOMMERCE_CCSAVED_CARDS', '');
        Configuration::updateValue('VRPAYECOMMERCE_DC_CARDS', '');

        //default setting amount registration
        Configuration::updateValue('VRPAYECOMMERCE_CCSAVED_AMOUNT', '');
        Configuration::updateValue('VRPAYECOMMERCE_DDSAVED_AMOUNT', '');
        Configuration::updateValue('VRPAYECOMMERCE_PAYPALSAVED_AMOUNT', '');

        //default setting multichannel
        Configuration::updateValue('VRPAYECOMMERCE_CCSAVED_MULTICHANNEL', '0');
        Configuration::updateValue('VRPAYECOMMERCE_CCSAVED_CHANNELMOTO', '');

        Configuration::updateValue('VRPAYECOMMERCE_KLARNAPAYLATER_MERCHANT_ID', '');

        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_MERCHANT_ID', '');
        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_CURRENCY', '');
        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_COUNTRY', '');
        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_LANGUAGE', '');
        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_SHARED_SECRET', '');
        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_ID', '');
        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_DESCRIPTION', '');
        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_MONTHS', '');
        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_START_FEE', '');
        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_INVOICE_FEE', '');
        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_INTEREST_RATE', '');
        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_MIN_PURCHASE', '');
        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_COUNTRY', '');
        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_TYPE', '');
        Configuration::updateValue('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_EXPIRY_DATE', '');
    }

    /**
     * prestashop function for uninstall process
     * @return void
     */
    public function uninstall()
    {

        if (!Configuration::deleteByName('VRPAYECOMMERCE_CC_ACTIVE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_CCSAVED_ACTIVE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DC_ACTIVE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DDSAVED_ACTIVE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DD_ACTIVE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_EPS_ACTIVE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_GIROPAY_ACTIVE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_IDEAL_ACTIVE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYPAL_ACTIVE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYDIREKT_ACTIVE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYPALSAVED_ACTIVE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNAOBT_ACTIVE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNAPAYLATER_ACTIVE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_ACTIVE')

            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYMENT_STATUS_PA')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYMENT_STATUS_REVIEW')

            || !Configuration::deleteByName('VRPAYECOMMERCE_CC_SORT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_CCSAVED_SORT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DC_SORT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DD_SORT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DDSAVED_SORT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_EPS_SORT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_GIROPAY_SORT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_IDEAL_SORT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYPAL_SORT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYDIREKT_SORT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYPALSAVED_SORT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNAOBT_SORT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNAPAYLATER_SORT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_SORT')

            || !Configuration::deleteByName('VRPAYECOMMERCE_GENERAL_BEARER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_GENERAL_LOGIN')
            || !Configuration::deleteByName('VRPAYECOMMERCE_GENERAL_PASSWORD')
            || !Configuration::deleteByName('VRPAYECOMMERCE_GENERAL_RECURRING')
            || !Configuration::deleteByName('VRPAYECOMMERCE_GENERAL_MERCHANTEMAIL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_GENERAL_MERCHANTNO')
            || !Configuration::deleteByName('VRPAYECOMMERCE_GENERAL_SHOPURL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_GENERAL_VERSION_TRACKER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_GENERAL_MERCHANTLOCATION')

            || !Configuration::deleteByName('VRPAYECOMMERCE_CC_CHANNEL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_CCSAVED_CHANNEL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DC_CHANNEL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DD_CHANNEL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DDSAVED_CHANNEL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_EPS_CHANNEL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_GIROPAY_CHANNEL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_IDEAL_CHANNEL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYPAL_CHANNEL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYDIREKT_CHANNEL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYPALSAVED_CHANNEL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNAOBT_CHANNEL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNAPAYLATER_CHANNEL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_CHANNEL')

            || !Configuration::deleteByName('VRPAYECOMMERCE_CC_SERVER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_CCSAVED_SERVER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DC_SERVER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DD_SERVER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DDSAVED_SERVER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_EPS_SERVER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_GIROPAY_SERVER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_IDEAL_SERVER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYPAL_SERVER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYDIREKT_SERVER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYPALSAVED_SERVER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNAOBT_SERVER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNAPAYLATER_SERVER')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_SERVER')

            || !Configuration::deleteByName('VRPAYECOMMERCE_CC_MODE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_CCSAVED_MODE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYPAL_MODE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYPALSAVED_MODE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DC_MODE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DD_MODE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DDSAVED_MODE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYDIREKT_MODE')

            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYDIREKT_PAYMENT_IS_PARTIAL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYDIREKT_MINIMUM_AGE')

            || !Configuration::deleteByName('VRPAYECOMMERCE_CC_CARDS')
            || !Configuration::deleteByName('VRPAYECOMMERCE_CCSAVED_CARDS')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DC_CARDS')

            || !Configuration::deleteByName('VRPAYECOMMERCE_CCSAVED_AMOUNT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_DDSAVED_AMOUNT')
            || !Configuration::deleteByName('VRPAYECOMMERCE_PAYPALSAVED_AMOUNT')

            || !Configuration::deleteByName('VRPAYECOMMERCE_CCSAVED_MULTICHANNEL')
            || !Configuration::deleteByName('VRPAYECOMMERCE_CCSAVED_CHANNELMOTO')

            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNAPAYLATER_MERCHANT_ID')

            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_MERCHANT_ID')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_CURRENCY')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_COUNTRY')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_LANGUAGE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_SHARED_SECRET')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_ID')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_DESCRIPTION')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_MONTHS')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_START_FEE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_INVOICE_FEE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_INTEREST_RATE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_MIN_PURCHASE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_COUNTRY')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_TYPE')
            || !Configuration::deleteByName('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_EXPIRY_DATE')

            || !$this->unregisterHook('header')
            || !$this->unregisterHook('displayAdminOrder')
            || !$this->unregisterHook('displayAdminAfterHeader')
            || !$this->unregisterHook('customerAccount')
            || !$this->unregisterHook('paymentReturn')
            || !$this->unregisterHook('updateOrderStatus')
            || !$this->unregisterHook('displayInvoice')
            || !$this->unregisterHook('paymentOptions')
            || !$this->unregisterHook('displayTop')
            || !parent::uninstall()) {
                return false;
        }

        return true;
    }

    public function createOrderRefTables()
    {
        $sql= "CREATE TABLE IF NOT EXISTS `vrpayecommerce_order_ref`(
            `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_order` INT(10) NOT NULL,
            `transaction_id` VARCHAR(32) NOT NULL,
            `payment_method` VARCHAR(50) NOT NULL,
            `order_status` VARCHAR(50) NOT NULL,
            `ref_id` VARCHAR(32) NOT NULL,
            `payment_code` VARCHAR(5) NOT NULL,
            `currency` VARCHAR(3) NOT NULL,
            `amount` decimal(17,2) NOT NULL,
            `mandate_date` DATE,
            `mandate_id` VARCHAR(50)
            )";

        if (!Db::getInstance()->Execute($sql)) {
            return false;
        }
            return true;
    }

    public function createPaymentRecurringTables()
    {
        $sql= "CREATE TABLE IF NOT EXISTS `vrpayecommerce_payment_recurring`(
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `cust_id` int(10) NOT NULL,
            `payment_group` VARCHAR(6),
            `brand` VARCHAR(100),
            `holder` VARCHAR(100) NULL default NULL,
            `email` VARCHAR(100) NULL default NULL,
            `last4digits` VARCHAR(4),
            `expiry_month` VARCHAR(2),
            `expiry_year` VARCHAR(4),
            `ref_id` VARCHAR(32),
            `payment_default` boolean NOT NULL default '0')";


        if (!Db::getInstance()->Execute($sql)) {
            return false;
        }

        $showSql = "SHOW columns FROM `vrpayecommerce_payment_recurring` LIKE 'server_mode'";
        $serverMode = Db::getInstance()->ExecuteS($showSql);

        if (empty($serverMode)) {
            $sql = "ALTER TABLE vrpayecommerce_payment_recurring
            ADD server_mode VARCHAR(4) NOT NULL AFTER expiry_year,
            ADD channel_id VARCHAR(32) NOT NULL AFTER server_mode";

            if (!Db::getInstance()->Execute($sql)) {
                return false;
            }
        }
        return true;
    }

    public function addOrderStatus($config_key, $locale_de, $locale_en)
    {
        if (!Configuration::get($config_key)) {
            $newOrderState = new OrderState();
            $newOrderState->name = array();
            $newOrderState->module_name = $this->name;
            $newOrderState->send_email = true;
            $newOrderState->color = 'LimeGreen';
            $newOrderState->hidden = false;
            $newOrderState->delivery = false;
            $newOrderState->logable = true;
            $newOrderState->invoice = true;
            $newOrderState->paid = true;
            foreach (Language::getLanguages() as $language) {
                $newOrderState->template[$language['id_lang']] = 'payment';
                if (Tools::strtolower($language['iso_code']) == 'de') {
                    $newOrderState->name[$language['id_lang']] = $locale_de;
                } else {
                    $newOrderState->name[$language['id_lang']] = $locale_en;
                }
            }

            if ($newOrderState->add()) {
                $vrpay_icon = dirname(__FILE__).'/logo.gif';
                $new_state_icon = dirname(__FILE__).'/../../img/os/'.(int)$newOrderState->id.'.gif';
                copy($vrpay_icon, $new_state_icon);
            }

            Configuration::updateValue($config_key, (int)$newOrderState->id);
        }
    }

    public function addVrpayecommerceOrderStatus()
    {
        try {
            $this->addOrderStatus(
                'VRPAYECOMMERCE_PAYMENT_STATUS_PA',
                'Pre-Authorization of Payment',
                'Pre-Authorization of Payment'
            );
            $this->addOrderStatus(
                'VRPAYECOMMERCE_PAYMENT_STATUS_REVIEW',
                'In Review',
                'In Review'
            );
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    public function hookdisplayInvoice($hook)
    {
        $success_message = '';
        $error_message = '';

        if (isset($this->context->cookie->vrpayecommerce_status_capture)) {
            if ($this->context->cookie->vrpayecommerce_status_capture == 'true') {
                $success_message = 'capture';
            } elseif ($this->context->cookie->vrpayecommerce_status_capture == 'false') {
                $error_message = 'capture';
            } else {
                $error_message = 'ssl';
            }
        } elseif (isset($this->context->cookie->vrpayecommerce_status_update_order)) {
            if ($this->context->cookie->vrpayecommerce_status_update_order == 'true') {
                $success_message = 'update_order';
            } elseif ($this->context->cookie->vrpayecommerce_status_update_order == 'false') {
                $error_message = 'update_order';
            } else {
                $error_message = 'ssl';
            }
        } elseif (isset($this->context->cookie->vrpayecommerce_status_refund)) {
            if ($this->context->cookie->vrpayecommerce_status_refund == 'true') {
                $success_message = 'refund';
            } elseif ($this->context->cookie->vrpayecommerce_status_refund == 'false') {
                $error_message = 'refund';
            } else {
                $error_message = 'ssl';
            }
        } elseif (isset($this->context->cookie->vrpayecommerce_status_create_order)) {
            if ($this->context->cookie->vrpayecommerce_status_create_order) {
                $success_message = 'new_order';
            }
        }

        $orderId =Tools::getValue('id_order');
        $order = new Order((int) $orderId);

        $this->context->smarty->assign(array(
            'module' => $order->module,
            'successMessage' => $success_message,
            'errorMessage' => $error_message
        ));

        unset($this->context->cookie->vrpayecommerce_status_capture);
        unset($this->context->cookie->vrpayecommerce_status_update_order);
        unset($this->context->cookie->vrpayecommerce_status_refund);
        unset($this->context->cookie->vrpayecommerce_status_create_order);

        return $this->display(__FILE__, 'views/templates/hook/displayStatusOrder.tpl');
    }

    public function hookCustomerAccount($params)
    {
        if (Configuration::get('VRPAYECOMMERCE_GENERAL_RECURRING')) {
            $this->context->smarty->assign(array(
                'paymentInformationUrl' =>
                    $this->context->link->getModuleLink('vrpayecommerce', 'paymentinformation', array(), true)
            ));
            return $this->display(__FILE__, 'payment_information.tpl');
        }
        return false;
    }

    public function checkCurrency($cart)
    {
        $currencyOrder = new Currency($cart->id_currency);
        $currencyModules = $this->getCurrency($cart->id_currency);

        if (is_array($currencyModules)) {
            foreach ($currencyModules as $currencyModule) {
                if ($currencyOrder->id == $currencyModule['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * the PrestaShop hook to display an error message
     * @return boolean
     */
    public function hookdisplayTop()
    {
        if (!$this->active || !Tools::getValue('vrpayecommerce_error')) {
            return false;
        } else {
            $errorMessage = $this->getErrorMessage(Tools::getValue('vrpayecommerce_error'));

            $this->context->smarty->assign(array(
                'error_message' => $errorMessage,
                'module' => "vrpayecommerce"
            ));
            return $this->display(__FILE__, 'error.tpl');
        }
    }

    public function hookHeader($parameters)
    {
        if ($this->active
            && isset($this->context->controller->php_self)
            && $this->context->controller->php_self == 'order'
        ) {
            $this->context->controller->addCSS(($this->_path).'views/css/payment_options.css', 'all');
            $this->context->controller->addJS(($this->_path).'views/js/payment_options.js', 'all');
        }
    }

    public function hookPaymentOptions($parameters)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($parameters['cart'])) {
            return;
        }

        $paymentMethods = $this->getAvailablePaymentMethods();
        $language = Tools::strtolower($this->getCountryIsoCode());

        $paymentOptions = array();

        foreach ($paymentMethods as $value) {
            $newOption = new PaymentOption();
            switch ($value) {
                case 'DD':
                case 'DDSAVED':
                    $logo = 'sepa.png';
                    break;
                case 'PAYPALSAVED':
                    $logo = 'paypal.png';
                    break;
                case 'KLARNAPAYLATER':
                    $logo = $language == 'de' ? 'klarnapaylater_de.png' : 'klarnapaylater_en.png';
                    break;
                case 'KLARNASLICEIT':
                    $logo = $language == 'de' ? 'klarnasliceit_de.png' : 'klarnasliceit_en.png';
                    break;
                case 'KLARNAOBT':
                    $logo = $language == 'de' ? 'klarnaobt_de.png' : 'klarnaobt_en.png';
                    break;
                default:
                    $logo = Tools::strtolower($value).'.png';
                    break;
            }
            $paymentController = $this->context->link->getModuleLink(
                $this->name,
                'payment'.Tools::strtolower($value),
                array(),
                true
            );
            if ($value == 'CC' || $value == 'CCSAVED') {
                $ccBrands = explode(',', Configuration::get('VRPAYECOMMERCE_'.$value.'_CARDS'));
                if (!empty($ccBrands)) {
                    $hiddenLogos = '';
                    foreach ($ccBrands as $key => $value) {
                        $logo = Tools::strtolower($value).'.png';
                        $logo = Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/'.$logo);
                        $hiddenLogos .= '<input type="hidden" id="cc_brand_'.$key.'" value="'.$logo.'">';
                    }
                    $logo = Tools::strtolower($ccBrands[0]).'.png';
                    $logo = Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/'.$logo);
                    $newOption->setCallToActionText('')
                        ->setLogo($logo)
                        ->setAdditionalInformation($hiddenLogos)
                        ->setAction($paymentController);
                }
            } else {
                if ($value == 'EASYCREDIT') {
                    $easycreditNotify = $this->getEasycreditNotify();
                    $this->context->smarty->assign(array(
                        'easycreditNotifys'   => $easycreditNotify,
                    ));

                    $newOption->setCallToActionText('')
                        ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/'.$logo))
                        ->setAdditionalInformation(
                            $this->context->smarty->fetch(
                                'module:vrpayecommerce/views/templates/front/easycredit_notification.tpl'
                            )
                        )
                        ->setAction($paymentController);
                } elseif ($value == 'KLARNAPAYLATER') {
                    $notify = $this->getKlarnaDisabledErrors($value);
                    $this->context->smarty->assign(array(
                        'klarna_notifys'   => $notify,
                    ));

                    $newOption->setCallToActionText('')
                        ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/'.$logo))
                        ->setAdditionalInformation(
                            $this->context->smarty->fetch(
                                'module:vrpayecommerce/views/templates/front/klarnapaylater_notification.tpl'
                            )
                        )
                        ->setAction($paymentController);
                } elseif ($value == 'KLARNASLICEIT') {
                    $notify = $this->getKlarnaDisabledErrors($value);
                    $this->context->smarty->assign(array(
                        'klarna_notifys'   => $notify,
                    ));

                    $newOption->setCallToActionText('')
                        ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/'.$logo))
                        ->setAdditionalInformation(
                            $this->context->smarty->fetch(
                                'module:vrpayecommerce/views/templates/front/klarnasliceit_notification.tpl'
                            )
                        )
                        ->setAction($paymentController);
                } elseif ($value == 'ENTERPAY') {
                    $newOption->setCallToActionText(
                            $this->getEnterPayCallToActionText()
                        )
                        ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/'.$logo))
                        ->setAdditionalInformation(
                            $this->getEnterPayNotify()
                        )
                        ->setAction($paymentController);
                } else {
                    $newOption->setCallToActionText('')
                        ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/'.$logo))
                        ->setAction($paymentController);
                }
            }
            $paymentOptions[] = $newOption;
        }

        return $paymentOptions;
    }

    private function getAvailablePaymentMethods()
    {

        if ($this->context->customer->id_default_group == 2) {
            $remove_methods = array('CCSAVED','DDSAVED','PAYPALSAVED');
        } elseif (Configuration::get('VRPAYECOMMERCE_GENERAL_RECURRING')) {
            $remove_methods = array('CC','DD','PAYPAL');
        } else {
            $remove_methods = array('CCSAVED','DDSAVED','PAYPALSAVED');
        }

        $paymentMethods = array_diff($this->payment_methods, $remove_methods);
        $sortedPaymentMethods = $this->sortedPaymentMethods($paymentMethods);

        return $sortedPaymentMethods;
    }

    private function sortedPaymentMethods($paymentMethods)
    {

        $sortedPaymentMethods = array();
        foreach ($paymentMethods as $pm) {
            if (Configuration::get('VRPAYECOMMERCE_'.$pm.'_ACTIVE')) {
                if ($pm == 'CC' || $pm == 'CCSAVED' || $pm == 'DC') {
                    if (trim(Configuration::get('VRPAYECOMMERCE_'.$pm.'_CARDS'))) {
                        $sortedPaymentMethods[$pm] = Configuration::get('VRPAYECOMMERCE_'.$pm.'_SORT');
                    }
                } else {
                    $sortedPaymentMethods[$pm] = Configuration::get('VRPAYECOMMERCE_'.$pm.'_SORT');
                }
            }
        }

        $keys   = array_keys($sortedPaymentMethods);
        $values = array_values($sortedPaymentMethods);
        array_multisort($values, $keys);

        return $keys;
    }

    public function hookPaymentReturn($parameters)
    {
        if (!$this->active) {
            return false;
        }

        $state = $parameters['order']->getCurrentState();
        $status='';
        $template='';
        if ($state == Configuration::get('PS_OS_PAYMENT') ||
            $state == Configuration::get('VRPAYECOMMERCE_PAYMENT_STATUS_PA') ||
            $state == Configuration::get('VRPAYECOMMERCE_PAYMENT_STATUS_REVIEW')
        ) {
            $this->smarty->assign(array(
                'shop_name' => $this->context->shop->name,
                'status' => 'ok'
            ));

            if ($state == Configuration::get('PS_OS_PAYMENT')) {
                $status='SUCCESFUL';
                $template='order_successful';
            } elseif ($state == Configuration::get('VRPAYECOMMERCE_PAYMENT_STATUS_PA')) {
                $status='PRE-AUTHORIZATION';
                $template='order_pa';
            } elseif ($state == Configuration::get('VRPAYECOMMERCE_PAYMENT_STATUS_REVIEW')) {
                $status='IN REVIEW';
                $template='order_ir';
            }

            $this->mailAlert(
                $parameters['order'],
                $this->context->cookie->vrpayecommerce_paymentBrand,
                $status,
                null,
                null,
                $template
            );
        }

        unset($this->context->cookie->vrpayecommerce_paymentBrand);
        unset($this->context->cookie->vrpayecommerce_refId);

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function isMultiChannel($paymentMethod)
    {
        return Configuration::get('VRPAYECOMMERCE_'.$paymentMethod.'_MULTICHANNEL');
    }

    public function getOrderVrpayecommerce($id)
    {
        $query = "SELECT * FROM `vrpayecommerce_order_ref` WHERE `id_order` = '".(int)$id."' ";
        $OrderVrpayecommerce = Db::getInstance()->getRow($query);

        return $OrderVrpayecommerce;
    }

    public function getCredentials($paymentMethod)
    {
        $credentials = array();
        $bearerToken = Configuration::get('VRPAYECOMMERCE_GENERAL_BEARER');
        if(!empty($bearerToken))
        {
            $credentials['bearer'] = $bearerToken;
        }
        else
        {
            $credentials['login']       = Configuration::get('VRPAYECOMMERCE_GENERAL_LOGIN');
            $credentials['password']    = Configuration::get('VRPAYECOMMERCE_GENERAL_PASSWORD');
        }
        $credentials['channel_id']  = Configuration::get('VRPAYECOMMERCE_'.$paymentMethod.'_CHANNEL');
        $credentials['server_mode'] = $this->getServerMode($paymentMethod);

        return $credentials;
    }

    public function getServerMode($paymentMethod)
    {
        return Configuration::get('VRPAYECOMMERCE_'.$paymentMethod.'_SERVER');
    }

    public function getTestMode($paymentMethod)
    {
        if ($this->getServerMode($paymentMethod) == "LIVE") {
            return false;
        }

        if ($paymentMethod == 'GIROPAY') {
            return 'INTERNAL';
        } else {
            return "EXTERNAL";
        }
    }

    public function isPaymentRecurring($paymentMethod)
    {
        switch ($paymentMethod) {
            case 'CCSAVED':
            case 'DDSAVED':
            case 'PAYPALSAVED':
                return true;
            default:
                return false;
        }
    }

    /**
     * Get merchant location
     *
     * @return array
     */
    public function getMerchantLocation()
    {
        return Configuration::get('VRPAYECOMMERCE_GENERAL_MERCHANTLOCATION');
    }

    /**
     * get the version tracker data
     * @param  string $paymentMethod
     * @return array
     */
    public function getVersionData($paymentMethod)
    {
        return array_merge(
            $this->getGeneralVersionData($paymentMethod),
            $this->getCreditCardVersionData($paymentMethod)
        );
    }

    /**
     * get general version data
     * @param  string $paymentMethod
     * @return array
     */
    public function getGeneralVersionData($paymentMethod)
    {
        $versionData = array();
        $versionData['transaction_mode'] = $this->getServerMode($paymentMethod);
        $versionData['ip_address'] = $_SERVER['SERVER_ADDR'];
        $versionData['shop_version'] = _PS_VERSION_;
        $versionData['plugin_version'] = $this->version;
        $versionData['client'] = 'CardProcess';
        $versionData['email'] = Configuration::get('VRPAYECOMMERCE_GENERAL_MERCHANTEMAIL');
        $versionData['merchant_id'] = Configuration::get('VRPAYECOMMERCE_GENERAL_MERCHANTNO');
        $versionData['shop_system'] = 'Prestashop';
        $versionData['shop_url'] = Configuration::get('VRPAYECOMMERCE_GENERAL_SHOPURL');

        return $versionData;
    }

    /**
     * Get credit card version data
     * @param  string $paymentMethod
     * @return array
     */
    public function getCreditCardVersionData($paymentMethod)
    {
        $versionData = array();
        if ($paymentMethod == 'CC' || $paymentMethod == 'CCSAVED') {
            $versionData['merchant_location'] = $this->getMerchantLocation();
        }

        return $versionData;
    }

    public function doBackendTransaction($id, $paymentType, &$errorMessage)
    {
        $result = $this->getOrderVrpayecommerce($id);
        $paymentMethod = $result['payment_method'];

        if ($result['payment_code'] == $paymentType) {
            return true;
        } else {
            $referenceId = $result['ref_id'];
            $transactionData =  $this->getCredentials($paymentMethod);

            if ($this->isMultiChannel($paymentMethod)) {
                $transactionData['channel_id'] = Configuration::get('VRPAYECOMMERCE_'.$paymentMethod.'_CHANNELMOTO');
            }

            $transactionData['currency'] = $result['currency'];
            $transactionData['amount'] = VRpayecommercePaymentCore::setNumberFormat($result['amount']);
            $transactionData['test_mode'] = $this->getTestMode($paymentMethod);
            $transactionData['payment_type'] = $paymentType;

            if ($paymentType === 'RF' && $paymentMethod === 'ENTERPAY')
            {
                $transactionData['customParameters'] = ['refundType' => 'fullRefund'];
            }

            $response = VRpayecommercePaymentCore::backOfficeOperation($referenceId, $transactionData);

            if ($response['is_valid']) {
                $returnCode = $response['response']['result']['code'];
                $transactionResult = VRpayecommercePaymentCore::getTransactionResult($returnCode);

                if ($transactionResult == 'ACK') {
                    $sql = "UPDATE vrpayecommerce_order_ref
                    SET `payment_code` = '".$paymentType."' WHERE `id_order` = '" .(int) $id. "' ";
                    if (!Db::getInstance()->execute($sql)) {
                        die('Erreur etc.');
                    }
                    return true;
                } else {
                    return false;
                }
            } else {
                $errorMessage = $response['response'];
                return false;
            }
        }
    }

    public function isRegistrationExist($referenceId)
    {
        $query = "SELECT * FROM `vrpayecommerce_payment_recurring` WHERE `ref_id`= '".pSQL($referenceId)."'";
        $registrationsData = Db::getInstance()->ExecuteS($query);

        return $registrationsData;
    }

    public function hookUpdateOrderStatus($params)
    {

        $order = new Order((int)($params['id_order']));

        //update order status from pre authorization to payment accepted
        if ($order->module == "vrpayecommerce"
            && $order->current_state == Configuration::get('VRPAYECOMMERCE_PAYMENT_STATUS_PA')
            && $params['newOrderStatus']->id==Configuration::get('PS_OS_PAYMENT')
        ) {
            $errorMessage = '';
            $result = $this->doBackendTransaction((int)$params['id_order'], "CP", $errorMessage);

            if ($result) {
                $status = 'CONFIRMED';
                $template = 'order_confirmed';
                $this->mailAlert($order, $order->payment, $status, null, null, $template);
                $this->context->cookie->vrpayecommerce_status_capture = 'true';
            } else {
                if ($errorMessage) {
                    $this->context->cookie->vrpayecommerce_status_capture = $errorMessage;
                } else {
                    $this->context->cookie->vrpayecommerce_status_capture = 'false';
                }
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminOrders').'&vieworder&id_order='.$params['id_order']
                );
            }
        }

        //update order status from payment accepted to refund
        if ($order->module == "vrpayecommerce"
            && $order->current_state == Configuration::get('PS_OS_PAYMENT')
            && $params['newOrderStatus']->id == Configuration::get('PS_OS_REFUND')
        ) {
            $result = $this->doBackendTransaction((int)$params['id_order'], "RF", $errorMessage);

            if ($result) {
                $status = 'REFUND';
                $template = 'order_refund';
                $this->mailAlert($order, $order->payment, $status, null, null, $template);
                $this->context->cookie->vrpayecommerce_status_refund = 'true';
            } else {
                if ($errorMessage) {
                    $this->context->cookie->vrpayecommerce_status_capture = $errorMessage;
                } else {
                    $this->context->cookie->vrpayecommerce_status_capture = 'false';
                }
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminOrders').'&vieworder&id_order='.$params['id_order']
                );
            }
        }
    }

    private function validateConfigurationPost()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $isRequired = false;
            $fieldsRequired = array();

            if (trim(Tools::getValue('VRPAYECOMMERCE_GENERAL_MERCHANTEMAIL'))=='') {
                if ($this->l('BACKEND_GENERAL_MERCHANTEMAIL') == "BACKEND_GENERAL_MERCHANTEMAIL") {
                    $fieldsRequired[] = "Merchant Email";
                } else {
                    $fieldsRequired[] = $this->l('BACKEND_GENERAL_MERCHANTEMAIL');
                }
                $isRequired = true;
            }
            if (trim(Tools::getValue('VRPAYECOMMERCE_GENERAL_MERCHANTNO'))=='') {
                if ($this->l('BACKEND_GENERAL_MERCHANTNO') == "BACKEND_GENERAL_MERCHANTNO") {
                    $fieldsRequired[] = "Merchant No. (VR pay)";
                } else {
                    $fieldsRequired[] = $this->l('BACKEND_GENERAL_MERCHANTNO');
                }
                $isRequired = true;
            }
            if (trim(Tools::getValue('VRPAYECOMMERCE_GENERAL_SHOPURL'))=='') {
                if ($this->l('BACKEND_GENERAL_SHOPURL') == "BACKEND_GENERAL_SHOPURL") {
                    $fieldsRequired[] = "Shop URL" ;
                } else {
                    $fieldsRequired[] = $this->l('BACKEND_GENERAL_SHOPURL');
                }
                $isRequired = true;
            }
            if (trim(Tools::getValue('VRPAYECOMMERCE_GENERAL_MERCHANTLOCATION'))=='') {
                if ($this->l('BACKEND_GENERAL_MERCHANT_LOCATION_TITLE') == "BACKEND_GENERAL_MERCHANT_LOCATION_TITLE") {
                    $fieldsRequired[] = "Merchant Location" ;
                } else {
                    $fieldsRequired[] = $this->l('BACKEND_GENERAL_MERCHANT_LOCATION_TITLE');
                }
                $isRequired = true;
            }
            if (trim(Tools::getValue('VRPAYECOMMERCE_PAYDIREKT_MINIMUM_AGE'))=='') {
                if ($this->l('BACKEND_CH_MINIMUM_AGE') == "BACKEND_CH_MINIMUM_AGE") {
                    $fieldsRequired[] = "Minimum Age";
                } else {
                    $fieldsRequired[] =  $this->l('BACKEND_CH_MINIMUM_AGE');
                }
                $isRequired = true;
            }

            if ($isRequired) {
                $warning = implode(', ', $fieldsRequired).' ';
                if ($this->l('ERROR_MANDATORY') == "ERROR_MANDATORY") {
                    $warning .= "is required. please fill out this field";
                } else {
                    $warning .= $this->l('ERROR_MANDATORY');
                }
                $this->html .= $this->displayWarning($warning);
            } else {
                $pClassParameters = $this->getPClassParameters();
                $pClasses = VRpayecommercePaymentKlarna::getPClasses($pClassParameters);

                $pClassData = array();
                if ($pClasses) {
                    if (isset($pClasses->params->param->value->array->data->value->array->data)) {
                        $pClassData = $pClasses->params->param->value->array->data->value->array->data;
                    }
                }

                $this->setPClassPostValue($pClassData);
                $this->configurationPost();
            }
        }
    }

    private function configurationPost()
    {
        Configuration::updateValue(
            'VRPAYECOMMERCE_GENERAL_BEARER',
            Tools::getValue('VRPAYECOMMERCE_GENERAL_BEARER')
        );
        Configuration::updateValue(
            'VRPAYECOMMERCE_GENERAL_LOGIN',
            Tools::getValue('VRPAYECOMMERCE_GENERAL_LOGIN')
        );
        Configuration::updateValue(
            'VRPAYECOMMERCE_GENERAL_PASSWORD',
            Tools::getValue('VRPAYECOMMERCE_GENERAL_PASSWORD')
        );
        Configuration::updateValue(
            'VRPAYECOMMERCE_GENERAL_RECURRING',
            Tools::getValue('VRPAYECOMMERCE_GENERAL_RECURRING')
        );
        Configuration::updateValue(
            'VRPAYECOMMERCE_GENERAL_MERCHANTEMAIL',
            Tools::getValue('VRPAYECOMMERCE_GENERAL_MERCHANTEMAIL')
        );
        Configuration::updateValue(
            'VRPAYECOMMERCE_GENERAL_MERCHANTNO',
            Tools::getValue('VRPAYECOMMERCE_GENERAL_MERCHANTNO')
        );
        Configuration::updateValue(
            'VRPAYECOMMERCE_GENERAL_SHOPURL',
            Tools::getValue('VRPAYECOMMERCE_GENERAL_SHOPURL')
        );
        Configuration::updateValue(
            'VRPAYECOMMERCE_GENERAL_VERSION_TRACKER',
            Tools::getValue('VRPAYECOMMERCE_GENERAL_VERSION_TRACKER')
        );
        Configuration::updateValue(
            'VRPAYECOMMERCE_GENERAL_MERCHANTLOCATION',
            Tools::getValue('VRPAYECOMMERCE_GENERAL_MERCHANTLOCATION')
        );

        foreach ($this->payment_methods as $value) {
            Configuration::updateValue(
                'VRPAYECOMMERCE_'.$value.'_ACTIVE',
                Tools::getValue('VRPAYECOMMERCE_'.$value.'_ACTIVE')
            );
            Configuration::updateValue(
                'VRPAYECOMMERCE_'.$value.'_SERVER',
                Tools::getValue('VRPAYECOMMERCE_'.$value.'_SERVER')
            );

            if ($value == "CC" || $value == "CCSAVED" || $value == "DC") {
                $multiSelect = '';
                $cards = Tools::getValue('VRPAYECOMMERCE_'.$value.'_CARDS');
                if (is_array($cards)) {
                    $multiSelect = implode(",", $cards);
                }
                Configuration::updateValue(
                    'VRPAYECOMMERCE_'.$value.'_CARDS',
                    $multiSelect
                );
            }

            if (in_array($value, $this->db_pa_payment_methods)) {
                Configuration::updateValue(
                    'VRPAYECOMMERCE_'.$value.'_MODE',
                    Tools::getValue('VRPAYECOMMERCE_'.$value.'_MODE')
                );
            }

            if ($value == "CCSAVED" || $value == "DDSAVED" || $value == "PAYPALSAVED") {
                Configuration::updateValue(
                    'VRPAYECOMMERCE_'.$value.'_AMOUNT',
                    Tools::getValue('VRPAYECOMMERCE_'.$value.'_AMOUNT')
                );
            }

            if ($value == "PAYDIREKT") {
                Configuration::updateValue(
                    'VRPAYECOMMERCE_'.$value.'_MINIMUM_AGE',
                    Tools::getValue('VRPAYECOMMERCE_'.$value.'_MINIMUM_AGE')
                );
                Configuration::updateValue(
                    'VRPAYECOMMERCE_'.$value.'_PAYMENT_IS_PARTIAL',
                    Tools::getValue('VRPAYECOMMERCE_'.$value.'_PAYMENT_IS_PARTIAL')
                );
            }

            if ($value == "CCSAVED") {
                Configuration::updateValue(
                    'VRPAYECOMMERCE_'.$value.'_MULTICHANNEL',
                    Tools::getValue('VRPAYECOMMERCE_'.$value.'_MULTICHANNEL')
                );
            }

            Configuration::updateValue(
                'VRPAYECOMMERCE_'.$value.'_CHANNEL',
                Tools::getValue('VRPAYECOMMERCE_'.$value.'_CHANNEL')
            );

            if ($value == "CCSAVED") {
                Configuration::updateValue(
                    'VRPAYECOMMERCE_'.$value.'_CHANNELMOTO',
                    Tools::getValue('VRPAYECOMMERCE_'.$value.'_CHANNELMOTO')
                );
            }

            if ($value == "KLARNASLICEIT") {
                foreach ($this->klarnasliceitConfig as $config) {
                    Configuration::updateValue(
                        'VRPAYECOMMERCE_'.$value.'_'.$config,
                        Tools::getValue('VRPAYECOMMERCE_'.$value.'_'.$config)
                    );
                }
            }

            if ($value == "KLARNAPAYLATER") {
                Configuration::updateValue(
                    'VRPAYECOMMERCE_'.$value.'_MERCHANT_ID',
                    Tools::getValue('VRPAYECOMMERCE_'.$value.'_MERCHANT_ID')
                );
            }

            Configuration::updateValue(
                'VRPAYECOMMERCE_'.$value.'_SORT',
                Tools::getValue('VRPAYECOMMERCE_'.$value.'_SORT')
            );
        }

        $chUpdated = $this->l('BACKEND_CH_UPDATED') == "BACKEND_CH_UPDATED" ? "Debit" : $this->l('BACKEND_CH_UPDATED');
        $this->html .= $this->displayConfirmation($chUpdated);
    }

    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->validateConfigurationPost();
        } elseif (Configuration::get('VRPAYECOMMERCE_POPUP')) {
            $popUpInfo = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/popUpInfo.tpl');
            $this->html .= $popUpInfo;
            Configuration::updateValue('VRPAYECOMMERCE_POPUP', false);
        } else {
            $this->html .= '<br />';
        }

        $this->html .= $this->renderForm();

        return $this->html;
    }

    public function renderForm()
    {
        $locale = $this->getLocaleForm();

        $generalForm = $this->getGeneralForm($locale);
        $paymentForm = $this->getPaymentForm($locale);
        $fieldsForm = array_merge($generalForm, $paymentForm);

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        if (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')) {
            $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        } else {
            $helper->allow_employee_form_lang = 0;
        }
        $this->fields_form = array();
        $this->fields_form = $fieldsForm;
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink(
            'AdminModules',
            false
        ).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm($this->fields_form);
    }

    private function getLocaleForm()
    {
        $locale = array();
        if ($this->l('BACKEND_CH_BEARER') == "BACKEND_CH_BEARER") {
            $locale['bearer']['label'] = "Auth-Token";
        } else {
            $locale['bearer']['label'] = $this->l('BACKEND_CH_BEARER');
        }
        if ($this->l('BACKEND_CH_LOGIN') == "BACKEND_CH_LOGIN") {
            $locale['login']['label'] = "User-ID";
        } else {
            $locale['login']['label'] = $this->l('BACKEND_CH_LOGIN');
        }
        if ($this->l('BACKEND_CH_PASSWORD') == "BACKEND_CH_PASSWORD") {
            $locale['password']['label'] = "Password";
        } else {
            $locale['password']['label'] = $this->l('BACKEND_CH_PASSWORD');
        }
        if ($this->l('BACKEND_GENERAL_RECURRING') == "BACKEND_GENERAL_RECURRING") {
            $locale['recurring']['label'] = "Recurring";
        } else {
            $locale['recurring']['label'] = $this->l('BACKEND_GENERAL_RECURRING');
        }
        if ($this->l('BACKEND_GENERAL_MERCHANTEMAIL') == "BACKEND_GENERAL_MERCHANTEMAIL") {
            $locale['merchantemail']['label'] = "Merchant Email";
        } else {
            $locale['merchantemail']['label'] = $this->l('BACKEND_GENERAL_MERCHANTEMAIL');
        }
        if ($this->l('BACKEND_GENERAL_MERCHANTNO') == "BACKEND_GENERAL_MERCHANTNO") {
            $locale['merchantno']['label'] = "Merchant No. (VR pay)";
        } else {
            $locale['merchantno']['label'] = $this->l('BACKEND_GENERAL_MERCHANTNO');
        }
        if ($this->l('BACKEND_TT_MERCHANT_ID') == "BACKEND_TT_MERCHANT_ID") {
            $locale['merchantno']['desc'] = "Your Customer ID from VR pay.";
        } else {
            $locale['merchantno']['desc'] = $this->l('BACKEND_TT_MERCHANT_ID');
        }
        if ($this->l('BACKEND_GENERAL_SHOPURL') == "BACKEND_GENERAL_SHOPURL") {
            $locale['shopurl']['label'] = "Shop URL";
        } else {
            $locale['shopurl']['label'] =  $this->l('BACKEND_GENERAL_SHOPURL');
        }
        if ($this->l('BACKEND_GENERAL_VERSION_TRACKER') == "BACKEND_GENERAL_VERSION_TRACKER") {
            $locale['versionTracker']['label'] = "Version Tracker";
        } else {
            $locale['versionTracker']['label'] =  $this->l('BACKEND_GENERAL_VERSION_TRACKER');
        }
        if ($this->l('BACKEND_TT_VERSION_TRACKER') == "BACKEND_TT_VERSION_TRACKER") {
            $locale['versionTracker']['desc'] =
                " When enabled, you accept to share your IP, email address, etc with Cardprocess.";
        } else {
            $locale['versionTracker']['desc'] = $this->l('BACKEND_TT_VERSION_TRACKER');
        }
        if ($this->l('BACKEND_PM_CC') == "BACKEND_PM_CC") {
            $locale['cc'] = "Credit Cards";
        } else {
            $locale['cc'] = $this->l('BACKEND_PM_CC');
        }
        if ($this->l('BACKEND_CH_GENERAL') == "BACKEND_CH_GENERAL") {
            $locale['general'] = "General Setting";
        } else {
            $locale['general'] = $this->l('BACKEND_CH_GENERAL');
        }
        if ($this->l('BACKEND_PM_CCSAVED') == "BACKEND_PM_CCSAVED") {
            $locale['ccsaved'] = "Credit Cards (Recurring)";
        } else {
            $locale['ccsaved'] = $this->l('BACKEND_PM_CCSAVED');
        }
        if ($this->l('BACKEND_PM_DC') == "BACKEND_PM_DC") {
            $locale['dc'] = "Debit Cards";
        } else {
            $locale['dc'] = $this->l('BACKEND_PM_DC');
        }
        if ($this->l('BACKEND_PM_DD') == "BACKEND_PM_DD") {
            $locale['dd'] = "Direct Debit";
        } else {
            $locale['dd'] = $this->l('BACKEND_PM_DD');
        }
        if ($this->l('BACKEND_PM_DDSAVED') == "BACKEND_PM_DDSAVED") {
            $locale['ddsaved'] = "Direct Debit (Recurring)";
        } else {
            $locale['ddsaved'] = $this->l('BACKEND_PM_DDSAVED');
        }
        if ($this->l('BACKEND_PM_EPS') == "BACKEND_PM_EPS") {
            $locale['eps'] = "eps";
        } else {
            $locale['eps'] = $this->l('BACKEND_PM_EPS');
        }
        if ($this->l('BACKEND_PM_GIROPAY') == "BACKEND_PM_GIROPAY") {
            $locale['giropay'] = "Giropay";
        } else {
            $locale['giropay'] = $this->l('BACKEND_PM_GIROPAY');
        }
        if ($this->l('BACKEND_PM_IDEAL') == "BACKEND_PM_IDEAL") {
            $locale['ideal'] = "iDeal";
        } else {
            $locale['ideal'] = $this->l('BACKEND_PM_IDEAL');
        }
        if ($this->l('BACKEND_PM_PAYPAL') == "BACKEND_PM_PAYPAL") {
            $locale['paypal'] = "PayPal";
        } else {
            $locale['paypal'] = $this->l('BACKEND_PM_PAYPAL');
        }
        if ($this->l('BACKEND_PM_PAYDIREKT') == "BACKEND_PM_PAYDIREKT") {
            $locale['paydirekt'] = "paydirekt";
        } else {
            $locale['paydirekt'] = $this->l('BACKEND_PM_PAYDIREKT');
        }
        if ($this->l('BACKEND_PM_EASYCREDIT') == "EASYCREDIT") {
            $locale['easycredit'] = "ratenkauf by easyCredit";
        } else {
            $locale['easycredit'] = $this->l('BACKEND_PM_EASYCREDIT');
        }
        if ($this->l('BACKEND_PM_ENTERPAY') == "BACKEND_PM_ENTERPAY") {
            $locale['enterpay'] = "Kauf auf Rechnung TEBA Pay";
        } else {
            $locale['enterpay'] = $this->l('BACKEND_PM_ENTERPAY');
        }
        if ($this->l('BACKEND_PM_PAYPALSAVED') == "BACKEND_PM_PAYPALSAVED") {
            $locale['paypalsaved'] = "PayPal (Recurring)";
        } else {
            $locale['paypalsaved'] = $this->l('BACKEND_PM_PAYPALSAVED');
        }
        if ($this->l('BACKEND_PM_KLARNAOBT') == "BACKEND_PM_KLARNAOBT") {
            $locale['klarnaobt'] = "Online Bank Transfer.";
        } else {
            $locale['klarnaobt'] = $this->l('BACKEND_PM_KLARNAOBT');
        }
        if ($this->l('BACKEND_PM_KLARNAPAYLATER') == "BACKEND_PM_KLARNAPAYLATER") {
            $locale['klarnapaylater'] = "Pay later.";
        } else {
            $locale['klarnapaylater'] = $this->l('BACKEND_PM_KLARNAPAYLATER');
        }
        if ($this->l('BACKEND_PM_KLARNASLICEIT') == "BACKEND_PM_KLARNASLICEIT") {
            $locale['klarnasliceit'] = "Slice it.";
        } else {
            $locale['klarnasliceit'] = $this->l('BACKEND_PM_KLARNASLICEIT');
        }

        if ($this->l('BACKEND_CH_SERVER') == "BACKEND_CH_SERVER") {
            $locale['server']['label'] = "Server";
        } else {
            $locale['server']['label'] = $this->l('BACKEND_CH_SERVER');
        }
        if ($this->l('BACKEND_CH_MODE_TEST') == "BACKEND_CH_MODE_TEST") {
            $locale['server']['test'] = "TEST";
        } else {
            $locale['server']['test'] = $this->l('BACKEND_CH_MODE_TEST');
        }
        if ($this->l('BACKEND_CH_MODE_LIVE') == "BACKEND_CH_MODE_LIVE") {
            $locale['server']['live'] = "LIVE";
        } else {
            $locale['server']['live'] = $this->l('BACKEND_CH_MODE_LIVE');
        }

        if ($this->l('BACKEND_CH_MODE') == "BACKEND_CH_MODE") {
            $locale['mode']['label'] = "Transaction-Mode";
        } else {
            $locale['mode']['label'] = $this->l('BACKEND_CH_MODE');
        }
        if ($this->l('BACKEND_CH_MODEDEBIT') == "BACKEND_CH_MODEDEBIT") {
            $locale['mode']['db'] = "Debit";
        } else {
            $locale['mode']['db'] = $this->l('BACKEND_CH_MODEDEBIT');
        }
        if ($this->l('BACKEND_CH_MODEPREAUTH') == "BACKEND_CH_MODEPREAUTH") {
            $locale['mode']['pa'] = "Pre-Authorization";
        } else {
            $locale['mode']['pa'] = $this->l('BACKEND_CH_MODEPREAUTH');
        }

        if ($this->l('BACKEND_CH_CARDS') == "BACKEND_CH_CARDS") {
            $locale['cardtype']['label'] = "Cards Types";
        } else {
            $locale['cardtype']['label'] = $this->l('BACKEND_CH_CARDS');
        }
        if ($this->l('BACKEND_CC_VISA') == "BACKEND_CC_VISA") {
            $locale['cardtype']['visa'] = "VISA";
        } else {
            $locale['cardtype']['visa'] = $this->l('BACKEND_CC_VISA');
        }
        if ($this->l('BACKEND_CC_MASTER') == "BACKEND_CC_MASTER") {
            $locale['cardtype']['master'] = "MASTER";
        } else {
            $locale['cardtype']['master'] = $this->l('BACKEND_CC_MASTER');
        }
        if ($this->l('BACKEND_CC_AMEX') == "BACKEND_CC_AMEX") {
            $locale['cardtype']['amex'] = "AMEX";
        } else {
            $locale['cardtype']['amex'] = $this->l('BACKEND_CC_AMEX');
        }
        if ($this->l('BACKEND_CC_DINERS') == "BACKEND_CC_DINERS") {
            $locale['cardtype']['diners'] = "DINERS";
        } else {
            $locale['cardtype']['diners'] = $this->l('BACKEND_CC_DINERS');
        }
        if ($this->l('BACKEND_CC_JCB') == "BACKEND_CC_JCB") {
            $locale['cardtype']['jcb'] = "JCB";
        } else {
            $locale['cardtype']['jcb'] = $this->l('BACKEND_CC_JCB');
        }
        if ($this->l('BACKEND_CC_VPAY') == "BACKEND_CC_VPAY") {
            $locale['cardtype']['vpay'] = "VPAY";
        } else {
            $locale['cardtype']['vpay'] = $this->l('BACKEND_CC_VPAY');
        }
        if ($this->l('BACKEND_CC_MAESTRO') == "BACKEND_CC_MAESTRO") {
            $locale['cardtype']['maestro'] = "MAESTRO";
        } else {
            $locale['cardtype']['maestro'] = $this->l('BACKEND_CC_MAESTRO');
        }
        if ($this->l('BACKEND_CC_DANKORT') == "BACKEND_CC_DANKORT") {
            $locale['cardtype']['dankort'] = "DANKORT";
        } else {
            $locale['cardtype']['dankort'] = $this->l('BACKEND_CC_DANKORT');
        }
        if ($this->l('BACKEND_CC_VISAELECTRON') == "BACKEND_CC_VISAELECTRON") {
            $locale['cardtype']['visaelectron'] = "VISAELECTRON";
        } else {
            $locale['cardtype']['visaelectron'] = $this->l('BACKEND_CC_VISAELECTRON');
        }
        if ($this->l('BACKEND_CC_POSTEPAY') == "BACKEND_CC_POSTEPAY") {
            $locale['cardtype']['postepay'] = "POSTEPAY";
        } else {
            $locale['cardtype']['postepay'] = $this->l('BACKEND_CC_POSTEPAY');
        }

        if ($this->l('BACKEND_CH_AMOUNT') == "BACKEND_CH_AMOUNT") {
            $locale['amount']['label'] = "Amount for Registration";
        } else {
            $locale['amount']['label'] = $this->l('BACKEND_CH_AMOUNT');
        }
        if ($this->l('BACKEND_TT_REGISTRATION_AMOUNT') == "BACKEND_TT_REGISTRATION_AMOUNT") {
            $locale['amount']['desc'] = "Amount that is debited and refunded when a shopper
             registers a payment method without purchase.";
        } else {
            $locale['amount']['desc'] = $this->l('BACKEND_TT_REGISTRATION_AMOUNT');
        }

        if ($this->l('BACKEND_CH_ACTIVE') == "BACKEND_CH_ACTIVE") {
            $locale['active']['label'] = "Enabled";
        } else {
            $locale['active']['label'] = $this->l('BACKEND_CH_ACTIVE');
        }
        if ($this->l('BACKEND_CH_CHANNEL') == "BACKEND_CH_CHANNEL") {
            $locale['channel']['label'] = "Entity-ID";
        } else {
            $locale['channel']['label'] = $this->l('BACKEND_CH_CHANNEL');
        }
        if ($this->l('BACKEND_CH_MULTICHANNEL') == "BACKEND_CH_MULTICHANNEL") {
            $locale['multichannel']['label'] = "Multichannel";
        } else {
            $locale['multichannel']['label'] = $this->l('BACKEND_CH_MULTICHANNEL');
        }
        if ($this->l('BACKEND_TT_MULTICHANNEL') == "BACKEND_TT_MULTICHANNEL") {
            $locale['multichannel']['desc'] = "If activated, repeated recurring payments
             are handled by the alternative channel.";
        } else {
            $locale['multichannel']['desc'] = $this->l('BACKEND_TT_MULTICHANNEL');
        }

        if ($this->l('BACKEND_CH_MINIMUM_AGE') == "BACKEND_CH_MINIMUM_AGE") {
            $locale['minimum_age']['label'] = "Minimum Age";
        } else {
            $locale['minimum_age']['label'] = $this->l('BACKEND_CH_MINIMUM_AGE');
        }
        if ($this->l('BACKEND_CH_PAYMENT_IS_PARTIAL') == "BACKEND_CH_PAYMENT_IS_PARTIAL") {
            $locale['payment_is_partial']['label'] = "Partial Capture or Refund";
        } else {
            $locale['payment_is_partial']['label'] = $this->l('BACKEND_CH_PAYMENT_IS_PARTIAL');
        }

        if ($this->l('BACKEND_CH_CHANNELMOTO') == "BACKEND_CH_CHANNELMOTO") {
            $locale['channelmoto']['label'] = "Entity-ID MOTO";
        } else {
            $locale['channelmoto']['label'] = $this->l('BACKEND_CH_CHANNELMOTO');
        }
        if ($this->l('BACKEND_TT_CHANNEL_MOTO') == "BACKEND_TT_CHANNEL_MOTO") {
            $locale['channelmoto']['desc'] = "Alternative channel for recurring payments
             if Multichannel is activated (to bypass 3D Secure).";
        } else {
            $locale['channelmoto']['desc'] = $this->l('BACKEND_TT_CHANNEL_MOTO');
        }

        if ($this->l('BACKEND_CH_ORDER') == "BACKEND_CH_ORDER") {
            $locale['order']['label'] = "Sort Order";
        } else {
            $locale['order']['label'] = $this->l('BACKEND_CH_ORDER');
        }
        if ($this->l('BACKEND_CH_SAVE') == "BACKEND_CH_SAVE") {
            $locale['save'] = "Save";
        } else {
            $locale['save'] = $this->l('BACKEND_CH_SAVE');
        }

        if ($this->l('BACKEND_CH_KLARNAPAYLATER_MERCHANT_ID') == "BACKEND_CH_KLARNAPAYLATER_MERCHANT_ID") {
            $locale['klarnapaylater_merchant_id']['label'] = "Pay later. Merchant ID";
        } else {
            $locale['klarnapaylater_merchant_id']['label'] = $this->l('BACKEND_CH_KLARNAPAYLATER_MERCHANT_ID');
        }

        if ($this->l('BACKEND_CH_KLARNA_PCLASS') == "BACKEND_CH_KLARNA_PCLASS") {
            $locale['pclass']['label'] = "Installment Plan (PCLASS)";
        } else {
            $locale['pclass']['label'] = $this->l('BACKEND_CH_KLARNA_PCLASS');
        }
        if ($this->l('BACKEND_TT_KLARNAPCLASS') == "BACKEND_TT_KLARNAPCLASS") {
            $locale['pclass']['desc'] = "Please insert your Slice it. plan (PCLASS) here.";
        } else {
            $locale['pclass']['desc'] = $this->l('BACKEND_TT_KLARNAPCLASS');
        }

        if ($this->l('BACKEND_CH_KLARNASLICEIT_MERCHANT_ID') == "BACKEND_CH_KLARNASLICEIT_MERCHANT_ID") {
            $locale['klarnasliceit_merchant_id']['label'] = "Slice it. Merchant ID";
        } else {
            $locale['klarnasliceit_merchant_id']['label'] = $this->l('BACKEND_CH_KLARNASLICEIT_MERCHANT_ID');
        }
        if ($this->l('BACKEND_CH_KLARNA_CURRENCY') == "BACKEND_CH_KLARNA_CURRENCY") {
            $locale['klarna_currency']['label'] = "Klarna Currency";
        } else {
            $locale['klarna_currency']['label'] = $this->l('BACKEND_CH_KLARNA_CURRENCY');
        }
        if ($this->l('BACKEND_CH_KLARNA_CURRENCY_SWEDISH') == "BACKEND_CH_KLARNA_CURRENCY_SWEDISH") {
            $locale['klarna_currency']['swedish'] = "Swedish krona";
        } else {
            $locale['klarna_currency']['swedish'] = $this->l('BACKEND_CH_KLARNA_CURRENCY_SWEDISH');
        }
        if ($this->l('BACKEND_CH_KLARNA_CURRENCY_NORWEGIAN') == "BACKEND_CH_KLARNA_CURRENCY_NORWEGIAN") {
            $locale['klarna_currency']['norwegian'] = "Norwegian krona";
        } else {
            $locale['klarna_currency']['norwegian'] = $this->l('BACKEND_CH_KLARNA_CURRENCY_NORWEGIAN');
        }
        if ($this->l('BACKEND_CH_KLARNA_CURRENCY_EURO') == "BACKEND_CH_KLARNA_CURRENCY_EURO") {
            $locale['klarna_currency']['euro'] = "Euro";
        } else {
            $locale['klarna_currency']['euro'] = $this->l('BACKEND_CH_KLARNA_CURRENCY_EURO');
        }
        if ($this->l('BACKEND_CH_KLARNA_CURRENCY_DANISH') == "BACKEND_CH_KLARNA_CURRENCY_DANISH") {
            $locale['klarna_currency']['danish'] = "Danish krona";
        } else {
            $locale['klarna_currency']['danish'] = $this->l('BACKEND_CH_KLARNA_CURRENCY_DANISH');
        }
        if ($this->l('BACKEND_CH_KLARNA_COUNTRY') == "BACKEND_CH_KLARNA_COUNTRY") {
            $locale['klarna_country']['label'] = "Klarna Country";
        } else {
            $locale['klarna_country']['label'] = $this->l('BACKEND_CH_KLARNA_COUNTRY');
        }
        if ($this->l('BACKEND_CH_KLARNA_COUNTRY_AUSTRIA') == "BACKEND_CH_KLARNA_COUNTRY_AUSTRIA") {
            $locale['klarna_country']['austria'] = "Austria";
        } else {
            $locale['klarna_country']['austria'] = $this->l('BACKEND_CH_KLARNA_COUNTRY_AUSTRIA');
        }
        if ($this->l('BACKEND_CH_KLARNA_COUNTRY_DENMARK') == "BACKEND_CH_KLARNA_COUNTRY_DENMARK") {
            $locale['klarna_country']['denmark'] = "Denmark";
        } else {
            $locale['klarna_country']['denmark'] = $this->l('BACKEND_CH_KLARNA_COUNTRY_DENMARK');
        }
        if ($this->l('BACKEND_CH_KLARNA_COUNTRY_FINLAND') == "BACKEND_CH_KLARNA_COUNTRY_FINLAND") {
            $locale['klarna_country']['finland'] = "Finland";
        } else {
            $locale['klarna_country']['finland'] = $this->l('BACKEND_CH_KLARNA_COUNTRY_FINLAND');
        }
        if ($this->l('BACKEND_CH_KLARNA_COUNTRY_GERMANY') == "BACKEND_CH_KLARNA_COUNTRY_GERMANY") {
            $locale['klarna_country']['germany'] = "Germany";
        } else {
            $locale['klarna_country']['germany'] = $this->l('BACKEND_CH_KLARNA_COUNTRY_GERMANY');
        }
        if ($this->l('BACKEND_CH_KLARNA_COUNTRY_NETHERLANDS') == "BACKEND_CH_KLARNA_COUNTRY_NETHERLANDS") {
            $locale['klarna_country']['netherlands'] = "Netherlands";
        } else {
            $locale['klarna_country']['netherlands'] = $this->l('BACKEND_CH_KLARNA_COUNTRY_NETHERLANDS');
        }
        if ($this->l('BACKEND_CH_KLARNA_COUNTRY_NORWAY') == "BACKEND_CH_KLARNA_COUNTRY_NORWAY") {
            $locale['klarna_country']['norway'] = "Norway";
        } else {
            $locale['klarna_country']['norway'] = $this->l('BACKEND_CH_KLARNA_COUNTRY_NORWAY');
        }
        if ($this->l('BACKEND_CH_KLARNA_COUNTRY_SWEDEN') == "BACKEND_CH_KLARNA_COUNTRY_SWEDEN") {
            $locale['klarna_country']['sweden'] = "Sweden";
        } else {
            $locale['klarna_country']['sweden'] = $this->l('BACKEND_CH_KLARNA_COUNTRY_SWEDEN');
        }
        if ($this->l('BACKEND_CH_KLARNA_LANGUAGE') == "BACKEND_CH_KLARNA_LANGUAGE") {
            $locale['klarna_language']['label'] = "Klarna Language";
        } else {
            $locale['klarna_language']['label'] = $this->l('BACKEND_CH_KLARNA_LANGUAGE');
        }
        if ($this->l('BACKEND_CH_KLARNA_LANGUAGE_DANISH') == "BACKEND_CH_KLARNA_LANGUAGE_DANISH") {
            $locale['klarna_language']['danish'] = "Danish";
        } else {
            $locale['klarna_language']['danish'] = $this->l('BACKEND_CH_KLARNA_LANGUAGE_DANISH');
        }
        if ($this->l('BACKEND_CH_KLARNA_LANGUAGE_AUSTRIA') == "BACKEND_CH_KLARNA_LANGUAGE_AUSTRIA") {
            $locale['klarna_language']['austria'] = "Austrian";
        } else {
            $locale['klarna_language']['austria'] = $this->l('BACKEND_CH_KLARNA_LANGUAGE_AUSTRIA');
        }
        if ($this->l('BACKEND_CH_KLARNA_LANGUAGE_GERMAN') == "BACKEND_CH_KLARNA_LANGUAGE_GERMAN") {
            $locale['klarna_language']['german'] = "German";
        } else {
            $locale['klarna_language']['german'] = $this->l('BACKEND_CH_KLARNA_LANGUAGE_GERMAN');
        }
        if ($this->l('BACKEND_CH_KLARNA_LANGUAGE_FINNISH') == "BACKEND_CH_KLARNA_LANGUAGE_FINNISH") {
            $locale['klarna_language']['finnish'] = "Finnish";
        } else {
            $locale['klarna_language']['finnish'] = $this->l('BACKEND_CH_KLARNA_LANGUAGE_FINNISH');
        }
        if ($this->l('BACKEND_CH_KLARNA_LANGUAGE_NORWEGIAN') == "BACKEND_CH_KLARNA_LANGUAGE_NORWEGIAN") {
            $locale['klarna_language']['norwegian'] = "Norwegian";
        } else {
            $locale['klarna_language']['norwegian'] = $this->l('BACKEND_CH_KLARNA_LANGUAGE_NORWEGIAN');
        }
        if ($this->l('BACKEND_CH_KLARNA_LANGUAGE_DUTCH') == "BACKEND_CH_KLARNA_LANGUAGE_DUTCH") {
            $locale['klarna_language']['dutch'] = "Dutch";
        } else {
            $locale['klarna_language']['dutch'] = $this->l('BACKEND_CH_KLARNA_LANGUAGE_DUTCH');
        }
        if ($this->l('BACKEND_CH_KLARNA_LANGUAGE_SWEDISH') == "BACKEND_CH_KLARNA_LANGUAGE_SWEDISH") {
            $locale['klarna_language']['swedish'] = "Swedish";
        } else {
            $locale['klarna_language']['swedish'] = $this->l('BACKEND_CH_KLARNA_LANGUAGE_SWEDISH');
        }
        if ($this->l('BACKEND_CH_KLARNA_SHARED_SECRET') == "BACKEND_CH_KLARNA_SHARED_SECRET") {
            $locale['klarna_shared_secret']['label'] = "Klarna Shared Secret";
        } else {
            $locale['klarna_shared_secret']['label'] = $this->l('BACKEND_CH_KLARNA_SHARED_SECRET');
        }
        if ($this->l('BACKEND_CH_KLARNA_PCLASS_ID') == "BACKEND_CH_KLARNA_PCLASS_ID") {
            $locale['klarna_pclass_id']['label'] = "PClass ID";
        } else {
            $locale['klarna_pclass_id']['label'] = $this->l('BACKEND_CH_KLARNA_PCLASS_ID');
        }
        if ($this->l('BACKEND_CH_KLARNA_PCLASS_DESC') == "BACKEND_CH_KLARNA_PCLASS_DESC") {
            $locale['klarna_pclass_desc']['label'] = "PClass Description";
        } else {
            $locale['klarna_pclass_desc']['label'] = $this->l('BACKEND_CH_KLARNA_PCLASS_DESC');
        }
        if ($this->l('BACKEND_CH_KLARNA_PCLASS_AMOUNTOFMONTHS') == "BACKEND_CH_KLARNA_PCLASS_AMOUNTOFMONTHS") {
            $locale['klarna_pclass_amountofmonths']['label'] = "PClass Amount of Months";
        } else {
            $locale['klarna_pclass_amountofmonths']['label'] = $this->l('BACKEND_CH_KLARNA_PCLASS_AMOUNTOFMONTHS');
        }
        if ($this->l('BACKEND_CH_KLARNA_PCLASS_STARTFEE') == "BACKEND_CH_KLARNA_PCLASS_STARTFEE") {
            $locale['klarna_pclass_startfee']['label'] = "PClass Start Fee";
        } else {
            $locale['klarna_pclass_startfee']['label'] = $this->l('BACKEND_CH_KLARNA_PCLASS_STARTFEE');
        }
        if ($this->l('BACKEND_CH_KLARNA_PCLASS_INVOICEFEE') == "BACKEND_CH_KLARNA_PCLASS_INVOICEFEE") {
            $locale['klarna_pclass_invoicefee']['label'] = "PClass Invoice Fee";
        } else {
            $locale['klarna_pclass_invoicefee']['label'] = $this->l('BACKEND_CH_KLARNA_PCLASS_INVOICEFEE');
        }
        if ($this->l('BACKEND_CH_KLARNA_PCLASS_INTERESTRATE') == "BACKEND_CH_KLARNA_PCLASS_INTERESTRATE") {
            $locale['klarna_pclass_interestrate']['label'] = "PClass Interest Rate";
        } else {
            $locale['klarna_pclass_interestrate']['label'] = $this->l('BACKEND_CH_KLARNA_PCLASS_INTERESTRATE');
        }
        if ($this->l('BACKEND_CH_KLARNA_PCLASS_MINPURCHASE') == "BACKEND_CH_KLARNA_PCLASS_MINPURCHASE") {
            $locale['klarna_pclass_minpurchase']['label'] = "PClass Mininum Purchase";
        } else {
            $locale['klarna_pclass_minpurchase']['label'] = $this->l('BACKEND_CH_KLARNA_PCLASS_MINPURCHASE');
        }
        if ($this->l('BACKEND_CH_KLARNA_PCLASS_COUNTRY') == "BACKEND_CH_KLARNA_PCLASS_COUNTRY") {
            $locale['klarna_pclass_country']['label'] = "PClass Country";
        } else {
            $locale['klarna_pclass_country']['label'] = $this->l('BACKEND_CH_KLARNA_PCLASS_COUNTRY');
        }
        if ($this->l('BACKEND_CH_KLARNA_PCLASS_TYPE') == "BACKEND_CH_KLARNA_PCLASS_TYPE") {
            $locale['klarna_pclass_type']['label'] = "PClass Type";
        } else {
            $locale['klarna_pclass_type']['label'] = $this->l('BACKEND_CH_KLARNA_PCLASS_TYPE');
        }
        if ($this->l('BACKEND_CH_KLARNA_PCLASS_EXPIRYDATE') == "BACKEND_CH_KLARNA_PCLASS_EXPIRYDATE") {
            $locale['klarna_pclass_expirydate']['label'] = "PClass Expiry Date";
        } else {
            $locale['klarna_pclass_expirydate']['label'] = $this->l('BACKEND_CH_KLARNA_PCLASS_EXPIRYDATE');
        }
        if ($this->l('BACKEND_CH_KLARNA_PCLASS') == "BACKEND_CH_KLARNA_PCLASS") {
            $locale['pclass']['label'] = "Installment Plan (PCLASS)";
        } else {
            $locale['pclass']['label'] = $this->l('BACKEND_CH_KLARNA_PCLASS');
        }
        if ($this->l('BACKEND_TT_KLARNAPCLASS') == "BACKEND_TT_KLARNAPCLASS") {
            $locale['pclass']['desc'] = "Please insert your Slice it. plan (PCLASS) here.";
        } else {
            $locale['pclass']['desc'] = $this->l('BACKEND_TT_KLARNAPCLASS');
        }
        if ($this->l('BACKEND_GENERAL_MERCHANT_LOCATION_TITLE') == "BACKEND_GENERAL_MERCHANT_LOCATION_TITLE") {
            $locale['merchantLocation']['label'] = "Merchant Location";
        } else {
            $locale['merchantLocation']['label'] =  $this->l('BACKEND_GENERAL_MERCHANT_LOCATION_TITLE');
        }
        if ($this->l('BACKEND_GENERAL_MERCHANT_LOCATION_DESC') == "BACKEND_GENERAL_MERCHANT_LOCATION_DESC") {
            $locale['merchantLocation']['desc'] =
            "Principal place of business (Company Name, Address including the Country)";
        } else {
            $locale['merchantLocation']['desc'] = $this->l('BACKEND_GENERAL_MERCHANT_LOCATION_DESC');
        }
        if ($this->l('FRONTEND_MERCHANT_LOCATION_DESC') == "FRONTEND_MERCHANT_LOCATION_DESC") {
            $locale['frontendMerchantLocation']['desc'] =
            "Principal place of business (Company Name, Address including the Country)";
        } else {
            $locale['frontendMerchantLocation']['desc'] = $this->l('FRONTEND_MERCHANT_LOCATION_DESC');
        }

        return $locale;
    }

    private function getPaymentForm($locale)
    {
        $paymentForm = array();
        foreach ($this->payment_methods as $key => $value) {
            $paymentName = Tools::strtolower($value);
            $paymentForm[] = array(
                'form' => array(
                    'legend' => array(
                        'title' => $locale["$paymentName"]
                    ),
                    'input' => array(
                        $this->getSwitchForm(
                            $value.'_ACTIVE',
                            $locale['active'],
                            $this->getSwitchLIst('active')
                        ),
                        $this->getSelectForm(
                            $value.'_SERVER',
                            $locale['server'],
                            $this->getServerLIst(
                                $locale['server']['test'],
                                $locale['server']['live']
                            )
                        ),
                        $this->getTextForm(
                            $value.'_CHANNEL',
                            $locale['channel']
                        ),
                        $this->getTextForm(
                            $value.'_SORT',
                            $locale['order']
                        ),
                    ),
                    'submit' => array(
                        'title' => $locale['save']
                    )
                )
            );

            if ($value == "CCSAVED" || $value == "DDSAVED" || $value == "PAYPALSAVED") {
                $addRegisterAmount = array ($this->getTextForm($value.'_AMOUNT', $locale['amount']));
                array_splice($paymentForm[$key]['form']['input'], 2, 0, $addRegisterAmount);
            }

            if (in_array($value, $this->db_pa_payment_methods)) {
                $transModeList = $this->getTransactionModeList($locale['mode']['db'], $locale['mode']['pa']);
                $addTransactionMode = array ($this->getSelectForm($value.'_MODE', $locale['mode'], $transModeList));
                array_splice($paymentForm[$key]['form']['input'], 2, 0, $addTransactionMode);
            }

            if ($value == "CC" || $value == "CCSAVED" || $value == "DC") {
                if ($value == "DC") {
                    $cardTypesList = $this->getDCTypesList($locale['cardtype']);
                } else {
                    $cardTypesList = $this->getCCTypesList($locale['cardtype']);
                }

                $addCardType = array (
                    $this->getMultiSelectForm($value.'_CARDS', $locale['cardtype'], $cardTypesList)
                );
                array_splice($paymentForm[$key]['form']['input'], 2, 0, $addCardType);
            }

            if ($value == "PAYDIREKT") {
                $switchList = $this->getSwitchLIst('payment_is_partial');
                $addPaymentIsPartial = array (
                    $this->getSwitchForm($value.'_PAYMENT_IS_PARTIAL', $locale['payment_is_partial'], $switchList)
                );
                array_splice($paymentForm[$key]['form']['input'], 3, 0, $addPaymentIsPartial);

                $addMinimumAge = array ($this->getTextForm($value.'_MINIMUM_AGE', $locale['minimum_age'], true));
                array_splice($paymentForm[$key]['form']['input'], 4, 0, $addMinimumAge);
            }

            if ($value == "CCSAVED") {
                $switchList = $this->getSwitchLIst('multichannel');
                $addMultiChannel = array (
                    $this->getSwitchForm($value.'_MULTICHANNEL', $locale['multichannel'], $switchList)
                );
                array_splice($paymentForm[$key]['form']['input'], 5, 0, $addMultiChannel);

                $addChannelMoto = array ($this->getTextForm($value.'_CHANNELMOTO', $locale['channelmoto']));
                array_splice($paymentForm[$key]['form']['input'], 7, 0, $addChannelMoto);
            }

            if ($value == "KLARNASLICEIT") {
                $merchantId = array ($this->getTextForm($value.'_MERCHANT_ID', $locale['klarnasliceit_merchant_id']));
                array_splice($paymentForm[$key]['form']['input'], 3, 0, $merchantId);
                $klarnaCurrency = array (
                    $this->getSelectForm(
                        $value.'_CURRENCY',
                        $locale['klarna_currency'],
                        $this->getKlarnaCurrency($locale['klarna_currency'])
                    )
                );
                array_splice($paymentForm[$key]['form']['input'], 4, 0, $klarnaCurrency);
                $klarnaCountry = array (
                    $this->getSelectForm(
                        $value.'_COUNTRY',
                        $locale['klarna_country'],
                        $this->getKlarnaCountry($locale['klarna_country'])
                    )
                );
                array_splice($paymentForm[$key]['form']['input'], 5, 0, $klarnaCountry);
                $klarnaLanguage = array(
                    $this->getSelectForm(
                        $value.'_LANGUAGE',
                        $locale['klarna_language'],
                        $this->getKlarnaLanguage($locale['klarna_language'])
                    )
                );
                array_splice($paymentForm[$key]['form']['input'], 6, 0, $klarnaLanguage);
                $sharedSecret = array($this->getTextForm($value.'_SHARED_SECRET', $locale['klarna_shared_secret']));
                array_splice($paymentForm[$key]['form']['input'], 7, 0, $sharedSecret);
                $pclass = array($this->getTextForm($value.'_PCLASS_ID', $locale['klarna_pclass_id']));
                array_splice($paymentForm[$key]['form']['input'], 8, 0, $pclass);
                $pclass = array($this->getTextForm($value.'_PCLASS_DESCRIPTION', $locale['klarna_pclass_desc']));
                array_splice($paymentForm[$key]['form']['input'], 9, 0, $pclass);
                $pclass = array($this->getTextForm($value.'_PCLASS_MONTHS', $locale['klarna_pclass_amountofmonths']));
                array_splice($paymentForm[$key]['form']['input'], 10, 0, $pclass);
                $pclass = array($this->getTextForm($value.'_PCLASS_START_FEE', $locale['klarna_pclass_startfee']));
                array_splice($paymentForm[$key]['form']['input'], 11, 0, $pclass);
                $pclass = array($this->getTextForm($value.'_PCLASS_INVOICE_FEE', $locale['klarna_pclass_invoicefee']));
                array_splice($paymentForm[$key]['form']['input'], 12, 0, $pclass);
                $pclass = array(
                    $this->getTextForm($value.'_PCLASS_INTEREST_RATE', $locale['klarna_pclass_interestrate'])
                );
                array_splice($paymentForm[$key]['form']['input'], 13, 0, $pclass);
                $pclass = array(
                    $this->getTextForm($value.'_PCLASS_MIN_PURCHASE', $locale['klarna_pclass_minpurchase'])
                );
                array_splice($paymentForm[$key]['form']['input'], 14, 0, $pclass);
                $pclass = array($this->getTextForm($value.'_PCLASS_COUNTRY', $locale['klarna_pclass_country']));
                array_splice($paymentForm[$key]['form']['input'], 15, 0, $pclass);
                $pclass = array($this->getTextForm($value.'_PCLASS_TYPE', $locale['klarna_pclass_type']));
                array_splice($paymentForm[$key]['form']['input'], 16, 0, $pclass);
                $pclass = array($this->getTextForm($value.'_PCLASS_EXPIRY_DATE', $locale['klarna_pclass_expirydate']));
                array_splice($paymentForm[$key]['form']['input'], 17, 0, $pclass);
            }

            if ($value == "KLARNAPAYLATER") {
                $addMerchantId = array (
                    $this->getTextForm($value.'_MERCHANT_ID', $locale['klarnapaylater_merchant_id'])
                );
                array_splice($paymentForm[$key]['form']['input'], 3, 0, $addMerchantId);
            }
        }

        return $paymentForm;
    }

    private function getGeneralForm($locale)
    {
        $generalForm = array();
        $generalForm[] = array(
            'form' => array(
                'legend' => array(
                    'title' => 'GENERAL SETTING'
                ),
                'input' => array(
                    $this->getTextForm(
                        'GENERAL_BEARER',
                        $locale['bearer']
                    ),
                    $this->getTextForm(
                        'GENERAL_LOGIN',
                        $locale['login']
                    ),
                    $this->getTextForm(
                        'GENERAL_PASSWORD',
                        $locale['password']
                    ),
                    $this->getSwitchForm(
                        'GENERAL_RECURRING',
                        $locale['recurring'],
                        $this->getSwitchLIst('recurring')
                    ),
                    $this->getTextForm(
                        'GENERAL_MERCHANTEMAIL',
                        $locale['merchantemail'],
                        true
                    ),
                    $this->getTextForm(
                        'GENERAL_MERCHANTNO',
                        $locale['merchantno'],
                        true
                    ),
                    $this->getTextForm(
                        'GENERAL_SHOPURL',
                        $locale['shopurl'],
                        true
                    ),
                    $this->getSwitchForm(
                        'GENERAL_VERSION_TRACKER',
                        $locale['versionTracker'],
                        $this->getVersionTrackerSwitchLIst('versionTracker')
                    ),
                    $this->getTextForm(
                        'GENERAL_MERCHANTLOCATION',
                        $locale['merchantLocation'],
                        true
                    ),
                ),
                'submit' => array(
                    'title' => $locale['save']
                )
            )
        );

        return $generalForm;
    }

    private function getTextForm($pm, $locale, $requirement = false)
    {
        $textForm =
            array(
                'type' => 'text',
                'label' => $locale['label'],
                'name' => 'VRPAYECOMMERCE_'.$pm,
                'required' => $requirement,
                'desc' => isset($locale['desc']) ? $locale['desc'] : ''
            );


        return $textForm;
    }

    private function getSelectForm($pm, $locale, $selectList)
    {
        $selectForm =
            array(
                'type' => 'select',
                'label' => $locale['label'],
                'name' => 'VRPAYECOMMERCE_'.$pm,
                'options' => array(
                    'query' => $selectList,
                    'id' => 'id',
                    'name' => 'name'
                )
            );

        return $selectForm;
    }

    private function getMultiSelectForm($pm, $locale, $multiSelectList)
    {
        $selectForm =
            array(
                'type' => 'select',
                'label' => $locale['label'],
                'id' => 'VRPAYECOMMERCE_'.$pm,
                'name' => 'VRPAYECOMMERCE_'.$pm.'[]',
                'multiple' => true,
                'options' => array(
                    'query' => $multiSelectList,
                    'id' => 'id',
                    'name' => 'name'
                )
            );

        return $selectForm;
    }

    private function getSwitchForm($pm, $locale, $switchList)
    {
        $switchForm =
            array(
                'type' => 'switch',
                'label' => $locale['label'],
                'name' => 'VRPAYECOMMERCE_'.$pm,
                'is_bool' => true,
                'values' => $switchList,
                'desc' => isset($locale['desc']) ? $locale['desc'] : ''
            );

        return $switchForm;
    }

    /*
    *    Form Content (Enabled, Server, Transaction-mode, Cards Types)
    */

    //Enabled
    private function getSwitchLIst($field)
    {
        $list = array(
            array(
                'id' => $field.'_on',
                'value' => 1,
            ),
            array(
                'id' => $field.'_off',
                'value' => 0,
            )
        );

        return $list;
    }

    //Enabled
    private function getVersionTrackerSwitchLIst($field)
    {
        $list = array(
            array(
                'id' => $field.'_true',
                'value' => 1,
            ),
            array(
                'id' => $field.'_false',
                'value' => 0,
            )
        );

        return $list;
    }

    //Server
    private function getServerLIst($chModeTest, $chModeLive)
    {
        $listChServer = array (
            array(
                'id' => 'TEST',
                'name' => $chModeTest
            ),
            array(
                'id' => "LIVE",
                'name' => $chModeLive
            )
        );

        return $listChServer;
    }

    //Transaction-mode
    private function getTransactionModeList($chModeDebit, $chModePreauth)
    {
        $listChMode = array (
            array(
                'id' => 'DB',
                'name' => $chModeDebit
            ),
            array(
                'id' => "PA",
                'name' => $chModePreauth
            )
        );

        return $listChMode;
    }

    //Cards Types
    private function getCCTypesList($cardTypes)
    {

        $listChCardType = array (
            array(
                'id' => 'VISA',
                'name' => $cardTypes['visa']
            ),
            array(
                'id' => 'MASTER',
                'name' => $cardTypes['master']
            ),
            array(
                'id' => 'AMEX',
                'name' => $cardTypes['amex']
            ),
            array(
                'id' => 'DINERS',
                'name' => $cardTypes['diners']
            ),
            array(
                'id' => "JCB",
                'name' => $cardTypes['jcb']
            )
        );

        return $listChCardType;
    }

    //Cards Types
    private function getDCTypesList($cardTypes)
    {

        $listChCardType = array (
            array(
                'id' => 'VPAY',
                'name' => $cardTypes['vpay']
            ),
            array(
                'id' => 'MAESTRO',
                'name' => $cardTypes['maestro']
            ),
            array(
                'id' => 'DANKORT',
                'name' => $cardTypes['dankort']
            ),
            array(
                'id' => 'VISAELECTRON',
                'name' => $cardTypes['visaelectron']
            ),
            array(
                'id' => "POSTEPAY",
                'name' => $cardTypes['postepay']
            )
        );

        return $listChCardType;
    }
    /*
    *    end of form content
    */

    public function hookDisplayAdminOrder($hook)
    {
        $orderId = Tools::getValue('id_order');
        $vrpayOrder = $this->getOrderVrpayecommerce($orderId);
        if (Tools::isSubmit('vrpayecommerceUpdateOrder')) {
            $this->updateStatusFromGateway($orderId, $vrpayOrder);
        }

        if ($vrpayOrder) {
            $this->context->smarty->assign('transaction_id', $vrpayOrder['transaction_id']);

            if ($vrpayOrder['payment_code'] == 'IR') {
                  $this->context->smarty->assign('in_review', true);
                  $this->context->smarty->assign('id_order', $orderId);
            }
            return $this->display(__FILE__, 'views/templates/hook/displayAdminOrder.tpl');
        }
    }
    public function hookDisplayAdminAfterHeader($hook)
    {
        if ($this->context->cookie->vrpayecommerce_ssl_error) {
            $this->context->smarty->assign(array(
                'module' => 'vrpayecommerce',
                'errorMessage' => 'ssl'
            ));
            unset($this->context->cookie->vrpayecommerce_ssl_error);

            return $this->display(__FILE__, 'views/templates/hook/displayStatusOrder.tpl');
        }
    }
    private function updateStatusFromGateway($orderId, $vrpayOrder)
    {
        $order = new Order($orderId);
        $transactionData = $this->getCredentials($vrpayOrder['payment_method']);
        $transactionData['test_mode'] = $this->getTestMode($vrpayOrder['payment_method']);

        $result = VRpayecommercePaymentCore::updateStatus($vrpayOrder['ref_id'], $transactionData);

        if ($result['is_valid']) {
            $xmlResult = simplexml_load_string($result['response']);
            $returnCode = (string) $xmlResult->Result->Transaction->Processing->Return['code'];
            $transactionResult = VRpayecommercePaymentCore::getTransactionResult($returnCode);

            if ($transactionResult == 'ACK') {
                $this->context->cookie->vrpayecommerce_status_update_order = 'true';
                if (!VRpayecommercePaymentCore::isSuccessReview($returnCode)) {
                    $paymentCode = (string) $xmlResult->Result->Transaction->Payment['code'];
                    $paymentType = Tools::substr($paymentCode, -2);
                    if ($paymentType == 'PA') {
                        $orderStatusId = Configuration::get('VRPAYECOMMERCE_PAYMENT_STATUS_PA');
                    }
                    if ($paymentType == 'DB') {
                        $orderStatusId = Configuration::get('PS_OS_PAYMENT');
                    }

                    $history = new OrderHistory();
                    $history->id_order = (int)$orderId;
                    $history->id_employee = (int)$this->context->employee->id;
                    $useExistingsPayment = false;
                    if (!$order->hasInvoice()) {
                        $useExistingsPayment = true;
                    }
                    $history->changeIdOrderState((int)($orderStatusId), $order, $useExistingsPayment);
                    $history->addWithemail();
                    $sql = "UPDATE vrpayecommerce_order_ref
                    SET payment_code = '".$paymentType."' where ref_id = '".$vrpayOrder['ref_id']."'";
                    if (!Db::getInstance()->execute($sql)) {
                        die('Erreur etc.');
                    }
                }
            } else {
                $this->context->cookie->vrpayecommerce_status_update_order = 'false';
            }
        } else {
            $this->context->cookie->vrpayecommerce_status_update_order = $result['response'];
        }

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders').'&vieworder&id_order='.$orderId);
    }

    private function getConfigGeneralValues()
    {
        $saveConfig = array();
        $saveConfig['VRPAYECOMMERCE_GENERAL_BEARER'] = Tools::getValue(
            'VRPAYECOMMERCE_GENERAL_BEARER',
            Configuration::get('VRPAYECOMMERCE_GENERAL_BEARER')
        );
        $saveConfig['VRPAYECOMMERCE_GENERAL_LOGIN'] = Tools::getValue(
            'VRPAYECOMMERCE_GENERAL_LOGIN',
            Configuration::get('VRPAYECOMMERCE_GENERAL_LOGIN')
        );
        $saveConfig['VRPAYECOMMERCE_GENERAL_PASSWORD'] = Tools::getValue(
            'VRPAYECOMMERCE_GENERAL_PASSWORD',
            Configuration::get('VRPAYECOMMERCE_GENERAL_PASSWORD')
        );
        $saveConfig['VRPAYECOMMERCE_GENERAL_RECURRING'] = Tools::getValue(
            'VRPAYECOMMERCE_GENERAL_RECURRING',
            Configuration::get('VRPAYECOMMERCE_GENERAL_RECURRING')
        );
        $saveConfig['VRPAYECOMMERCE_GENERAL_MERCHANTEMAIL'] = Tools::getValue(
            'VRPAYECOMMERCE_GENERAL_MERCHANTEMAIL',
            Configuration::get('VRPAYECOMMERCE_GENERAL_MERCHANTEMAIL')
        );
        $saveConfig['VRPAYECOMMERCE_GENERAL_MERCHANTNO'] = Tools::getValue(
            'VRPAYECOMMERCE_GENERAL_MERCHANTNO',
            Configuration::get('VRPAYECOMMERCE_GENERAL_MERCHANTNO')
        );
        $saveConfig['VRPAYECOMMERCE_GENERAL_SHOPURL'] = Tools::getValue(
            'VRPAYECOMMERCE_GENERAL_SHOPURL',
            Configuration::get('VRPAYECOMMERCE_GENERAL_SHOPURL')
        );
        $saveConfig['VRPAYECOMMERCE_GENERAL_VERSION_TRACKER'] = Tools::getValue(
            'VRPAYECOMMERCE_GENERAL_VERSION_TRACKER',
            Configuration::get('VRPAYECOMMERCE_GENERAL_VERSION_TRACKER')
        );
        $saveConfig['VRPAYECOMMERCE_GENERAL_MERCHANTLOCATION'] = Tools::getValue(
            'VRPAYECOMMERCE_GENERAL_MERCHANTLOCATION',
            Configuration::get('VRPAYECOMMERCE_GENERAL_MERCHANTLOCATION')
        );

        return $saveConfig;
    }

    public function getConfigFieldsValues()
    {
        $saveConfig = $this->getConfigGeneralValues();

        foreach ($this->payment_methods as $value) {
            $saveConfig['VRPAYECOMMERCE_'.$value.'_ACTIVE'] = Tools::getValue(
                'VRPAYECOMMERCE_'.$value.'_ACTIVE',
                Configuration::get('VRPAYECOMMERCE_'.$value.'_ACTIVE')
            );
            $saveConfig['VRPAYECOMMERCE_'.$value.'_SERVER'] = Tools::getValue(
                'VRPAYECOMMERCE_'.$value.'_SERVER',
                Configuration::get('VRPAYECOMMERCE_'.$value.'_SERVER')
            );

            if ($value == "CC" || $value == "CCSAVED" || $value == "DC") {
                $saveConfig['VRPAYECOMMERCE_'.$value.'_CARDS[]'] = Tools::getValue(
                    'VRPAYECOMMERCE_'.$value.'_CARDS',
                    Configuration::get('VRPAYECOMMERCE_'.$value.'_CARDS')
                );
            }

            if (in_array($value, $this->db_pa_payment_methods)) {
                $saveConfig['VRPAYECOMMERCE_'.$value.'_MODE'] = Tools::getValue(
                    'VRPAYECOMMERCE_'.$value.'_MODE',
                    Configuration::get('VRPAYECOMMERCE_'.$value.'_MODE')
                );
            }

            if ($value == "CCSAVED" || $value == "DDSAVED" || $value == "PAYPALSAVED") {
                $saveConfig['VRPAYECOMMERCE_'.$value.'_AMOUNT'] = Tools::getValue(
                    'VRPAYECOMMERCE_'.$value.'_AMOUNT',
                    Configuration::get('VRPAYECOMMERCE_'.$value.'_AMOUNT')
                );
            }

            if ($value == "PAYDIREKT") {
                $saveConfig['VRPAYECOMMERCE_'.$value.'_MINIMUM_AGE'] = Tools::getValue(
                    'VRPAYECOMMERCE_'.$value.'_MINIMUM_AGE',
                    Configuration::get('VRPAYECOMMERCE_'.$value.'_MINIMUM_AGE')
                );
                $saveConfig['VRPAYECOMMERCE_'.$value.'_PAYMENT_IS_PARTIAL'] = Tools::getValue(
                    'VRPAYECOMMERCE_'.$value.'_PAYMENT_IS_PARTIAL',
                    Configuration::get('VRPAYECOMMERCE_'.$value.'_PAYMENT_IS_PARTIAL')
                );
            }

            if ($value == "CCSAVED") {
                $saveConfig['VRPAYECOMMERCE_'.$value.'_MULTICHANNEL'] = Tools::getValue(
                    'VRPAYECOMMERCE_'.$value.'_MULTICHANNEL',
                    Configuration::get('VRPAYECOMMERCE_'.$value.'_MULTICHANNEL')
                );
                $saveConfig['VRPAYECOMMERCE_'.$value.'_CHANNELMOTO'] = Tools::getValue(
                    'VRPAYECOMMERCE_'.$value.'_CHANNELMOTO',
                    Configuration::get('VRPAYECOMMERCE_'.$value.'_CHANNELMOTO')
                );
            }

            $saveConfig['VRPAYECOMMERCE_'.$value.'_CHANNEL'] = Tools::getValue(
                'VRPAYECOMMERCE_'.$value.'_CHANNEL',
                Configuration::get('VRPAYECOMMERCE_'.$value.'_CHANNEL')
            );

            if ($value == "KLARNASLICEIT") {
                $saveConfig['VRPAYECOMMERCE_'.$value.'_PCLASS'] = Tools::getValue(
                    'VRPAYECOMMERCE_'.$value.'_PCLASS',
                    Configuration::get('VRPAYECOMMERCE_'.$value.'_PCLASS')
                );

                $selectedCountry = Configuration::get('VRPAYECOMMERCE_'.$value.'_COUNTRY');
                foreach ($this->klarnasliceitConfig as $config) {
                    if ($config == 'LANGUAGE') {
                        if ($selectedCountry == '81') {
                            $saveConfig['VRPAYECOMMERCE_'.$value.'_'.$config] =
                            Tools::getValue(
                                'VRPAYECOMMERCE_'.$value.'_'.$config,
                                '28'
                            );
                        } elseif ($selectedCountry == '15') {
                            $saveConfig['VRPAYECOMMERCE_'.$value.'_'.$config] =
                            Tools::getValue(
                                'VRPAYECOMMERCE_'.$value.'_'.$config,
                                '28'
                            );
                        } else {
                            $saveConfig['VRPAYECOMMERCE_'.$value.'_'.$config] = Tools::getValue(
                                'VRPAYECOMMERCE_'.$value.'_'.$config,
                                Configuration::get('VRPAYECOMMERCE_'.$value.'_'.$config)
                            );
                        }
                    } else {
                        $saveConfig['VRPAYECOMMERCE_'.$value.'_'.$config] = Tools::getValue(
                            'VRPAYECOMMERCE_'.$value.'_'.$config,
                            Configuration::get('VRPAYECOMMERCE_'.$value.'_'.$config)
                        );
                    }
                }
            }

            if ($value == "KLARNAPAYLATER") {
                $saveConfig['VRPAYECOMMERCE_'.$value.'_MERCHANT_ID'] = Tools::getValue(
                    'VRPAYECOMMERCE_'.$value.'_MERCHANT_ID',
                    Configuration::get('VRPAYECOMMERCE_'.$value.'_MERCHANT_ID')
                );
            }

            $saveConfig['VRPAYECOMMERCE_'.$value.'_SORT'] = Tools::getValue(
                'VRPAYECOMMERCE_'.$value.'_SORT',
                Configuration::get('VRPAYECOMMERCE_'.$value.'_SORT')
            );

            if (!Tools::isSubmit('btnSubmit')) {
                if (isset($saveConfig['VRPAYECOMMERCE_'.$value.'_CARDS[]'])) {
                    $saveConfig['VRPAYECOMMERCE_'.$value.'_CARDS[]'] = explode(
                        ',',
                        $saveConfig['VRPAYECOMMERCE_'.$value.'_CARDS[]']
                    );
                } else {
                    $saveConfig['VRPAYECOMMERCE_'.$value.'_CARDS[]'] = '';
                }
            }
        }
        return $saveConfig;
    }

    public function createBackendOrder($recurringCart)
    {
        $accountPayment = $this->getAccountPaymentById($recurringCart['id']);
        $refId = $accountPayment['ref_id'];
        $paymentResult = '';

        $paymentMethod = $this->getBackendOrderPayment($accountPayment['payment_group']);
        $paymentDescription = $this->getPaymentDescription($paymentMethod);

        $transactionData = $this->getBackendOrderTransactionData($paymentMethod, $recurringCart);
        $paymentResult['payment_desc'] = $paymentDescription;

        $transactionResult = VrpayecommercePaymentCore::getRecurringPaymentResult($refId, $transactionData);
        if ($transactionResult['is_valid']) {
            $code = $transactionResult['response']['result']['code'];
            $paymentResult['status'] = VrpayecommercePaymentCore::getTransactionResult($code);
            $paymentResult['ref_id'] = $transactionResult['response']['id'];

            $this->saveTransactionLog($paymentMethod, $transactionResult['response'], $paymentResult['status']);

            if ($paymentResult['status'] == 'ACK') {
                if ($transactionResult['response']['payment_type'] == 'PA') {
                    $paymentResult['order_status'] = Configuration::get('VRPAYECOMMERCE_PAYMENT_STATUS_PA');
                } else {
                    $paymentResult['order_status'] =  Configuration::get('PS_OS_PAYMENT');
                }
            } else {
                $paymentResult['order_status'] =  Configuration::get('PS_OS_ERROR');
            }
        } else {
            $paymentResult['response'] = $transactionResult['response'];
            $paymentResult['order_status'] =  Configuration::get('PS_OS_ERROR');
        }
        return $paymentResult;
    }

    private function getBackendOrderPayment($paymentGroup)
    {

        switch ($paymentGroup) {
            case 'CC':
                $paymentMethod = 'CCSAVED';
                break;
            case 'DD':
                $paymentMethod = 'DDSAVED';
                break;
            case 'PAYPAL':
                $paymentMethod = 'PAYPALSAVED';
                break;
        }

        return $paymentMethod;
    }

    private function getBackendOrderTransactionData($paymentMethod, $recurringCart)
    {

        $transactionData = $this->getCredentials($paymentMethod);
        $transactionData['payment_type'] = 'DB';

        if ($paymentMethod == 'CCSAVED' || $paymentMethod == 'DDSAVED') {
            $transactionData['payment_type'] = Configuration::get('VRPAYECOMMERCE_'.$paymentMethod.'_MODE');
        }

        $transactionData['test_mode'] = $this->getTestMode($paymentMethod);
        $transactionData['currency'] = $recurringCart['currency']->iso_code;
        $transactionData['amount'] = $recurringCart['order_total'];
        $transactionData['transaction_id'] = $recurringCart['transaction_id'];
        $transactionData['payment_recurring'] = "REPEATED";

        return $transactionData;
    }


    //---
    public function getAccountPaymentByPaymentMethod($id_customer, $paymentMethod)
    {
        $credentials = $this->getCredentials($paymentMethod);

        $query = "SELECT * FROM `vrpayecommerce_payment_recurring`
        WHERE `cust_id` = '".(int)$id_customer.
        "' AND server_mode = '".$credentials['server_mode'].
        "' AND channel_id = '".$credentials['channel_id']."'";
        $accountPayment = Db::getInstance()->ExecuteS($query);
        return $accountPayment;
    }

    public function getAccountPayment($id_customer)
    {
        $accountPaymentCC = $this->getAccountPaymentByPaymentMethod($id_customer, 'CCSAVED');
        $accountPaymentDD = $this->getAccountPaymentByPaymentMethod($id_customer, 'DDSAVED');
        $accountPaymentPAYPAL = $this->getAccountPaymentByPaymentMethod($id_customer, 'PAYPALSAVED');
        $accountPayment = array_merge($accountPaymentCC, $accountPaymentDD, $accountPaymentPAYPAL);
        return $accountPayment;
    }

    public function getAccountPaymentById($id)
    {

        $query = "SELECT * FROM `vrpayecommerce_payment_recurring` WHERE `id` = ".(int)$id." ";
        $accountPaymentById =  Db::getInstance()->getRow($query);
        return $accountPaymentById;
    }

    public function saveTransactionLog($paymentMethod, $paymentStatus, $transactionResult)
    {
        $paymentType = $this->getPaymentTypeResponse($paymentStatus);

        $sql = "INSERT INTO vrpayecommerce_order_ref (
                transaction_id,
                payment_method,
                order_status,
                ref_id,
                payment_code,
                currency,
                amount,
                mandate_date,
                mandate_id) VALUES ".
                "('".$paymentStatus['merchantTransactionId']."',
                '".pSQL($paymentMethod)."',
                '".$transactionResult."',
                '".$paymentStatus['id']."',
                '".$paymentType."',
                '".$paymentStatus['currency']."',
                '".$paymentStatus['amount']."',
                '',
                '')";

        if (!Db::getInstance()->execute($sql)) {
            die('Erreur etc.');
        }
    }

    public function updateOrderId($refId, $orderId)
    {
        $sql = "UPDATE `vrpayecommerce_order_ref` SET `id_order` = '".(int)$orderId."' where `ref_id` = '".$refId."'";
        if (!Db::getInstance()->execute($sql)) {
            die('Erreur etc.');
        }
    }

    public function getOrderStatus($paymentType)
    {
        switch ($paymentType) {
            case 'IR':
                $orderStatus = Configuration::get('VRPAYECOMMERCE_PAYMENT_STATUS_REVIEW');
                break;
            case 'PA':
                $orderStatus = Configuration::get('VRPAYECOMMERCE_PAYMENT_STATUS_PA');
                break;
            default:
                $orderStatus = Configuration::get('PS_OS_PAYMENT');
                break;
        }
        return $orderStatus;
    }

    public function getPaymentTypeResponse($paymentStatus)
    {
        $resultCode = $paymentStatus['result']['code'];

        if (VRpayecommercePaymentCore::isSuccessReview($resultCode)) {
            $paymentType = 'IR';
        } else {
            $paymentType = (isset($paymentStatus['paymentType']) ? $paymentStatus['paymentType'] : '');
        }

        return $paymentType;
    }

    public function getPaymentDescription($paymentMethod)
    {
        $transactionMode = Configuration::get('VRPAYECOMMERCE_'.$paymentMethod.'_MODE');

        switch ($paymentMethod) {
            case 'CC':
                if ($transactionMode == "DB") {
                    if ($this->l('VRPAYECOMMERCE_TT_CC_DEBIT') == "VRPAYECOMMERCE_TT_CC_DEBIT") {
                        $paymentDescription = "VR pay eCommerce Credit Card - Debit";
                    } else {
                        $paymentDescription = $this->l('VRPAYECOMMERCE_TT_CC_DEBIT');
                    }
                } elseif ($transactionMode == "PA") {
                    if ($this->l('VRPAYECOMMERCE_TT_CC_PREAUTH') == "VRPAYECOMMERCE_TT_CC_PREAUTH") {
                        $paymentDescription = "VR pay eCommerce Credit Card - Pre-Authorization";
                    } else {
                        $paymentDescription = $this->l('VRPAYECOMMERCE_TT_CC_PREAUTH');
                    }
                }
                break;
            case 'CCSAVED':
                if ($transactionMode == "DB") {
                    if ($this->l('VRPAYECOMMERCE_TT_CCSAVED_DEBIT') == "VRPAYECOMMERCE_TT_CCSAVED_DEBIT") {
                        $paymentDescription = "VR pay eCommerce Credit Card (Recurring) - Debit";
                    } else {
                        $paymentDescription = $this->l('VRPAYECOMMERCE_TT_CCSAVED_DEBIT');
                    }
                } elseif ($transactionMode == "PA") {
                    if ($this->l('VRPAYECOMMERCE_TT_CCSAVED_PREAUTH') == "VRPAYECOMMERCE_TT_CCSAVED_PREAUTH") {
                        $paymentDescription = "VR pay eCommerce Credit Card (Recurring) - Pre-Authorization";
                    } else {
                        $paymentDescription = $this->l('VRPAYECOMMERCE_TT_CCSAVED_PREAUTH');
                    }
                }
                break;
            case 'DC':
                if ($this->l('VRPAYECOMMERCE_TT_DC') == "VRPAYECOMMERCE_TT_DC") {
                    $paymentDescription = "VR pay eCommerce Debit Card";
                } else {
                    $paymentDescription = $this->l('VRPAYECOMMERCE_TT_DC');
                }
                break;
            case 'DD':
                if ($this->l('VRPAYECOMMERCE_TT_DD') == "VRPAYECOMMERCE_TT_DD") {
                    $paymentDescription = "VR pay eCommerce Direct Debit (SEPA)";
                } else {
                    $paymentDescription = $this->l('VRPAYECOMMERCE_TT_DD');
                }
                break;
            case 'DDSAVED':
                if ($this->l('VRPAYECOMMERCE_TT_DDSAVED') == "VRPAYECOMMERCE_TT_DDSAVED") {
                    $paymentDescription = "VR pay eCommerce Direct Debit (SEPA) (Recurring)";
                } else {
                    $paymentDescription = $this->l('VRPAYECOMMERCE_TT_DDSAVED');
                }
                break;
            case 'PAYDIREKT':
                if ($this->l('VRPAYECOMMERCE_TT_PAYDIREKT') == "VRPAYECOMMERCE_TT_PAYDIREKT") {
                    $paymentDescription = "VR pay eCommerce paydirekt";
                } else {
                    $paymentDescription = $this->l('VRPAYECOMMERCE_TT_PAYDIREKT');
                }
                break;
            case 'EPS':
                if ($this->l('VRPAYECOMMERCE_TT_EPS') == "VRPAYECOMMERCE_TT_EPS") {
                    $paymentDescription = "VR pay eCommerce eps";
                } else {
                    $paymentDescription = $this->l('VRPAYECOMMERCE_TT_EPS');
                }
                break;
            case 'GIROPAY':
                if ($this->l('VRPAYECOMMERCE_TT_GIROPAY') == "VRPAYECOMMERCE_TT_GIROPAY") {
                    $paymentDescription = "VR pay eCommerce Giropay";
                } else {
                    $paymentDescription = $this->l('VRPAYECOMMERCE_TT_GIROPAY');
                }
                break;
            case 'IDEAL':
                if ($this->l('VRPAYECOMMERCE_TT_IDEAL') == "VRPAYECOMMERCE_TT_IDEAL") {
                    $paymentDescription = "VR pay eCommerce iDeal";
                } else {
                    $paymentDescription = $this->l('VRPAYECOMMERCE_TT_IDEAL');
                }
                break;
            case 'KLARNAPAYLATER':
                if ($this->l('VRPAYECOMMERCE_TT_KLARNAPAYLATER') == "VRPAYECOMMERCE_TT_KLARNAPAYLATER") {
                    $paymentDescription = "VR pay eCommerce Pay later.";
                } else {
                    $paymentDescription = $this->l('VRPAYECOMMERCE_TT_KLARNAPAYLATER');
                }
                break;
            case 'KLARNASLICEIT':
                if ($this->l('VRPAYECOMMERCE_TT_KLARNASLICEIT') == "VRPAYECOMMERCE_TT_KLARNASLICEIT") {
                    $paymentDescription = "VR pay eCommerce Slice it.";
                } else {
                    $paymentDescription = $this->l('VRPAYECOMMERCE_TT_KLARNASLICEIT');
                }
                break;
            case 'PAYPAL':
                if ($this->l('VRPAYECOMMERCE_TT_PAYPAL') == "VRPAYECOMMERCE_TT_PAYPAL") {
                    $paymentDescription = "VR pay eCommerce PayPal";
                } else {
                    $paymentDescription = $this->l('VRPAYECOMMERCE_TT_PAYPAL');
                }
                break;
            case 'PAYPALSAVED':
                if ($this->l('VRPAYECOMMERCE_TT_PAYPALSAVED') == "VRPAYECOMMERCE_TT_PAYPALSAVED") {
                    $paymentDescription = "VR pay eCommerce PayPal (Recurring)";
                } else {
                    $paymentDescription = $this->l('VRPAYECOMMERCE_TT_PAYPALSAVED');
                }
                break;
            case 'KLARNAOBT':
                if ($this->l('VRPAYECOMMERCE_TT_KLARNAOBT') == "VRPAYECOMMERCE_TT_KLARNAOBT") {
                    $paymentDescription = "VR pay eCommerce Online Bank Transfer.";
                } else {
                    $paymentDescription = $this->l('VRPAYECOMMERCE_TT_KLARNAOBT');
                }
                break;
            case 'EASYCREDIT':
                if ($this->l('VRPAYECOMMERCE_TT_EASYCREDIT') == "VRPAYECOMMERCE_TT_EASYCREDIT") {
                    $paymentDescription = "VR pay eCommerce Easycredit";
                } else {
                    $paymentDescription = $this->l('VRPAYECOMMERCE_TT_EASYCREDIT');
                }
                break;
            case 'ENTERPAY':
                if ($this->l('VRPAYECOMMERCE_TT_ENTERPAY') == "VRPAYECOMMERCE_TT_ENTERPAY") {
                    $paymentDescription = "Kauf auf Rechnung TEBA Pay";
                } else {
                    $paymentDescription = $this->l('VRPAYECOMMERCE_TT_ENTERPAY');
                }
                break;
            default:
                $paymentDescription = 'FRAUD';
                break;
        }
        return $paymentDescription;
    }

    public function getSuccessMessage($successIdentifier)
    {
        $returnMessage = '';
        switch ($successIdentifier) {
            case 'SUCCESS_MC_ADD':
                if ($this->l('SUCCESS_MC_ADD') == "SUCCESS_MC_ADD") {
                    $returnMessage = "Congratulations, your payment information were successfully saved.";
                } else {
                    $returnMessage = $this->l('SUCCESS_MC_ADD');
                }
                break;
            case 'SUCCESS_MC_UPDATE':
                if ($this->l('SUCCESS_MC_UPDATE') == "SUCCESS_MC_UPDATE") {
                    $returnMessage = "Congratulations, your payment information were successfully updated.";
                } else {
                    $returnMessage = $this->l('SUCCESS_MC_UPDATE');
                }
                break;
            case 'SUCCESS_MC_DELETE':
                if ($this->l('SUCCESS_MC_DELETE') == "SUCCESS_MC_DELETE") {
                    $returnMessage = "Congratulations, your payment information were successfully deleted.";
                } else {
                    $returnMessage = $this->l('SUCCESS_MC_DELETE');
                }
                break;
        }
        return $returnMessage;
    }

    public function getErrorMessage($errorIdentifier)
    {
        $returnMessage = '';
        switch ($errorIdentifier) {
            case 'ERROR_PARAMETER_CART':
                if ($this->l('ERROR_PARAMETER_CART') == "ERROR_PARAMETER_CART") {
                    $returnMessage = "Please fill your shopping cart to make payment with Klarna.";
                } else {
                    $returnMessage = $this->l('ERROR_PARAMETER_CART');
                }
                break;
            case 'ERROR_ORDER_INVALID':
                if ($this->l('ERROR_ORDER_INVALID') == "ERROR_ORDER_INVALID") {
                    $returnMessage = "Your cart or your information is not complete.
                    Please complete the information before make a payment.";
                } else {
                    $returnMessage = $this->l('ERROR_ORDER_INVALID');
                }
                break;
            case 'ERROR_MESSAGE_PCLASS_REQUIRED':
                if ($this->l('ERROR_MESSAGE_PCLASS_REQUIRED') == "ERROR_MESSAGE_PCLASS_REQUIRED") {
                    $returnMessage = "Transaction cannot be processed because PCLASS parameter is missing,
                    please contact the shop admin for futher information.";
                } else {
                    $returnMessage = $this->l('ERROR_MESSAGE_PCLASS_REQUIRED');
                }
                break;
            case 'ERROR_GENERAL_NORESPONSE':
                if ($this->l('ERROR_GENERAL_NORESPONSE') == "ERROR_GENERAL_NORESPONSE") {
                    $returnMessage = "Unfortunately, the confirmation of your payment failed.
                    Please contact your merchant for clarification.";
                } else {
                    $returnMessage = $this->l('ERROR_GENERAL_NORESPONSE');
                }
                break;
            case 'ERROR_GENERAL_FRAUD_DETECTION':
                if ($this->l('ERROR_GENERAL_FRAUD_DETECTION') == "ERROR_GENERAL_FRAUD_DETECTION") {
                    $returnMessage = "Unfortunately, there was an error while processing your order.
                    In case a payment has been made, it will be automatically refunded.";
                } else {
                    $returnMessage =  $this->l('ERROR_GENERAL_FRAUD_DETECTION');
                }
                break;
            case 'ERROR_CC_ACCOUNT':
                if ($this->l('ERROR_CC_ACCOUNT') == "ERROR_CC_ACCOUNT") {
                    $returnMessage = "The account holder entered does not match your name.
                    Please use an account that is registered on your name.";
                } else {
                    $returnMessage =  $this->l('ERROR_CC_ACCOUNT');
                }
                break;
            case 'ERROR_CC_INVALIDDATA':
                if ($this->l('ERROR_CC_INVALIDDATA') == "ERROR_CC_INVALIDDATA") {
                    $returnMessage = "Unfortunately, the card/account data you entered was not correct.
                    Please try again.";
                } else {
                    $returnMessage = $this->l('ERROR_CC_INVALIDDATA');
                }
                break;
            case 'ERROR_CC_BLACKLIST':
                if ($this->l('ERROR_CC_BLACKLIST') == "ERROR_CC_BLACKLIST") {
                    $returnMessage = "Unfortunately, the credit card you entered can not be accepted.
                    Please choose a different card or payment method.";
                } else {
                    $returnMessage = $this->l('ERROR_CC_BLACKLIST');
                }
                break;
            case 'ERROR_CC_DECLINED_CARD':
                if ($this->l('ERROR_CC_DECLINED_CARD') == "ERROR_CC_DECLINED_CARD") {
                    $returnMessage = "Unfortunately, the credit card you entered can not be accepted.
                    Please choose a different card or payment method.";
                } else {
                    $returnMessage = $this->l('ERROR_CC_DECLINED_CARD');
                }
                break;
            case 'ERROR_CC_EXPIRED':
                if ($this->l('ERROR_CC_EXPIRED') == "ERROR_CC_EXPIRED") {
                    $returnMessage = "Unfortunately, the credit card you entered is expired.
                    Please choose a different card or payment method.";
                } else {
                    $returnMessage = $this->l('ERROR_CC_EXPIRED');
                }
                break;
            case 'ERROR_CC_INVALIDCVV':
                if ($this->l('ERROR_CC_INVALIDCVV') == "ERROR_CC_INVALIDCVV") {
                    $returnMessage = "Unfortunately, the CVV/CVC you entered is not correct.
                    Please try again.";
                } else {
                    $returnMessage = $this->l('ERROR_CC_INVALIDCVV');
                }
                break;
            case 'ERROR_CC_EXPIRY':
                if ($this->l('ERROR_CC_EXPIRY') == "ERROR_CC_EXPIRY") {
                    $returnMessage = "Unfortunately, the expiration date you entered is not correct.
                    Please try again.";
                } else {
                    $returnMessage = $this->l('ERROR_CC_EXPIRY');
                }
                break;
            case 'ERROR_CC_LIMIT_EXCEED':
                if ($this->l('ERROR_CC_LIMIT_EXCEED') == "ERROR_CC_LIMIT_EXCEED") {
                    $returnMessage = "Unfortunately, the limit of your credit card is exceeded.
                    Please choose a different card or payment method.";
                } else {
                    $returnMessage = $this->l('ERROR_CC_LIMIT_EXCEED');
                }
                break;
            case 'ERROR_CC_3DAUTH':
                if ($this->l('ERROR_CC_3DAUTH') == "ERROR_CC_3DAUTH") {
                    $returnMessage = "Unfortunately, the password you entered was not correct.
                    Please try again.";
                } else {
                    $returnMessage = $this->l('ERROR_CC_3DAUTH');
                }
                break;
            case 'ERROR_CC_3DERROR':
                if ($this->l('ERROR_CC_3DERROR') == "ERROR_CC_3DERROR") {
                    $returnMessage = "Unfortunately, there has been an error while processing your request.
                    Please try again.";
                } else {
                    $returnMessage = $this->l('ERROR_CC_3DERROR');
                }
                break;
            case 'ERROR_CC_NOBRAND':
                if ($this->l('ERROR_CC_NOBRAND') == "ERROR_CC_NOBRAND") {
                    $returnMessage = "Unfortunately, there has been an error while processing your request.
                    Please try again.";
                } else {
                    $returnMessage = $this->l('ERROR_CC_NOBRAND');
                }
                break;
            case 'ERROR_GENERAL_LIMIT_AMOUNT':
                if ($this->l('ERROR_GENERAL_LIMIT_AMOUNT') == "ERROR_GENERAL_LIMIT_AMOUNT") {
                    $returnMessage = "Unfortunately, your credit limit is exceeded.
                    Please choose a different card or payment method.";
                } else {
                    $returnMessage = $this->l('ERROR_GENERAL_LIMIT_AMOUNT');
                }
                break;
            case 'ERROR_GENERAL_LIMIT_TRANSACTIONS':
                if ($this->l('ERROR_GENERAL_LIMIT_TRANSACTIONS') == "ERROR_GENERAL_LIMIT_TRANSACTIONS") {
                    $returnMessage = "Unfortunately, your limit of transaction is exceeded.
                    Please try again later.";
                } else {
                    $returnMessage = $this->l('ERROR_GENERAL_LIMIT_TRANSACTIONS');
                }
                break;
            case 'ERROR_CC_DECLINED_AUTH':
                if ($this->l('ERROR_CC_DECLINED_AUTH') == "ERROR_CC_DECLINED_AUTH") {
                    $returnMessage = "Unfortunately, your transaction has failed.
                    Please choose a different card or payment method.";
                } else {
                    $returnMessage = $this->l('ERROR_CC_DECLINED_AUTH');
                }
                break;
            case 'ERROR_GENERAL_DECLINED_RISK':
                if ($this->l('ERROR_GENERAL_DECLINED_RISK') == "ERROR_GENERAL_DECLINED_RISK") {
                    $returnMessage = "Unfortunately, your transaction has failed.
                    Please choose a different card or payment method.";
                } else {
                    $returnMessage = $this->l('ERROR_GENERAL_DECLINED_RISK');
                }
                break;
            case 'ERROR_CC_ADDRESS':
                if ($this->l('ERROR_CC_ADDRESS') == "ERROR_CC_ADDRESS") {
                    $returnMessage = "We are sorry.
                    We could no accept your card as its origin does not match your address.";
                } else {
                    $returnMessage = $this->l('ERROR_CC_ADDRESS');
                }
                break;
            case 'ERROR_GENERAL_CANCEL':
                if ($this->l('ERROR_GENERAL_CANCEL') == "ERROR_GENERAL_CANCEL") {
                    $returnMessage = "You cancelled the payment prior to its execution.
                    Please try again.";
                } else {
                    $returnMessage = $this->l('ERROR_GENERAL_CANCEL');
                }
                break;
            case 'ERROR_CC_RECURRING':
                if ($this->l('ERROR_CC_RECURRING') == "ERROR_CC_RECURRING") {
                    $returnMessage = "Recurring transactions have been deactivated for this credit card.
                    Please choose a different card or payment method.";
                } else {
                    $returnMessage = $this->l('ERROR_CC_RECURRING');
                }
                break;
            case 'ERROR_CC_REPEATED':
                if ($this->l('ERROR_CC_REPEATED') == "ERROR_CC_REPEATED") {
                    $returnMessage = "Unfortunately, your transaction has been declined due to invalid data.
                    Please choose a different card or payment method.";
                } else {
                    $returnMessage = $this->l('ERROR_CC_REPEATED');
                }
                break;
            case 'ERROR_GENERAL_ADDRESS':
                if ($this->l('ERROR_GENERAL_ADDRESS') == "ERROR_GENERAL_ADDRESS") {
                    $returnMessage = "Unfortunately, your transaction has failed.
                    Please check the personal data you entered.";
                } else {
                    $returnMessage = $this->l('ERROR_GENERAL_ADDRESS');
                }
                break;
            case 'ERROR_GENERAL_BLACKLIST':
                if ($this->l('ERROR_GENERAL_BLACKLIST') == "ERROR_GENERAL_BLACKLIST") {
                    $returnMessage = "The chosen payment method is not available at the moment.
                    Please choose a different card or payment method.";
                } else {
                    $returnMessage = $this->l('ERROR_GENERAL_BLACKLIST');
                }
                break;
            case 'ERROR_GENERAL_GENERAL':
                if ($this->l('ERROR_GENERAL_GENERAL') == "ERROR_GENERAL_GENERAL") {
                    $returnMessage = "Unfortunately, your transaction has failed.
                    Please try again.";
                } else {
                    $returnMessage = $this->l('ERROR_GENERAL_GENERAL');
                }
                break;
            case 'ERROR_GENERAL_REDIRECT':
                if ($this->l('ERROR_GENERAL_REDIRECT') == "ERROR_GENERAL_REDIRECT") {
                    $returnMessage = "Error before redirect.";
                } else {
                    $returnMessage = $this->l('ERROR_GENERAL_REDIRECT');
                }
                break;
            case 'ERROR_GENERAL_TIMEOUT':
                if ($this->l('ERROR_GENERAL_TIMEOUT') == "ERROR_GENERAL_TIMEOUT") {
                    $returnMessage = "Unfortunately, your transaction has failed.
                    Please try again.";
                } else {
                    $returnMessage = $this->l('ERROR_GENERAL_TIMEOUT');
                }
                break;
            case 'ERROR_GIRO_NOSUPPORT':
                if ($this->l('ERROR_GIRO_NOSUPPORT') == "ERROR_GIRO_NOSUPPORT") {
                    $returnMessage = "Giropay is not supported for this transaction.
                    Please choose a different payment method.";
                } else {
                    $returnMessage = $this->l('ERROR_GIRO_NOSUPPORT');
                }
                break;
            case 'ERROR_ADDRESS_PHONE':
                if ($this->l('ERROR_ADDRESS_PHONE') == "ERROR_ADDRESS_PHONE") {
                    $returnMessage = "Unfortunately, your transaction has failed.
                    Please enter a valid telephone number.";
                } else {
                    $returnMessage = $this->l('ERROR_ADDRESS_PHONE');
                }
                break;
            case 'ERROR_UNKNOWN':
                if ($this->l('ERROR_UNKNOWN') == "ERROR_UNKNOWN") {
                    $returnMessage = "Unfortunately, your transaction has failed.
                    Please try again.";
                } else {
                    $returnMessage = $this->l('ERROR_UNKNOWN');
                }
                break;
            case 'ERROR_MC_UPDATE':
                if ($this->l('ERROR_MC_UPDATE') == "ERROR_MC_UPDATE") {
                    $returnMessage = "We are sorry.
                            Your attempt to update your payment information was not successful.";
                } else {
                    $returnMessage = $this->l('ERROR_MC_UPDATE');
                }
                break;
            case 'ERROR_MC_ADD':
                if ($this->l('ERROR_MC_ADD') == "ERROR_MC_ADD") {
                    $returnMessage = "We are sorry. Your attempt to save your payment information was not successful";
                } else {
                    $returnMessage = $this->l('ERROR_MC_ADD');
                }
                break;
            case 'ERROR_MC_DELETE':
                if ($this->l('ERROR_MC_DELETE') == "ERROR_MC_DELETE") {
                    $returnMessage = "We are sorry. Your attempt to delete your payment information was not successful";
                } else {
                    $returnMessage = $this->l('ERROR_MC_DELETE');
                }
                break;
            case 'ERROR_MERCHANT_SSL_CERTIFICATE':
                if ($this->l('ERROR_MERCHANT_SSL_CERTIFICATE') == "ERROR_MERCHANT_SSL_CERTIFICATE") {
                    $returnMessage = "SSL certificate problem, please contact the merchant.";
                } else {
                    $returnMessage = $this->l('ERROR_MERCHANT_SSL_CERTIFICATE');
                }
                break;
            case 'ERROR_GENERAL_CAPTURE_PAYMENT':
                if ($this->l('ERROR_GENERAL_CAPTURE_PAYMENT') == "ERROR_GENERAL_CAPTURE_PAYMENT") {
                    $returnMessage = "Unfortunately, your attempt to capture the payment failed.";
                } else {
                    $returnMessage = $this->l('ERROR_GENERAL_CAPTURE_PAYMENT');
                }
                break;
            default:
                if ($this->l('ERROR_UNKNOWN') == "ERROR_UNKNOWN") {
                    $returnMessage = "Unfortunately, your transaction has failed.
                    Please try again.";
                } else {
                    $returnMessage = $this->l('ERROR_UNKNOWN');
                }
                break;
        }
        return $returnMessage;
    }

    /**
     * get translation by identifier
     * @param  string $identifier
     * @return string
     */
    public function getTranslationByIdentifier($identifier)
    {
        switch ($identifier) {
            case 'FRONTEND_TT_KLARNAPAYLATER':
                if ($this->l('FRONTEND_TT_KLARNAPAYLATER') == "FRONTEND_TT_KLARNAPAYLATER") {
                    $translation = "Invoice";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNAPAYLATER');
                }
                break;
            case 'FRONTEND_TT_KLARNAPAYLATER_SUBTITLE':
                if ($this->l('FRONTEND_TT_KLARNAPAYLATER_SUBTITLE') == "FRONTEND_TT_KLARNAPAYLATER_SUBTITLE") {
                    $translation = "In 14 Tagen bezahlen";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNAPAYLATER_SUBTITLE');
                }
                break;
            case 'FRONTEND_TT_KLARNA_TERM1':
                if ($this->l('FRONTEND_TT_KLARNA_TERM1') == "FRONTEND_TT_KLARNA_TERM1") {
                    $translation = "Mit der Übermittlung der für die Abwicklung der gewählten Klarna Zahlungsmethode
                    und einer Identitäts - und Bonitätsprüfung erforderlichen Daten an Klarna bin ich einverstanden.
                    Meine";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNA_TERM1');
                }
                break;
            case 'FRONTEND_TT_KLARNA_TERM2':
                if ($this->l('FRONTEND_TT_KLARNA_TERM2') == "FRONTEND_TT_KLARNA_TERM2") {
                    $translation = "kann ich jederzeit mit Wirkung für die Zukunft widerrufen.
                    Es gelten die AGB des Händlers.";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNA_TERM2');
                }
                break;
            case 'FRONTEND_TT_KLARNASLICEIT':
                if ($this->l('FRONTEND_TT_KLARNASLICEIT') == "FRONTEND_TT_KLARNASLICEIT") {
                    $translation = "Installments";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNASLICEIT');
                }
                break;
            case 'FRONTEND_TT_KLARNASLICEIT_FLEXIBEL':
                if ($this->l('FRONTEND_TT_KLARNASLICEIT_FLEXIBEL') == "FRONTEND_TT_KLARNASLICEIT_FLEXIBEL") {
                    $translation = "Flexible - Pay at your own pace";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNASLICEIT_FLEXIBEL');
                }
                break;
            case 'FRONTEND_TT_KLARNASLICEIT_INTEREST':
                if ($this->l('FRONTEND_TT_KLARNASLICEIT_INTEREST') == "FRONTEND_TT_KLARNASLICEIT_INTEREST") {
                    $translation = "Interest Rate";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNASLICEIT_INTEREST');
                }
                break;
            case 'FRONTEND_TT_KLARNASLICEIT_MONTHLY_FEE':
                if ($this->l('FRONTEND_TT_KLARNASLICEIT_MONTHLY_FEE') == "FRONTEND_TT_KLARNASLICEIT_MONTHLY_FEE") {
                    $translation = "Monthly Installment";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNASLICEIT_MONTHLY_FEE');
                }
                break;
            case 'FRONTEND_TT_KLARNASLICEIT_MONTHLY_PAY':
                if ($this->l('FRONTEND_TT_KLARNASLICEIT_MONTHLY_PAY') == "FRONTEND_TT_KLARNASLICEIT_MONTHLY_PAY") {
                    $translation = "Minimum Installment Rate for this Order";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNASLICEIT_MONTHLY_PAY');
                }
                break;
            case 'GENERAL_TEXT_MONTH':
                if ($this->l('GENERAL_TEXT_MONTH') == "GENERAL_TEXT_MONTH") {
                    $translation = "Month";
                } else {
                    $translation = $this->l('GENERAL_TEXT_MONTH');
                }
                break;
            case 'FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO1':
                if ($this->l('FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO1') == "FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO1") {
                    $translation = "Verfügungsrahmen ab 199,99 € (abhängig von der Höhe Ihrer Einkäufe),
                    effektiver Jahreszins 18,07%* und Gesamtbetrag 218,57€* (*bei Ausnutzung des vollen
                    Verfügungsrahmens und Rückzahlung in 12 monatlichen Raten je 18,21 €). Hier finden Sie'";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO1');
                }
                break;
            case 'FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK1':
                if ($this->l('FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK1')
                    == "FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK1"
                ) {
                    $translation = "weitere Informationen";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK1');
                }
                break;
            case 'FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK2':
                if ($this->l('FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK2')
                    == "FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK2"
                ) {
                    $translation = "AGB mit Widerrufsbelehrung";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK2');
                }
                break;
            case 'FRONTEND_TT_KLARNASLICEIT_AND':
                if ($this->l('FRONTEND_TT_KLARNASLICEIT_AND') == "FRONTEND_TT_KLARNASLICEIT_AND") {
                    $translation = "und";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNASLICEIT_AND');
                }
                break;
            case 'FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK3':
                if ($this->l('FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK3')
                    == "FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK3"
                ) {
                    $translation = "Standardinformationen für Verbraucherkredite";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK3');
                }
                break;
            case 'FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK4':
                if ($this->l('FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK4')
                    == "FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK4"
                ) {
                    $translation = "Rechungskauf";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO_LINK4');
                }
                break;
            case 'FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO2':
                if ($this->l('FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO2') == "FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO2") {
                    $translation = "Übersteigt Ihr Einkauf mit Ratenkauf. erstmals einen Betrag von 199,99 €
                    erhalten Sie von Klarna einen Ratenkaufvertrag mit der Bitte um Unterzeichnung zugesandt.
                    Ihr Kauf gilt solange als";
                } else {
                    $translation = $this->l('FRONTEND_TT_KLARNASLICEIT_CREDIT_INFO2');
                }
                break;
            case 'ERROR_MESSAGE_KLARNA_REQUIRED':
                if ($this->l('ERROR_MESSAGE_KLARNA_REQUIRED') == "ERROR_MESSAGE_KLARNA_REQUIRED") {
                    $translation = "Bitte bestätigen Sie, dass Sie mit der Übermittlung der Daten
                    an Klarna einverstanden sind, um Klarna nutzen zu können.";
                } else {
                    $translation = $this->l('ERROR_MESSAGE_KLARNA_REQUIRED');
                }
                break;
            default:
                $translation = $identifier;
                break;
        }
        return $translation;
    }

    public function mailAlert($order, $paymentBrand, $status, $returnCode, $returnMessage, $template)
    {
        if (!Module::isInstalled('mailalerts')) {
            $id_lang = (int)Context::getContext()->language->id;
            $customer = $this->context->customer;
            $delivery = new Address((int)$order->id_address_delivery);
            $invoice = new Address((int)$order->id_address_invoice);
            $order_date_text = Tools::displayDate($order->date_add, (int)$id_lang);
            $carrier = new Carrier((int)$order->id_carrier);
            $products = $order->getProducts();
            $customized_datas = Product::getAllCustomizedDatas((int)$this->context->cart->id);
            $currency = $this->context->currency;
            $emails = array();
            $message = $order->getFirstMessage();
            if (!$message || empty($message)) {
                if ($this->l('ERROR_GENERAL_NOMESSAGE') == "ERROR_GENERAL_NOMESSAGE") {
                    $message = "Without Message";
                } else {
                    $message = $this->l('ERROR_GENERAL_NOMESSAGE');
                }
            }
            $items_table = '';
            $sql = 'SELECT * FROM '._DB_PREFIX_.'employee where id_profile = 1 and active = 1';
            $results = Db::getInstance()->ExecuteS($sql);
            if ($results) {
                foreach ($results as $row) {
                    array_push($emails, $row['email']);
                }
            }
            foreach ($products as $key => $product) {
                $unit_price = $product['product_price_wt'];

                $customization_text = '';
                $product_attribute_id = $product['product_attribute_id'];

                if (isset($customized_datas[$product['product_id']][$product_attribute_id])) {
                    foreach ($customized_datas[$product['product_id']][$product_attribute_id] as $customization) {
                        if (isset($customization['datas'][_CUSTOMIZE_TEXTFIELD_])) {
                            foreach ($customization['datas'][_CUSTOMIZE_TEXTFIELD_] as $text) {
                                $customization_text .= $text['name'].': '.$text['value'].'<br />';
                            }
                        }

                        if (isset($customization['datas'][_CUSTOMIZE_FILE_])) {
                            if ($this->l('BACKEND_TEXT_IMAGE') == "BACKEND_TEXT_IMAGE") {
                                $images = "image(s)";
                            } else {
                                $images = $this->l('BACKEND_TEXT_IMAGE');
                            }
                                $customization_text .=
                                count($customization['datas'][_CUSTOMIZE_FILE_]).' '.$images.'<br />';
                        }

                        $customization_text .= '---<br />';
                    }

                    $customization_text = rtrim($customization_text, '---<br />');
                }

                if (isset($product['attributes_small'])) {
                    $product_attributes_small = $product['attributes_small'];
                } else {
                    $product_attributes_small = '';
                }

                if (empty($customization_text)) {
                    $customization_text = '';
                }

                $unit_price_product = Tools::displayPrice($unit_price, $currency, false);
                $total_price = Tools::displayPrice(($unit_price * $product['product_quantity']), $currency, false);

                $items_table .=
                    '<tr style="background-color:'.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
                        <td style="padding:0.6em 0.4em;">'.$product['product_reference'].'</td>
                        <td style="padding:0.6em 0.4em;">
                            <strong>'
                                .$product['product_name'].(' '.$product_attributes_small.' ').
                                ('<br />'.$customization_text).
                            '</strong>
                        </td>
                        <td style="padding:0.6em 0.4em; text-align:right;">'.$unit_price_product.'</td>
                        <td style="padding:0.6em 0.4em; text-align:center;">'.(int)$product['product_quantity'].'</td>
                        <td style="padding:0.6em 0.4em; text-align:right;">'.$total_price.'</td>
                    </tr>';
            }
            foreach ($order->getCartRules() as $discount) {
                if ($this->l('BACKEND_TEXT_VOUCHER_CODE') == "BACKEND_TEXT_VOUCHER_CODE") {
                    $voucher_code = "Voucher code :";
                } else {
                    $voucher_code = $this->l('BACKEND_TEXT_VOUCHER_CODE').' '.$discount['name'];
                }
                $items_table .=
                '<tr style="background-color:#EBECEE;">
                        <td colspan="4" style="padding:0.6em 0.4em; text-align:right;">
                        '.$voucher_code.'</td>
                        <td style="padding:0.6em 0.4em; text-align:right;">-
                        '.Tools::displayPrice($discount['value'], $currency, false).'</td>
                </tr>';
            }

            if ($delivery->id_state) {
                $delivery_state = new State((int)$delivery->id_state);
            }
            if ($invoice->id_state) {
                $invoice_state = new State((int)$invoice->id_state);
            }

            $template_vars = array(
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{email}' => $customer->email,
            '{delivery_block_txt}' => VrpayecommerceCustomMailAlert::getFormatedAddress($delivery, "\n"),
            '{invoice_block_txt}' => VrpayecommerceCustomMailAlert::getFormatedAddress($invoice, "\n"),
            '{delivery_block_html}' => VrpayecommerceCustomMailAlert::getFormatedAddress($delivery, '<br />', array(
            'firstname' => '<span style="color:blue; font-weight:bold;">%s</span>',
            'lastname' => '<span style="color:blue; font-weight:bold;">%s</span>')),
            '{invoice_block_html}' => VrpayecommerceCustomMailAlert::getFormatedAddress($invoice, '<br />', array(
            'firstname' => '<span style="color:blue; font-weight:bold;">%s</span>',
            'lastname' => '<span style="color:blue; font-weight:bold;">%s</span>')),
            '{delivery_company}' => $delivery->company,
            '{delivery_firstname}' => $delivery->firstname,
            '{delivery_lastname}' => $delivery->lastname,
            '{delivery_address1}' => $delivery->address1,
            '{delivery_address2}' => $delivery->address2,
            '{delivery_city}' => $delivery->city,
            '{delivery_postal_code}' => $delivery->postcode,
            '{delivery_country}' => $delivery->country,
            '{delivery_state}' => $delivery->id_state ? $delivery_state->name : '',
            '{delivery_phone}' => $delivery->phone ? $delivery->phone : $delivery->phone_mobile,
            '{delivery_other}' => $delivery->other,
            '{invoice_company}' => $invoice->company,
            '{invoice_firstname}' => $invoice->firstname,
            '{invoice_lastname}' => $invoice->lastname,
            '{invoice_address2}' => $invoice->address2,
            '{invoice_address1}' => $invoice->address1,
            '{invoice_city}' => $invoice->city,
            '{invoice_postal_code}' => $invoice->postcode,
            '{invoice_country}' => $invoice->country,
            '{invoice_state}' => $invoice->id_state ? $invoice_state->name : '',
            '{invoice_phone}' => $invoice->phone ? $invoice->phone : $invoice->phone_mobile,
            '{invoice_other}' => $invoice->other,
            '{order_name}' => sprintf('%06d', $order->id),
            '{shop_name}' => Configuration::get('PS_SHOP_NAME'),
            '{date}' => $order_date_text,
            '{carrier}' => (($carrier->name == '0') ? Configuration::get('PS_SHOP_NAME') : $carrier->name),
            '{payment}' => $order->payment,
            '{items}' => $items_table,
            '{total_paid}' => Tools::displayPrice($order->total_paid, $currency),
            '{total_products}' => Tools::displayPrice($order->getTotalProductsWithTaxes(), $currency),
            '{total_discounts}' => Tools::displayPrice($order->total_discounts, $currency),
            '{total_shipping}' => Tools::displayPrice($order->total_shipping, $currency),
            '{total_wrapping}' => Tools::displayPrice($order->total_wrapping, $currency),
            '{currency}' => $currency->sign,
            '{message}' => $message,
            '{returnCode}' => $returnCode,
            '{returnMessage}' => $returnMessage
            );

            Mail::Send(
                $id_lang,
                $template,
                sprintf(
                    Mail::l(
                        'Order #%06d - '.$this->displayName.' ('.$paymentBrand.' - '.$status.')',
                        $id_lang
                    ),
                    $order->id
                ),
                $template_vars,
                $emails,
                null,
                Configuration::get('PS_SHOP_EMAIL'),
                Configuration::get('PS_SHOP_NAME'),
                null,
                null,
                dirname(__FILE__).'/mails/'
            );
        }
    }

    private function getKlarnaCurrency($klarnaCurrenyLocales)
    {
        $klarnaCurrencies = array (
            array(
                'id' => '0',
                'name' => $klarnaCurrenyLocales['swedish']
            ),
            array(
                'id' => '1',
                'name' => $klarnaCurrenyLocales['norwegian']
            ),
            array(
                'id' => '2',
                'name' => $klarnaCurrenyLocales['euro']
            ),
            array(
                'id' => '3',
                'name' => $klarnaCurrenyLocales['danish']
            )
        );

        return $klarnaCurrencies;
    }

    private function getKlarnaCountry($klarnaCountryLocales)
    {
        $klarnaCountries = array (
            array(
                'id' => '15',
                'name' => $klarnaCountryLocales['austria']
            ),
            array(
                'id' => '59',
                'name' => $klarnaCountryLocales['denmark']
            ),
            array(
                'id' => '73',
                'name' => $klarnaCountryLocales['finland']
            ),
            array(
                'id' => '81',
                'name' => $klarnaCountryLocales['germany']
            ),
            array(
                'id' => '154',
                'name' => $klarnaCountryLocales['netherlands']
            ),
            array(
                'id' => '164',
                'name' => $klarnaCountryLocales['norway']
            ),
            array(
                'id' => '209',
                'name' => $klarnaCountryLocales['sweden']
            )
        );

        return $klarnaCountries;
    }

    /**
     * returns language id and language name that supported by klarna
     * @param  array $klarnaLanguageLocales
     * @return array
     */
    private function getKlarnaLanguage($klarnaLanguageLocales)
    {
        $selectedCountry = Configuration::get('VRPAYECOMMERCE_KLARNASLICEIT_COUNTRY');
        if ($selectedCountry == '15') {
            $klarnaLanguages = array (
              array(
                  'id' => '28',
                  'name' => $klarnaLanguageLocales['austria']
              ),
              array(
                  'id' => '28',
                  'name' => $klarnaLanguageLocales['german']
              )           );
        } else {
            $klarnaLanguages = array (
               array(
                   'id' => '28',
                   'name' => $klarnaLanguageLocales['german']
               ),
               array(
                   'id' => '28',
                   'name' => $klarnaLanguageLocales['austria']
               )
            );
        }

        array_push(
            $klarnaLanguages,
            array(
                'id' => '27',
                'name' => $klarnaLanguageLocales['danish']
            ),
            array(
                'id' => '37',
                'name' => $klarnaLanguageLocales['finnish']
            ),
            array(
                'id' => '97',
                'name' => $klarnaLanguageLocales['norwegian']
            ),
            array(
                'id' => '101',
                'name' => $klarnaLanguageLocales['dutch']
            ),
            array(
                'id' => '138',
                'name' => $klarnaLanguageLocales['swedish']
            )
        );

        return $klarnaLanguages;
    }

    /**
     * return parameters to get pClass value from gateway
     * @return array
     */
    protected function getPClassParameters()
    {
        $pClassParameters = array();
        $pClassParameters['SERVER'] = Tools::getValue('VRPAYECOMMERCE_KLARNASLICEIT_SERVER');
        $pClassParameters['MERCHANT_ID'] = Tools::getValue('VRPAYECOMMERCE_KLARNASLICEIT_MERCHANT_ID');
        $pClassParameters['CURRENCY'] = Tools::getValue('VRPAYECOMMERCE_KLARNASLICEIT_CURRENCY');
        $pClassParameters['COUNTRY'] = Tools::getValue('VRPAYECOMMERCE_KLARNASLICEIT_COUNTRY');
        $pClassParameters['LANGUAGE'] = Tools::getValue('VRPAYECOMMERCE_KLARNASLICEIT_LANGUAGE');
        $pClassParameters['SHARED_SECRET'] = Tools::getValue('VRPAYECOMMERCE_KLARNASLICEIT_SHARED_SECRET');
        $pClassParameters['DIGEST'] = $this->getPClassDigest($pClassParameters);

        return $pClassParameters;
    }

    /**
     * return monthly cost for klarna payment
     * @return string
     */
    public function getKlarnaMonthlyCost()
    {
        $pClass = array();
        $pClass['id'] = Configuration::get('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_ID');
        $pClass['months'] = Configuration::get('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_MONTHS');
        $pClass['startFee'] = Configuration::get('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_START_FEE');
        $pClass['invoiceFee'] = Configuration::get('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_INVOICE_FEE');
        $pClass['interestRate'] = Configuration::get('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_INTEREST_RATE');
        $pClass['country'] = (int) Configuration::get('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_COUNTRY');
        $pClass['type'] = (int) Configuration::get('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_TYPE');

        $amount = $this->context->cart->getOrderTotal(true, Cart::BOTH);

        return VRpayecommercePaymentKlarna::getKlarnaCalcMonthlyCost($amount, $pClass);
    }

    /**
     * set PClass value
     * @param object $pClassData
     * @return void
     */
    protected function setPClassPostValue($pClassData)
    {
        $_POST['VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_ID'] = $this->getPClassDataValueByNumber($pClassData, 0);
        $_POST['VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_DESCRIPTION'] = $this->getPClassDataValueByNumber($pClassData, 1);
        $_POST['VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_MONTHS'] = $this->getPClassDataValueByNumber($pClassData, 2);
        $_POST['VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_START_FEE'] = $this->getPClassDataValueByNumber($pClassData, 3);
        $_POST['VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_INVOICE_FEE'] =
            (float) $this->getPClassDataValueByNumber($pClassData, 4) / 100;
        $_POST['VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_INTEREST_RATE'] =
            (float) $this->getPClassDataValueByNumber($pClassData, 5) / 100;
        $_POST['VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_MIN_PURCHASE'] = $this->getPClassDataValueByNumber(
            $pClassData,
            6
        );
        $_POST['VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_COUNTRY'] = $this->getPClassDataValueByNumber(
            $pClassData,
            7,
            'int'
        );
        $_POST['VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_TYPE'] = $this->getPClassDataValueByNumber(
            $pClassData,
            8,
            'int'
        );
        $_POST['VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_EXPIRY_DATE'] = $this->getPClassDataValueByNumber(
            $pClassData,
            9
        );
    }

    /**
     * return pClass data by number and data type
     * @param  object $pClassData
     * @param  int $number
     * @param  string $dataType
     * @return string | null
     */
    protected function getPClassDataValueByNumber($pClassData, $number, $dataType = 'string')
    {
        if ($dataType == 'string') {
            if (isset($pClassData->value[$number]->string)) {
                return (string)$pClassData->value[$number]->string;
            }
        }
        if ($dataType == 'int') {
            if (isset($pClassData->value[$number]->int)) {
                return (string)$pClassData->value[$number]->int;
            }
        }
        return null;
    }

    protected function getPClassDigest($pClassParameters)
    {
        $merchantId = $pClassParameters['MERCHANT_ID'];
        $currency = $pClassParameters['CURRENCY'];
        $sharedSecret = $pClassParameters['SHARED_SECRET'];
        $pClassDigest = base64_encode(hash("sha512", "$merchantId:$currency:$sharedSecret", true));

        return $pClassDigest;
    }

    public function isOrderValid()
    {
        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);

        if ($cart->id_customer == 0
            || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0
            || !Validate::isLoadedObject($customer)
            || !$this->active
        ) {
            return false;
        }

        return true;
    }

    public function getEasycreditNotify()
    {
        $error = array();

        if ($this->isEasycreditAmountAllowed()) {
            if ($this->l('ERROR_MESSAGE_EASYCREDIT_AMOUNT_NOTALLOWED') ==
                "ERROR_MESSAGE_EASYCREDIT_AMOUNT_NOTALLOWED") {
                $error[] = "Der Finanzierungsbetrag liegt außerhalb der zulässigen Beträge (200 - 10.000 EUR).";
            } else {
                $error[] = $this->l('ERROR_MESSAGE_EASYCREDIT_AMOUNT_NOTALLOWED');
            }
        }

        if (!$this->isGenderValid()) {
            if ($this->l('ERROR_MESSAGE_EASYCREDIT_PARAMETER_GENDER') == "ERROR_MESSAGE_EASYCREDIT_PARAMETER_GENDER") {
                $error[] = "Please enter your gender to make payment with easyCredit.";
            } else {
                $error[] = $this->l('ERROR_MESSAGE_EASYCREDIT_PARAMETER_GENDER');
            }
        }

        if (!$this->isBillingAddressEqualShipping()) {
            if ($this->l('ERROR_EASYCREDIT_BILLING_NOTEQUAL_SHIPPING') ==
                "ERROR_EASYCREDIT_BILLING_NOTEQUAL_SHIPPING") {
                $error[] = "Um mit easyCredit bezahlen zu können, muss die Lieferadresse mit
                der Rechnungsadresse übereinstimmen.";
            } else {
                $error[] = $this->l('ERROR_EASYCREDIT_BILLING_NOTEQUAL_SHIPPING');
            }
        }

        if (!$this->isDateOfBirthValid()) {
            if ($this->l('ERROR_EASYCREDIT_PARAMETER_DOB') == "ERROR_EASYCREDIT_PARAMETER_DOB") {
                $error[] = "Bitte geben Sie Ihr Geburtsdatum an, um easyCredit nutzen zu können.";
            } else {
                $error[] = $this->l('ERROR_EASYCREDIT_PARAMETER_DOB');
            }
        }

        if (!$this->isDateOfBirthLowerThanToday()) {
            if ($this->l('ERROR_EASYCREDIT_FUTURE_DOB') == "ERROR_EASYCREDIT_FUTURE_DOB") {
                $error[] = "Das Geburtsdatum sollte nicht in der Zukunft liegen.";
            } else {
                $error[] = $this->l('ERROR_EASYCREDIT_FUTURE_DOB');
            }
        }
        if (!$error) {
            return false;
        }
        return $error;
    }

    public function getEnterPayNotify()
    {
        if ($this->l('ENTERPAY_NOTIFY_REDIRECT') ==
            "ENTERPAY_NOTIFY_REDIRECT") {
            return "Sie werden zum B2B Rechnungskauf TEBA PAY weitergeleitet. Eine einmalige Registrierung mit Ihren Firmendaten ist erforderlich";
        } else {
            return $this->l('ENTERPAY_NOTIFY_REDIRECT');
        }
    }

    public function getEnterPayCallToActionText()
    {
        if ($this->l('ENTERPAY_CALL_TO_ACTION') ==
            "ENTERPAY_CALL_TO_ACTION") {
            return "Rechnungskauf/Firmenkunden";
        } else {
            return $this->l('ENTERPAY_CALL_TO_ACTION');
        }
    }

    public function getKlarnaDisabledErrors()
    {

        $error = array();

        if (!$this->isBillingAddressEqualShipping()) {
            if ($this->l('ERROR_MESSAGE_BILLING_NOTEQUAL_SHIPPING') == "ERROR_MESSAGE_BILLING_NOTEQUAL_SHIPPING") {
                $error[] = "Um mit Klarna Rechnungskauf oder Ratenkauf bezahlen zu können,
                muss die Lieferadresse mit der Rechnungsadresse übereinstimmen.";
            } else {
                $error[] = $this->l('ERROR_MESSAGE_BILLING_NOTEQUAL_SHIPPING');
            }
        }
        if ($this->isB2BInputted()) {
            if ($this->l('ERROR_MESSAGE_B2B_INPUTTED') == "ERROR_MESSAGE_B2B_INPUTTED") {
                $error[] = "Um mit Klarna Rechnungskauf oder Ratenkauf bezahlen zu können,
                muss die Lieferadresse mit der Rechnungsadresse übereinstimmen.";
            } else {
                $error[] = $this->l('ERROR_MESSAGE_B2B_INPUTTED');
            }
        }
        if (!$this->isBillingNameEqualsShipping()) {
            if ($this->l('ERROR_MESSAGE_SHOPPERNAME_NOTEQUAL_BILLINGANDSHIPPING') ==
                "ERROR_MESSAGE_SHOPPERNAME_NOTEQUAL_BILLINGANDSHIPPING"
            ) {
                $error[] = "Um mit Klarna Rechnungskauf oder Ratenkauf bezahlen zu können,
                muss die Person in Rechnungs- und Lieferadresse übereinstimmen.";
            } else {
                $error[] = $this->l('ERROR_MESSAGE_SHOPPERNAME_NOTEQUAL_BILLINGANDSHIPPING');
            }
        }
        if (!($this->isBillingNameEqualsShipping() && $this->isBillingCountryEqualsShipping())) {
            if ($this->l('ERROR_MESSAGE_COUNTRYANDNAME_BILLING_NOTEQUAL_SHIPPING') ==
                "ERROR_MESSAGE_COUNTRYANDNAME_BILLING_NOTEQUAL_SHIPPING"
            ) {
                $error[] = "Um mit Klarna Rechnungskauf oder Ratenkauf bezahlen zu können,
                müssen die Person und das Land in Rechnungs- und Lieferadresse übereinstimmen.";
            } else {
                $error[] = $this->l('ERROR_MESSAGE_COUNTRYANDNAME_BILLING_NOTEQUAL_SHIPPING');
            }
        }
        if (!$this->isDateOfBirthValid()) {
            if ($this->l('ERROR_PARAMETER_DOB') == "ERROR_PARAMETER_DOB") {
                $error[] = "Bitte geben Sie Ihr Geburtsdatum an, um Klarna nutzen zu können.";
            } else {
                $error[] = $this->l('ERROR_PARAMETER_DOB');
            }
        }
        if (!$this->isGenderValid()) {
            if ($this->l('ERROR_PARAMETER_GENDER') == "ERROR_PARAMETER_GENDER") {
                $error[] = "Please enter your gender to make payment with Klarna.";
            } else {
                $error[] = $this->l('ERROR_PARAMETER_GENDER');
            }
        }
        if (!$this->isPhoneValid()) {
            if ($this->l('ERROR_PARAMETER_PHONE') == "ERROR_PARAMETER_PHONE") {
                $error[] = "Bitte geben Sie Ihre Telefonnummer an, um Klarna nutzen zu können.";
            } else {
                $error[] = $this->l('ERROR_PARAMETER_PHONE');
            }
        }
        if (!$this->isDateOfBirthLowerThanToday()) {
            if ($this->l('ERROR_EASYCREDIT_FUTURE_DOB') == "ERROR_EASYCREDIT_FUTURE_DOB") {
                $error[] = "Das Geburtsdatum sollte nicht in der Zukunft liegen.";
            } else {
                $error[] = $this->l('ERROR_EASYCREDIT_FUTURE_DOB');
            }
        }
        if (!$error) {
            return false;
        }
        return $error;
    }

    protected function isBillingAddressEqualShipping()
    {
        $filters = array('address1', 'address2', 'city', 'country', 'postcode');

        return $this->isBillingEqualShipping($filters);
    }

    protected function isBillingNameEqualsShipping()
    {
        $filters = array('firstname', 'lastname');

        return $this->isBillingEqualShipping($filters);
    }

    protected function isBillingEqualShipping($filters)
    {
        $billingAddresses = new Address((int)$this->context->cart->id_address_invoice);
        $shippingAddresses = new Address((int)$this->context->cart->id_address_delivery);

        $filterBillingAddresses = array();
        $filterShippingAddresses = array();

        foreach ($filters as $value) {
            $filterBillingAddresses[] = $billingAddresses->$value;
            $filterShippingAddresses[] = $shippingAddresses->$value;
        }

        if ($filterBillingAddresses === $filterShippingAddresses) {
            return true;
        }
        return false;
    }

    protected function isB2BInputted()
    {
        $customerCompany = $this->context->customer->company;

        $billingAddresses = new Address((int)$this->context->cart->id_address_invoice);
        $billingCompany = $billingAddresses->company;
        $vatNumber = $billingAddresses->vat_number;

        if (!empty($customerCompany) || !empty($billingCompany) || !empty($vatNumber)) {
            return true;
        }
        return false;
    }

    protected function isEasycreditAmountAllowed()
    {
        $amount = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        $currencyobj = new Currency((int)$this->context->cart->id_currency);
        $currency = $currencyobj->iso_code;
        if ($amount < 200 || $amount > 10000 || $currency != 'EUR') {
            return true;
        }
        return false;
    }

    protected function isBillingCountryEqualsShipping()
    {
        $filters = array('country');

        return $this->isBillingEqualShipping($filters);
    }

    protected function isDateOfBirthValid()
    {
        $customerDateOfBirth = explode("-", $this->context->customer->birthday);

        $year = (int)$customerDateOfBirth[0];
        $month = (int)$customerDateOfBirth[1];
        $day = (int)$customerDateOfBirth[2];

        if ($year < 1900) {
            return false;
        }

        if ($month < 0 || $month > 12) {
            return false;
        }

        if ($day < 0 || $day > 31) {
            return false;
        }

        $valid = checkdate($month, $day, $year);

        if (!$valid) {
            return false;
        }
        return true;
    }

    protected function isDateOfBirthLowerThanToday()
    {
        $customerDateOfBirth = $this->context->customer->birthday;
        $dateOfBirth = strtotime($customerDateOfBirth);
        $today = strtotime(date('Y-m-d'));

        if ($dateOfBirth < $today) {
            return true;
        }
        return false;
    }

    protected function isGenderValid()
    {
        $gender = $this->context->customer->id_gender;

        if (empty($gender)) {
            return false;
        }
        return true;
    }

    protected function isPhoneValid()
    {
        $billingAddresses = new Address((int)$this->context->cart->id_address_invoice);
        $phone = $billingAddresses->phone;

        if (empty($phone)) {
            return false;
        }
        return true;
    }

    public function getTranslationByCountry($identifier)
    {
        $countryIsoCode = $this->getCountryIsoCode();
        switch ($countryIsoCode) {
            case 'DE':
            case 'AT':
                $langIsoCode = 'de';
                break;
            default:
                $langIsoCode = 'en';
                break;
        }

        $translation = VRpayecommercePaymentCore::getTranslationByLangIsoCode($identifier, $langIsoCode);

        if ($translation == $identifier) {
            $translation = $this->getTranslationByIdentifier($identifier);
        }

        return $translation;
    }

    public function getCountryIsoCode()
    {
        $address = new Address((int)$this->context->cart->id_address_invoice);
        $country = new Country($address->id_country);
        return $country->iso_code;
    }

    public function getKlarnaLocale()
    {
        $countryIsoCode = $this->getCountryIsoCode();
        switch ($countryIsoCode) {
            case 'AT':
                return 'de_at';
            default:
                return 'de_de';
        }
    }
}
