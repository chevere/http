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

namespace Chevere\Http\Interfaces;

use Countable;
use IteratorAggregate;
use OutOfBoundsException;

/**
 * @extends IteratorAggregate<int>
 */
interface StatusInterface extends IteratorAggregate, Countable
{
    /**
     * Provides access to named code.
     *
     * @throws OutOfBoundsException If the name is not defined
     */
    public function code(string|int $name): int;

    /**
     * @return array<string|int, int>
     */
    public function codes(): array;
}
