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

use Chevere\Http\Attributes\Description;
use Chevere\Http\Attributes\Request;
use Chevere\Http\Attributes\Response;
use Chevere\Http\Interfaces\MiddlewareNameInterface;
use Chevere\Http\Interfaces\MiddlewaresInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Create a MiddlewareNameInterface instance without setup assertion.
 *
 * @param class-string<MiddlewareInterface> $name The middleware class name.
 */
function middlewareNameOnly(string $name): MiddlewareNameInterface
{
    return new MiddlewareNameOnly($name);
}

/**
 * Create a MiddlewaresInterface instance.
 *
 * String arguments are converted to `MiddlewareName`, which asserts setup.
 * Use `middlewareNameOnly($middleware)` to skip setup assertion.
 *
 * @param class-string<MiddlewareInterface>|MiddlewareNameInterface ...$middleware Middleware class name or instance.
 */
function middlewares(string|MiddlewareNameInterface ...$middleware): MiddlewaresInterface
{
    foreach ($middleware as &$item) {
        if (is_string($item)) {
            $item = new MiddlewareName($item);
        }
    }

    /** @var array<MiddlewareNameInterface> $middleware */
    return new Middlewares(...$middleware);
}

/**
 * Retrieves the Request attribute from the provided class name or the calling class.
 */
function requestAttribute(string $className = ''): ?Request
{
    if ($className === '') {
        $className = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'] ?? '';
    }
    // @phpstan-ignore-next-line
    $reflection = new ReflectionClass($className);

    // @phpstan-ignore-next-line
    return getAttribute($reflection, Request::class);
}

/**
 * Retrieves the Response attribute from the provided class name or the calling class.
 */
function responseAttribute(string $className = ''): ?Response
{
    if ($className === '') {
        $className = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'] ?? '';
    }
    // @phpstan-ignore-next-line
    $reflection = new ReflectionClass($className);

    // @phpstan-ignore-next-line
    return getAttribute($reflection, Response::class);
}

/**
 * Retrieves the Description attribute from the provided class name or the calling class.
 */
function descriptionAttribute(string $className = ''): ?Description
{
    if ($className === '') {
        $className = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'] ?? '';
    }
    // @phpstan-ignore-next-line
    $reflection = new ReflectionClass($className);

    // @phpstan-ignore-next-line
    return getAttribute($reflection, Description::class);
}

// @phpstan-ignore-next-line
function getAttribute(
    ReflectionClass|ReflectionFunction|ReflectionMethod|ReflectionProperty|ReflectionParameter|ReflectionClassConstant $reflection,
    string $attribute
): ?object {
    $attributes = $reflection->getAttributes($attribute);
    if ($attributes === []) {
        return null;
    }

    return $attributes[0]->newInstance();
}
