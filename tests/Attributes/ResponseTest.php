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

use Chevere\Http\Attributes\Response;
use Chevere\Http\Header;
use Chevere\Http\Status;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testConstructEmpty(): void
    {
        $response = new Response();
        $this->assertSame(200, $response->status->success());
        $status = new Status();
        $this->assertCount(0, $response->headers);
        $this->assertEquals($status, $response->status);
        $this->assertCount(1, $response);
        $this->assertEquals(
            [
                'status' => $status,
            ],
            $response->toArray()
        );
    }

    public function testConstruct(): void
    {
        $status = new Status(400);
        $headerDisposition = new Header('Content-Disposition', 'attachment');
        $response = new Response($status, $headerDisposition);
        $this->assertCount(2, $response);
        $this->assertEquals($status, $response->status);
        $this->assertEquals(
            [
                'status' => $status,
                'Content-Disposition' => $headerDisposition,
            ],
            $response->toArray()
        );
    }
}
