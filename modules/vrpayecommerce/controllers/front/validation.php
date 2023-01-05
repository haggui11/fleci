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
//require_once(dirname(__FILE__).'/../../core/versiontracker.php');
 
class VrpayecommerceValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $paymentMethod = $this->getPaymentMethodByResponse();
        $checkoutId = Tools::getValue('id');
        $registrationId = Tools::getValue('registrationId');

//        if (Configuration::get('VRPAYECOMMERCE_GENERAL_VERSION_TRACKER')) {
//            VersionTracker::sendVersionTracker($this->module->getVersionData($paymentMethod));
//        }

        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $this->validateUserLogin($cart, $customer);

        $transaction = $this->module->getCredentials($paymentMethod);

        if ($registrationId && $paymentMethod == 'PAYPALSAVED') {
            $this->debitRecurringPaypal($paymentMethod, $registrationId);
        }

        if ($paymentMethod == 'EASYCREDIT') {
            $this->capturePayment();
        } else {
            if($paymentMethod === 'ENTERPAY') // or any other server2server
            {
                $resultJson = VRpayecommercePaymentCore::getPaymentServerToServerStatus($checkoutId, $transaction);
                if($resultJson['is_valid'] && $resultJson['response']['paymentType'] === 'PA')
                {
                    // capture
                    $transactionData = $this->module->getCredentials($paymentMethod);
                    $transactionData['test_mode'] = $this->module->getTestMode($paymentMethod);
                    $transactionData['payment_type'] = "CP";
                    $transactionData['amount'] = $resultJson['response']['amount'];
                    $transactionData['currency'] = $resultJson['response']['currency'];
                    $captureStatus = VRpayecommercePaymentCore::backOfficeOperation($checkoutId, $transactionData);

                    if (!$captureStatus['is_valid']) {
                        $this->errors[] = $this->module->getErrorMessage($captureStatus['response']);
                        $this->redirectWithNotifications(
                            $this->context->link->getPageLink('order', true, null, array('step' => '3'))
                        );
                    } else {
                        $transactionResult =
                            VRpayecommercePaymentCore::getTransactionResult($captureStatus['response']['result']['code']);

                        if ($transactionResult == "ACK") {
                            $this->doSuccessPayment($paymentMethod, $captureStatus['response']);
                            return;
                        } elseif ($transactionResult == "NOK") {
                            $errorIdentifier =
                                VRpayecommercePaymentCore::getErrorIdentifier($captureStatus['response']['result']['code']);
                            $this->errors[] = $this->module->getErrorMessage($errorIdentifier);
                            $this->redirectWithNotifications(
                                $this->context->link->getPageLink('order', true, null, array('step' => '3'))
                            );
                        } else {
                            $this->errors[] = $this->module->getErrorMessage('ERROR_UNKNOWN');
                            $this->redirectWithNotifications(
                                $this->context->link->getPageLink('order', true, null, array('step' => '3'))
                            );
                        }
                    }
                }
            }
            else
            {
                $resultJson = VRpayecommercePaymentCore::getPaymentStatus($checkoutId, $transaction);
            }

            if ($resultJson['is_valid']) {
                $this->validatePaymentResponse($paymentMethod, $resultJson['response']);
            } else {
                $this->redirectErrorResponse($resultJson['response']);
            }
        }
    }

    private function validatePaymentResponse($paymentMethod, $paymentResponse)
    {
        $returnCode = $paymentResponse['result']['code'];
        $transactionResult = VRpayecommercePaymentCore::getTransactionResult($returnCode);
        
        if ($transactionResult == 'ACK') {
            $this->doSuccessPayment($paymentMethod, $paymentResponse);
        } else {
            if ($transactionResult == 'NOK') {
                $returnMessage = VRpayecommercePaymentCore::getErrorIdentifier($returnCode);
            } else {
                $returnMessage = 'ERROR_UNKNOWN';
            }
        }
        
        $this->redirectErrorResponse($returnMessage);
    }

    private function deleteRegistrationByFraud($referenceId, $paymentMethod)
    {
        $isRegistrationExist = $this->module->isRegistrationExist($referenceId);

        if (!$isRegistrationExist) {
            $deRegisterParameters = $this->module->getCredentials($paymentMethod);
            $deRegisterParameters['test_mode'] = $this->module->getTestMode($paymentMethod);
            VrpayecommercePaymentCore::deleteRegistration($referenceId, $deRegisterParameters);
        }
    }

    private function doSuccessPayment($paymentMethod, $resultJson)
    {
        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $isPaymentRecurring = $this->module->isPaymentRecurring($paymentMethod);

        if ($isPaymentRecurring) {
            $accountRegistered = $this->getAccountRecurring($paymentMethod, $resultJson);

            if ($paymentMethod == 'PAYPALSAVED') {
                $registrationId = $resultJson['id'];
                $resultJson = $this->payAndSavePaypal($paymentMethod, $resultJson);
            } else {
                $registrationId = $resultJson['registrationId'];
            }

            $this->saveRecurringPayment($paymentMethod, $accountRegistered, $registrationId);
        }

        $transaction = unserialize($this->context->cookie->vrpayecommerce_transaction);
        $mailVars = array();
        $currency = $this->context->currency;
        $orderTotal = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $paymentType = $this->getPaymentTypeResponse($resultJson);
        $orderStatus = $this->getOrderStatus($paymentType);
        $paymentDescription = $this->module->getPaymentDescription($paymentMethod);

        $this->saveTransactionLog($paymentMethod, $resultJson, $transaction);

        $this->module->validateOrder(
            (int)$cart->id,
            $orderStatus,
            $orderTotal,
            $paymentDescription,
            null,
            $mailVars,
            (int)$currency->id,
            false,
            $customer->secure_key
        );
        $orderId = $this->module->currentOrder;
        $this->updateTransLogId($orderId, $resultJson['id']);

        Tools::redirect(
            'index.php?controller=order-confirmation&id_cart='.(int)$cart->id.
                '&id_module='.(int)$this->module->id.
                '&id_order='.$orderId.
                '&key='.$customer->secure_key
        );
    }

    private function payAndSavePaypal($paymentMethod, $resultJson)
    {
        $registrationId = $resultJson['id'];
        $transaction = $this->module->getCredentials($paymentMethod);
        $session_transaction = unserialize($this->context->cookie->vrpayecommerce_transaction);
        $transaction['amount'] = $session_transaction['amount'];
        $transaction['currency'] = $session_transaction['currency'];
        $transaction['transaction_id'] = $resultJson['merchantTransactionId'];
        $transaction['payment_recurring'] = 'INITIAL';
        $transaction['test_mode'] = $this->module->getTestMode($paymentMethod);
        $transaction['payment_type'] = 'DB';

        $debitResponse = VRpayecommercePaymentCore::getRecurringPaymentResult($registrationId, $transaction);

        if ($debitResponse['is_valid']) {
            $returnCode = $debitResponse['response']['result']['code'];
            $transactionResult = VRpayecommercePaymentCore::getTransactionResult($returnCode);

            if ($transactionResult == 'ACK') {
                $this->context->cookie->resultJson_id = $debitResponse['response']['id'];
                return $debitResponse['response'];
            } else {
                if ($transactionResult == 'NOK') {
                    $returnMessage = VRpayecommercePaymentCore::getErrorIdentifier($returnCode);
                } else {
                    $returnMessage = 'ERROR_UNKNOWN';
                }
            }
        } else {
            $returnMessage = $debitResponse['response'];
        }

        $this->redirectErrorResponse($returnMessage);
        return false;
    }

    private function debitRecurringPaypal($paymentMethod, $registrationId)
    {
        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $currency = $this->context->currency;
        $transaction = unserialize($this->context->cookie->vrpayecommerce_transaction);
        $transaction['payment_type'] = 'DB';
        $transaction['payment_recurring'] = 'REPEATED';
        
        $debitResponse = VRpayecommercePaymentCore::getRecurringPaymentResult($registrationId, $transaction);

        if ($debitResponse['is_valid']) {
            $transactionResult =
                VRpayecommercePaymentCore::getTransactionResult($debitResponse['response']['result']['code']);
            
            if ($transactionResult == 'ACK') {
                $paymentStatus = Configuration::get('PS_OS_PAYMENT');
                $orderTotal = (float)$cart->getOrderTotal(true, Cart::BOTH);
                $mailVars = array();
                $paymentDescription = $this->module->getPaymentDescription($paymentMethod);
                $this->saveTransactionLog($paymentMethod, $debitResponse['response'], $transaction);
                
                $this->module->validateOrder(
                    (int)$cart->id,
                    $paymentStatus,
                    $orderTotal,
                    $paymentDescription,
                    null,
                    $mailVars,
                    (int)$currency->id,
                    false,
                    $customer->secure_key
                );
                $orderId = $this->module->currentOrder;
                $this->updateTransLogId($orderId, $debitResponse['response']['id']);
                Tools::redirect(
                    'index.php?controller=order-confirmation&id_cart='.(int)$cart->id.
                    '&id_module='.(int)$this->module->id.
                    '&id_order='.$this->module->currentOrder.
                    '&key='.$customer->secure_key
                );
            } else {
                if ($transactionResult == 'NOK') {
                    $returnMessage =
                        VRpayecommercePaymentCore::getErrorIdentifier($debitResponse['response']['result']['code']);
                } else {
                    $returnMessage = 'ERROR_UNKNOWN';
                }
            }
        } else {
             $returnMessage = $debitResponse['response'];
        }
        $this->redirectErrorResponse($returnMessage);
    }

    private function getPaymentMethodByResponse()
    {
        $paymentMethod = Tools::getValue('payment_method');
        $paymentDescription = $this->module->getPaymentDescription($paymentMethod);

        if ($paymentDescription == 'FRAUD') {
            $paymentMethod = $this->context->cookie->vrpayecommerce_paymentBrand;
        }

        return $paymentMethod;
    }

    private function validateUserLogin($cart, $customer)
    {
        if ($cart->id_customer == 0
            || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0
            || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }
    }

    private function getOrderStatus($paymentType)
    {
        switch ($paymentType) {
            case 'IR':
                $orderStatus = Configuration::get('VRPAYECOMMERCE_PAYMENT_STATUS_REVIEW');
                break;
            case 'PA':
                $orderStatus = Configuration::get('VRPAYECOMMERCE_PAYMENT_STATUS_PA');
                break;
            default:
                $orderStatus = Configuration::get('PS_OS_PAYMENT');
                break;
        }

        return $orderStatus;
    }

    private function getPaymentTypeResponse($resultJson)
    {
        $returnCode = $resultJson['result']['code'];

        if (VRpayecommercePaymentCore::isSuccessReview($returnCode)) {
            $paymentType = 'IR';
        } else {
            $paymentType = (isset($resultJson['paymentType']) ? $resultJson['paymentType'] : '');
        }

        return $paymentType;
    }

    private function getAccountRecurring($paymentMethod, $resultJson)
    {
        $account = array();
        switch ($paymentMethod) {
            case 'CCSAVED':
                $resultAccount = $resultJson['card'];
                $account['paymentGroup'] = 'CC';
                $account['paymentBrand'] = $resultJson['paymentBrand'];
                $account['holder'] = $resultAccount['holder'];
                $account['last4Digits'] = $resultAccount['last4Digits'];
                $account['expiryMonth'] = $resultAccount['expiryMonth'];
                $account['expiryYear'] = $resultAccount['expiryYear'];
                break;
            case 'DDSAVED':
                $resultAccount = $resultJson['bankAccount'];
                $account['paymentGroup'] = 'DD';
                $account['paymentBrand'] = $resultJson['paymentBrand'];
                $account['holder']  = $resultAccount['holder'];
                $account['last4Digits'] = Tools::substr($resultAccount['iban'], -4);
                break;
            case 'PAYPALSAVED':
                $account['paymentGroup'] = 'PAYPAL';
                $resultAccount = $resultJson['virtualAccount'];
                $account['paymentBrand'] = $resultJson['paymentBrand'];
                $account['holder'] = $resultAccount['holder'];
                $account['email'] = $resultAccount['accountId'];
                break;
        }

        return $account;
    }

    private function saveRecurringPayment($paymentMethod, $account, $referenceId)
    {
        $cart = $this->context->cart;
        $isRegistrationExist = $this->module->isRegistrationExist($referenceId);
        $credentials = $this->module->getCredentials($paymentMethod);

        if (!$isRegistrationExist) {
            $default = 1;
            $query = "SELECT * FROM `vrpayecommerce_payment_recurring` WHERE `cust_id` = '".(int)$cart->id_customer."'
                    AND `payment_group` = '".pSQL($account['paymentGroup'])."' AND payment_default = '1'";
            $count_row = Db::getInstance()->getRow($query);
            if ($count_row > 0) {
                $default = 0;
            }

            $sql = "INSERT INTO vrpayecommerce_payment_recurring (
                    cust_id, payment_group, brand, holder, email,
                    last4Digits, expiry_month, expiry_year, server_mode, channel_id, ref_id, payment_default
                    ) VALUES "."('".$cart->id_customer."',
                    '".$account["paymentGroup"]."',
                    '".$account["paymentBrand"]."',
                    '".(isset($account["holder"]) ? $account["holder"] : '')."',
                    '".(isset($account["email"]) ? $account["email"] : '')."',
                    '".(isset($account["last4Digits"]) ? $account["last4Digits"] : '')."',
                    '".(isset($account["expiryMonth"]) ? $account["expiryMonth"] : '')."',
                    '".(isset($account["expiryYear"]) ? $account["expiryYear"] : '')."',
                    '".$credentials['server_mode']."',
                    '".$credentials['channel_id']."',
                    '".$referenceId."',
                    '".$default."')";
            if (!Db::getInstance()->execute($sql)) {
                die('Erreur etc.');
            }
        }
    }

    private function refundByPaymentResponse($paymentResponse, $paymentMethod)
    {
        $referenceId = $paymentResponse['id'];
        $refundParameters = array();
        $refundParameters = $this->module->getCredentials($paymentMethod);
        if ($paymentResponse['paymentType'] == "PA") {
            $refundParameters['payment_type'] = "RV";
        } else {
            $refundParameters['payment_type'] = "RF";
            $refundParameters['amount'] = VRpayecommercePaymentCore::setNumberFormat($paymentResponse['amount']);
            $refundParameters['currency'] = $paymentResponse['currency'];
        }
        $refundParameters['test_mode'] = $this->module->getTestMode($paymentMethod);
        VrpayecommercePaymentCore::backOfficeOperation($referenceId, $refundParameters);
    }

    private function isAmountFraud($resultJson)
    {
        if (empty($resultJson['amount']) || empty($resultJson['currency'])) {
            return false;
        }

        $currencyobj = new Currency((int)$this->context->cart->id_currency);
        $orderCurrency = $currencyobj->iso_code;
        $paymentCurrency = $resultJson['currency'];

        $cart = $this->context->cart;
        $paymentAmount = VRpayecommercePaymentCore::setNumberFormat($cart->getOrderTotal(true, Cart::BOTH));
        $orderAmount = VRpayecommercePaymentCore::setNumberFormat($resultJson['amount']);

        if ($orderCurrency == $paymentCurrency && $orderAmount == $paymentAmount) {
            return false;
        }
     
        return true;
    }

    private function isAuthorized()
    {
        // Check that this payment option is still available in case the customer
        //changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'vrpayecommerce') {
                $authorized = true;
                break;
            }
        }
        
        return $authorized;
    }

    private function updateTransLogId($orderId, $referenceId)
    {
        $sql = "UPDATE vrpayecommerce_order_ref SET id_order = '".(int)$orderId."' where ref_id = '".$referenceId."'";
        if (!Db::getInstance()->execute($sql)) {
            die('Erreur etc.');
        }
    }

    private function saveTransactionLog($paymentMethod, $resultJson, $transaction)
    {
        $paymentType = $this->getPaymentTypeResponse($resultJson);
        $transactionResult = VRpayecommercePaymentCore::getTransactionResult($resultJson['result']['code']);

        $sql = "INSERT INTO vrpayecommerce_order_ref (
                transaction_id, 
                payment_method, 
                order_status, 
                ref_id, 
                payment_code, 
                currency, 
                amount, 
                mandate_date, 
                mandate_id) VALUES ".
                "('".$resultJson['id']."',
                '".pSQL($paymentMethod)."',
                '".$transactionResult."',
                '".$resultJson['id']."',
                '".$paymentType."',
                '".$resultJson['currency']."',
                '".$resultJson['amount']."',
                '',
                '')";
        if (!Db::getInstance()->execute($sql)) {
            die('Erreur etc.');
        }
    }

    private function redirectErrorResponse($returnMessage)
    {
        $this->errors[] = $this->module->getErrorMessage($returnMessage);
        $this->redirectWithNotifications(
            $this->context->link->getPageLink('order', true, null, array('step' => '3'))
        );
    }

    public function capturePayment()
    {

        $paymentMethod = Tools::getValue('payment_method');
//        VersionTracker::sendVersionTracker($this->module->getVersionData($paymentMethod));

        $checkoutId = Tools::getValue('vrpay_id');
    
        $transactionData = $this->module->getCredentials($paymentMethod);

        $transactionData['test_mode'] = $this->module->getTestMode($paymentMethod);
        $transactionData['payment_type'] = "CP";
        $transactionData['amount'] = Tools::getValue('amount');
        $transactionData['currency'] = Tools::getValue('currency');
        if ($this->context->cart->getOrderTotal(true, Cart::BOTH) != $transactionData['amount']) {
            $this->errors[] = $this->module->getErrorMessage('ERROR_GENERAL_CAPTURE_PAYMENT');
            $this->redirectWithNotifications(
                $this->context->link->getPageLink('order', true, null, array('step' => '3'))
            );
        }
        $paymentStatus = VRpayecommercePaymentCore::backOfficeOperation($checkoutId, $transactionData);

        if (!$paymentStatus['is_valid']) {
            $this->errors[] = $this->module->getErrorMessage($paymentStatus['response']);
            $this->redirectWithNotifications(
                $this->context->link->getPageLink('order', true, null, array('step' => '3'))
            );
        } else {
            $transactionResult =
                VRpayecommercePaymentCore::getTransactionResult($paymentStatus['response']['result']['code']);

            if ($transactionResult == "ACK") {
                $this->doSuccessPayment($paymentMethod, $paymentStatus['response']);
            } elseif ($transactionResult == "NOK") {
                $errorIdentifier =
                    VRpayecommercePaymentCore::getErrorIdentifier($paymentStatus['response']['result']['code']);
                $this->errors[] = $this->module->getErrorMessage($errorIdentifier);
                $this->redirectWithNotifications(
                    $this->context->link->getPageLink('order', true, null, array('step' => '3'))
                );
            } else {
                $this->errors[] = $this->module->getErrorMessage('ERROR_UNKNOWN');
                $this->redirectWithNotifications(
                    $this->context->link->getPageLink('order', true, null, array('step' => '3'))
                );
            }
        }
    }
}
