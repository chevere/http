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
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Parameter\Interfaces\ArrayParameterInterface;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\arrayString;

abstract class Controller extends BaseController implements ControllerInterface
{
    private ?ArgumentsInterface $query = null;

    private ?ArgumentsInterface $body = null;

    /**
     * @var ?array<ArgumentsInterface>
     */
    private ?array $files = null;

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

    final public function withQuery(array $query): static
    {
        $new = clone $this;
        $new->query = arguments($new::acceptQuery()->parameters(), $query);

        return $new;
    }

    final public function withBody(array $body): static
    {
        $new = clone $this;
        $new->body = arguments($new::acceptBody()->parameters(), $body);

        return $new;
    }

    final public function withFiles(array $files): static
    {
        $new = clone $this;
        $array = [];
        $parameters = $new->acceptFiles()->parameters();
        foreach ($files as $key => $file) {
            $key = strval($key);
            $parameters->assertHas($key);
            $collection = match (true) {
                $parameters->requiredKeys()->contains($key) => $parameters->required($key),
                default => $parameters->optional($key),
            };
            $arguments = arguments($collection->array(), $file);
            $array[$key] = $arguments;
        }
        $new->files = $array;

        return $new;
    }

    final public function query(): ArgumentsInterface
    {
        return $this->query
            ??= arguments(static::acceptQuery()->parameters(), []);
    }

    final public function body(): ArgumentsInterface
    {
        return $this->body
            ??= arguments(static::acceptBody()->parameters(), []);
    }

    final public function files(): array
    {
        return $this->files
            ??= [];
    }

    protected function assertRuntime(ReflectionActionInterface $reflection): void
    {
        $this->query();
        $this->body();
        $this->files();
    }
}
