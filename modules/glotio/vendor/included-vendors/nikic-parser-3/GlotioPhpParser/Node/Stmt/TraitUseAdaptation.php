<?php

namespace GlotioPhpParser\Node\Stmt;

use GlotioPhpParser\Node;

abstract class TraitUseAdaptation extends Node\Stmt
{
    /** @var Node\Name Trait name */
    public $trait;
    /** @var string Method name */
    public $method;
}
