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

use Chevere\Http\Attributes\Description;
use PHPUnit\Framework\TestCase;

final class DescriptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $description = 'Test description';
        $attribute = new Description($description);
        $this->assertSame($description, $attribute->description);
    }

    public function testConstructDefault(): void
    {
        $attribute = new Description();
        $this->assertSame('', $attribute->description);
    }

    public function testToString(): void
    {
        $description = 'Test description';
        $attribute = new Description($description);
        $this->assertSame($description, (string) $attribute);
    }
}
