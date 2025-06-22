<?php

namespace App\AST\Expr;

use App\AST\Visitor;

class Literal extends Expr
{
    public function __construct(
        private mixed $value
    ) {}

    public function accept(Visitor $visitor): mixed
    {
        return $visitor->visitLiteral($this);
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getDisplayableValue(): string
    {
        if (is_null($this->value)) return "nil";
        if (is_float($this->value)) return fmod($this->value, 1) === 0.0 ? sprintf('%.1f', $this->value) : (string) $this->value;
        if (is_bool($this->value)) return $this->value ? "true" : "false";

        return $this->value;
    }
}