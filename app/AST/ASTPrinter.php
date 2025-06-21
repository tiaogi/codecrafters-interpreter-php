<?php

namespace App\AST;

use App\AST\Expr\Binary;
use App\AST\Expr\Expr;
use App\AST\Expr\Grouping;
use App\AST\Expr\Literal;
use App\AST\Expr\Unary;

class ASTPrinter implements Visitor
{
    public function print(Expr $expr): string
    {
        return $expr->accept($this);
    }

    public function visitBinary(Binary $expr): string
    {
        return $this->parenthesize($expr->getOperator()->getLexeme(), $expr->getLeft(), $expr->getRight());
    }

    public function visitGrouping(Grouping $expr): string
    {
        return $this->parenthesize("group", $expr->getExpression());
    }

    public function visitLiteral(Literal $expr): string
    {
        return $expr->getDisplayableValue();
    }

    public function visitUnary(Unary $expr): string
    {
        return $this->parenthesize($expr->getOperator()->getLexeme(), $expr->getRight());
    }

    private function parenthesize(string $name, Expr ...$exprs): string
    {
        $value = "($name";
        foreach ($exprs as $expr) {
            $value .= " " . $expr->accept($this);
        }
        $value .= ")";
        return $value;
    }
}