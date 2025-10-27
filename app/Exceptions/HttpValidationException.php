<?php

declare(strict_types=1);

namespace App\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpValidationException extends HttpSpecializedException
{
    protected $code = 422;
    protected $message = "Validation failed";
    protected string $title = "422 Unprocessable Entity";
    protected string $description = "Some input data is invalid or missing";
}
