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

use Stringable;

/**
 * Describes the component in charge of defining an HTTP header according to RFC 7230.
 */
interface HeaderInterface extends Stringable
{
    public function name(): string;

    public function value(): string;
}
