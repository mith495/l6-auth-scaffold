<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class EmailTakenException extends Exception
{
    public function __construct($message = 'Email already taken', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
