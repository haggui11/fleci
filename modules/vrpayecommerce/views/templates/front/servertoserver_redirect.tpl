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

<style>
	body {
		background: white;
		display:none;
	}
</style>

<form action="{$redirectUrl|escape:'html':'UTF-8'}" class="paymentWidgets" id="servertoserver">
	{foreach from=$redirectParameters key=k item=v}
	    <input type="hidden" name="{$v.name|escape:'html':'UTF-8'}" value="{$v.value|escape:'html':'UTF-8'}">
	{/foreach}
</form>

<script language="javascript">
	document.getElementById("servertoserver").submit();
</script>