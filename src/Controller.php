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
     * @var array<string, mixed>
     */
    private array $_attributes;

    /**
     * @var array<string, mixed>
     */
    private array $_serverParams;

    private ?ArgumentsInterface $_query = null;

    private ?ArgumentsInterface $_body = null;

    private ?ArgumentsInterface $_files = null;

    private Status $_status;

    public static function acceptQuery(): ArrayStringParameterInterface
    {
        return arrayString();
    }

    public static function acceptBody(): ArrayParameterInterface
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
        $new->_serverParams = $serverRequest->getServerParams();
        $new->_attributes = $serverRequest->getAttributes();
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

    final public function files(): ArgumentsInterface
    {
        return $this->_files
            ??= arguments(static::acceptFiles()->parameters(), []);
    }

    final public function serverParams(): array
    {
        return $this->_serverParams;
    }

    final public function attributes(): array
    {
        return $this->_attributes;
    }

    final public function status(): StatusInterface
    {
        return $this->_status
            ??= responseAttribute()->status;
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
