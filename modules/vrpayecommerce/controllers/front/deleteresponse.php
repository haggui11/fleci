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
require_once(dirname(__FILE__).'/paymentabstract.php');

class VrpayecommerceDeleteResponseModuleFrontController extends VrpayecommercePaymentAbstractModuleFrontController
{
    public function postProcess()
    {
        $id = Tools::getValue('id');
        $selectedPayment = Tools::getValue('selected_payment');
        $customerId = (int)$this->context->cookie->id_customer;
        $this->setPaymentMethod($selectedPayment);

        $referenceId = $this->getReferenceId($id);
        $transactionData = $this->getDeleteParameter($selectedPayment);

        $response = VRpayecommercePaymentCore::deleteRegistration($referenceId, $transactionData);

        if ($response['is_valid']) {
            $returnCode = $response['response']['result']['code'];
            $transactionResult = VRpayecommercePaymentCore::getTransactionResult($returnCode);

            if ($transactionResult == "ACK") {
                $this->deletePaymentRecurring($id, $customerId);
                $this->redirectSuccessRecurring('SUCCESS_MC_DELETE');
            } else {
                $returnMessage = VRpayecommercePaymentCore::getErrorIdentifier($returnCode);
                $this->redirectErrorRecurring($returnMessage, 'ERROR_MC_DELETE');
            }
        } else {
            $this->redirectErrorRecurring($response['response'], 'ERROR_MC_DELETE');
        }
    }

    private function getDeleteParameter($selectedPayment)
    {
        $transactionData = $this->module->getCredentials($selectedPayment);
        $transactionData['transaction_id'] = (int)$this->context->cookie->id_customer;
        $transactionData['test_mode'] = $this->module->getTestMode($selectedPayment);

        return $transactionData;
    }

    private function deletePaymentRecurring($id, $customerId)
    {
        $sql = "DELETE FROM `vrpayecommerce_payment_recurring`
        WHERE `cust_id`='".$customerId."' AND id = '".(int)$id."' ";
        Db::getInstance()->Execute($sql);
    }
}
