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

if ($command !== "tokenize") {
    fwrite(STDERR, "Unknown command: {$command}\n");
    exit(1);
}

$fileContents = file_get_contents($filename);

Lox::run($fileContents);
