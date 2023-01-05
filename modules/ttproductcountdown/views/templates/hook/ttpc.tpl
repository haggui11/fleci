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
    <div class="ttproductcountdown ttpc-inactive ttpc{$ttpc_psv|escape:'html':'UTF-8'} {$ttpc_id|escape:'html':'UTF-8'}
        {if strpos($ttpc_product_list_position, 'over_img') !== false}ttpc-over-img{/if}
        {if $ttpc_show_promo_text}ttpc-show-promo-text{else}ttpc-hide-promo-text{/if}"
         data-to="{$ttpc->to_time|escape:'html':'UTF-8'}">
        <div class="ttpc-main days-diff-{$ttpc_days_diff|intval} {if $ttpc_days_diff >= 100}ttpc-diff-m100{/if}">
            <div class="ttpc-offer-ends">
                {if $ttpc->name}
                    {$ttpc->name|escape:'html':'UTF-8'}
                {else}
                    {l s='Offer ends in:' mod='ttproductcountdown'}
                {/if}
            </div>
        </div>
    </div>
<script>
    if (typeof ttpc_initCountdown === 'function') {
        ttpc_initCountdown('.{$ttpc_id|escape:'html':'UTF-8'}');
    }
</script>
