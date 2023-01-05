/**
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
*  International Registered Trademark & Property of PrestaShop SA */
$(function(){
    ttpc_loadDatetimepicker();
    if (typeof tabs_manager !== 'undefined') {
        tabs_manager.onLoad('ModulePsproductcountdown', function () {
            ttpc_loadDatetimepicker();
        });
    }

    $(document).on('change', '#ttpc_specific_price', function () {
        if ($(this).val()) {
            var from = $(this).find('option:selected').data('from');
            var to = $(this).find('option:selected').data('to');
            $('#ttpc_from').val(from);
            $('#ttpc_from').next('.ttpc-datepicker').val(from);
            $('#ttpc_to').val(to);
            $('#ttpc_to').next('.ttpc-datepicker').val(to);
        }
    });

    $(document).on('click', '#ttpc-reset-countdown',function(){
        var id_countdown = $(this).data('id-countdown');

        $('#ttproductcountdown').find('input[type=text], select').val('');

        $.ajax({
            url: ttpc_ajax_url,
            data: {ajax: true, action: 'removeProductCountdown', id_countdown: id_countdown},
            method: 'post',
            success: function () {
                location.reload();
            }
        });
    })

    $(document).on('click', '#ttpc_save_product_countdown', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $this = $(this);

        var data = {ajax: true, action: 'productUpdate'};
        $('#module_ttproductcountdown').find(':input').each(function () {
            var name = $(this).attr('name');
            var value = $(this).val();
            if ($(this).attr('type') === 'radio' && !$(this).is(':checked')) {
                return;
            }
            if (name) {
                data[name] = value;
            }
        });

        // clear errors
        $('#ttpc_error').html('').hide();
        $('#ttpc_saved').hide();
        $this.prop('disabled', true);

        $.ajax({
            url: ttpc_ajax_url,
            data: data,
            method: 'post',
            dataType: 'json',
            success: function (result) {
                $this.prop('disabled', false);

                if (result.success) {
                    // If success
                    $('#ttpc_saved').fadeIn(200);
                    setTimeout(function () {
                        $('#ttpc_saved').fadeOut(500);
                    }, 5000);
                    $('#id_ttpc').val(result.id_ttpc);
                } else {
                    // If error
                    $('#ttpc_error').html(result.error).fadeIn(200);
                }
            }
        });
    });
});
