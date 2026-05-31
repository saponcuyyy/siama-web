<?php

namespace App\Exceptions;

use Exception;

class InvalidHashIdException extends Exception
{
    public function __construct(string $message = 'Invalid hash ID.')
    {
        parent::__construct($message, 404);
    }
}
