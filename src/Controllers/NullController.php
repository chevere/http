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

namespace Chevere\Http\Controllers;

use Chevere\Http\Controller;

/**
 * Null controller doesn't do anything.
 *
 * @codeCoverageIgnore
 */
class NullController extends Controller
{
    final public function __invoke(): void
    {
    }
}
