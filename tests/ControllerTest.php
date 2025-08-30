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
use BadMethodCallException;
use Chevere\Action\Exceptions\ActionException;
use Chevere\Http\Exceptions\ControllerException;
use Chevere\Http\Status;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use Chevere\Tests\src\AcceptBodyController;
use Chevere\Tests\src\AcceptController;
use Chevere\Tests\src\AcceptOptionalController;
use Chevere\Tests\src\AcceptQueryController;
use Chevere\Tests\src\JsonBodyController;
use Chevere\Tests\src\NullController;
use Chevere\Tests\src\WithoutResponseAttributeStatusController;
use Chevere\Tests\src\WithResponseAttributeStatusController;
use Error;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\UploadedFile;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\arrayString;
use function Chevere\Parameter\mixed;
use function Chevere\Parameter\string;
use function Chevere\Writer\streamTemp;

final class ControllerTest extends TestCase
{
    public function testAssertWithoutServerRequest(): void
    {
        $controller = new AcceptController();
        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            `Chevere\Tests\src\AcceptController` LogicException → Server request not set. Did you forget to call withServerRequest() method?
            PLAIN
        );
        $controller->__invoke();
    }

    public function testAssertRuntime(): void
    {
        $controller = new AcceptController();
        $this->expectException(ControllerException::class);
        $this->expectExceptionMessage(<<<PLAIN
        Missing required argument(s): `foo`
        PLAIN);
        $controller->withServerRequest(
            new ServerRequest('GET', '/')
        );
    }

    public function testDefaults(): void
    {
        $controller = new NullController();
        $this->assertCount(0, $controller->acceptQuery()->parameters());
        $this->assertEquals(mixed(), $controller->acceptBody());
        $this->assertCount(0, $controller->acceptFiles()->parameters());
        $this->assertEquals(new Status(), $controller->status());
    }

    public static function dataProviderDefaultsNull(): array
    {
        return [
            ['query'],
            ['bodyParsed'],
            ['cookieParams'],
            ['files'],
            ['serverParams'],
            ['attributes'],
        ];
    }

    /**
     * @dataProvider dataProviderDefaultsNull
     */
    public function testDefaultsNull(string $method): void
    {
        $controller = new NullController();
        $this->expectException(BadMethodCallException::class);
        $controller->{$method}();
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
        $controllerWithStatus = new WithResponseAttributeStatusController();
        $this->assertSame(666, $controllerWithStatus->status()->success()->int());
        $controllerWithoutStatus = new WithoutResponseAttributeStatusController();
        $this->assertSame(200, $controllerWithoutStatus->status()->success()->int());
    }

    public function testAcceptHeaders(): void
    {
        $serverRequest = new ServerRequest(
            'GET',
            '/',
            headers: [
                'Content-Type' => 'application/json',
                'X-Custom-Header' => 'value',
            ]
        );
        $controller = new class() extends NullController {
            public static function acceptHeaders(): ArrayStringParameterInterface
            {
                return arrayString(
                    ...[
                        'Content-Type' => string(),
                        'X-Custom-Header' => string(),
                    ]
                );
            }
        };
        $controllerWith = $controller->withServerRequest($serverRequest);
        $this->assertNotSame($controller, $controllerWith);
        $this->expectException(ControllerException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage(
            <<<PLAIN
            [HTTP headers] Missing required argument(s): `Content-Type, X-Custom-Header`
            PLAIN
        );
        $controller->withServerRequest(
            new ServerRequest('GET', '/')
        );
    }

    public function testAcceptQuery(): void
    {
        $serverRequest = new ServerRequest('GET', '/');
        $controller = new AcceptQueryController();
        $controllerWith = $controller->withServerRequest(
            $serverRequest
                ->withQueryParams([
                    'foo' => 'abc',
                ])
        );
        $this->assertNotSame($controller, $controllerWith);
        $this->assertNotEquals($controller, $controllerWith);
        $this->assertSame('abc', $controllerWith->query()->required('foo')->string());
        $this->expectException(ControllerException::class);
        $this->expectExceptionCode(400);
        $controller->withServerRequest(
            $serverRequest
                ->withQueryParams([
                    'foo' => '123',
                ])
        );
    }

    public function testAcceptBody(): void
    {
        $serverRequest = new ServerRequest('GET', '/');
        $controller = new AcceptBodyController();
        $controllerWith = $controller->withServerRequest(
            $serverRequest
                ->withParsedBody([
                    'bar' => '123',
                ])
        );
        $this->assertNotSame($controller, $controllerWith);
        $this->assertNotEquals($controller, $controllerWith);
        $this->assertSame('123', $controllerWith->bodyParsed()->required('bar')->string());
        $this->expectException(ControllerException::class);
        $this->expectExceptionCode(400);
        $controller->withServerRequest(
            $serverRequest
                ->withParsedBody([
                    'bar' => 'error',
                ])
        );
    }

    public function testAcceptQueryBodyFiles(): void
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
        $this->expectException(ControllerException::class);
        $this->expectExceptionCode(400);
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

    public function testServerRequestHeaders(): void
    {
        $headers = [
            'Content-Type' => 'text/html',
            'Set-Cookie' => [
                'session_id=abc123; Path=/; Secure; HttpOnly',
                'theme=dark; Path=/; Secure',
            ],
            'Cache-Control' => [
                'no-store',
                'no-cache, must-revalidate',
            ],
        ];
        $expected = [];
        foreach ($headers as $key => $value) {
            $expected[$key] = implode(', ', (array) $value);
        }
        $serverRequest = new ServerRequest('GET', '/', headers: $headers);
        $controller = (new NullController())->withServerRequest($serverRequest);
        $this->assertSame(
            $expected,
            $controller->headers()->toArray()
        );
    }

    public function testServerRequestCookieParams(): void
    {
        $cookieParams = [
            'session_id' => 'abc123',
            'theme' => 'dark',
        ];
        $serverRequest = (new ServerRequest('GET', '/'))
            ->withCookieParams($cookieParams);
        $controller = (new NullController())->withServerRequest($serverRequest);
        $this->assertSame(
            $cookieParams,
            $controller->cookieParams()->toArray()
        );
    }

    public function testJsonBody(): void
    {
        $json = json_encode(99);
        $stream = streamTemp($json);
        $serverRequest = (new ServerRequest('POST', '/'))
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);
        $controller = (new JsonBodyController())
            ->withServerRequest($serverRequest);
        $return = $controller->__invoke();
        $this->assertSame([[], 99], $return);
        $this->assertSame(
            $stream,
            $controller->bodyStream()
        );
    }
}
