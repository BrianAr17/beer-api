<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace App\Exceptions;

/** @api */
class HttpArrayNotFoundException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 486;

    /**
     * @var string
     */
    protected $message = 'Array Not Found.';

    protected string $title = '486 Array Not Found';
    protected string $description = 'The Array is missing or invalid.';
}
