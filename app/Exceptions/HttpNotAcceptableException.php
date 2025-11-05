<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace App\Exceptions;

class HttpNotAcceptableException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 400;

    /**
     * @var string
     */
    protected $message = 'Unacceptable Application Type. Must be \'application/json\'';

    protected string $title = '400 Bad Request';
    protected string $description = 'The requested resource could not be delivered. Please verify the Header\'s Accept and try again.';
}
