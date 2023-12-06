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

use Chevere\Http\Attributes\Description;
use Chevere\Http\Attributes\Request;
use Chevere\Http\Attributes\Response;
use Chevere\Http\Interfaces\MiddlewaresInterface;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

function middlewares(string ...$middleware): MiddlewaresInterface
{
    $middlewares = [];
    foreach ($middleware as $name) {
        $middlewares[] = new MiddlewareName($name);
    }

    return new Middlewares(...$middlewares);
}

function requestAttribute(string $className): Request
{
    // @phpstan-ignore-next-line
    $reflection = new ReflectionClass($className);

    // @phpstan-ignore-next-line
    return getAttribute($reflection, Request::class);
}

function responseAttribute(string $className): Response
{
    // @phpstan-ignore-next-line
    $reflection = new ReflectionClass($className);

    // @phpstan-ignore-next-line
    return getAttribute($reflection, Response::class);
}

function descriptionAttribute(string $className): Description
{
    // @phpstan-ignore-next-line
    $reflection = new ReflectionClass($className);

    // @phpstan-ignore-next-line
    return getAttribute($reflection, Description::class);
}

// @phpstan-ignore-next-line
function getAttribute(
    ReflectionClass|ReflectionFunction|ReflectionMethod|ReflectionProperty|ReflectionParameter|ReflectionClassConstant $reflection,
    string $attribute
): object {
    $attributes = $reflection->getAttributes($attribute);
    if ($attributes === []) {
        return new $attribute();
    }

    return $attributes[0]->newInstance();
}
