<?php

namespace App\AST\Stmt;

use App\AST\Expr;
use App\AST\Stmt;
use App\Lexer\Token;

class ReturnStmt implements Stmt
{
    public function __construct(
        private Token $keyword,
        private ?Expr $value,
    ) {}

    public function accept(StmtVisitor $visitor): mixed
    {
        return $visitor->visitReturnStmt($this);
    }

    public function getKeyword(): Token
    {
        return $this->keyword;
    }

    public function getValue(): ?Expr
    {
        return $this->value;
    }
}