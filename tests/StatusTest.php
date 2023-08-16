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

final class StatusTest extends TestCase
{
    public function testDefault(): void
    {
        $status = new Status();
        $this->assertSame(200, $status->primary);
        $this->assertSame([], $status->other);
        $this->assertSame([200], $status->toArray());
    }

    public function testPrimary(): void
    {
        $status = new Status(200);
        $this->assertSame(200, $status->primary);
        $this->assertSame([], $status->other);
        $this->assertSame([200], $status->toArray());
    }

    public function testPrimaryOverride(): void
    {
        $status = new Status(200, 200);
        $this->assertSame(200, $status->primary);
        $this->assertSame([], $status->other);
        $this->assertSame([200], $status->toArray());
    }

    public function testOther(): void
    {
        $status = new Status(201, 400);
        $this->assertSame(201, $status->primary);
        $this->assertSame([400], $status->other);
        $this->assertSame([201, 400], $status->toArray());
    }

    public function testOtherOverride(): void
    {
        $status = new Status(200, 400, 400);
        $this->assertSame(200, $status->primary);
        $this->assertSame([400], $status->other);
        $this->assertSame([200, 400], $status->toArray());
    }
}
