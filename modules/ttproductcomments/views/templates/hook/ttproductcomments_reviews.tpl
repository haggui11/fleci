{*
*  @author    Templatetrip
*  @copyright 2015-2017 Templatetrip. All Rights Reserved.
*  @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*}
<div class="hook-reviews">
    <div class="comments_note" itemscope itemtype="https://schema.org/AggregateRating">
        <div class="star_content clearfix">
            {section name="i" start=0 loop=5 step=1}
                {if $averageTotal le $smarty.section.i.index}
                    <div class="star"></div>
                {else}
                    <div class="star star_on"></div>
                {/if}
            {/section}
            <meta content = "{if isset($ratings.avg)}{$ratings.avg|round:1|escape:'html':'UTF-8'}{else}{$averageTotal|round:1|escape:'html':'UTF-8'}{/if}" />
        </div>
		
		<meta content="{$product->name}" />
      <span class="reviewCount">{$nbComments}</span>
        {if isset($nbCommentsCounter) && $nbCommentsCounter}
            <span class="nb-comments"><span>{$nbComments}</span> {l s='Review(s)' mod='ttproductcomments'}</span>
        {/if}
    </div>
	</div>

