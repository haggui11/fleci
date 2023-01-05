<?php

namespace GlotioPhpParser\ErrorHandler;

use GlotioPhpParser\Error;
use GlotioPhpParser\ErrorHandler;

/**
 * Error handler that handles all errors by throwing them.
 *
 * This is the default strategy used by all components.
 */
class Throwing implements ErrorHandler
{
    public function handleError(Error $error) {
        throw $error;
    }
}