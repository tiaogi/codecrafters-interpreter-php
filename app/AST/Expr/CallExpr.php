<?php

namespace App\AST\Expr;

use App\AST\Expr;
use App\Lexer\Token;

class CallExpr extends Expr
{
    public function __construct(
        private Expr $callee,
        private Token $paren,
        private array $arguments
    ) {}

    public function accept(ExprVisitor $visitor): mixed
    {
        return $visitor->visitCallExpr($this);
    }

    public function getCallee(): Expr
    {
        return $this->callee;
    }

    public function getParen(): Token
    {
        return $this->paren;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}