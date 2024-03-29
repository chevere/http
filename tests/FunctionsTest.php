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
use function Chevere\Http\middlewares;
use function Chevere\Http\requestAttribute;
use function Chevere\Http\responseAttribute;

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
        $request = requestAttribute(NullController::class);
        $this->assertCount(0, $request->headers);
        $request = requestAttribute(AcceptController::class);
        $header = new Header('foo', 'bar');
        $this->assertEquals(
            [
                $header->line,
            ],
            $request->headers->toArray()
        );
    }

    public function testGetResponse(): void
    {
        $response = responseAttribute(NullController::class);
        $attribute = new Status();
        $this->assertEquals($attribute, $response->status);
        $this->assertCount(0, $response->headers);
        $response = responseAttribute(AcceptController::class);
        $this->assertSame(200, $response->status->primary);
        $this->assertSame([400], $response->status->other);
        $contentDisposition = new Header('Content-Disposition', 'attachment');
        $contentType = new Header('Content-Type', 'text/html; charset=UTF-8');
        $contentType2 = new Header('Content-Type', 'multipart/form-data; boundary=something');
        $this->assertEquals(
            [
                $contentDisposition->line,
                $contentType->line,
                $contentType2->line,
            ],
            $response->headers->toArray()
        );
    }
}
