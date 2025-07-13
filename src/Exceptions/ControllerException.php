<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Http\Exceptions;

use Chevere\Action\Exceptions\ActionException;
use Chevere\Http\ControllerName;
use Exception;
use Throwable;

/**
 * Exception thrown at HTTP Controller layer.
 *
 * This exception must be thrown from a HTTP Controller and must pass the corresponding
 * return value to the constructor. The return value must be compatible with the
 * return type defined in the Controller's `return()` method.
 */
class ControllerException extends Exception
{
    public readonly mixed $returnTyped;

    public function __construct(
        string $message = '',
        int $code = 0,
        public readonly mixed $return = null,
        ?Throwable $previous = null
    ) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $file = $backtrace[0]['file'] ?? __FILE__;
        $line = $backtrace[0]['line'] ?? __LINE__;
        $class = $backtrace[1]['class'] ?? null;

        try {
            new ControllerName($class);
        } catch (Throwable $e) {
            throw new ActionException(
                self::class . ' must be thrown from a Controller',
                $e,
                $file,
                $line
            );
        }
        parent::__construct($message, $code, $previous);
    }
}
