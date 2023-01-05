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

{if $status == 'ok'}
	<p>
		{if {l s='FRONTEND_MESSAGE_YOUR_ORDER' mod='vrpayecommerce'} == "FRONTEND_MESSAGE_YOUR_ORDER"}Your order on{else}{l s='FRONTEND_MESSAGE_YOUR_ORDER' mod='vrpayecommerce'}{/if} {$shop_name|escape:'htmlall':'UTF-8'} {if {l s='FRONTEND_MESSAGE_COMPLETE' mod='vrpayecommerce'} == "FRONTEND_MESSAGE_COMPLETE"}is complete.{else}{l s='FRONTEND_MESSAGE_COMPLETE' mod='vrpayecommerce'}{/if}<br/>
		{if {l s='FRONTEND_MESSAGE_THANK_YOU' mod='vrpayecommerce'} == "FRONTEND_MESSAGE_THANK_YOU"}Thank you for your purchase!{else}{l s='FRONTEND_MESSAGE_THANK_YOU' mod='vrpayecommerce'}{/if}
	</p>
{/if}
