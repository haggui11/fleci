<?php

namespace GlotioPhpParser\Node\Scalar\MagicConst;

use GlotioPhpParser\Node\Scalar\MagicConst;

class Line extends MagicConst
{
    public function getName() {
        return '__LINE__';
    }
}