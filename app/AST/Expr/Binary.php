<?php

namespace App\AST\Expr;

use App\Lexer\Token;
use App\AST\Visitor;

class Binary extends Expr
{
    public function __construct(
        private Expr $left,
        private Token $operator,
        private Expr $right
    ) {}

    public function accept(Visitor $visitor): string
    {
        return $visitor->visitBinary($this);
    }

    public function getLeft(): Expr
    {
        return $this->left;
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