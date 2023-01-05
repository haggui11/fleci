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
*  International Registered Trademark & Property of PrestaShop SA*}

{extends file="helpers/form/form.tpl"}

{block name="label"}
	{if $psv == 1.5}
		<div class="form-group {if isset($input.form_group_class)} {$input.form_group_class|escape:'html':'UTF-8'}{/if}">
		{$smarty.block.parent}
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
{block name="field"}
	{if $psv == 1.5}
		{if $input.type == 'html'}
			<div class="html_content15">
				{if isset($input.html_content)}{$input.html_content nofilter}{/if}
			</div>
		{else}
            {$smarty.block.parent}
		{/if}
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{block name="field"}
	{if $input.type == 'theme'}
		<div class="col-lg-8{if !isset($input.label)} col-lg-offset-3{/if} ttpc-themes-wrp themes-wrp-{$psvd|escape:'html':'UTF-8'}">
			<div class="row">
                {foreach $input.values as $value}
                    {strip}
						<div class="col-lg-3 col-md-4 col-xs-6 theme-item {if isset($input.class)}{$input.class|escape:'html':'UTF-8'}{/if}">
							<label>
								<input type="radio"	name="{$input.name|escape:'html':'UTF-8'}" id="theme-{$value.label|escape:'html':'UTF-8'}" value="{$value.value|escape:'html':'UTF-8'}" data-theme="{rtrim($value.value, '.css')|escape:'quotes':'UTF-8'}" {if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
								<img class="theme-img" src="{$value.img|escape:'html':'UTF-8'}" alt="{$value.label|escape:'html':'UTF-8'}" loading="lazy" >
							</label>
						</div>
                    {/strip}
                    {if isset($value.p) && $value.p}<p class="help-block">{$value.p|escape:'html':'UTF-8'}</p>{/if}
                {/foreach}
			</div>
		</div>
    {elseif $input.type == 'product_sources'}
		<div class="ttpc-sources-wrp col-lg-9 ps{$psvd|intval}">
			<div class="checkbox">
				<label for="ttpc_source_all_{$ttpc_block->id|intval}">
					<input type="checkbox" id="ttpc_source_all_{$ttpc_block->id|intval}" name="sources[]" value="source_all" class="ttpc_source_all" {if $ttpc_block->source_all}checked{/if}>
					{l s='All with timers' mod='ttproductcountdown'}
				</label>
				<span class="btn btn-default ttpc-toggle-children-sources">{if $ttpc_block->source_all}+{else}-{/if}</span>

				<div class="ttpc-children-sources" {if $ttpc_block->source_all}style="display: none;" {/if}>
					<div>
						<label for="ttpc_source_sp_{$ttpc_block->id|intval}">
							<input type="checkbox" id="ttpc_source_sp_{$ttpc_block->id|intval}" name="sources[]"
								   value="source_specific_prices" class="ttpc_source_sp ttpc_source_checkbox" {if $ttpc_block->source_specific_prices || $ttpc_block->source_all}checked{/if}>
							{l s='Specific prices' mod='ttproductcountdown'}
						</label>
					</div>
					<div>
						<label for="ttpc_source_ttpc_{$ttpc_block->id|intval}">
							<input type="checkbox" id="ttpc_source_ttpc_{$ttpc_block->id|intval}" name="sources[]"
								   value="source_ttpc" class="ttpc_source_ttpc" {if $ttpc_block->source_ttpc || $ttpc_block->source_all}checked{/if}>
							{l s='Countdown timers' mod='ttproductcountdown'}
						</label>

						{if count($ttpc_all_timers)}
							<span class="btn btn-default ttpc-toggle-children-sources">{if $ttpc_block->source_ttpc || $ttpc_block->source_all}+{else}-{/if}</span>
							<div class="ttpc-children-sources" {if $ttpc_block->source_ttpc || $ttpc_block->source_all}style="display: none;" {/if}>
								{foreach from=$ttpc_all_timers item="ttpc"}
									<div>
										<label for="ttpc_source_ttpc_{$ttpc_block->id|intval}_{$ttpc->id|intval}">
											<input type="checkbox" id="ttpc_source_ttpc_{$ttpc_block->id|intval}_{$ttpc->id|intval}" name="sources[ttpc][]"
												   value="{$ttpc->id|intval}" class="ttpc_source_ttpc_item ttpc_source_checkbox"
												   {if !$ttpc_block->id || $ttpc_block->source_all || $ttpc_block->source_ttpc || in_array($ttpc->id, $ttpc_selected_timers)}checked{/if}>
										</label>
									</div>
								{/foreach}
							</div>
                        {/if}
					</div>

				</div>
			</div>
		</div>
    {elseif $input.type == 'custom_switch'}
		<div class="col-lg-9">
			<span class="switch prestashop-switch fixed-width-lg">
				{foreach $input.values as $value}
					<input type="radio" name="{$input.name|escape:'html':'UTF-8'}" id="{$value.id|escape:'html':'UTF-8'}" value="{$value.value|escape:'html':'UTF-8'}" {if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if (isset($input.disabled) && $input.disabled) or (isset($value.disabled) && $value.disabled)} disabled="disabled"{/if}/>
					{strip}
						<label for="{$value.id|escape:'html':'UTF-8'}">
							{$value.label|escape:'html':'UTF-8'}
						</label>
					{/strip}
				{/foreach}
				<a class="slide-button btn"></a>
			</span>
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
