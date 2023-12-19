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

use ArgumentCountError;
use Chevere\Tests\src\AcceptController;
use Chevere\Tests\src\AcceptOptionalController;
use Chevere\Tests\src\NullController;
use InvalidArgumentException;
use LogicException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

final class ControllerTest extends TestCase
{
    public function testAssertRuntime(): void
    {
        $controller = new AcceptController();
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('query: Missing required argument(s): `foo`');
        $controller->__invoke();
    }

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

    public function testAcceptFile(): void
    {
        $controller = new AcceptController();
        $myFile = [
            'error' => UPLOAD_ERR_OK,
            'name' => 'readme.txt',
            'size' => 12345,
            'type' => 'text/plain',
            'tmp_name' => '/tmp/file.yx5kVl',
        ];
        $myImage = [
            'error' => UPLOAD_ERR_OK,
            'name' => 'image.png',
            'size' => 1234,
            'type' => 'image/png',
            'tmp_name' => '/tmp/file.pnp4t1',
        ];
        $this->assertSame([], $controller->files());
        $controllerWith = $controller->withFiles([
            'myFile' => $myFile,
            'myImage' => $myImage,
        ]);
        $this->assertNotSame($controller, $controllerWith);
        $this->assertNotEquals($controller, $controllerWith);
        $theFile = $controllerWith->files()['myFile'];
        $theImage = $controllerWith->files()['myImage'];
        $this->assertSame($myFile, $theFile->toArray());
        $this->assertSame($myImage, $theImage->toArray());
    }

    public function testAcceptFileInvalidArgument(): void
    {
        $controller = new AcceptController();
        $this->expectException(ArgumentCountError::class);
        $this->expectExceptionMessage('Missing required argument(s): `error, name, size, type, tmp_name`');
        $controller->withFiles([
            'myFile' => [],
        ]);
    }

    public function testAcceptFileMissingKey(): void
    {
        $controller = new AcceptController();
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Missing key(s) `404`');
        $controller->withFiles([
            '404' => [],
        ]);
    }
}
