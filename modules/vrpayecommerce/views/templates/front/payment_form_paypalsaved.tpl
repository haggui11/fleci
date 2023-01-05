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
{extends file='page.tpl'}
{block name="content"}

{if $frameTestMode }
    <!--[if lte IE 9]><link rel="stylesheet" href="https://test.oppwa.com/v1/paymentWidgets/css/card.min.css" type="text/css" /><![endif]-->
{else}
    <!--[if lte IE 9]><link rel="stylesheet" href="https://oppwa.com/v1/paymentWidgets/css/card.min.css" type="text/css" /><![endif]-->
{/if}

<link href="{$this_path|escape:'html':'UTF-8'}views/css/formpayment.css" rel="stylesheet" type="text/css">

<script type="text/javascript"> 
    var wpwlOptions = {
        locale: "{$lang|escape:'htmlall':'UTF-8'}",
        style: "card",
        onReady: function(){
        var buttonCancel = "<a href='{$cancelUrl|escape:'html':'UTF-8'}' class='wpwl-button btn_cancel'>{if {l s='FRONTEND_BT_CANCEL' mod='vrpayecommerce'} == 'FRONTEND_BT_CANCEL'}Cancel{else}{l s='FRONTEND_BT_CANCEL' mod='vrpayecommerce'}{/if}</a>";
        var ttTestMode = "<div class='testmode'>{if {l s='FRONTEND_TT_TESTMODE' mod='vrpayecommerce'} == 'FRONTEND_TT_TESTMODE'}THIS IS A TEST. NO REAL MONEY WILL BE TRANSFERED{else}{l s='FRONTEND_TT_TESTMODE' mod='vrpayecommerce'}{/if}</div>";
        var headerWidget = "<h2 style='text-align: center'>{if {l s='FRONTEND_RECURRING_WIDGET_HEADER2' mod='vrpayecommerce'} == 'FRONTEND_RECURRING_WIDGET_HEADER2'}Use alternative payment data{else}{l s='FRONTEND_RECURRING_WIDGET_HEADER2' mod='vrpayecommerce'}{/if}</h2>";
        var btnPayNow = "<button type='submit' name='pay' class='wpwl-button wpwl-button-pay'>{if {l s='FRONTEND_BT_PAYNOW' mod='vrpayecommerce'} == 'FRONTEND_BT_PAYNOW'}Pay Now{else}{l s='FRONTEND_BT_PAYNOW' mod='vrpayecommerce'}{/if}</button>";
            jQuery('form.wpwl-form').find('.wpwl-button').after(buttonCancel);
            jQuery('form.wpwl-form').css("display","block");
            var clearFloat = "<div style='clear:both'></div>";
            jQuery('form.wpwl-form-virtualAccount-PAYPAL').find('.wpwl-button-brand').wrap( "<div class='payment-brand'></div>");
            jQuery('form.wpwl-form-virtualAccount-PAYPAL').find('.btn_cancel').after(btnPayNow);
            jQuery('form.wpwl-form-virtualAccount-PAYPAL').find('.wpwl-button-pay').after(clearFloat);
            {if $frameTestMode }
                jQuery(".wpwl-container").wrap( "<div class='frametest'></div>");
                jQuery('.wpwl-container').before(ttTestMode);   
            {/if}
            {if $recurring}
                jQuery('#wpwl-registrations').after(headerWidget);
            {/if}
        },
        registrations: {
            hideInitialPaymentForms: false,
            requireCvv: false
        }
    }
</script>
  
{if !empty($registrations)}
    <h2 style="text-align: center">{if {l s='FRONTEND_RECURRING_WIDGET_HEADER1' mod='vrpayecommerce'} == 'FRONTEND_RECURRING_WIDGET_HEADER1'}Use stored payment data{else}{l s='FRONTEND_RECURRING_WIDGET_HEADER1' mod='vrpayecommerce'}{/if}</h2>
{else}
    <h2 style="text-align: center">{if {l s='FRONTEND_MC_PAYANDSAFE' mod='vrpayecommerce'} == 'FRONTEND_MC_PAYANDSAFE'}Pay and Save Payment Information{else}{l s='FRONTEND_MC_PAYANDSAFE' mod='vrpayecommerce'}{/if}</h2>
{/if}

{if !empty($registrations)}
    {literal}
        <script>
            jQuery(document).ready(function() {
                jQuery( "input[type=radio][name=registrationId]" ).on( "click", function() {
                jQuery(".wpwl-group-registration").removeClass("wpwl-selected");
                    jQuery(".regid"+this.value).addClass("wpwl-selected");
                });
            });
        </script>
    {/literal}
    <div id="wpwl-registrations">
    <div class="wpwl-container wpwl-container-registration wpwl-clearfix" style="display: block;">
        <form class="wpwl-form wpwl-form-registrations wpwl-form-has-inputs wpwl-clearfix" action="{$responseUrl|escape:'html':'UTF-8'}" method="POST" lang="en" accept-charset="UTF-8" data-action="submit-registration">
            {foreach from=$registrations key=k item=v}
                {if $v.payment_default == 1}
                    <div class="regid{$v.ref_id|escape:'htmlall':'UTF-8'} wpwl-group wpwl-group-registration wpwl-clearfix wpwl-selected ">
                {else}
                    <div class="regid{$v.ref_id|escape:'htmlall':'UTF-8'} wpwl-group wpwl-group-registration wpwl-clearfix ">
                {/if}
                    <label class="wpwl-registration">
                        <div class="wpwl-wrapper-registration wpwl-wrapper-registration-registrationId">
                            {if $v.payment_default == 1}
                                <input type="radio" name="registrationId" value="{$v.ref_id|escape:'htmlall':'UTF-8'}" checked="checked" data-action="change-registration">
                            {else}
                                <input type="radio" name="registrationId" value="{$v.ref_id|escape:'htmlall':'UTF-8'}" data-action="change-registration">
                            {/if}
                        </div>
                        <div class="wpwl-wrapper-registration wpwl-wrapper-registration-details">
                            <div class="wpwl-wrapper-registration wpwl-wrapper-registration-email">{$v.email|escape:'htmlall':'UTF-8'}</div>
                            <div class="wpwl-wrapper-registration wpwl-wrapper-registration-holder">{$v.holder|escape:'htmlall':'UTF-8'}</div>
                        </div>
                        <div class="wpwl-wrapper-registration wpwl-wrapper-registration-cvv"></div>
                    </label>
               </div>
            {/foreach}
            <div class="wpwl-group wpwl-group-submit wpwl-clearfix">
                <div class="wpwl-wrapper wpwl-wrapper-submit">
                    <button type="submit" name="pay" class="wpwl-button wpwl-button-pay">{if {l s='FRONTEND_BT_PAYNOW' mod='vrpayecommerce'} == 'FRONTEND_BT_PAYNOW'}Pay Now{else}{l s='FRONTEND_BT_PAYNOW' mod='vrpayecommerce'}{/if}</button>
                </div>
            </div>
        </form>
        <iframe name="registrations-target" class="wpwl-target" src="about:blank" frameborder="0"></iframe>
        </div>
    </div>
{/if} 

<script src="{$paymentWidgetUrl|escape:'html':'UTF-8'}" type="text/javascript"></script>
<form action="{$responseUrl|escape:'html':'UTF-8'}" class="paymentWidgets">{$paymentBrand|escape:'htmlall':'UTF-8'}</form>

{/block}
