<?php

namespace App\AST\Stmt;

use App\AST\Expr;
use App\AST\Stmt;

class IfStmt implements Stmt
{
    public function __construct(
        private Expr $condition,
        private Stmt $thenBranch,
        private ?Stmt $elseBranch,
    ) {}

    public function accept(StmtVisitor $visitor): mixed
    {
        return $visitor->visitIfStmt($this);
    }

    public function getCondition(): Expr
    {
        return $this->condition;
    }

    public function getThenBranch(): Stmt
    {
        return $this->thenBranch;
    }

    public function getElseBranch(): ?Stmt
    {
        return $this->elseBranch;
    }
}