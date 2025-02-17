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
use Chevere\Http\Status;
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Parameter\Interfaces\ArrayParameterInterface;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use Chevere\Parameter\Interfaces\CastInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Describes the component in charge of defining an Http Controller which adds methods for handling HTTP requests.
 */
interface ControllerInterface extends BaseControllerInterface
{
    /**
     * Defines the query accepted.
     */
    public static function acceptQuery(): ArrayStringParameterInterface;

    /**
     * Defines the body accepted.
     */
    public static function acceptBody(): ArrayParameterInterface;

    /**
     * Defines the FILES accepted.
     */
    public static function acceptFiles(): ArrayParameterInterface;

    public function withServerRequest(ServerRequestInterface $serverRequest): static;

    public function query(): ArgumentsInterface;

    public function body(): ArgumentsInterface;

    public function files(): ArgumentsInterface;

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return MapInterface<mixed>
     */
    public function serverParams(): MapInterface;

    public function attribute(
        string $name,
        mixed $default = null
    ): CastInterface;

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
     * Provides access to the ResponseAttr Status codes for the controller.
     */
    public function status(): StatusInterface;

    /**
     * Define a method to handle terminated responses (e.g. set headers, redirects)
     */
    public function terminate(ResponseInterface $response): ResponseInterface;
}
