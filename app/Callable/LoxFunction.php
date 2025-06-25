<?php

namespace App\Callable;

use App\AST\Stmt\FunctionStmt;
use App\Environment;
use App\Exception\ReturnException;
use App\Interpreter;
use App\Lexer\Token;

class LoxFunction implements LoxCallable
{
    public function __construct(
        private FunctionStmt $declaration,
        protected readonly Environment $closure
    ) {}

    public function __toString(): string
    {
        return "<fn ".$this->declaration->getName()->getLexeme().">";
    }

    public function __invoke(Interpreter $interpreter, array $arguments): mixed
    {
        $environment = new Environment($this->closure);

        for ($i = 0; $i < count($this->declaration->getArguments()); $i++) {
            /** @var Token $parameter */
            $parameter = $this->declaration->getArguments()[$i];
            $environment->define($parameter->getLexeme(), $arguments[$i]);
        }

        try {
            $interpreter->executeBlock($this->declaration->getBody(), $environment);
        } catch (ReturnException $e) {
            return $e->value;
        }
        return null;
    }

    public function arity(): int
    {
        return count($this->declaration->getArguments());
    }
}