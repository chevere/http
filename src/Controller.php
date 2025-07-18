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

namespace Chevere\Http;

use Chevere\Action\Controller as BaseController;
use Chevere\Action\Interfaces\ReflectionActionInterface;
use Chevere\DataStructure\Interfaces\MapInterface;
use Chevere\DataStructure\Map;
use Chevere\Http\Exceptions\ControllerException;
use Chevere\Http\Interfaces\ControllerInterface;
use Chevere\Http\Interfaces\StatusInterface;
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Parameter\Interfaces\ArrayParameterInterface;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use Chevere\Parameter\Interfaces\CastInterface;
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Interfaces\ParametersAccessInterface;
use PhpParser\Builder\Param;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Throwable;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\arrayString;
use function Chevere\Parameter\cast;
use function Chevere\Parameter\mixed;

abstract class Controller extends BaseController implements ControllerInterface
{
    /**
     * @var Map<mixed>
     */
    private Map $_attributes;

    /**
     * @var Map<mixed>
     */
    private Map $_serverParams;

    /**
     * @var Map<string>
     */
    private Map $_headers;

    /**
     * @var Map<string>
     */
    private Map $_cookieParams;

    private ?ArgumentsInterface $_query = null;

    private ?ArgumentsInterface $_bodyParsed = null;

    private ?ArgumentsInterface $_files = null;

    private ?Status $_status = null;

    private mixed $_body = null;

    private StreamInterface $bodyStream;

    public static function acceptQuery(): ArrayParameterInterface|ArrayStringParameterInterface
    {
        return arrayString();
    }

    public static function acceptBody(): ParameterInterface
    {
        return mixed();
    }

    public static function acceptFiles(): ArrayParameterInterface
    {
        return arrayp();
    }

    public function terminate(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    final public function withServerRequest(ServerRequestInterface $serverRequest): static
    {
        $new = clone $this;

        try {
            $new->bodyStream = $serverRequest->getBody();
            $new->_query = arguments(
                $new::acceptQuery()->parameters(),
                $serverRequest->getQueryParams()
            );
            $parsedBody = (array) ($serverRequest->getParsedBody() ?? []);
            $new->_body = $parsedBody;
            if ($serverRequest->getHeaderLine('Content-Type') === 'application/json') {
                $streamed = $new->bodyStream->__toString();
                $new->_body = json_decode($streamed, true);
                if ($new->_body === null && $streamed !== '') {
                    $new->_body = $streamed;
                }
            }
            $acceptBody = $new::acceptBody();
            $acceptBody->__invoke($new->_body);
            $new->_bodyParsed = arguments(
                $acceptBody instanceof ParametersAccessInterface
                    ? $acceptBody->parameters()
                    : arrayp(),
                is_array($new->_body)
                    ? $new->_body
                    : $parsedBody
            );
        } catch (Throwable $e) {
            throw new ControllerException($e->getMessage(), 400, $e);
        }
        $new->_serverParams = new Map(...$serverRequest->getServerParams());
        $new->_attributes = new Map(...$serverRequest->getAttributes());
        $headers = [];
        $headersKeys = array_keys($serverRequest->getHeaders());
        foreach ($headersKeys as $key) {
            $headers[$key] = $serverRequest->getHeaderLine($key);
        }
        $new->_headers = new Map(...$headers);
        $new->_cookieParams = new Map(...$serverRequest->getCookieParams());
        $new->setFiles($serverRequest->getUploadedFiles());

        return $new;
    }

    final public function query(): ArgumentsInterface
    {
        return $this->_query
            ??= arguments(static::acceptQuery()->parameters(), []);
    }

    final public function bodyParsed(): ArgumentsInterface
    {
        $acceptBody = static::acceptBody();

        return $this->_bodyParsed
            ??= arguments(
                $acceptBody instanceof ParametersAccessInterface
                    ? $acceptBody->parameters()
                    : arrayp(),
                []
            );
    }

    final public function body(): CastInterface
    {
        return cast($this->_body);
    }

    final public function bodyStream(): StreamInterface
    {
        return $this->bodyStream;
    }

    final public function headers(): MapInterface
    {
        return $this->_headers
            ??= new Map();
    }

    final public function cookieParams(): MapInterface
    {
        return $this->_cookieParams
            ??= new Map();
    }

    final public function files(): ArgumentsInterface
    {
        return $this->_files
            ??= arguments(static::acceptFiles()->parameters(), []);
    }

    final public function serverParams(): MapInterface
    {
        return $this->_serverParams
            ??= new Map();
    }

    final public function attributes(): MapInterface
    {
        return $this->_attributes
            ??= new Map();
    }

    final public function status(): StatusInterface
    {
        return $this->_status
            ??= responseAttribute(static::class)->status
            ?? new Status();
    }

    protected function assertRuntime(ReflectionActionInterface $reflection): void
    {
        $this->query();
        $this->bodyParsed();
        $this->files();
    }

    /**
     * @param array<string, UploadedFileInterface> $files
     */
    protected function setFiles(array $files): void
    {
        $arguments = [];
        $parameters = $this->acceptFiles()->parameters();
        foreach ($files as $key => $file) {
            $key = strval($key);
            $parameters->assertHas($key);
            $array = [
                'error' => $file->getError(),
                'name' => $file->getClientFilename(),
                'type' => $file->getClientMediaType(),
                'size' => $file->getSize(),
                'tmp_name' => $file->getStream()->getMetadata('uri'),
            ];
            $arguments[$key] = $array;
        }
        $this->_files = arguments($parameters, $arguments);
    }
}
