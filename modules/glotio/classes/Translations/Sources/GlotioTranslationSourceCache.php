<?php

class GlotioTranslationSourceCache
{
    const CACHE_FILENAME  = 'translation_sources.cache.php';

    /** @var string */
    private $cache_directory;

    /** @var string */
    private $cache_file;

    /** @var array */
    private $cachedSources = array();

    /**
     * ModuleModelCache constructor.
     * @param string $cache_directory
     */
    public function __construct($cache_directory = null)
    {
        if (!$cache_directory) {
            $cache_directory = _PS_MODULE_DIR_ . 'glotio/cache/';
        }

        $this->cache_directory = rtrim($cache_directory, '/');
        $this->cache_file      = $this->cache_directory . '/' . self::CACHE_FILENAME;
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->cache_file);
    }

    /**
     * @param string $key
     * @param array $sourceDefinition
     */
    public function add($key, array $sourceDefinition)
    {
        $this->cachedSources[$key] = $sourceDefinition;
    }

    /**
     * @param string $key
     * @return array|null
     */
    public function get($key)
    {
        return isset($this->cachedSources[$key]) ? $this->cachedSources[$key] : null;
    }

    /**
     * @return bool
     */
    public function isObsolete()
    {
        if (!$this->exists()) {
            return false;
        }

        $created_date_cache_file = date("Ymd", filemtime($this->cache_file));

        // 1 day cache is valid
        return $created_date_cache_file < date("Ymd");
    }

    public function load()
    {
        if ($this->exists()) {
            $cachedSources = include $this->cache_file;
            if (!$cachedSources || !is_array($cachedSources)) {
                $cachedSources = array();
            }

            $this->cachedSources = $cachedSources;
        }
    }

    /**
     * @throws GlotioException
     */
    public function store()
    {
        if (!$this->cachedSources) {
            return;
        }

        $write_index = file_put_contents($this->cache_file, "<?php\n\nreturn " . var_export($this->cachedSources, true) . ";");
        if (!$write_index) {
            throw new GlotioException("Can't write to location [$this->cache_file]");
        }
    }

    /**
     * @return bool
     */
    public function reset()
    {
        $this->cachedSources = array();

        $cache_file = $this->cache_file;
        if (file_exists($cache_file)) {
            return @unlink($cache_file);
        } else {
            return false;
        }
    }
}
