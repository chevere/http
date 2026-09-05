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

use Chevere\Parameter\Interfaces\TypedInterface;
use Countable;
use IteratorAggregate;

/**
 * @extends IteratorAggregate<int|string>
 */
interface StatusInterface extends IteratorAggregate, Countable
{
    /**
     * Provides access to the success status code.
     */
    public function success(): TypedInterface;

    /**
     * Provides access to named codes.
     */
    public function code(string $name): TypedInterface;

    /**
     * @return array<int|string>
     */
    public function codes(): array;
}
