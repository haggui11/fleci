<?php

namespace GlotioPhpParser\Node\Stmt;

use GlotioPhpParser\Node;

/** Nop/empty statement (;). */
class Nop extends Node\Stmt
{
    public function getSubNodeNames() {
        return array();
    }
}
