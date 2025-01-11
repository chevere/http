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

use Chevere\Http\Attributes\Request;
use Chevere\Http\Header;
use Chevere\Http\Headers;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testConstructEmpty(): void
    {
        $request = new Request();
        $this->assertCount(0, $request->headers);
    }

    /**
     * @dataProvider provideConstructWithHeaders
     */
    public function testConstructWithHeaders(
        array $headers,
        array $expectLines
    ): void {
        $objects = [];
        foreach ($headers as $name => $header) {
            $objects[] = new Header($name, $header);
        }
        $request = new Request(...$objects);
        $this->assertCount(count($headers), $request->headers);
        $array =
        $this->assertSame(
            $headers,
            $request->headers->toArray()
        );
        $this->assertSame(
            $expectLines,
            $request->headers->toLines()
        );
    }

    public static function provideConstructWithHeaders(): array
    {
        return [
            [
                [
                    'Content-Type' => 'application/json',
                    'X-Key' => 'x-value',
                ],
                [
                    'Content-Type: application/json',
                    'X-Key: x-value',
                ],
            ],
        ];
    }

    public function testHeadersInstance(): void
    {
        $request = new Request();
        $this->assertInstanceOf(Headers::class, $request->headers);
    }
}
