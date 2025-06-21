<?php

namespace App\Enum;

enum TokenType: string
{
    // Single-character tokens.
    case LEFT_PAREN = "(";
    case RIGHT_PAREN = ")";
    case LEFT_BRACE = "{";
    case RIGHT_BRACE = "}";
    case COMMA = ",";
    case DOT = ".";
    case MINUS = "-";
    case PLUS = "+";
    case SEMICOLON = ";";
    case SLASH = "/";
    case STAR = "*";

    // Operations.
    case BANG = "!";
    case BANG_EQUAL = "!=";
    case EQUAL = "=";
    case EQUAL_EQUAL = "==";
    case GREATER = ">";
    case GREATER_EQUAL = ">=";
    case LESS = "<";
    case LESS_EQUAL = "<=";

    // Literals.
    case IDENTIFIER = "ID";
    case STRING = "S";
    case NUMBER = "N";

      // Keywords.
    case AND = "and";
    case K_CLASS = "class"; // Reserved keyword for PHP
    case ELSE = "else";
    case FALSE = "false";
    case FUN = "fun";
    case FOR = "for";
    case IF = "if";
    case NIL = "nil";
    case OR = "or";
    case PRINT = "print";
    case RETURN = "return";
    case SUPER = "super";
    case THIS = "this";
    case TRUE = "true";
    case VAR = "var";
    case WHILE = "while";

    // EOF
    case EOF = PHP_EOL;
}