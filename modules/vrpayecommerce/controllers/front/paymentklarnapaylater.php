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

class VrpayecommercePaymentKlarnapaylaterModuleFrontController extends VrpayecommercePaymentKlarnaModuleFrontController
{
    /**
     * @var string
     */
    protected $payment_method = 'KLARNAPAYLATER';
    
    /**
     * @var string
     */
    protected $template_name = 'payment_form_klarnapaylater.tpl';
    
    /**
     * @var string
     */
    protected $payment_brand = 'KLARNA_INVOICE';

    /**
     * function init content
     * @return [type] [description]
     */
    public function initContent()
    {
        $this->parentInitContent();

        $this->context->cookie->vrpayecommerce_paymentBrand = $this->getPaymentMethod();

        if (!$this->isCartItemEmpty()) {
            $this->redirectErrorResponse('ERROR_PARAMETER_CART');
        }

        if (!$this->module->isOrderValid() || $this->module->getKlarnaDisabledErrors()) {
            $this->redirectErrorResponse('ERROR_ORDER_INVALID');
        }

        $this->context->smarty->assign(array(
            'paymentMethod' => $this->getPaymentMethod(),
            'this_path' => $this->module->getPathUri(),
            'title' => $this->module->getTranslationByCountry('FRONTEND_TT_KLARNAPAYLATER'),
            'subTitle' => $this->module->getTranslationByCountry('FRONTEND_TT_KLARNAPAYLATER_SUBTITLE'),
            'term1' => $this->module->getTranslationByCountry('FRONTEND_TT_KLARNA_TERM1'),
            'term2' => $this->module->getTranslationByCountry('FRONTEND_TT_KLARNA_TERM2'),
            'errorRequired' => $this->module->getTranslationByCountry('ERROR_MESSAGE_KLARNA_REQUIRED'),
            'eid' => Configuration::get('VRPAYECOMMERCE_KLARNAPAYLATER_MERCHANT_ID'),
            'locale' => $this->module->getKlarnaLocale()
        ));

        $this->setTemplate($this->getTemplateName());
    }
}
