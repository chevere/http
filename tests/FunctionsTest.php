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

namespace Chevere\Tests;

use Chevere\Http\Header;
use Chevere\Http\MiddlewareNameWithSetUp;
use Chevere\Http\Middlewares;
use Chevere\Tests\src\AcceptController;
use Chevere\Tests\src\Middleware;
use Chevere\Tests\src\NullController;
use PHPUnit\Framework\TestCase;
use function Chevere\Http\middlewares;
use function Chevere\Http\requestAttribute;
use function Chevere\Http\responseAttribute;

final class FunctionsTest extends TestCase
{
    public function testMiddlewares(): void
    {
        $middleware = Middleware::class;
        $name1 = new MiddlewareNameWithSetUp($middleware);
        $name2 = new MiddlewareNameWithSetUp($middleware);
        $middlewares = middlewares($middleware, $name2);
        $new = new Middlewares($name1, $name2);
        $this->assertEquals($new, $middlewares);
    }

    public function testRequestAttribute(): void
    {
        $request = requestAttribute(AcceptController::class);
        $header = new Header('foo', 'bar');
        $this->assertEquals(
            [
                $header->line,
            ],
            $request->headers->toLines()
        );
    }

    public function testResponseAttribute(): void
    {
        $response = responseAttribute(NullController::class);
        $this->assertNull($response);
        $response = responseAttribute(AcceptController::class);
        $this->assertSame(200, $response->status->success()->int());
        $this->assertSame([400], $response->status->codes());
        $contentDisposition = new Header('Content-Disposition', 'attachment');
        $contentType = new Header('Content-Type', 'text/html; charset=UTF-8');
        $contentType2 = new Header('Content-Type', 'multipart/form-data; boundary=something');
        $this->assertEquals(
            [
                $contentDisposition->line,
                $contentType->line,
                $contentType2->line,
            ],
            $response->headers->toLines()
        );
    }
}
