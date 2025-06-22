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

foreach (Lox::$errors as $error) {
    fwrite(STDERR, $error.PHP_EOL);
}

switch ($command) {
    case "tokenize":
        Lox::printTokens();
        if (Lox::$hadError) {
            exit(65);
        }
        break;
    case "parse":
        Lox::parse();
        Lox::printAST();
        if (Lox::$hadError) {
            exit(65);
        }
        break;
    default:
        fwrite(STDERR, "Unknown command: {$command}\n");
        exit(1);

}
