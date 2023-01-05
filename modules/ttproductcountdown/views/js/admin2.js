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
$(function () {
    ttpc_loadLocalTime();
    ttpc_loadDatetimepicker();

    // open last opened tab
    var tab_id = localStorage.getItem('ttpc_tab_id');
    if (tab_id) {
        var id = tab_id.replace('#psttab-', '');
        pst_openTab($('#psttn-' + id), tab_id);
    }

    // Tab switching
    $('.pst-tabs-list a').on('click', function(e) {
        e.preventDefault();

        var tab_id = $(this).attr('href');
        var hash = $(this).data('hash');

        pst_openTab($(this), tab_id);
    });

    $(document).on('click', '.add-ttpc', function (e) {
        e.preventDefault();
        $(this).siblings('.countdown-form').slideToggle(150);
    });
    $(document).on('click', '.close-countdown-form', function (e) {
        e.preventDefault();

        var $edit_row = $(this).parents('.ttpc_edit_row:first');
        // it's either edit form or new form
        if ($edit_row.length) {
            var $edit_row_content = $edit_row.find('.ttpc_edit_row_content');
            $edit_row.fadeOut(150);
            $edit_row_content.html('');
        } else {
            var $new_row = $(this).parents('.add-countdown-wrp:first').find('.countdown-form');
            $new_row.fadeOut(150);
        }
    });

    // Add/edit countdown
    $(document).on('click', '.btn-ttpc-submit', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $parent = $(this).parents('.countdown-form:first');
        $parent.find('.add-ttpc-error').fadeOut(100).html('');
        $parent.find('.btn-ttpc-submit').addClass('loading');
        $parent.find('.ttpc_prod_selected option').prop('selected', true);
        $parent.find('.ttpc-swap-right option').prop('selected', true);
        var data = $parent.find(':input').serialize();

        $.ajax({
            url: ttpc_ajax_url,
            data: data,
            method: 'post',
            success: function (result) {
                if (result === '1') {
                    ttpc_reloadCountdownList();
                    ttpc_reloadBlocks();
                } else {
                    $parent.find('.add-ttpc-error').fadeIn(100).html(result);
                }
            },
            complete: function () {
                $parent.find('.btn-ttpc-submit').removeClass('loading');
            }
        });
    });

    // Delete countdown from the list
    var del_selector = '.ttpc-countdown-list .delete';
    $(document).on('click', del_selector, function (e) {
        e.preventDefault();

        var url = $(this).attr('href');
        $.ajax({
            url: url,
            method: 'post',
            beforeSend: function () {
                $('#ttpc-countdown-list').addClass('ttpc-list-loading');
            },
            complete: function () {
                ttpc_reloadCountdownList();
            }
        });
    });

    // Bulk delete timers
    $(document).on('submit', '.ttpc-countdown-list form:first', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var action = $(this).attr('action');
        if (action.indexOf('submitBulkdeletettpc') !== -1) {
            var ids = [];
            $('[name="ttpcfBox[]"]:checked').each(function () {
                ids.push($(this).val());
            });
            $.ajax({
                url: ttpc_ajax_url,
                data: {ajax: true, action: 'bulkDeletePSPC', ids: ids},
                method: 'post',
                beforeSend: function () {
                    $('#ttpc-countdown-list').addClass('ttpc-list-loading');
                },
                success: function () {
                    ttpc_reloadCountdownList();
                }
            });
        }
        // todo can't submit form, pagination doesn't work

        return false;
    });
    // ps1.5
    $(document).on('click', '.ttpc15 [name=submitBulkdeletettpcf]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var ids = [];
        $('[name="ttpcfBox[]"]:checked').each(function () {
            ids.push($(this).val());
        });
        $.ajax({
            url: ttpc_ajax_url,
            data: {ajax: true, action: 'bulkDeletePSPC', ids: ids},
            method: 'post',
            beforeSend: function () {
                $('#ttpc-countdown-list').addClass('ttpc-list-loading');
            },
            success: function () {
                ttpc_reloadCountdownList();
            }
        });
        // todo can't submit form, pagination doesn't work

        return false;
    });

    // Toggle countdown status
    $(document).on('click', '.ttpc_active_toggle', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var id_ttpc = $(this).data('id-ttpc');

        if (id_ttpc) {
            $(this).parents('td:first').css('opacity', 0.4);
            $.ajax({
                url: ttpc_ajax_url,
                data: {ajax: true, action: 'changeCountdownStatus', id_ttpc: id_ttpc},
                method: 'post',
                success: function () {
                    ttpc_reloadCountdownList();
                }
            });
        }
    });

    // "edit" button in the list of timers
    $(document).on('click', '.ttpc-countdown-list .edit, .ttpc-countdown-list td.pointer', function (e) {
        e.preventDefault();

        if (!$(this).hasClass('ttpc-td-items-toggle')) {
            var $edit_row = $(this).parents('tr:first').next('.ttpc_edit_row');
            var $edit_row_content = $edit_row.find('.ttpc_edit_row_content');
            var id_ttpc = $edit_row.data('id-ttpc');

            if (!$edit_row.is(':visible')) {
                $edit_row_content.html('<div class="pst_loader"></div>');
                $edit_row.fadeToggle(150);

                $.ajax({
                    url: ttpc_ajax_url,
                    data: {ajax: true, action: 'getCountdownForm', id_ttpc: id_ttpc},
                    method: 'post',
                    success: function (html) {
                        $edit_row_content.html(html);
                        ttpc_loadDatetimepicker();
                        ttpc_initTypeWatch($edit_row.find('.ttpc-product-search'));
                    }
                });
            } else {
                $edit_row.fadeOut(150);
                $edit_row_content.html('');
            }
        }
    });

    // Click on an item link should simply open the link
    $(document).on('click', '.ttpc-item-link-wrp a', function (e) {
        e.stopPropagation();
    });

    // Toggle items list display
    $(document).on('click', '.ttpc-items-toggle', function (e) {
        e.stopPropagation();
        e.preventDefault();

        $(this).find('i').toggleClass('icon-caret-down icon-caret-up');
        $(this).parents('.ttpc-td-items-toggle').find('.ttpc-items-wrp').slideToggle(100);
    });

    // Toggle items list display
    $(document).on('click', '.ttpc-td-items-toggle', function (e) {
        e.stopPropagation();
        e.preventDefault();

        $(this).find('.ttpc-items-toggle i').toggleClass('icon-caret-down icon-caret-up');
        $(this).find('.ttpc-items-text-1').slideToggle(100);
        $(this).find('.ttpc-items-wrp').slideToggle(100);
    });

    $(document).on('change', '.ttpc-cg-all', function (e) {
        var $parent = $(this).parents('.ttpc-options-row:first');
        if ($(this).is(':checked')) {
            $parent.find('.ttpc-cg-wrp').slideUp(150);
        } else {
            $parent.find('.ttpc-cg-wrp').slideDown(150);
        }
    });

    $('#ttpc_show_pro').on('click', function (e) {
        $('#ttpc_pro_features_content').slideDown();
        $(this).remove();

        e.preventDefault();
    });
});

function ttpc_reloadCountdownList() {
    var $list_container = $('#ttpc-countdown-list');
    $list_container.addClass('ttpc-list-loading');

    $.ajax({
        url: ttpc_ajax_url,
        data: {ajax: 1, action: 'renderCountdownList'},
        method: 'post',
        success: function (result) {
            $list_container.html(result);
            ttpc_loadLocalTime();
            ttpc_loadDatetimepicker();
            ttpc_initTypeWatch();
       },
        complete: function () {
            $list_container.removeClass('ttpc-list-loading');
        }
    });
}

function pst_openTab($elem, tab_id) {
    $('.pst-tabs-list a').removeClass('active');
    $elem.addClass('active');
    $('.pst-tab-content').hide();
    $(tab_id).fadeIn(200);

    localStorage.setItem('ttpc_tab_id', tab_id);
}

function ttpc_loadLocalTime() {
    $('.ttpc-datetime-utc').each(function () {
        var text = $.trim($(this).text());
        if (text) {
            var date = moment.utc(text).format('YYYY-MM-DD HH:mm:ss');
            var stillUtc = moment.utc(date).toDate();
            var date_string = moment(stillUtc).local().format('YYYY-MM-DD HH:mm:ss');
            $(this).text(date_string);
            $(this).removeClass('ttpc-datetime-utc');
        }
    });
}