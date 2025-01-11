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
use Chevere\Http\Status;
use Chevere\Tests\src\UsesAttributes;
use PHPUnit\Framework\TestCase;
use function Chevere\Http\descriptionAttribute;
use function Chevere\Http\requestAttribute;
use function Chevere\Http\responseAttribute;

final class UsesAttributesTest extends TestCase
{
    public function testDescription(): void
    {
        $description = descriptionAttribute(UsesAttributes::class);
        $this->assertEquals(
            new Description('This is a description'),
            $description->__toString()
        );
    }

    public function testRequest(): void
    {
        $request = requestAttribute(UsesAttributes::class);
        $this->assertEquals(
            new Request(
                new Header('foo', 'bar'),
            ),
            $request
        );
    }

    public function testResponse(): void
    {
        $response = responseAttribute(UsesAttributes::class);
        $status = new Status(200, 400);
        $headerDisposition = new Header('Content-Disposition', 'attachment');
        $headerType = new Header('Content-Type', 'text/html; charset=UTF-8');
        $this->assertEquals(
            new Response($status, $headerDisposition, $headerType),
            $response
        );
        $this->assertCount(3, $response);
        $this->assertEquals(
            [
                'status' => $status,
                'Content-Disposition' => $headerDisposition,
                'Content-Type' => $headerType,
            ],
            $response->toArray()
        );
    }
}
