<?php

namespace App\Exception;

use RuntimeException;

class ReturnException extends RuntimeException
{
    public function __construct(
        public readonly mixed $value,
    ) {
        parent::__construct();
    }
}