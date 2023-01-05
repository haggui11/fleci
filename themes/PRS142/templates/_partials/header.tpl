{**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
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
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2021 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{block name='header_banner'}
  <div class="header-banner">
    {hook h='displayBanner'}
  </div>
{/block}
{block name='header_nav'}
  <nav class="header-nav">
          <div class="hidden-sm-down top-nav">
            <div class="left-nav">
			   <div class="container">
        			<div class="row">
              {hook h='displayNav1'}
			   {hook h='displayTtCompareHeader'}
               {hook h='displayTtWishlistHeader'}
            </div>
			 </div>
       </div>
            <div class="right-nav">
			<div class="container">
        			<div class="row">
					<div class="col-md-2 hidden-sm-down" id="_desktop_logo">
					{if $shop.logo_details}
						{if $page.page_name == 'index'}
							<h1>
								{renderLogo}
							</h1>
						{else}
								{renderLogo}
						{/if}
					{/if}
					</div>
                {hook h='displayNav2'}
            </div>
			 </div>
			 </div>
          </div>
  </nav>
{/block}

{block name='header_top'}
  <div class="header-top">
		<div class="position-static">
	<div class="container">
	  <div class="row">
		{hook h='displayTop'}
		<div class="hidden-md-up text-sm-center mobile">
	 <div class="top-logo" id="_mobile_logo"></div>
	 <div class="full-menu">
		<div class="floatxs-left title-menu-mobile" id="menu-icon">
              <i class="material-icons">&#xE5D2;</i>
		</div>
            <div class="float-xs-right" id="_mobile_cart"></div>
            <div class="float-xs-right" id="_mobile_user_info"></div>
            <div class="clearfix"></div>
          </div>
		  </div>
		  <div id="mobile_top_menu_wrapper title-menu-mobile" class="row hidden-md-up">
        <div class="js-top-menu mobile" id="_mobile_top_menu"></div>
        <div class="js-top-menu-bottom">
          <div id="_mobile_currency_selector title-menu-mobile"></div>   
          <div id="_mobile_language_selector title-menu-mobile"></div>
          <div id="_mobile_contact_link title-menu-mobile"></div>
        </div>
  </div>
		<div class="clearfix"></div>
	  </div>
	  </div>
	</div>
  </div>
		
      
  {hook h='displayNavFullWidth'}
{/block}

