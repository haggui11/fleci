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
{if !empty($klarna_notifys)}
<div id="klarnapaylater_notify">
	<ul style="list-style: disc; color: #878787; font-size: 13px;">
		{foreach $klarna_notifys as $notify}
		<li>{$notify|escape:'html':'UTF-8'}</li>
		{/foreach}
	</ul>
</div>
<script type="text/javascript">
(function() {
	var elm = document.getElementById("klarnapaylater_notify").parentElement;
	elm.style.display = "block"; 
	elm.classList.remove('js-additional-information');
	var elm_id = elm.id;
	var payment_id = elm_id.replace('-additional-information', '');
	document.getElementById(payment_id).disabled = true ;
	document.getElementById(payment_id).style.cursor = "not-allowed";
})();
</script>
<style type="text/css">
	.additional-information{
		display: block;
	}
</style>
{/if}