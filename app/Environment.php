<?php

namespace App;

use App\Exception\RuntimeError;
use App\Lexer\Token;

class Environment
{
    private array $values = [];

    public function __construct(
        private ?Environment $enclosing = null
    ) {}

    public function define(string $name, mixed $value): void
    {
        $this->values[$name] = $value;
    }

    public function get(Token $name): mixed
    {
        if (isset($this->values[$name->getLexeme()])) {
            return $this->values[$name->getLexeme()];
        }

        if (!is_null($this->enclosing)) return $this->enclosing->get($name);

        throw new RuntimeError($name, "Undefined variable '".$name->getLexeme()."'.");
    }

    public function assign(Token $name, mixed $value): void
    {
        if (isset($this->values[$name->getLexeme()])) {
            $this->values[$name->getLexeme()] = $value;
            return;
        }

        if (!is_null($this->enclosing)) {
            $this->enclosing->assign($name, $value);
            return;
        }

        throw new RuntimeError($name, "Undefined variable '".$name->getLexeme()."'.");
    }
}