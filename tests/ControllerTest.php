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

use Chevere\Tests\src\AcceptController;
use Chevere\Tests\src\AcceptOptionalController;
use Chevere\Tests\src\NullController;
use Chevere\Throwable\Errors\ArgumentCountError;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ControllerTest extends TestCase
{
    public function testDefaults(): void
    {
        $controller = new NullController();
        $this->assertCount(0, $controller->acceptQuery()->parameters());
        $this->assertCount(0, $controller->acceptBody()->parameters());
        $this->assertCount(0, $controller->acceptFiles()->parameters());
        $this->assertCount(0, $controller->query()->parameters());
        $this->assertCount(0, $controller->body()->parameters());
    }

    public function testAcceptQueryParameters(): void
    {
        $controller = new AcceptController();
        $controllerWith = $controller->withQuery([
            'foo' => 'abc',
        ]);
        $this->assertNotSame($controller, $controllerWith);
        $this->assertNotEquals($controller, $controllerWith);
        $this->assertSame('abc', $controllerWith->query()->required('foo')->string());
        $this->expectException(InvalidArgumentException::class);
        $controller->withQuery([
            'foo' => '123',
        ]);
    }

    public function testAcceptQueryParametersOptional(): void
    {
        $controller = new AcceptOptionalController();
        $this->assertSame([], $controller->query()->toArray());
        $controllerWith = $controller->withQuery([
            'foo' => 'abc',
        ]);
        $this->assertNotSame($controller, $controllerWith);
        $this->assertNotEquals($controller, $controllerWith);
        $this->assertSame('abc', $controllerWith->query()->optional('foo')->string());
    }

    public function testAcceptBodyParameters(): void
    {
        $controller = new AcceptController();
        $controllerWith = $controller->withBody([
            'bar' => '123',
        ]);
        $this->assertNotSame($controller, $controllerWith);
        $this->assertNotEquals($controller, $controllerWith);
        $this->assertSame('123', $controllerWith->body()->required('bar')->string());
        $this->expectException(InvalidArgumentException::class);
        $controller->withBody([
            'bar' => 'abc',
        ]);
    }

    public function testAcceptBodyParametersOptional(): void
    {
        $controller = new AcceptOptionalController();
        $this->assertSame([], $controller->body()->toArray());
        $controllerWith = $controller->withBody([
            'bar' => '123',
        ]);
        $this->assertNotSame($controller, $controllerWith);
        $this->assertNotEquals($controller, $controllerWith);
        $this->assertSame('123', $controllerWith->body()->optional('bar')->string());
    }

    public function testAcceptFileParameters(): void
    {
        $controller = new AcceptController();
        $file = [
            'type' => 'text/plain',
            'tmp_name' => '/tmp/file.yx5kVl',
            'size' => 1313,
            'name' => 'readme.txt',
            'error' => UPLOAD_ERR_OK,
        ];
        $this->assertSame([], $controller->files());
        $controllerWith = $controller->withFiles([
            'MyFile' => $file,
        ]);
        $this->assertNotSame($controller, $controllerWith);
        $this->assertNotEquals($controller, $controllerWith);
        $myFile = $controllerWith->files()['MyFile'];
        $this->assertSame($file, $myFile->toArray());
        $this->expectException(ArgumentCountError::class);
        $controller->withFiles([
            'MyFile' => [],
        ]);
    }
}
