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

use Attribute;
use Chevere\Http\Attributes\Header;
use Chevere\Http\Attributes\Status;
use Chevere\Http\Interfaces\MiddlewaresInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use function Chevere\Attribute\getAttribute;

function middlewares(string ...$middleware): MiddlewaresInterface
{
    $middlewares = [];
    foreach ($middleware as $name) {
        $middlewares[] = new MiddlewareName($name);
    }

    return new Middlewares(...$middlewares);
}

/**
 * @return array<int, object>
 * @phpstan-ignore-next-line
 */
function getAttributes(
    ReflectionClass|ReflectionFunction|ReflectionMethod|ReflectionProperty|ReflectionParameter|ReflectionClassConstant $reflection,
    string $attribute
): array {
    $attributes = $reflection->getAttributes($attribute);
    $return = [];
    if ($attributes === []) {
        return $return;
    }
    /**
     * @var ReflectionAttribute<Attribute> $attribute
     */
    foreach ($attributes as $attribute) {
        $return[] = $attribute->newInstance();
    }

    return $return;
}

function getHeaders(string $className): Headers
{
    $attributes = getAttributes(
        // @phpstan-ignore-next-line
        new ReflectionClass($className),
        Header::class
    );
    // @phpstan-ignore-next-line
    return new Headers(...$attributes);
}

function getStatus(string $className): Status
{
    // @phpstan-ignore-next-line
    $reflection = new ReflectionClass($className);
    // @phpstan-ignore-next-line
    return getAttribute($reflection, Status::class);
}
