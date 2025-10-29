<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace App\Exception;
use Slim\Exception\HttpSpecializedException;

/** @api */
class HttpWrongTypeException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 438;

    /**
     * @var string
     */
    protected $message = 'Wrong Type.';

    protected string $title = '438 Wrong Type';
    protected string $description = 'The request requires the use to use the correct data type.';
}
