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
    function validateHolder(e){
        var holder = jQuery('.wpwl-control-cardHolder').val();
        if (holder.trim().length < 2){
            jQuery('.wpwl-control-cardHolder').addClass('wpwl-has-error').after('<div class="wpwl-hint wpwl-hint-cardHolderError">{if {l s='FRONTEND_INVALID_CARD_HOLDER' mod='vrpayecommerce'} == 'FRONTEND_INVALID_CARD_HOLDER'}Invalid card holder{else}{l s='FRONTEND_INVALID_CARD_HOLDER' mod='vrpayecommerce'}{/if}</div>');
            return false;
        }
        return true;
    }
    var wpwlOptions = {
    	locale: "{$lang|escape:'htmlall':'UTF-8'}",
		style: "card",
        onReady: function(){
            jQuery('.wpwl-form-card').find('.wpwl-button-pay').on('click', function(e){
                validateHolder(e);
            });
			var buttonCancel = "<a href='{$cancelUrl|escape:'html':'UTF-8'}' class='wpwl-button btn_cancel'>{if {l s='FRONTEND_BT_CANCEL' mod='vrpayecommerce'} == 'FRONTEND_BT_CANCEL'}Cancel{else}{l s='FRONTEND_BT_CANCEL' mod='vrpayecommerce'}{/if}</a>";
			var ttTestMode = "<div class='testmode'>{if {l s='FRONTEND_TT_TESTMODE' mod='vrpayecommerce'} == 'FRONTEND_TT_TESTMODE'}THIS IS A TEST. NO REAL MONEY WILL BE TRANSFERED{else}{l s='FRONTEND_TT_TESTMODE' mod='vrpayecommerce'}{/if}</div>";
			var headerWidget = "<h2 style='text-align: center'>{if {l s='FRONTEND_RECURRING_WIDGET_HEADER2' mod='vrpayecommerce'} == 'FRONTEND_RECURRING_WIDGET_HEADER2'}Use alternative payment data{else}{l s='FRONTEND_RECURRING_WIDGET_HEADER2' mod='vrpayecommerce'}{/if}</h2>";
            jQuery('form.wpwl-form').find('.wpwl-button').before(buttonCancel);
            {if !empty($frameTestMode) }
	            jQuery(".wpwl-container").wrap( "<div class='frametest'></div>").before(ttTestMode);   
            {/if}
			{if !empty($recurring) }
            	jQuery('#wpwl-registrations').after(headerWidget);
            {/if}
		},
        onBeforeSubmitCard: function(e){
            return validateHolder(e);
        },
		registrations: {
        	hideInitialPaymentForms: false,
        	requireCvv: false
    	}
    }
</script>

{if $recurring}
	{if !empty($registrations)}
	  <h2 style="text-align: center">{if {l s='FRONTEND_RECURRING_WIDGET_HEADER1' mod='vrpayecommerce'} == 'FRONTEND_RECURRING_WIDGET_HEADER1'}Use stored payment data{else}{l s='FRONTEND_RECURRING_WIDGET_HEADER1' mod='vrpayecommerce'}{/if}</h2>
	{else}
	  <h2 style="text-align: center">{if {l s='FRONTEND_MC_PAYANDSAFE' mod='vrpayecommerce'} == 'FRONTEND_MC_PAYANDSAFE'}Pay and Save Payment Information{else}{l s='FRONTEND_MC_PAYANDSAFE' mod='vrpayecommerce'}{/if}</h2>
	{/if}
{/if}

<script src="{$paymentWidgetUrl|escape:'html':'UTF-8'}" type="text/javascript"></script>
<form action="{$responseUrl|escape:'html':'UTF-8'}" class="paymentWidgets">{$paymentBrand|escape:'htmlall':'UTF-8'}</form>
{if $merchantLocation}
    <div class="wpwl-label wpwl-label-custom" style="margin: 10px auto 24px auto; max-width: 24em">{if {l s='FRONTEND_MERCHANT_LOCATION_DESC' mod='vrpayecommerce'} == 'FRONTEND_MERCHANT_LOCATION_DESC'}Payee: {else}{l s='FRONTEND_MERCHANT_LOCATION_DESC' mod='vrpayecommerce'}{/if} {$merchantLocation|escape:'htmlall':'UTF-8'}</div>
{/if}
{/block}
