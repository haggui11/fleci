{**
 * 2007-2021 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{* Javascript is used to display each product grade, this allows every rating to be udpated as soon as they change *}
<script type="text/javascript">
  document.addEventListener("DOMContentLoaded", function() {
    const $ = jQuery;
    const productId = {$product.id};
    const productReview = $('#product-list-reviews-{$product.id}');
	const productReviewc = $('#product-list-reviews-{$product.id} .comments-nb');
	const productReviewg = $('#product-list-reviews-{$product.id} .grade-stars');
    const productCommentGradeUrl = '{$product_comment_grade_url nofilter}';
    $.get(productCommentGradeUrl, { id_product: productId }, function(jsonResponse) {
      var jsonData = false;
      try {
        jsonData = JSON.parse(jsonResponse);
      } catch (e) {
      }

      if (jsonData) {
        if (jsonData.id_product && (jsonData.comments_nb != 0)) {
			$(productReviewc).html('('+jsonData.comments_nb+')');
		  $(productReviewg).rating({ grade: jsonData.average_grade, starWidth: 16 });		  
		  productReview.closest('.thumbnail-container').addClass('has-reviews');
		  productReview.css('visibility', 'visible');
        }
      }
    });
  });
</script>
{if $nb_comments != 0}
<div id="product-list-reviews-{$product.id}" class="product-list-reviews">
  <div class="grade-stars small-stars"></div>
  <div class="comments-nb"></div>
</div>
{/if}

{if $nb_comments != 0}
{* Rich snippet rating is displayed via php/smarty meaning it will be cached (for example on homepage) *}
<div itemtype="http://schema.org/AggregateRating" itemscope>
  <meta content="{$nb_comments}" />
  <meta content="{$average_grade}" />
</div>
{/if}
