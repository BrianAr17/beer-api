<?php

declare(strict_types=1);

namespace App\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpProcessingException extends HttpSpecializedException
{
    protected $code = 500;
    protected $message = "Processing error";
    protected string $title = "500 Internal Server Error";
    protected string $description = "An error occurred while processing your request";
}
