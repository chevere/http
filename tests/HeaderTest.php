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
use PHPUnit\Framework\TestCase;

final class HeaderTest extends TestCase
{
    public function testConstruct(): void
    {
        $name = 'foo';
        $value = 'bar';
        $headers = new Header($name, $value);
        $this->assertSame($name, $headers->name);
        $this->assertSame($value, $headers->value);
        $this->assertSame("{$name}: {$value}", $headers->line);
    }
}
