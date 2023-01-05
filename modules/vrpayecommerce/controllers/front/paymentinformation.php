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

class VrpayecommercePaymentInformationModuleFrontController extends ModuleFrontController
{
    public $auth = true;
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign(array(
            'isRecurringActive' => Configuration::get('VRPAYECOMMERCE_GENERAL_RECURRING') == 1,
            'isCardsSavedActive' => Configuration::get('VRPAYECOMMERCE_CCSAVED_ACTIVE') == 1,
            'isDDSavedActive' => Configuration::get('VRPAYECOMMERCE_DDSAVED_ACTIVE') == 1,
            'isPayPalSavedActive' => Configuration::get('VRPAYECOMMERCE_PAYPALSAVED_ACTIVE') == 1,
            'customerDataCC' => $this->getAccountPayment('CCSAVED', 'CC'),
            'customerDataDD' => $this->getAccountPayment('DDSAVED', 'DD'),
            'customerDataPAYPAL' =>  $this->getAccountPayment('PAYPALSAVED', 'PAYPAL'),
            'this_path' => Tools::getShopDomain(true, true).__PS_BASE_URI__.'modules/vrpayecommerce/',
            'paymentInformationUrl' =>
                $this->context->link->getModuleLink('vrpayecommerce', 'paymentinformation', array(), true),
            'registerPaymentUrl' =>
                $this->context->link->getModuleLink('vrpayecommerce', 'registerpayment', array(), true),
            'changePaymentUrl' =>
                $this->context->link->getModuleLink('vrpayecommerce', 'changepayment', array(), true),
            'deletePaymentUrl' =>
                $this->context->link->getModuleLink('vrpayecommerce', 'deletepayment', array(), true)
        ));

        $this->setTemplate('module:vrpayecommerce/views/templates/front/paymentinformation.tpl');
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();

        return $breadcrumb;
    }

    public function getAccountPayment($paymentMethod, $paymentGroup)
    {
        $serverMode = Configuration::get('VRPAYECOMMERCE_'.$paymentMethod.'_SERVER');
        $channelId = Configuration::get('VRPAYECOMMERCE_'.$paymentMethod.'_CHANNEL');

        $query = "SELECT * FROM `vrpayecommerce_payment_recurring`
                  WHERE `cust_id` = '".(int)$this->context->cookie->id_customer."' 
                  AND `payment_group` = '".$paymentGroup."' 
                  AND `server_mode` = '".$serverMode."' 
                  AND `channel_id` = '".$channelId."'";
        $AccountPayment = Db::getInstance()->ExecuteS($query);
        return $AccountPayment;
    }

    public function postProcess()
    {
        $set_default = Tools::getValue('set_default');
        $id =  Tools::getValue('id');
        $payment_group = Tools::getValue('payment_group');

        if ($set_default) {
            $this->setNotDefaultPayment($payment_group);
            $this->setDefaultPayment($id, $payment_group);
        }
    }

    public function setNotDefaultPayment($payment_group)
    {
        $sql = "UPDATE vrpayecommerce_payment_recurring SET payment_default = '0'
                WHERE payment_default = '1' AND payment_group ='".pSQL($payment_group)."' ";
        if (!Db::getInstance()->execute($sql)) {
            die('Erreur etc.');
        }
    }

    public function setDefaultPayment($id, $payment_group)
    {
        $sql = "UPDATE vrpayecommerce_payment_recurring SET payment_default = '1'
                WHERE id = '".(int) $id."' AND payment_group ='".pSQL($payment_group)."' ";
        if (!Db::getInstance()->execute($sql)) {
            die('Erreur etc.');
        }
    }
}
