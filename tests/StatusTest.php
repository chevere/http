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
use OverflowException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class StatusTest extends TestCase
{
    public function testDefault(): void
    {
        $status = new Status();
        $this->assertSame(200, $status->code(0));
        $this->assertSame([200], $status->codes());
        $this->assertSame([200], [...$status]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Status `missing` is not defined
            PLAIN
        );
        $status->code('missing');
    }

    public function testCode(): void
    {
        $status = new Status(204);
        $this->assertSame(204, $status->code(0));
        $this->assertSame([204], $status->codes());
        $this->assertSame([204], [...$status]);
    }

    public function testCodeOverride(): void
    {
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Status `200` is already defined
            PLAIN
        );
        new Status(200, 200);
    }

    public function testAdditional(): void
    {
        $status = new Status(201, 400);
        $this->assertSame(201, $status->code(0));
        $this->assertSame(400, $status->code(1));
        $this->assertSame([201, 400], $status->codes());
        $this->assertSame([201, 400], [...$status]);
    }

    public function testAdditionalOverride(): void
    {
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Status `400` is already defined
            PLAIN
        );
        new Status(200, 400, 400);
    }

    public function testAdditionalNamed(): void
    {
        $status = new Status(200, bad: 400, notFound: 404);
        $this->assertSame(
            [
                0 => 200,
                'bad' => 400,
                'notFound' => 404,
            ],
            $status->codes(),
        );
        $this->assertSame([200, 400, 404], [...$status]);
        $this->assertSame(200, $status->code(0));
        $this->assertSame(400, $status->code('bad'));
        $this->assertSame(404, $status->code('notFound'));
    }
}
