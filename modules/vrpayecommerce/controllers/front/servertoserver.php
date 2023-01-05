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
//require_once(dirname(__FILE__).'/../../core/versiontracker.php');
use PrestaShop\PrestaShop\Adapter\Cart\CartPresenter;

class VrpayecommerceServerToServerModuleFrontController extends VrpayecommercePaymentAbstractModuleFrontController
{
    protected $template_name = 'server_to_server_confirmation.tpl';
    protected $id_product;
    protected $id_product_attribute;
    protected $id_address_delivery;
    protected $customization_id;
    protected $qty;
    public $ssl = true;

    public function initContent()
    {
        $this->parentInitContent();
        $checkoutId = Tools::getValue('id');
        $paymentMethod = Tools::getValue('payment_method');
        $transactionData = $this->module->getCredentials($paymentMethod);

        if (Tools::getIsset('response') && Tools::getValue('response') == 'servertoserver') {
            $serverToServerStatus =
                VRpayecommercePaymentCore::getPaymentServerToServerStatus($checkoutId, $transactionData);
        } else {
            $this->errors[] = $this->module->getErrorMessage('ERROR_UNKNOWN');
            $this->redirectWithNotifications(
                $this->context->link->getPageLink('order', true, null, array('step' => '3'))
            );
        }

        if ($serverToServerStatus['is_valid']) {
            $transactionResult =
                VRpayecommercePaymentCore::getTransactionResult($serverToServerStatus['response']['result']['code']);
        
            if ($transactionResult == "NOK") {
                $returnMessage =
                    VRpayecommercePaymentCore::getErrorIdentifier($serverToServerStatus['response']['result']['code']);
                $this->errors[] = $this->module->getErrorMessage($returnMessage);
                $this->redirectWithNotifications(
                    $this->context->link->getPageLink('order', true, null, array('step' => '3'))
                );
            }
        } else {
            $this->errors[] = $this->module->getErrorMessage($serverToServerStatus['response']);
            $this->redirectWithNotifications(
                $this->context->link->getPageLink('order', true, null, array('step' => '3'))
            );
        }
        

        $presenter = new CartPresenter();
        $presented_cart = $presenter->present($this->context->cart);

        $this->context->smarty->assign(array(
            'tilgungsplanText' => $serverToServerStatus['response']['resultDetails']['tilgungsplanText'],
            'linkInfo'  => $serverToServerStatus['response']['resultDetails']['vorvertraglicheInformationen'],
            'sumOfInterest' => $serverToServerStatus['response']['resultDetails']['ratenplan.zinsen.anfallendeZinsen'],
            'curency' => $this->context->currency->sign,
            'curency_iso'   => $this->context->currency->iso_code,
            'amount'    => $serverToServerStatus['response']['amount'],
            'total' => $serverToServerStatus['response']['resultDetails']['ratenplan.gesamtsumme'],
            'order' => $presented_cart,
            'paymentMethod' => $paymentMethod,
            'formUrl'   =>
                $this->context->link->getModuleLink(
                    'vrpayecommerce',
                    'validation',
                    array('payment_method' => $paymentMethod, 'id'   => $checkoutId)
                ),
            'cancelUrl' =>  $this->context->link->getPageLink('order', true, null, array('step' => '3'))
        ));

        $this->setTemplate($this->getTemplateName());
    }
}
