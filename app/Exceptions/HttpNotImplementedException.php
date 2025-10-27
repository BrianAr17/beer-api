<?php

declare(strict_types=1);

namespace App\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpNotImplementedException extends HttpSpecializedException
{
    protected $code = 501;
    protected $message = "Not implemented";
    protected string $title = "501 Not Implemented";
    protected string $description = "This operation is not supported by the server";
}
