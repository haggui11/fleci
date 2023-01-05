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

<form action="{$redirect_url|escape:'html':'UTF-8'}" class="paymentWidgets" id="easycreditform">
{foreach from=$parameters key=k item=v}
    <input type="text" name="{$v.name|escape:'html':'UTF-8'}" value="{$v.value|escape:'html':'UTF-8'}">
{/foreach}
</form>
<script language="javascript">document.getElementById("easycreditform").submit();</script>

{/block}
