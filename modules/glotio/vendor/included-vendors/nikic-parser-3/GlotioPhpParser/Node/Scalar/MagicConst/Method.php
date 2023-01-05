<?php

namespace GlotioPhpParser\Node\Scalar\MagicConst;

use GlotioPhpParser\Node\Scalar\MagicConst;

class Method extends MagicConst
{
    public function getName() {
        return '__METHOD__';
    }
}