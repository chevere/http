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

use Chevere\Http\HttpControllerName;
use Chevere\Tests\_resources\TestController;
use Chevere\Tests\_resources\TestHttpController;
use Chevere\Throwable\Errors\TypeError;
use PHPUnit\Framework\TestCase;
use Throwable;

final class HttpControllerNameTest extends TestCase
{
    public function testInvalid(): void
    {
        $this->expectException(Throwable::class);
        new HttpControllerName('');
    }

    public function testControllerNotHttp(): void
    {
        $this->expectException(TypeError::class);
        new HttpControllerName(TestController::class);
    }

    public function testConstruct(): void
    {
        $name = TestHttpController::class;
        $controllerName = new HttpControllerName($name);
        $this->assertSame($name, $controllerName->__toString());
    }
}
