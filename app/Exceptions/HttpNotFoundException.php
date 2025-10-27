<?php

declare(strict_types=1);

namespace App\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpNotFoundException extends HttpSpecializedException
{
    protected $code = 404;
    protected $message = "Resource not found";
    protected string $title = "404 Not Found";
    protected string $description = "The requested resource does not exist";
}
