<?php

namespace App;

use App\Token;

class Lox
{
    public static bool $hadError = false;

    static function run(string $source): void
    {
        if ($source) {
            $lexer = new Lexer($source);
            /** @var array<Token> $tokens */
            $tokens = $lexer->tokenize();

            foreach ($tokens as $token) {
                fwrite(STDOUT, $token.PHP_EOL);
            }

            if (Lox::$hadError) {
                exit(65);
            }
        } else {
            fwrite(STDOUT, "EOF  null\n");
        }
    }

    static function report(int $line, string $where, string $message): void
    {
        fwrite(STDERR, "[line $line] Error".$where.": $message".PHP_EOL);
        self::$hadError = true;
    }

    static function error(int $line, string $message): void
    {
        self::report($line, "", $message);
    }
}