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

namespace Chevere\Tests\src;

use Chevere\Http\Attributes\Description;
use Chevere\Http\Attributes\Request;
use Chevere\Http\Attributes\Response;
use Chevere\Http\Header;
use Chevere\Http\Status;

#[Description('This is a description')]
#[Request(
    new Header('foo', 'bar'),
)]
#[Response(
    new Status(200, 400),
    new Header('Content-Disposition', 'attachment'),
    new Header('Content-Type', 'text/html; charset=UTF-8'),
)]
final class UsesAttributes
{
}
