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

use Chevere\Http\Attributes\Statuses;
use PHPUnit\Framework\TestCase;

final class StatusesTest extends TestCase
{
    public function testDefault(): void
    {
        $default = new Statuses();
        $this->assertSame(200, $default->primary);
        $this->assertSame([], $default->other);
    }

    public function testPrimary(): void
    {
        $primary = new Statuses(200);
        $this->assertSame(200, $primary->primary);
        $this->assertSame([], $primary->other);
    }

    public function testPrimaryOverride(): void
    {
        $statuses = new Statuses(200, 200);
        $this->assertSame(200, $statuses->primary);
        $this->assertSame([], $statuses->other);
    }

    public function testOther(): void
    {
        $statuses = new Statuses(201, 400);
        $this->assertSame(201, $statuses->primary);
        $this->assertSame([400], $statuses->other);
    }

    public function testOtherOverride(): void
    {
        $statuses = new Statuses(200, 400, 400);
        $this->assertSame(200, $statuses->primary);
        $this->assertSame([400], $statuses->other);
    }
}
