<?php

namespace GlotioPhpParser\Node\Scalar\MagicConst;

use GlotioPhpParser\Node\Scalar\MagicConst;

class Class_ extends MagicConst
{
    public function getName() {
        return '__CLASS__';
    }
}