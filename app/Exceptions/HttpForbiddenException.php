<?php

declare(strict_types=1);

namespace App\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpForbiddenException extends HttpSpecializedException
{
    protected $code = 403;
    protected $message = "Forbidden";
    protected string $title = "403 Forbidden";
    protected string $description = "You do not have permission to access this resource";
}
