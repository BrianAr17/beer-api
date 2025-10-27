<?php

declare(strict_types=1);

namespace App\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpBadRequestException extends HttpSpecializedException
{
    protected $code = 400;
    protected $message = "Bad request";
    protected string $title = "400 Bad Request";
    protected string $description = "The request is malformed or missing parameters";
}
