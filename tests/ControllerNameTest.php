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

use Chevere\Http\ControllerName;
use Chevere\Http\Interfaces\ControllerInterface;
use Chevere\Tests\src\NullController;
use Chevere\Tests\src\WrongController;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ControllerNameTest extends TestCase
{
    public function testControllerNotHttp(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            HTTP Controller `Chevere\Tests\src\WrongController` doesn't implement `Chevere\Http\Interfaces\ControllerInterface`
            PLAIN
        );
        new ControllerName(WrongController::class);
    }

    public function testConstruct(): void
    {
        $name = NullController::class;
        $controllerName = new ControllerName($name);
        $this->assertSame($name, $controllerName->__toString());
    }

    public function testInterface(): void
    {
        $this->assertSame(
            'HTTP Controller',
            ControllerName::symbol()
        );
        $this->assertSame(
            ControllerInterface::class,
            ControllerName::interface()
        );
    }
}
