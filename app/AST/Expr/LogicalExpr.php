<?php

namespace App\AST\Expr;

use App\AST\Expr;
use App\Lexer\Token;

class LogicalExpr extends Expr
{
    public function __construct(
        private Expr $left,
        private Token $operator,
        private Expr $right
    ) {}

    public function accept(ExprVisitor $visitor): mixed
    {
        return $visitor->visitLogicalExpr($this);
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