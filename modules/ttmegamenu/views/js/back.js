/**
 * 2007-2021 PrestaShop
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
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2021 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
*/
$(document).ready(function()
{	  
	$("#type_link_custom").click(function()
	{
		$('.ps_link').css('display','none');
		$('.show_sub').css('display','none');
		$('.tt-menu-title').css('display','block');
		$('.tt-menu-link').css('display','block');
	});
	
	$("#type_link_ps").click(function()
	{
	  $('.ps_link').css('display','block');
	  $('.show_sub').css('display','block');
	  $('.tt-menu-title').css('display','none');
	  $('.tt-menu-link').css('display','none');
	});
	
	$("#type_icon_fw").click(function()
	{
	  $('.tt-img-icon').css('display','none');
	  $('.tt-fw-icon').css('display','block');	
	});
	
	$("#type_icon_img").click(function()
	{
	  $('.tt-img-icon').css('display','block');
	  $('.tt-fw-icon').css('display','none');	
	});
	
	$( "#type_link" ).change(function() {
		var type_val = $(this).val();
		if (type_val == 2)
		{
			$('.tt-menu-title').parent('.form-group').show();
			$('.tt-menu-link').parent('.form-group').show();
			$('.ps_link').parent('.form-group').hide();
			$('.tt-menu-text').parent('.form-group').hide();
			$('.tt-menu-product').parent('.form-group').hide();
			$('.ps_link').parent('.form-group').removeClass('hide-ps-link');
		}
		else if (type_val == 3)
		{
			$('.tt-menu-title').parent('.form-group').show();
			$('.tt-menu-link').parent('.form-group').show();
			$('.ps_link').parent('.form-group').hide();
			$('.tt-menu-text').parent('.form-group').show();
			$('.tt-menu-product').parent('.form-group').hide();
			$('.ps_link').parent('.form-group').removeClass('hide-ps-link');
		}
		else if (type_val == 4)
		{
			$('.tt-menu-product').parent('.form-group').show();
			$('.tt-menu-title').parent('.form-group').hide();
			$('.tt-menu-link').parent('.form-group').hide();
			$('.tt-menu-text').parent('.form-group').hide();
			$('.ps_link').parent('.form-group').hide();
			$('.ps_link').parent('.form-group').addClass('hide-ps-link');
		}
		else
		{
			$('.tt-menu-title').parent('.form-group').hide();
			$('.tt-menu-link').parent('.form-group').hide();
			$('.ps_link').parent('.form-group').show();
			$('.tt-menu-text').parent('.form-group').hide();
			$('.tt-menu-product').parent('.form-group').hide();
			$('.ps_link').parent('.form-group').removeClass('hide-ps-link');
		}
	});
	
});