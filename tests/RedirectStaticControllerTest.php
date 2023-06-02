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

use Chevere\Http\RedirectStaticController;
use Chevere\Throwable\Exceptions\InvalidArgumentException;
use Chevere\Throwable\Exceptions\LogicException;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;

final class RedirectStaticControllerTest extends TestCase
{
    public function testDefault(): void
    {
        $controller = new RedirectStaticController();
        $this->assertSame(302, $controller->status());
        $this->expectException(LogicException::class);
        $controller->uri();
    }

    public function testWithUri(): void
    {
        $controller = new RedirectStaticController();
        $uri = new Uri('https://chevere.org');
        $controllerWithUri = $controller->withUri($uri);
        $this->assertSame(
            [
                'uri' => $controllerWithUri->uri(),
                'status' => $controllerWithUri->status(),
            ],
            $controllerWithUri->getResponse()->data()
        );
        $this->assertNotSame($controller, $controllerWithUri);
        $this->assertNotEquals($controller, $controllerWithUri);
        $this->assertSame($uri, $controllerWithUri->uri());
    }

    public function testWithStatus(): void
    {
        $status = 301;
        $controller = new RedirectStaticController();
        $controllerWitStatus = $controller->withStatus($status);
        $this->assertNotSame($controller, $controllerWitStatus);
        $this->assertNotEquals($controller, $controllerWitStatus);
        $this->assertSame($status, $controllerWitStatus->status());
        $this->expectException(InvalidArgumentException::class);
        $controller->withStatus(200);
    }
}
