<?php

namespace App\AST\Stmt;

use App\AST\Expr;
use App\AST\Stmt;
use App\Lexer\Token;

class WhileStmt implements Stmt
{
    public function __construct(
        private Expr $condition,
        private Stmt $body
    ) {}

    public function accept(StmtVisitor $visitor): mixed
    {
        return $visitor->visitWhileStmt($this);
    }

    public function getCondition(): Expr
    {
        return $this->condition;
    }

    public function getBody(): Stmt
    {
        return $this->body;
    }
}