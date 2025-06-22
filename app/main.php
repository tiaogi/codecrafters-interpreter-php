<?php

use App\Lox;

error_reporting(E_ALL);
require_once __DIR__ . './../autoload.php';

if ($argc < 3) {
    fwrite(STDERR, "Usage: ./your_program.sh tokenize <filename>\n");
    exit(1);
}

$command = $argv[1];
$filename = $argv[2];

$fileContents = file_get_contents($filename);
Lox::tokenize($fileContents);
if (count(Lox::$tokens) > 0) {
    Lox::parse();
}

foreach (Lox::$lexerErrors as $error) {
    fwrite(STDERR, $error.PHP_EOL);
}

switch ($command) {
    case "tokenize":
        Lox::printTokens();
        if (Lox::$hadLexerError) {
            exit(65);
        }
        break;
    case "parse":
        Lox::printAST();
        if (Lox::$hadParserError) {
            exit(65);
        }
        break;
    case "evaluate":
        Lox::evaluate();
        if (Lox::$hadRuntimeError) {
            exit(70);
        }
        break;
    default:
        fwrite(STDERR, "Unknown command: {$command}\n");
        exit(1);

}