<?php

namespace App\AST\Expr;

use App\AST\Visitor;

abstract class Expr
{
    abstract public function accept(Visitor $visitor): mixed;
}