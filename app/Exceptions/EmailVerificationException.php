<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class EmailVerificationException extends HttpException
{
    public function __construct(string $message = null, \Exception $previous = null, ?int $code = 0, array $headers = [])
    {
        parent::__construct(400, $message, $previous, $headers, $code);
    }
}
