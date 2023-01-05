<?php

namespace GlotioPhpParser\Node\Scalar\MagicConst;

use GlotioPhpParser\Node\Scalar\MagicConst;

class Namespace_ extends MagicConst
{
    public function getName() {
        return '__NAMESPACE__';
    }
}