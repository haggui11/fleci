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

require_once(dirname(__FILE__).'/paymentabstract.php');

class VrpayecommercePaymentEasycreditModuleFrontController extends VrpayecommercePaymentAbstractModuleFrontController
{
    /**
     * @var string
     */
    protected $payment_method = 'EASYCREDIT';
    
    /**
     * @var string
     */
    protected $template_name = 'server_to_server.tpl';
    
    /**
     * @var string
     */
    protected $payment_type = 'PA';
    
    /**
     * @var string
     */
    protected $payment_brand = 'RATENKAUF';

    /**
     * function init content
     * @return void
     */
    public function initContent()
    {
        parent::initContent();

        $this->context->cookie->vrpayecommerce_paymentBrand = $this->getPaymentMethod();

        $transactionParameter = $this->getTransactionParameter();
        $this->context->cookie->vrpayecommerce_transaction = serialize($transactionParameter);

        $serverToServerResponse = VRpayecommercePaymentCore::getServerToServerResponse($transactionParameter);

        if ($serverToServerResponse['is_valid']) {
            $transaction_result =
                VRpayecommercePaymentCore::getTransactionResult(
                    $serverToServerResponse['response']['result']['code']
                );
            if ($transaction_result == 'NOK') {
                $error_identifier =
                    VRpayecommercePaymentCore::getErrorIdentifier(
                        $serverToServerResponse['response']['result']['code']
                    );
                $this->redirectWithErrorMessage($error_identifier);
            }

            $this->context->smarty->assign(array(
                'redirect_url' => $serverToServerResponse['response']['redirect']['url'],
                'parameters' => $serverToServerResponse['response']['redirect']['parameters'],
            ));
        } else {
            $this->redirectWithErrorMessage($serverToServerResponse['response']);
        }

        $this->setTemplate($this->getTemplateName());
    }

    /**
     * return transaction parameter to be sent to the gateway for easycredit payment method
     * @return array
     */
    public function getTransactionParameter()
    {
        $transactionParameter = parent::getTransactionParameter();
        $cart = $this->getCartItems();

        $transactionParameter['customParameters']['RISK_ANZAHLBESTELLUNGEN'] = $this->getOrderCount();
        $transactionParameter['customParameters']['RISK_BESTELLUNGERFOLGTUEBERLOGIN'] =
            $this->checkCustomerLogin();
        $transactionParameter['customParameters']['RISK_KUNDENSTATUS'] = $this->getRiskKundenStatus();
        $transactionParameter['customParameters']['RISK_KUNDESEIT'] = $this->getCustomerCreatedDate();
        $transactionParameter['paymentBrand'] = $this->getPaymentBrand();

        $address = new Address((int)$this->context->cart->id_address_invoice);
        $country = new Country($address->id_country);

        $transactionParameter['shipping']['city'] = $address->city;
        $transactionParameter['shipping']['country'] = $country->iso_code;
        $transactionParameter['shipping']['postcode'] = $address->postcode;
        $transactionParameter['shipping']['street1'] = $address->address1.$address->address2;

        $transactionParameter['shopperResultUrl'] =
            $this->context->link->getModuleLink(
                'vrpayecommerce',
                'servertoserver',
                array('response' => 'servertoserver', 'payment_method' => $this->getPaymentMethod())
            );

        $transactionParameter = array_merge($transactionParameter, $cart);

        return $transactionParameter;
    }
}
