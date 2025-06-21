<?php

namespace App;

use App\AST\ASTPrinter;
use App\AST\Parser;
use App\Lexer\Enum\TokenType;
use App\Lexer\Lexer;
use App\Lexer\Token;

class Lox
{
    public static bool $hadError = false;
    public static array $tokens = [];
    public static array $errors = [];

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
        $expr = $parser->parse();
        $printer = new ASTPrinter();
        echo $printer->print($expr) . "\n";
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
            self::report($token->getLine(), " at '" + $token->getLexeme() + "'", $message);
        }
    }
}