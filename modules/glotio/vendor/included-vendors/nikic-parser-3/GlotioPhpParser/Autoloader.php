<?php

namespace GlotioPhpParser;

/**
 * @codeCoverageIgnore
 */
class Autoloader
{
    /** @var bool Whether the autoloader has been registered. */
    private static $registered = false;

    /**
     * Registers GlotioPhpParser\Autoloader as an SPL autoloader.
     *
     * @param bool $prepend Whether to prepend the autoloader instead of appending
     */
    static public function register($prepend = false) {
        if (self::$registered === true) {
            return;
        }

        spl_autoload_register(array(__CLASS__, 'autoload'), true, $prepend);
        self::$registered = true;
    }

    /**
     * Handles autoloading of classes.
     *
     * @param string $class A class name.
     */
    static public function autoload($class) {
        if (0 === strpos($class, 'GlotioPhpParser\\')) {
            $fileName = __DIR__ . strtr(substr($class, 15), '\\', '/') . '.php';
            if (file_exists($fileName)) {
                require $fileName;
            }
        }
    }
}
