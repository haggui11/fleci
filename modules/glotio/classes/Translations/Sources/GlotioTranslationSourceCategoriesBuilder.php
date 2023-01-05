<?php

class GlotioTranslationSourceCategoriesBuilder
{
    const CATEGORY_DB_TABLES  = 'db_tables';
    const CATEGORY_DB_CATALOG = 'db_catalog';
    const CATEGORY_DB_OTHERS  = 'db_others';
    const CATEGORY_MODULES    = 'modules';
    const CATEGORY_THEME_MODULES = 'theme_modules';
    const CATEGORY_THEMES     = 'themes';
    const CATEGORY_TEMPLATES  = 'templates';
    const CATEGORY_MAILS      = 'mails';
    const CATEGORY_MAILS_CORE = 'mails_core';

    /** @var string[] */
    private static $catalog_tables = array(
        'attachment',
        'attribute',
        'attribute_group',
        'category',
        'customization_field',
        'feature',
        'feature_value',
        'image',
        'manufacturer',
        'product',
        'supplier',
    );

    /** @var array */
    private $categories = array();

    /**
     * @return self
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @param string $table
     * @return self
     */
    public function dbCatalogOrOthers($table)
    {
        $this->push(self::CATEGORY_DB_TABLES);

        if (in_array($table, self::$catalog_tables)) {
            $this->push(self::CATEGORY_DB_CATALOG);
        } else {
            $this->push(self::CATEGORY_DB_OTHERS);
        }

        $this->push($table);
        return $this;
    }

    /**
     * @param string $table
     * @return self
     */
    public function db($table)
    {
        $this->push(self::CATEGORY_DB_TABLES);
        $this->push($table);
        return $this;
    }

    /**
     * @param string $module
     * @return self
     */
    public function module($module)
    {
        $this->push(self::CATEGORY_MODULES);
        $this->push($module);
        return $this;
    }

    /**
     * @param string $theme
     * @return self
     */
    public function theme($theme)
    {
        $this->push(self::CATEGORY_THEMES);
        $this->push($theme);
        return $this;
    }

    /**
     * @param string $theme
     * @param string $module
     * @return self
     */
    public function themeModule($theme, $module)
    {
        $this->theme($theme);

        $this->push(self::CATEGORY_THEME_MODULES);
        $this->push($module);
        return $this;
    }

    /**
     * @return self
     */
    public function mails()
    {
        $this->push(self::CATEGORY_MAILS);
        return $this;
    }

    /**
     * @return self
     */
    public function mailsCore()
    {
        $this->push(self::CATEGORY_MAILS);
        $this->push(self::CATEGORY_MAILS_CORE);
        return $this;
    }

    /**
     * @return self
     */
    public function templates()
    {
        $this->push(self::CATEGORY_TEMPLATES);
        return $this;
    }

    /**
     * @param string $category
     * @return self
     */
    public function push($category)
    {
        $this->categories[] = $category;
        return $this;
    }

    /**
     * @return string[]
     */
    public function build()
    {
        return $this->categories;
    }
}
