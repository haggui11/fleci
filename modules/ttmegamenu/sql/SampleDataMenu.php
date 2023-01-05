<?php
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class SampleDataMenu
{
    public function initData()
    {
        $return = true;
        $languages = Language::getLanguages(true);
        $id_shop = Configuration::get('PS_SHOP_DEFAULT');

        $return &= Db::getInstance()->Execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ttmegamenu` (`id_ttmegamenu`, `type_link`, `dropdown`, `type_icon`, `icon`, `align_sub`, `width_sub`, `class`, `active`) VALUES 
		(2, 0, 0, 0, "", "tt-sub-left", "col-sm-12", "", 1),
		(3, 0, 0, 0, "", "tt-sub-left", "col-sm-12", "", 1),
		(4, 0, 0, 0, "", "tt-sub-left", "col-sm-12", "", 1),
		(5, 0, 0, 0, "", "tt-sub-right", "col-sm-12", "", 1),
		(6, 0, 0, 0, "", "tt-sub-right", "col-sm-12", "", 1),
		(7, 0, 0, 0, "", "tt-sub-auto", "col-sm-12", "", 1);');

        $return &= Db::getInstance()->Execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ttmegamenu_shop` (`id_ttmegamenu`, `id_shop`, `type_link`, `dropdown`, `type_icon`, `icon`, `align_sub`, `width_sub`, `class`, `active`) VALUES 
		(2, ' . $id_shop . ', 0, 0, 0, "", "tt-sub-left", "col-sm-12", "", 1),
		(3, ' . $id_shop . ', 0, 0, 0, "", "tt-sub-left", "col-sm-12", "", 1),
		(4, ' . $id_shop . ', 0, 0, 0, "", "tt-sub-left", "col-sm-12", "", 1),
		(5, ' . $id_shop . ', 0, 0, 0, "", "tt-sub-right", "col-sm-12", "", 1),
		(6, ' . $id_shop . ', 0, 0, 0, "", "tt-sub-right", "col-sm-12", "", 1),
		(7, ' . $id_shop . ', 0, 0, 0, "", "tt-sub-auto", "col-sm-12", "", 1);');

        $return &= Db::getInstance()->Execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ttmegamenu_row` (`id_row`, `id_ttmegamenu`, `class`, `active`) VALUES 
		(1,2,"five-column",1),
		(2,3,"four-column",1),
		(3,4,"ttproduct-block",1);');

        $return &= Db::getInstance()->Execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ttmegamenu_row_shop` (`id_row`, `id_ttmegamenu`, `id_shop`, `class`, `active`) VALUES 
		(1,2,' . $id_shop . ',"five-column",1),
		(2,3,' . $id_shop . ',"four-column",1),
		(3,4,' . $id_shop . ',"ttproduct-block",1);');

        $return &= Db::getInstance()->Execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ttmegamenu_column` (`id_column`, `id_row`, `width`, `class`, `active`) VALUES 
		(1, 1, "col-sm-3", "", 1),
		(2, 1, "col-sm-3", "", 1),
		(3, 1, "col-sm-3", "", 1),
		(4, 1, "col-sm-3", "", 1),
		(6, 2, "col-sm-3", "", 1),
		(7, 2, "col-sm-3", "", 1),
		(10, 2, "col-sm-3", "product-block", 1),
		(11, 3, "col-sm-3", "", 1),
		(12, 3, "col-sm-3", "", 1),
		(13, 3, "col-sm-3", "", 1),
		(14, 3, "col-sm-3", "", 1),
		(15, 3, "col-sm-3", "", 1),
		(16, 2, "col-sm-3", "", 1);');

        $return &= Db::getInstance()->Execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ttmegamenu_column_shop` (`id_column`, `id_row`, `id_shop`, `width`, `class`, `position` ,`active`) VALUES 
		(1, 1, ' . $id_shop . ', "col-sm-3", "", 0, 1),
		(2, 1, ' . $id_shop . ', "col-sm-3", "", 0, 1),
		(3, 1, ' . $id_shop . ', "col-sm-3", "", 0, 1),
		(4, 1, ' . $id_shop . ', "col-sm-3", "", 0, 1),
		(6, 2, ' . $id_shop . ', "col-sm-3", "", 0, 1),
		(7, 2, ' . $id_shop . ', "col-sm-3", "", 0, 1),
		(10, 2, ' . $id_shop . ', "col-sm-3", "product-block", 3, 1),
        (11, 3, ' . $id_shop . ', "col-sm-3", "", 0, 1),
        (12, 3, ' . $id_shop . ', "col-sm-3", "", 0, 1),
        (13, 3, ' . $id_shop . ', "col-sm-3", "", 0, 1),
		(14, 3, ' . $id_shop . ', "col-sm-3", "", 0, 1),
		(15, 3, ' . $id_shop . ', "col-sm-3", "", 0, 1),
		(16, 2, ' . $id_shop . ', "col-sm-3", "", 1, 1);');

        $return &= Db::getInstance()->Execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ttmegamenu_item` (`id_item`, `id_column`, `type_link`, `type_item`, `id_product`, `active`) VALUES 
		(1, 1, 1, "1", 0, 1),
		(9, 2, 1, "1", 0, 1),
		(10, 2, 1, "2", 0, 1),
		(17, 3, 1, "1", 0, 1),
		(25, 4, 1, "1", 0, 1),
		(41, 6, 1, "1", 0, 1),
		(44, 6, 1, "2", 0, 1),
		(45, 6, 1, "2", 0, 1),
		(46, 6, 1, "2", 0, 1),
		(47, 6, 1, "2", 0, 1),
		(49, 8, 4, "2", 5, 1),
		(52, 10, 4, "2", 6, 1),
		(53, 10, 4, "2", 18, 1),
		(54, 11, 4, "2", 7, 1),
		(55, 12, 4, "2", 10, 1),
		(56, 13, 4, "2", 18, 1),
		(57, 14, 4, "2", 12, 1),
		(58, 15, 4, "2", 16, 1),
		(59, 2, 1, "2", 0, 1),
		(60, 2, 1, "2", 0, 1),
		(61, 2, 1, "2", 0, 1),
		(62, 1, 1, "2", 0, 1),
		(63, 1, 1, "2", 0, 1),
		(65, 1, 1, "2", 0, 1),
		(66, 3, 1, "2", 0, 1),
		(67, 3, 1, "2", 0, 1),
		(68, 3, 1, "2", 0, 1),
		(69, 3, 1, "2", 0, 1),
		(70, 4, 1, "2", 0, 1),
		(71, 4, 1, "2", 0, 1),
		(72, 4, 1, "2", 0, 1),
		(73, 4, 1, "2", 0, 1),
		(79, 7, 3, "1", 0, 1),
		(80, 16, 1, "1", 0, 1),
		(81, 16, 1, "2", 0, 1),
		(82, 16, 1, "2", 0, 1),
		(83, 16, 1, "2", 0, 1),
		(84, 16, 1, "2", 0, 1),
		(85, 1, 1, "2", 0, 1),
		(86, 9, 3, "2", 0, 1);');

        $return &= Db::getInstance()->Execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ttmegamenu_item_shop` (`id_item`, `id_column`, `id_shop`, `type_link`, `type_item`, `id_product`, `active`) VALUES 
		(1,1,' . $id_shop . ',1,1,0,1),
		(9,2,' . $id_shop . ',1,1,0,1),
		(10,2,' . $id_shop . ',1,2,0,1),
		(17,3,' . $id_shop . ',1,1,0,1),
		(25,4,' . $id_shop . ',1,1,0,1),
		(41,6,' . $id_shop . ',1,1,0,1),
		(44,6,' . $id_shop . ',1,2,0,1),
		(45,6,' . $id_shop . ',1,2,0,1),
		(46,6,' . $id_shop . ',1,2,0,1),
		(47,6,' . $id_shop . ',1,2,0,1),
		(49,8,' . $id_shop . ',4,2,5,1),
		(52,10,' . $id_shop . ',4,2,6,1),
		(53,10,' . $id_shop . ',4,2,18,1),
		(54,11,' . $id_shop . ',4,2,7,1),
		(55,12,' . $id_shop . ',4,2,10,1),
		(56,13,' . $id_shop . ',4,2,18,1),
		(57,14,' . $id_shop . ',4,2,12,1),
		(58,15,' . $id_shop . ',4,2,16,1),
		(59,2,' . $id_shop . ',1,2,0,1),
		(60,2,' . $id_shop . ',1,2,0,1),
		(61,2,' . $id_shop . ',1,2,0,1),
		(62,1,' . $id_shop . ',1,2,0,1),
		(63,1,' . $id_shop . ',1,2,0,1),
		(64,1,' . $id_shop . ',1,1,0,1),
		(65,1,' . $id_shop . ',1,2,0,1),
		(66,3,' . $id_shop . ',1,2,0,1),
		(67,3,' . $id_shop . ',1,2,0,1),
		(68,3,' . $id_shop . ',1,2,0,1),
		(69,3,' . $id_shop . ',1,2,0,1),
		(70,4,' . $id_shop . ',1,2,0,1),
		(71,4,' . $id_shop . ',1,2,0,1),
		(72,4,' . $id_shop . ',1,2,0,1),
		(73,4,' . $id_shop . ',1,2,0,1),
		(79,7,' . $id_shop . ',3,1,0,1),
		(80,16,' . $id_shop . ',1,1,0,1),
		(81,16,' . $id_shop . ',1,2,0,1),
		(82,16,' . $id_shop . ',1,2,0,1),
		(83,16,' . $id_shop . ',1,2,0,1),
		(84,16,' . $id_shop . ',1,2,0,1),
		(85,1,' . $id_shop . ',1,2,0,1),
		(86,9,' . $id_shop . ',3,1,0,1);');

        foreach ($languages as $language) {
            $return &= Db::getInstance()->Execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ttmegamenu_lang` (`id_ttmegamenu`, `id_shop`, `id_lang`, `title`, `link`, `subtitle`) VALUES 
			(2,' . $id_shop . ',' . $language['id_lang'] . ',"CAT3","CAT3","Sale"),
			(3,' . $id_shop . ',' . $language['id_lang'] . ',"CAT6","CAT6","New"),
			(4,' . $id_shop . ',' . $language['id_lang'] . ',"CAT9","CAT9",""),
			(5,' . $id_shop . ',' . $language['id_lang'] . ',"PAGmy-account","PAGmy-account",""),
			(6,' . $id_shop . ',' . $language['id_lang'] . ',"PAGsitemap","PAGsitemap",""),
			(7,' . $id_shop . ',' . $language['id_lang'] . ',"PAGcontact","PAGcontact","");');

            $return &= Db::getInstance()->Execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ttmegamenu_item_lang` (`id_item`, `id_shop`, `id_lang`, `title`, `link`, `text`) VALUES 
			(1,' . $id_shop . ',' . $language['id_lang'] . ',"CAT3","CAT3",""),
			(9,' . $id_shop . ',' . $language['id_lang'] . ',"CAT7","CAT7",""),
			(10,' . $id_shop . ',' . $language['id_lang'] . ',"CAT9","CAT9",""),
			(17,' . $id_shop . ',' . $language['id_lang'] . ',"CAT8","CAT8",""),
			(25,' . $id_shop . ',' . $language['id_lang'] . ',"CAT33","CAT33",""),
			(41,' . $id_shop . ',' . $language['id_lang'] . ',"CMS4","CMS4",""),
			(44,' . $id_shop . ',' . $language['id_lang'] . ',"PAGcontact","PAGcontact",""),
			(45,' . $id_shop . ',' . $language['id_lang'] . ',"PAGbest-sales","PAGbest-sales",""),
			(46,' . $id_shop . ',' . $language['id_lang'] . ',"PAGnew-products","PAGnew-products",""),
			(47,' . $id_shop . ',' . $language['id_lang'] . ',"PAGprices-drop","PAGprices-drop",""),
			(48,' . $id_shop . ',' . $language['id_lang'] . ',"PAGsitemap","PAGsitemap",""),
			(49,' . $id_shop . ',' . $language['id_lang'] . ',"","#",""),
			(52,' . $id_shop . ',' . $language['id_lang'] . ',"","#",""),
			(53,' . $id_shop . ',' . $language['id_lang'] . ',"","#",""),
			(54,' . $id_shop . ',' . $language['id_lang'] . ',"","#",""),
			(55,' . $id_shop . ',' . $language['id_lang'] . ',"","#",""),
			(56,' . $id_shop . ',' . $language['id_lang'] . ',"","#",""),
			(57,' . $id_shop . ',' . $language['id_lang'] . ',"","#",""),
			(58,' . $id_shop . ',' . $language['id_lang'] . ',"","#",""),
			(59,' . $id_shop . ',' . $language['id_lang'] . ',"CAT27","CAT27",""),
			(60,' . $id_shop . ',' . $language['id_lang'] . ',"CAT28","CAT28",""),
			(61,' . $id_shop . ',' . $language['id_lang'] . ',"CAT29","CAT29",""),
			(62,' . $id_shop . ',' . $language['id_lang'] . ',"CAT4","CAT4",""),
			(63,' . $id_shop . ',' . $language['id_lang'] . ',"CAT21","CAT21",""),
			(65,' . $id_shop . ',' . $language['id_lang'] . ',"CAT23","CAT23",""),
			(66,' . $id_shop . ',' . $language['id_lang'] . ',"CAT30","CAT30",""),
			(67,' . $id_shop . ',' . $language['id_lang'] . ',"CAT31","CAT31",""),
			(68,' . $id_shop . ',' . $language['id_lang'] . ',"CAT32","CAT32",""),
			(69,' . $id_shop . ',' . $language['id_lang'] . ',"CAT9","CAT9",""),
			(70,' . $id_shop . ',' . $language['id_lang'] . ',"CAT34","CAT34",""),
			(71,' . $id_shop . ',' . $language['id_lang'] . ',"CAT35","CAT35",""),
			(72,' . $id_shop . ',' . $language['id_lang'] . ',"CAT36","CAT36",""),
			(73,' . $id_shop . ',' . $language['id_lang'] . ',"CAT37","CAT37",""),
			(79,' . $id_shop . ',' . $language['id_lang'] . ',"","#",""),
			(80,' . $id_shop . ',' . $language['id_lang'] . ',"CAT13","CAT13",""),
			(81,' . $id_shop . ',' . $language['id_lang'] . ',"CAT15","CAT15",""),
			(82,' . $id_shop . ',' . $language['id_lang'] . ',"CAT33","CAT33",""),
			(83,' . $id_shop . ',' . $language['id_lang'] . ',"CAT19","CAT19",""),
			(84,' . $id_shop . ',' . $language['id_lang'] . ',"CAT37","CAT37",""),
			(85,' . $id_shop . ',' . $language['id_lang'] . ',"CAT32","CAT32",""),
			(86,' . $id_shop . ',' . $language['id_lang'] . ',"","#","");');
        }
        return $return;
    }
}
