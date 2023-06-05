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

use Chevere\Http\Attributes\Headers;
use Chevere\Http\Attributes\Statuses;
use Chevere\Http\Interfaces\MiddlewaresInterface;
use ReflectionClass;

function middlewares(string ...$middleware): MiddlewaresInterface
{
    $middlewares = [];
    foreach ($middleware as $name) {
        $middlewares[] = new MiddlewareName($name);
    }

    return new Middlewares(...$middlewares);
}

function classAttribute(string $className, string $attribute): object
{
    // @phpstan-ignore-next-line
    $reflection = new ReflectionClass($className);
    $attributes = $reflection->getAttributes($attribute);
    if ($attributes === []) {
        return new $attribute();
    }

    return $attributes[0]->newInstance();
}

function classHeaders(string $className): Headers
{
    // @phpstan-ignore-next-line
    return classAttribute($className, Headers::class);
}

function classStatuses(string $className): Statuses
{
    // @phpstan-ignore-next-line
    return classAttribute($className, Statuses::class);
}
