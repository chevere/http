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

namespace Chevere\Http\Traits;

use Chevere\Http\Interfaces\MiddlewareNameInterface;
use Chevere\Http\MiddlewareName;

trait MiddlewareNameWithArgumentsTrait
{
    public static function middlewareName(mixed ...$arguments): MiddlewareNameInterface
    {
        return new MiddlewareName(static::class, ...$arguments); // @phpstan-ignore-line
    }
}
