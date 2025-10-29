<?php

namespace App\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpDateFormatException extends HttpSpecializedException
{
    protected $code = 422;
    protected $message = 'Invalid date format.';
    protected string $title = '422 Unprocessable Entity';
    protected string $description = 'Dates must be in YYYY-MM-DD format.';
}

