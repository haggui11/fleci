{**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2021 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{extends file="helpers/form/form.tpl"}
{block name="field"}
	{if $input.type == 'select_link'}
		<select class="form-control fixed-width-xxl ps_link" name="ps_link" id="ps_link">
			{$all_options|escape:'quotes':'UTF-8'}
		</select>
		<script type="text/javascript">
			var type_link = {$type_link|intval};
			{if $type_link == 1}
			$(document).ready(function() {
				$("#ps_link").val('{if isset($ps_link_value) &&  $ps_link_value != ''}{$ps_link_value|escape:"html":"UTF-8"}{/if}');
			});
			{else}
				$('.ps_link').parent('.form-group').css('display','none');
			{/if}
		</script>
	{/if}
	{if $input.name == 'title'}
		<div class="tt-menu-title">{$smarty.block.parent}</div>
		<script type="text/javascript">
		var type_link = {$type_link|intval};
		if (type_link == 1 || type_link == 4)
			$('.tt-menu-title').parent('.form-group').css('display','none');
		</script>
	{elseif $input.name == 'link'}
		<div class="tt-menu-link">{$smarty.block.parent}</div>
		<script type="text/javascript">
		var type_link = {$type_link|intval};
		if (type_link == 1 || type_link == 4)
			$('.tt-menu-link').parent('.form-group').css('display','none');
		</script>
	{elseif $input.name == 'text'}
		<div class="tt-menu-text">{$smarty.block.parent}</div>
		<script type="text/javascript">
		var type_link = {$type_link|intval};
		if (type_link == 1 || type_link == 2 || type_link == 4)
			$('.tt-menu-text').parent('.form-group').css('display','none');
		</script>
	{elseif $input.name == 'id_product'}
		<div class="tt-menu-product">{$smarty.block.parent}</div>
		<script type="text/javascript">
		var type_link = {$type_link|intval};
		if (type_link == 1 || type_link == 2 || type_link == 3)
			$('.tt-menu-product').parent('.form-group').css('display','none');
		</script>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}