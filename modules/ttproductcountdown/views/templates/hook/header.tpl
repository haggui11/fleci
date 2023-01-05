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
<style type="text/css">
    {* both positions can be in the hook displayProductPriceBlock, so we have to hide duplicates via css *}
    {if $ttpc_module->product_position != 'displayProductPriceBlock' && ($ttpc_module->product_list_position == 'over_img' || $ttpc_module->product_list_position == 'displayProductPriceBlock')}
    #product .ttpc-wrp.ttpc_displayProductPriceBlock {ldelim}
        display: none !important;
    {rdelim}
    #product .ajax_block_product .ttpc-wrp.ttpc_displayProductPriceBlock,
    #product .product_list .ttpc-wrp.ttpc_displayProductPriceBlock,
    #product #product_list .ttpc-wrp.ttpc_displayProductPriceBlock,
    #product .product-miniature .ttpc-wrp.ttpc_displayProductPriceBlock {ldelim}
        display: block !important;
    {rdelim}
    {elseif $ttpc_module->product_position == 'displayProductPriceBlock' && $ttpc_module->product_list_position != 'over_img' && $ttpc_module->product_list_position != 'displayProductPriceBlock'}
    #product .ttpc-wrp.ttpc_displayProductPriceBlock {ldelim}
        display: block !important;
    {rdelim}
    .ajax_block_product .ttpc-wrp.ttpc_displayProductPriceBlock,
    .product_list .ttpc-wrp.ttpc_displayProductPriceBlock,
    #product_list .ttpc-wrp.ttpc_displayProductPriceBlock,
    .product-miniature .ttpc-wrp.ttpc_displayProductPriceBlock {ldelim}
        display: none !important;
    {rdelim}
    {/if}

    {if $ttpc_custom_css}
        {$ttpc_custom_css|escape:'quotes':'UTF-8'}
    {/if}
</style>

<script type="text/javascript">
    var ttpc_labels = ['days', 'hours', 'minutes', 'seconds'];
    var ttpc_labels_lang = {
        'days': '{l s='days' mod='ttproductcountdown'}',
        'hours': '{l s='hours' mod='ttproductcountdown'}',
        'minutes': '{l s='min' mod='ttproductcountdown'}',
        'seconds': '{l s='sec' mod='ttproductcountdown'}'
    };
    var ttpc_labels_lang_1 = {
        'days': '{l s='day' mod='ttproductcountdown'}',
        'hours': '{l s='hour' mod='ttproductcountdown'}',
        'minutes': '{l s='min' mod='ttproductcountdown'}',
        'seconds': '{l s='sec' mod='ttproductcountdown'}'
    };
    var ttpc_offer_txt = "{l s='Offer ends in:' mod='ttproductcountdown'}";
    var ttpc_theme = "{$ttpc_theme|escape:'html':'UTF-8'}";
    var ttpc_psv = {$psv|floatval};
    var ttpc_hide_after_end = {$ttpc_hide_after_end|intval};
    var ttpc_hide_expired = {$ttpc_hide_expired|intval};
    var ttpc_highlight = "{$ttpc_highlight|escape:'html':'UTF-8'}";
    var ttpc_position_product = "{$ttpc_position_product|escape:'html':'UTF-8'}";
    var ttpc_position_list = "{$ttpc_position_list|escape:'html':'UTF-8'}";
    var ttpc_adjust_positions = {$ttpc_adjust_positions|intval};
    var ttpc_token = "{Tools::getToken(false)|escape:'html':'UTF-8'}";
</script>