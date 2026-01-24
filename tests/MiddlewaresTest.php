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

use Chevere\Http\Interfaces\MiddlewareNameInterface;
use Chevere\Http\MiddlewareNameWithArguments;
use Chevere\Http\Middlewares;
use Chevere\Tests\src\Middleware;
use Chevere\Tests\src\MiddlewareAlt;
use PHPUnit\Framework\TestCase;

final class MiddlewaresTest extends TestCase
{
    public function testConstructEmpty(): void
    {
        $middlewares = new Middlewares();
        $this->assertCount(0, $middlewares);
        $this->assertFalse($middlewares->has(Middleware::class));
    }

    public function testConstructValues(): void
    {
        $middleware = Middleware::class;
        $name = new MiddlewareNameWithArguments($middleware);
        $nameAlt = new MiddlewareNameWithArguments($middleware);
        $middlewares = new Middlewares($name, $nameAlt);
        $this->assertCount(2, $middlewares);
        $this->assertSame([0, 1], $middlewares->keys());
        $this->assertSame(
            [$name, $nameAlt],
            iterator_to_array($middlewares->getIterator())
        );
        $this->assertTrue($middlewares->has($middleware, $nameAlt));
    }

    public function testWithAppend(): void
    {
        $middlewareTest = new MiddlewareNameWithArguments(Middleware::class);
        $middlewareAlt = new MiddlewareNameWithArguments(MiddlewareAlt::class);
        $middlewares = new Middlewares();
        $middlewaresWith = $middlewares->withAppend($middlewareTest, $middlewareAlt);
        $this->assertNotSame($middlewares, $middlewaresWith);
        $this->assertTrue($middlewaresWith->has(MiddlewareAlt::class, Middleware::class));
        $this->assertCount(2, $middlewaresWith);
        $this->assertSame([0, 1], $middlewaresWith->keys());
        $array = array_map(
            function (MiddlewareNameInterface $middleware) {
                return $middleware::class;
            },
            iterator_to_array($middlewaresWith->getIterator())
        );
        $this->assertSame(
            [$middlewareTest::class, $middlewareAlt::class],
            $array
        );
    }

    public function testWithPrepend(): void
    {
        $middlewareTest = new MiddlewareNameWithArguments(Middleware::class);
        $middlewareAlt = new MiddlewareNameWithArguments(MiddlewareAlt::class);
        $middlewares = new Middlewares();
        $middlewaresWith = $middlewares->withPrepend($middlewareTest, $middlewareAlt);
        $this->assertNotSame($middlewares, $middlewaresWith);
        $this->assertCount(2, $middlewaresWith);
        $this->assertSame([0, 1], $middlewaresWith->keys());
        $array = array_map(
            function (MiddlewareNameInterface $middleware) {
                return $middleware::class;
            },
            iterator_to_array($middlewaresWith->getIterator())
        );
        $this->assertSame(
            [$middlewareAlt::class, $middlewareTest::class],
            $array
        );
    }
}
