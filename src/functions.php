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

use Chevere\Http\Attributes\Header;
use Chevere\Http\Attributes\Status;
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

/**
 * @return array<object>
 */
function classAttributes(string $className, string $attribute): array
{
    // @phpstan-ignore-next-line
    $reflection = new ReflectionClass($className);
    $attributes = $reflection->getAttributes($attribute);
    $return = [];
    if ($attributes === []) {
        return $return;
    }
    foreach ($attributes as $attribute) {
        // $attribute->getArguments();
        $return[] = $attribute->newInstance();
    }

    return $return;
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
    /** @var array<Header> */
    $attributes = classAttributes($className, Header::class);

    return new Headers(...$attributes);
}

function classStatus(string $className): Status
{
    // @phpstan-ignore-next-line
    return classAttribute($className, Status::class);
}
