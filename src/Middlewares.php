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

use Chevere\DataStructure\Interfaces\VectorInterface;
use Chevere\DataStructure\Traits\VectorTrait;
use Chevere\DataStructure\Vector;
use Chevere\Http\Interfaces\MiddlewareNameInterface;
use Chevere\Http\Interfaces\MiddlewaresInterface;
use Psr\Http\Server\MiddlewareInterface;

final class Middlewares implements MiddlewaresInterface
{
    use VectorTrait;

    /**
     * @var VectorInterface<MiddlewareNameInterface>
     */
    private VectorInterface $vector;

    /**
     * @var VectorInterface<class-string<MiddlewareInterface>>
     */
    private VectorInterface $index;

    public function __construct(MiddlewareNameInterface ...$middleware)
    {
        $this->vector = new Vector(...$middleware);
        $this->index = new Vector(...$this->toNames(...$middleware));
    }

    public function withAppend(MiddlewareNameInterface ...$middleware): self
    {
        $new = clone $this;
        $new->vector = $new->vector->withPush(...$middleware);
        $new->index = $new->index->withPush(...$this->toNames(...$middleware));

        return $new;
    }

    public function withPrepend(MiddlewareNameInterface ...$middleware): self
    {
        $new = clone $this;
        $new->vector = $new->vector->withUnshift(...$middleware);
        $new->index = $new->index->withUnshift(...$this->toNames(...$middleware));

        return $new;
    }

    public function has(string ...$middleware): bool
    {
        foreach ($middleware as $name) {
            if ($this->index->find($name) === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string>
     */
    private function toNames(MiddlewareNameInterface ...$middleware): array
    {
        $names = [];
        foreach ($middleware as $name) {
            $names[] = strval($name);
        }

        return $names;
    }
}
