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

{if $module == "vrpayecommerce"}
	{literal}
		<script>
			$( document ).ready(function() {
	    		$('#desc-order-standard_refund').css("display","none");
	    		$('#desc-order-partial_refund').css("display","none");
			});
		</script>
	{/literal}

	{if !empty($successMessage)}
		<div class="alert alert-success">
			<button type="button" class="close" data-dismiss="alert">×</button>
		    {if $successMessage == "capture"}
			    {if {l s='SUCCESS_GENERAL_CAPTURE_PAYMENT' mod='vrpayecommerce'} == "SUCCESS_GENERAL_CAPTURE_PAYMENT"}Your attempt to capture the payment success.{else}{l s='SUCCESS_GENERAL_CAPTURE_PAYMENT' mod='vrpayecommerce'}{/if}
			{/if}
		    {if $successMessage == "update_order"}
			    {if {l s='SUCCESS_GENERAL_UPDATE_PAYMENT' mod='vrpayecommerce'} == "SUCCESS_GENERAL_UPDATE_PAYMENT"}The payment status has been successfully updated.{else}{l s='SUCCESS_GENERAL_UPDATE_PAYMENT' mod='vrpayecommerce'}{/if}
			{/if}
		    {if $successMessage == "refund"}
			    {if {l s='SUCCESS_GENERAL_REFUND_PAYMENT' mod='vrpayecommerce'} == "SUCCESS_GENERAL_REFUND_PAYMENT"}Your attempt to refund the payment success.{else}{l s='SUCCESS_GENERAL_REFUND_PAYMENT' mod='vrpayecommerce'}{/if}
			{/if}
			{if $successMessage == "new_order"}
			    {if {l s='SUCCESS_BACKEND_ORDER' mod='vrpayecommerce'} == "SUCCESS_BACKEND_ORDER"}Your order has been successfully created.{else}{l s='SUCCESS_BACKEND_ORDER' mod='vrpayecommerce'}{/if}
			{/if}
		</div>
	{/if}
	{if !empty($errorMessage)}
		<div class="alert alert-danger">
			<button type="button" class="close" data-dismiss="alert">×</button>
		    {if $errorMessage == "capture"}
			    {if {l s='ERROR_GENERAL_CAPTURE_PAYMENT' mod='vrpayecommerce'} == "ERROR_GENERAL_CAPTURE_PAYMENT"}Unfortunately, your attempt to capture the payment failed.{else}{l s='ERROR_GENERAL_CAPTURE_PAYMENT' mod='vrpayecommerce'}{/if}
			{/if}
		    {if $errorMessage == "update_order"}
			    {if {l s='ERROR_UPDATE_BACKEND' mod='vrpayecommerce'} == "ERROR_UPDATE_BACKEND"}Order status can not be updated.{else}{l s='ERROR_UPDATE_BACKEND' mod='vrpayecommerce'}{/if}
			{/if}
		    {if $errorMessage == "refund"}
			    {if {l s='ERROR_GENERAL_REFUND_PAYMENT' mod='vrpayecommerce'} == "ERROR_GENERAL_REFUND_PAYMENT"}Unfortunately, your attempt to refund the payment failed.{else}{l s='ERROR_GENERAL_REFUND_PAYMENT' mod='vrpayecommerce'}{/if}
			{/if}
			{if $errorMessage == "ssl"}
			    {if {l s='ERROR_MERCHANT_SSL_CERTIFICATE' mod='vrpayecommerce'} == "ERROR_MERCHANT_SSL_CERTIFICATE"}SSL certificate problem, please contact the merchant.{else}{l s='ERROR_MERCHANT_SSL_CERTIFICATE' mod='vrpayecommerce'}{/if}
			{/if}
		</div>
	{/if}
{/if}
