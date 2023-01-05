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
<style>
	body {
		display:none;
		background: white;
	}
</style>

<script type="text/javascript">
	var wpwlOptions = {
		onReady: function(){
			jQuery(".wpwl-form").submit();
		}
	}
</script>

<input type="submit" value="Submit" style="display:none" />
<script src="{$paymentWidgetUrl|escape:'html':'UTF-8'}" type="text/javascript"></script>
<form action="{$responseUrl|escape:'html':'UTF-8'}" class="paymentWidgets">{$paymentBrand|escape:'htmlall':'UTF-8'}</form>
{/block}