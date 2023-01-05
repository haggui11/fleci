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

class GlotioMailTemplateTranslationSource extends GlotioAbstractTranslationSource
{
    /** @var string */
    protected $mail_templates_search_path;

    /** @var bool */
    protected $loaded = false;

    /** @var GlotioMailTemplate[][] */
    protected $mailTemplatesByName = array();

    /**
     * MailTemplateTranslationSource constructor.
     * @param string $mail_templates_search_path
     * @param string|null $theme
     * @param string|null $module
     */
    public function __construct($mail_templates_search_path, $theme = null, $module = null)
    {
        $this->mail_templates_search_path = $mail_templates_search_path;

        $this->theme  = $theme;
        $this->module = $module;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param string $subtype
     */
    public function setSubtype($subtype)
    {
        $this->subtype = $subtype;
    }

    public function getCategoriesTree()
    {
        if ($this->theme && $this->module) {
            // Theme module mails
            $categories = GlotioTranslationSourceCategoriesBuilder::create()
                ->themeModule($this->theme, $this->module)
                ->mails()
                ->build();
        } elseif ($this->theme && !$this->module) {
            // Theme mails
            $categories = GlotioTranslationSourceCategoriesBuilder::create()
                ->theme($this->theme)
                ->mails()
                ->build();
        } elseif (!$this->theme && $this->module) {
            // Theme module mails
            $categories = GlotioTranslationSourceCategoriesBuilder::create()
                ->module($this->module)
                ->mails()
                ->build();
        } else {
            // Core mails
            $categories = GlotioTranslationSourceCategoriesBuilder::create()
                ->mailsCore()
                ->build();
        }

        return $categories;
    }

    public function getFieldsDefinition()
    {
        $this->loadData();

        $num_chars        = 0;
        $default_id_lang  = (int) Configuration::get('PS_LANG_DEFAULT');
        $default_iso_lang = GlotioPsLanguages::idLangToIso($default_id_lang);

        foreach ($this->mailTemplatesByName as $mailTemplates) {
            foreach ($mailTemplates as $mailTemplate) {
                if ($default_iso_lang === $mailTemplate->iso_lang) {
                    $num_chars += Tools::strlen($mailTemplate->getContent());
                }
            }
        }

        return array(
            array(
                'field'     => '*',
                'type'      => 'string',
                'size'      => null,
                'html'      => true,
                'num_chars' => $num_chars,
            )
        );
    }

    public function isValid()
    {
        return is_dir($this->mail_templates_search_path);
    }

    public function getNumRecords()
    {
        $this->loadData();

        return count($this->mailTemplatesByName);
    }

    public function getRecords($page, $limit)
    {
        $this->loadData();

        $records = array();

        foreach ($this->mailTemplatesByName as $template_name => $mailTemplates) {
            $texts = array();

            foreach ($mailTemplates as $mailTemplate) {
                $iso = $mailTemplate->iso_lang;
                $id  = $mailTemplate->getTemplateId();

                $texts[$iso][$id] = $mailTemplate->getContent();
            }

            $meta_keys = array('template' => $template_name);
            $records[] = $this->createTranslationText($texts, compact('meta_keys'));
        }

        return $records;
    }

    private function loadData()
    {
        if ($this->loaded) {
            return;
        }

        if (!$this->isValid()) {
            throw new GlotioException("Mail directory [{$this->mail_templates_search_path}] doesn't exists");
        }

        $mailTemplateSearcher = new GlotioMailTemplateSearcher();

        foreach (GlotioPsLanguages::getIsos() as $iso_lang) {
            $search_path          = $this->mail_templates_search_path . $iso_lang . '/';
            $mailTemplatePaths    = $mailTemplateSearcher->find($search_path);

            foreach ($mailTemplatePaths as $mailTemplatePath) {
                $mailTemplate = GlotioMailTemplate::createWithFilePath($mailTemplatePath);
                $this->mailTemplatesByName[$mailTemplate->name][] = $mailTemplate;
            }
        }

        $this->loaded = true;
    }

    public function addRecords($records)
    {
        $results = array();

        foreach ($records as $record) {
            foreach ($record['texts'] as $iso_lang => $translated_fields) {
                $mail_path = $this->mail_templates_search_path . $iso_lang . '/';

                foreach ($translated_fields as $file_as_field => $email_content) {
                    if (!$email_content) {
                        throw new GlotioException("Empty email content received in {$iso_lang}.{$file_as_field}");
                    }

                    $filenameParts = explode('_', $file_as_field);
                    $extension     = array_pop($filenameParts);
                    $filename      = implode('_', $filenameParts);
                    $file_path     = $mail_path . $filename . '.' . $extension;

                    $mailTemplate = GlotioMailTemplate::createWithFilePath($file_path);
                    $mailTemplate->setContent($email_content);

                    // Info to generate result record
                    $fields = array($iso_lang => array_keys($translated_fields));

                    try {
                        GlotioMailTemplateFileWriter::saveMailTemplate($mailTemplate->file_path, $mailTemplate->content);
                        $result = self::createTranslationUploadResultSuccess($record['_id'], $fields);
                    } catch (GlotioException $ex) {
                        $result = self::createTranslationUploadResultError($record['_id'], $fields, $ex->getMessage());
                    }

                    $results[] = $result;
                }
            }
        }

        return $results;
    }
}
