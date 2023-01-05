<?php

namespace GlotioPhpParser\Node\Scalar\MagicConst;

use GlotioPhpParser\Node\Scalar\MagicConst;

class Function_ extends MagicConst
{
    public function getName() {
        return '__FUNCTION__';
    }
}