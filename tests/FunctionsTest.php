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

use Chevere\Http\Attributes\Description;
use Chevere\Http\Attributes\Request;
use Chevere\Http\Attributes\Response;
use Chevere\Http\Header;
use Chevere\Http\Interfaces\RequestInterface;
use Chevere\Http\Interfaces\ResponseInterface;
use Chevere\Http\MiddlewareName;
use Chevere\Http\MiddlewareNameWithoutSetup;
use Chevere\Http\Middlewares;
use Chevere\Http\Status;
use Chevere\Tests\src\AcceptController;
use Chevere\Tests\src\Middleware;
use Chevere\Tests\src\MiddlewareWithSetup;
use Chevere\Tests\src\NullController;
use PHPUnit\Framework\TestCase;
use function Chevere\Http\descriptionAttribute;
use function Chevere\Http\middlewareNames;
use function Chevere\Http\middlewares;
use function Chevere\Http\requestAttribute;
use function Chevere\Http\responseAttribute;

final class FunctionsTest extends TestCase
{
    public function testMiddlewares(): void
    {
        $middleware = Middleware::class;
        $name1 = new MiddlewareName($middleware);
        $name2 = new MiddlewareName($middleware);
        $middlewares = middlewares($middleware, $name2);
        $new = new Middlewares($name1, $name2);
        $this->assertEquals($new, $middlewares);
    }

    public function testDescriptionAttribute(): void
    {
        $object = new #[Description('The description')] class() {
            public function __invoke(): Description
            {
                return descriptionAttribute();
            }
        };
        $this->assertSame(
            'The description',
            $object->__invoke()
                ->__toString()
        );
        $description = descriptionAttribute(NullController::class);
        $this->assertNull($description);
        $description = descriptionAttribute(AcceptController::class);
        $this->assertSame(
            'This is a description',
            $description->__toString()
        );
    }

    public function testRequestAttribute(): void
    {
        $object = new #[Request(new Header('foo', 'bar'))] class() {
            public function __invoke(): RequestInterface
            {
                return requestAttribute();
            }
        };
        $this->assertSame(
            ['foo: bar'],
            $object->__invoke()
                ->headers()
                ->toLines()
        );
        $request = requestAttribute(NullController::class);
        $this->assertNull($request);
        $request = requestAttribute(AcceptController::class);
        $header = new Header('foo', 'bar');
        $this->assertEquals(
            [
                $header->__toString(),
            ],
            $request->headers()
                ->toLines()
        );
    }

    public function testResponseAttribute(): void
    {
        $object = new #[Response(new Status(204))] class() {
            public function __invoke(): ResponseInterface
            {
                return responseAttribute();
            }
        };
        $this->assertSame(
            204,
            $object->__invoke()
                ->status()
                ->success()
                ->int()
        );
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
                $contentDisposition->__toString(),
                $contentType->__toString(),
                $contentType2->__toString(),
            ],
            $response->headers()
                ->toLines()
        );
    }

    public function testMiddlewareNames(): void
    {
        $middlewares = middlewareNames();
        $this->assertCount(0, $middlewares);
        $middlewares = middlewareNames(Middleware::class, MiddlewareWithSetup::class);
        $this->assertCount(2, $middlewares);
        foreach ($middlewares as $middleware) {
            $this->assertInstanceOf(MiddlewareNameWithoutSetup::class, $middleware);
        }
    }
}
