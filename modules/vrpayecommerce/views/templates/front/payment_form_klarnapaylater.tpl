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

<script src="https://cdn.klarna.com/public/kitt/core/v1.0/js/klarna.min.js"></script>
<script src="https://cdn.klarna.com/public/kitt/toc/v1.1/js/klarna.terms.min.js"></script>
<link href="{$this_path|escape:'htmlall':'UTF-8'}views/css/formklarna.css" rel="stylesheet" type="text/css">

{capture name=path}{if {l s='FRONTEND_PAYMENT' mod='vrpayecommerce'} == "FRONTEND_PAYMENT"}Payment{else}{l s='FRONTEND_PAYMENT' mod='vrpayecommerce'}{/if}{/capture}

<h1 class="page-heading">{if {l s='FRONTEND_ORDER_SUMMATION' mod='vrpayecommerce'} == "FRONTEND_ORDER_SUMMATION"}Order summation{else}{l s='FRONTEND_ORDER_SUMMATION' mod='vrpayecommerce'}{/if}</h1>

<div class="box">
    {assign var='current_step' value='payment'}
    <form name="klarnaForm" action="{$link->getModuleLink('vrpayecommerce', 'servertoserverklarna', ['payment_method' => $paymentMethod ], true)|escape:'htmlall':'UTF-8'}" method="post">
    	<p>
    		{if $locale == 'de_de'}
                <img src="{$this_path|escape:'htmlall':'UTF-8'}views/img/klarnapaylater_de.png" alt="" height="49"/>
            {else}
                <img src="{$this_path|escape:'htmlall':'UTF-8'}views/img/klarnapaylater_en.png" alt="" height="49"/>
            {/if}
    		<span id="klarna_terms_invoice"></span>
    	</p>
    	<p class="klarna_group_desc">
            <span>{$subTitle|escape:'htmlall':'UTF-8'}</span>
        </p>
        <p>
        <div class="checker">
            <input type="checkbox" id="klarna_terms" name="klarna_terms" value="1" autocomplete="off" class="checkbox">
        </div>
            <label for="klarna_terms" class="klarna_terms">
                <span>{$term1|escape:'htmlall':'UTF-8'}</span>
                <span id="klarna_terms_consent"></span>
                <span>{$term2|escape:'htmlall':'UTF-8'}</span>
            </label>
        </p>
    	<p class="cart_nav" id="cart_navigation">
    		<a class="klarna_button_back" href="{$link->getPageLink('order', true, NULL, 'step=3')|escape:'htmlall':'UTF-8'}">
    			{if {l s='GENERAL_TEXT_OTHER_PAYMENT' mod='vrpayecommerce'} == 'GENERAL_TEXT_OTHER_PAYMENT'}Other payment methods{else}{l s='GENERAL_TEXT_OTHER_PAYMENT' mod='vrpayecommerce'}{/if}
    		</a>
            <button class="klarna_button_submit" type="submit" onclick="
            {literal}
                if(!this.form.klarna_terms.checked){
                    alert('{/literal}{$errorRequired|escape:'javascript'}{literal}');
                    return false
                }
            {/literal}
            ">
                <span>{if {l s='GENERAL_TEXT_CONFIRM_ORDER' mod='vrpayecommerce'} == 'GENERAL_TEXT_CONFIRM_ORDER'}I confirm my order{else}{l s='GENERAL_TEXT_CONFIRM_ORDER' mod='vrpayecommerce'}{/if}</span>
            </button>
    	</p>
    </form>
</div>

{literal}
<script type="text/javascript">

    new Klarna.Terms.Invoice({
        el: "klarna_terms_invoice",
        eid: "{/literal}{$eid|escape:'htmlall':'UTF-8'}{literal}",
        locale: "{/literal}{$locale|escape:'htmlall':'UTF-8'}{literal}",
        charge: 0,
        type: 'desktop'
    });
    new Klarna.Terms.Consent({
        el: "klarna_terms_consent",
        eid: "{/literal}{$eid|escape:'htmlall':'UTF-8'}{literal}",
        locale: "{/literal}{$locale|escape:'htmlall':'UTF-8'}{literal}",
        type: 'desktop'
    });

    $(document).ready(function(){
        if (!!$.prototype.fancybox) {
            $("a.iframe").fancybox({
                'type': 'iframe',
                'width': 600,
                'height': 600
            });
        }

    });

</script>
{/literal}

{/block}
