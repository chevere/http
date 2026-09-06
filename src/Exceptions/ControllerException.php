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
use Chevere\Http\Interfaces\StatusCodesInterface;
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
        /** @infection-ignore-all */
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $file = $backtrace[0]['file'] ?? __FILE__;
        $line = $backtrace[0]['line'] ?? __LINE__;
        $controller ??= $backtrace[1]['class'] ?? '';

        try {
            $controllerClass = (new ControllerName($controller))->__toString();
        } catch (Throwable $e) {
            throw new ActionException(
                (string) message(
                    'Exception `%self%` must be thrown from a class implementing `%interface%`',
                    self: self::class,
                    interface: ControllerInterface::class
                ),
                $file,
                $line,
                $e
            );
        }
        if (! array_key_exists($code, StatusCodesInterface::CODES)) {
            throw new ActionException(
                (string) message(
                    'Status code `{{ code }}` is not a valid HTTP status code',
                    code: $code
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
}
