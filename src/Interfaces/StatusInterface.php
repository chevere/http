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

use IteratorAggregate;

/**
 * @extends IteratorAggregate<int>
 */
interface StatusInterface extends IteratorAggregate
{
    /**
     * Provides access to the success status code.
     */
    public function success(): int;

    /**
     * Provides access to the additional named codes.
     */
    public function code(string $name): int;

    /**
     * @return array<int>
     */
    public function toArray(): array;
}
