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
use Chevere\Action\Exceptions\ActionException;
use Chevere\Http\Status;
use Chevere\Tests\src\AcceptController;
use Chevere\Tests\src\AcceptOptionalController;
use Chevere\Tests\src\NullController;
use InvalidArgumentException;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\UploadedFile;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

final class ControllerTest extends TestCase
{
    public function testAssertRuntime(): void
    {
        $controller = new AcceptController();
        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(<<<PLAIN
        `Chevere\Tests\src\AcceptController` ArgumentCountError → Missing required argument(s): `foo`
        PLAIN);
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
        $this->assertEquals(new Status(), $controller->status());
        $this->assertSame(
            spl_object_id($controller->body()),
            spl_object_id($controller->body()),
        );
    }

    public function testWithServerParams(): void
    {
        $serverParams = [
            'super' => 'taldo',
        ];
        $serverRequest = new ServerRequest('GET', '/', serverParams: $serverParams);
        $controller = (new NullController())->withServerRequest($serverRequest);
        $this->assertSame(
            $serverParams,
            $controller->serverParams()->toArray()
        );
    }

    public function testStatus(): void
    {
        $controller = new AcceptController();
        $status = new Status(200, 400);
        $this->assertSame(
            spl_object_id($controller->status()),
            spl_object_id($controller->status()),
        );
        $this->assertEquals($status, $controller->status());
    }

    public function testAcceptQueryBody(): void
    {
        $serverRequest = new ServerRequest('GET', '/');
        $controller = new AcceptController();
        $file = __DIR__ . '/src/test.txt';
        $size = filesize($file);
        $myFile = new UploadedFile(
            streamOrFile: $file,
            size: $size,
            errorStatus: UPLOAD_ERR_OK,
            clientFilename: 'test.txt',
            clientMediaType: 'text/plain'
        );
        $controllerWith = $controller->withServerRequest(
            $serverRequest
                ->withQueryParams([
                    'foo' => 'abc',
                ])
                ->withParsedBody([
                    'bar' => '123',
                ])
                ->withUploadedFiles([
                    'myFile' => $myFile,
                ])
        );
        $this->assertNotSame($controller, $controllerWith);
        $this->assertNotEquals($controller, $controllerWith);
        $this->assertSame('abc', $controllerWith->query()->required('foo')->string());
        $this->expectException(InvalidArgumentException::class);
        $controller->withServerRequest(
            $serverRequest
                ->withQueryParams([
                    'foo' => '123',
                ])
        );
    }

    public function testAcceptQueryBodyOptional(): void
    {
        $serverRequest = new ServerRequest('GET', '/');
        $controller = new AcceptOptionalController();
        $this->assertSame([], $controller->query()->toArray());
        $controllerWith = $controller->withServerRequest(
            $serverRequest
                ->withQueryParams([
                    'foo' => 'abc',
                ])
                ->withParsedBody([
                    'bar' => '123',
                ])
        );
        $this->assertNotSame($controller, $controllerWith);
        $this->assertNotEquals($controller, $controllerWith);
        $this->assertSame('abc', $controllerWith->query()->optional('foo')->string());
    }

    public function testWithRequestAttributes(): void
    {
        $serverRequest = new ServerRequest('GET', '/');
        $controller = (new NullController())
            ->withServerRequest(
                $serverRequest->withAttribute('foo', 'bar')
            );
        $controller->attributes()->get('foo');
        $this->assertSame(
            [
                'foo' => 'bar',
            ],
            $controller->attributes()->toArray()
        );
    }

    public function testAcceptFile(): void
    {
        $serverRequest = new ServerRequest('GET', '/');
        $controller = new AcceptController();
        $file = __DIR__ . '/src/test.txt';
        $size = filesize($file);
        $myFile = new UploadedFile(
            streamOrFile: $file,
            size: $size,
            errorStatus: UPLOAD_ERR_OK,
            clientFilename: 'test.txt',
            clientMediaType: 'text/plain'
        );
        $myImage = new UploadedFile(
            streamOrFile: $file,
            size: $size,
            errorStatus: UPLOAD_ERR_OK,
            clientFilename: 'image.png',
            clientMediaType: 'image/png'
        );
        $controllerWith = $controller->withServerRequest(
            $serverRequest
                ->withQueryParams([
                    'foo' => 'abc',
                ])
                ->withParsedBody([
                    'bar' => '123',
                ])
                ->withUploadedFiles([
                    'myFile' => $myFile,
                    'myImage' => $myImage,
                ])
        );

        $this->assertNotSame($controller, $controllerWith);
        $this->assertNotEquals($controller, $controllerWith);
        $theFile = $controllerWith->files()->required('myFile');
        $theImage = $controllerWith->files()->optional('myImage');
        $this->assertSame(
            [
                'error' => $myFile->getError(),
                'name' => $myFile->getClientFilename(),
                'type' => $myFile->getClientMediaType(),
                'size' => $myFile->getSize(),
                'tmp_name' => $myFile->getStream()->getMetadata('uri'),
            ],
            $theFile->array()
        );
        $this->assertSame(
            [
                'error' => $myImage->getError(),
                'name' => $myImage->getClientFilename(),
                'type' => $myImage->getClientMediaType(),
                'size' => $myImage->getSize(),
                'tmp_name' => $myImage->getStream()->getMetadata('uri'),
            ],
            $theImage->array()
        );
    }

    // public function testAcceptFileInvalidArgument(): void
    // {
    //     $controller = new AcceptController();
    //     $this->expectException(ArgumentCountError::class);
    //     $this->expectExceptionMessage('Missing required argument(s): `error, name, size, type, tmp_name`');
    //     $controller->withFiles([
    //         'myFile' => [],
    //     ]);
    // }

    // public function testAcceptFileMissingKey(): void
    // {
    //     $serverRequest = new ServerRequest('GET', '/');
    //     $controller = new AcceptController();
    //     // $this->expectException(OutOfBoundsException::class);
    //     // $this->expectExceptionMessage('Missing key(s) `404`');
    //     $controller->withServerRequest(
    //         $serverRequest
    //             ->withQueryParams([
    //                 'foo' => 'abc',
    //             ])
    //             ->withParsedBody([
    //                 'bar' => '123',
    //             ])
    //             ->withUploadedFiles([])
    //     );
    // }

    public function testTerminate(): void
    {
        $controller = new AcceptController();
        $response = new Response();
        $terminate = $controller->terminate($response);
        $this->assertSame($response, $terminate);
    }
}
