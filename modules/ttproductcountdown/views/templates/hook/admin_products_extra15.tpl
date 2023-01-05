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
<div id="module_ttproductcountdown" class="panel product-tab pstab15">
    <input type="hidden" name="submitted_tabs[]" value="{$module_name|escape:'html':'UTF-8'}" />
    <input type="hidden" name="{$module_name|escape:'html':'UTF-8'}-submit" value="1" />
    <h3>{l s='Countdown' mod='ttproductcountdown'}</h3>

    <div class="form-group">
        <div class="col-lg-1"><span class="pull-right"></span></div>
        <label class="control-label col-lg-2">
            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title='{l s='Set to NO if you want to completely disable countdown for this product.' mod='ttproductcountdown'}'>
				 {l s='Enabled:' mod='ttproductcountdown'}
			</span>
        </label>
        <div class="col-lg-5">
            <span class="switch prestashop-switch fixed-width-lg">
				<input onclick="toggleDraftWarning(false);showOptions(true);showRedirectProductOptions(false);" type="radio" name="ttpc_active" id="ttpc_active_on" value="1" {if !isset($countdown_data.active) || (isset($countdown_data.active) && $countdown_data.active)}checked{/if}>
				<label for="ttpc_active_on" class="radioCheck">
                    {l s='Yes' mod='ttproductcountdown'}
                </label>
				<input onclick="toggleDraftWarning(true);showOptions(false);showRedirectProductOptions(true);" type="radio" name="ttpc_active" id="ttpc_active_off" value="0"{if (isset($countdown_data.active) && !$countdown_data.active)}checked{/if}>
				<label for="ttpc_active_off" class="radioCheck">
                    {l s='No' mod='ttproductcountdown'}
                </label>
				<a class="slide-button btn"></a>
			</span>
        </div>
    </div>

    <div class="form-group">
        <div class="col-lg-1"><span class="pull-right"></span></div>
        <label class="control-label col-lg-2">
            {l s='Promo text:' mod='ttproductcountdown'}
        </label>
        <div class="col-lg-5">
            {assign var='key' value='ttpc_name'}
            {foreach from=$languages item='language' name='ttpc_lang_foreach'}
                {if $smarty.foreach.ttpc_lang_foreach.first}
                    {assign var='current_id_lang' value=$language.id_lang}
                {/if}
                <div id="{$key|escape:'html':'UTF-8'}_{$language.id_lang|intval}" style="margin-bottom:8px; display: {if !$smarty.foreach.ttpc_lang_foreach.first}none{/if}; float: left; vertical-align: top;">
                    <input type="text" name="{$key|escape:'html':'UTF-8'}_{$language.id_lang|intval}" value="{if isset($countdown_data['name'][$language.id_lang|intval])}{$countdown_data['name'][$language.id_lang]|intval}{/if}" />
                </div>
            {/foreach}
            {if count($languages) > 1}
                <div class="displayed_flag">
                    <img src="../img/l/{$current_id_lang|intval}.jpg"
                         class="pointer"
                         id="language_current_{$key|escape:'html':'UTF-8'}"
                         onclick="toggleLanguageFlags(this);" loading="lazy"/>
                </div>
                <div id="languages_{$key|escape:'html':'UTF-8'}" class="language_flags">
                    {*{l s='Choose language:'}<br /><br />*}
                    {foreach $languages as $language}
                        <img src="../img/l/{$language.id_lang|intval}.jpg"
                             class="pointer"
                             alt="{$language.name|escape:'html':'UTF-8'}"
                             title="{$language.name|escape:'html':'UTF-8'}"
                             onclick="changeLanguage('{$key|escape:'html':'UTF-8'}', '{if isset($custom_key)}{$custom_key|escape:'html':'UTF-8'}{else}{$key|escape:'html':'UTF-8'}{/if}', {$language.id_lang|intval}, '{$language.iso_code|escape:'html':'UTF-8'}');" loading="lazy"/>
                    {/foreach}
                </div>
            {/if}
        </div>
    </div>

    <div class="form-group">
        <div class="col-lg-1"><span class="pull-right"></span></div>

        <label class="control-label col-lg-2">
             {l s='Display:' mod='ttproductcountdown'}
        </label>
        <div class="col-lg-5">
            <div class="row">
                <div class="col-lg-6" style="display: inline-block;">
                    <div class="input-group">
                        <span class="input-group-addon">{l s='from' mod='ttproductcountdown'}</span>
                        <input type="text" name="ttpc_from" class="ttpc-datepicker test ttpc-datetime-utc" value="{if isset($countdown_data.from_tz)}{$countdown_data.from_tz|escape:'html':'UTF-8'}{/if}" style="text-align: center;" id="ttpc_from">
                        <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
                    </div>
                </div>
                <div class="col-lg-6" style="display: inline-block;">
                    <div class="input-group">
                        <span class="input-group-addon">{l s='to' mod='ttproductcountdown'}</span>
                        <input type="text" name="ttpc_to" class="ttpc-datepicker ttpc-datetime-utc" value="{if isset($countdown_data.to_tz)}{$countdown_data.to_tz|escape:'html':'UTF-8'}{/if}" style="text-align: center;" id="ttpc_to">
                        <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-lg-1"><span class="pull-right"></span></div>
        <label class="control-label col-lg-2">
            {l s='Use dates from specific prices:' mod='ttproductcountdown'}
        </label>
        <div class="col-lg-5">
            <select name="ttpc_specific_price" id="ttpc_specific_price">
                <option value="">--</option>
                {foreach from=$specific_prices item=specific_price}
                    <option value="{$specific_price.id_specific_price|intval}"
                            data-from="{$specific_price.from|escape:'html':'UTF-8'}"
                            data-to="{$specific_price.to|escape:'html':'UTF-8'}">
                        {l s='from' mod='ttproductcountdown'}: {$specific_price.from|escape:'html':'UTF-8'}&nbsp;&nbsp;&nbsp;
                        {l s='to' mod='ttproductcountdown'}: {$specific_price.to|escape:'html':'UTF-8'}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>

    {if isset($countdown_data.id_ttpcf)}
        <input type="hidden" name="id_ttpc" id="id_ttpc" value="{$countdown_data.id_ttpcf|intval}">
        <div class="form-group">
            <div class="col-lg-1"><span class="pull-right"></span></div>
            <div class="control-label col-lg-2"></div>
            <div class="col-lg-5">
                <button type="button" id="ttpc-reset-countdown" class="btn btn-default" data-id-countdown="{$countdown_data.id_ttpcf|intval}">{l s='Reset & remove' mod='ttproductcountdown'}</button>
            </div>
        </div>
    {else}
        <input type="hidden" name="id_ttpc" id="id_ttpc" value="0">
    {/if}

    <div class="form-group">
        <div class="row">
            <div class="col-lg-3"></div>
            <div class="col-lg-9">
                <div id="ttpc_error" class="alert alert-danger" style="display: none;"></div>
                <div id="ttpc_saved" class="conf" style="display: none;">{l s='Saved' mod='ttproductcountdown'}</div>
                <input type="hidden" name="id_product" value="{$id_product|intval}">
                <button class="btn btn-primary" id="ttpc_save_product_countdown">{l s='Save countdown' mod='ttproductcountdown'}</button>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var ttpc_ajax_url = "{$ajax_url|escape:'quotes':'UTF-8'}";
    </script>
</div>
