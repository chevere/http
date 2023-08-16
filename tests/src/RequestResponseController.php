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

use Chevere\Http\Attributes\Request;
use Chevere\Http\Attributes\Response;
use Chevere\Http\Controller;
use Chevere\Http\Header;
use Chevere\Http\Status;

#[
    Request(
        new Header('Accept-Language', 'en-US,en;q=0.5'),
        new Header('Accept', 'application/json'),
    ),
    Response(
        new Status(200, 201, 202),
        new Header('Content-Type', 'application/json'),
        new Header('Etag', 'c561c68d0ba92bbeb8b0f612a9199f722e3a621a'),
    )
]
class RequestResponseController extends Controller
{
    public function run(): array
    {
        return [];
    }
}
