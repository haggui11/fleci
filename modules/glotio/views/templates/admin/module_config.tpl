{**
* 2007-2020 PrestaShop and Contributors
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/OSL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to https://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2021 PrestaShop SA and Contributors
* @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}
<div class="glotio" style="border: #DFDFDF 1px solid; height: 900px; margin-bottom: 20px;">
    <iframe id="glotio-iframe"
            scrolling="no"
            frameborder="0"
            style="overflow: hidden; height: 900px; width: 100%"
            src="{$iframe_url|escape:'htmlall':'UTF-8'}"
    >
    </iframe>
</div>

<div style="margin-top: 15px">
    <a id="glotio-debug-show" class="btn btn-default">{l s='Show debug information' mod='glotio'}</a>
    <div id="glotio-debug-info" style="display: none" class="panel">
        <p>
            {l s='If you have problems to create an account or to connect your shop with Glotio, copy & paste this info and submit it to us on' mod='glotio'}
            <a href="mailto:support@glotio.com">support@glotio.com</a>
            {l s='describing your problem.' mod='glotio'}
        </p>
        <p>
            {l s='Avoid sharing this information with third parties who are not part of Glotio.' mod='glotio'}
        </p>
        <pre>{$JSON_PRETTY_PRINT = 128}{json_encode($params, $JSON_PRETTY_PRINT)}</pre>
    </div>
</div>

<script>
    jQuery(function($){
        $('#glotio-debug-show').on('click', function (e) {
            e.preventDefault();
            $('#glotio-debug-info').toggle();
            return false;
        });
    });
</script>