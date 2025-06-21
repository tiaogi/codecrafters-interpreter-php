<?php

namespace App;

use App\Enum\TokenType;

class Token
{
    public function __construct(
        private TokenType $type,
        private String $lexeme,
        private int $line,
    ) {}

    public function __toString()
    {
        return $this->type->name." ".$this->lexeme." null";
    }

    public function getType(): TokenType
    {
        return $this->type;
    }
}