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

use Chevere\Http\MiddlewareNameWithSetUp;
use Chevere\Tests\src\Middleware;
use Chevere\Tests\src\MiddlewareAlt;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Throwable;

final class MiddlewareNameTest extends TestCase
{
    public function testInvalid(): void
    {
        $this->expectException(Throwable::class);
        new MiddlewareNameWithSetUp('');
    }

    public function testConstruct(): void
    {
        $middleware = Middleware::class;
        $name = new MiddlewareNameWithSetUp($middleware);
        $this->assertSame($middleware, $name->__toString());
        $this->assertSame([], $name->arguments());
    }

    public function testConstructArgumentsNoSetup(): void
    {
        $middleware = Middleware::class;
        $arguments = ['arg1', 'arg2'];
        $name = new MiddlewareNameWithSetUp($middleware, ...$arguments);
        $this->assertSame([], $name->arguments());
    }

    public function testInterface(): void
    {
        $this->assertSame(
            MiddlewareInterface::class,
            MiddlewareNameWithSetUp::interface()
        );
    }

    /**
     * @dataProvider provideConstructArguments
     */
    public function testConstructArgumentsSetup(
        string $middleware,
        array $arguments,
        array $expectedArguments
    ): void {
        $name = new MiddlewareNameWithSetUp($middleware, ...$arguments);
        $this->assertSame($expectedArguments, $name->arguments());
    }

    public static function provideConstructArguments(): array
    {
        return [
            [
                MiddlewareAlt::class,
                [
                    'foo',
                    123,
                ],
                [
                    'foo',
                    123,
                ],
            ],
            [
                MiddlewareAlt::class,
                [
                    'test' => 'foo',
                    'code' => 123,
                ],
                [
                    'test' => 'foo',
                    'code' => 123,
                ],
            ],
        ];
    }
}
