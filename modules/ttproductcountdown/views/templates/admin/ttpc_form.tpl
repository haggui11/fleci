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

<div class="{if !$ttpc->id}add-countdown-wrp{else}edit-countdown-wrp{/if}">
    {if !$ttpc->id}
    <button class="btn btn-primary add-ttpc">
        <i class="icon-plus-square"></i>
        {l s='Add new countdown' mod='ttproductcountdown'}
    </button>
    {/if}

    <div class="countdown-form">
        <div class="row">
            <div class="col-lg-{if $ttpc->id}11{else}12{/if}">
                <div class="form-group row ttpc-items-form-group">
                    <label class="control-label col-lg-2">
                        <span class="label-tooltip" data-toggle="tooltip" data-placement="bottom" data-html="true" title="{l s='Select products for using with this countdown' mod='ttproductcountdown'}">
                            {l s='Items:' mod='ttproductcountdown'} <sup>*</sup>
                        </span>
                    </label>
                    <div class="col-lg-10 col-xs-12 ttpc-prodselects-wrp">
                        <button class="btn btn-default ttpc-select-obj ttpc-select-products" data-type="products"><i class="icon-plus"></i> {l s='Select products' mod='ttproductcountdown'} <span class="badge badge-info ttpc-number-products">{count($ttpc_chosen_products)|intval}</span></button>

                        <div class="ttpc-select-wrp ttpc-select-wrp-products">
                            <div class="row">
                                <div class="col-lg-6 ttpc-col-filters">
                                    <span class="btn btn-default btn-xs ttpc-toggle-category-filter"><i class="icon-filter"></i> {l s='Filter by category' mod='ttproductcountdown'}</span>
                                    <span class="btn btn-default btn-xs ttpc-toggle-manufacturer-filter"><i class="icon-filter"></i> {l s='Filter by manufacturer' mod='ttproductcountdown'}</span>
                                    <label class="ttpc-search-combinations-label" for="ttpc-search-combinations-{$ttpc->id|intval}"><input type="checkbox" class="ttpc-search-combinations" id="ttpc-search-combinations-{$ttpc->id|intval}"> {l s='Search combinations' mod='ttproductcountdown'}</label>
                                    <div class="ttpc-manufacturer-wrp ttpc-filter-wrp">
                                        <select class="ttpc-manufacturer-select">
                                            <option value="">--</option>
                                            {foreach from=$ttpc_manufacturers item='manufacturer'}
                                                <option value="{$manufacturer.id_manufacturer|intval}">{$manufacturer.name|escape:'html':'UTF-8'}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="ttpc-category-wrp ttpc-filter-wrp">
                                        {$product_category_tree nofilter} {*rendered html*}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <input type="text" class="form-control ttpc-product-search" placeholder="{l s='Search products' mod='ttproductcountdown'}">
                                </div>
                                <div class="col-lg-6"><h4>{l s='Selected products:' mod='ttproductcountdown'}</h4></div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <select multiple class="ttpc_prod_select">
                                        <option disabled value="" class="ttpc-default-option">{l s='Search products or use filters to get results' mod='ttproductcountdown'}</option>
                                    </select>
                                    <a href="#" class="btn btn-default btn-block ttpc_multiple_select_add">
                                        {l s='Add' mod='ttproductcountdown'} <i class="icon-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="col-lg-6">
                                    <select multiple class="ttpc_prod_selected" name="products[]">
                                        {foreach from=$ttpc_chosen_products item='chosen_product'}
                                            {assign var='ref' value=$ttpc_module->getProductReference($chosen_product.id_object, $chosen_product.id_product_attribute)}
                                            <option value="{$chosen_product.id_object|intval}{if $chosen_product.id_product_attribute}-{$chosen_product.id_product_attribute|intval}{/if}">#{$chosen_product.id_object|intval} {Product::getProductName($chosen_product.id_object, $chosen_product.id_product_attribute)|escape:'html':'UTF-8'} {if $ref}({l s='ref:' mod='ttproductcountdown'} {$ref|escape:'html':'UTF-8'}){/if}</option>
                                        {/foreach}
                                    </select>
                                    <a href="#" class="btn btn-default btn-block ttpc_multiple_select_del">
                                        {l s='Remove' mod='ttproductcountdown'} <i class="icon-arrow-left"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group row">
                    <label class="control-label col-lg-2">
                        <span class="label-tooltip" data-toggle="tooltip" data-placement="bottom" data-html="true" title="{l s='A text displayed alongside the countdown.' mod='ttproductcountdown'}">
                            {l s='Promo text:' mod='ttproductcountdown'}
                        </span>
                    </label>
                    <div class="col-lg-5">
                        {assign var='name_id' value="ttpc-add-name`$ttpc->id`"}
                        {$ttpc_module->generateInput(['type' => 'text', 'lang' => true, 'name' => 'name', 'class' => 'ttpc-add-name', 'id' => $name_id, 'values' => $ttpc->name]) nofilter}
                    </div>
                </div>

                <div class="form-group row datepicker-row">
                    <label class="control-label col-lg-2">
                        <span class="label-tooltip" data-toggle="tooltip" data-placement="bottom" data-html="true" title="{l s='Select time interval for this countdown.' mod='ttproductcountdown'}">
                            {l s='Display:' mod='ttproductcountdown'} <sup>*</sup>
                        </span>
                    </label>
                    <div class="col-lg-5">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="input-group">
                                    <span class="input-group-addon">{l s='from' mod='ttproductcountdown'}</span>
                                    <input type="text" name="from" class="ttpc-datepicker" value="{$ttpc->from_tz|escape:'html':'UTF-8'}" style="text-align: center;">
                                    <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="input-group">
                                    <span class="input-group-addon">{l s='to' mod='ttproductcountdown'}</span>
                                    <input type="text" name="to" class="ttpc-datepicker" value="{$ttpc->to_tz|escape:'html':'UTF-8'}" style="text-align: center;">
                                    <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {if !$ttpc->id}
                <div class="form-group row ">
                    <div class="col-lg-2">

                    </div>
                    <div class="col-lg-4 col-xs-12">
                        <div class="alert alert-danger add-ttpc-error" style="display: none;"></div>
                        <div class="ttpc-form-btns-add">
                            <button class="btn btn-primary btn-ttpc-submit">{l s='Add' mod='ttproductcountdown'}</button>
                        </div>
                    </div>
                </div>
                {else}
                    <div class="alert alert-danger add-ttpc-error" style="display: none;"></div>
                {/if}
            </div>
            {if $ttpc->id}
                <div class="col-lg-1">
                    <div class="ttpc-form-btns-edit">
                        <button class="btn btn-primary pull-right btn-ttpc-submit"><i class="icon-save"></i> {l s='Save' mod='ttproductcountdown'}</button>
                        <input type="hidden" name="id_ttpc" value="{$ttpc->id|intval}">
                    </div>
                </div>
            {/if}
            <input type="hidden" name="action" value="saveCountdown">
            <input type="hidden" name="ajax" value="1">
        </div>

        <div class="close-countdown-form close-product-countdown-form">&#10006;</div>
    </div>
</div>
