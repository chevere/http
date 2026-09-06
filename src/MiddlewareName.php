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

namespace Chevere\Http;

use Chevere\Action\Traits\ActionNameTrait;
use Chevere\Http\Interfaces\MiddlewareNameInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionMethod;
use function Chevere\Parameter\reflectionToParameters;

final class MiddlewareName implements MiddlewareNameInterface
{
    use ActionNameTrait;

    /**
     * @param array<string|int, mixed> $arguments
     */
    public function __construct(
        private string $name,
        mixed ...$arguments
    ) {
        $this->onConstruct();
        $this->arguments = [];
        if (method_exists($this->name, 'setUp')) {
            $parameters = reflectionToParameters(
                new ReflectionMethod($this->name, 'setUp')
            );
            $this->arguments = $parameters->__invoke(...$arguments)
                ->toArray();
        }
    }

    public static function interface(): string
    {
        return MiddlewareInterface::class;
    }
}
