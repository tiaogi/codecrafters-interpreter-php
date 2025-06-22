<?php

namespace App\AST;

use App\AST\Expr\Binary;
use App\AST\Expr\Grouping;
use App\AST\Expr\Literal;
use App\AST\Expr\Unary;

interface Visitor
{
    public function visitBinary(Binary $expr): mixed;
    public function visitGrouping(Grouping $expr): mixed;
    public function visitLiteral(Literal $expr): mixed;
    public function visitUnary(Unary $expr): mixed;
}