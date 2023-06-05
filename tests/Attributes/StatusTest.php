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

use Chevere\Http\Attributes\Status;
use PHPUnit\Framework\TestCase;

final class StatusTest extends TestCase
{
    public function testDefault(): void
    {
        $default = new Status();
        $this->assertSame(200, $default->primary);
        $this->assertSame([], $default->other);
    }

    public function testPrimary(): void
    {
        $primary = new Status(200);
        $this->assertSame(200, $primary->primary);
        $this->assertSame([], $primary->other);
    }

    public function testPrimaryOverride(): void
    {
        $status = new Status(200, 200);
        $this->assertSame(200, $status->primary);
        $this->assertSame([], $status->other);
    }

    public function testOther(): void
    {
        $status = new Status(201, 400);
        $this->assertSame(201, $status->primary);
        $this->assertSame([400], $status->other);
    }

    public function testOtherOverride(): void
    {
        $status = new Status(200, 400, 400);
        $this->assertSame(200, $status->primary);
        $this->assertSame([400], $status->other);
    }
}
