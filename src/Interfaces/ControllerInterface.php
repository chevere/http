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

namespace Chevere\Http\Interfaces;

use Chevere\Action\Interfaces\ControllerInterface as BaseControllerInterface;
use Chevere\DataStructure\Interfaces\MapInterface;
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Parameter\Interfaces\ArrayParameterInterface;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use Chevere\Parameter\Interfaces\CastInterface;
use Chevere\Parameter\Interfaces\ParameterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Describes the component in charge of defining an Http Controller
 * which is an action that adds methods for handling HTTP contexts.
 */
interface ControllerInterface extends BaseControllerInterface
{
    /**
     * Defines the query string accepted.
     */
    public static function acceptQuery(): ArrayStringParameterInterface;

    /**
     * Defines the body accepted.
     */
    public static function acceptBody(): ParameterInterface;

    /**
     * Defines the FILES accepted.
     */
    public static function acceptFiles(): ArrayParameterInterface;

    public function withServerRequest(ServerRequestInterface $serverRequest): static;

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal.
     * The data IS NOT REQUIRED to originate from $_SERVER.
     *
     * @return MapInterface<mixed>
     */
    public function serverParams(): MapInterface;

    /**
     * Provides access to cookie parameters sent by the client to the server.
     *
     * @return MapInterface<string>
     */
    public function cookieParams(): MapInterface;

    /**
     * Provides access to server request headers.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is the header line.
     *
     * @return MapInterface<string>
     */
    public function headers(): MapInterface;

    /**
     * Provides access to query string arguments.
     */
    public function query(): ArgumentsInterface;

    /**
     * Provides access to arguments provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded,
     * multipart/form-data, or application/json, this method will return
     * the parsed body content arguments.
     */
    public function bodyParsed(): ArgumentsInterface;

    /**
     * Gets the body of the message.
     */
    public function bodyStream(): StreamInterface;

    /**
     * Provides access to the body variable (typed).
     */
    public function body(): CastInterface;

    public function files(): ArgumentsInterface;

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific.
     *
     * @return MapInterface<mixed>
     */
    public function attributes(): MapInterface;

    /**
     * Provides access to the response attribute (ResponseAttr)
     * StatusInterface codes for the controller.
     */
    public function status(): StatusInterface;

    /**
     * Define a method to handle terminated responses (e.g. set headers, redirects)
     */
    public function terminate(ResponseInterface $response): ResponseInterface;
}
