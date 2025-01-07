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

use Chevere\Http\Attributes\Response;
use Chevere\Http\Controller;
use Chevere\Http\Status;

#[Response(
    new Status(405),
)]
final class MethodNotAllowedController extends Controller
{
    protected function main(): void
    {
    }
}
