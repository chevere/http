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

use Chevere\Http\MiddlewareName;
use Chevere\Http\MiddlewareNameWithoutSetup;
use Chevere\Tests\src\Middleware;
use Chevere\Tests\src\MiddlewareWithSetup;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Throwable;

final class MiddlewareNameTest extends TestCase
{
    #[DataProvider('provideClasses')]
    public function testInvalid(string $className): void
    {
        $this->expectException(Throwable::class);
        new $className($className);
    }

    public function testConstructArgumentsNoSetup(): void
    {
        $middleware = Middleware::class;
        $arguments = ['arg1', 'arg2'];
        $name = new MiddlewareName($middleware, ...$arguments);
        $this->assertSame([], $name->arguments());
    }

    #[DataProvider('provideClasses')]
    public function testInterface(string $className): void
    {
        $this->assertSame(
            MiddlewareInterface::class,
            $className::interface()
        );
    }

    public static function provideClasses(): array
    {
        return [
            [MiddlewareName::class],
            [MiddlewareNameWithoutSetup::class],
        ];
    }

    #[DataProvider('provideConstructArguments')]
    public function testConstructArgumentsSetup(
        string $middleware,
        array $arguments,
        array $expectedArguments
    ): void {
        $name = new MiddlewareName($middleware, ...$arguments);
        $this->assertSame($expectedArguments, $name->arguments());
        $withoutSetup = new MiddlewareNameWithoutSetup($middleware);
        $this->assertSame([], $withoutSetup->arguments());
    }

    public static function provideConstructArguments(): array
    {
        return [
            [
                MiddlewareWithSetup::class,
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
                MiddlewareWithSetup::class,
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
