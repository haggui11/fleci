{*
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
{foreach from=$products item="product"}
	{$tt_total = $tt_total+1}
{/foreach}

<section class="ttspecial-products clearfix">
<div class="ttspecial-products-innner">
<div class="tt-titletab">
	  <h3 class="tt-title">{l s='Specials' mod='ttspecials'}</h3>
    <h3 class="ttdesc">{l s='Best Offered Products' mod='ttspecials'}</h3>
    {if $tt_total > 1}
	<div class="customNavigation">
		<div class="btn-prev">
		<a class="btn prev ttspecial_prev">{l s='Prev' mod='ttspecials'}</a>
		</div>
		<div class="btn-next">
		<a class="btn next ttspecial_next">{l s='Next' mod='ttspecials'}</a>
		</div>
	</div>
	{/if}
</div>
  <div class="ttspecial-list">
  <div class="row">
  <div class="products">
    {foreach from=$products item="product"}
      {include file="catalog/_partials/miniatures/ttproduct.tpl" product=$product}
    {/foreach}
  </div>
  </div>
  </div>
   <div class="allproduct"><a href="{$allSpecialProductsLink}">{l s='All sale products'  mod='ttspecials'}</a></div>
</div>
</section>