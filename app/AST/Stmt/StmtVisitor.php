<?php

namespace App\AST\Stmt;

interface StmtVisitor
{
    public function visitExpressionStmt(ExpressionStmt $stmt): void;
    public function visitPrintStmt(PrintStmt $stmt): void;
    public function visitVarStmt(VarStmt $stmt): void;
    public function visitBlockStmt(BlockStmt $stmt): void;
    public function visitIfStmt(IfStmt $stmt): void;
    public function visitWhileStmt(WhileStmt $stmt): void;
    public function visitFunctionStmt(FunctionStmt $stmt): void;
    public function visitReturnStmt(ReturnStmt $stmt): void;
}