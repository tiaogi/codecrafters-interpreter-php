<?php

namespace App;

use App\AST\Expr\AssignExpr;
use App\AST\Expr\BinaryExpr;
use App\AST\Expr;
use App\AST\Expr\CallExpr;
use App\AST\Expr\ExprVisitor;
use App\AST\Expr\GroupingExpr;
use App\AST\Expr\LiteralExpr;
use App\AST\Expr\LogicalExpr;
use App\AST\Stmt\BlockStmt;
use App\AST\Expr\UnaryExpr;
use App\AST\Stmt\ExpressionStmt;
use App\AST\Stmt\PrintStmt;
use App\AST\Stmt;
use App\AST\Stmt\StmtVisitor;
use App\AST\Stmt\VarStmt;
use App\AST\Expr\VariableExpr;
use App\AST\Stmt\FunctionStmt;
use App\AST\Stmt\IfStmt;
use App\AST\Stmt\ReturnStmt;
use App\AST\Stmt\WhileStmt;
use App\Callable\Clock;
use App\Callable\LoxCallable;
use App\Callable\LoxFunction;
use App\Environment;
use App\Exception\ReturnException;
use App\Exception\RuntimeError;
use App\Lexer\Enum\TokenType;
use App\Lexer\Token;
use App\Lox;

class Interpreter implements ExprVisitor, StmtVisitor
{
    private readonly Environment $globals;
    private Environment $environment;

    public function __construct() {
        $this->globals = new Environment();
        $this->globals->define("clock", new Clock);

        $this->environment = $this->globals;
    }

    public function interpret(array $statements): void
    {
        try {
            /** @var array<Stmt> $statements */
            foreach ($statements as $statement) {
                $this->execute($statement);
            }
        } catch (RuntimeError $e) {
            Lox::runtimeError($e);
        }
    }

    public function interpretExpr(Expr $expression): void
    {
        try {
            $value = $this->evaluate($expression);
            fwrite(STDOUT, $this->stringify($value).PHP_EOL);
        } catch (RuntimeError $e) {
            Lox::runtimeError($e);
        }
    }

    public function visitLiteralExpr(LiteralExpr $expr): mixed
    {
        return $expr->getValue();
    }

    public function visitLogicalExpr(LogicalExpr $expr): mixed
    {
        $left = $this->evaluate($expr->getLeft());

        if ($expr->getOperator()->getType() === TokenType::OR) {
            if ($this->isTruthy($left)) return $left;
        } else {
            if (!$this->isTruthy($left)) return $left;
        }

        return $this->evaluate($expr->getRight());
    }

    public function visitGroupingExpr(GroupingExpr $expr): mixed
    {
        return $this->evaluate($expr->getExpression());
    }

    public function visitUnaryExpr(UnaryExpr $expr): mixed
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

    public function visitVariableExpr(VariableExpr $expr): mixed
    {
        return $this->environment->get($expr->getName());
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

    public function visitBinaryExpr(BinaryExpr $expr): mixed
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

    public function visitCallExpr(CallExpr $expr): mixed
    {
        $function = $this->evaluate($expr->getCallee());

        $arguments = [];
        foreach ($expr->getArguments() as $argument) {
            $arguments[] = $this->evaluate($argument);
        }

        if (!is_a($function, LoxCallable::class)) {
            throw new RuntimeError($expr->getParen(), "Can only call functions and classes.");
        }

        if (count($arguments) !== $function->arity()) {
            throw new RuntimeError($expr->getParen(), "Expected ".$function->arity()." arguments but got ".count($arguments).".");
        }

        return $function($this, $arguments);
    }

    private function evaluate(Expr $expr): mixed
    {
        return $expr->accept($this);
    }

    private function execute(?Stmt $stmt): void
    {
        if (is_null($stmt)) return;
        $stmt->accept($this);
    }

    public function executeBlock(array $statements, Environment $environment): void
    {
        $previous = $this->environment;
        try {
            $this->environment = $environment;

            foreach ($statements as $statement) {
                $this->execute($statement);
            }
        } finally {
            $this->environment = $previous;
        }
    }

    public function visitBlockStmt(BlockStmt $stmt): void
    {
        $this->executeBlock($stmt->getStatements(), new Environment($this->environment));
    }

    public function visitExpressionStmt(ExpressionStmt $stmt): void
    {
        $this->evaluate($stmt->getExpression());
    }

    public function visitIfStmt(IfStmt $stmt): void
    {
        if ($this->isTruthy($this->evaluate($stmt->getCondition()))) {
            $this->execute($stmt->getThenBranch());
        } else if (!is_null($stmt->getElseBranch())) {
            $this->execute($stmt->getElseBranch());
        }
    }

    public function visitFunctionStmt(FunctionStmt $stmt): void
    {
        $function = new LoxFunction($stmt, $this->environment);
        $this->environment->define($stmt->getName()->getLexeme(), $function);
    }

    public function visitPrintStmt(PrintStmt $stmt): void
    {
        $value = $this->evaluate($stmt->getExpression());
        fwrite(STDOUT, $this->stringify($value).PHP_EOL);
    }

    public function visitReturnStmt(ReturnStmt $stmt): void
    {
        $value = null;
        if (!is_null($stmt->getValue())) $value = $this->evaluate($stmt->getValue());

        throw new ReturnException($value);
    }

    public function visitVarStmt(VarStmt $stmt): void
    {
        $value = null;
        if (!is_null($stmt->getInitializer())) {
            $value = $this->evaluate($stmt->getInitializer());
        }

        $this->environment->define($stmt->getName()->getLexeme(), $value);
    }

    public function visitWhileStmt(WhileStmt $stmt): void
    {
        while ($this->isTruthy($this->evaluate($stmt->getCondition()))) {
            $this->execute($stmt->getBody());
        }
    }

    public function visitAssignExpr(AssignExpr $expr): mixed
    {
        $value = $this->evaluate($expr->getValue());
        $this->environment->assign($expr->getName(), $value);
        return $value;
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