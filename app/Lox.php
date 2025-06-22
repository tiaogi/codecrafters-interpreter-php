<?php

namespace App;

use App\AST\ASTPrinter;
use App\AST\Parser;
use App\Lexer\Enum\TokenType;
use App\Lexer\Lexer;
use App\Lexer\Token;

class Lox
{
    public static bool  $hadError   = false;
    public static array $tokens     = [];
    public static array $errors     = [];
    public static array $exprs      = [];

    static function tokenize(string $source): void
    {
        if ($source) {
            $lexer = new Lexer($source);
            /** @var array<Token> $tokens */
            self::$tokens = $lexer->tokenize();
        }
    }

    static function parse(): void
    {
        $parser = new Parser(self::$tokens);
        self::$exprs[] = $parser->parse();
    }

    static function printTokens(): void
    {
        if (count(self::$tokens) === 0) {
            fwrite(STDOUT, "EOF  null\n");
        }

        foreach (self::$tokens as $token) {
            fwrite(STDOUT, $token.PHP_EOL);
        }
    }

    static function printAST(): void
    {
        if (self::$hadError) {
            foreach (self::$errors as $error) fwrite(STDERR, $error.PHP_EOL);
            return;
        }

        foreach (self::$exprs as $expr) fwrite(STDOUT, (new ASTPrinter())->print($expr).PHP_EOL);
    }

    static function report(int $line, string $where, string $message): void
    {
        self::$errors[] = "[line $line] Error".$where.": $message";
        self::$hadError = true;
    }

    static function lexerError(int $line, string $message): void
    {
        self::report($line, "", $message);
    }

    static function parserError(Token $token, string $message): void
    {
        if ($token->getType() === TokenType::EOF) {
            self::report($token->getLine(), " at end", $message);
        } else {
            self::report($token->getLine(), " at '".$token->getLexeme()."'", $message);
        }
    }
}