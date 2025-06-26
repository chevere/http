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
use Chevere\Http\Interfaces\ControllerInterface;
use Chevere\Http\Interfaces\StatusInterface;
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Parameter\Interfaces\ArrayParameterInterface;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\arrayString;

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

    private ?ArgumentsInterface $_body = null;

    private ?ArgumentsInterface $_files = null;

    private ?Status $_status = null;

    public static function acceptQuery(): ArrayParameterInterface|ArrayStringParameterInterface
    {
        return arrayString();
    }

    public static function acceptBody(): ArrayParameterInterface|ArrayStringParameterInterface
    {
        return arrayp();
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
        $new->_query = arguments(
            $new::acceptQuery()->parameters(),
            $serverRequest->getQueryParams()
        );
        $new->_body = arguments(
            $new::acceptBody()->parameters(),
            (array) ($serverRequest->getParsedBody() ?? [])
        );
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

    final public function body(): ArgumentsInterface
    {
        return $this->_body
            ??= arguments(static::acceptBody()->parameters(), []);
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
        $this->body();
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
