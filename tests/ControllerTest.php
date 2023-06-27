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

use function Chevere\Http\classHeaders;
use function Chevere\Http\classStatus;
use Chevere\Tests\_resources\AcceptController;
use Chevere\Tests\_resources\NullController;
use Chevere\Throwable\Errors\ArgumentCountError;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ControllerTest extends TestCase
{
    public function nullMethodCallProvider(): array
    {
        return [
            ['query'],
            ['body'],
        ];
    }

    /**
     * @dataProvider nullMethodCallProvider
     */
    public function testNullMethodCall(string $method): void
    {
        $controller = new NullController();
        $this->assertNull($controller->{$method}());
    }

    public function testDefaults(): void
    {
        $controller = new NullController();
        $this->assertCount(0, $controller->acceptQuery()->parameters());
        $this->assertCount(0, $controller->acceptBody()->parameters());
        $this->assertCount(0, $controller->acceptFiles()->parameters());
    }

    public function testStatusAttribute(): void
    {
        $status = classStatus(AcceptController::class);
        $this->assertSame(200, $status->primary);
        $this->assertSame([400], $status->other);
    }

    public function testHeadersAttribute(): void
    {
        $headers = classHeaders(AcceptController::class);
        $this->assertSame(
            [
                'Content-Disposition' => 'attachment',
                'Content-Type' => 'application/json',
            ],
            $headers->toArray()
        );
    }

    public function testAcceptQueryParameters(): void
    {
        $controller = new AcceptController();
        $controllerWith = $controller->withQuery([
            'foo' => 'abc',
        ]);
        $this->assertNotSame($controller, $controllerWith);
        $this->assertNotEquals($controller, $controllerWith);
        $this->assertSame('abc', $controllerWith->query()->getString('foo'));
        $this->expectException(InvalidArgumentException::class);
        $controller->withQuery([
            'foo' => '123',
        ]);
    }

    public function testAcceptBodyParameters(): void
    {
        $controller = new AcceptController();
        $controllerWith = $controller->withBody([
            'bar' => '123',
        ]);
        $this->assertNotSame($controller, $controllerWith);
        $this->assertNotEquals($controller, $controllerWith);
        $this->assertSame('123', $controllerWith->body()->getString('bar'));
        $this->expectException(InvalidArgumentException::class);
        $controller->withBody([
            'bar' => 'abc',
        ]);
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
