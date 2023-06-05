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

use Chevere\Http\Attributes\Headers;
use Chevere\Http\Attributes\Status;
use function Chevere\Http\classHeaders;
use function Chevere\Http\classStatus;
use Chevere\Http\MiddlewareName;
use function Chevere\Http\middlewares;
use Chevere\Http\Middlewares;
use Chevere\Tests\_resources\Middleware;
use Chevere\Tests\_resources\NullController;
use PHPUnit\Framework\TestCase;

final class FunctionsTest extends TestCase
{
    public function testMiddlewares(): void
    {
        $middleware = Middleware::class;
        $name = new MiddlewareName($middleware);
        $middlewares = middlewares($middleware);
        $new = new Middlewares($name);
        $this->assertEquals($new, $middlewares);
    }

    public function testClassHeaders(): void
    {
        $headers = classHeaders(NullController::class);
        $attribute = new Headers();
        $this->assertEquals($attribute, $headers);
    }

    public function testClassStatus(): void
    {
        $status = classStatus(NullController::class);
        $attribute = new Status();
        $this->assertEquals($attribute, $status);
    }
}
