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

use Chevere\Http\Status;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class StatusTest extends TestCase
{
    public function testDefault(): void
    {
        $status = new Status();
        $this->assertSame(200, $status->success());
        $this->assertSame([], $status->codes());
        $this->assertSame([200], $status->toArray());
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Status `missing` is not defined
            PLAIN
        );
        $status->code('missing');
    }

    public function testPrimary(): void
    {
        $status = new Status(200);
        $this->assertSame(200, $status->success());
        $this->assertSame([], $status->codes());
        $this->assertSame([200], $status->toArray());
    }

    public function testPrimaryOverride(): void
    {
        $status = new Status(200, 200);
        $this->assertSame(200, $status->success());
        $this->assertSame([], $status->codes());
        $this->assertSame([200], $status->toArray());
    }

    public function testOther(): void
    {
        $status = new Status(201, 400);
        $this->assertSame(201, $status->success());
        $this->assertSame([400], $status->codes());
        $this->assertSame([201, 400], $status->toArray());
    }

    public function testOtherOverride(): void
    {
        $status = new Status(200, 400, 400);
        $this->assertSame(200, $status->success());
        $this->assertSame(400, $status->code('0'));
        $this->assertSame([400], $status->codes());
        $this->assertSame([200, 400], $status->toArray());
    }

    public function testOtherNamed(): void
    {
        $status = new Status(200, bad: 400, notFound: 404);
        $this->assertSame(200, $status->success());
        $this->assertSame(
            [
                'bad' => 400,
                'notFound' => 404,
            ],
            $status->codes()
        );
        $this->assertSame([200, 400, 404], $status->toArray());
        $this->assertSame(400, $status->code('bad'));
        $this->assertSame(404, $status->code('notFound'));
    }
}
