{**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2021 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{assign var="tt_cnt" value="1"}
{assign var="tt_total" value="0"}
{foreach from=$brands item="product"}
	{$tt_total = $tt_total+1}
{/foreach}

 <section class="brands container">
	<h1 class="h1 products-section-title text-uppercase tt-title">
		{if $display_link_brand}<a href="{$page_link}" title="{l s='Brands' mod='ttbrandlogo'}">{/if}
		{l s='Brands' mod='ttbrandlogo'}
		{if $display_link_brand}</a>{/if}
	</h1>
	  	 {if $tt_total > 5}
	<div class="customNavigation">
		<div class="btn-prev">
		<a class="btn prev ttbrandlogo_prev">{l s='Prev' mod='ttbrandlogo'}</a>
		</div>
	    <div class="btn-next">
		<a class="btn next ttbrandlogo_next">{l s='Next' mod='ttbrandlogo'}</a>
		</div>
	</div>
	{/if}  
	<div class="products">
			{if $brands}
	 <ul id="ttbrandlogo-carousel" class="product_list">
		{foreach from=$brands item=brand name=brand_list}
	<li>
	<div class="brand-image">
		<a href="{$link->getmanufacturerLink($brand['id_manufacturer'], $brand['link_rewrite'])}" title="{$brand.name}">
			<img src="{$link->getManufacturerImageLink($brand['id_manufacturer'])}" alt="{$brand.name}" loading="lazy"/>
		</a>
	</div>
	{if $brandname}
		<h1 class="h3 product-title">
			<a class="product-name"  href="{$link->getmanufacturerLink($brand['id_manufacturer'], $brand['link_rewrite'])}" title="{$brand.name}">{$brand.name}</a>
		</h1>
	{/if}
	</li>
	{/foreach}
	</ul>
	{else}
	<p>{l s='No brand' mod='ttbrandlogo'}</p>
	{/if}
	</div>
</section>