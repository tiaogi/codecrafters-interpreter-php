<?php

namespace App;

use App\Enum\TokenType;

class Lexer
{
    /**
     * @var array<Token>
     */
    private array $tokens = [];
    private int $start = 0;
    private int $current = 0;
    private int $line = 1;

    public function __construct(
        private string $source,
    ) {}

    public function tokenize(): array
    {
        while (!$this->isAtEnd()) {
            $this->start = $this->current;
            $c = $this->advance();

            switch ($c) {
                case '(': $this->addToken(TokenType::LEFT_PAREN); break;
                case ')': $this->addToken(TokenType::RIGHT_PAREN); break;
                case '{': $this->addToken(TokenType::LEFT_BRACE); break;
                case '}': $this->addToken(TokenType::RIGHT_BRACE); break;
                case ',': $this->addToken(TokenType::COMMA); break;
                case '.': $this->addToken(TokenType::DOT); break;
                case '-': $this->addToken(TokenType::MINUS); break;
                case '+': $this->addToken(TokenType::PLUS); break;
                case ';': $this->addToken(TokenType::SEMICOLON); break;
                case '*': $this->addToken(TokenType::STAR); break;
                case '!':
                    $this->addToken($this->match('=') ? TokenType::OP_BANG_EQUAL : TokenType::OP_BANG);
                    break;
                case '=':
                    $this->addToken($this->match('=') ? TokenType::OP_EQUAL_EQUAL : TokenType::OP_EQUAL);
                    break;
                case '<':
                    $this->addToken($this->match('=') ? TokenType::OP_LESS_EQUAL : TokenType::OP_LESS);
                    break;
                case '>':
                    $this->addToken($this->match('=') ? TokenType::OP_GREATER_EQUAL : TokenType::OP_GREATER);
                    break;
                default:
                    Lox::error($this->line, "Unexpected character: $c");
                    break;
            }
        }

        $this->tokens[] = new Token(TokenType::EOF, "", $this->line);
        return $this->tokens;
    }

    private function isAtEnd(): bool
    {
        return $this->current >= strlen($this->source);
    }

    private function match(string $expected): bool
    {
        if ($this->isAtEnd()) return false;
        if ($this->source[$this->current] !== $expected) return false;

        $this->current++;
        return true;
    }

    private function advance(): string
    {
        return $this->source[$this->current++];
    }

    private function addToken(TokenType $type): void
    {
        $text = substr($this->source, $this->start, $this->current - $this->start);
        $this->tokens[] = new Token($type, $text, $this->line);
    }
}