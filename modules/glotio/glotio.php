<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
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
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    require_once dirname(__FILE__) . '/vendor/autoload_52.php';
} else {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

/**
 * Translations module.
 */
class Glotio extends Module
{
    const AFFILIATE_ID = 'PRESTASHOP';

    /**
     * Constant to enable detailed error traces in API responses
     */
    const GLOTIO_DEBUG = false;

    /**
     * Url to load glotio web ui
     */
    const GLOTIO_URL = 'https://my.glotio.com';

    public function __construct()
    {
        $this->name = 'glotio';
        $this->tab = 'i18n_localization';
        $this->version = '3.6.9';
        $this->author = 'Sellboost Solutions';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.7.99');
        $this->bootstrap = true;
        $this->module_key = 'a699a0b2e2340e133f9d59927ecdb542';
        $this->controllers = array('api');

        parent::__construct();

        $this->displayName = $this->l('Glotio - Translate your Prestashop!');
        $this->description = $this->l('Translate your shop into multiple languages quickly and automatically. Boost your SEO and marketing instantly.');
    }

    public function install()
    {
        Db::getInstance()->update(Meta::$definition['table'], array('configurable' => false), "page LIKE 'module-glotio%'");

        return parent::install() && $this->registerHook('moduleRoutes');
    }

    public function hookModuleRoutes()
    {
        return array(
            'module-glotio-api' => array(
                'rule'       => 'glotio-api/v{version}/{resource}',
                'keywords'   => array(
                    'version'  => array('regexp' => '[0-9]+', 'param' => 'version'),
                    'resource' => array('regexp' => '[_a-zA-Z0-9\pL\pS-]+', 'param' => 'resource'),
                ),
                'controller' => 'api',
                'params'     => array(
                    'fc'     => 'module',
                    'module' => $this->name,
                )
            ),
        );
    }

    /**
     * Load module configuration page.
     * @return string
     */
    public function getContent()
    {
        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            return $this->displayWarning($this->l('You must select a shop first.'));
        }

        if (!Configuration::get('GLOTIO_API_KEY')) {
            Configuration::updateValue('GLOTIO_API_KEY', GlotioUtils::getRandomApiKey());
        }

        return $this->displayGlotioLoginOrRegister();
    }

    /**
     * @return string
     */
    private function displayGlotioLoginOrRegister()
    {
        if (!Module::isEnabled($this->name)) {
            return '';
        }

        $base_url   = self::GLOTIO_URL;
        $params     = $this->getParamsForGlotio();
        $iframe_url = $base_url . '?' . http_build_query($params);

        $this->context->smarty->assign(compact('iframe_url', 'params'));

        return $this->display(__FILE__, 'views/templates/admin/module_config.tpl');
    }

    /**
     * @return array
     */
    private function getParamsForGlotio()
    {
        $project_data = GlotioShopInfoHelper::getProjectData();

        return array(
            'user_lang'        => $this->context->language->iso_code,
            'platform'         => $project_data['platform'],
            'module_version'   => $project_data['module_version'],
            'name'             => $this->context->employee->firstname . ' ' . $this->context->employee->lastname,
            'email'            => $this->context->employee->email,
            'company_name'     => $project_data['name'],
            'project_name'     => $project_data['name'],
            'description'      => $project_data['description'],
            'verticals'        => $project_data['verticals'],
            'url'              => GlotioUtils::getBaseLink(),
            'endpoint'         => $project_data['endpoint'],
            'platform_version' => $project_data['platform_version'],
            'apikey'           => Configuration::get('GLOTIO_API_KEY'),
            'affiliate_id'     => self::AFFILIATE_ID,
            'timezone'         => Configuration::get('PS_TIMEZONE'),
        );
    }
}
