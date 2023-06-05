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

function classStatuses(string $className): Statuses
{
    // @phpstan-ignore-next-line
    $reflection = new ReflectionClass($className);
    $attributes = $reflection->getAttributes(Statuses::class);
    if ($attributes === []) {
        return new Statuses(200);
    }
    /** @var Statuses */
    return $attributes[0]->newInstance();
}
