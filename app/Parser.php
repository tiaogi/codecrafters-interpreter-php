<?php

namespace App;

use App\AST\Expr\AssignExpr;
use App\AST\Expr\BinaryExpr;
use App\AST\Expr;
use App\AST\Expr\CallExpr;
use App\AST\Expr\GroupingExpr;
use App\AST\Expr\LiteralExpr;
use App\AST\Expr\LogicalExpr;
use App\AST\Stmt\BlockStmt;
use App\AST\Expr\UnaryExpr;
use App\AST\Stmt\ExpressionStmt;
use App\AST\Stmt\PrintStmt;
use App\AST\Stmt;
use App\AST\Stmt\VarStmt;
use App\AST\Expr\VariableExpr;
use App\AST\Stmt\FunctionStmt;
use App\AST\Stmt\IfStmt;
use App\AST\Stmt\ReturnStmt;
use App\AST\Stmt\WhileStmt;
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

    public function parse(): array
    {
        $statements = [];

        while (!$this->isAtEnd()) {
            $statements[] = $this->declaration();
        }

        return array_filter($statements);
    }

    public function parseExpr(): ?Expr
    {
        try {
            return $this->expression();
        } catch (ParseError $e) {
            return null;
        }
    }

    private function expression(): Expr
    {
        return $this->assignment();
    }

    private function declaration(): ?Stmt
    {
        try {
            if ($this->match(TokenType::FUN)) return $this->function("function");
            if ($this->match(TokenType::VAR)) return $this->varDeclaration();
            return $this->statement();
        } catch (ParseError $e) {
            $this->synchronize();
            return null;
        }
    }

    private function statement(): Stmt
    {
        if ($this->match(TokenType::FOR)) return $this->forStatement();
        if ($this->match(TokenType::IF)) return $this->ifStatement();
        if ($this->match(TokenType::PRINT)) return $this->printStatement();
        if ($this->match(TokenType::RETURN)) return $this->returnStatement();
        if ($this->match(TokenType::WHILE)) return $this->whileStatement();
        if ($this->match(TokenType::LEFT_BRACE)) return new BlockStmt($this->block());

        return $this->expressionStatement();
    }

    private function forStatement(): Stmt
    {
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'for'.");

        $initializer = null;
        if ($this->match(TokenType::SEMICOLON)) {
            $initializer = null;
        } else if ($this->match(TokenType::VAR)) {
            $initializer = $this->varDeclaration();
        } else {
            $initializer = $this->expressionStatement();
        }

        $condition = null;
        if (!$this->check(TokenType::SEMICOLON)) {
            $condition = $this->expression();
        }
        $this->consume(TokenType::SEMICOLON, "Expect ';' after loop condition.");

        $increment = null;
        if (!$this->check(TokenType::RIGHT_PAREN)) {
            $increment = $this->expression();
        }
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after for clauses.");

        $body = $this->statement();

        if (!is_null($increment)) {
            $body = new BlockStmt([$body, new ExpressionStmt($increment)]);
        }

        if (is_null($condition)) $condition = new LiteralExpr(true);
        $body = new WhileStmt($condition, $body);

        if (!is_null($initializer)) {
            $body = new BlockStmt([$initializer, $body]);
        }

        return $body;
    }

    private function ifStatement(): Stmt
    {
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'if'.");
        $condition = $this->expression();
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after if condition.");

        $thenBranch = $this->statement();
        $elseBranch = null;
        if ($this->match(TokenType::ELSE)) {
            $elseBranch = $this->statement();
        }

        return new IfStmt($condition, $thenBranch, $elseBranch);
    }

    private function printStatement(): Stmt
    {
        $value = $this->expression();
        $this->consume(TokenType::SEMICOLON, "Expect ';' after value.");
        return new PrintStmt($value);
    }

    private function returnStatement(): Stmt
    {
        $keyword = $this->previous();
        $value = null;
        if (!$this->check(TokenType::SEMICOLON)) {
            $value = $this->expression();
        }

        $this->consume(TokenType::SEMICOLON, "Expect ';' after return value.");
        return new ReturnStmt($keyword, $value);
    }

    private function varDeclaration(): Stmt
    {
        $name = $this->consume(TokenType::IDENTIFIER, "Expect variable name.");

        $initializer = null;
        if ($this->match(TokenType::EQUAL)) {
            $initializer = $this->expression();
        }

        $this->consume(TokenType::SEMICOLON, "Expect ';' after variable declaration.");
        return new VarStmt($name, $initializer);
    }

    private function whileStatement(): Stmt
    {
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'while'.");
        $condition = $this->expression();
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after condition.");
        $body = $this->statement();

        return new WhileStmt($condition, $body);
    }

    private function expressionStatement(): Stmt
    {
        $expr = $this->expression();
        $this->consume(TokenType::SEMICOLON, "Expect ';' after expression.");
        return new ExpressionStmt($expr);
    }

    private function function(string $kind): FunctionStmt
    {
        $name = $this->consume(TokenType::IDENTIFIER, "Expect ".$kind." name.");
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after ".$kind." name.");
        $parameters = [];
        if (!$this->check(TokenType::RIGHT_PAREN)) {
            do {
                if (count($parameters) >= 255) {
                    $this->error($this->peek(), "Can't have more than 255 parameters.");
                }

                $parameters[] = $this->consume(TokenType::IDENTIFIER, "Expect parameter name.");
            } while ($this->match(TokenType::COMMA));
        }
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after parameters.");

        $this->consume(TokenType::LEFT_BRACE, "Expect '{' before ".$kind." body.");
        $body = $this->block();

        return new FunctionStmt($name, $parameters, $body);
    }

    private function block(): array
    {
        $statements = [];

        while (!$this->check(TokenType::RIGHT_BRACE) && !$this->isAtEnd()) {
            $statements[] = $this->declaration();
        }

        $this->consume(TokenType::RIGHT_BRACE, "Expect '}' after block.");
        return $statements;
    }

    private function assignment(): Expr
    {
        $expr = $this->or();

        if ($this->match(TokenType::EQUAL)) {
            $equals = $this->previous();
            $value = $this->assignment();

            if (is_a($expr, VariableExpr::class)) {
                $name = $expr->getName();
                return new AssignExpr($name, $value);
            }

            $this->error($equals, "Invalid assignment target.");
        }

        return $expr;
    }

    private function or(): Expr
    {
        $expr = $this->and();

        while ($this->match(TokenType::OR)) {
            $operator = $this->previous();
            $right = $this->and();
            $expr = new LogicalExpr($expr, $operator, $right);
        }

        return $expr;
    }

    private function and(): Expr
    {
        $expr = $this->equality();

        while ($this->match(TokenType::AND)) {
            $operator = $this->previous();
            $right = $this->equality();
            $expr = new LogicalExpr($expr, $operator, $right);
        }

        return $expr;
    }

    private function equality(): Expr
    {
        $expr = $this->comparison();

        while ($this->match(TokenType::BANG_EQUAL, TokenType::EQUAL_EQUAL)) {
            $operator = $this->previous();
            $right = $this->comparison();
            $expr = new BinaryExpr($expr, $operator, $right);
        }

        return $expr;
    }

    private function comparison(): Expr
    {
        $expr = $this->term();

        while ($this->match(TokenType::GREATER, TokenType::GREATER_EQUAL, TokenType::LESS, TokenType::LESS_EQUAL)) {
            $operator = $this->previous();
            $right = $this->term();
            $expr = new BinaryExpr($expr, $operator, $right);
        }

        return $expr;
    }

    private function term(): Expr
    {
        $expr = $this->factor();

        while ($this->match(TokenType::MINUS, TokenType::PLUS)) {
            $operator = $this->previous();
            $right = $this->factor();
            $expr = new BinaryExpr($expr, $operator, $right);
        }

        return $expr;
    }

    private function factor(): Expr
    {
        $expr = $this->unary();

        while ($this->match(TokenType::SLASH, TokenType::STAR)) {
            $operator = $this->previous();
            $right = $this->unary();
            $expr = new BinaryExpr($expr, $operator, $right);
        }

        return $expr;
    }

    private function unary(): Expr
    {
        if ($this->match(TokenType::BANG, TokenType::MINUS)) {
            $operator = $this->previous();
            $right = $this->unary();
            return new UnaryExpr($operator, $right);
        }

        return $this->call();
    }

    private function finishCall(Expr $callee): Expr
    {
        $arguments = [];
        if (!$this->check(TokenType::RIGHT_PAREN)) {
            do {
                if (count($arguments) >= 255) {
                   $this->error($this->peek(), "Can't have more than 255 arguments.");
                }
                $arguments[] = $this->expression();
            } while ($this->match(TokenType::COMMA));
        }

        $paren = $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after arguments.");

        return new CallExpr($callee, $paren, $arguments);
    }

    private function call(): Expr
    {
        $expr = $this->primary();

        while (true) {
            if ($this->match(TokenType::LEFT_PAREN)) {
                $expr = $this->finishCall($expr);
            } else {
                break;
            }
        }

        return $expr;
    }

    private function primary(): Expr
    {
        if ($this->match(TokenType::FALSE)) return new LiteralExpr(false);
        if ($this->match(TokenType::TRUE)) return new LiteralExpr(true);
        if ($this->match(TokenType::NIL)) return new LiteralExpr(null);

        if ($this->match(TokenType::NUMBER, TokenType::STRING)) {
            /** @var Token $previousToken */
            $previousToken = $this->previous();
            return new LiteralExpr($previousToken->getLiteral());
        }

        if ($this->match(TokenType::IDENTIFIER)) {
            return new VariableExpr($this->previous());
        }

        if ($this->match(TokenType::LEFT_PAREN)) {
            $expr = $this->expression();
            $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after expression.");
            return new GroupingExpr($expr);
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