<?php

namespace App\AST\Expr;

use App\Lexer\Token;
use App\AST\Visitor;

class Unary extends Expr
{
    public function __construct(
        private Token $operator,
        private Expr $right
    ) {}

    public function accept(Visitor $visitor): mixed
    {
        return $visitor->visitUnary($this);
    }

    public function getOperator(): Token
    {
        return $this->operator;
    }

    public function getRight(): Expr
    {
        return $this->right;
    }
}