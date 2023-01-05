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
<section id="main">  
	<header class="page-header">
	    <h1>
			{if {l s='FRONTEND_MC_INFO' mod='vrpayecommerce'} == "FRONTEND_MC_INFO"}My Payment Information{else}{l s='FRONTEND_MC_INFO' mod='vrpayecommerce'}{/if}
		</h1>
	</header>

{if $isRecurringActive}

	<link href="{$this_path|escape:'html':'UTF-8'}views/css/account_payment_form.css" rel="stylesheet" type="text/css">

	<section id="content" class="page-content">
		{if $isCardsSavedActive}
			<div class="group"><h3>{if {l s='FRONTEND_MC_CC' mod='vrpayecommerce'} == 'FRONTEND_MC_CC'}Credit Card{else}{l s='FRONTEND_MC_CC' mod='vrpayecommerce'}{/if}</h3></div>
			{foreach from=$customerDataCC item=list}
				{assign var='list_brand_lower' value=$list.brand|lower}
				{assign var='list_expiry_year' value=$list.expiry_year|substr:2}
				<div class="group-list">
					<div class="group-img">
						<img src="{$this_path|escape:'html':'UTF-8'}views/img/{$list_brand_lower|escape:'htmlall':'UTF-8'}.png" class="brandImage" alt="{$list.brand|escape:'htmlall':'UTF-8'}">
					</div>
					<div class="group-info">{if {l s='FRONTEND_MC_ENDING' mod='vrpayecommerce'} == 'FRONTEND_MC_ENDING'}ending in:{else}{l s='FRONTEND_MC_ENDING' mod='vrpayecommerce'}{/if}{$list.last4digits|escape:'htmlall':'UTF-8'}; {if {l s='FRONTEND_MC_VALIDITY' mod='vrpayecommerce'} == 'FRONTEND_MC_VALIDITY'}expires on:{else}{l s='FRONTEND_MC_VALIDITY' mod='vrpayecommerce'}{/if} {$list.expiry_month|escape:'htmlall':'UTF-8'}/{$list_expiry_year|escape:'htmlall':'UTF-8'}</div>
					<div class="group-button">
						{if $list.payment_default}
							<button class="btn btn-custom btnDefault">{if {l s='FRONTEND_MC_BT_DEFAULT' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_DEFAULT'}Default{else}{l s='FRONTEND_MC_BT_DEFAULT' mod='vrpayecommerce'}{/if}</button>
						{else}
							<form class="btnDefault" action="{$paymentInformationUrl|escape:'html':'UTF-8'}" method="post">
								<input type="hidden" name="id" value="{$list.id|escape:'htmlall':'UTF-8'}"/>
								<input type="hidden" name="payment_group" value="CC"/>
								<input type="hidden" name="set_default" value="1"/>
								<button style = "width:100%;" class="btn btn-custom btnDefault" type="submit" value="submit">{if {l s='FRONTEND_MC_BT_SETDEFAULT' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_SETDEFAULT'}Set as Default{else}{l s='FRONTEND_MC_BT_SETDEFAULT' mod='vrpayecommerce'}{/if}</button>
							</form>	
						{/if}
						<form class="btnChange" action="{$changePaymentUrl|escape:'html':'UTF-8'}" method="post">
							<input type="hidden" name="id" value="{$list.id|escape:'htmlall':'UTF-8'}"/>
							<input type="hidden" name="selected_payment" value="CCSAVED"/>
							<button style = "width:100%;" class="btn btn-custom" type="submit" value="submit">{if {l s='FRONTEND_MC_BT_CHANGE' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_CHANGE'}Change{else}{l s='FRONTEND_MC_BT_CHANGE' mod='vrpayecommerce'}{/if}</button>
						</form>
						<form class="btnDelete" action="{$deletePaymentUrl|escape:'html':'UTF-8'}" method="post">
							<input type="hidden" name="id" value="{$list.id|escape:'htmlall':'UTF-8'}"/>
							<input type="hidden" name="selected_payment" value="CCSAVED"/>
							<button style = "width:100%;" class="btn btn-custom" type="submit" value="submit">{if {l s='FRONTEND_MC_BT_DELETE' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_DELETE'}Delete{else}{l s='FRONTEND_MC_BT_DELETE' mod='vrpayecommerce'}{/if}</button>
						</form>
					</div>
					<div style="clear:both"></div>					
				</div>
			{/foreach}
			<div class="group-add">
				<form class = "btnAdd" action="{$registerPaymentUrl|escape:'html':'UTF-8'}" method="post">
					<input type="hidden" name="selected_payment" value="CCSAVED"/>
					<button style = "width:100%;" class="btn btn-custom" type="submit" value="submit">{if {l s='FRONTEND_MC_BT_ADD' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_ADD'}Add{else}{l s='FRONTEND_MC_BT_ADD' mod='vrpayecommerce'}{/if}</button>
				</form>
			</div>
			<div class="group-separator"></div>
		{/if}

		{if $isDDSavedActive}
			<div class="group"><h3>{if {l s='FRONTEND_MC_DD' mod='vrpayecommerce'} == 'FRONTEND_MC_DD'}Direct Debit{else}{l s='FRONTEND_MC_DD' mod='vrpayecommerce'}{/if}</h3></div>
			{foreach from=$customerDataDD item=list}
				<div class="group-list">
					<div class="group-img">
						<img src="{$this_path|escape:'htmlall':'UTF-8'}views/img/sepa.png" class="brandImage" alt="SEPA">			
					</div>
					<div class="group-info">{if {l s='FRONTEND_MC_ACCOUNT' mod='vrpayecommerce'} == 'FRONTEND_MC_ACCOUNT'}Account: ****{else}{l s='FRONTEND_MC_ACCOUNT' mod='vrpayecommerce'}{/if}{$list.last4digits|escape:'htmlall':'UTF-8'}</div>
					<div class="group-button">
						{if $list.payment_default}
							<button class="btn btn-custom btnDefault">{if {l s='FRONTEND_MC_BT_DEFAULT' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_DEFAULT'}Default{else}{l s='FRONTEND_MC_BT_DEFAULT' mod='vrpayecommerce'}{/if}</button>
						{else}
							<form class="btnDefault" action="{$paymentInformationUrl|escape:'html':'UTF-8'}" method="post">
								<input type="hidden" name="id" value="{$list.id|escape:'htmlall':'UTF-8'}"/>
								<input type="hidden" name="payment_group" value="DD"/>
								<input type="hidden" name="set_default" value="1"/>
								<button style = "width:100%;" class="btn btn-custom btnDefault" type="submit" value="submit">{if {l s='FRONTEND_MC_BT_SETDEFAULT' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_SETDEFAULT'}Set as Default{else}{l s='FRONTEND_MC_BT_SETDEFAULT' mod='vrpayecommerce'}{/if}</button>
							</form>	
						{/if}
						<form class="btnChange" action="{$changePaymentUrl|escape:'html':'UTF-8'}" method="post">
							<input type="hidden" name="id" value="{$list.id|escape:'htmlall':'UTF-8'}"/>
							<input type="hidden" name="selected_payment" value="DDSAVED"/>
							<button style = "width:100%;" class="btn btn-custom" type="submit" value="submit">{if {l s='FRONTEND_MC_BT_CHANGE' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_CHANGE'}Change{else}{l s='FRONTEND_MC_BT_CHANGE' mod='vrpayecommerce'}{/if}</button>
						</form>
						<form class="btnDelete" action="{$deletePaymentUrl|escape:'html':'UTF-8'}" method="post">
							<input type="hidden" name="id" value="{$list.id|escape:'htmlall':'UTF-8'}"/>
							<input type="hidden" name="selected_payment" value="DDSAVED"/>
							<button style = "width:100%;" class="btn btn-custom" type="submit" value="submit">{if {l s='FRONTEND_MC_BT_DELETE' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_DELETE'}Delete{else}{l s='FRONTEND_MC_BT_DELETE' mod='vrpayecommerce'}{/if}</button>
						</form>
					</div>
					<div style="clear:both"></div>					
				</div>
			{/foreach}
			<div class="group-add">
				<form class = "btnAdd" action="{$registerPaymentUrl|escape:'html':'UTF-8'}" method="post">
					<input type="hidden" name="selected_payment" value="DDSAVED"/>
					<button style = "width:100%;" class="btn btn-custom" type="submit" value="submit">{if {l s='FRONTEND_MC_BT_ADD' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_ADD'}Add{else}{l s='FRONTEND_MC_BT_ADD' mod='vrpayecommerce'}{/if}</button>
				</form>
			</div>
			<div class="group-separator"></div>
		{/if}

		{if $isPayPalSavedActive}
			<div class="group"><h3>{if {l s='FRONTEND_MC_PAYPAL' mod='vrpayecommerce'} == 'FRONTEND_MC_PAYPAL'}Paypal{else}{l s='FRONTEND_MC_PAYPAL' mod='vrpayecommerce'}{/if}{#FRONTEND_MC_PAYPAL#|escape:'htmlall':'UTF-8'}</h3></div>
			{foreach from=$customerDataPAYPAL item=list}
				<div class="group-list">
					<div class="group-img">
						<img src="{$this_path|escape:'htmlall':'UTF-8'}views/img/paypal.png" class="brandImage" alt="PayPal">
					</div>
					<div class="group-info">{if {l s='FRONTEND_MC_EMAIL' mod='vrpayecommerce'} == 'FRONTEND_MC_EMAIL'}Email:{else}{l s='FRONTEND_MC_EMAIL' mod='vrpayecommerce'}{/if}{#FRONTEND_MC_EMAIL#|escape:'htmlall':'UTF-8'} {$list.email|escape:'htmlall':'UTF-8'}</div>
					<div class="group-button">
						{if $list.payment_default}
							<button class="btn btn-custom btnDefault">{if {l s='FRONTEND_MC_BT_DEFAULT' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_DEFAULT'}Default{else}{l s='FRONTEND_MC_BT_DEFAULT' mod='vrpayecommerce'}{/if}</button>
						{else}
							<form class="btnDefault" action="{$paymentInformationUrl|escape:'html':'UTF-8'}" method="post">
								<input type="hidden" name="id" value="{$list.id|escape:'htmlall':'UTF-8'}"/>
								<input type="hidden" name="payment_group" value="PAYPAL"/>
								<input type="hidden" name="set_default" value="1"/>
								<button style = "width:100%;" class="btn btn-custom" type="submit" value="submit">{if {l s='FRONTEND_MC_BT_SETDEFAULT' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_SETDEFAULT'}Set as Default{else}{l s='FRONTEND_MC_BT_SETDEFAULT' mod='vrpayecommerce'}{/if}</button>
							</form>	
						{/if}
						<form class="btnChange" action="{$changePaymentUrl|escape:'html':'UTF-8'}" method="post">
							<input type="hidden" name="id" value="{$list.id|escape:'htmlall':'UTF-8'}"/>
							<input type="hidden" name="selected_payment" value="PAYPALSAVED"/>
							<button style = "width:100%;" class="btn btn-custom" type="submit" value="submit">{if {l s='FRONTEND_MC_BT_CHANGE' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_CHANGE'}Change{else}{l s='FRONTEND_MC_BT_CHANGE' mod='vrpayecommerce'}{/if}</button>
						</form>
						<form class="btnDelete" action="{$deletePaymentUrl|escape:'html':'UTF-8'}" method="post">
							<input type="hidden" name="id" value="{$list.id|escape:'htmlall':'UTF-8'}"/>
							<input type="hidden" name="selected_payment" value="PAYPALSAVED"/>			
							<button style = "width:100%;" class="btn btn-custom" type="submit" value="submit">{if {l s='FRONTEND_MC_BT_DELETE' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_DELETE'}Delete{else}{l s='FRONTEND_MC_BT_DELETE' mod='vrpayecommerce'}{/if}</button>
						</form>
					</div>
					<div style="clear:both"></div>					
				</div>
			{/foreach}
			<div class="group-add">
				<form class = "btnAdd" action="{$registerPaymentUrl|escape:'html':'UTF-8'}" method="post">
					<input type="hidden" name="selected_payment" value="PAYPALSAVED"/>
					<button style = "width:100%;" class="btn btn-custom" type="submit" value="submit">{if {l s='FRONTEND_MC_BT_ADD' mod='vrpayecommerce'} == 'FRONTEND_MC_BT_ADD'}Add{else}{l s='FRONTEND_MC_BT_ADD' mod='vrpayecommerce'}{/if}</button>
				</form>
			</div>
			<div style="clear:both"></div>
			<div class="group-separator"></div>
		{/if}

	</section>

{/if}

	<footer class="page-footer">
		{block name='my_account_links'}
	    	{include file='customer/_partials/my-account-links.tpl'}
	  	{/block}
	</footer>

</section>
{/block}