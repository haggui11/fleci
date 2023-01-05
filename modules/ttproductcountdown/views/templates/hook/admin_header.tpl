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
<script type="text/javascript">
    var ttpc_psv = {$psv|floatval};
    var ttpc_ajax_url = "{$ajax_url|escape:'quotes':'UTF-8'}";
    var ttpc_remove_confirm_txt = "{l s='Are you sure you want to delete this countdown?' mod='ttproductcountdown'}";
    var ttpc_basic_confirm_txt = "{l s='Are you sure?' mod='ttproductcountdown'}";
    var ttpc_flatpickr = false;

    $(document).on('focus', '.ttpc-datepicker', function () {
        if (!$(this).hasClass('flatpickr-input')) {
            ttpc_loadDatetimepicker();
        }
    });

    function ttpc_loadDatetimepicker() {
        if (typeof ttpc_flatpickr === 'object' && typeof ttpc_flatpickr.destroy === 'function') {
            ttpc_flatpickr.destroy();
        }

        {literal}
        ttpc_flatpickr = flatpickr('.ttpc-datepicker', {
            enableTime: true,
            time_24hr: true,
            dateFormat: 'Z',
            altInput: true,
            altFormat: 'Y-m-d H:i',
            disableMobile: true,
            locale: {
                weekdays: {
                    {/literal}shorthand: ['{l s='Su.' mod='ttproductcountdown'}', '{l s='Mo.' mod='ttproductcountdown'}', '{l s='Tu.' mod='ttproductcountdown'}', '{l s='We.' mod='ttproductcountdown'}', '{l s='Th.' mod='ttproductcountdown'}', '{l s='Fr.' mod='ttproductcountdown'}', '{l s='Sa.' mod='ttproductcountdown'}'],
                    longhand: ['{l s='Sunday' mod='ttproductcountdown'}', '{l s='Monday' mod='ttproductcountdown'}', '{l s='Tuesday' mod='ttproductcountdown'}', '{l s='Wednesday' mod='ttproductcountdown'}', '{l s='Thursday' mod='ttproductcountdown'}', '{l s='Friday' mod='ttproductcountdown'}', '{l s='Saturday' mod='ttproductcountdown'}']{literal}
                },
                months: {
                    {/literal}shorthand: ['{l s='Jan' mod='ttproductcountdown'}', '{l s='Feb' mod='ttproductcountdown'}', '{l s='Mar' mod='ttproductcountdown'}', '{l s='Apr' mod='ttproductcountdown'}', '{l s='May' mod='ttproductcountdown'}', '{l s='Jun' mod='ttproductcountdown'}', '{l s='Jul' mod='ttproductcountdown'}', '{l s='Aug' mod='ttproductcountdown'}', '{l s='Sep' mod='ttproductcountdown'}', '{l s='Oct' mod='ttproductcountdown'}', '{l s='Nov' mod='ttproductcountdown'}', '{l s='Dec' mod='ttproductcountdown'}'],
                    longhand: ['{l s='January' mod='ttproductcountdown'}', '{l s='February' mod='ttproductcountdown'}', '{l s='March' mod='ttproductcountdown'}', '{l s='April' mod='ttproductcountdown'}', '{l s='May' mod='ttproductcountdown'}', '{l s='June' mod='ttproductcountdown'}', '{l s='July' mod='ttproductcountdown'}', '{l s='August' mod='ttproductcountdown'}', '{l s='September' mod='ttproductcountdown'}', '{l s='October' mod='ttproductcountdown'}', '{l s='November' mod='ttproductcountdown'}', '{l s='December' mod='ttproductcountdown'}']{literal}
                },
                firstDayOfWeek: 1
            }
        });
        {/literal}
    }
</script>