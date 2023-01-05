<?php

namespace GlotioPhpParser\Node\Scalar\MagicConst;

use GlotioPhpParser\Node\Scalar\MagicConst;

class Dir extends MagicConst
{
    public function getName() {
        return '__DIR__';
    }
}