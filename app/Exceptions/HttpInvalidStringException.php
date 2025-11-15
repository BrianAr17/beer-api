<?php
namespace App\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpInvalidStringException extends HttpSpecializedException
{
    protected $code = 450;
    protected $message = 'Invalid string parameters, cannot be a number or character';
    protected string $title = '450 Invalid String';
    protected string $description = 'The number needs to be string, not a number or character!';
}
