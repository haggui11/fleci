{**
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* @author    Presta.Site
* @copyright 2017 Presta.Site
* @license   LICENSE.txt
*}
<div id="module_ttproductcountdown" class="">
    <input type="hidden" name="submitted_tabs[]" value="{$module_name|escape:'html':'UTF-8'}" />
    <input type="hidden" name="{$module_name|escape:'html':'UTF-8'}-submit" value="1" />

    <div class="row">
        <div class="col-lg-12 col-xl-4">
            <fieldset class="form-group">
                <label class="form-control-label">{l s='Enabled:' mod='ttproductcountdown'}</label>
                <div id="ttpc_active">
                    <div class="radio">
                        <label class="">
                            <input type="radio" id="ttpc_active_1" name="ttpc_active" value="1" {if !isset($countdown_data.active) || (isset($countdown_data.active) && $countdown_data.active)}checked{/if}>
                            {l s='Yes' mod='ttproductcountdown'}
                        </label>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <label class="">
                            <input type="radio" id="ttpc_active_0" name="ttpc_active" value="0" {if (isset($countdown_data.active) && !$countdown_data.active)}checked{/if}>
                            {l s='No' mod='ttproductcountdown'}
                        </label>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="col-lg-12 col-xl-4">
            <fieldset class="form-group">
                <label class="form-control-label">{l s='Promo text:' mod='ttproductcountdown'}</label>
                <div class="translations tabbable" id="ttpc_name_wrp">
                    <div class="translationsFields tab-content ">
                        {foreach from=$languages item=language name=ttpc_lang_foreach}
                            <div class="translationsFields-ttpc_name tab-pane translation-field translation-label-{$language.iso_code|escape:'html':'UTF-8'} {if $smarty.foreach.ttpc_lang_foreach.first}active{/if}">
                                <input type="text"
                                       id="ttpc_name_{$language.id_lang|intval}"
                                       name="ttpc_name_{$language.id_lang|intval}"
                                       class="form-control"
                                       value="{if isset($countdown_data['name'][$language.id_lang])}{$countdown_data['name'][$language.id_lang]|escape:'html':'UTF-8'}{/if}"
                                />
                            </div>
                        {/foreach}
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-xl-4">
            <fieldset class="form-group">
                <label class="form-control-label">{l s='Display:' mod='ttproductcountdown'}</label>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">{l s='from' mod='ttproductcountdown'}</span></div>
                            <input type="text" name="ttpc_from" class="ttpc-datepicker form-control ttpc-datetime-utc" value="{if isset($countdown_data.from_tz)}{$countdown_data.from_tz|escape:'html':'UTF-8'}{/if}" style="text-align: center;" id="ttpc_from">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">{l s='to' mod='ttproductcountdown'}</span></div>
                            <input type="text" name="ttpc_to" class="ttpc-datepicker form-control ttpc-datetime-utc" value="{if isset($countdown_data.to_tz)}{$countdown_data.to_tz|escape:'html':'UTF-8'}{/if}" style="text-align: center;" id="ttpc_to">
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="col-lg-12 col-xl-4">
            <fieldset class="form-group">
                <label class="form-control-label">{l s='Use dates from specific prices:' mod='ttproductcountdown'}</label>
                <div id="ttpc_specific_price_wrp">
                    <select name="ttpc_specific_price" id="ttpc_specific_price" class="form-control">
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
            </fieldset>
        </div>
    </div>

    {if isset($countdown_data.id_ttpcf)}
        <input type="hidden" name="id_ttpc" id="id_ttpc" value="{$countdown_data.id_ttpcf|intval}">
        <div class="form-group">
            <div class="row">
                <div class="col-lg-12 col-xl-4">
                    <fieldset class="form-group">
                        <div>
                            <button type="button" id="ttpc-reset-countdown" class="btn btn-default" data-id-countdown="{$countdown_data.id_ttpcf|intval}">{l s='Reset & remove' mod='ttproductcountdown'}</button>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    {else}
        <input type="hidden" name="id_ttpc" id="id_ttpc" value="0">
    {/if}

    <div class="form-group">
        <div id="ttpc_error" class="alert alert-danger" style="display: none;"></div>
        <div id="ttpc_saved" class="alert alert-success" style="display: none;">{l s='Saved' mod='ttproductcountdown'}</div>
        <input type="hidden" name="id_product" value="{$id_product|intval}">
        <button class="btn btn-primary" id="ttpc_save_product_countdown">{l s='Save countdown' mod='ttproductcountdown'}</button>
    </div>
</div>
