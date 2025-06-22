<?php

namespace App\Interpreter;

use App\AST\Expr\Binary;
use App\AST\Expr\Expr;
use App\AST\Expr\Grouping;
use App\AST\Expr\Literal;
use App\AST\Expr\Unary;
use App\AST\Visitor;
use App\Exception\RuntimeError;
use App\Lexer\Enum\TokenType;
use App\Lexer\Token;
use App\Lox;

class Interpreter implements Visitor
{
    public function interpret(Expr $expression): void
    {
        try {
            $value = $this->evaluate($expression);
            fwrite(STDOUT, $this->stringify($value).PHP_EOL);
        } catch (RuntimeError $e) {
            Lox::runtimeError($e);
        }
    }

    public function visitLiteral(Literal $expr): mixed
    {
        return $expr->getValue();
    }

    public function visitGrouping(Grouping $expr): mixed
    {
        return $this->evaluate($expr->getExpression());
    }

    public function visitUnary(Unary $expr): mixed
    {
        $right = $this->evaluate($expr->getRight());

        switch ($expr->getOperator()->getType()) {
            case TokenType::BANG:
                return !$this->isTruthy($right);
            case TokenType::MINUS:
                $this->checkNumberOperand($expr->getOperator(), $right);
                return -((float) $right);
        }

        // Unreachable.
        return null;
    }

    private function checkNumberOperand(Token $operator, mixed $operand): void
    {
        if (is_float($operand)) return;
        throw new RuntimeError($operator, "Operand must be a number.");
    }

    private function checkNumberOperands(Token $operator, mixed $left, mixed $right): void
    {
        if (is_float($left) && is_float($right)) return;
        throw new RuntimeError($operator, "Operands must be numbers.");
    }

    public function visitBinary(Binary $expr): mixed
    {
        $left = $this->evaluate($expr->getLeft());
        $right = $this->evaluate($expr->getRight());

        switch ($expr->getOperator()->getType()) {
            case TokenType::GREATER:
                $this->checkNumberOperands($expr->getOperator(), $left, $right);
                return (float) $left > (float) $right;
            case TokenType::GREATER_EQUAL:
                $this->checkNumberOperands($expr->getOperator(), $left, $right);
                return (float) $left >= (float) $right;
            case TokenType::LESS:
                $this->checkNumberOperands($expr->getOperator(), $left, $right);
                return (float) $left < (float) $right;
            case TokenType::LESS_EQUAL:
                $this->checkNumberOperands($expr->getOperator(), $left, $right);
                return (float) $left <= (float) $right;
            case TokenType::MINUS:
                $this->checkNumberOperands($expr->getOperator(), $left, $right);
                return (float) $left - (float) $right;
            case TokenType::SLASH:
                $this->checkNumberOperands($expr->getOperator(), $left, $right);
                return (float) $left / (float) $right;
            case TokenType::STAR:
                $this->checkNumberOperands($expr->getOperator(), $left, $right);
                return (float) $left * (float) $right;
            case TokenType::PLUS:
                if (is_float($left) && is_float($right)) {
                    return (float) $left + (float) $right;
                }

                if (is_string($left) && is_string($right)) {
                    return ((string) $left).((string) $right);
                }

                throw new RuntimeError($expr->getOperator(), "Operands must be two numbers or two strings.");
            case TokenType::BANG_EQUAL: return !$this->isEqual($left, $right);
            case TokenType::EQUAL_EQUAL: return $this->isEqual($left, $right);
        }

        // Unreachable.
        return null;
    }

    private function evaluate(Expr $expr): mixed
    {
        return $expr->accept($this);
    }

    private function isTruthy(mixed $object): bool
    {
        if (is_null($object)) return false;
        if (is_bool($object)) return (bool) $object;
        return true;
    }

    private function isEqual(mixed $a, mixed $b): bool
    {
        if (is_null($a) && is_null($b)) return true;
        if (is_null($a)) return false;

        return $a === $b;
    }

    private function stringify(mixed $object): string
    {
        if (is_null($object)) return "nil";

        if (is_float($object)) {
            $text = (string) $object;
            if (str_ends_with($text, ".0")) {
                $text = substr($text, 0, strlen($text) - 2);
            }
            return $text;
        }

        if (is_bool($object)) return $object ? "true" : "false";

        return (string) $object;
    }
}