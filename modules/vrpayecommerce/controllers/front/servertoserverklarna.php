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

require_once(dirname(__FILE__).'/paymentklarna.php');
//require_once(dirname(__FILE__).'/../../core/versiontracker.php');

class VrpayecommerceServertoserverklarnaModuleFrontController extends VrpayecommercePaymentKlarnaModuleFrontController
{
    protected $payment_type = 'PA';
    protected $servertoserver_response = array();
    protected $template_name = 'server_to_server_confirmation.tpl';

    /**
     * set the payment method
     * @param string $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->payment_method = $paymentMethod;

        switch ($paymentMethod) {
            case 'KLARNAPAYLATER':
                $this->payment_brand = 'KLARNA_INVOICE';
                break;
            case 'KLARNASLICEIT':
                $this->payment_brand = "KLARNA_INSTALLMENTS";
                break;
        }
    }

    /**
     * get server to server parameters
     *
     * @return array
     */
    public function getServerToServerParameters()
    {
        $serverToServerParameters = array();
        $contextLink = $this->context->link;

        $serverToServerParameters['paymentType'] = $this->payment_type;
        $serverToServerParameters['paymentBrand'] = $this->payment_brand;
        $serverToServerParameters['shopperResultUrl'] = $contextLink->getModuleLink(
            'vrpayecommerce',
            'validation',
            array('payment_method' => $this->payment_method),
            true
        );
        return $serverToServerParameters;
    }

    /**
     * return checkout parameter to be sent to the gateway
     * @return array
     */
    public function getCheckoutParameter()
    {
        $transactionParameter = $this->getTransactionParameter();
        $this->context->cookie->vrpayecommerce_transaction = serialize($transactionParameter);

        $checkoutParameters = array_merge_recursive(
            $transactionParameter,
            $this->getServerToServerParameters()
        );
        return $checkoutParameters;
    }

    /**
     * Handles post process
     * This function is the prestashop function
     * @return void
     */
    public function postProcess()
    {
        $paymentMethod = Tools::getValue('payment_method');
        $this->setPaymentMethod($paymentMethod);

        if (!$this->isCartItemEmpty()) {
            $this->redirectErrorResponse('ERROR_PARAMETER_CART');
        }

        if (!($paymentMethod == 'KLARNAPAYLATER' || $paymentMethod == 'KLARNASLICEIT')) {
            $this->redirectErrorResponse('ERROR_ORDER_INVALID');
        }
        if ($paymentMethod == 'KLARNAPAYLATER' || $paymentMethod == 'KLARNASLICEIT') {
            if ($this->module->getKlarnaDisabledErrors()) {
                $this->redirectErrorResponse('ERROR_ORDER_INVALID');
            }
            if (!$this->isCartItemEmpty()) {
                $this->redirectErrorResponse('ERROR_PARAMETER_CART');
            }
        }
        if ($paymentMethod == 'KLARNASLICEIT') {
            $pclassId = Configuration::get('VRPAYECOMMERCE_KLARNASLICEIT_PCLASS_ID');
            if (empty($pclassId)) {
                $this->redirectErrorResponse('ERROR_MESSAGE_PCLASS_REQUIRED');
            }
        }
       
        $checkoutParameters = $this->getCheckoutParameter();
        $servertoserverResponse = VRpayecommercePaymentCore::getServerToServerResponse($checkoutParameters);

        if ($servertoserverResponse['is_valid']) {
            if (isset($servertoserverResponse['response']['redirect']['url'])) {
                $this->servertoserverResponse = $servertoserverResponse['response'];
            } else {
//                if (Configuration::get('VRPAYECOMMERCE_GENERAL_VERSION_TRACKER')) {
//                    VersionTracker::sendVersionTracker($this->module->getVersionData($paymentMethod));
//                }
                $resultCode = $servertoserverResponse['response']['result']['code'];
                $transactionResult = VRpayecommercePaymentCore::getTransactionResult($resultCode);

                if ($transactionResult == 'ACK') {
                    $this->doSuccessPayment($paymentMethod, $servertoserverResponse['response']);
                } else {
                    if ($transactionResult == 'NOK') {
                        $errorIdentifier = VRpayecommercePaymentCore::getErrorIdentifier($resultCode);
                    } else {
                        $errorIdentifier = 'ERROR_UNKNOWN';
                    }
                    $this->redirectErrorResponse($errorIdentifier);
                }
            }
        } else {
             $this->redirectErrorResponse($servertoserverResponse['response']);
        }
    }

    /**
     * the process after a success payment
     * @param  string $paymentMethod
     * @param  array $paymentStatus
     * @return void
     */
    private function doSuccessPayment($paymentMethod, $paymentStatus)
    {
        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);

        $mailVars = array();
        $currency = $this->context->currency;
        $orderTotal = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $paymentType = $this->module->getPaymentTypeResponse($paymentStatus);
        $orderStatus = $this->module->getOrderStatus($paymentType);
        $paymentDescription = $this->module->getPaymentDescription($paymentMethod);

        $this->module->saveTransactionLog($paymentMethod, $paymentStatus, 'ACK');

        $this->module->validateOrder(
            (int)$cart->id,
            $orderStatus,
            $orderTotal,
            $paymentDescription,
            null,
            $mailVars,
            (int)$currency->id,
            false,
            $customer->secure_key
        );
        $orderId = $this->module->currentOrder;
        $this->module->updateOrderId($paymentStatus['id'], $orderId);

        Tools::redirect(
            'index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='
            .(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key
        );
    }

    /**
     * Initializes the content.
     * This function is the prestashop function
     * @return void
     */
    public function initContent()
    {
        $this->context->smarty->assign(array(
            'redirectUrl' => $this->servertoserverResponse['redirect']['url'],
            'redirectParameters' => $this->servertoserverResponse['redirect']['parameters']
        ));

        $this->setTemplate($this->getTemplateName());
    }
}
