<?php
namespace App\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpInvalidNumberException extends HttpSpecializedException
{
    protected $code = 499;
    protected $message = 'Invalid number parameters.';
    protected string $title = '449 Invalid Number';
    protected string $description = 'The number needs to be a positive number and above 0!';
}
