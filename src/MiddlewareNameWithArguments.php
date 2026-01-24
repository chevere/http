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
use Chevere\Parameter\Arguments;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionMethod;
use function Chevere\Parameter\reflectionToParameters;

final class MiddlewareNameWithArguments implements MiddlewareNameInterface
{
    use ActionNameTrait;

    /**
     * @var array<string|int, mixed>
     */
    private array $arguments;

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
            $this->arguments = (new Arguments($parameters, $arguments))->toArray();
        }
    }

    /**
     * @return array<string|int, mixed>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    public static function interface(): string
    {
        return MiddlewareInterface::class;
    }
}
