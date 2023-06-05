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

namespace Chevere\Tests\Attributes;

use Chevere\Http\Attributes\Headers;
use Chevere\Throwable\Errors\TypeError;
use PHPUnit\Framework\TestCase;

final class HeadersTest extends TestCase
{
    public function testEmpty(): void
    {
        $headers = new Headers([]);
        $this->assertSame([], $headers->array);
    }

    public function testInvalidKey(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Header key must be a string at index 0');
        new Headers([
            123 => 'value',
        ]);
    }

    public function testInvalidValue(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Header value must be a string for llave at index 0');
        new Headers([
            'llave' => 123,
        ]);
    }

    public function testInvalidPosition(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Header key must be a string at index 1');
        new Headers([
            'llave' => 'valor',
            123 => 'value',
        ]);
    }
}
