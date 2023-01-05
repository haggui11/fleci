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

class VrpayecommerceDeletePaymentModuleFrontController extends VrpayecommercePaymentAbstractModuleFrontController
{
    public $auth = true;
    public $ssl = true;

    public function initContent()
    {
        $this->parentInitContent();
       
        $selected_payment = Tools::getValue('selected_payment');
        $id = Tools::getValue('id');

        $this->context->smarty->assign(array(
            'id' => $id,
            'selected_payment' => $selected_payment,
            'this_path' => Tools::getShopDomain(true, true).__PS_BASE_URI__.'modules/vrpayecommerce/',
            'cancelUrl' =>
                $this->context->link->getModuleLink('vrpayecommerce', 'paymentinformation', array(), true),
            'deleteResponseUrl' =>
                $this->context->link->getModuleLink('vrpayecommerce', 'deleteresponse', array(), true)
        ));
           
        $this->setTemplate('module:vrpayecommerce/views/templates/front/delete_payment.tpl');
    }

    public function getBreadcrumbLinks()
    {
        return $this->getBreadcrumbLinkMyAccount();
    }
}
