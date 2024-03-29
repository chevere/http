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

use Chevere\Http\Interfaces\ControllerInterface;
use Chevere\Http\Interfaces\ControllerNameInterface;
use InvalidArgumentException;

final class ControllerName implements ControllerNameInterface
{
    public function __construct(
        private string $name
    ) {
        if (is_subclass_of($this->name, ControllerInterface::class)) {
            return;
        }

        throw new InvalidArgumentException();
    }

    public function __toString(): string
    {
        /**
         * @var class-string HttpControllerInterface
         */
        return $this->name;
    }
}
