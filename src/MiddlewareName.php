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

use Chevere\Http\Interfaces\MiddlewareNameInterface;
use InvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;

final class MiddlewareName implements MiddlewareNameInterface
{
    public function __construct(
        private string $name
    ) {
        if (is_subclass_of($this->name, MiddlewareInterface::class)) {
            return;
        }

        throw new InvalidArgumentException();
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
