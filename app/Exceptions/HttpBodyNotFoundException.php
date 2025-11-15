<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace App\Exceptions;

/** @api */
class HttpBodyNotFoundException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 487;

    /**
     * @var string
     */
    protected $message = 'Body Null.';

    protected string $title = '487 Body Not Found';
    protected string $description = 'The JSON body is missing or invalid.';
}
