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

use function Chevere\Common\assertClassName;
use Chevere\Http\Interfaces\HttpControllerInterface;
use Chevere\Http\Interfaces\HttpControllerNameInterface;

final class HttpControllerName implements HttpControllerNameInterface
{
    public function __construct(
        private string $name
    ) {
        assertClassName(HttpControllerInterface::class, $name);
    }

    public function __toString(): string
    {
        /**
         * @var class-string HttpControllerInterface
         */
        return $this->name;
    }
}