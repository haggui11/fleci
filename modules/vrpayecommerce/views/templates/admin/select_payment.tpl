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

<script>
	$("form").submit(function(){
		var paymentName = $('input[name=reccuring_id]:checked').val();
		var alertWarning = '<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert">×</button>Please select payment method for your order.</div>';
		if(paymentName == null && $('#payment_module_name').val() == 'vrpayecommerce'){
			$('#recurring').before(alertWarning);
	    	return false;
	    }
	
	});

	if($('#payment_module_name').val() == 'vrpayecommerce'){
		$('#id_order_state').parent().parent().hide();
	}

	$("#payment_module_name").click(function(){
		showRecurringPayments();

	});
	$("#payment_module_name").change(function(){
		showRecurringPayments();

	});
	$(document).ready(function(){
		showRecurringPayments();
	});
	
	function showRecurringPayments(){
		if($("#payment_module_name").val() == 'vrpayecommerce')
		{
			$('#recurring').show();
			$('#id_order_state').parent().parent().hide();

		}else{
			$('#recurring').hide();
			$('#id_order_state').parent().parent().show();
		}
	}
</script>
<link href="{$this_path|escape:'htmlall':'UTF-8'}views/css/account_payment_form.css" rel="stylesheet" type="text/css">
<select name="payment_module_name" id="payment_module_name">
    {if !$PS_CATALOG_MODE}
    	{foreach from=$payment_modules item='module'}
    		<option value="{$module->name|escape:'htmlall':'UTF-8'}" {if isset($smarty.post.payment_module_name) && $module->name == $smarty.post.payment_module_name}selected="selected"{/if}>{$module->displayName|escape:'htmlall':'UTF-8'}</option>
    	{/foreach}
  	{else}
      	<option value="{if {l s='Back office order' mod='vrpayecommerce'} == "Back office order"}Back office order{else}{l s='Back office order' mod='vrpayecommerce'}{/if}">{if {l s='Back office order' mod='vrpayecommerce'} == "Back office order"}Back office order{else}{l s='Back office order' mod='vrpayecommerce'}{/if}</option>
  {/if}
</select>
<br/>
<div id='recurring' style="display:none">
	{foreach from=$payment_recurring item='recurring'}
		<div class="group-list">
			<div class="group-button" margin-left="35px">
				<input type="radio" name='reccuring_id' value="{$recurring.id|escape:'htmlall':'UTF-8'}">
			</div>
			{if $recurring.payment_group == 'CC'}
				{assign var='recurring_brand_lower' value=$recurring.brand|lower}
				{assign var='recurring_expiry_year' value=$recurring.expiry_year|substr:2}
				<div class="group-img">
				<img src="{$this_path|escape:'htmlall':'UTF-8'}views/img/{$recurring_brand_lower|escape:'htmlall':'UTF-8'}.png" class="brandImage" height="35" alt="{$recurring.brand|escape:'htmlall':'UTF-8'}">
				</div>
				<span class="group-info">{if {l s='FRONTEND_MC_ENDING' mod='vrpayecommerce'} == 'FRONTEND_MC_ENDING'}ending in:{else}{l s='FRONTEND_MC_ENDING' mod='vrpayecommerce'}{/if}{$recurring.last4digits|escape:'htmlall':'UTF-8'}; {if {l s='FRONTEND_MC_VALIDITY' mod='vrpayecommerce'} == 'FRONTEND_MC_VALIDITY'}expires on:{else}{l s='FRONTEND_MC_VALIDITY' mod='vrpayecommerce'}{/if} {$recurring.expiry_month|escape:'htmlall':'UTF-8'}/{$recurring_expiry_year|escape:'htmlall':'UTF-8'}</span>
			{elseif $recurring.payment_group == 'DD'}
				<div class="group-img">
				<img src="{$this_path|escape:'htmlall':'UTF-8'}views/img/sepa.png" class="brandImage" height="35" alt="sepa">
				</div>
				<span class="group-info">{if {l s='FRONTEND_MC_ACCOUNT' mod='vrpayecommerce'} == 'FRONTEND_MC_ACCOUNT'}Account: ****{else}{l s='FRONTEND_MC_ACCOUNT' mod='vrpayecommerce'}{/if}{$recurring.last4digits|escape:'htmlall':'UTF-8'}</span>
			{elseif $recurring.payment_group == 'PAYPAL'}
				<div class="group-img">
				<img src="{$this_path|escape:'htmlall':'UTF-8'}views/img/paypal.png" class="brandImage" height="35" alt="{$recurring.brand|escape:'htmlall':'UTF-8'}">
				</div>
				<span class="group-info">{if {l s='FRONTEND_MC_EMAIL' mod='vrpayecommerce'} == 'FRONTEND_MC_EMAIL'}Email:{else}{l s='FRONTEND_MC_EMAIL' mod='vrpayecommerce'}{/if}{#FRONTEND_MC_EMAIL#|escape:'htmlall':'UTF-8'}{$recurring.email|escape:'htmlall':'UTF-8'}</span>
			{/if}
		</div>

	{/foreach}
</div>
