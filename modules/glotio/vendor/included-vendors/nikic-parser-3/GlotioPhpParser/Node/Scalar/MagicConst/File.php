<?php

namespace GlotioPhpParser\Node\Scalar\MagicConst;

use GlotioPhpParser\Node\Scalar\MagicConst;

class File extends MagicConst
{
    public function getName() {
        return '__FILE__';
    }
}