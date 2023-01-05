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
    <section id="content-hook_order_confirmation" class="card">
      <div class="card-block">
        <div class="row">
          <div class="col-md-12">
            <h3 class="h1 card-title">
              {l s='Order Review' d='vrpayecommerce' mod='vrpayecommerce'}
            </h3>
          </div>
        </div>
      </div>
    </section>

    <section id="content" class="page-content page-order-confirmation card">
	    <div class="card-block">
	      <div class="row">

	        {block name='order_confirmation_table'}

	          {assign var=products value=$order.products scope="global"}
	          {assign var=subtotals value=$order.subtotals scope="global"}
	          {assign var=totals value=$order.totals scope="global"}
	          {assign var=labels value=$order.labels scope="global"}
	          {assign var=add_product_link value=false scope="global"}

	          {block name='order-items-table-head'}
				<div id="order-items" class="col-md-8">
				  <h3 class="card-title h3">{l s='Order items' d='vrpayecommerce' mod='vrpayecommerce'}</h3>
				{/block}
				  <div class="order-confirmation-table">
				    <table class="table">
				      {foreach from=$products item=product}
				        <div class="order-line row">
				          <div class="col-sm-2 col-xs-3">
				            <span class="image">
				              <img src="{$product.cover.medium.url|escape:'html':'UTF-8'}" />
				            </span>
				          </div>
				          <div class="col-sm-4 col-xs-9 details">
				            {if $add_product_link}<a href="{$product.url|escape:'html':'UTF-8'}" target="_blank">{/if}
				              <span>{$product.name|escape:'html':'UTF-8'}</span>
				            {if $add_product_link}</a>{/if}
				            {if $product.customizations|count}
				              {foreach from=$product.customizations item="customization"}
				                <div class="customizations">
				                  <a href="#" data-toggle="modal" data-target="#product-customizations-modal-{$customization.id_customization|escape:'html':'UTF-8'}">{l s='Product customization'  d='vrpayecommerce' mod='vrpayecommerce'}</a>
				                </div>
				                <div class="modal fade customization-modal" id="product-customizations-modal-{$customization.id_customization|escape:'html':'UTF-8'}" tabindex="-1" role="dialog" aria-hidden="true">
				                  <div class="modal-dialog" role="document">
				                    <div class="modal-content">
				                      <div class="modal-header">
				                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				                          <span aria-hidden="true">&times;</span>
				                        </button>
				                        <h4 class="modal-title">{l s='Product customization' d='vrpayecommerce' mod='vrpayecommerce'}</h4>
				                      </div>
				                      <div class="modal-body">
				                        {foreach from=$customization.fields item="field"}
				                          <div class="product-customization-line row">
				                            <div class="col-sm-3 col-xs-4 label">
				                              {$field.label|escape:'html':'UTF-8'}
				                            </div>
				                            <div class="col-sm-9 col-xs-8 value">
				                              {if $field.type == 'text'}
				                                {if (int)$field.id_module}
				                                  {$field.text|escape:'html':'UTF-8'}
				                                {else}
				                                  {$field.text|escape:'html':'UTF-8'}
				                                {/if}
				                              {elseif $field.type == 'image'}
				                                <img src="{$field.image.small.url|escape:'html':'UTF-8'}">
				                              {/if}
				                            </div>
				                          </div>
				                        {/foreach}
				                      </div>
				                    </div>
				                  </div>
				                </div>
				              {/foreach}
				            {/if}
				            {hook h='displayProductPriceBlock' product=$product type="unit_price"}
				          </div>
				          <div class="col-sm-6 col-xs-12 qty">
				            <div class="row">
				              <div class="col-xs-5 text-sm-right text-xs-left">{$product.price|escape:'html':'UTF-8'}</div>
				              <div class="col-xs-2">{$product.quantity|escape:'html':'UTF-8'}</div>
				              <div class="col-xs-5 text-xs-right bold">{$product.total|escape:'html':'UTF-8'}</div>
				            </div>
				          </div>
				        </div>
				      {/foreach}
				    <hr />
				    <table>
				      {foreach $subtotals as $subtotal}
				        {if $subtotal.type !== 'tax'}
				          <tr>
				            <td>{$subtotal.label|escape:'html':'UTF-8'}</td>
				            <td>{$subtotal.value|escape:'html':'UTF-8'}</td>
				          </tr>
				        {/if}
				      {/foreach}
				      {if $subtotals.tax.label !== null}
				        <tr class="sub">
				          <td>{$subtotals.tax.label|escape:'html':'UTF-8'}</td>
				          <td>{$subtotals.tax.value|escape:'html':'UTF-8'}</td>
				        </tr>
				      {/if}
				      <tr class="font-weight-bold">
				        <td><span class="text-uppercase">{$totals.total.label|escape:'html':'UTF-8'}</span> {$labels.tax_short|escape:'html':'UTF-8'}</td>
				        <td>{$totals.total.value|escape:'html':'UTF-8'}</td>
				      </tr>
				      <tr>
				        <td>{l s='Sum of Interest' d='vrpayecommerce' mod='vrpayecommerce'}</td>
				        <td>{$curency|escape:'html':'UTF-8'}{$sumOfInterest|escape:'html':'UTF-8'}</td>
				      </tr>
				      <tr class="font-weight-bold">
				        <td><span class="text-uppercase"><strong>{if {l s='FRONTEND_EASYCREDIT_ORDER_TOTAL' mod='vrpayecommerce'} == "FRONTEND_EASYCREDIT_ORDER_TOTAL"}Order Total{else}{l s='FRONTEND_EASYCREDIT_ORDER_TOTAL' mod='vrpayecommerce'}{/if}</strong></span></td>
				        <td><strong>{$curency|escape:'html':'UTF-8'}{$total|escape:'html':'UTF-8'}</strong></td>
				      </tr>
				    </table>
				  </div>
				</div>
	        {/block}

	        <div id="order-details" class="col-md-4">
	          <h3 class="h3 card-title">{l s='Order details' d='vrpayecommerce' mod='vrpayecommerce'}:</h3>
	          <ul>
	            <li>{l s='Payment method: %method%' d='vrpayecommerce' mod='vrpayecommerce' sprintf=['%method%' => $paymentMethod]}</li>
	            {if !$order.is_virtual}
	              <li>
	                {l s='Shipping method: %method%' d='vrpayecommerce' mod='vrpayecommerce' sprintf=['%method%' => $order.subtotals.shipping.value]}
	              </li>
	            {/if}
	            <li>{$tilgungsplanText|escape:'html':'UTF-8'}</li>
	            <li><a target="_blank" href="{$linkInfo|escape:'html':'UTF-8'}">{if {l s='FRONTEND_EASYCREDIT_LINK' mod='vrpayecommerce'} == "FRONTEND_EASYCREDIT_LINK"}Vorvertragliche Informationen zum Ratenkauf hier abrufen{else}{l s='FRONTEND_EASYCREDIT_LINK' mod='vrpayecommerce'}{/if}</a></li>
	          </ul>
	        </div>

	      </div>
	    </div>
	</section>
	<form method="post" action="{$formUrl|escape:'html':'UTF-8'}">
	    <section class="card">
		    <div class="card-block">
		      <div class="row">
		        <div class="col-md-6">
		        <input type="hidden" name="vrpay_id" value="{$smarty.get.id|escape:'html':'UTF-8'}" />
				<input type="hidden" name="amount" value="{$amount|escape:'html':'UTF-8'}" />
				<input type="hidden" name="currency" value="{$curency_iso|escape:'html':'UTF-8'}" />
		          <input type="submit" class="btn btn-primary pull-xs-right hidden-xs-down" name="submitform" value="{l s='Confirmation'  mod='vrpayecommerce'}">
		        </div>
		      </div>
		    </div>
		</section>
	</form>
{/block}