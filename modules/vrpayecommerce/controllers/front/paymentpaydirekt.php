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

class VrpayecommercePaymentPaydirektModuleFrontController extends VrpayecommercePaymentAbstractModuleFrontController
{
    /**
     * @var string
     */
    protected $payment_method = 'PAYDIREKT';

    /**
     * @var string
     */
    protected $template_name = 'payment_redirect.tpl';

    /**
     * @var string
     */
    protected $payment_type = 'DB';
    
    /**
     * @var string
     */
    protected $payment_brand = 'PAYDIREKT';

    /**
     * return payment type for paydirekt from configuration
     * @return string
     */
    public function getPaymentType()
    {
        return Configuration::get('VRPAYECOMMERCE_'.$this->getPaymentMethod().'_MODE');
    }

    /**
     * return transaction parameter to be sent to the gateway for paydirekt payment method
     * @return array
     */
    public function getTransactionParameter()
    {
        $transactionParameter = parent::getTransactionParameter();
        $transactionParameter['customParameters']['PAYDIREKT_minimumAge'] =
            Configuration::get('VRPAYECOMMERCE_PAYDIREKT_MINIMUM_AGE');
        $transactionParameter['customParameters']['PAYDIREKT_payment.isPartial'] = $this->getPaymentIsPartial();

        return $transactionParameter;
    }
}
