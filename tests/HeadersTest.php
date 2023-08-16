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
use Chevere\Http\Headers;
use PHPUnit\Framework\TestCase;

final class HeadersTest extends TestCase
{
    public function testConstruct(): void
    {
        $headers = new Headers();
        $this->assertCount(0, $headers);
    }

    public function testHeaders(): void
    {
        $header1 = new Header('foo', 'bar');
        $header2 = new Header('foo', 'baz');
        $headers = new Headers($header1, $header2);
        $this->assertCount(2, $headers);
        $this->assertSame([
            'foo: bar',
            'foo: baz',
        ], $headers->toArray());
    }
}
