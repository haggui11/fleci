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

class GlotioTranslationTextInfoCollection
{
    /** @var GlotioTranslationTextInfo[] Clave: hash del texto, Valor: objeto TranslationTextInfo */
    private $translations = array();

    /**
     * Crea una instancia con traducciones de textos.
     * @param array $translations Formato: clave translation_key, valor translated_text
     * @return GlotioTranslationTextInfoCollection
     */
    public static function createWithTranslatedTexts(array $translations)
    {
        $instance = new self();

        foreach ($translations as $translation_key => $translated_text) {
            $instance->addTranslatedText($translation_key, $translated_text);
        }

        return $instance;
    }

    /**
     * Añade el texto traducido de una traducción.
     * @param string $translation_key
     * @param string $translated_text
     */
    public function addTranslatedText($translation_key, $translated_text)
    {
        $this->addTranslationKey($translation_key);

        $this->translations[$translation_key]->translation = stripslashes(html_entity_decode($translated_text, ENT_COMPAT, 'UTF-8'));
    }

    /**
     * Añade el texto original de una traducción.
     * @param string $translation_key
     * @param string $original_text
     */
    public function addOriginalText($translation_key, $original_text)
    {
        $this->addTranslationKey($translation_key);

        $this->translations[$translation_key]->original = stripslashes(html_entity_decode($original_text, ENT_COMPAT, 'UTF-8'));
    }

    /**
     * Añade una clave de traducción, si no existe ya.
     * @param string $translation_key
     */
    private function addTranslationKey($translation_key)
    {
        if (!isset($this->translations[$translation_key])) {
            $this->translations[$translation_key] = new GlotioTranslationTextInfo($translation_key);
        }
    }

    /**
     * Devuelve la traducción con la clave pasada.
     * @param string $tranlation_key
     * @return GlotioTranslationTextInfo
     */
    public function getByTranslationKey($tranlation_key)
    {
        $return = null;

        if (isset($this->translations[$tranlation_key])) {
            $return = $this->translations[$tranlation_key];
        }

        return $return;
    }

    /**
     * Completa los valores vacíos en el campo "original" de esta colección con los valores de "original"
     * de otra colección. Para esto las claves de las traducciones deben tener el formato XXXX_md5(texto), ya que únicamente
     * se usa la parte de md5 como valor para unificar las colecciones.
     * @param GlotioTranslationTextInfoCollection $collection
     */
    public function fillOriginalTranslationValuesFromCollection(GlotioTranslationTextInfoCollection $collection)
    {
        // Indexar los valores de la colección pasada por su hash md5
        $indexCollectionByMd5HashText = array();
        foreach ($collection->translations as $translation) {
            $key = md5($translation->original);
            $indexCollectionByMd5HashText[$key] = $translation->original;
        }

        // Rellenar todos los valores de original vacíos de esta colleción
        foreach ($this->translations as $translation) {
            if ($translation->original) {
                continue;
            }

            $md5_hash = substr($translation->key, strrpos($translation->key, '_') + 1);
            if (isset($indexCollectionByMd5HashText[$md5_hash])) {
                $translation->original = $indexCollectionByMd5HashText[$md5_hash];
            }
        }
    }

    /**
     * @return GlotioTranslationTextInfo[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Devuelve un array simple con las traducciones.
     * @return array Formato: clave => hash de traducción, valor => texto traducido.
     */
    public function toSimpleTranslationsArray()
    {
        $return = array();

        foreach ($this->translations as $translation) {
            $return[$translation->key] = $translation->translation;
        }

        return $return;
    }
}
