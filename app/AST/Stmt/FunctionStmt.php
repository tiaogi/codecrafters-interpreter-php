<?php

namespace App\AST\Stmt;

use App\AST\Stmt;
use App\Lexer\Token;

class FunctionStmt implements Stmt
{
    public function __construct(
        private Token $name,
        private array $arguments,
        private array $body
    ) {}

    public function accept(StmtVisitor $visitor): mixed
    {
        return $visitor->visitFunctionStmt($this);
    }

    public function getName(): Token
    {
        return $this->name;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getBody(): array
    {
        return $this->body;
    }
}