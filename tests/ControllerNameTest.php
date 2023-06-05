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
use Chevere\Tests\_resources\NullController;
use Chevere\Tests\_resources\WrongController;
use Chevere\Throwable\Errors\TypeError;
use PHPUnit\Framework\TestCase;
use Throwable;

final class ControllerNameTest extends TestCase
{
    public function testInvalid(): void
    {
        $this->expectException(Throwable::class);
        new ControllerName('');
    }

    public function testControllerNotHttp(): void
    {
        $this->expectException(TypeError::class);
        new ControllerName(WrongController::class);
    }

    public function testConstruct(): void
    {
        $name = NullController::class;
        $controllerName = new ControllerName($name);
        $this->assertSame($name, $controllerName->__toString());
    }
}
