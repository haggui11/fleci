{*

* 2015 VR pay eCommerce
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
*
*  @author VR pay eCommerce <info@vr-epay.info>
*  @copyright  2015 VR pay eCommerce
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="row">
    <div class="col-lg-12">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-credit-card"></i>
                {if {l s='BACKEND_GENERAL_INFORMATION' mod='vrpayecommerce'} == "BACKEND_GENERAL_INFORMATION"}PAYMENT INFORMATION{else}{l s='BACKEND_GENERAL_INFORMATION' mod='vrpayecommerce'}{/if}
                
            </div>
            <div class="col-xs-6">
                <dl class="well list-detail">
                    {if !empty($transaction_id)}
                        <dt>{if {l s='BACKEND_TT_TRANSACTION_ID' mod='vrpayecommerce'} == "BACKEND_TT_TRANSACTION_ID"}Transaction ID{else}{l s='BACKEND_TT_TRANSACTION_ID' mod='vrpayecommerce'}{/if}</dt>
                        <dd>{$transaction_id|escape:'htmlall':'UTF-8'}</dd>
                    {/if}
                    {if !empty($in_review)}
                        <form method='POST' action="">
                            <input type="hidden" name='id_order' value="{$id_order|escape:'htmlall':'UTF-8'}">
                            <button type="submit" class="btn btn-primary pull-right" name="vrpayecommerceUpdateOrder">
                                {if {l s='BACKEND_BT_UPDATE_ORDER' mod='vrpayecommerce'} == "BACKEND_BT_UPDATE_ORDER"}Update Order{else}{l s='BACKEND_BT_UPDATE_ORDER' mod='vrpayecommerce'}{/if}
                            </button>
                        </form>
                    {/if}
                    <div style="clear:both"></div>
                </dl>
            </div>
            <div style="clear:both"></div>
        </div>
    </div>
</div>
