<?php

namespace GlotioPhpParser\Node\Scalar\MagicConst;

use GlotioPhpParser\Node\Scalar\MagicConst;

class Trait_ extends MagicConst
{
    public function getName() {
        return '__TRAIT__';
    }
}