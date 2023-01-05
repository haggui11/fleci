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
<link href="{$this_path|escape:'html':'UTF-8'}views/css/account_payment_form.css" rel="stylesheet" type="text/css">

<section id="main">  
    <header class="page-header">
        <h1>
            {if {l s='FRONTEND_MC_DELETE' mod='vrpayecommerce'} == "FRONTEND_MC_DELETE"}Delete Payment Information{else}{l s='FRONTEND_MC_DELETE' mod='vrpayecommerce'}{/if}
        </h1>
    </header>

    <section id="content" class="page-content">
        <div class="box-unreg">
            <p class="text-unreg">{if {l s='FRONTEND_MC_DELETESURE' mod='vrpayecommerce'} == "FRONTEND_MC_DELETESURE"}Are you sure to delete this payment information?{else}{l s='FRONTEND_MC_DELETESURE' mod='vrpayecommerce'}{/if}</p>
            <form class="cancel_form" action="{$cancelUrl|escape:'html':'UTF-8'}" method="post">
                <input type="submit" value="{if {l s='FRONTEND_BT_CANCEL' mod='vrpayecommerce'} == "FRONTEND_BT_CANCEL"}Cancel{else}{l s='FRONTEND_BT_CANCEL' mod='vrpayecommerce'}{/if}" class="btn btn-custom">
            </form>
            <form action="{$deleteResponseUrl|escape:'html':'UTF-8'}" method="post">
                <input type="hidden" name="id" value="{$id|escape:'htmlall':'UTF-8'}"/>
                <input type="hidden" name="selected_payment" value="{$selected_payment|escape:'htmlall':'UTF-8'}"/>  
                <input type="submit" name="action" value="{if {l s='FRONTEND_BT_CONFIRM' mod='vrpayecommerce'} == "FRONTEND_BT_CONFIRM"}Confirm{else}{l s='FRONTEND_BT_CONFIRM' mod='vrpayecommerce'}{/if}" class="btn btn-custom">
            </form>
        </div>
    </section>

    <footer class="page-footer">
        {block name='my_account_links'}
            {include file='customer/_partials/my-account-links.tpl'}
        {/block}
    </footer>
</section>
{/block}