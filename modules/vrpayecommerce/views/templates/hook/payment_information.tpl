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

<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="payment-info-link" href="{$paymentInformationUrl|escape:'html':'UTF-8'}">
	<span class="link-item">
		<i class="material-icons">person_pin</i>
	    {if {l s='FRONTEND_MC_INFO' mod='vrpayecommerce'} == "FRONTEND_MC_INFO"}My Payment Information{else}{l s='FRONTEND_MC_INFO' mod='vrpayecommerce'}{/if}
	</span>
</a>