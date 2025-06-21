<?php

namespace App\AST;

use App\AST\Expr\Binary;
use App\AST\Expr\Grouping;
use App\AST\Expr\Literal;
use App\AST\Expr\Unary;

interface Visitor
{
    public function visitBinary(Binary $expr): string;
    public function visitGrouping(Grouping $expr): string;
    public function visitLiteral(Literal $expr): string;
    public function visitUnary(Unary $expr): string;
}