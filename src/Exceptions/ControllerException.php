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
            $controllerName = new ControllerName($class);
        } catch (Throwable $e) {
            throw new ActionException(
                self::class . ' must be thrown from a Controller',
                $e,
                $file,
                $line
            );
        }

        try {
            $this->returnTyped = $controllerName->__toString()::return()->__invoke($this->return);
        } catch (Throwable $e) {
            throw new ActionException(
                <<<PLAIN
                Argument `\$return` value is not compatible with return type defined in {$controllerName}
                PLAIN,
                $e,
                $file,
                $line
            );
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * This method returns the value that was passed to the constructor, after
     * being processed by the Controller's `return()` method.
     */
    public function returnTyped(): mixed
    {
        return $this->returnTyped;
    }
}
