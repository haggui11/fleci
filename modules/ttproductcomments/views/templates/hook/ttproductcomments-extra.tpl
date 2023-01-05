{*
*  @author    Templatetrip
*  @copyright 2015-2017 Templatetrip. All Rights Reserved.
*  @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*}
{if (($nbComments == 0 && $too_early == false && ($logged || $allow_guests)) || ($nbComments != 0))}
    <div id="product_comments_block_extra" {if $nbComments != 0} itemscope itemtype="https://schema.org/AggregateRating"{/if}>
        {if $nbComments != 0}
            <div class="comments_note">
                <div class="star_content clearfix">
                    {section name="i" start=0 loop=5 step=1}
                        {if $averageTotal le $smarty.section.i.index}
                            <div class="star"></div>
                        {else}
                            <div class="star star_on"></div>
                        {/if}
                    {/section}
                    <meta content = "0" />
                    <meta content = "{if isset($ratings.avg)}{$ratings.avg|round:1|escape:'html':'UTF-8'}{else}{$averageTotal|round:1|escape:'html':'UTF-8'}{/if}" />
                    <meta content = "5" />
					<meta content="{$nbComments}" />
                </div>
            </div>
        {/if}
        <div class="comments_advices">
            {if $nbComments != 0}
                <a class="reviews" href="javascript:void(0);"><i class="material-icons comments" aria-hidden="true">&#xE253;</i> {l s='Read reviews' mod='ttproductcomments'} ({$nbComments})</a>
            {/if}
            {if ($too_early == false AND ($logged OR $allow_guests))}
                <a class="open-comment-form" href="javascript:void(0);"><i class="material-icons comments" aria-hidden="true">&#xE254;</i> {l s='Write your review' mod='ttproductcomments'}</a>
            {/if}
        </div>
    </div>
{/if}
<!--  /Module ProductComments -->