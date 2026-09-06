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

use BadMethodCallException;
use Chevere\Action\Controller as BaseController;
use Chevere\DataStructure\Interfaces\MapInterface;
use Chevere\DataStructure\Map;
use Chevere\DataStructure\Vector;
use Chevere\Http\Exceptions\ControllerException;
use Chevere\Http\Interfaces\ControllerInterface;
use Chevere\Http\Interfaces\StatusInterface;
use Chevere\Parameter\ArgumentsString;
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Parameter\Interfaces\ArgumentsStringInterface;
use Chevere\Parameter\Interfaces\ArrayParameterInterface;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Interfaces\ParametersAccessInterface;
use Chevere\Parameter\Interfaces\TypedInterface;
use Chevere\Parameter\Interfaces\UnionParameterInterface;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Throwable;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\arrayString;
use function Chevere\Parameter\mixed;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\typed;

abstract class Controller extends BaseController implements ControllerInterface
{
    /**
     * @var Map<mixed>
     */
    private ?Map $_attributes = null;

    /**
     * @var Map<mixed>
     */
    private ?Map $_serverParams = null;

    /**
     * @var Map<string>
     */
    private ?Map $_cookieParams = null;

    private ?ArgumentsStringInterface $_headers = null;

    private ?ArgumentsStringInterface $_query = null;

    private ?ArgumentsInterface $_bodyParsed = null;

    private ?ArgumentsInterface $_files = null;

    private ?StatusInterface $_status = null;

    private mixed $_body = null;

    private ?StreamInterface $_bodyStream = null;

    private ?ServerRequestInterface $_serverRequest = null;

    public static function acceptHeaders(): ArrayStringParameterInterface
    {
        return arrayString();
    }

    public static function acceptQuery(): ArrayStringParameterInterface
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
        $errors = [];

        try {
            $acceptHeaders = $new::acceptHeaders()->parameters();
            $parameterKeys = $acceptHeaders->keys();
            $headerIndex = new Vector(...$parameterKeys);
            $headerIndexLowercase = new Vector(
                ...array_map('mb_strtolower', $parameterKeys)
            );
            $headers = [];
            foreach (array_keys($serverRequest->getHeaders()) as $key) {
                $lowercased = mb_strtolower($key);
                $pos = $headerIndexLowercase->find($lowercased) ?? null;
                if ($pos !== null) {
                    /** @var string $key */
                    $key = $headerIndex->get($pos);
                }
                $headers[$key] = $serverRequest->getHeaderLine($key);
            }
            $new->_headers = new ArgumentsString(
                $acceptHeaders,
                $headers
            );
        } catch (Throwable $e) {
            $errors[] = $this->getHttpErrorMessage('headers', $e);
        }

        try {
            $new->_query = new ArgumentsString(
                $new::acceptQuery()->parameters(),
                $serverRequest->getQueryParams()
            );
        } catch (Throwable $e) {
            $errors[] = $this->getHttpErrorMessage('query', $e);
        }

        try {
            $new->_bodyStream = $serverRequest->getBody();
            $parsedBody = $serverRequest->getParsedBody();
            if (is_object($parsedBody)) {
                $parsedBody = (array) $parsedBody;
            }
            $new->_body = $parsedBody ?? null;
            if ($serverRequest->getHeaderLine('Content-Type') === 'application/json') {
                if (! $new->_body) {
                    $streamed = $new->_bodyStream->__toString();
                    $new->_body = json_decode($streamed, true);
                    if ($new->_body === null && $streamed !== '') {
                        $new->_body = $streamed;
                    }
                }
            } elseif ($new->_body === null) {
                $new->_body = $new->_bodyStream->__toString();
            }
            $acceptBody = $new::acceptBody();
            $new->_bodyParsed = arguments(parameters(), []);
            if ($acceptBody instanceof UnionParameterInterface) {
                $acceptBody->__invoke($new->_body);
            } else {
                if ($acceptBody instanceof ParametersAccessInterface) {
                    $new->_bodyParsed = arguments(
                        $acceptBody,
                        is_array($new->_body)
                            ? $new->_body
                            : ($parsedBody ?? [])
                    );
                }
            }
        } catch (Throwable $e) {
            $errors[] = $this->getHttpErrorMessage('body', $e);
        }

        try {
            $new->setFiles(...$serverRequest->getUploadedFiles());
        } catch (Throwable $e) {
            $errors[] = $this->getHttpErrorMessage('files', $e);
        }
        if ($errors !== []) {
            throw new ControllerException(
                message: implode("\n", $errors),
                code: 400,
                controller: static::class
            );
        }
        $new->_serverRequest = $serverRequest;
        $new->_serverParams = new Map(...$serverRequest->getServerParams());
        $new->_attributes = new Map(...$serverRequest->getAttributes());
        $new->_cookieParams = new Map(...$serverRequest->getCookieParams());

        return $new;
    }

    final public function query(): ArgumentsStringInterface
    {
        return $this->_query
            ?? throw new BadMethodCallException();
    }

    final public function bodyParsed(): ArgumentsInterface
    {
        return $this->_bodyParsed
            ?? throw new BadMethodCallException();
    }

    final public function body(): TypedInterface
    {
        return typed($this->_body);
    }

    final public function bodyStream(): StreamInterface
    {
        return $this->_bodyStream
            ?? throw new BadMethodCallException();
    }

    final public function headers(): ArgumentsStringInterface
    {
        return $this->_headers
            ?? throw new BadMethodCallException();
    }

    final public function cookieParams(): MapInterface
    {
        return $this->_cookieParams
            ?? throw new BadMethodCallException();
    }

    final public function files(): ArgumentsInterface
    {
        return $this->_files
            ?? throw new BadMethodCallException();
    }

    final public function serverRequest(): ServerRequestInterface
    {
        return $this->_serverRequest
            ?? throw new BadMethodCallException();
    }

    final public function serverParams(): MapInterface
    {
        return $this->_serverParams
            ?? throw new BadMethodCallException();
    }

    final public function attributes(): MapInterface
    {
        return $this->_attributes
            ?? throw new BadMethodCallException();
    }

    /**
     * @infection-ignore-all False positive
     */
    final public function status(): StatusInterface
    {
        return $this->_status
            ??= responseAttribute(static::class)?->status()
            ?? new Status();
    }

    /**
     * @infection-ignore-all
     */
    public function acceptRulesRuntime(): void
    {
        if (! isset($this->_query, $this->_bodyParsed, $this->_files)) {
            throw new LogicException('Server request not set. Did you forget to call withServerRequest() method?');
        }
    }

    private function setFiles(UploadedFileInterface ...$files): void
    {
        $arguments = [];
        $parameters = $this->acceptFiles()
            ->parameters();
        foreach ($files as $key => $file) {
            $key = strval($key);
            if (! $parameters->has($key)) {
                continue;
            }
            $array = [
                'error' => $file->getError(),
                'name' => $file->getClientFilename(),
                'type' => $file->getClientMediaType(),
                'size' => $file->getSize(),
                'tmp_name' => $file->getStream()
                    ->getMetadata('uri'),
            ];
            $arguments[$key] = $array;
        }
        $this->_files = arguments($parameters, $arguments);
    }

    private function getHttpErrorMessage(string $context, Throwable $e): string
    {
        $message = $e->getMessage();
        $httpContext = "[http.{$context}]";
        $errorMessage = preg_replace(
            '/\[([^\]]+)\]:/',
            $httpContext . ' [$1]:',
            $message
        ) ?? '';
        if ($errorMessage === $message) {
            $errorMessage = "{$httpContext} {$errorMessage}";
        }

        return $errorMessage;
    }
}
