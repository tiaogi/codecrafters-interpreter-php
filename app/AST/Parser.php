<?php

namespace App\AST;

use App\AST\Expr\Binary;
use App\AST\Expr\Expr;
use App\AST\Expr\Grouping;
use App\AST\Expr\Literal;
use App\AST\Expr\Unary;
use App\Exception\ParseError;
use App\Lexer\Enum\TokenType;
use App\Lexer\Token;
use App\Lox;

class Parser
{
    private int $current = 0;

    public function __construct(
        private array $tokens
    ) {}

    public function parse(): ?Expr
    {
        try {
            return $this->expression();
        } catch (ParseError $e) {
            return null;
        }
    }

    private function expression(): Expr
    {
        return $this->equality();
    }

    private function equality(): Expr
    {
        $expr = $this->comparison();

        while ($this->match(TokenType::BANG_EQUAL, TokenType::EQUAL_EQUAL)) {
            $operator = $this->previous();
            $right = $this->comparison();
            $expr = new Binary($expr, $operator, $right);
        }

        return $expr;
    }

    private function comparison(): Expr
    {
        $expr = $this->term();

        while ($this->match(TokenType::GREATER, TokenType::GREATER_EQUAL, TokenType::LESS, TokenType::LESS_EQUAL)) {
            $operator = $this->previous();
            $right = $this->term();
            $expr = new Binary($expr, $operator, $right);
        }

        return $expr;
    }

    private function term(): Expr
    {
        $expr = $this->factor();

        while ($this->match(TokenType::MINUS, TokenType::PLUS)) {
            $operator = $this->previous();
            $right = $this->factor();
            $expr = new Binary($expr, $operator, $right);
        }

        return $expr;
    }

    private function factor(): Expr
    {
        $expr = $this->unary();

        while ($this->match(TokenType::SLASH, TokenType::STAR)) {
            $operator = $this->previous();
            $right = $this->unary();
            $expr = new Binary($expr, $operator, $right);
        }

        return $expr;
    }

    private function unary(): Expr
    {
        if ($this->match(TokenType::BANG, TokenType::MINUS)) {
            $operator = $this->previous();
            $right = $this->unary();
            return new Unary($operator, $right);
        }

        return $this->primary();
    }

    private function primary(): Expr
    {
        if ($this->match(TokenType::FALSE)) return new Literal(false);
        if ($this->match(TokenType::TRUE)) return new Literal(true);
        if ($this->match(TokenType::NIL)) return new Literal(null);

        if ($this->match(TokenType::NUMBER, TokenType::STRING)) {
            /** @var Token $previousToken */
            $previousToken = $this->previous();
            return new Literal($previousToken->getLiteral());
        }

        if ($this->match(TokenType::LEFT_PAREN)) {
            $expr = $this->expression();
            $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after expression.");
            return new Grouping($expr);
        }

        throw $this->error($this->peek(), "Expect expression.");
    }

    private function match(TokenType ...$types): bool
    {
        foreach ($types as $type) {
            if ($this->check($type)) {
                $this->advance();
                return true;
            }
        }

        return false;
    }

    private function consume(TokenType $type, string $message): Token
    {
        if ($this->check($type)) return $this->advance();

        throw $this->error($this->peek(), $message);
    }

    private function check(TokenType $type): bool
    {
        if ($this->isAtEnd()) return false;
        return $this->peek()->getType() === $type;
    }

    private function advance(): Token {
        if (!$this->isAtEnd()) $this->current++;
        return $this->previous();
    }

    private function isAtEnd(): bool
    {
        return $this->peek()->getType() == TokenType::EOF;
    }

    private function peek(): Token
    {
        return $this->tokens[$this->current];
    }

    private function previous(): Token
    {
        return $this->tokens[$this->current - 1];
    }

    private function error(Token $token, string $message): ParseError
    {
        Lox::parserError($token, $message);
        return new ParseError();
    }

    private function synchronize(): void {
        $this->advance();

        while (!$this->isAtEnd()) {
            if ($this->previous()->getType() == TokenType::SEMICOLON) return;

            switch ($this->peek()->getType()) {
                case TokenType::K_CLASS:
                case TokenType::FUN:
                case TokenType::VAR:
                case TokenType::FOR:
                case TokenType::IF:
                case TokenType::WHILE:
                case TokenType::PRINT:
                case TokenType::RETURN:
                return;
            }

            $this->advance();
        }
    }
}