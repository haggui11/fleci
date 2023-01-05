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
*  International Registered Trademark & Property of PrestaShop SA
*/
$(function () {
    ttpc_initTypeWatch();

    // select products/categories/manufacturers block on adding/modifying a countdown
    $(document).on('click', '.ttpc-select-obj', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var type = $(this).data('type');
        var $parent = $(this).parents('.countdown-form:first');

        $(this).toggleClass('active');
        $parent.find('.ttpc-select-obj').not(this).removeClass('active');

        $parent.find('.ttpc-select-wrp-' + type).fadeToggle(150);
        $parent.find('.ttpc-select-wrp').not('.ttpc-select-wrp-' + type).hide();
    });

    // Add selected products
    $(document).on('click', '.ttpc_multiple_select_add', function (e) {
        e.preventDefault();

        var $parent = $(this).parents('.ttpc-prodselects-wrp:first');
        ttpc_addCountdownProducts($parent);
    });
    $(document).on('dblclick', '.ttpc_prod_select', 'option', function(){
        var $parent = $(this).parents('.ttpc-prodselects-wrp:first');
        ttpc_addCountdownProducts($parent);
    });

    // Remove selected products
    $(document).on('click', '.ttpc_multiple_select_del', function (e) {
        e.preventDefault();

        var $parent = $(this).parents('.ttpc-prodselects-wrp:first');
        ttpc_removeCountdownProducts($parent);
    });
    $(document).on('dblclick', '.ttpc_prod_selected', 'option', function(){
        var $parent = $(this).parents('.ttpc-prodselects-wrp:first');
        ttpc_removeCountdownProducts($parent);
    });

    // Show / hide category filter
    $(document).on('click', '.ttpc-toggle-category-filter', function () {
        $(this).siblings('.ttpc-category-wrp').slideToggle(200);
    });
    // Show / hide manufacturer filter
    $(document).on('click', '.ttpc-toggle-manufacturer-filter', function () {
        $(this).siblings('.ttpc-manufacturer-wrp').slideToggle(200);
    });

    // Category filter
    $(document).on('change', '.ttpc-filter-wrp [name="itemsCategoryFilter"]', function () {
        var $parent = $(this).parents('.ttpc-prodselects-wrp:first');
        $parent.find('.ttpc-product-search:first').val('');

        ttpc_searchProducts($parent, null);
    });

    // Manufacturer filter
    $(document).on('change', '.ttpc-filter-wrp .ttpc-manufacturer-select', function () {
        var $parent = $(this).parents('.ttpc-prodselects-wrp:first');
        $parent.find('.ttpc-product-search:first').val('');

        ttpc_searchProducts($parent, null);
    });

    // Search combinations checkbox
    $(document).on('change', '.ttpc-search-combinations', function () {
        var $parent = $(this).parents('.ttpc-prodselects-wrp:first');
        var categories = ttpc_getChosenCategories($parent);
        var id_manufacturer = $parent.find('.ttpc-manufacturer-select').val();
        var query = $parent.find('.ttpc-product-search:first').val();

        if (query || categories.length || id_manufacturer) {
            ttpc_searchProducts($parent, query);
        }
    });

    // Select categories
    $(document).on('change', '.countdown-form [name="categories[]"]', function () {
        var $parent = $(this).parents('.countdown-form:first');
        var count = $('.countdown-form [name="categories[]"]:checked').length;
        $parent.find('.ttpc-number-categories').text(count);
    });

    // Swap add
    $(document).on('click', '.ttpc-add-swap', function (e) {
        e.preventDefault();

        var $parent = $(this).parents('.ttpc-swap-wrp:first');
        ttpc_swapAdd($parent);
    });
    $(document).on('dblclick', '.ttpc-swap-left', 'option', function(){
        var $parent = $(this).parents('.ttpc-swap-wrp:first');
        ttpc_swapAdd($parent);
    });

    // Swap remove
    $(document).on('click', '.ttpc-remove-swap', function (e) {
        e.preventDefault();

        var $parent = $(this).parents('.ttpc-swap-wrp:first');
        ttpc_swapRemove($parent);
    });
    $(document).on('dblclick', '.ttpc-swap-right', 'option', function(){
        var $parent = $(this).parents('.ttpc-swap-wrp:first');
        ttpc_swapRemove($parent);
    });
});

function ttpc_swapAdd($parent) {
    var $left = $parent.find('.ttpc-swap-left');
    var $right = $parent.find('.ttpc-swap-right');

    $left.find('option:selected').each(function () {
        $(this).detach().appendTo($right);
    });

    var count = $right.find('option').length;
    $parent.parents('.countdown-form:first').find('.ttpc-number-manufacturers').text(count);
}
function ttpc_swapRemove($parent) {
    var $left = $parent.find('.ttpc-swap-left');
    var $right = $parent.find('.ttpc-swap-right');

    $right.find('option:selected').each(function () {
        $(this).detach().appendTo($left);
    });

    var count = $right.find('option').length;
    $parent.parents('.countdown-form:first').find('.ttpc-number-manufacturers').text(count);
}

function ttpc_searchProducts($parent, query) {
    var categories = ttpc_getChosenCategories($parent);
    var id_manufacturer = $parent.find('.ttpc-manufacturer-select').val();
    var $select = $parent.find('.ttpc_prod_select');
    var $input = $parent.find('.ttpc-product-search:first');
    var search_combinations = +$parent.find('.ttpc-search-combinations:first').is(':checked');
    query = (query ? query : '');

    if (categories.length) {
        $('.ttpc-toggle-category-filter').addClass('chosen');
    } else {
        $('.ttpc-toggle-category-filter').removeClass('chosen');
    }
    if (id_manufacturer) {
        $('.ttpc-toggle-manufacturer-filter').addClass('chosen');
    } else {
        $('.ttpc-toggle-manufacturer-filter').removeClass('chosen');
    }

    $.ajax(ttpc_ajax_url, {
        data: {ajax: 1, action: 'getProducts', query: query, categories: categories, id_manufacturer: id_manufacturer, search_combinations: search_combinations},
        type: "POST",
        method: "POST",
        dataType: 'json',
        beforeSend: function () {
            $input.addClass('loading');
        },
        success: function (data) {
            var options_html = '';
            if (data) {
                var chosen_products = ttpc_getChosenProducts($parent);
                $.each(data, function (index, value) {
                    var added = (chosen_products.indexOf(value.id_product) !== -1);
                    options_html += '<option ' + (added ? 'disabled' : '') + ' value="' + value.id_product + '">' + value.name + '</option>';
                });
                $select.html(options_html);
            }
        },
        complete: function () {
            $input.removeClass('loading');
        }
    });
}

function ttpc_getChosenCategories($parent) {
    var categories = [];

    if (ttpc_psv === 1.5) {
        $parent.find('[name="itemsCategoryFilter"] :selected').each(function () {
            categories.push($(this).val());
        });
    } else {
        $parent.find('[name="itemsCategoryFilter"]:checked').each(function () {
            categories.push($(this).val());
        });
    }

    return categories;
}

function ttpc_addCountdownProducts($parent) {
    var $options = $parent.find('.ttpc_prod_select option:selected');

    var html = '';
    $options.each(function () {
        var id = $(this).val();
        var name = $(this).text();
        html += '<option value="' + id + '">' + name + '</option>';
        $(this).prop('disabled', true);
    });

    var $select = $parent.find('.ttpc_prod_selected');
    $select.append(html);
    ttpc_sortTagProductsTable($select);

    var count = $select.find('option').length;
    $parent.parents('.countdown-form:first').find('.ttpc-number-products').text(count);
}

function ttpc_removeCountdownProducts($parent) {
    var $options = $parent.find('.ttpc_prod_selected option:selected');

    $options.each(function () {
        var val = $(this).val();
        $(this).remove();
        $parent.find('.ttpc_prod_select option[value="' + val + '"]').prop('disabled', false)
    });

    var $select = $parent.find('.ttpc_prod_selected');
    var count = $select.find('option').length;
    $parent.parents('.countdown-form:first').find('.ttpc-number-products').text(count);
}

function ttpc_sortTagProductsTable($select) {
    var sorted = $select.find('option').sort(function(a, b) {
        return parseInt($(a).val()) - parseInt($(b).val());
    });
    $select.html(sorted);
}

function ttpc_getChosenProducts($parent) {
    var products = [];

    $parent.find('[name="products[]"] option').each(function() {
        products.push(parseInt($(this).val()));
    });

    return products;
}

function ttpc_initTypeWatch($elem) {
    if (!$elem) {
        $elem = $('.ttpc-product-search');
    }
    // Product search
    $elem.typeWatch({
        captureLength: 0,
        highlight: false,
        wait: 100,
        callback: function(text){
            var $this = $($(this)[0].el);
            var $parent = $this.parents('.ttpc-prodselects-wrp:first');

            ttpc_searchProducts($parent, text);
        }
    });
}
