/**
* 2015 VR pay eCommerce
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
*
*  @author VR pay eCommerce <info@vr-epay.info>
*  @copyright  2015 VR pay eCommerce
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

$(document).ready(function () {
    var logos = '';
    var ccBrand = $("#cc_brand_0").val();
    if (ccBrand) {
        for (i = 1; i <= 5; i++) {
            var logo = $("#cc_brand_"+i).val();
            if (logo) {
                logos += '<img src="'+logo+'">';
            }
        }
        $("img[src$='"+ccBrand+"']").after(logos);
    }
});










