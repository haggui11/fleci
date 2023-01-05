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
    ttpc_updateDisplayCustomThemeOptions();
    ttpc_updateDisplayListPositionOptions();
    ttpc_afterEndChange();

    // Update options display
    $('[name=THEME]').on('change', function(){
        ttpc_updateDisplayCustomThemeOptions();
    });

    // Update options display
    $('[name=HIDE_AFTER_END]').on('change', function () {
        ttpc_afterEndChange();
    });

    // Update product list position options display
    $('#PRODUCT_LIST_POSITION').on('change', function () {
        ttpc_updateDisplayListPositionOptions();
    });

    // "More options" btn
    $('.ttpc-more-options').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var text = $(this).data('text');
        var alt_text = $(this).data('alt-text');
        var group = $(this).data('group');

        if (!$(this).hasClass('active')) {
            $(this).addClass('active');
            $(this).removeClass('btn-primary');
            $('.' + group).fadeIn(100);
            $(this).text(alt_text);

            ttpc_updateDisplayCustomThemeOptions();
            ttpc_afterEndChange();
        } else {
            $(this).removeClass('active');
            $(this).addClass('btn-primary');
            $('.' + group).fadeOut(100);
            $(this).text(text);
        }
    });

    // Toggle countdown additional options
    $(document).on('click', '.ttpc-toggle-options', function (e) {
        e.stopPropagation();
        e.preventDefault();

        var $parent = $(this).parents('.countdown-form');

        if ($(this).hasClass('expanded')) {
            $parent.find('.ttpc-options-row').fadeOut(300);
        } else {
            $parent.find('.ttpc-options-row').fadeIn(300);
        }

        $(this).find('.ttpc-toggle-sign').toggle();
        $(this).toggleClass('expanded');
    });

    // Slider inputs for font-size
    $(".ttpc-fz-slider").slider({
        step: 0.01,
        min: 0,
        max: 2,
        slide: function(event, ui) {
            var $parent = $(this).parents('.form-group:first');
            $parent.find('.ttpc-fz-input').val(ui.value);
            var val = parseInt(parseFloat(ui.value) * 100);
            $parent.find('.ttpc-br-text').text(val + '%');
        },
        create: function() {
            var val = $(this).parents('.form-group:first').find('.ttpc-fz-input').val();
            $(this).slider("value", val);
        }
    });

    if (ttpc_psv === 1.5) {
        $(document).on('mouseenter', '.label-tooltip', function () {
            var title = $(this).attr('title');
            $(this).data('title', title);
            $(this).append('<div class="tooltiptext">' + title + '</div>');
        });
        $(document).on('mouseleave', '.label-tooltip', function () {
            $(this).find('.tooltiptext').remove();
            var title = $(this).data('title');
            $(this).attr('title', title);
        });
    }
});

function ttpc_updateDisplayCustomThemeOptions() {
    var $theme_radio = $('[name=THEME]:checked');
    var theme_name = $theme_radio.data('theme');
    $('.ttpc_color_wrp').addClass('hidden');
    $('.color-theme-' + theme_name).removeClass('hidden');

    // display the promo side option
    var themes = {
        '7-minimal': '.ttpc_options_promo_side',
        '10-minimal-1': '.ttpc_options_promo_side',
        '11-minimal-2': '.ttpc_options_promo_side',
        '12-minimal-3': '.ttpc_options_promo_side'
    };
    if (themes[theme_name]) {
        var options_selector = themes[theme_name];
        $(options_selector).show();
    } else {
        $('.ttpc_options_promo_side').hide();
    }

    // if custom options block is visible
    if ($('.ttpc_more_app_options').hasClass('active')) {
        themes = {
            '1-simple': '.ttpc_options_highlight, .ttpc_options_compact, .ttpc_options_radius, .ttpc_options_bg, .ttpc_options_colon',
            '2-dark': '.ttpc_options_highlight, .ttpc_options_compact, .ttpc_options_radius, .ttpc_options_bg',
        };

        options_selector = themes[theme_name];
        $('.ttpc_custom_option').hide();
        $(options_selector).show();
    }
}

// indexOf for IE8 and below
if(!Array.prototype.indexOf) {
    Array.prototype.indexOf = function(what, i) {
        i = i || 0;
        var L = this.length;
        while (i < L) {
            if(this[i] === what) return i;
            ++i;
        }
        return -1;
    };
}

function ttpc_afterEndChange()
{
    if (ttpc_psv >= 1.6) {
        if ($('#HIDE_AFTER_END_on').prop('checked')) {
            $('#HIDE_EXPIRED_on').prop('checked', true);
            $('#HIDE_EXPIRED_on').prop('disabled', true);
            $('#HIDE_EXPIRED_off').prop('disabled', true);
        }
        else {
            $('#HIDE_EXPIRED_on').prop('disabled', false);
            $('#HIDE_EXPIRED_off').prop('disabled', false);
        }
    } else {
        if ($('#hide_after_end_on').prop('checked')) {
            $('#hide_expired_on').prop('checked', true);
            $('#hide_expired_on').prop('disabled', true);
            $('#hide_expired_off').prop('disabled', true);
        }
        else {
            $('#hide_expired_on').prop('disabled', false);
            $('#hide_expired_off').prop('disabled', false);
        }
    }
}

function ttpc_updateDisplayListPositionOptions() {
    var $pos_select = $('#PRODUCT_LIST_POSITION');
    var val = $pos_select.val();

    if (val === 'custom_over_img' || val === 'over_img') {
        $pos_select.parents('.ttpc-list-position-group:first').find('.ttpc-select-addon').fadeIn(150);
    } else {
        $pos_select.parents('.ttpc-list-position-group:first').find('.ttpc-select-addon').hide();
    }
}