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
    case OP_BANG = "!";
    case OP_BANG_EQUAL = "!=";
    case OP_EQUAL = "=";
    case OP_EQUAL_EQUAL = "==";
    case OP_GREATER = ">";
    case OP_GREATER_EQUAL = ">=";
    case OP_LESS = "<";
    case OP_LESS_EQUAL = "<=";

    // Literals.
    case L_IDENTIFIER = "ID";
    case L_STRING = "S";
    case L_NUMBER = "N";

      // Keywords.
    case K_AND = "and";
    case K_CLASS = "class";
    case K_ELSE = "else";
    case K_FALSE = "false";
    case K_FUN = "fun";
    case K_FOR = "for";
    case K_IF = "if";
    case K_NIL = "nil";
    case K_OR = "or";
    case K_PRINT = "print";
    case K_RETURN = "return";
    case K_SUPER = "super";
    case K_THIS = "this";
    case K_TRUE = "true";
    case K_VAR = "var";
    case K_WHILE = "while";

    // EOF
    case EOF = PHP_EOL;
}