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

<div class="pstg-tabs row {if $psv == 1.5}ttpc15{/if}">
    <div class="col-lg-12">
        <div class="pst-tabs-list list-group">
            {foreach from=$ttpc_tabs item="tab" name="tab_names" key='key'}
                <a class="list-group-item col-lg-2 col-md-3 col-sm-4 {if $smarty.foreach.tab_names.first}active{/if}" href="#psttab-{$key|escape:'html':'UTF-8'}" data-hash="#tab-{$key|escape:'html':'UTF-8'}" id="psttn-{$key|escape:'html':'UTF-8'}">{$tab.name|escape:'html':'UTF-8'}</a>
            {/foreach}
        </div>
    </div>
    <div class="col-lg-12 pst-tab-content-wrp">
        {foreach from=$ttpc_tabs item="tab" name="tab_contents" key='key'}
            <div class="pst-tab-content" id="psttab-{$key|escape:'html':'UTF-8'}" {if !$smarty.foreach.tab_contents.first}style="display: none;" {/if}>
                {if is_array($tab.content)}
                    {foreach from=$tab.content item='content' key='tab_wrp_id'}
                        <div id="{$tab_wrp_id|escape:'html':'UTF-8'}">
                            {$content nofilter} {* html *}
                        </div>
                    {/foreach}
                {else}
                    {$tab.content nofilter} {* html *}
                {/if}
            </div>
        {/foreach}
    </div>
</div>