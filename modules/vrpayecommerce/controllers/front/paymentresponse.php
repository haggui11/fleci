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
//require_once(dirname(__FILE__).'/../../core/versiontracker.php');

class VrpayecommercePaymentResponseModuleFrontController extends VrpayecommercePaymentAbstractModuleFrontController
{
    protected $successAction = '';
    protected $errorAction = '';

    public function postProcess()
    {
        $checkoutId = Tools::getValue('id');

        if (Tools::getIsset('recurring_id')) {
            $recurringId = Tools::getValue('recurring_id');
            $this->successAction = 'SUCCESS_MC_UPDATE';
            $this->errorAction = 'ERROR_MC_UPDATE';
        } else {
            $recurringId = false;
            $this->successAction = 'SUCCESS_MC_ADD';
            $this->errorAction = 'ERROR_MC_ADD';
        }
        
        $paymentMethod = Tools::getValue('payment_method');
        $this->setPaymentMethod($paymentMethod);
//        VersionTracker::sendVersionTracker($this->module->getVersionData($this->payment_method));

        $transactionData = $this->module->getCredentials($paymentMethod);
        $paymentStatus = VRpayecommercePaymentCore::getPaymentStatus($checkoutId, $transactionData);

        if ($paymentStatus['is_valid']) {
            $returnCode = $paymentStatus['response']['result']['code'];
            $transactionResult = VRpayecommercePaymentCore::getTransactionResult($returnCode);
            if ($transactionResult == "ACK") {
                $this->doSuccessRegister($recurringId, $paymentMethod, $paymentStatus['response']);
            } else {
                if ($transactionResult == "NOK") {
                    $returnMessage = VRpayecommercePaymentCore::getErrorIdentifier($returnCode);
                } else {
                    $returnMessage = 'ERROR_UNKOWN';
                }
                $this->redirectErrorRecurring($returnMessage, $this->errorAction);
            }
        } else {
            $this->redirectErrorRecurring($paymentStatus['response']);
        }
    }

    private function doSuccessRegister($recurringId, $paymentMethod, $resultJson)
    {

        $paymentBrand = $resultJson['paymentBrand'];
        $referenceId = $resultJson['id'];
        $registrationId = (isset($resultJson['registrationId']) ? $resultJson['registrationId'] : '');

        $transactionData = $this->getRegisterParameter($paymentMethod);
        if ($recurringId) {
            $transactionData['transaction_id'] = $resultJson['merchantTransactionId'];
        }

        if ($paymentBrand == 'PAYPAL') {
            $paypalResult = $this->doPaypalRegister($recurringId, $paymentMethod, $transactionData, $resultJson);
            $referenceId = $paypalResult['id'];
        } elseif ($this->getPaymentType() == "PA") {
            $captureResult = $this->capturePayment($recurringId, $paymentMethod, $transactionData, $resultJson);
            $referenceId = $captureResult['id'];
        } else {
            $this->saveRegisteredPayment($recurringId, $registrationId, $resultJson, $paymentMethod);
        }

        $this->refundPayment($transactionData, $referenceId);

        if ($recurringId) {
            $this->deletePayment($transactionData, $resultJson['merchantTransactionId']);
        }
        $this->redirectSuccessRecurring($this->successAction);
    }

    private function doPaypalRegister($recurringId, $paymentMethod, $transactionData, $resultJson)
    {

        $referenceId = $resultJson['id'];
        $registrationId = $resultJson['id'];
        $transactionData['payment_type'] = 'DB';
        $paypalResult = VRpayecommercePaymentCore::getRecurringPaymentResult($referenceId, $transactionData);
        if ($paypalResult['is_valid']) {
            $referenceId = $paypalResult['response']['id'];

            $returnCode = $paypalResult['response']['result']['code'];
            $resultPayPal = VRpayecommercePaymentCore::getTransactionResult($returnCode);

            if ($resultPayPal == "ACK") {
                $this->saveRegisteredPayment($recurringId, $registrationId, $resultJson, $paymentMethod);
            } else {
                if ($resultPayPal == "NOK") {
                    $returnMessage = VRpayecommercePaymentCore::getErrorIdentifier($returnCode);
                } else {
                    $returnMessage = 'ERROR_UNKOWN';
                }
                $this->redirectErrorRecurring($returnMessage, $this->errorAction);
            }

            return $paypalResult['response'];
        } else {
            $this->redirectErrorRecurring($paypalResult['response'], $this->errorAction);
        }
    }

    private function getRegisterParameter($paymentMethod)
    {

        $currency = $this->context->currency;

        $transactionData = $this->module->getCredentials($paymentMethod);
        $transactionData['amount'] = $this->getRegisterAmount();
        $transactionData['currency'] = $currency->iso_code;
        $transactionData['transaction_id'] = (int)$this->context->cookie->id_customer;
        $transactionData['payment_recurring'] = 'INITIAL';
        $transactionData['test_mode'] = $this->module->getTestMode($this->payment_method);

        return $transactionData;
    }

    private function capturePayment($recurringId, $paymentMethod, $transactionData, $resultJson)
    {

        $referenceId = $resultJson['id'];
        $registrationId = $resultJson['registrationId'];

        $transactionData['payment_type'] = "CP";
        $captureResult = VRpayecommercePaymentCore::backOfficeOperation($referenceId, $transactionData);

        if ($captureResult['is_valid']) {
            $returnCode = $captureResult['response']['result']['code'];
            $captureStatus = VRpayecommercePaymentCore::getTransactionResult($returnCode);

            if ($captureStatus == "ACK") {
                $this->saveRegisteredPayment($recurringId, $registrationId, $resultJson, $paymentMethod);
            } else {
                if ($captureStatus == "NOK") {
                    $returnMessage = VRpayecommercePaymentCore::getErrorIdentifier($returnCode);
                } else {
                    $returnMessage = 'ERROR_UNKOWN';
                }
                $this->redirectErrorRecurring($returnMessage, $this->errorAction);
            }

            return $captureResult['response'];
        } else {
            $this->redirectErrorRecurring($captureResult['response'], $this->errorAction);
        }
    }

    private function refundPayment($transactionData, $referenceId)
    {

        $transactionData['payment_type'] = "RF";
        VRpayecommercePaymentCore::backOfficeOperation($referenceId, $transactionData);
    }

    private function deletePayment($transactionData, $referenceId)
    {

        VRpayecommercePaymentCore::deleteRegistration($referenceId, $transactionData);
    }

    private function saveRegisteredPayment($recurringId, $registrationId, $resultJson, $paymentMethod)
    {
        if ($recurringId) {
            $this->updateRegistration($recurringId, $registrationId, $resultJson, $paymentMethod);
        } else {
            $this->insertRegistration($registrationId, $resultJson, $paymentMethod);
        }
    }

    protected function getAccount($paymentMethod, $resultJson)
    {
        if ($paymentMethod == 'CCSAVED') {
            $account = $resultJson['card'];
        } elseif ($paymentMethod == 'DDSAVED') {
            $account = $resultJson['bankAccount'];
            $account['last4Digits'] = Tools::substr($account['iban'], -4);
        } else {
            $account = $resultJson['virtualAccount'];
            $account['email'] = $account['accountId'];
        }

        return $account;
    }

    protected function checkDefault()
    {
        $default = 1;
        $credentials = $this->module->getCredentials($this->getPaymentMethod());
        $query = "SELECT * FROM `vrpayecommerce_payment_recurring`
                  WHERE `cust_id` = '".(int)$this->context->cookie->id_customer."'
                  AND `payment_group` = '".$this->getRecurringGroup()."'
                  AND `server_mode` = '".$credentials['server_mode']."'
                  AND `channel_id` = '".$credentials['channel_id']."'
                  AND payment_default = '1'";
        $count_row = Db::getInstance()->getRow($query);

        if ($count_row > 0) {
            $default = 0;
        }

        return $default;
    }

    protected function insertRegistration($registrationId, $resultJson, $paymentMethod)
    {
        $default = $this->checkDefault();
        $credentials = $this->module->getCredentials($paymentMethod);
        $account = $this->getAccount($paymentMethod, $resultJson);

        $sql = "INSERT INTO vrpayecommerce_payment_recurring (
                cust_id,
                payment_group,
                brand,
                email,
                holder,
                last4digits,
                expiry_month,
                expiry_year,
                server_mode,
                channel_id,
                ref_id,
                payment_default) VALUES "."('".
                (int)$this->context->cookie->id_customer."',
                '".$this->getRecurringGroup()."',
                '".$resultJson['paymentBrand']."',
                '".(isset($account["email"]) ? $account["email"] : '')."',
                '".(isset($account["holder"]) ? $account["holder"] : '')."',
                '".(isset($account["last4Digits"]) ? $account["last4Digits"] : '')."',
                '".(isset($account["expiryMonth"]) ? $account["expiryMonth"] : '')."',
                '".(isset($account["expiryYear"]) ? $account["expiryYear"] : '')."',
                '".$credentials['server_mode']."',
                '".$credentials['channel_id']."',
                '".$registrationId."',
                '".$default."')";
        if (!Db::getInstance()->execute($sql)) {
            die('Erreur etc.');
        }
    }

    protected function updateRegistration($recurringId, $registrationId, $resultJson, $paymentMethod)
    {
        $account = $this->getAccount($paymentMethod, $resultJson);
        $credentials = $this->module->getCredentials($paymentMethod);

        $sql = "UPDATE vrpayecommerce_payment_recurring SET cust_id = '".(int)$this->context->cookie->id_customer."',
                payment_group = '".$this->getRecurringGroup()."',
                brand = '".$resultJson['paymentBrand']."',
                email = '".(isset($account["email"]) ? $account["email"] : '')."',
                holder = '".(isset($account["holder"]) ? $account["holder"] : '')."',
                last4digits = '".(isset($account["last4Digits"]) ? $account["last4Digits"] : '')."',
                expiry_month = '".(isset($account["expiryMonth"]) ? $account["expiryMonth"] : '')."',
                expiry_year = '".(isset($account["expiryYear"]) ? $account["expiryYear"] : '')."',
                server_mode = '".$credentials['server_mode']."',
                channel_id = '".$credentials['channel_id']."',
                ref_id = '".$registrationId."' where id = '".(int)$recurringId."'";
        if (!Db::getInstance()->execute($sql)) {
            die('Erreur etc.');
        }
    }
}
