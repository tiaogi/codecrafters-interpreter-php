<?php

namespace App\Callable;

use App\Interpreter;

interface LoxCallable
{
    public function __toString(): string;
    public function __invoke(Interpreter $interpreter, array $arguments): mixed;
    public function arity(): int;
}