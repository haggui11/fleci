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

require_once(dirname(__FILE__).'/../../core/core.php');

abstract class VrpayecommercePaymentAbstractModuleFrontController extends ModuleFrontController
{
    protected $payment_method = '';
    protected $template_name = '';
    protected $payment_type = '';
    protected $payment_brand = '';
    protected $recurring = false;
    protected $recurring_group = '';
    protected $cust_id = "cust_id";
    public $ssl = true;

    public function setPaymentMethod($paymentMethod)
    {
        $this->payment_method = $paymentMethod;

        switch ($this->payment_method) {
            case 'CCSAVED':
                $this->payment_brand = $this->getPaymentBrandCards();
                $this->recurring_group = 'CC';
                $this->payment_type = Configuration::get('VRPAYECOMMERCE_'.$this->getPaymentMethod().'_MODE');
                break;
            case 'DDSAVED':
                $this->payment_brand = "DIRECTDEBIT_SEPA";
                $this->recurring_group = 'DD';
                $this->payment_type = Configuration::get('VRPAYECOMMERCE_'.$this->getPaymentMethod().'_MODE');
                break;
            case 'PAYPALSAVED':
                $this->payment_brand = "PAYPAL";
                $this->recurring_group = 'PAYPAL';
                $this->payment_type = Configuration::get('VRPAYECOMMERCE_'.$this->getPaymentMethod().'_MODE');
                break;
        }
    }

    public function getPaymentBrandCards()
    {
        $brand = Configuration::get('VRPAYECOMMERCE_'.$this->getPaymentMethod().'_CARDS');
        return str_replace(',', ' ', $brand);
    }
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    public function getTemplateName()
    {
        return 'module:vrpayecommerce/views/templates/front/'.$this->template_name;
    }

    public function getPaymentType()
    {
        if ($this->getRecurringGroup() != 'PAYPAL') {
            return $this->payment_type;
        }
    }

    public function getPaymentBrand()
    {
        return $this->payment_brand;
    }

    public function getRecurring()
    {
        return $this->recurring;
    }

    public function getRecurringGroup()
    {
        return $this->recurring_group;
    }

    public function getPaymentIsPartial()
    {
        if (!Configuration::get('VRPAYECOMMERCE_PAYDIREKT_PAYMENT_IS_PARTIAL')) {
            return 'false';
        }

        return 'true';
    }

    /**
     * load prestashop function initContent from class ModuleFrontController
     * @return void
     */
    protected function parentInitContent()
    {
        parent::initContent();
    }

    /**
     * function init content
     * @return void
     */
    public function initContent()
    {
        $this->parentInitContent();

        $this->context->cookie->vrpayecommerce_paymentBrand = $this->getPaymentMethod();
        $lang = $this->getLanguage();

        $transactionParameter = $this->getTransactionParameter();
        $this->context->cookie->vrpayecommerce_transaction = serialize($transactionParameter);

        $checkoutResult = VRpayecommercePaymentCore::getCheckoutResult($transactionParameter);

        if (!$checkoutResult['is_valid']) {
            $this->redirectWithErrorMessage($checkoutResult['response']);
        } elseif (!isset($checkoutResult['response']['id'])) {
            $this->redirectWithErrorMessage('ERROR_GENERAL_REDIRECT');
        } else {
            $paymentWidgetUrl = VRpayecommercePaymentCore::getPaymentWidgetUrl(
                $transactionParameter['server_mode'],
                $checkoutResult['response']['id']
            );
            
            $paymentWidgetContent =
                VRpayecommercePaymentCore::getGatewayResponse($paymentWidgetUrl, $transactionParameter['server_mode']);

            if (!$paymentWidgetContent['is_valid'] ||
                strpos($paymentWidgetContent['response'], 'errorDetail') !==
                false) {
                $this->redirectWithErrorMessage('ERROR_GENERAL_REDIRECT');
            }

            $merchantLocation = false;
            if ($this->payment_method == 'CC' || $this->payment_method == 'CCSAVED') {
                $merchantLocation = $this->module->getMerchantLocation();
            }

            $this->context->smarty->assign(array(
                'fullname' => $this->context->customer->firstname ." ". $this->context->customer->lastname,
                'lang'    => $lang,
                'paymentWidgetUrl' => $paymentWidgetUrl,
                'merchantLocation' => $merchantLocation,
                'frameTestMode' => $this->module->getTestMode($this->payment_method),
                'paymentBrand' => $this->getPaymentBrand(),
                'registrations' => $this->getRegisteredPayment(),
                'recurring' => $this->getRecurring(),
                'total' => $this->context->cart->getOrderTotal(true, Cart::BOTH),
                'cancelUrl' => $this->context->link->getPageLink('order', true, null, array('step' => '3')),
                'responseUrl' => $this->context->link->getModuleLink(
                    'vrpayecommerce',
                    'validation',
                    array('payment_method' => $this->getPaymentMethod()),
                    true
                ),
                'this_path' => $this->module->getPathUri(),
                'this_path_ssl' =>
                Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
            ));
        }

        $this->setTemplate($this->getTemplateName());
    }

    /**
     * redirect and show the error message
     * @param  string $message
     * @return void
     */
    public function redirectWithErrorMessage($message)
    {
        $this->errors[] = $this->module->getErrorMessage($message);
        $this->redirectWithNotifications(
            $this->context->link->getPageLink('order', true, null, array('step' => '3'))
        );
    }

    /**
     * return transaction parameter to be sent to the gateway
     * @return array
     */
    public function getTransactionParameter()
    {
        $currencyobj = new Currency((int)$this->context->cart->id_currency);
        $currency = $currencyobj->iso_code;

        $paymentMethod = $this->getPaymentMethod();
        $credentials = $this->module->getCredentials($this->payment_method);
        $customer = $this->getCustomerParameter();
        $payment = $this->getPaymentParameter($currency, $paymentMethod);
        $transactionParameter = array_merge($credentials, $customer, $payment);

        if ($this->recurring) {
            $recurringParameter = $this->getRecurringPrameter($paymentMethod, $transactionParameter);
            $transactionParameter = array_merge($transactionParameter, $recurringParameter);
        }

        $transactionParameter['customer_ip'] = $this->getCustomerIP();

        if(Configuration::get('VRPAYECOMMERCE_GENERAL_VERSION_TRACKER'))
        {
            if(!isset($transactionParameter['customParameters']))
            {
                $transactionParameter['customParameters'] = array();
            }
            $transactionParameter['customParameters'] = array_merge($transactionParameter['customParameters'], array(
                'PLUGIN_email'             => Configuration::get('VRPAYECOMMERCE_GENERAL_MERCHANTEMAIL'),
                'PLUGIN_accountId'         => Configuration::get('VRPAYECOMMERCE_GENERAL_MERCHANTNO'),
                'PLUGIN_shopUrl'           => Configuration::get('VRPAYECOMMERCE_GENERAL_SHOPURL'),
                'PLUGIN_shopSystem'        => 'Prestashop',
                'PLUGIN_shopSystemVersion' => constant('_PS_VERSION_'),
                'PLUGIN_version'           => $this->module->version,
                'PLUGIN_outletLocation'    => Configuration::get('VRPAYECOMMERCE_GENERAL_MERCHANTLOCATION'),
                'PLUGIN_mode'              => $this->module->getServerMode($this->payment_method),
            ));
        }

        return $transactionParameter;
    }

    private function getPaymentParameter($currency, $paymentMethod)
    {
        $this->context->cookie->vrpayecommerce_transactionid = date('ymd') . $this->context->cart->id;
        $transaction = array();
        $transaction['transaction_id'] = $this->context->cookie->vrpayecommerce_transactionid;
        $transaction['payment_type'] = $this->getPaymentType();

        if ($this->getPaymentMethod() == 'PAYPALSAVED') {
            unset($transaction['payment_type']);
        }
        $transaction['amount'] = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        $transaction['currency'] = $currency;
        $transaction['test_mode'] = $this->module->getTestMode($paymentMethod);

        return $transaction;
    }

    private function getRecurringPrameter($paymentMethod, $transaction)
    {
        $recurringParameter = $this->getPaymentReference();
        $recurringParameter['payment_registration'] = 'true';
        if ($paymentMethod == 'CCSAVED') {
            $recurringParameter['3D']['amount'] = $transaction['amount'];
            $recurringParameter['3D']['currency'] = $transaction['currency'];
        }

        return $recurringParameter;
    }

    /**
     * Get total order from database
     *
     * @return int
     */
    protected function getOrderCount()
    {
        $customerId = $this->context->cookie->id_customer;
        $query = "SELECT COUNT(id_order) as order_count
                  FROM `"._DB_PREFIX_."orders`
                  WHERE id_customer = ".$customerId ;
        $results = Db::getInstance()->ExecuteS($query);

        if ($results[0]['order_count'] > 0) {
             return $results[0]['order_count'];
        }
        return 0;
    }

    /**
     * Check customer login status and return as string
     *
     * @return string
     */
    protected function checkCustomerLogin()
    {
        $customerId = $this->context->cookie->id_customer;
        if (isset($customerId) === false) {
            return 'false';
        } else {
            return 'true';
        }
    }

    /**
     * Get risk kunden status
     *
     * @return string|boolean
     */
    protected function getRiskKundenStatus()
    {
        if ($this->getOrderCount() > 0) {
            return 'BESTANDSKUNDE';
        }
        return 'NEUKUNDE';
    }

    /**
     * Get customer created date
     *
     * @return string|boolean
     */
    protected function getCustomerCreatedDate()
    {
        $customerId = (int) $this->context->cookie->id_customer;

        $query = "SELECT date_add FROM `"._DB_PREFIX_."customer` WHERE id_customer = ".$customerId;
        $results = Db::getInstance()->ExecuteS($query);

        if (isset($results[0]['date_add'])) {
            return date('Y-m-d', strtotime($results[0]['date_add']));
        }
        return date('Y-m-d');
    }

    /**
     * Get shipping data
     *
     * @return string|boolean
     */
    protected function getShippingData($id_address)
    {
        $query = "SELECT a.*, b. FROM `"._DB_PREFIX_."address` WHERE id_address = ".$id_address;
        $results = Db::getInstance()->ExecuteS($query);

        return $results;
    }

    public function getCustomerParameter()
    {
        $address = new Address((int)$this->context->cart->id_address_delivery);
        $country = new Country($address->id_country);

        $customer = array();
        $customer['customer']['first_name'] = $this->context->customer->firstname;
        $customer['customer']['last_name'] = $this->context->customer->lastname;
        $customer['customer']['email'] = $this->context->customer->email;
        $customer['customer']['phone'] = $this->getPhoneNumber();
        $customer['billing']['street'] = $address->address1.$address->address2;
        $customer['billing']['city'] = $address->city;
        $customer['billing']['zip'] = $address->postcode;
        $customer['billing']['country_code'] = $country->iso_code;

        $customer['customer']['birthdate'] = $this->context->customer->birthday;
        $customer['customer']['sex'] = ($this->context->customer->id_gender == '1')? 'M':'F';

        if ($address->vat_number)
        {
            $customer['customParameters']['buyerCompanyVat'] = $address->vat_number;
        }

        return $customer;
    }

    public function getCartItems()
    {
        $percent = 100;
        $cartItems = array();
        $carts = $this->context->cart->getProducts();

        $key = 0;
        $highestTaxRate = 0;
        foreach ($carts as $key => $cart) {
            $priceWithTax = number_format($cart['price_without_reduction'], 2);
            $priceWithDisc = number_format($cart['price_with_reduction'], 2);
            $priceWithoutTax = number_format($cart['price_with_reduction_without_tax'], 2);

            $discount = (($priceWithTax-$priceWithDisc) * $percent)/$priceWithTax;

            // first calculate tax rate and then format to two decimals
            $tax = number_format($cart['price_with_reduction'] / $cart['price_with_reduction_without_tax'] - 1, 2);

            $cartItems['cartItems'][$key]['merchant_item_id'] = $cart['id_product'];
            $cartItems['cartItems'][$key]['discount'] = $discount;
            $cartItems['cartItems'][$key]['quantity'] = $cart['cart_quantity'];
            $cartItems['cartItems'][$key]['name'] = $cart['name'];
            $cartItems['cartItems'][$key]['price'] = $priceWithTax;
            $cartItems['cartItems'][$key]['tax'] = $tax;
            $cartItems['cartItems'][$key]['totalTaxAmount'] = $cart['total_wt'] - $cart['total'];
            $cartItems['cartItems'][$key]['totalAmount'] = $cart['total_wt'];
            $highestTaxRate = max($highestTaxRate, $tax);
        }

        if($shippingCosts = $this->context->cart->getTotalShippingCost())
        {
            $cartItems['cartItems'][++$key] = [
                'merchant_item_id' => 'shipping',
                'discount' => 0,
                'quantity' => 1,
                'name' => 'Shipping',
                'price' => $shippingCosts,
                'tax' => $highestTaxRate,
                'totalTaxAmount' => $shippingCosts - ($shippingCosts / (1 + $highestTaxRate)),
                'totalAmount' => $shippingCosts,
            ];
        }

        return $cartItems;
    }

    public function getPaymentReference()
    {
        $registeredPayments = $this->getRegisteredPayment();

        $paymentReference = array();
        foreach ($registeredPayments as $key => $value) {
            $paymentReference['registrations'][$key ] = $value['ref_id'];
        }

        return $paymentReference;
    }

    public function getRegisteredPayment()
    {
        $customerId = (int)$this->context->cookie->id_customer;
        $paymentGroup = $this->getRecurringGroup();
        $credentials = $this->module->getCredentials($this->getPaymentMethod());

        $query = "SELECT * FROM `vrpayecommerce_payment_recurring`
                  WHERE `cust_id` = '".$customerId."'
                  AND `payment_group` = '".$paymentGroup."'
                  AND `server_mode` = '".$credentials['server_mode']."'
                  AND `channel_id` = '".$credentials['channel_id']."'";
        $registeredPayments = Db::getInstance()->ExecuteS($query);
        return $registeredPayments;
    }

    public function getPhoneNumber()
    {
        $customerId = (int)$this->context->cookie->id_customer;
        $query = "SELECT `phone` FROM `"._DB_PREFIX_."address`
                   WHERE `id_customer` = '".$customerId."' AND `deleted` = 0 ";
        $phoneNumber = Db::getInstance()->ExecuteS($query);

        return $phoneNumber[0]['phone'];
    }

    public function getReferenceId($id)
    {
        $customerId = (int)$this->context->cookie->id_customer;

        $query = "SELECT ref_id FROM `vrpayecommerce_payment_recurring`
                  WHERE `cust_id` = '".$customerId."' AND `id` = '".(int) $id."' ";
        $ReferenceId = Db::getInstance()->ExecuteS($query);
        return $ReferenceId[0]["ref_id"];
    }

    public function getCustomerIP()
    {
        $customerIP = $_SERVER['REMOTE_ADDR'];
        if ($customerIP == '::1') {
            $customerIP = '127.0.0.1';
        }

        return $customerIP;
    }

    public function getRegisterAmount()
    {
        return Configuration::get('VRPAYECOMMERCE_'.$this->getPaymentMethod().'_AMOUNT');
    }

    public function getRecurringPaymentParameters($referenceId = false)
    {
        $transactionData = array_merge(
            $this->module->getCredentials($this->payment_method),
            $this->getCustomerParameter()
        );
        $currency = $this->context->currency;

        $transactionData['amount'] = $this->getRegisterAmount();
        $transactionData['currency'] = $currency->iso_code;
        $transactionData['customer_ip'] = $this->getCustomerIP();
        $transactionData['3D']['amount'] = $this->get3dAmount($this->payment_method);
        $transactionData['3D']['currency'] = $this->get3dCurrency($this->payment_method, $currency);
        $transactionData['test_mode'] = $this->module->getTestMode($this->payment_method);
        $transactionData['payment_type'] = $this->getPaymentType();
        $transactionData['payment_recurring'] = 'INITIAL';
        $transactionData['payment_registration'] = 'true';
        $transactionData['transaction_id'] = $this->getTransactionIdbyReference($referenceId);

        if(Configuration::get('VRPAYECOMMERCE_GENERAL_VERSION_TRACKER'))
        {
            if(!isset($transactionData['customParameters']))
            {
                $transactionData['customParameters'] = array();
            }
            $transactionData['customParameters'] = array_merge($transactionData['customParameters'], array(
                'PLUGIN_email'             => Configuration::get('VRPAYECOMMERCE_GENERAL_MERCHANTEMAIL'),
                'PLUGIN_accountId'         => Configuration::get('VRPAYECOMMERCE_GENERAL_MERCHANTNO'),
                'PLUGIN_shopUrl'           => Configuration::get('VRPAYECOMMERCE_GENERAL_SHOPURL'),
                'PLUGIN_shopSystem'        => 'Prestashop',
                'PLUGIN_shopSystemVersion' => constant('_PS_VERSION_'),
                'PLUGIN_version'           => $this->module->version,
                'PLUGIN_outletLocation'    => Configuration::get('VRPAYECOMMERCE_GENERAL_MERCHANTLOCATION'),
                'PLUGIN_mode'              => $this->module->getServerMode($this->payment_method),
            ));
        }

        return $transactionData;
    }

    protected function get3dAmount($paymentMethod)
    {
        if ($paymentMethod == 'CCSAVED') {
            return $this->getRegisterAmount();
        }
    }

    protected function get3dCurrency($paymentMethod, $currency)
    {
        if ($paymentMethod == 'CCSAVED') {
            return $currency->iso_code;
        }
    }

    protected function getTransactionIdbyReference($referenceId)
    {
        if (!empty($referenceId)) {
            return $this->getReferenceId($referenceId);
        } else {
            return (int)$this->context->cookie->id_customer;
        }
    }

    protected function isRedirectPayment($selected_payment)
    {
        if ($selected_payment == 'PAYPALSAVED') {
            return  true;
        }

        return false;
    }

    protected function getLanguage()
    {
        $langobj = new Language((int)$this->context->cart->id_lang);
        $langs = $langobj->iso_code;

        switch ($langs) {
            case 'de':
                $lang = $langs;
                break;

            default:
                $lang='en';
        }

        return $lang;
    }

    protected function redirectErrorRecurring($errorMessage = false, $errorAction = false)
    {
        $error = '';
        if ($errorAction) {
            $error .= $this->module->getErrorMessage($errorAction).' : ';
        }
        if ($errorMessage) {
            $error .= $this->module->getErrorMessage($errorMessage);
        }
        $this->errors[] = $error;
        $this->redirectWithNotifications(
            $this->context->link->getModuleLink('vrpayecommerce', 'paymentinformation', array(), true)
        );
    }

    protected function redirectSuccessRecurring($successMessage = false)
    {
        if ($successMessage) {
            $this->success[] = $this->module->getSuccessMessage($successMessage);
        }
        $this->redirectWithNotifications(
            $this->context->link->getModuleLink('vrpayecommerce', 'paymentinformation', array(), true)
        );
    }

    protected function getBreadcrumbLinkMyAccount()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();

        return $breadcrumb;
    }

    /**
     * check if a cart not empty
     * @return boolean
     */
    public function isCartItemEmpty()
    {
        $cartItem = $this->getCartItems();

        if (empty($cartItem['cartItems'])) {
            return false;
        }
        return true;
    }

    public function redirectErrorResponse($returnMessage)
    {
        $this->errors[] = $this->module->getErrorMessage($returnMessage);
        $this->redirectWithNotifications(
            $this->context->link->getPageLink('order', true, null, array('step' => '3'))
        );
    }
}
