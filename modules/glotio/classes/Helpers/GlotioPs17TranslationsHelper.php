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

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class GlotioPs17TranslationsHelper
{
    /**
     * Process submitted records to TranslationSource::addRecords for Prestashop 1.7 translation files.
     * @param array $records
     * @param array $originals
     * @param string|null $theme
     * @return array
     * @throws GlotioException
     */
    public static function addRecordsAsTranslations(array $records, array $originals, $theme = null)
    {
        // Group texts by _id and language
        $translationTextsMap = array();

        foreach ($records as $record) {
            if (!isset($record['_id'], $record['texts'])) {
                throw new GlotioException('Incorrect JSON format provided, should have [_id] and [texts] properties');
            }

            $_id   = $record['_id'];
            $texts = $record['texts'];

            foreach ($texts as $iso_lang => $translated_fields) {
                foreach ($translated_fields as $field => $value) {
                    // Parses strings like: <ModulesBannerShop>92fbf0e5d97b8afd7e73126b52bdc4bb
                    preg_match('/<(.+)>(.+)/i', $field, $matches);
                    if (count($matches) < 3) {
                        continue;
                    }

                    $domain = $matches[1];
                    $hash   = $matches[2];

                    $translationMessage = array(
                        'field'       => $field,
                        'original'    => isset($originals[$field]) ? $originals[$field] : null,
                        'translation' => $value,
                        'domain'      => $domain,
                        'theme'       => $theme,
                    );
                    $translationTextsMap[$_id][$iso_lang][$hash] = $translationMessage;
                }
            }
        }

        // Try to save texts to translation table
        $results = array();

        foreach ($translationTextsMap as $_id => $translationTextsByIso) {
            foreach ($translationTextsByIso as $iso_lang => $translationMessages) {
                $id_lang = GlotioPsLanguages::isoToIdLang($iso_lang);

                $errors = array();
                foreach ($translationMessages as $hash => $translationMessage) {
                    $domain      = $translationMessage['domain'];
                    $key         = htmlspecialchars_decode($translationMessage['original'], ENT_QUOTES);
                    $translation = $translationMessage['translation'];
                    $theme       = $translationMessage['theme'];

                    if (!$key) {
                        $errors[] = 'Cannot found original text. ' . json_encode($translationMessage);
                        continue;
                    }

                    // Get existing ID from translation table
                    $query = new DbQuery();
                    $query->select('`id_translation`')
                        ->from('translation')
                        ->where('`id_lang` = ' . (int)$id_lang)
                        ->where("`domain` = '" . pSQL($domain) . "'")
                        ->where("`key` = '" . pSQL($key) . "'")
                        ->where("`theme` " . (($theme) ? "= '" . pSQL($theme) . "'" : "IS NULL"))
                        ->orderBy('`id_translation` DESC');
                    $current_id = (int) Db::getInstance()->getValue($query);

                    // Insert or update existing value in translation table
                    $values = array(
                        'id_translation' => $current_id ? $current_id : null,
                        'id_lang'     => $id_lang,
                        'key'         => $key,
                        'translation' => $translation,
                        'domain'      => $domain,
                        'theme'       => $theme,
                    );

                    $query = new GlotioInsertQueryBuilder();
                    $query->setTable(_DB_PREFIX_ . 'translation')
                        ->setColumns(array('id_translation', 'id_lang', 'key', 'translation', 'domain', 'theme'))
                        ->setValues(array($values))
                        ->setUpdatefields(array('id_lang', 'key', 'translation', 'domain', 'theme'));

                    $query_result = Db::getInstance()->execute($query->build());
                    if (!$query_result) {
                        $msg = 'Error while saving translation. ' . Db::getInstance()->getMsgError();
                        $errors[md5($msg)] = $msg;
                    }
                }

                // Info to generate result record
                $fields = array($iso_lang => array_column($translationMessages, 'field'));

                if ($errors) {
                    $result = GlotioAbstractTranslationSource::createTranslationUploadResultError($_id, $fields, implode('|', array_values($errors)));
                } else {
                    $result = GlotioAbstractTranslationSource::createTranslationUploadResultSuccess($_id, $fields);
                }

                $results[] = $result;
            }
        }

        if ($results) {
            $kernel = GlotioPsKernel::getInstance();

            /** @var \PrestaShopBundle\Service\Cache\Refresh $cacheRefresh */
            $cacheRefresh = $kernel->getContainer()->get('prestashop.cache.refresh', ContainerInterface::NULL_ON_INVALID_REFERENCE);
            if ($cacheRefresh) {
                try {
                    $cacheRefresh->addCacheClear();
                    $cacheRefresh->execute();
                } catch (Exception $exception) {
                    // Ignore exception, should be a permissions issue
                }
            }
        }

        return $results;
    }

    /**
     * Retrieves theme translation texts in Prestsahop 1.7.
     * @param string $theme_name
     * @return array Format array(texts => array(), originals => array())
     * @throws GlotioException
     */
    public static function getThemeTranslations($theme_name)
    {
        $kernel = GlotioPsKernel::getInstance();

        /** @var \PrestaShopBundle\Service\TranslationService $translationService */
        $translationService = $kernel->getContainer()->get('prestashop.service.translation');

        $texts     = array();
        $originals = array();

        foreach (GlotioPsLanguages::getIsos() as $iso) {
            $locale = GlotioPsLanguages::isoToLocale($iso);

            try {
                $domains = $translationService->getTranslationsCatalogue($iso, 'themes', $theme_name);
            } catch (Exception $ex) {
                throw new GlotioException("Error while loading catalogue of theme {$theme_name} for locale {$locale}");
            }

            foreach (array_keys($domains) as $domain) {
                $domain = self::normalizeDomain($domain);

                try {
                    $domainTranslations = $translationService->listDomainTranslation($locale, $domain, $theme_name);
                } catch (Exception $ex) {
                    // continue loop if getting translations fails
                    continue;
                }

                $translationsData = self::extractTranslationsDataFromDomainTranslation($domainTranslations);
                if (!$translationsData) {
                    continue;
                }

                if (!isset($texts[$iso])) {
                    $texts[$iso] = array();
                }

                $texts[$iso] = array_merge($texts[$iso], $translationsData['translations']);
                $originals = array_merge($originals, $translationsData['defaults']);
            }
        }

        return compact('texts', 'originals');
    }

    /**
     * Retrieves module translation texts using the new translation system for Prestashop 1.7.
     * @param string $module_name
     * @return array Format array(texts => array(), originals => array())
     * @throws GlotioException
     */
    public static function getModuleTranslations($module_name)
    {
        $kernel = GlotioPsKernel::getInstance();

        /** @var \PrestaShopBundle\Service\TranslationService $translationService */
        $translationService = $kernel->getContainer()->get('prestashop.service.translation');

        /** @var \PrestaShopBundle\Translation\Provider\ExternalModuleLegacySystemProvider $moduleProvider */
        $moduleProvider = $kernel->getContainer()->get('prestashop.translation.external_module_provider', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        if (!$moduleProvider) {
            /** @var \PrestaShopBundle\Translation\Provider\ModuleProvider $moduleProvider */
            $moduleProvider = $kernel->getContainer()->get('prestashop.translation.module_provider');
        }
        $moduleProvider->setModuleName($module_name);

        $texts     = array();
        $originals = array();

        foreach (GlotioPsLanguages::getIsos() as $iso) {
            $locale  = GlotioPsLanguages::isoToLocale($iso);
            $domains = $moduleProvider->setLocale($locale)->getDefaultCatalogue()->getDomains();

            foreach ($domains as $domain) {
                $domain = self::normalizeDomain($domain);

                try {
                    $domainTranslations = $translationService->listDomainTranslation($locale, $domain);
                } catch (Exception $ex) {
                    // continue loop if getting translations fails
                    continue;
                }

                $translationsData   = self::extractTranslationsDataFromDomainTranslation($domainTranslations);
                if (!$translationsData) {
                    continue;
                }

                if (!isset($texts[$iso])) {
                    $texts[$iso] = array();
                }

                $texts[$iso] = array_merge($texts[$iso], $translationsData['translations']);
                $originals   = array_merge($originals, $translationsData['defaults']);
            }
        }

        return compact('texts', 'originals');
    }

    /**
     * @param array array $domainTranslations
     * @return array|null Format array(translations => array(), defaults => array())
     */
    private static function extractTranslationsDataFromDomainTranslation(array $domainTranslations)
    {
        if (!isset($domainTranslations['data'])) {
            return null;
        }

        $translations = array();
        $defaults     = array();

        foreach ($domainTranslations['data'] as $domainTranslation) {
            $domain = implode('', $domainTranslation['tree_domain']);

            $original    = $domainTranslation['default'];
            $translation = ($domainTranslation['database']) ? $domainTranslation['database'] : $domainTranslation['xliff'];
            $key         = self::buildTextKey($domain, $original);

            $translations[$key] = $translation;
            $defaults[$key]     = $original;
        }

        return compact('translations', 'defaults');
    }

    /**
     * @param string $domain
     * @param string $text
     * @return string
     */
    private static function buildTextKey($domain, $text)
    {
        return "<$domain>" . md5($text);
    }

    /**
     * Fix domains ending with locale like .es-ES in oldest 1.7 versions.
     * @param $domain
     * @return string
     */
    private static function normalizeDomain($domain)
    {
        $parts = explode('.', $domain);
        return reset($parts);
    }
}
