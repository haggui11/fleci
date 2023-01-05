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

class VrpayecommercePaymentEnterpayModuleFrontController extends VrpayecommercePaymentAbstractModuleFrontController
{
    /**
     * @var string
     */
    protected $payment_method = 'ENTERPAY';
    
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
    protected $payment_brand = 'ENTERPAY';

    /**
     * function init content
     * @return void
     */
    public function initContent()
    {
        parent::parentInitContent();

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
     * return transaction parameter to be sent to the gateway for enterpay payment method
     * @return array
     */
    public function getTransactionParameter()
    {
        $transactionParameter = parent::getTransactionParameter();
        $cart = $this->getCartItems();

        $transactionParameter['paymentBrand'] = $this->getPaymentBrand();

        $address = new Address((int)$this->context->cart->id_address_invoice);
        $country = new Country($address->id_country);

        $transactionParameter['shipping']['city'] = $address->city;
        $transactionParameter['shipping']['country'] = $country->iso_code;
        $transactionParameter['shipping']['postcode'] = $address->postcode;
        $transactionParameter['shipping']['street1'] = $address->address1.$address->address2;

        $contextLink = $this->context->link;
        $transactionParameter['shopperResultUrl'] = $contextLink->getModuleLink(
            'vrpayecommerce',
            'validation',
            array('payment_method' => $this->payment_method),
            true
        );

        $transactionParameter = array_merge($transactionParameter, $cart);

        return $transactionParameter;
    }
}
