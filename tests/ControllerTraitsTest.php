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

use Chevere\Http\Traits\ResponseHtmlTrait;
use Chevere\Tests\_resources\TestHttpController;
use PHPUnit\Framework\TestCase;

final class ControllerTraitsTest extends TestCase
{
    public function testResponseHtmlTrait(): void
    {
        $class = new class() extends TestHttpController {
            use ResponseHtmlTrait;
        };
        $this->assertSame(
            'text/html; charset=utf-8',
            $class->responseHeaders()['Content-Type']
        );
    }
}
