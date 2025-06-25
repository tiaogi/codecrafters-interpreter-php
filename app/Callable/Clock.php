<?php

namespace App\Callable;

use App\Interpreter;

class Clock implements LoxCallable
{
    public function __toString(): string
    {
        return "<native fn>";
    }

    public function __invoke(Interpreter $interpreter, array $arguments): float
    {
        return floor(microtime(true));
    }

    public function arity(): int
    {
        return 0;
    }
}