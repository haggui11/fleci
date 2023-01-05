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

if (typeof updateDisplay === 'function') {
    var updateDisplay_ttpc_original = updateDisplay;
    updateDisplay = function () {
        updateDisplay_ttpc_original();
        ttpc_refreshProductTimers();
    }
} else {
    $('.ttpc-combi-wrp:first').removeClass('ttpc-cw-hide').fadeIn(100);
}

$(function () {
    ttpc_initCountdown();

    $(document).on('click', '#grid', function(e){
        e.preventDefault();
        ttpc_initCountdown('.ttproductcountdown');
    });

    $(document).on('click', '#list', function(e){
        e.preventDefault();
        ttpc_initCountdown('.ttproductcountdown');
    });
});

function ttpc_initCountdown(selector) {
    selector = (selector ? selector : '.ttpc-inactive');
    $(selector).each(function(){
        var $ttpc = $(this);
        var $ttpc_container = $(this).parent('.ttpc-wrp');
        $ttpc_container = ($ttpc_container.length ? $ttpc_container : $ttpc);

        // get "to" date
        var to = $ttpc.data('to');
        if (typeof to === 'undefined' || !to) {
            return true;
        }
        // Check if "to" is a number or a string
        to = (isNaN(to) ? dateStringToTimestamp(to) : parseInt(to));

        $ttpc.addClass('ttproductcountdown').removeClass('ttpc-inactive');
        var $ttpc_main = $ttpc.find('.ttpc-main').clone();
        $ttpc.html('');
        if ($ttpc_main.length) {
            $ttpc_main.appendTo($ttpc);
        } else {
            $ttpc.append('<div class="ttpc-main" />');
        }
        $ttpc_main = $ttpc.find('.ttpc-main');

        var now = +new Date();
        if (!to || (to < now && (ttpc_hide_expired || ttpc_hide_after_end))) {
            $ttpc.hide();
            return true;
        }

        // adjust countdown position at the page
        if (ttpc_adjust_positions) {
            var $parent = $ttpc_container.parents('.product-grid a:first');
            if (ttpc_position_product === 'displayProductPriceBlock' && $parent.length) {
                $ttpc_container.detach().appendTo($parent);
            } else if (ttpc_position_list === 'over_img') {
                var $img = $ttpc_container.parents('.product-grid a:first').find('.ttproducthover');
                $img = ($img.length ? $img : $ttpc_container.parents('.thumbnail-container:first').find('.ttproducthover'));
                if ($img.length) {
                    $ttpc_container.detach();
                    $img.after($ttpc_container);
                    $ttpc_container.parent().addClass('ttpc-parent');
                }
            }
        }



		if (ttpc_adjust_positions) {
            var $parent = $ttpc_container.parents('.ttbestseller-products:first');
            if (ttpc_position_product === 'displayProductPriceBlock' && $parent.length) {
                $ttpc_container.detach().appendTo($parent);
            } else if (ttpc_position_list === 'over_img') {
                var $img = $ttpc_container.parents('.ttbestseller-products:first').find('.ttqtyprogress');
                $img = ($img.length ? $img : $ttpc_container.parents('.ajax_block_product:first').find('.ttqtyprogress'));
                if ($img.length) {
                    $ttpc_container.detach();
                    $img.after($ttpc_container);
                    $ttpc_container.parent().addClass('ttpc-parent');
                }
            }
        }
		
		
		
        if (typeof ttpc_callbackBeforeDisplay === 'function') {
            ttpc_callbackBeforeDisplay($ttpc, $ttpc_container);
        }

        var tpl = ttpc_countdown_tpl;
        var labels = ttpc_labels,
            template = _.template(tpl);
        var currDate = '00:00:00:00';
        var nextDate = '00:00:00:00';

        // Build the layout
        var initData = ttpc_strfobj(currDate);
        $ttpc_main.find('.ttpc-time').remove();
        labels.forEach(function(label, i) {
            $ttpc_main.append(template({
                curr: initData[label],
                next: initData[label],
                label: label,
                label_lang: ttpc_labels_lang[label]
            }));
        });
        // Start the countdown
        $ttpc_main.ttpccountdown(to, function(event) {
            var now = + new Date();
            var from = $ttpc.data('from');

            if (to < now && ttpc_hide_after_end) {
                $ttpc.hide(400);
            } else if (from) {
                from = dateStringToTimestamp(from);
                if (now < from) {
                    $ttpc.hide();
                } else {
                    $ttpc.show(300);
                }
            }

            var data;
            var newDate = event.strftime('%D:%H:%M:%S');

            if (newDate !== nextDate) {
                currDate = nextDate;
                nextDate = newDate;
                // Setup the data
                data = {
                    'curr': ttpc_strfobj(currDate),
                    'next': ttpc_strfobj(nextDate)
                };
                // Apply the new values to each node that changed
                ttpc_diff(data.curr, data.next).forEach(function(label) {
                    var selector = '.%s'.replace(/%s/, label),
                        $node = $ttpc_main.find(selector);
                    // Update the node
                    $node.removeClass('flip hidden');
                    $node.find('.ttpc-curr').text(data.curr[label]);
                    $node.find('.ttpc-next').text(data.next[label]);
                    // Wait for a repaint to then flip
                    _.delay(function($node) {
                        $node.addClass('flip');
                    }, 50, $node);
                });
            }
        });
    });
}
if (typeof initCountdown === 'undefined') {
    var initCountdown = ttpc_initCountdown;
}

// Parse countdown string to an object
function ttpc_strfobj(str) {
    var pieces = str.split(':');
    var obj = {};
    ttpc_labels.forEach(function(label, i) {
        obj[label] = pieces[i]
    });
    return obj;
}

// Return the time components that diffs
function ttpc_diff(obj1, obj2) {
    var diff = [];
    ttpc_labels.forEach(function(key) {
        if (obj1[key] !== obj2[key]) {
            diff.push(key);
        }
    });
    return diff;
}

function dateStringToTimestamp(dateString) {
    var dateTimeParts = dateString.split(' '),
        timeParts = dateTimeParts[1].split(':'),
        dateParts = dateTimeParts[0].split('-'),
        date;

    date = new Date(dateParts[0], parseInt(dateParts[1], 10) - 1, dateParts[2], timeParts[0], timeParts[1]);

    return date.getTime();
}

function ttpc_refreshProductTimers() {
    var id_pa = $('#idCombination').val();
    $('.ttpc-combi-wrp').hide().addClass('ttpc-cw-hide');
    if (id_pa) {
        $('.ttpc-cw-' + id_pa).removeClass('ttpc-cw-hide').fadeIn(100);
    } else {
        $('.ttpc-combi-wrp:first').removeClass('ttpc-cw-hide').fadeIn(100);
    }
}

var ttpc_countdown_tpl = '' +
    '<div class="ttpc-time <%= label %> <%= label == ttpc_highlight ? \'ttpc-highlight\' : \'\' %>">' +
        '<span class="ttpc-count ttpc-curr ttpc-top"><%= curr %></span>' +
        '<span class="ttpc-count ttpc-next ttpc-top"><%= next %></span>' +
        '<span class="ttpc-count ttpc-next ttpc-bottom"><%= next %></span>' +
        '<span class="ttpc-count ttpc-curr ttpc-bottom"><%= curr %></span>' +
        '<span class="ttpc-label"><%= label_lang %></span>' +
    '</div>';