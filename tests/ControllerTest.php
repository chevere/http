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

use BadMethodCallException;
use Chevere\Action\Exceptions\ActionException;
use Chevere\Http\Exceptions\ControllerException;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use Chevere\Tests\src\AcceptBodyController;
use Chevere\Tests\src\AcceptBodyUnionController;
use Chevere\Tests\src\AcceptController;
use Chevere\Tests\src\AcceptHeadersController;
use Chevere\Tests\src\AcceptOptionalController;
use Chevere\Tests\src\AcceptQueryController;
use Chevere\Tests\src\JsonBodyController;
use Chevere\Tests\src\NullController;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
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
    }

    public static function dataProviderDefaultsNull(): array
    {
        return [
            ['attributes'],
            ['bodyParsed'],
            ['bodyStream'],
            ['cookieParams'],
            ['files'],
            ['headers'],
            ['query'],
            ['serverParams'],
            ['serverRequest'],
            ['uploadedFiles'],
        ];
    }

    #[DataProvider('dataProviderDefaultsNull')]
    public function testDefaultsNull(string $method): void
    {
        $controller = new NullController();
        $this->expectException(BadMethodCallException::class);
        $controller->{$method}();
    }

    public function testWithServerRequest(): void
    {
        $serverRequest = new ServerRequest('GET', '/');
        $controller = (new NullController())->withServerRequest($serverRequest);
        $this->assertSame($serverRequest, $controller->serverRequest());
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
            $controller->serverParams()
                ->toArray()
        );
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
        $this->assertSame(
            [
                'Content-Type' => 'application/json',
                'X-Custom-Header' => 'value',
            ],
            $controllerWith->headers()
                ->toArray()
        );
        $this->assertSame(
            'application/json',
            $controllerWith->headers()
                ->required('Content-Type')
        );
        $this->assertSame(
            'value',
            $controllerWith->headers()
                ->required('X-Custom-Header')
        );
        $this->assertNotSame($controller, $controllerWith);
        $this->expectException(ControllerException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage(
            <<<PLAIN
            [http.headers] Missing required argument(s): `Content-Type`, `X-Custom-Header`
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
        $this->assertSame('abc', $controllerWith->query()->required('foo'));
        $this->expectException(ControllerException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage(
            <<<PLAIN
            [http.query] [foo]: Argument value provided `123` doesn't match the regex `/^[a-z]+$/`
            PLAIN
        );
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
        $this->expectExceptionMessage(
            <<<PLAIN
            [http.body] [bar]: Argument value provided `error` doesn't match the regex `/^[1-9]+$/`
            PLAIN
        );
        $controller->withServerRequest(
            $serverRequest
                ->withParsedBody([
                    'bar' => 'error',
                ])
        );
    }

    public function testAcceptBodyUnion(): void
    {
        $serverRequest = new ServerRequest('GET', '/', [
            'Content-Type' => 'application/json',
        ]);
        $controller = new AcceptBodyUnionController();
        $with = $controller->withServerRequest(
            $serverRequest
        );
        $this->assertSame(null, $with->__invoke());
        $streamString = (new Stream(fopen('php://temp', 'r+')));
        $streamString->write(json_encode('123'));
        $with = $controller->withServerRequest(
            $serverRequest
                ->withBody($streamString)
        );
        $this->assertCount(0, $with->bodyParsed()->parameters());
        $this->assertSame('123', $with->__invoke());
        $this->assertSame('123', $with->body()->string());
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
        $this->assertSame('abc', $controllerWith->query()->required('foo'));
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
        $this->assertSame('abc', $controllerWith->query()->optional('foo'));
    }

    public function testWithRequestAttributes(): void
    {
        $serverRequest = new ServerRequest('GET', '/');
        $controller = (new NullController())
            ->withServerRequest(
                $serverRequest->withAttribute('foo', 'bar')
            );
        $controller->attributes()
            ->get('foo');
        $this->assertSame(
            [
                'foo' => 'bar',
            ],
            $controller->attributes()
                ->toArray()
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
                    'ignore' => $myImage,
                ])
        );
        $this->assertSame(
            [
                'myFile',
                'myImage',
            ],
            $controllerWith->files()
                ->parameters()
                ->keys()
        );
        $this->assertSame($myFile, $controllerWith->uploadedFiles()->get('myFile'));
        $this->assertSame($myImage, $controllerWith->uploadedFiles()->get('myImage'));
        $this->assertNotSame($controller, $controllerWith);
        $this->assertNotEquals($controller, $controllerWith);
        $theFile = $controllerWith->files()
            ->required('myFile');
        $theImage = $controllerWith->files()
            ->optional('myImage');
        $this->assertSame(
            [
                'error' => $myFile->getError(),
                'name' => $myFile->getClientFilename(),
                'type' => $myFile->getClientMediaType(),
                'size' => $myFile->getSize(),
                'tmp_name' => $myFile->getStream()
                    ->getMetadata('uri'),
            ],
            $theFile->array()
        );
        $this->assertSame(
            [
                'error' => $myImage->getError(),
                'name' => $myImage->getClientFilename(),
                'type' => $myImage->getClientMediaType(),
                'size' => $myImage->getSize(),
                'tmp_name' => $myImage->getStream()
                    ->getMetadata('uri'),
            ],
            $theImage->array()
        );
    }

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
            $controller->headers()
                ->toArray()
        );
    }

    public function testServerRequestHeadersAcceptHeaders(): void
    {
        $headers = [
            'foo' => 'super',
            'bar' => 'taldo',
        ];
        $serverRequest = new ServerRequest('GET', '/', headers: $headers);
        $controller = (new AcceptHeadersController())->withServerRequest($serverRequest);
        $this->assertSame(
            $headers['foo'],
            $controller->headers()
                ->required('Foo')
        );
        $this->assertSame(
            $headers['bar'],
            $controller->headers()
                ->required('Bar')
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
            $controller->cookieParams()
                ->toArray()
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
