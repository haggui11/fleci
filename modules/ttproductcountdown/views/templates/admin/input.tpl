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
{if isset($params.type)}
    {if $params.type == 'text'}
        {if isset($params.lang) && $params.lang}
            {if $psv >= 1.6}
                {foreach from=$languages item=language name=helper_lang_foreach}
                    <div class="translatable-field row lang-{$language.id_lang|intval}" {if !$smarty.foreach.helper_lang_foreach.first}style="display: none;"{/if}>
                        <div class="col-lg-8">
                            <input type="text"
                                   id="{if isset($params.id)}{$params.id|escape:'html':'UTF-8'}{/if}_{$language.id_lang|intval}"
                                   class="form-control {if isset($params.class)}{$params.class|escape:'html':'UTF-8'}{/if}"
                                   name="{if isset($params.name)}{$params.name|escape:'html':'UTF-8'}{/if}_{$language.id_lang|intval}"
                                   value="{if isset($params.values) && isset($params.values[$language.id_lang])}{$params.values[$language.id_lang]|escape:'html':'UTF-8'}{/if}"
                            />
                        </div>
                        <div class="col-lg-2">
                            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                {$language.iso_code|escape:'html':'UTF-8'}
                                <i class="icon-caret-down"></i>
                            </button>
                            <ul class="dropdown-menu">
                                {foreach from=$languages item=lang}
                                    <li><a href="javascript:hideOtherLanguage({$lang.id_lang|intval});" tabindex="-1">{$lang.name|escape:'html':'UTF-8'}</a></li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>
                {/foreach}
            {elseif $psv == 1.5}
                <div class="translatable">
                    {foreach from=$languages item=language name=helper_lang_foreach}
                        <div class="lang_{$language.id_lang|intval}" style="{if !$smarty.foreach.helper_lang_foreach.first}display: none;{/if} float: left;">
                            <input type="text"
                                   name="{if isset($params.name)}{$params.name|escape:'html':'UTF-8'}{/if}_{$language.id_lang|intval}"
                                   id="{if isset($params.id)}{$params.id|escape:'html':'UTF-8'}{/if}_{$language.id_lang|intval}"
                                   value="{if isset($params.values) && isset($params.values[$language.id_lang])}{$params.values[$language.id_lang]|escape:'html':'UTF-8'}{/if}"
                                   class="{if isset($params.class)}{$params.class|escape:'html':'UTF-8'}{/if}"
                            >
                        </div>
                    {/foreach}
                </div>
                <script type="text/javascript">
                    $(function () {
                        var ttpc_languages = new Array();
                        {foreach from=$languages item=language key=k}
                        ttpc_languages[{$k|escape:'quotes':'UTF-8'}] = {
                            id_lang: {$language.id_lang|intval},
                            iso_code: '{$language.iso_code|escape:'quotes':'UTF-8'}',
                            name: '{$language.name|escape:'quotes':'UTF-8'}'
                        };
                        {/foreach}
                        displayFlags(ttpc_languages, {$id_lang_default|intval});
                    });
                </script>
            {/if}
        {/if}
    {/if}
{/if}