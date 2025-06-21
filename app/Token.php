<?php

namespace App;

use App\Enum\TokenType;

class Token
{
    public function __construct(
        private TokenType $type,
        private string $lexeme,
        private string|float|null $literal,
        private int $line,
    ) {}

    public function __toString()
    {
        return $this->type->name." ".$this->lexeme." ".$this->getLiteralForDisplay();
    }

    public function getType(): TokenType
    {
        return $this->type;
    }

    private function getLiteralForDisplay(): string
    {
        if (is_null($this->literal)) return "null";
        if (is_float($this->literal)) return fmod($this->literal, 1) === 0.0 ? sprintf('%.1f', $this->literal) : (string) $this->literal;

        return $this->literal;
    }
}