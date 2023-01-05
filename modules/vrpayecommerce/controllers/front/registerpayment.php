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

class VrpayecommerceRegisterPaymentModuleFrontController extends VrpayecommercePaymentAbstractModuleFrontController
{
    public $auth = true;
    public $ssl = true;

    public function initContent()
    {
        $this->parentInitContent();

        $selectedPayment = Tools::getValue('selected_payment');
        $this->setPaymentMethod($selectedPayment);
        $isRedirect = $this->isRedirectPayment($selectedPayment);
        $lang = $this->getLanguage();
        
        $recurringTranscationParameters = $this->getRecurringPaymentParameters();

        $checkoutResult = VRpayecommercePaymentCore::getCheckoutResult($recurringTranscationParameters);
        if (!$checkoutResult['is_valid']) {
            $this->redirectErrorRecurring($checkoutResult['response']);
        } elseif (!isset($checkoutResult['response']['id'])) {
            $this->redirectErrorRecurring('ERROR_GENERAL_REDIRECT');
        }
        $paymentWidgetUrl = VRpayecommercePaymentCore::getPaymentWidgetUrl(
            $recurringTranscationParameters['server_mode'],
            $checkoutResult['response']['id']
        );
        
        $this->context->smarty->assign(array(
            'lang' => $lang,
            'paymentWidgetUrl' => $paymentWidgetUrl,
            'frameTestMode' => $this->module->getTestMode($this->payment_method),
            'paymentBrand' => $this->getPaymentBrand(),
            'redirect' => $isRedirect,
            'this_path' => Tools::getShopDomain(true, true).__PS_BASE_URI__.'modules/vrpayecommerce/',
            'cancelUrl' =>
                $this->context->link->getModuleLink('vrpayecommerce', 'paymentinformation', array(), true),
            'paymentResponseUrl' =>
                $this->context->link->getModuleLink(
                    'vrpayecommerce',
                    'paymentresponse',
                    array('payment_method' => $selectedPayment),
                    true
                )
        ));
        $this->setTemplate('module:vrpayecommerce/views/templates/front/register_payment.tpl');
    }

    public function getBreadcrumbLinks()
    {
        return $this->getBreadcrumbLinkMyAccount();
    }
}
