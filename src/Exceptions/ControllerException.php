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
use Chevere\Http\Interfaces\ControllerInterface;
use Exception;
use Throwable;
use function Chevere\Message\message;

/**
 * Exception thrown at HTTP Controller layer.
 *
 * This exception MUST be thrown from a class implementing ControllerInterface.
 *
 * Dependencies should throw domain-specific exceptions. Controllers should
 * translate them to ControllerException with appropriate HTTP status codes.
 *
 * Example:
 * ```php
 * try {
 *     $user = $this->userService->findById($id);
 * } catch (UserNotFoundException $e) {
 *     throw new ControllerException('User not found', 404);
 * }
 * ```
 *
 * @param mixed $return Return value compatible with the definition at Controller's `return()` method
 */
class ControllerException extends Exception
{
    public readonly mixed $return;

    public function __construct(
        string $message = '',
        int $code = 0,
        mixed $return = null,
        ?Throwable $previous = null
    ) {
        /** @infection-ignore-all */
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $file = $backtrace[0]['file'] ?? __FILE__;
        $line = $backtrace[0]['line'] ?? __LINE__;
        $class = $backtrace[1]['class'] ?? '';

        try {
            $controllerName = new ControllerName($class);
        } catch (Throwable $e) {
            throw new ActionException(
                (string) message(
                    '%self% must be thrown from a class implementing %interface%',
                    self: self::class,
                    interface: ControllerInterface::class
                ),
                $e,
                $file,
                $line
            );
        }
        if ($return !== null) {
            try {
                $this->return = $controllerName->__toString()::return()->__invoke($return);
            } catch (Throwable $e) {
                throw new ActionException(
                    (string) message(
                        'Argument `%argument%` value is not compatible with return type defined in %controller%::return() method',
                        argument: '$return',
                        controller: $controllerName
                    ),
                    $e,
                    $file,
                    $line
                );
            }
        } else {
            $this->return = null;
        }

        parent::__construct($message, $code, $previous);
    }
}
