<?php
namespace App\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpRangeValidationException extends HttpSpecializedException
{
    protected $code = 445;
    protected $message = 'Invalid range parameters.';
    protected string $title = '445 Bad Request';
    protected string $description = 'Range-based query parameters must have logical ordering (e.g., from < to).';
}
