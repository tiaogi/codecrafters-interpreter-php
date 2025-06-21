<?php

namespace App;

use App\Enum\TokenType;

class Token
{
    public function __construct(
        private TokenType $type,
        private string $lexeme,
        private ?string $literal,
        private int $line,
    ) {}

    public function __toString()
    {
        return $this->type->name." ".$this->lexeme." ".(is_null($this->literal) ? "null" : $this->literal);
    }

    public function getType(): TokenType
    {
        return $this->type;
    }
}