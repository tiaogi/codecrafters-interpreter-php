<?php

namespace App\AST\Expr;

use App\AST\Visitor;

class Grouping extends Expr
{
    public function __construct(
        private Expr $expression
    ) {}

    public function accept(Visitor $visitor): string
    {
        return $visitor->visitGrouping($this);
    }

    public function getExpression(): Expr
    {
        return $this->expression;
    }
}