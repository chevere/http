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
use Chevere\Http\MiddlewareName;
use Chevere\Http\Middlewares;
use Chevere\Http\Status;
use Chevere\Tests\src\AcceptController;
use Chevere\Tests\src\Middleware;
use Chevere\Tests\src\NullController;
use PHPUnit\Framework\TestCase;
use function Chevere\Http\getRequest;
use function Chevere\Http\getResponse;
use function Chevere\Http\middlewares;

final class FunctionsTest extends TestCase
{
    public function testMiddlewares(): void
    {
        $middleware = Middleware::class;
        $name = new MiddlewareName($middleware);
        $middlewares = middlewares($middleware);
        $new = new Middlewares($name);
        $this->assertEquals($new, $middlewares);
    }

    public function testGetRequest(): void
    {
        $request = getRequest(NullController::class);
        $this->assertCount(0, $request->headers());
        $request = getRequest(AcceptController::class);
        $header = new Header('foo', 'bar');
        $this->assertEquals(
            [
                $header->name => $header,
            ],
            $request->headers()->toArray()
        );
    }

    public function testGetResponse(): void
    {
        $response = getResponse(NullController::class);
        $attribute = new Status();
        $this->assertEquals($attribute, $response->status);
        $this->assertCount(0, $response->headers());
        $response = getResponse(AcceptController::class);
        $this->assertSame(200, $response->status->primary);
        $this->assertSame([400], $response->status->other);
        $contentDisposition = new Header('Content-Disposition', 'attachment');
        $contentType = new Header('Content-Type', 'application/json');
        $this->assertEquals(
            [
                $contentDisposition->name => $contentDisposition,
                $contentType->name => $contentType,
            ],
            $response->headers()->toArray()
        );
        $this->assertSame(
            [
                $contentDisposition->name => $contentDisposition->value,
                $contentType->name => $contentType->value,
            ],
            $response->headers()->toExport()
        );
    }
}
