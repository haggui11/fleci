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

class AdminOrdersController extends AdminOrdersControllerCore
{
    public function postProcess()
    {
        $params = array();

        if (Tools::isSubmit('id_order') && Tools::getValue('id_order') > 0) {
            $order = new Order(Tools::getValue('id_order'));
            if (!Validate::isLoadedObject($order)) {
                $this->errors[] = $this->trans(
                    'The order cannot be found within your database.',
                    array(),
                    'Admin.OrdersCustomers.Notification'
                );
            }
            ShopUrl::cacheMainDomainForShop((int)$order->id_shop);
        }
        if (Tools::isSubmit('submitShippingNumber') && isset($order)) {
            if ($this->access('edit')) {
                $tracking_number = Tools::getValue('shipping_tracking_number');
                $id_carrier = Tools::getValue('shipping_carrier');
                $old_tracking_number = $order->shipping_number;

                $order_carrier = new OrderCarrier(Tools::getValue('id_order_carrier'));
                if (!Validate::isLoadedObject($order_carrier)) {
                    $this->errors[] = $this->trans(
                        'The order carrier ID is invalid.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } elseif (!empty($tracking_number) && !Validate::isTrackingNumber($tracking_number)) {
                    $this->errors[] = $this->trans(
                        'The tracking number is incorrect.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } else {
                    $old_id_carrier = $order_carrier->id_carrier;
                    if (!empty($id_carrier) && $old_id_carrier != $id_carrier) {
                        $order->id_carrier = (int) $id_carrier;
                        $order_carrier->id_carrier = (int) $id_carrier;
                        $order_carrier->update();
                        $order->refreshShippingCost();
                    }

                    $order_carrier = new OrderCarrier((int) Tools::getValue('id_order_carrier'));

                    $order->shipping_number = $tracking_number;
                    $order->update();

                    $order_carrier->tracking_number = pSQL($tracking_number);
                    if ($order_carrier->update()) {
                        if (!empty($tracking_number) && $old_tracking_number != $tracking_number) {
                            $customer = new Customer((int)$order->id_customer);
                            $carrier = new Carrier((int)$order->id_carrier, $order->id_lang);
                            if (!Validate::isLoadedObject($customer)) {
                                throw new PrestaShopException('Can\'t load Customer object');
                            }
                            if (!Validate::isLoadedObject($carrier)) {
                                throw new PrestaShopException('Can\'t load Carrier object');
                            }
                            $orderLanguage = new Language((int) $order->id_lang);
                            $templateVars = array(
                                '{followup}' => str_replace('@', $order->shipping_number, $carrier->url),
                                '{firstname}' => $customer->firstname,
                                '{lastname}' => $customer->lastname,
                                '{id_order}' => $order->id,
                                '{shipping_number}' => $order->shipping_number,
                                '{order_name}' => $order->getUniqReference()
                            );
                            if (@Mail::Send(
                                (int)$order->id_lang,
                                'in_transit',
                                $this->trans(
                                    'Package in transit',
                                    array(),
                                    'Emails.Subject',
                                    $orderLanguage->locale
                                ),
                                $templateVars,
                                $customer->email,
                                $customer->firstname.' '.$customer->lastname,
                                null,
                                null,
                                null,
                                null,
                                _PS_MAIL_DIR_,
                                true,
                                (int)$order->id_shop
                            )) {
                                Hook::exec(
                                    'actionAdminOrdersTrackingNumberUpdate',
                                    array('order' => $order, 'customer' => $customer, 'carrier' => $carrier),
                                    null,
                                    false,
                                    true,
                                    false,
                                    $order->id_shop
                                );
                                Tools::redirectAdmin(
                                    self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token
                                );
                            } else {
                                $this->errors[] = Tools::displayError(
                                    'An error occurred while sending an email to the customer.',
                                    array(),
                                    'Admin.Notifications.Error'
                                );
                            }
                        }
                    } else {
                        $this->errors[] = $this->trans(
                            'The order carrier cannot be updated.',
                            array(),
                            'Admin.OrdersCustomers.Notification'
                        );
                    }
                }
            } else {
                $this->errors[] = $this->trans(
                    'You do not have permission to edit this.',
                    array(),
                    'Admin.Notifications.Error'
                );
            }
        } elseif (Tools::isSubmit('submitState') && isset($order)) {
            if ($this->access('edit')) {
                $order_state = new OrderState(Tools::getValue('id_order_state'));
                if (!Validate::isLoadedObject($order_state)) {
                    $this->errors[] = $this->trans(
                        'The new order status is invalid.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } else {
                    $current_order_state = $order->getCurrentOrderState();
                    if ($current_order_state->id != $order_state->id) {
                        $history = new OrderHistory();
                        $history->id_order = $order->id;
                        $history->id_employee = (int)$this->context->employee->id;
                        $use_existings_payment = false;
                        if (!$order->hasInvoice()) {
                            $use_existings_payment = true;
                        }
                        $history->changeIdOrderState((int)$order_state->id, $order, $use_existings_payment);
                        $carrier = new Carrier($order->id_carrier, $order->id_lang);
                        $templateVars = array();
                        if ($history->id_order_state == Configuration::get('PS_OS_SHIPPING')
                            && $order->shipping_number) {
                            $templateVars = array(
                                '{followup}' => str_replace('@', $order->shipping_number, $carrier->url)
                            );
                        }
                        if ($history->addWithemail(true, $templateVars)) {
                            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                                foreach ($order->getProducts() as $product) {
                                    if (StockAvailable::dependsOnStock($product['product_id'])) {
                                        StockAvailable::synchronize($product['product_id'], (int)$product['id_shop']);
                                    }
                                }
                            }
                            Tools::redirectAdmin(
                                self::$currentIndex.'&id_order='.(int)$order->id.'&vieworder&token='.$this->token
                            );
                        }
                        $this->errors[] = $this->trans(
                            'An error occurred while changing order status, '
                            .'or we were unable to send an email to the customer.',
                            array(),
                            'Admin.OrdersCustomers.Notification'
                        );
                    } else {
                        $this->errors[] = $this->trans(
                            'The order has already been assigned this status.',
                            array(),
                            'Admin.OrdersCustomers.Notification'
                        );
                    }
                }
            } else {
                $this->errors[] = $this->trans(
                    'You do not have permission to edit this.',
                    array(),
                    'Admin.Notifications.Error'
                );
            }
        } elseif (Tools::isSubmit('submitMessage') && isset($order)) {
            if ($this->access('edit')) {
                $customer = new Customer(Tools::getValue('id_customer'));
                if (!Validate::isLoadedObject($customer)) {
                    $this->errors[] = $this->trans(
                        'The customer is invalid.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } elseif (!Tools::getValue('message')) {
                    $this->errors[] = $this->trans(
                        'The message cannot be blank.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } else {
                    /* Get message rules and and check fields validity */
                    $rules = call_user_func(array('Message', 'getValidationRules'), 'Message');
                    foreach ($rules['required'] as $field) {
                        if (($value = Tools::getValue($field)) == false && (string)$value != '0') {
                            if (!Tools::getValue('id_'.$this->table) || $field != 'passwd') {
                                $this->errors[] = $this->trans(
                                    'field %s is required.',
                                    array('%s' => $field),
                                    'Admin.OrdersCustomers.Notification'
                                );
                            }
                        }
                    }
                    foreach ($rules['size'] as $field => $maxLength) {
                        if (Tools::getValue($field) && Tools::strlen(Tools::getValue($field)) > $maxLength) {
                            $this->errors[] = $this->trans(
                                'The %1$s field is too long (%2$d chars max).',
                                array(
                                    '%1$s' => $field,
                                    '%2$d' => $maxLength,
                                ),
                                'Admin.Notifications.Error'
                            );
                        }
                    }
                    foreach (array_keys($rules['validate']) as $field) {
                        if (Tools::getValue($field)) {
                            if (!Validate::$function(htmlentities(Tools::getValue($field), ENT_COMPAT, 'UTF-8'))) {
                                $this->errors[] = $this->trans(
                                    'The %s field is invalid.',
                                    array('%s' => $field),
                                    'Admin.Notifications.Error'
                                );
                            }
                        }
                    }
                    if (!count($this->errors)) {
                        $id_customer_thread = CustomerThread::getIdCustomerThreadByEmailAndIdOrder(
                            $customer->email,
                            $order->id
                        );
                        if (!$id_customer_thread) {
                            $customer_thread = new CustomerThread();
                            $customer_thread->id_contact = 0;
                            $customer_thread->id_customer = (int)$order->id_customer;
                            $customer_thread->id_shop = (int)$this->context->shop->id;
                            $customer_thread->id_order = (int)$order->id;
                            $customer_thread->id_lang = (int)$this->context->language->id;
                            $customer_thread->email = $customer->email;
                            $customer_thread->status = 'open';
                            $customer_thread->token = Tools::passwdGen(12);
                            $customer_thread->add();
                        } else {
                            $customer_thread = new CustomerThread((int)$id_customer_thread);
                        }
                        $customer_message = new CustomerMessage();
                        $customer_message->id_customer_thread = $customer_thread->id;
                        $customer_message->id_employee = (int)$this->context->employee->id;
                        $customer_message->message = Tools::getValue('message');
                        $customer_message->private = Tools::getValue('visibility');
                        if (!$customer_message->add()) {
                            $this->errors[] = $this->trans(
                                'An error occurred while saving the message.',
                                array(),
                                'Admin.Notifications.Error'
                            );
                        } elseif ($customer_message->private) {
                            Tools::redirectAdmin(
                                self::$currentIndex.'&id_order='.(int)$order->id.
                                '&vieworder&conf=11&token='.$this->token
                            );
                        } else {
                            $message = $customer_message->message;
                            if (Configuration::get('PS_MAIL_TYPE', null, null, $order->id_shop) != Mail::TYPE_TEXT) {
                                $message = Tools::nl2br($customer_message->message);
                            }
                            $varsTpl = array(
                                '{lastname}' => $customer->lastname,
                                '{firstname}' => $customer->firstname,
                                '{id_order}' => $order->id,
                                '{order_name}' => $order->getUniqReference(),
                                '{message}' => $message
                            );
                            if (@Mail::Send(
                                (int)$order->id_lang,
                                'order_merchant_comment',
                                $this->trans(
                                    'New message regarding your order',
                                    array(),
                                    'Emails.Subject',
                                    $orderLanguage->locale
                                ),
                                $varsTpl,
                                $customer->email,
                                $customer->firstname.' '.$customer->lastname,
                                null,
                                null,
                                null,
                                null,
                                _PS_MAIL_DIR_,
                                true,
                                (int)$order->id_shop
                            )) {
                                Tools::redirectAdmin(
                                    self::$currentIndex.'&id_order='.$order->id.'
                                    &vieworder&conf=11'.'&token='.$this->token
                                );
                            }
                        }
                        $this->errors[] = $this->trans(
                            'An error occurred while sending an email to the customer.',
                            array(),
                            'Admin.OrdersCustomers.Notification'
                        );
                    }
                }
            } else {
                $this->errors[] = $this->trans(
                    'You do not have permission to delete this.',
                    array(),
                    'Admin.Notifications.Error'
                );
            }
        } elseif (Tools::isSubmit('partialRefund') && isset($order)) {
            if ($this->access('edit')) {
                if (Tools::isSubmit('partialRefundProduct')
                    && ($refunds = Tools::getValue('partialRefundProduct'))
                    && is_array($refunds)) {
                    $amount = 0;
                    $order_detail_list = array();
                    $full_quantity_list = array();
                    foreach ($refunds as $id_order_detail => $amount_detail) {
                        $quantity = Tools::getValue('partialRefundProductQuantity');
                        if (!$quantity[$id_order_detail]) {
                            continue;
                        }
                        $full_quantity_list[$id_order_detail] = (int)$quantity[$id_order_detail];
                        $order_detail_list[$id_order_detail] = array(
                            'quantity' => (int)$quantity[$id_order_detail],
                            'id_order_detail' => (int)$id_order_detail
                        );
                        $order_detail = new OrderDetail((int)$id_order_detail);
                        if (empty($amount_detail)) {
                            $unit_price_tax_excl = $order_detail->unit_price_tax_excl;
                            $unit_price_tax_incl = $order_detail->unit_price_tax_incl;
                            if (!Tools::getValue('TaxMethod')) {
                                $order_detail_list[$id_order_detail]['unit_price'] = $unit_price_tax_excl;
                            } else {
                                $order_detail_list[$id_order_detail]['unit_price'] = $unit_price_tax_incl;
                            }
                            $order_detail_list[$id_order_detail]['amount'] =
                            $order_detail->unit_price_tax_incl * $order_detail_list[$id_order_detail]['quantity'];
                        } else {
                            $order_detail_list[$id_order_detail]['amount'] = (float)str_replace(
                                ',',
                                '.',
                                $amount_detail
                            );
                            $order_detail_list[$id_order_detail]['unit_price'] =
                                $order_detail_list[$id_order_detail]['amount'] /
                                $order_detail_list[$id_order_detail]['quantity'];
                        }
                        $amount += $order_detail_list[$id_order_detail]['amount'];
                        if (!$order->hasBeenDelivered() || ($order->hasBeenDelivered()
                            && Tools::isSubmit('reinjectQuantities'))
                            && $order_detail_list[$id_order_detail]['quantity'] > 0) {
                            $this->reinjectQuantity($order_detail, $order_detail_list[$id_order_detail]['quantity']);
                        }
                    }
                    if ((float)str_replace(',', '.', Tools::getValue('partialRefundShippingCost'))) {
                        $shipping_cost_amount = (float)str_replace(
                            ',',
                            '.',
                            Tools::getValue('partialRefundShippingCost')
                        );
                    } else {
                        $shipping_cost_amount = false;
                    }
                    if ($amount == 0 && $shipping_cost_amount == 0) {
                        if (!empty($refunds)) {
                            $this->errors[] = $this->trans(
                                'Please enter a quantity to proceed with your refund.',
                                array(),
                                'Admin.OrdersCustomers.Notification'
                            );
                        } else {
                            $this->errors[] = $this->trans(
                                'Please enter an amount to proceed with your refund.',
                                array(),
                                'Admin.OrdersCustomers.Notification'
                            );
                        }
                        return false;
                    }
                    $choosen = false;
                    $voucher = 0;
                    if ((int)Tools::getValue('refund_voucher_off') == 1) {
                        $amount -= $voucher = (float)Tools::getValue('order_discount_price');
                    } elseif ((int)Tools::getValue('refund_voucher_off') == 2) {
                        $choosen = true;
                        $amount = $voucher = (float)Tools::getValue('refund_voucher_choose');
                    }
                    if ($shipping_cost_amount > 0) {
                        if (!Tools::getValue('TaxMethod')) {
                            $tax = new Tax();
                            $tax->rate = $order->carrier_tax_rate;
                            $tax_calculator = new TaxCalculator(array($tax));
                            $amount += $tax_calculator->addTaxes($shipping_cost_amount);
                        } else {
                            $amount += $shipping_cost_amount;
                        }
                    }
                    $order_carrier = new OrderCarrier((int)$order->getIdOrderCarrier());
                    if (Validate::isLoadedObject($order_carrier)) {
                        $order_carrier->weight = (float)$order->getTotalWeight();
                        if ($order_carrier->update()) {
                            $order->weight = sprintf(
                                "%.3f ".Configuration::get('PS_WEIGHT_UNIT'),
                                $order_carrier->weight
                            );
                        }
                    }
                    if ($amount >= 0) {
                        if (!OrderSlip::create(
                            $order,
                            $order_detail_list,
                            $shipping_cost_amount,
                            $voucher,
                            $choosen,
                            (Tools::getValue('TaxMethod') ? false : true)
                        )) {
                            $this->errors[] = $this->trans(
                                'You cannot generate a partial credit slip.',
                                array(),
                                'Admin.OrdersCustomers.Notification'
                            );
                        } else {
                            Hook::exec(
                                'actionOrderSlipAdd',
                                array(
                                    'order' => $order,
                                    'productList' => $order_detail_list,
                                    'qtyList' => $full_quantity_list
                                ),
                                null,
                                false,
                                true,
                                false,
                                $order->id_shop
                            );
                            $customer = new Customer((int)($order->id_customer));
                            $params['{lastname}'] = $customer->lastname;
                            $params['{firstname}'] = $customer->firstname;
                            $params['{id_order}'] = $order->id;
                            $params['{order_name}'] = $order->getUniqReference();
                            @Mail::Send(
                                (int)$order->id_lang,
                                'credit_slip',
                                $this->trans(
                                    'New credit slip regarding your order',
                                    array(),
                                    'Emails.Subject',
                                    $orderLanguage->locale
                                ),
                                $params,
                                $customer->email,
                                $customer->firstname.' '.$customer->lastname,
                                null,
                                null,
                                null,
                                null,
                                _PS_MAIL_DIR_,
                                true,
                                (int)$order->id_shop
                            );
                        }
                        foreach ($order_detail_list as &$product) {
                            $order_detail = new OrderDetail((int)$product['id_order_detail']);
                            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                                StockAvailable::synchronize($order_detail->product_id);
                            }
                        }
                        if (Tools::isSubmit('generateDiscountRefund') && !count($this->errors) && $amount > 0) {
                            $cart_rule = new CartRule();
                            $cart_rule->description = $this->trans(
                                'Credit slip for order #%d',
                                array('#%d' => $order->id),
                                'Admin.OrdersCustomers.Feature'
                            );
                            $language_ids = Language::getIDs(false);
                            foreach ($language_ids as $id_lang) {
                                $cart_rule->name[$id_lang] = sprintf('V0C%1$dO%2$d', $order->id_customer, $order->id);
                            }
                            $cart_rule->code = sprintf(
                                'V0C%1$dO%2$d',
                                $order->id_customer,
                                $order->id
                            );
                            $cart_rule->quantity = 1;
                            $cart_rule->quantity_per_user = 1;
                            $cart_rule->id_customer = $order->id_customer;
                            $now = time();
                            $cart_rule->date_from = date('Y-m-d H:i:s', $now);
                            $cart_rule->date_to = date('Y-m-d H:i:s', strtotime('+1 year'));
                            $cart_rule->partial_use = 1;
                            $cart_rule->active = 1;
                            $cart_rule->reduction_amount = $amount;
                            $cart_rule->reduction_tax = true;
                            $cart_rule->minimum_amount_currency = $order->id_currency;
                            $cart_rule->reduction_currency = $order->id_currency;
                            if (!$cart_rule->add()) {
                                $this->errors[] = $this->trans(
                                    'You cannot generate a voucher.',
                                    array(),
                                    'Admin.OrdersCustomers.Notification'
                                );
                            } else {
                                foreach ($language_ids as $id_lang) {
                                    $cart_rule->name[$id_lang] = sprintf(
                                        'V%1$dC%2$dO%3$d',
                                        $cart_rule->id,
                                        $order->id_customer,
                                        $order->id
                                    );
                                }
                                $cart_rule->code = sprintf(
                                    'V%1$dC%2$dO%3$d',
                                    $cart_rule->id,
                                    $order->id_customer,
                                    $order->id
                                );
                                if (!$cart_rule->update()) {
                                    $this->errors[] = $this->trans(
                                        'You cannot generate a voucher.',
                                        array(),
                                        'Admin.OrdersCustomers.Notification'
                                    );
                                } else {
                                    $currency = $this->context->currency;
                                    $customer = new Customer((int)($order->id_customer));
                                    $params['{lastname}'] = $customer->lastname;
                                    $params['{firstname}'] = $customer->firstname;
                                    $params['{id_order}'] = $order->id;
                                    $params['{order_name}'] = $order->getUniqReference();
                                    $params['{voucher_amount}'] = Tools::displayPrice(
                                        $cart_rule->reduction_amount,
                                        $currency,
                                        false
                                    );
                                    $params['{voucher_num}'] = $cart_rule->code;
                                    @Mail::Send(
                                        (int)$order->id_lang,
                                        'voucher',
                                        $this->trans(
                                            'New voucher for your order #%s',
                                            array($order->reference),
                                            'Emails.Subject',
                                            $orderLanguage->locale
                                        ),
                                        $params,
                                        $customer->email,
                                        $customer->firstname.' '.$customer->lastname,
                                        null,
                                        null,
                                        null,
                                        null,
                                        _PS_MAIL_DIR_,
                                        true,
                                        (int)$order->id_shop
                                    );
                                }
                            }
                        }
                    } else {
                        if (!empty($refunds)) {
                            $this->errors[] = $this->trans(
                                'Please enter a quantity to proceed with your refund.',
                                array(),
                                'Admin.OrdersCustomers.Notification'
                            );
                        } else {
                            $this->errors[] = $this->trans(
                                'Please enter an amount to proceed with your refund.',
                                array(),
                                'Admin.OrdersCustomers.Notification'
                            );
                        }
                    }
                    if (!count($this->errors)) {
                        Tools::redirectAdmin(
                            self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=30&token='.$this->token
                        );
                    }
                } else {
                    $this->errors[] = $this->trans(
                        'The partial refund data is incorrect.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                }
            } else {
                $this->errors[] = $this->trans(
                    'You do not have permission to delete this.',
                    array(),
                    'Admin.Notifications.Error'
                );
            }
        } elseif (Tools::isSubmit('cancelProduct') && isset($order)) {
            if ($this->tabAccess['delete'] === '1') {
                if (!Tools::isSubmit('id_order_detail') && !Tools::isSubmit('id_customization')) {
                    $this->errors[] = $this->trans(
                        'You must select a product.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } elseif (!Tools::isSubmit('cancelQuantity') && !Tools::isSubmit('cancelCustomizationQuantity')) {
                    $this->errors[] = $this->trans(
                        'You must enter a quantity.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } else {
                    $productList = Tools::getValue('id_order_detail');
                    if ($productList) {
                        $productList = array_map('intval', $productList);
                    }
                    $customizationList = Tools::getValue('id_customization');
                    if ($customizationList) {
                        $customizationList = array_map('intval', $customizationList);
                    }
                    $qtyList = Tools::getValue('cancelQuantity');
                    if ($qtyList) {
                        $qtyList = array_map('intval', $qtyList);
                    }
                    $customizationQtyList = Tools::getValue('cancelCustomizationQuantity');
                    if ($customizationQtyList) {
                        $customizationQtyList = array_map('intval', $customizationQtyList);
                    }
                    $full_product_list = $productList;
                    $full_quantity_list = $qtyList;
                    if ($customizationList) {
                        foreach ($customizationList as $key => $id_order_detail) {
                            $full_product_list[(int)$id_order_detail] = $id_order_detail;
                            if (isset($customizationQtyList[$key])) {
                                $full_quantity_list[(int)$id_order_detail] += $customizationQtyList[$key];
                            }
                        }
                    }
                    if ($productList || $customizationList) {
                        if ($productList) {
                            $id_cart = Cart::getCartIdByOrderId($order->id);
                            $customization_quantities = Customization::countQuantityByCart($id_cart);
                            foreach ($productList as $key => $id_order_detail) {
                                $qtyCancelProduct = abs($qtyList[$key]);
                                if (!$qtyCancelProduct) {
                                    $this->errors[] = $this->trans(
                                        'No quantity has been selected for this product.',
                                        array(),
                                        'Admin.OrdersCustomers.Notification'
                                    );
                                }
                                $order_detail = new OrderDetail($id_order_detail);
                                $customization_quantity = 0;
                                if (array_key_exists(
                                    $order_detail->product_id,
                                    $customization_quantities
                                ) && array_key_exists(
                                    $order_detail->product_attribute_id,
                                    $customization_quantities[$order_detail->product_id]
                                )) {
                                    $customization_product = $customization_quantities[$order_detail->product_id];
                                    $order_product_attribute_id = $order_detail->product_attribute_id;
                                    $customization_quantity =(int)$customization_product[$order_product_attribute_id];
                                }
                                if (($order_detail->product_quantity
                                    - $customization_quantity
                                    - $order_detail->product_quantity_refunded
                                    - $order_detail->product_quantity_return)
                                    < $qtyCancelProduct) {
                                    $this->errors[] = $this->trans(
                                        'An invalid quantity was selected for this product.',
                                        array(),
                                        'Admin.OrdersCustomers.Notification'
                                    );
                                }
                            }
                        }
                        if ($customizationList) {
                            $customization_quantities = Customization::retrieveQuantitiesFromIds(
                                array_keys($customizationList)
                            );
                            foreach ($customizationList as $id_customization => $id_order_detail) {
                                $qtyCancelProduct = abs($customizationQtyList[$id_customization]);
                                $customization_quantity = $customization_quantities[$id_customization];
                                if (!$qtyCancelProduct) {
                                    $this->errors[] = $this->trans(
                                        'No quantity has been selected for this product.',
                                        array(),
                                        'Admin.OrdersCustomers.Notification'
                                    );
                                }
                                if ($qtyCancelProduct
                                    > ($customization_quantity['quantity']
                                    - ($customization_quantity['quantity_refunded']
                                    + $customization_quantity['quantity_returned']))) {
                                    $this->errors[] = $this->trans(
                                        'An invalid quantity was selected for this product.',
                                        array(),
                                        'Admin.OrdersCustomers.Notification'
                                    );
                                }
                            }
                        }
                        if (!count($this->errors) && $productList) {
                            foreach ($productList as $key => $id_order_detail) {
                                $qty_cancel_product = abs($qtyList[$key]);
                                $order_detail = new OrderDetail((int)($id_order_detail));
                                if (!$order->hasBeenDelivered()
                                    || ($order->hasBeenDelivered()
                                    && Tools::isSubmit('reinjectQuantities'))
                                    && $qty_cancel_product > 0) {
                                    $this->reinjectQuantity($order_detail, $qty_cancel_product);
                                }
                                $order_detail = new OrderDetail((int)$id_order_detail);
                                if (!$order->deleteProduct($order, $order_detail, $qty_cancel_product)) {
                                    $this->errors[] = $this->trans(
                                        'An error occurred while attempting to delete the product.',
                                        array(),
                                        'Admin.OrdersCustomers.Notification'
                                    ).' <span class="bold">'.$order_detail->product_name.'</span>';
                                }
                                $order_carrier = new OrderCarrier((int)$order->getIdOrderCarrier());
                                if (Validate::isLoadedObject($order_carrier)) {
                                    $order_carrier->weight = (float)$order->getTotalWeight();
                                    if ($order_carrier->update()) {
                                        $order->weight = sprintf(
                                            "%.3f ".Configuration::get('PS_WEIGHT_UNIT'),
                                            $order_carrier->weight
                                        );
                                    }
                                }
                                if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')
                                    && StockAvailable::dependsOnStock($order_detail->product_id)) {
                                    StockAvailable::synchronize($order_detail->product_id);
                                }
                                Hook::exec(
                                    'actionProductCancel',
                                    array(
                                        'order' => $order,
                                        'id_order_detail' => (int)$id_order_detail
                                    ),
                                    null,
                                    false,
                                    true,
                                    false,
                                    $order->id_shop
                                );
                            }
                        }
                        if (!count($this->errors) && $customizationList) {
                            foreach ($customizationList as $id_customization => $id_order_detail) {
                                $order_detail = new OrderDetail((int)($id_order_detail));
                                $qtyCancelProduct = abs($customizationQtyList[$id_customization]);
                                if (!$order->deleteCustomization($id_customization, $qtyCancelProduct, $order_detail)) {
                                    $this->errors[] = $this->trans(
                                        'An error occurred while attempting to delete product customization.',
                                        array(),
                                        'Admin.OrdersCustomers.Notification'
                                    ).' '.$id_customization;
                                }
                            }
                        }
                        if ((Tools::isSubmit('generateCreditSlip')
                            || Tools::isSubmit('generateDiscount'))
                            && !count($this->errors)) {
                            $customer = new Customer((int)($order->id_customer));
                            $params['{lastname}'] = $customer->lastname;
                            $params['{firstname}'] = $customer->firstname;
                            $params['{id_order}'] = $order->id;
                            $params['{order_name}'] = $order->getUniqReference();
                        }
                        if (Tools::isSubmit('generateCreditSlip') && !count($this->errors)) {
                            $product_list = array();
                            $amount = $order_detail->unit_price_tax_incl * $full_quantity_list[$id_order_detail];
                            $choosen = false;
                            if ((int)Tools::getValue('refund_total_voucher_off') == 1) {
                                $amount -= $voucher = (float)Tools::getValue('order_discount_price');
                            } elseif ((int)Tools::getValue('refund_total_voucher_off') == 2) {
                                $choosen = true;
                                $amount = $voucher = (float)Tools::getValue('refund_total_voucher_choose');
                            }
                            foreach ($full_product_list as $id_order_detail) {
                                $unit_price_tax_incl = $order_detail->unit_price_tax_incl;
                                if (!isset($amount)) {
                                    $amount = $unit_price_tax_incl * $full_quantity_list[$id_order_detail];
                                }
                                $order_detail = new OrderDetail((int)$id_order_detail);
                                $product_list[$id_order_detail] = array(
                                    'id_order_detail' => $id_order_detail,
                                    'quantity' => $full_quantity_list[$id_order_detail],
                                    'unit_price' => $order_detail->unit_price_tax_excl,
                                    'amount' => $amount,
                                );
                            }
                            $shipping = Tools::isSubmit('shippingBack') ? null : false;
                            if (!OrderSlip::create($order, $product_list, $shipping, $voucher, $choosen)) {
                                $this->errors[] = $this->trans(
                                    'A credit slip cannot be generated.',
                                    array(),
                                    'Admin.OrdersCustomers.Notification'
                                );
                            } else {
                                Hook::exec(
                                    'actionOrderSlipAdd',
                                    array(
                                        'order' => $order,
                                        'productList' => $full_product_list,
                                        'qtyList' => $full_quantity_list
                                    ),
                                    null,
                                    false,
                                    true,
                                    false,
                                    $order->id_shop
                                );
                                @Mail::Send(
                                    (int)$order->id_lang,
                                    'credit_slip',
                                    Mail::l('New credit slip regarding your order', (int)$order->id_lang),
                                    $params,
                                    $customer->email,
                                    $customer->firstname.' '.$customer->lastname,
                                    null,
                                    null,
                                    null,
                                    null,
                                    _PS_MAIL_DIR_,
                                    true,
                                    (int)$order->id_shop
                                );
                            }
                        }
                        if (Tools::isSubmit('generateDiscount') && !count($this->errors)) {
                            $cartrule = new CartRule();
                            $language_ids = Language::getIDs((bool)$order);
                            $cartrule->description = sprintf($this->l('Credit card slip for order #%d'), $order->id);
                            foreach ($language_ids as $id_lang) {
                                $cartrule->name[$id_lang] = 'V0C'.(int)($order->id_customer).'O'.(int)($order->id);
                            }
                            $cartrule->code = 'V0C'.(int)($order->id_customer).'O'.(int)($order->id);
                            $cartrule->quantity = 1;
                            $cartrule->quantity_per_user = 1;
                            $cartrule->id_customer = $order->id_customer;
                            $now = time();
                            $cartrule->date_from = date('Y-m-d H:i:s', $now);
                            $cartrule->date_to = date('Y-m-d H:i:s', $now + (3600 * 24 * 365.25));
                            $cartrule->active = 1;
                            $products = $order->getProducts(false, $full_product_list, $full_quantity_list);
                            $total = 0;
                            foreach ($products as $product) {
                                $total += $product['unit_price_tax_incl'] * $product['product_quantity'];
                            }
                            if (Tools::isSubmit('shippingBack')) {
                                $total += $order->total_shipping;
                            }
                            if ((int)Tools::getValue('refund_total_voucher_off') == 1) {
                                $total -= (float)Tools::getValue('order_discount_price');
                            } elseif ((int)Tools::getValue('refund_total_voucher_off') == 2) {
                                $total = (float)Tools::getValue('refund_total_voucher_choose');
                            }
                            $cartrule->reduction_amount = $total;
                            $cartrule->reduction_tax = true;
                            $cartrule->minimum_amount_currency = $order->id_currency;
                            $cartrule->reduction_currency = $order->id_currency;
                            if (!$cartrule->add()) {
                                $this->errors[] = $this->trans(
                                    'You cannot generate a voucher.',
                                    array(),
                                    'Admin.OrdersCustomers.Notification'
                                );
                            } else {
                                foreach ($language_ids as $id_lang) {
                                    $cartrule->name[$id_lang] = 'V'.(int)($cartrule->id).
                                    'C'.(int)($order->id_customer).
                                    'O'.$order->id;
                                }
                                $cartrule->code = 'V'.(int)($cartrule->id).
                                'C'.(int)($order->id_customer).'O'.$order->id;
                                if (!$cartrule->update()) {
                                    $this->errors[] = $this->trans(
                                        'You cannot generate a voucher.',
                                        array(),
                                        'Admin.OrdersCustomers.Notification'
                                    );
                                } else {
                                    $currency = $this->context->currency;
                                    $params['{voucher_amount}'] = Tools::displayPrice(
                                        $cartrule->reduction_amount,
                                        $currency,
                                        false
                                    );
                                    $params['{voucher_num}'] = $cartrule->code;
                                    @Mail::Send(
                                        (int)$order->id_lang,
                                        'voucher',
                                        $this->trans(
                                            'New voucher for your order #%s',
                                            array($order->reference),
                                            'Emails.Subject',
                                            $orderLanguage->locale
                                        ),
                                        $params,
                                        $customer->email,
                                        $customer->firstname.' '.$customer->lastname,
                                        null,
                                        null,
                                        null,
                                        null,
                                        _PS_MAIL_DIR_,
                                        true,
                                        (int)$order->id_shop
                                    );
                                }
                            }
                        }
                    } else {
                         $this->errors[] = $this->trans(
                             'No product or quantity has been selected.',
                             array(),
                             'Admin.OrdersCustomers.Notification'
                         );
                    }
                    if (!count($this->errors)) {
                        Tools::redirectAdmin(
                            self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=31&token='.$this->token
                        );
                    }
                }
            } else {
                $this->errors[] = $this->trans(
                    'You do not have permission to delete this.',
                    array(),
                    'Admin.Notifications.Error'
                );
            }
        } elseif (Tools::isSubmit('messageReaded')) {
            Message::markAsReaded(Tools::getValue('messageReaded'), $this->context->employee->id);
        } elseif (Tools::isSubmit('submitAddPayment') && isset($order)) {
            if ($this->access('edit')) {
                $amount = str_replace(',', '.', Tools::getValue('payment_amount'));
                $currency = new Currency(Tools::getValue('payment_currency'));
                $order_has_invoice = $order->hasInvoice();
                if ($order_has_invoice) {
                    $order_invoice = new OrderInvoice(Tools::getValue('payment_invoice'));
                } else {
                    $order_invoice = null;
                }
                if (!Validate::isLoadedObject($order)) {
                    $this->errors[] = $this->trans(
                        'The order cannot be found',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } elseif (!Validate::isNegativePrice($amount) || !(float)$amount) {
                    $this->errors[] = $this->trans(
                        'The amount is invalid.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } elseif (!Validate::isGenericName(Tools::getValue('payment_method'))) {
                    $this->errors[] = $this->trans(
                        'The selected payment method is invalid.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } elseif (!Validate::isString(Tools::getValue('payment_transaction_id'))) {
                    $this->errors[] = $this->trans(
                        'The transaction ID is invalid.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } elseif (!Validate::isLoadedObject($currency)) {
                    $this->errors[] = $this->trans(
                        'The selected currency is invalid.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } elseif ($order_has_invoice && !Validate::isLoadedObject($order_invoice)) {
                    $this->errors[] = $this->trans(
                        'The invoice is invalid.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } elseif (!Validate::isDate(Tools::getValue('payment_date'))) {
                    $this->errors[] = $this->trans(
                        'The date is invalid',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } else {
                    if (!$order->addOrderPayment(
                        $amount,
                        Tools::getValue('payment_method'),
                        Tools::getValue('payment_transaction_id'),
                        $currency,
                        Tools::getValue('payment_date'),
                        $order_invoice
                    )) {
                        $this->errors[] = $this->trans(
                            'An error occurred during payment.',
                            array(),
                            'Admin.OrdersCustomers.Notification'
                        );
                    } else {
                        Tools::redirectAdmin(
                            self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token
                        );
                    }
                }
            } else {
                $this->errors[] = $this->trans(
                    'You do not have permission to edit this.',
                    array(),
                    'Admin.Notifications.Error'
                );
            }
        } elseif (Tools::isSubmit('submitEditNote')) {
            $note = Tools::getValue('note');
            $order_invoice = new OrderInvoice((int)Tools::getValue('id_order_invoice'));
            if (Validate::isLoadedObject($order_invoice) && Validate::isCleanHtml($note)) {
                if ($this->access('edit')) {
                    $order_invoice->note = $note;
                    if ($order_invoice->save()) {
                        Tools::redirectAdmin(
                            self::$currentIndex.'&id_order='.
                            $order_invoice->id_order.'&vieworder&conf=4&token='.$this->token
                        );
                    } else {
                        $this->errors[] = $this->trans(
                            'The invoice note was not saved.',
                            array(),
                            'Admin.OrdersCustomers.Notification'
                        );
                    }
                } else {
                    $this->errors[] = $this->trans(
                        'You do not have permission to edit this.',
                        array(),
                        'Admin.Notifications.Error'
                    );
                }
            } else {
                $this->errors[] = $this->trans(
                    'Failed to upload the invoice and edit its note.',
                    array(),
                    'Admin.OrdersCustomers.Notification'
                );
            }
        } elseif (Tools::isSubmit('submitAddOrder') && ($id_cart = Tools::getValue('id_cart')) &&
            ($module_name = Tools::getValue('payment_module_name')) &&
            ($id_order_state = Tools::getValue('id_order_state')) && Validate::isModuleName($module_name)) {
            if ($this->access('edit')) {
                if (!Configuration::get('PS_CATALOG_MODE')) {
                    $payment_module = Module::getInstanceByName($module_name);
                } else {
                    $payment_module = new BoOrder();
                }
                $cart = new Cart((int)$id_cart);
                Context::getContext()->currency = new Currency((int)$cart->id_currency);
                Context::getContext()->customer = new Customer((int)$cart->id_customer);
                $bad_delivery = false;
                if (($bad_delivery = (bool)!Address::isCountryActiveById((int)$cart->id_address_delivery))
                    || !Address::isCountryActiveById((int)$cart->id_address_invoice)) {
                    if ($bad_delivery) {
                        $this->errors[] = $this->trans(
                            'This delivery address country is not active.',
                            array(),
                            'Admin.Orderscustomers.Notification'
                        );
                    } else {
                        $this->errors[] = $this->trans(
                            'This invoice address country is not active.',
                            array(),
                            'Admin.Orderscustomers.Notification'
                        );
                    }
                } else {
                    $employee = new Employee((int)Context::getContext()->cookie->id_employee);
                    if ($module_name == 'vrpayecommerce') {
                        $recurringCart = array();
                        $backendOrderResult = array();
                        $recurringCart['id'] = Tools::getValue('reccuring_id');
                        $recurringCart['currency'] = Context::getContext()->currency;
                        $recurringCart['order_total'] = $cart->getOrderTotal(true, Cart::BOTH);
                        $recurringCart['transaction_id'] = date('ymd') . Tools::getValue('id_cart');
                        if (empty($_POST['reccuring_id'])) {
                            $backendOrderResult['payment_desc'] = $payment_module->displayName;
                            $backendOrderResult['order_status'] = Configuration::get('PS_OS_ERROR');
                        } else {
                            $backendOrderResult = $payment_module->createBackendOrder($recurringCart);
                        }
                        if (!is_null($backendOrderResult['response'])) {
                            $this->context->cookie->vrpayecommerce_ssl_error = $backendOrderResult['response'];
                        }
                        $payment_module->validateOrder(
                            (int)$cart->id,
                            $backendOrderResult['order_status'],
                            $recurringCart['order_total'],
                            $backendOrderResult['payment_desc'],
                            null,
                            array(),
                            $recurringCart['currency']->id,
                            false,
                            $cart->secure_key
                        );
                        $payment_module->updateOrderId(
                            $backendOrderResult['ref_id'],
                            $payment_module->currentOrder
                        );
                    } else {
                        $payment_module->validateOrder(
                            (int)$cart->id,
                            (int)$id_order_state,
                            $cart->getOrderTotal(true, Cart::BOTH),
                            $payment_module->displayName,
                            $this->l('Manual order -- Employee:').' '.
                            Tools::substr($employee->firstname, 0, 1).'. '.$employee->lastname,
                            array(),
                            null,
                            false,
                            $cart->secure_key
                        );
                    }
                    if ($payment_module->currentOrder) {
                        Tools::redirectAdmin(
                            self::$currentIndex.'&id_order='
                            .$payment_module->currentOrder.'&vieworder'.'&token='.$this->token
                        );
                    }
                }
            } else {
                $this->errors[] = $this->trans(
                    'You do not have permission to add this.',
                    array(),
                    'Admin.Notifications.Error'
                );
            }
        } elseif ((Tools::isSubmit('submitAddressShipping')
            || Tools::isSubmit('submitAddressInvoice'))
            && isset($order)) {
            if ($this->access('edit')) {
                $address = new Address(Tools::getValue('id_address'));
                if (Validate::isLoadedObject($address)) {
                    if (Tools::isSubmit('submitAddressShipping')) {
                        $order->id_address_delivery = $address->id;
                    } elseif (Tools::isSubmit('submitAddressInvoice')) {
                        $order->id_address_invoice = $address->id;
                    }
                    $order->update();
                    Tools::redirectAdmin(
                        self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token
                    );
                } else {
                    $this->errors[] = $this->trans(
                        'This address can\'t be loaded',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                }
            } else {
                $this->errors[] = $this->trans(
                    'You do not have permission to edit this.',
                    array(),
                    'Admin.Notifications.Error'
                );
            }
        } elseif (Tools::isSubmit('submitChangeCurrency') && isset($order)) {
            if ($this->access('edit')) {
                if (Tools::getValue('new_currency') != $order->id_currency && !$order->valid) {
                    $old_currency = new Currency($order->id_currency);
                    $currency = new Currency(Tools::getValue('new_currency'));
                    if (!Validate::isLoadedObject($currency)) {
                        throw new PrestaShopException('Can\'t load Currency object');
                    }
                    foreach ($order->getOrderDetailList() as $row) {
                        $order_detail = new OrderDetail($row['id_order_detail']);
                        $fields = array(
                            'ecotax',
                            'product_price',
                            'reduction_amount',
                            'total_shipping_price_tax_excl',
                            'total_shipping_price_tax_incl',
                            'total_price_tax_incl',
                            'total_price_tax_excl',
                            'product_quantity_discount',
                            'purchase_supplier_price',
                            'reduction_amount',
                            'reduction_amount_tax_incl',
                            'reduction_amount_tax_excl',
                            'unit_price_tax_incl',
                            'unit_price_tax_excl',
                            'original_product_price'
                        );
                        foreach ($fields as $field) {
                            $order_detail->{$field} = Tools::convertPriceFull(
                                $order_detail->{$field},
                                $old_currency,
                                $currency
                            );
                        }
                        $order_detail->update();
                        $order_detail->updateTaxAmount($order);
                    }
                    $id_order_carrier = (int)$order->getIdOrderCarrier();
                    if ($id_order_carrier) {
                        $order_carrier = $order_carrier = new OrderCarrier((int)$order->getIdOrderCarrier());
                        $order_carrier->shipping_cost_tax_excl = (float)Tools::convertPriceFull(
                            $order_carrier->shipping_cost_tax_excl,
                            $old_currency,
                            $currency
                        );
                        $order_carrier->shipping_cost_tax_incl = (float)Tools::convertPriceFull(
                            $order_carrier->shipping_cost_tax_incl,
                            $old_currency,
                            $currency
                        );
                        $order_carrier->update();
                    }
                    $fields = array(
                        'total_discounts',
                        'total_discounts_tax_incl',
                        'total_discounts_tax_excl',
                        'total_discount_tax_excl',
                        'total_discount_tax_incl',
                        'total_paid',
                        'total_paid_tax_incl',
                        'total_paid_tax_excl',
                        'total_paid_real',
                        'total_products',
                        'total_products_wt',
                        'total_shipping',
                        'total_shipping_tax_incl',
                        'total_shipping_tax_excl',
                        'total_wrapping',
                        'total_wrapping_tax_incl',
                        'total_wrapping_tax_excl',
                    );
                    $invoices = $order->getInvoicesCollection();
                    if ($invoices) {
                        foreach ($invoices as $invoice) {
                            foreach ($fields as $field) {
                                if (isset($invoice->$field)) {
                                    $invoice->{$field} = Tools::convertPriceFull(
                                        $invoice->{$field},
                                        $old_currency,
                                        $currency
                                    );
                                }
                            }
                            $invoice->save();
                        }
                    }
                    foreach ($fields as $field) {
                        if (isset($order->$field)) {
                            $order->{$field} = Tools::convertPriceFull($order->{$field}, $old_currency, $currency);
                        }
                    }
                    $order->id_currency = $currency->id;
                    $order->conversion_rate = (float)$currency->conversion_rate;
                    $order->update();
                } else {
                    $this->errors[] = $this->trans(
                        'You cannot change the currency.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                }
            } else {
                $this->errors[] = $this->trans(
                    'You do not have permission to edit this.',
                    array(),
                    'Admin.Notifications.Error'
                );
            }
        } elseif (Tools::isSubmit('submitGenerateInvoice') && isset($order)) {
            if (!Configuration::get('PS_INVOICE', null, null, $order->id_shop)) {
                $this->errors[] = $this->trans(
                    'Invoice management has been disabled.',
                    array(),
                    'Admin.OrdersCustomers.Notification'
                );
            } elseif ($order->hasInvoice()) {
                $this->errors[] = $this->trans(
                    'This order already has an invoice.',
                    array(),
                    'Admin.OrdersCustomers.Notification'
                );
            } else {
                $order->setInvoice(true);
                Tools::redirectAdmin(
                    self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token
                );
            }
        } elseif (Tools::isSubmit('submitDeleteVoucher') && isset($order)) {
            if ($this->access('edit')) {
                $order_cart_rule = new OrderCartRule(Tools::getValue('id_order_cart_rule'));
                if (Validate::isLoadedObject($order_cart_rule) && $order_cart_rule->id_order == $order->id) {
                    if ($order_cart_rule->id_order_invoice) {
                        $order_invoice = new OrderInvoice($order_cart_rule->id_order_invoice);
                        if (!Validate::isLoadedObject($order_invoice)) {
                            throw new PrestaShopException('Can\'t load Order Invoice object');
                        }
                        $order_invoice->total_discount_tax_excl -= $order_cart_rule->value_tax_excl;
                        $order_invoice->total_discount_tax_incl -= $order_cart_rule->value;
                        $order_invoice->total_paid_tax_excl += $order_cart_rule->value_tax_excl;
                        $order_invoice->total_paid_tax_incl += $order_cart_rule->value;
                        $order_invoice->update();
                    }
                    $order->total_discounts -= $order_cart_rule->value;
                    $order->total_discounts_tax_incl -= $order_cart_rule->value;
                    $order->total_discounts_tax_excl -= $order_cart_rule->value_tax_excl;
                    $order->total_paid += $order_cart_rule->value;
                    $order->total_paid_tax_incl += $order_cart_rule->value;
                    $order->total_paid_tax_excl += $order_cart_rule->value_tax_excl;
                    $order_cart_rule->delete();
                    $order->update();
                    Tools::redirectAdmin(
                        self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token
                    );
                } else {
                    $this->errors[] = $this->trans(
                        'You cannot edit this cart rule.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                }
            } else {
                $this->errors[] = $this->trans(
                    'You do not have permission to edit this.',
                    array(),
                    'Admin.Notifications.Error'
                );
            }
        } elseif (Tools::isSubmit('submitNewVoucher') && isset($order)) {
            if ($this->access('edit')) {
                if (!Tools::getValue('discount_name')) {
                    $this->errors[] = $this->trans(
                        'You must specify a name in order to create a new discount.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } else {
                    if ($order->hasInvoice()) {
                        if (!Tools::isSubmit('discount_all_invoices')) {
                            $order_invoice = new OrderInvoice(Tools::getValue('discount_invoice'));
                            if (!Validate::isLoadedObject($order_invoice)) {
                                throw new PrestaShopException('Can\'t load Order Invoice object');
                            }
                        }
                    }
                    $cart_rules = array();
                    $discount_value = (float)str_replace(',', '.', Tools::getValue('discount_value'));
                    switch (Tools::getValue('discount_type')) {
                        case 1:
                            if ($discount_value < 100) {
                                if (isset($order_invoice)) {
                                    $cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round(
                                        $order_invoice->total_paid_tax_incl * $discount_value / 100,
                                        2
                                    );
                                    $cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round(
                                        $order_invoice->total_paid_tax_excl * $discount_value / 100,
                                        2
                                    );
                                    $this->applyDiscountOnInvoice(
                                        $order_invoice,
                                        $cart_rules[$order_invoice->id]['value_tax_incl'],
                                        $cart_rules[$order_invoice->id]['value_tax_excl']
                                    );
                                } elseif ($order->hasInvoice()) {
                                    $order_invoices_collection = $order->getInvoicesCollection();
                                    foreach ($order_invoices_collection as $order_invoice) {
                                        $cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round(
                                            $order_invoice->total_paid_tax_incl * $discount_value / 100,
                                            2
                                        );
                                        $cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round(
                                            $order_invoice->total_paid_tax_excl * $discount_value / 100,
                                            2
                                        );
                                        $this->applyDiscountOnInvoice(
                                            $order_invoice,
                                            $cart_rules[$order_invoice->id]['value_tax_incl'],
                                            $cart_rules[$order_invoice->id]['value_tax_excl']
                                        );
                                    }
                                } else {
                                    $cart_rules[0]['value_tax_incl'] = Tools::ps_round(
                                        $order->total_paid_tax_incl * $discount_value / 100,
                                        2
                                    );
                                    $cart_rules[0]['value_tax_excl'] = Tools::ps_round(
                                        $order->total_paid_tax_excl * $discount_value / 100,
                                        2
                                    );
                                }
                            } else {
                                $this->errors[] = $this->trans(
                                    'The discount value is invalid.',
                                    array(),
                                    'Admin.OrdersCustomers.Notification'
                                );
                            }
                            break;
                        case 2:
                            if (isset($order_invoice)) {
                                if ($discount_value > $order_invoice->total_paid_tax_incl) {
                                    $this->errors[] = $this->trans(
                                        'The discount value is greater than the order invoice total.',
                                        array(),
                                        'Admin.OrdersCustomers.Notification'
                                    );
                                } else {
                                    $cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round(
                                        $discount_value,
                                        2
                                    );
                                    $cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round(
                                        $discount_value / (1 + ($order->getTaxesAverageUsed() / 100)),
                                        2
                                    );
                                    $this->applyDiscountOnInvoice(
                                        $order_invoice,
                                        $cart_rules[$order_invoice->id]['value_tax_incl'],
                                        $cart_rules[$order_invoice->id]['value_tax_excl']
                                    );
                                }
                            } elseif ($order->hasInvoice()) {
                                $order_invoices_collection = $order->getInvoicesCollection();
                                foreach ($order_invoices_collection as $order_invoice) {
                                    if ($discount_value > $order_invoice->total_paid_tax_incl) {
                                        $this->errors[] = $this->trans(
                                            'The discount value is greater than the order invoice total.',
                                            array(),
                                            'Admin.OrdersCustomers.Notification'
                                        )
                                        .$order_invoice->getInvoiceNumberFormatted(
                                            Context::getContext()->language->id,
                                            (int)$order->id_shop
                                        ).
                                        ')';
                                    } else {
                                        $cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round(
                                            $discount_value,
                                            2
                                        );
                                        $cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round(
                                            $discount_value / (1 + ($order->getTaxesAverageUsed() / 100)),
                                            2
                                        );
                                        $this->applyDiscountOnInvoice(
                                            $order_invoice,
                                            $cart_rules[$order_invoice->id]['value_tax_incl'],
                                            $cart_rules[$order_invoice->id]['value_tax_excl']
                                        );
                                    }
                                }
                            } else {
                                if ($discount_value > $order->total_paid_tax_incl) {
                                    $this->errors[] = $this->trans(
                                        'The discount value is greater than the order total.',
                                        array(),
                                        'Admin.OrdersCustomers.Notification'
                                    );
                                } else {
                                    $cart_rules[0]['value_tax_incl'] = Tools::ps_round(
                                        $discount_value,
                                        2
                                    );
                                    $cart_rules[0]['value_tax_excl'] = Tools::ps_round(
                                        $discount_value / (1 + ($order->getTaxesAverageUsed() / 100)),
                                        2
                                    );
                                }
                            }
                            break;
                        case 3:
                            if (isset($order_invoice)) {
                                if ($order_invoice->total_shipping_tax_incl > 0) {
                                    $cart_rules[$order_invoice->id]['value_tax_incl'] =
                                    $order_invoice->total_shipping_tax_incl;
                                    $cart_rules[$order_invoice->id]['value_tax_excl'] =
                                    $order_invoice->total_shipping_tax_excl;
                                    $this->applyDiscountOnInvoice(
                                        $order_invoice,
                                        $cart_rules[$order_invoice->id]['value_tax_incl'],
                                        $cart_rules[$order_invoice->id]['value_tax_excl']
                                    );
                                }
                            } elseif ($order->hasInvoice()) {
                                $order_invoices_collection = $order->getInvoicesCollection();
                                foreach ($order_invoices_collection as $order_invoice) {
                                    if ($order_invoice->total_shipping_tax_incl <= 0) {
                                        continue;
                                    }
                                    $cart_rules[$order_invoice->id]['value_tax_incl'] =
                                    $order_invoice->total_shipping_tax_incl;
                                    $cart_rules[$order_invoice->id]['value_tax_excl'] =
                                    $order_invoice->total_shipping_tax_excl;
                                    $this->applyDiscountOnInvoice(
                                        $order_invoice,
                                        $cart_rules[$order_invoice->id]['value_tax_incl'],
                                        $cart_rules[$order_invoice->id]['value_tax_excl']
                                    );
                                }
                            } else {
                                $cart_rules[0]['value_tax_incl'] = $order->total_shipping_tax_incl;
                                $cart_rules[0]['value_tax_excl'] = $order->total_shipping_tax_excl;
                            }
                            break;
                        default:
                            $this->errors[] = $this->trans(
                                'The discount type is invalid.',
                                array(),
                                'Admin.OrdersCustomers.Notification'
                            );
                    }
                    $res = true;
                    foreach ($cart_rules as &$cart_rule) {
                        $cartRuleObj = new CartRule();
                        $cartRuleObj->date_from = date(
                            'Y-m-d H:i:s',
                            strtotime(
                                '-1 hour',
                                strtotime($order->date_add)
                            )
                        );
                        $cartRuleObj->date_to = date(
                            'Y-m-d H:i:s',
                            strtotime('+1 hour')
                        );
                        $cartRuleObj->name[Configuration::get('PS_LANG_DEFAULT')] = Tools::getValue('discount_name');
                        $cartRuleObj->quantity = 0;
                        $cartRuleObj->quantity_per_user = 1;
                        if (Tools::getValue('discount_type') == 1) {
                            $cartRuleObj->reduction_percent = $discount_value;
                        } elseif (Tools::getValue('discount_type') == 2) {
                            $cartRuleObj->reduction_amount = $cart_rule['value_tax_excl'];
                        } elseif (Tools::getValue('discount_type') == 3) {
                            $cartRuleObj->free_shipping = 1;
                        }
                        $cartRuleObj->active = 0;
                        if ($res = $cartRuleObj->add()) {
                            $cart_rule['id'] = $cartRuleObj->id;
                        } else {
                            break;
                        }
                    }
                    if ($res) {
                        foreach ($cart_rules as $id_order_invoice => $cart_rule) {
                            $order_cart_rule = new OrderCartRule();
                            $order_cart_rule->id_order = $order->id;
                            $order_cart_rule->id_cart_rule = $cart_rule['id'];
                            $order_cart_rule->id_order_invoice = $id_order_invoice;
                            $order_cart_rule->name = Tools::getValue('discount_name');
                            $order_cart_rule->value = $cart_rule['value_tax_incl'];
                            $order_cart_rule->value_tax_excl = $cart_rule['value_tax_excl'];
                            $res &= $order_cart_rule->add();
                            $order->total_discounts += $order_cart_rule->value;
                            $order->total_discounts_tax_incl += $order_cart_rule->value;
                            $order->total_discounts_tax_excl += $order_cart_rule->value_tax_excl;
                            $order->total_paid -= $order_cart_rule->value;
                            $order->total_paid_tax_incl -= $order_cart_rule->value;
                            $order->total_paid_tax_excl -= $order_cart_rule->value_tax_excl;
                        }
                        $res &= $order->update();
                    }
                    if ($res) {
                        Tools::redirectAdmin(
                            self::$currentIndex.'&id_order='.$order->id.'
                            &vieworder&conf=4&token='.$this->token
                        );
                    } else {
                        $this->errors[] = $this->trans(
                            'An error occurred during the OrderCartRule creation',
                            array(),
                            'Admin.OrdersCustomers.Notification'
                        );
                    }
                }
            } else {
                $this->errors[] = $this->trans(
                    'You do not have permission to edit this.',
                    array(),
                    'Admin.Notifications.Error'
                );
            }
        } elseif (Tools::isSubmit('sendStateEmail')
            && Tools::getValue('sendStateEmail') > 0
            && Tools::getValue('id_order') > 0) {
            if ($this->access('edit')) {
                $order_state = new OrderState((int)Tools::getValue('sendStateEmail'));
                if (!Validate::isLoadedObject($order_state)) {
                    $this->errors[] = $this->trans(
                        'An error occurred while loading order status.',
                        array(),
                        'Admin.OrdersCustomers.Notification'
                    );
                } else {
                    $history = new OrderHistory((int)Tools::getValue('id_order_history'));
                    $carrier = new Carrier($order->id_carrier, $order->id_lang);
                    $templateVars = array();
                    if ($order_state->id == Configuration::get('PS_OS_SHIPPING') && $order->shipping_number) {
                        $templateVars = array(
                            '{followup}' => str_replace(
                                '@',
                                $order->shipping_number,
                                $carrier->url
                            )
                        );
                    }
                    if ($history->sendEmail($order, $templateVars)) {
                        Tools::redirectAdmin(
                            self::$currentIndex.'&id_order='.$order->id.'
                            &vieworder&conf=10&token='.$this->token
                        );
                    } else {
                        $this->errors[] = $this->trans(
                            'An error occurred while sending the e-mail to the customer.',
                            array(),
                            'Admin.OrdersCustomers.Notification'
                        );
                    }
                }
            } else {
                $this->errors[] = $this->trans(
                    'You do not have permission to edit this.',
                    array(),
                    'Admin.Notifications.Error'
                );
            }
        }
        parent::postProcess();
    }

    public function ajaxProcessChangePaymentMethod()
    {
        $customer = new Customer(Tools::getValue('id_customer'));
        $modules = Module::getAuthorizedModules($customer->id_default_group);
        $authorized_modules = array();
        if (!Validate::isLoadedObject($customer) || !is_array($modules)) {
            die(Tools::jsonEncode(array('result' => false)));
        }
        foreach ($modules as $module) {
            $authorized_modules[] = (int)$module['id_module'];
        }
        $payment_modules = array();
        foreach (PaymentModule::getInstalledPaymentModules() as $key => $p_module) {
            if (in_array((int)$p_module['id_module'], $authorized_modules)) {
                $payment_modules[] = Module::getInstanceById((int)$p_module['id_module']);
            }
            if ($payment_modules[$key]->name == 'vrpayecommerce') {
                $payment_recurring = $payment_modules[$key]->getAccountPayment($customer->id);
                if (!Configuration::get('VRPAYECOMMERCE_GENERAL_RECURRING') || !$payment_recurring) {
                    unset($payment_modules[$key]);
                }
            }
        }
        $this->context->smarty->assign(array(
            'payment_modules' => $payment_modules,
            'payment_recurring' => $payment_recurring,
            'this_path' => Tools::getShopDomain(true, true).__PS_BASE_URI__.'modules/vrpayecommerce/',
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/vrpayecommerce/'
        ));
        die(Tools::jsonEncode(array(
            'result' => true,
            'view' => $this->context->smarty->createTemplate(
                _PS_MODULE_DIR_.'vrpayecommerce/views/templates/admin/select_payment.tpl',
                $this->context->smarty
            )->fetch(),
        )));
    }
}
