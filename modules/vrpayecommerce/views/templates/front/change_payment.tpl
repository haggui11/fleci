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
{block name='content'}
{if $redirect}  
    <style>
        body {
            display: none;
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
    <form action="{$paymentResponseUrl|escape:'html':'UTF-8'}" class="paymentWidgets">{$paymentBrand|escape:'htmlall':'UTF-8'}</form>      
{else}
    {if $frameTestMode }
        <!--[if lte IE 9]><link rel="stylesheet" href="https://test.oppwa.com/v1/paymentWidgets/css/card.min.css" type="text/css" /><![endif]-->
    {else}
        <!--[if lte IE 9]><link rel="stylesheet" href="https://oppwa.com/v1/paymentWidgets/css/card.min.css" type="text/css" /><![endif]-->
    {/if}

    <link href="{$this_path|escape:'html':'UTF-8'}views/css/account_payment_form.css" rel="stylesheet" type="text/css">

    <section id="main">  
        <header class="page-header">
            <h1>
                {if {l s='FRONTEND_MC_CHANGE' mod='vrpayecommerce'} == "FRONTEND_MC_CHANGE"}Change Payment Information{else}{l s='FRONTEND_MC_CHANGE' mod='vrpayecommerce'}{/if}
            </h1>
        </header>

        <section id="content" class="page-content">
            <script type="text/javascript"> 
                var wpwlOptions = {
                	locale: "{$lang|escape:'htmlall':'UTF-8'}",
            		style: "card",
                    onReady: function(){
            			var buttonCancel = "<a href='{$cancelUrl|escape:'html':'UTF-8'}' class='wpwl-button btn_cancel'>{if {l s='FRONTEND_BT_CANCEL' mod='vrpayecommerce'} == 'FRONTEND_BT_CANCEL'}Cancel{else}{l s='FRONTEND_BT_CANCEL' mod='vrpayecommerce'}{/if}</a>";
            			var buttonConfirm = "{if {l s='FRONTEND_MC_BT_CHANGE' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_CHANGE'}Change{else}{l s='FRONTEND_MC_BT_CHANGE' mod='vrpayecommerce'}{/if}";
            			var ttRegistration = "<div class='register-tooltip'>{if {l s='FRONTEND_TT_REGISTRATION' mod='vrpayecommerce'} == 'FRONTEND_TT_REGISTRATION'}A small amount (<1 €) will be charged and instantly refunded to verify your account/card details.{else}{l s='FRONTEND_TT_REGISTRATION' mod='vrpayecommerce'}{/if}</div>";
            			var ttTestMode = "<div class='testmode'>{if {l s='FRONTEND_TT_TESTMODE' mod='vrpayecommerce'} == 'FRONTEND_TT_TESTMODE'}THIS IS A TEST. NO REAL MONEY WILL BE TRANSFERED{else}{l s='FRONTEND_TT_TESTMODE' mod='vrpayecommerce'}{/if}</div>";
                        jQuery('form.wpwl-form').find('.wpwl-button').before(buttonCancel);
                        jQuery('.wpwl-button-pay').html(buttonConfirm);
                        jQuery('.wpwl-container').after(ttRegistration);
                        {if !empty($frameTestMode) }
            	            jQuery(".wpwl-container").wrap( "<div class='frametest'></div>").before(ttTestMode);   
                        {/if}
            		},
            		registrations: {
                    	hideInitialPaymentForms: false,
                    	requireCvv: false
                	}
                }
            </script>
            <script src="{$paymentWidgetUrl|escape:'html':'UTF-8'}" type="text/javascript"></script>
            <form action="{$paymentResponseUrl|escape:'html':'UTF-8'}" class="paymentWidgets">{$paymentBrand|escape:'htmlall':'UTF-8'}</form>
        </section>

        <footer class="page-footer">
            {block name='my_account_links'}
                {include file='customer/_partials/my-account-links.tpl'}
            {/block}
        </footer>

    </section>

{/if}
{/block}