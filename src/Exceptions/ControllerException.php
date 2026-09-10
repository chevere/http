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
use Chevere\Parameter\Interfaces\ParameterInterface;
use Exception;
use Throwable;
use function Chevere\Message\message;

/**
 * Exception thrown at HTTP Controller layer.
 *
 * This exception MUST be thrown from a concrete class implementing ControllerInterface.
 * It provides a mechanism to return structured responses with appropriate HTTP
 * status codes and optional return data.
 *
 * @example Basic usage with status code
 * ```php
 * try {
 *     $user = $this->userService->findById($id);
 * } catch (UserNotFoundException $e) {
 *     throw new ControllerException('User not found', 404);
 * }
 * ```
 *
 * @example With return data for API responses
 * ```php
 * try {
 *     $this->validator->validate($data);
 * } catch (ValidationException $e) {
 *     throw new ControllerException(
 *         message: 'Validation failed',
 *         code: 422,
 *         return: ['errors' => $e->getErrors()]
 *     );
 * }
 * ```
 */
class ControllerException extends Exception
{
    private mixed $return;

    private ParameterInterface $acceptReturn;

    /**
     * @throws ActionException When thrown from a class not implementing ControllerInterface
     *
     * @param string $message Exception message describing the error
     * @param int $code HTTP status code
     * @param mixed $return [optional] Return value compatible with Controller context return
     * @param class-string<ControllerInterface> $controller [internal] You should not set this manually
     */
    public function __construct(
        string $message = '',
        int $code = 500,
        ?Throwable $previous = null,
        mixed $return = null,
        ?string $controller = null
    ) {
        $frame = $this->getTraceFrame();
        $file = $frame['file'] ?? __FILE__;
        $line = $frame['line'] ?? __LINE__;
        $controller ??= $frame['class'];

        try {
            $controllerClass = (new ControllerName($controller))->__toString();
        } catch (Throwable $e) {
            throw new ActionException(
                sprintf(
                    'Exception `%s` must be thrown from a class implementing `%s`',
                    self::class,
                    ControllerInterface::class
                ),
                $file,
                $line,
                $e
            );
        }
        /**
         * @see https://datatracker.ietf.org/doc/html/rfc9110#name-status-codes
         */
        if ($code < 100 || $code > 599) {
            throw new ActionException(
                sprintf(
                    'Status code `%s` is not valid according to RFC 9110',
                    $code
                ),
                $file,
                $line,
                $previous,
            );
        }
        $this->return = $return;
        $this->acceptReturn = $controllerClass::reflection()->return();
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed The return value "as-is" without validation
     */
    public function return(): mixed
    {
        return $this->return;
    }

    /**
     * Returns asserted return value according to Controller context
     *
     * @return mixed The asserted return value
     * @throws ActionException If the return value is not compatible with the Controller context
     */
    public function assertReturn(): mixed
    {
        return $this->acceptReturn->__invoke($this->return);
    }

    /**
     * @return array{class: string, file: ?string, line: ?int}
     */
    private function getTraceFrame(): array
    {
        /** @infection-ignore-all */
        $frame = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS | DEBUG_BACKTRACE_PROVIDE_OBJECT);
        $file = $frame[1]['file'] ?? null;
        $line = $frame[1]['line'] ?? null;
        unset($frame[0], $frame[1]);
        foreach ($frame as $frame) {
            if (! isset($frame['object'])) {
                continue;
            }

            return [
                'class' => get_class($frame['object']),
                'file' => $file,
                'line' => $line,
            ];
        }

        return [
            'class' => '',
            'file' => null,
            'line' => null,
        ];
    }
}
